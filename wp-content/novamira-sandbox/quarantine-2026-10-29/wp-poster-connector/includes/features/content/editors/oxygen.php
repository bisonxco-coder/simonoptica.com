<?php
/**
 * Oxygen inserter/remover.
 *
 * Oxygen stores its layout in ct_builder_shortcodes / ct_builder_json post_meta,
 * but every shortcode there is HMAC-signed (ct_sign_sha256) with keys we can't
 * read, so we DO NOT touch that meta. Instead we write the marked block to the
 * standard post_content — Oxygen surfaces it only through its [ct_inner_content]
 * element. That means a page whose Oxygen template omits Inner Content will save
 * the block but never render it: the public-render verification step catches
 * exactly that case (saved=true, verified=false).
 *
 * Mechanically identical to Classic (append/strip in post_content); kept in its
 * own file per the one-editor-per-file convention.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert the marked block into an Oxygen page's post_content.
 */
function wp_poster_connector_insert_oxygen( $post, $html, $container_id ) {
	$res = wp_poster_connector_append_post_content( (int) $post->ID, (string) $post->post_content, $html );
	return array(
		'ok'      => $res['ok'],
		'method'  => 'oxygen_post_content_append',
		'storage' => 'post_content',
		'error'   => $res['error'],
	);
}

/**
 * Remove wp-poster wrapper(s) from an Oxygen page's post_content.
 *
 * Surgical when $container_id is given; bulk otherwise.
 */
function wp_poster_connector_remove_oxygen( $post, $container_id ) {
	$post_id = (int) $post->ID;
	$content = (string) $post->post_content;
	$count   = 0;
	$new     = preg_replace( wp_poster_connector_wrapper_regex( $container_id ), '', $content, -1, $count );

	if ( null === $new ) {
		return array(
			'ok'      => false,
			'removed' => 0,
			'method'  => 'oxygen_post_content_remove',
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
				'method'  => 'oxygen_post_content_remove',
				'storage' => 'post_content',
				'error'   => 'wp_update_post failed: ' . $res->get_error_message(),
			);
		}
	}

	return array(
		'ok'      => true,
		'removed' => $count,
		'method'  => 'oxygen_post_content_remove',
		'storage' => 'post_content',
		'error'   => null,
	);
}
