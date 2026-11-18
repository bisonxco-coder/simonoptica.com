<?php
/**
 * Uninstall cleanup. Runs only on plugin delete (WordPress defines
 * WP_UNINSTALL_PLUGIN). Removes the head/footer injection option so the
 * injected HTML doesn't linger site-wide after the render hooks are gone.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wp_poster_connector_injections' );
