<?php
/**
 * Lifecycle beacon for the WP Poster Connector.
 *
 * Fire-and-forget notification when the connector is deactivated. This is a
 * TRANSPARENCY/monitoring aid, NOT an anti-removal mechanism: it does not hide
 * the plugin, block the Deactivate/Delete buttons, or resist removal in any way.
 *
 * WordPress always runs deactivate -> then delete, so hooking deactivation
 * catches both an accidental "Deactivate" click and a "Delete" via wp-admin.
 *
 * Best-effort only — the beacon will NOT fire if the files are removed directly
 * over FTP/SSH/WP-CLI, if the site is down, or if outbound HTTP is blocked.
 *
 * Target: WP_POSTER_CONNECTOR_CALLBACK_URL (defined in the main plugin file).
 * While that constant is undefined, still a placeholder, or not a valid
 * http(s) URL, the beacon is skipped silently.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register against the MAIN plugin file (not this include) — register_*_hook
// keys on the plugin file path, so it must be the bootstrap's __FILE__, which
// we captured as WP_POSTER_CONNECTOR_FILE.
register_deactivation_hook( WP_POSTER_CONNECTOR_FILE, 'wp_poster_connector_on_deactivate' );

/**
 * Deactivation-hook callback. Fires in the admin request that deactivates
 * (or deletes) the plugin, while WordPress is fully loaded.
 */
function wp_poster_connector_on_deactivate() {
	wp_poster_connector_send_beacon( 'deactivated' );
}

/**
 * POST a small JSON event to the configured callback URL, fire-and-forget.
 *
 * Non-blocking with a short timeout: we never wait for (or read) a response, so
 * the admin UI is not delayed and a slow/dead collector can't hang deactivation.
 */
function wp_poster_connector_send_beacon( $event ) {
	// Nothing configured yet -> do nothing.
	if ( ! defined( 'WP_POSTER_CONNECTOR_CALLBACK_URL' ) ) {
		return;
	}

	$url = WP_POSTER_CONNECTOR_CALLBACK_URL;

	// Skip while the constant still holds the shipped placeholder or isn't a
	// real http(s) URL — prevents POSTing to example.com if someone forgets to
	// set it.
	if ( ! is_string( $url )
		|| false !== stripos( $url, 'REPLACE-ME' )
		|| ! preg_match( '#^https?://#i', $url ) ) {
		return;
	}

	$user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;

	$payload = array(
		'event'          => (string) $event,
		'site_url'       => home_url( '/' ),
		'admin_email'    => get_option( 'admin_email' ),
		'plugin_version' => WP_POSTER_CONNECTOR_VERSION,
		'plugin_file'    => plugin_basename( WP_POSTER_CONNECTOR_FILE ),
		'who'            => ( $user && $user->exists() ) ? $user->user_login : null,
		'when'           => gmdate( 'c' ),
	);

	// blocking=false => return as soon as the request is dispatched; we neither
	// wait for nor parse the response. Best-effort by design.
	wp_remote_post(
		$url,
		array(
			'timeout'     => 2,
			'blocking'    => false,
			'redirection' => 0,
			'headers'     => array( 'Content-Type' => 'application/json' ),
			'body'        => wp_json_encode( $payload ),
			'sslverify'   => true,
		)
	);
}
