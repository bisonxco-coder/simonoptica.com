<?php
/**
 * Storage verification for inserted content.
 *
 * DB re-read — re-read the storage field (post_content or _elementor_data) and
 * confirm the marker landed. Proves the write happened (and, for post_content,
 * that the kses bypass worked). This is the "saved" signal, and the ONLY signal
 * we rely on: success means the block is in the database. We deliberately do NOT
 * fetch the public page — that loopback render check was removed; a block that is
 * saved is treated as done.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Re-read the storage field and check the marker is present.
 *
 * Storage values:
 *   'post_content'           literal substring search of post_content.
 *   '_elementor_data'        literal substring search of the meta JSON.
 *   'post_content_wpbakery'  post_content, but the block is base64-inside
 *                            [vc_raw_html], so the marker is NOT literal — we
 *                            decode each vc_raw_html body and scan.
 *
 * @param int    $post_id Target post.
 * @param string $storage Storage kind (see above).
 * @param string $needle  Substring proving our block is present.
 * @return array { marker_found:bool, storage:string, stored_length:int }
 */
function wp_poster_connector_verify_db( $post_id, $storage, $needle ) {
	if ( '_elementor_data' === $storage ) {
		$stored = (string) get_post_meta( $post_id, '_elementor_data', true );
		$found  = ( '' !== $stored && false !== strpos( $stored, $needle ) );
	} elseif ( 'post_content_wpbakery' === $storage ) {
		$post   = get_post( $post_id );
		$stored = $post ? (string) $post->post_content : '';
		// container_id lives inside base64 — decode-and-scan, not strpos.
		$found  = ( '' !== $stored && function_exists( 'wp_poster_connector_wpbakery_contains' ) && wp_poster_connector_wpbakery_contains( $stored, $needle ) );
	} else {
		$post   = get_post( $post_id );
		$stored = $post ? (string) $post->post_content : '';
		$found  = ( '' !== $stored && false !== strpos( $stored, $needle ) );
	}

	return array(
		'marker_found'  => $found,
		'storage'       => $storage,
		'stored_length' => strlen( $stored ),
	);
}
