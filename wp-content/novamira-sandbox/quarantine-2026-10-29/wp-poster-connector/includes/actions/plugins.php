<?php
/**
 * Plugin-management actions for the WP Poster Connector:
 * plugins.delete, plugin.install, plugin.self_update.
 *
 * The install / self-update actions drive WordPress's OWN upgrader
 * (Plugin_Upgrader) — the same machinery behind the wp-admin "Upload Plugin"
 * screen and the "Update now" button. We never hand-roll file copying.
 *
 * All three return the standard flat result (never a WP_Error): `error` is null
 * on success; on failure `code`/`error` are set, which the dispatch envelope
 * reports as ok:false.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Derive a plugin "slug" (folder name) from its plugin-file path.
 *
 * "akismet/akismet.php" -> "akismet"; a single-file plugin "hello.php" -> "hello".
 */
function wp_poster_connector_plugin_slug( $plugin_file ) {
	$dir = dirname( $plugin_file );
	if ( '.' !== $dir && '' !== $dir ) {
		return $dir;
	}
	return basename( $plugin_file, '.php' );
}

/**
 * Find an installed plugin's file path by a flexible identifier.
 *
 * Matches in order of reliability: exact plugin-file path, then folder slug,
 * then display name (both case-insensitive).
 *
 * @param array|null $all Optional pre-fetched get_plugins() map.
 */
function wp_poster_connector_find_plugin( $needle, $all = null ) {
	$needle = trim( (string) $needle );
	if ( '' === $needle ) {
		return '';
	}
	if ( null === $all ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$all = get_plugins();
	}

	// 1. Exact plugin-file path.
	if ( isset( $all[ $needle ] ) ) {
		return $needle;
	}

	$needle_l = strtolower( $needle );

	// 2. Folder slug.
	foreach ( $all as $file => $data ) {
		if ( strtolower( wp_poster_connector_plugin_slug( $file ) ) === $needle_l ) {
			return $file;
		}
	}

	// 3. Display name.
	foreach ( $all as $file => $data ) {
		$name = isset( $data['Name'] ) ? strtolower( $data['Name'] ) : '';
		if ( '' !== $name && $name === $needle_l ) {
			return $file;
		}
	}

	return '';
}

/**
 * Build the standard plugins.delete result.
 */
function wp_poster_connector_plugin_delete_result( $plugin_file, $slug, $was_active, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'plugin_file' => ( is_string( $plugin_file ) && '' !== $plugin_file ) ? $plugin_file : null,
			'slug'        => ( is_string( $slug ) && '' !== $slug ) ? $slug : null,
			'was_active'  => ( null !== $was_active ) ? (bool) $was_active : null,
		),
		$code,
		$error
	);
}

/**
 * Build the standard plugin.install result.
 */
function wp_poster_connector_plugin_install_result( $plugin_file, $activated, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'plugin_file' => ( is_string( $plugin_file ) && '' !== $plugin_file ) ? $plugin_file : null,
			'activated'   => (bool) $activated,
		),
		$code,
		$error
	);
}

/**
 * Build the standard plugin.self_update result.
 */
function wp_poster_connector_self_update_result( $updated, $version_before, $version_after, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'updated'        => (bool) $updated,
			'version_before' => ( is_string( $version_before ) && '' !== $version_before ) ? $version_before : null,
			'version_after'  => ( is_string( $version_after ) && '' !== $version_after ) ? $version_after : null,
		),
		$code,
		$error
	);
}

/**
 * action: plugins.delete — deactivate (if active) and delete a plugin.
 *
 * params: { name }  plugin display name, folder slug, or "folder/file.php" path.
 * (Also accepts plugin_file as an alias.) Refuses to delete the connector itself.
 *
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_action_delete_plugin( $params ) {
	$name = isset( $params['name'] ) ? (string) $params['name'] : '';
	if ( '' === trim( $name ) && isset( $params['plugin_file'] ) ) {
		$name = (string) $params['plugin_file'];
	}
	if ( '' === trim( $name ) ) {
		return wp_poster_connector_plugin_delete_result( null, null, null, 'wp_poster_bad_name', 'Provide params.name (plugin display name, folder slug, or "folder/file.php" path).' );
	}

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	$all         = get_plugins();
	$plugin_file = wp_poster_connector_find_plugin( $name, $all );
	if ( '' === $plugin_file ) {
		return wp_poster_connector_plugin_delete_result( null, null, null, 'wp_poster_plugin_not_found', sprintf( 'No installed plugin matched "%s".', $name ) );
	}

	$slug = wp_poster_connector_plugin_slug( $plugin_file );

	// Safety: never delete the connector through this action — deleting the
	// running plugin's own files mid-request is a foot-gun. Use self_update.
	if ( $plugin_file === plugin_basename( WP_POSTER_CONNECTOR_FILE ) ) {
		return wp_poster_connector_plugin_delete_result( $plugin_file, $slug, null, 'wp_poster_refuse_self_delete', 'Refusing to delete the connector itself. Use plugin.self_update to replace it.' );
	}

	// delete_plugins() writes to disk — same filesystem constraint as install.
	$method = get_filesystem_method();
	if ( 'direct' !== $method ) {
		return wp_poster_connector_plugin_delete_result( $plugin_file, $slug, null, 'wp_poster_fs_unavailable', 'WordPress cannot modify plugin files directly on this host (filesystem method is "' . $method . '").' );
	}
	if ( ! WP_Filesystem() ) {
		return wp_poster_connector_plugin_delete_result( $plugin_file, $slug, null, 'wp_poster_fs_unavailable', 'WP_Filesystem() initialization failed even though the method is "direct".' );
	}

	$was_active = is_plugin_active( $plugin_file );
	if ( $was_active ) {
		// Non-silent so the plugin's deactivation hooks run before its files go.
		deactivate_plugins( array( $plugin_file ) );
	}

	$deleted = delete_plugins( array( $plugin_file ) );
	if ( is_wp_error( $deleted ) ) {
		return wp_poster_connector_plugin_delete_result( $plugin_file, $slug, $was_active, 'wp_poster_delete_failed', 'delete_plugins failed: ' . $deleted->get_error_message() );
	}
	if ( true !== $deleted ) {
		// false (empty list) or null (filesystem credentials needed).
		return wp_poster_connector_plugin_delete_result( $plugin_file, $slug, $was_active, 'wp_poster_delete_failed', 'delete_plugins did not complete (filesystem credentials may be required).' );
	}

	return wp_poster_connector_plugin_delete_result( $plugin_file, $slug, $was_active, null, null );
}

/**
 * Download a plugin zip from an http(s) URL and install it via WP's upgrader.
 *
 * Shared by plugin.install and plugin.self_update. Returns an array on success
 * (installed/plugin_file/activated/activate_error/messages) or a WP_Error on
 * failure; callers map that into the standard flat result.
 *
 * @param array $opts { @type bool $overwrite Replace in place. Default true. @type bool $activate Activate after install. Default false. }
 */
function wp_poster_connector_install_from_url( $url, $opts = array() ) {
	$overwrite = isset( $opts['overwrite'] ) ? (bool) $opts['overwrite'] : true;
	$activate  = isset( $opts['activate'] ) ? (bool) $opts['activate'] : false;

	$url = esc_url_raw( (string) $url );
	if ( '' === $url || ! preg_match( '#^https?://#i', $url ) ) {
		return new WP_Error( 'wp_poster_bad_url', 'A valid http(s) zip URL is required.', array( 'status' => 400 ) );
	}

	// The upgrade machinery is only loaded inside wp-admin; pull it in here.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	// Determine the filesystem method BEFORE touching WP_Filesystem(): on a
	// non-'direct' host it would render an FTP/SSH credentials form (echoing HTML
	// that corrupts our JSON response), so we bail cleanly here instead.
	$method = get_filesystem_method();
	if ( 'direct' !== $method ) {
		return new WP_Error(
			'wp_poster_fs_unavailable',
			'WordPress cannot write plugin files directly on this host '
			. '(filesystem method is "' . $method . '"; FTP/SSH credentials would be '
			. 'required, or DISALLOW_FILE_MODS is set). Install/update via files is not '
			. 'possible here.',
			array( 'status' => 423 )
		);
	}
	if ( ! WP_Filesystem() ) {
		return new WP_Error( 'wp_poster_fs_unavailable', 'WP_Filesystem() initialization failed even though the method is "direct".', array( 'status' => 423 ) );
	}

	$skin     = new Automatic_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader( $skin );

	$install_args = array();
	if ( $overwrite ) {
		$install_args['overwrite_package'] = true;
	}

	$result   = $upgrader->install( $url, $install_args );
	$messages = method_exists( $skin, 'get_upgrade_messages' ) ? $skin->get_upgrade_messages() : array();

	if ( is_wp_error( $result ) ) {
		return new WP_Error( 'wp_poster_install_failed', 'Install failed: ' . $result->get_error_message(), array( 'status' => 500, 'messages' => $messages ) );
	}
	if ( true !== $result ) {
		$skin_errors = method_exists( $skin, 'get_errors' ) ? $skin->get_errors() : null;
		$reason      = ( $skin_errors instanceof WP_Error && $skin_errors->has_errors() )
			? $skin_errors->get_error_message()
			: 'installer returned no success result';
		return new WP_Error( 'wp_poster_install_failed', 'Install did not complete: ' . $reason, array( 'status' => 500, 'messages' => $messages ) );
	}

	$plugin_file    = $upgrader->plugin_info(); // "<folder>/<file>.php" or false
	$activated      = false;
	$activate_error = null;
	if ( $activate && $plugin_file ) {
		$act = activate_plugin( $plugin_file );
		if ( is_wp_error( $act ) ) {
			$activate_error = $act->get_error_message();
		} else {
			$activated = true;
		}
	}

	return array(
		'installed'      => true,
		'plugin_file'    => $plugin_file ? $plugin_file : null,
		'activated'      => $activated,
		'activate_error' => $activate_error,
		'messages'       => $messages,
	);
}

/**
 * action: plugin.install — install (and by default activate) any plugin from a zip URL.
 *
 * params: { url, activate?(default true), overwrite?(default true) }
 *
 * If activation was requested but failed, that is reported as a FAILURE
 * (code wp_poster_activation_failed) — but plugin_file is still set and
 * activated:false, so the caller can see the install itself succeeded.
 *
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_action_install_plugin( $params ) {
	$url       = isset( $params['url'] ) ? (string) $params['url'] : '';
	$activate  = isset( $params['activate'] ) ? filter_var( $params['activate'], FILTER_VALIDATE_BOOLEAN ) : true;
	$overwrite = isset( $params['overwrite'] ) ? filter_var( $params['overwrite'], FILTER_VALIDATE_BOOLEAN ) : true;

	$res = wp_poster_connector_install_from_url( $url, array( 'overwrite' => $overwrite, 'activate' => $activate ) );
	if ( is_wp_error( $res ) ) {
		return wp_poster_connector_plugin_install_result( null, false, $res->get_error_code(), $res->get_error_message() );
	}

	$plugin_file = isset( $res['plugin_file'] ) ? $res['plugin_file'] : null;
	$activated   = ! empty( $res['activated'] );

	// Activation requested but it didn't take -> a failure. The install part DID
	// succeed (plugin_file set, activated:false makes that explicit).
	if ( $activate && ! $activated ) {
		$why = ! empty( $res['activate_error'] ) ? $res['activate_error'] : 'activation did not complete.';
		return wp_poster_connector_plugin_install_result( $plugin_file, false, 'wp_poster_activation_failed', 'Plugin installed but not activated: ' . $why );
	}

	return wp_poster_connector_plugin_install_result( $plugin_file, $activated, null, null );
}

/**
 * action: plugin.self_update — overwrite THIS plugin in place from a zip URL.
 *
 * params: { url }  (the new connector .zip)
 *
 * Overwritten in place (not deleted first), so the connector stays active. The
 * new code does NOT take effect in this request — this very response is rendered
 * by the OLD version still in memory; the new version is live on the next
 * request. We confirm the swap by reading the version header from disk.
 *
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_action_self_update( $params ) {
	$url = isset( $params['url'] ) ? (string) $params['url'] : '';
	if ( '' === trim( $url ) ) {
		return wp_poster_connector_self_update_result( false, null, null, 'wp_poster_bad_url', 'Provide params.url pointing at the new connector zip.' );
	}

	$version_before = WP_POSTER_CONNECTOR_VERSION; // old, in-memory value

	$res = wp_poster_connector_install_from_url( $url, array( 'overwrite' => true, 'activate' => false ) );
	if ( is_wp_error( $res ) ) {
		return wp_poster_connector_self_update_result( false, $version_before, null, $res->get_error_code(), $res->get_error_message() );
	}

	// Read the freshly written file's version from disk (plugin.php was loaded by
	// the helper above, so get_plugin_data() is available).
	$version_after = $version_before;
	$data          = get_plugin_data( WP_POSTER_CONNECTOR_FILE, false, false );
	if ( ! empty( $data['Version'] ) ) {
		$version_after = $data['Version'];
	}

	return wp_poster_connector_self_update_result( ( $version_after !== $version_before ), $version_before, $version_after, null, null );
}
