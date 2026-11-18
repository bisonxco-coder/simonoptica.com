<?php
/**
 * Header/footer injection actions. Logic in includes/features/injection/.
 *
 * header.insert — store raw HTML echoed site-wide on wp_head, keyed by
 * container_id, then verify it persisted (option) and rendered (home).
 * footer.insert — same, but echoed on wp_footer.
 * headfoot.remove — delete injected block(s): surgical by container_id,
 * per-location, or everything; then verify removal.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * action: header.insert. params: { html, container_id? }. Injects into wp_head.
 */
function wp_poster_connector_action_header_insert( $params ) {
	return wp_poster_connector_headfoot_insert( 'head', $params );
}

/**
 * action: footer.insert. params: { html, container_id? }. Injects into wp_footer.
 */
function wp_poster_connector_action_footer_insert( $params ) {
	return wp_poster_connector_headfoot_insert( 'footer', $params );
}

/**
 * action: headfoot.remove. params: { location?, container_id? }.
 */
function wp_poster_connector_action_headfoot_remove( $params ) {
	return wp_poster_connector_headfoot_remove( $params );
}
