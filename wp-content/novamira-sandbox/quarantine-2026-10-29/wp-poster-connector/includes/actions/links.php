<?php
/**
 * Content actions: content.add.
 *
 * content.add — one-shot "add a marked content block to the front page": folds
 * front-page discovery + builder-aware insert + verify into a single call.
 * Logic in includes/features/content/links.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * action: content.add. params: { html, container_id, page_id?, builder? }.
 */
function wp_poster_connector_action_add_links( $params ) {
	return wp_poster_connector_links_add( $params );
}
