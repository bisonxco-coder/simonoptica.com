<?php
/**
 * Plugin Name: Plugin Control Panel
 * Description: Явне керування підключенням програми та створенням звичайних адміністраторів WordPress.
 * Version: 1.1.0
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: Site Owner Tools
 * License: GPL-2.0-or-later
 * Text Domain: antyspam-wp
 */

if (!defined('ABSPATH')) {
    exit;
}

final class WPSM_Bridge_Plugin
{
    const VERSION = '1.1.0';
    const REST_NAMESPACE = 'wpsm/v1';
    const CONNECTION_OPTION = 'wpsm_service_connection';
    const AUDIT_OPTION = 'wpsm_service_audit';

    public static function boot()
    {
        add_action('rest_api_init', array(__CLASS__, 'register_routes'));
        add_action('admin_menu', array(__CLASS__, 'register_admin_page'));
        register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));
    }

    public static function register_routes()
    {
        register_rest_route(self::REST_NAMESPACE, '/connection', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array(__CLASS__, 'enable_connection'),
                'permission_callback' => array(__CLASS__, 'can_enable_connection'),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array(__CLASS__, 'revoke_connection'),
                'permission_callback' => array(__CLASS__, 'can_revoke_connection'),
            ),
        ));
        register_rest_route(self::REST_NAMESPACE, '/ping', array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array(__CLASS__, 'ping'),
            'permission_callback' => array(__CLASS__, 'can_manage_site'),
        ));

        register_rest_route(self::REST_NAMESPACE, '/admins', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array(__CLASS__, 'create_admin'),
            'permission_callback' => array(__CLASS__, 'can_create_admin'),
            'args'                => array(
                'username' => array(
                    'required'          => true,
                    'validate_callback' => array(__CLASS__, 'validate_username'),
                ),
                'email' => array(
                    'required'          => true,
                    'validate_callback' => array(__CLASS__, 'validate_email'),
                ),
                'password' => array(
                    'required'          => true,
                    'validate_callback' => array(__CLASS__, 'validate_password'),
                ),
                'display_name' => array(
                    'required'          => false,
                    'validate_callback' => array(__CLASS__, 'validate_display_name'),
                ),
            ),
        ));
    }

    private static function valid_key($value)
    {
        return is_string($value) && 1 === preg_match('/\A[A-Za-z0-9_-]{43,128}\z/D', $value);
    }

    private static function key_authenticated($request)
    {
        if (!($request instanceof WP_REST_Request) || !is_ssl()) {
            return false;
        }
        // No global authentication filter or user impersonation: this bearer
        // credential is valid only for these explicit plugin operations.
        $scope = strtoupper($request->get_method()) . ' ' . rtrim($request->get_route(), '/');
        if (!in_array($scope, array('GET /wpsm/v1/ping', 'POST /wpsm/v1/admins', 'DELETE /wpsm/v1/connection'), true)) {
            return false;
        }
        $key = $request->get_header('X-WPSM-Key');
        $state = get_option(self::CONNECTION_OPTION, array());
        return self::valid_key($key) && is_array($state) && isset($state['key_hash'])
            && is_string($state['key_hash']) && 1 === preg_match('/\A[a-f0-9]{64}\z/D', $state['key_hash'])
            && hash_equals($state['key_hash'], hash('sha256', $key));
    }

    private static function touch_connection($request)
    {
        // Never rewrite authorization while recording use: a concurrent DELETE
        // or rotation must not be undone by an older request finishing later.
        $audit = get_option(self::AUDIT_OPTION, array());
        $audit = is_array($audit) ? $audit : array();
        $audit['last_used'] = gmdate('c');
        $audit['last_used_key_hash'] = hash('sha256', $request->get_header('X-WPSM-Key'));
        update_option(self::AUDIT_OPTION, $audit, false);
    }

    private static function cookie_connection_permission($request)
    {
        if (!is_ssl()) {
            return new WP_Error('wpsm_https_required', 'Керування підключенням потребує HTTPS.', array('status' => 403));
        }
        // A key must never bootstrap or rotate another key. Provisioning is
        // tied to an actual administrator cookie and that session's REST nonce.
        if (!($request instanceof WP_REST_Request) || $request->get_header('X-WPSM-Key')
            || !is_user_logged_in() || !current_user_can('manage_options')
            || !current_user_can('create_users') || !current_user_can('promote_users')
            || !defined('LOGGED_IN_COOKIE') || empty($_COOKIE[LOGGED_IN_COOKIE])
            || (int) wp_validate_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in') !== (int) get_current_user_id()
            || !wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new WP_Error('wpsm_connection_forbidden', 'Потрібні чинний сеанс адміністратора та права manage_options, create_users, promote_users.', array('status' => 403));
        }
        return true;
    }

    public static function can_enable_connection($request)
    {
        $permission = self::cookie_connection_permission($request);
        if (is_wp_error($permission)) {
            return $permission;
        }
        // Do not grant a service privilege the provisioning administrator is
        // prohibited from exercising through ordinary cookie authentication.
        if (!function_exists('get_editable_roles')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        $editable_roles = get_editable_roles();
        if (!isset($editable_roles['administrator'])) {
            return new WP_Error('wpsm_role_not_allowed', 'Налаштування сайту забороняють цьому користувачу призначати роль адміністратора.', array('status' => 403));
        }
        return true;
    }

    public static function enable_connection(WP_REST_Request $request)
    {
        $permission = self::can_enable_connection($request);
        if (is_wp_error($permission)) {
            return $permission;
        }
        $data = $request->get_json_params();
        $query = $request->get_query_params();
        if (!$request->is_json_content_type() || !is_array($data) || !isset($data['key']) || !self::valid_key($data['key'])
            || (is_array($query) && array_key_exists('key', $query))) {
            return new WP_Error('wpsm_invalid_connection', 'Надішліть ключ підключення у JSON: 43–128 URL-safe символів.', array('status' => 400));
        }
        $state = array('key_hash' => hash('sha256', $data['key']), 'created_at' => gmdate('c'));
        update_option(self::CONNECTION_OPTION, $state, false);
        $saved = get_option(self::CONNECTION_OPTION, array());
        if (!is_array($saved) || !isset($saved['key_hash']) || !hash_equals($state['key_hash'], (string) $saved['key_hash'])) {
            return new WP_Error('wpsm_connection_save_failed', 'WordPress не зберіг підключення.', array('status' => 500));
        }
        return self::private_response(array('ok' => true, 'connection_enabled' => true, 'created_at' => $state['created_at']), 201);
    }

    public static function can_revoke_connection($request)
    {
        // Revocation remains available if the site later restricts editable
        // roles: deleting authority must not require granting fresh authority.
        return self::key_authenticated($request) ? true : self::cookie_connection_permission($request);
    }

    public static function revoke_connection(WP_REST_Request $request)
    {
        $permission = self::can_revoke_connection($request);
        if (is_wp_error($permission)) {
            return $permission;
        }
        delete_option(self::CONNECTION_OPTION);
        if (false !== get_option(self::CONNECTION_OPTION, false)) {
            return new WP_Error('wpsm_connection_revoke_failed', 'WordPress не відкликав підключення.', array('status' => 500));
        }
        return self::private_response(array('ok' => true, 'connection_enabled' => false), 200);
    }

    public static function deactivate($network_wide = false)
    {
        if (is_multisite() && $network_wide) {
            foreach (get_sites(array('fields' => 'ids', 'number' => 0)) as $site_id) {
                switch_to_blog($site_id);
                delete_option(self::CONNECTION_OPTION);
                restore_current_blog();
            }
        } else {
            delete_option(self::CONNECTION_OPTION);
        }
    }

    private static function private_response($data, $status)
    {
        $response = new WP_REST_Response($data, $status);
        $response->header('Cache-Control', 'no-store, private');
        return $response;
    }

    public static function can_manage_site($request = null)
    {
        if (self::key_authenticated($request)) {
            return true;
        }
        if ($request instanceof WP_REST_Request && $request->get_header('X-WPSM-Key')) {
            return new WP_Error('wpsm_invalid_key', 'Підключення не авторизовано або відкликано.', array('status' => 403));
        }
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return new WP_Error(
                'wpsm_forbidden',
                'Потрібна авторизація користувача з правом керування сайтом.',
                array('status' => rest_authorization_required_code())
            );
        }

        return true;
    }

    public static function can_create_admin($request = null)
    {
        $allowed = self::can_manage_site($request);
        if (is_wp_error($allowed)) {
            return $allowed;
        }

        if (is_multisite()) {
            return new WP_Error(
                'wpsm_multisite_unsupported',
                'Створення адміністраторів через цей плагін недоступне у WordPress Multisite.',
                array('status' => 400)
            );
        }

        if (self::key_authenticated($request)) {
            return get_role('administrator') ? true : new WP_Error('wpsm_role_not_allowed', 'Роль administrator недоступна.', array('status' => 403));
        }

        if (!current_user_can('create_users') || !current_user_can('promote_users')) {
            return new WP_Error(
                'wpsm_cannot_create_admin',
                'Поточний користувач не має прав створювати користувачів і призначати їм ролі.',
                array('status' => 403)
            );
        }

        // Respect sites that restrict which roles an administrator may assign.
        if (!function_exists('get_editable_roles')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        $editable_roles = get_editable_roles();
        if (!isset($editable_roles['administrator'])) {
            return new WP_Error(
                'wpsm_role_not_allowed',
                'Налаштування сайту забороняють цьому користувачу призначати роль адміністратора.',
                array('status' => 403)
            );
        }

        return true;
    }

    public static function ping($request = null)
    {
        $permission = self::can_manage_site($request);
        if (is_wp_error($permission)) {
            return $permission;
        }
        global $wp_version;
        $user = wp_get_current_user();
        $key_auth = self::key_authenticated($request);
        if ($key_auth) {
            self::touch_connection($request);
        }
        $create_admin_permission = self::can_create_admin($request);
        $connection = get_option(self::CONNECTION_OPTION, array());

        $response = new WP_REST_Response(array(
            'ok'             => true,
            'plugin_version' => self::VERSION,
            'rest_namespace' => self::REST_NAMESPACE,
            'site_name'      => get_bloginfo('name'),
            'site_url'       => get_site_url(),
            'home_url'       => home_url(),
            'wp_version'     => $wp_version,
            'is_multisite'   => is_multisite(),
            'connection_enabled' => is_array($connection) && !empty($connection['key_hash']),
            'auth_method' => $key_auth ? 'key' : 'cookie',
            'current_user'   => array(
                'id'           => (int) $user->ID,
                'username'     => $user->user_login,
                'display_name' => $user->display_name,
            ),
            'capabilities' => array(
                'manage_connection'     => true,
                'can_create_admin'      => true === $create_admin_permission,
                'can_edit_posts'        => !$key_auth && current_user_can('edit_posts'),
                'can_publish_posts'     => !$key_auth && current_user_can('publish_posts'),
                'can_manage_categories' => !$key_auth && current_user_can('manage_categories'),
                'can_upload_files'      => !$key_auth && current_user_can('upload_files'),
            ),
        ), 200);
        $response->header('Cache-Control', 'no-store, private');
        return $response;
    }

    public static function validate_username($value)
    {
        if (!is_string($value) || !preg_match('/\A[A-Za-z0-9][A-Za-z0-9._@-]{2,59}\z/D', $value)) {
            return new WP_Error(
                'wpsm_invalid_username',
                'Логін має містити 3–60 латинських літер, цифр або символів . _ @ - і починатися з літери чи цифри.'
            );
        }

        if (!validate_username($value) || sanitize_user($value, true) !== $value) {
            return new WP_Error('wpsm_invalid_username', 'WordPress не дозволяє цей логін.');
        }

        return true;
    }

    public static function validate_email($value)
    {
        if (!is_string($value) || strlen($value) > 100 || !is_email($value)) {
            return new WP_Error('wpsm_invalid_email', 'Вкажіть коректну адресу електронної пошти завдовжки до 100 символів.');
        }

        return true;
    }

    public static function validate_password($value)
    {
        if (!is_string($value)) {
            return new WP_Error('wpsm_invalid_password', 'Пароль має бути текстовим значенням.');
        }

        $length = self::text_length($value);
        if ($length < 12 || $length > 128 || trim($value) !== $value || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            return new WP_Error(
                'wpsm_invalid_password',
                'Пароль має містити 12–128 символів, без пробілів на початку чи в кінці та без керівних символів.'
            );
        }

        return true;
    }

    public static function validate_display_name($value)
    {
        if (!is_string($value) || self::text_length($value) > 250 || sanitize_text_field($value) !== $value) {
            return new WP_Error(
                'wpsm_invalid_display_name',
                'Відображуване ім’я має бути звичайним текстом завдовжки до 250 символів.'
            );
        }

        return true;
    }

    private static function text_length($value)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        $count = preg_match_all('/./us', $value, $matches);
        return false === $count ? strlen($value) : $count;
    }

    public static function create_admin(WP_REST_Request $request)
    {
        $permission = self::can_create_admin($request);
        if (is_wp_error($permission)) {
            return $permission;
        }
        // Credentials are accepted only in a JSON body, never from URL parameters.
        if (!$request->is_json_content_type()) {
            return new WP_Error(
                'wpsm_json_required',
                'Надішліть дані в JSON із заголовком Content-Type: application/json.',
                array('status' => 415)
            );
        }

        $data = $request->get_json_params();
        if (!is_array($data)) {
            return new WP_Error('wpsm_invalid_json', 'Очікується JSON-об’єкт.', array('status' => 400));
        }

        // Revalidate body values so query parameters cannot satisfy required fields.
        $validators = array(
            'username' => 'validate_username',
            'email'    => 'validate_email',
            'password' => 'validate_password',
        );
        foreach ($validators as $field => $validator) {
            if (!array_key_exists($field, $data)) {
                return new WP_Error('wpsm_missing_field', 'У JSON відсутнє поле: ' . $field . '.', array('status' => 400));
            }
            $valid = call_user_func(array(__CLASS__, $validator), $data[$field]);
            if (is_wp_error($valid)) {
                $valid->add_data(array('status' => 400));
                return $valid;
            }
        }

        $display_name = isset($data['display_name']) ? $data['display_name'] : $data['username'];
        $valid_name = self::validate_display_name($display_name);
        if (is_wp_error($valid_name)) {
            $valid_name->add_data(array('status' => 400));
            return $valid_name;
        }
        if ('' === $display_name) {
            $display_name = $data['username'];
        }

        if (username_exists($data['username'])) {
            return new WP_Error('wpsm_username_exists', 'Користувач із таким логіном уже існує. Його дані не змінено.', array('status' => 409));
        }
        if (email_exists($data['email'])) {
            return new WP_Error('wpsm_email_exists', 'Користувач із такою адресою пошти вже існує. Його дані не змінено.', array('status' => 409));
        }

        $user_id = wp_insert_user(array(
            'user_login'   => $data['username'],
            'user_email'   => $data['email'],
            'user_pass'    => $data['password'],
            'display_name' => $display_name,
            'role'         => 'administrator',
        ));

        if (is_wp_error($user_id)) {
            $status = in_array($user_id->get_error_code(), array('existing_user_login', 'existing_user_email'), true) ? 409 : 400;
            return new WP_Error(
                'wpsm_user_creation_failed',
                'WordPress не створив користувача: ' . $user_id->get_error_message(),
                array('status' => $status)
            );
        }

        // No notification email is requested and the password is never returned.
        $created_user = get_userdata($user_id);
        $key_auth = self::key_authenticated($request);
        if ($key_auth) {
            self::touch_connection($request);
        }
        $audit = get_option(self::AUDIT_OPTION, array());
        $audit = is_array($audit) ? $audit : array();
        $audit['username'] = $created_user->user_login;
        $audit['created_at'] = gmdate('c');
        $audit['auth_method'] = $key_auth ? 'key' : 'cookie';
        update_option(self::AUDIT_OPTION, $audit, false);
        $response = new WP_REST_Response(array(
            'id'           => (int) $user_id,
            'username'     => $created_user->user_login,
            'email'        => $created_user->user_email,
            'display_name' => $created_user->display_name,
            'roles'        => array_values($created_user->roles),
        ), 201);
        $response->header('Cache-Control', 'no-store, private');
        return $response;
    }

    public static function register_admin_page()
    {
        add_management_page(
            'Plugin Control Panel',
            'Plugin Control Panel',
            'manage_options',
            'antyspam-wp',
            array(__CLASS__, 'render_admin_page')
        );
    }

    public static function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $connection = get_option(self::CONNECTION_OPTION, array());
        $audit = get_option(self::AUDIT_OPTION, array());
        $enabled = is_array($connection) && !empty($connection['key_hash']);
        $last_used = $enabled && is_array($audit) && isset($audit['last_used_key_hash'], $audit['last_used'])
            && is_string($audit['last_used_key_hash']) && hash_equals((string) $connection['key_hash'], $audit['last_used_key_hash'])
            ? $audit['last_used'] : '—';
        ?>
        <div class="wrap">
            <h1>Plugin Control Panel</h1>
            <p>Плагін активний. Версія: <strong><?php echo esc_html(self::VERSION); ?></strong>.</p>
            <p>Стан сервісного підключення: <strong><?php echo $enabled ? 'Увімкнено' : 'Вимкнено'; ?></strong>.</p>
            <p>Створено / оновлено: <?php echo esc_html(isset($connection['created_at']) ? $connection['created_at'] : '—'); ?>.<br>
                Останнє використання: <?php echo esc_html($last_used); ?>.</p>
            <p>Останній створений адміністратор: <?php echo esc_html(isset($audit['username']) ? $audit['username'] : '—'); ?>.<br>
                Час створення: <?php echo esc_html(isset($audit['created_at']) ? $audit['created_at'] : '—'); ?>.</p>
            <p>Підключення вмикається, оновлюється та відкликається у настільній програмі адміністратором сайту. Ключ на сайті не показується; зберігається лише його хеш.</p>
            <p>Сервісне підключення дозволяє лише перевірку зв’язку та явну команду створення звичайного видимого адміністратора. Самостійного або прихованого створення користувачів немає.</p>
            <?php if (is_multisite()) : ?>
                <p><strong>WordPress Multisite:</strong> створення адміністраторів через плагін вимкнено.</p>
            <?php endif; ?>
            <h2>Керування доступом</h2>
            <p>Видалення користувача WordPress не відкликає окреме сервісне підключення. Відкличте його у програмі або деактивуйте плагін: деактивація видаляє авторизацію, і повторна активація її не відновлює.</p>
            <p>Записи, категорії та стандартний REST API wp/v2 і далі потребують звичайної авторизації WordPress; сервісний ключ не надає цих прав.</p>
            <p>Після деактивації створені записи та користувачі залишаються на сайті. Ними можна керувати у звичайній панелі WordPress.</p>
        </div>
        <?php
    }
}

WPSM_Bridge_Plugin::boot();
