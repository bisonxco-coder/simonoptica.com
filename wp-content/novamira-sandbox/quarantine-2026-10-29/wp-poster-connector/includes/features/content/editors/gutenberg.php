<?php
/**
 * Gutenberg (block editor) inserter.
 *
 * Gutenberg stores serialized blocks in post_content. We wrap the marked block
 * in a Custom HTML block (wp:html) so the page stays valid in the block editor
 * and the raw HTML renders on the front end, then append it via the shared
 * kses-bypassing post_content writer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert the marked block into a Gutenberg post as a wp:html block.
 */
function wp_poster_connector_insert_gutenberg( $post, $html, $container_id ) {
	// Custom HTML block: the editor treats the body as raw HTML, and the front
	// end renders it verbatim — so our off-screen wrapper + marker survive.
	$block = "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";

	$res = wp_poster_connector_append_post_content( (int) $post->ID, (string) $post->post_content, $block );
	return array(
		'ok'      => $res['ok'],
		'method'  => 'gutenberg_html_block_append',
		'storage' => 'post_content',
		'error'   => $res['error'],
	);
}

/**
 * Remove wp-poster wrapper(s) from a Gutenberg post's post_content.
 *
 * Strips the wrapper div (surgical/bulk like Classic), then collapses any
 * Custom HTML block (wp:html) left empty by the removal — so we don't leave
 * orphan "<!-- wp:html --><!-- /wp:html -->" pairs behind.
 */
function wp_poster_connector_remove_gutenberg( $post, $container_id ) {
	$post_id = (int) $post->ID;
	$content = (string) $post->post_content;
	$count   = 0;
	$new     = preg_replace( wp_poster_connector_wrapper_regex( $container_id ), '', $content, -1, $count );

	if ( null === $new ) {
		return array(
			'ok'      => false,
			'removed' => 0,
			'method'  => 'gutenberg_html_block_remove',
			'storage' => 'post_content',
			'error'   => 'preg_replace error (code ' . preg_last_error() . ').',
		);
	}

	if ( $count > 0 ) {
		// Collapse Custom HTML blocks emptied by the wrapper removal.
		$new = preg_replace( '/<!--\s*wp:html\s*-->\s*<!--\s*\/wp:html\s*-->/i', '', $new );

		$res = wp_poster_connector_write_without_kses(
			function () use ( $post_id, $new ) {
				return wp_update_post( array( 'ID' => $post_id, 'post_content' => $new ), true );
			}
		);
		if ( is_wp_error( $res ) ) {
			return array(
				'ok'      => false,
				'removed' => 0,
				'method'  => 'gutenberg_html_block_remove',
				'storage' => 'post_content',
				'error'   => 'wp_update_post failed: ' . $res->get_error_message(),
			);
		}
	}

	return array(
		'ok'      => true,
		'removed' => $count,
		'method'  => 'gutenberg_html_block_remove',
		'storage' => 'post_content',
		'error'   => null,
	);
}
