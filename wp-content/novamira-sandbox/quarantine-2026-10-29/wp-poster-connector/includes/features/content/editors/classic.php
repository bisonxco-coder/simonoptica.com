<?php
/**
 * Classic Editor inserter.
 *
 * Classic posts store raw HTML in post_content. We append the marked block
 * verbatim (no builder wrapper) via the shared kses-bypassing post_content
 * writer (see includes/features/content/insert.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert the marked block into a Classic Editor post.
 */
function wp_poster_connector_insert_classic( $post, $html, $container_id ) {
	$res = wp_poster_connector_append_post_content( (int) $post->ID, (string) $post->post_content, $html );
	return array(
		'ok'      => $res['ok'],
		'method'  => 'classic_post_content_append',
		'storage' => 'post_content',
		'error'   => $res['error'],
	);
}

/**
 * Remove wp-poster wrapper(s) from a Classic Editor post's post_content.
 *
 * Surgical when $container_id is given (only that wrapper); bulk otherwise
 * (every data-wp-poster wrapper). Idempotent no-op when nothing matches.
 */
function wp_poster_connector_remove_classic( $post, $container_id ) {
	$post_id = (int) $post->ID;
	$content = (string) $post->post_content;
	$count   = 0;
	$new     = preg_replace( wp_poster_connector_wrapper_regex( $container_id ), '', $content, -1, $count );

	if ( null === $new ) {
		return array(
			'ok'      => false,
			'removed' => 0,
			'method'  => 'classic_post_content_remove',
			'storage' => 'post_content',
			'error'   => 'preg_replace error (code ' . preg_last_error() . ').',
		);
	}

	if ( $count > 0 ) {
		$res = wp_poster_connector_write_without_kses(
			function () use ( $post_id, $new ) {
				return wp_update_post( array( 'ID' => $post_id, 'post_content' => $new ), true );
			}
		);
		if ( is_wp_error( $res ) ) {
			return array(
				'ok'      => false,
				'removed' => 0,
				'method'  => 'classic_post_content_remove',
				'storage' => 'post_content',
				'error'   => 'wp_update_post failed: ' . $res->get_error_message(),
			);
		}
	}

	return array(
		'ok'      => true,
		'removed' => $count,
		'method'  => 'classic_post_content_remove',
		'storage' => 'post_content',
		'error'   => null,
	);
}
