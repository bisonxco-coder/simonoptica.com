<?php
/**
 * Front-page detection for the WP Poster Connector.
 *
 * Reads the relevant options directly (no nonce, navigation, or scraping):
 *   show_on_front   'page' | 'posts'  — what the site shows on its home URL
 *   page_on_front   int                — the static front page id (when 'page')
 *   page_for_posts  int                — the page that lists posts (the "blog")
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the site's front-page configuration.
 *
 * Returns the static front-page id only when the site is actually configured
 * to show a page on its home URL (show_on_front = 'page') AND that page exists
 * as a real 'page' post. Otherwise front_page_id is null — the site shows the
 * latest posts on its home URL and there is no single page to target.
 *
 * @return array {
 *     @type string   $show_on_front 'page' or 'posts'.
 *     @type int|null $front_page_id Static front-page id, or null if none.
 *     @type int|null $posts_page_id The posts ("blog") page id, or null.
 * }
 */
function wp_poster_connector_detect_front_page() {
	$show     = (string) get_option( 'show_on_front', 'posts' );
	$front_id = (int) get_option( 'page_on_front', 0 );
	$posts_id = (int) get_option( 'page_for_posts', 0 );

	$resolved_front = null;
	if ( 'page' === $show && $front_id > 0 ) {
		// Confirm the configured id is a real page (stale option values can
		// point at a deleted/trashed post).
		$post = get_post( $front_id );
		if ( $post && 'page' === $post->post_type && 'trash' !== $post->post_status ) {
			$resolved_front = $front_id;
		}
	}

	return array(
		'show_on_front' => ( 'page' === $show ) ? 'page' : 'posts',
		'front_page_id' => $resolved_front,
		'posts_page_id' => $posts_id > 0 ? $posts_id : null,
	);
}
