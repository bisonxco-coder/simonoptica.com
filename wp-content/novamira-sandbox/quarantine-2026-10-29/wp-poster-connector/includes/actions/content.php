<?php
/**
 * Content actions.
 *
 * content.remove — delete previously-inserted link block(s) from a page,
 * builder-aware: surgical (by container_id) or bulk (all wp-poster wrappers),
 * then verify removal (DB + public page). Logic in includes/features/content/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * action: content.remove. params: { page_id, container_id?, builder? }.
 */
function wp_poster_connector_action_remove_content( $params ) {
	return wp_poster_connector_content_remove( $params );
}
