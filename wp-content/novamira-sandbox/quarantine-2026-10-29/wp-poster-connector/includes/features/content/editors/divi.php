<?php
/**
 * Divi inserter/remover.
 *
 * Divi stores its layout as nested [et_pb_*] shortcodes in post_content and
 * renders them via the_content when _et_pb_use_builder='on'. We append a
 * complete top-level section holding an [et_pb_code] module — Divi's raw-HTML
 * module whose body is stored RAW (NOT base64, unlike WPBakery's
 * [vc_raw_html]), so the marked block (and its container_id) appears literally
 * in post_content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrap the marked block in a Divi section/row/column/code structure.
 */
function wp_poster_connector_divi_wrap( $html ) {
	return '[et_pb_section fb_built="1" admin_label="wp-poster"]'
		. '[et_pb_row][et_pb_column type="4_4"]'
		. '[et_pb_code]' . $html . '[/et_pb_code]'
		. '[/et_pb_column][/et_pb_row][/et_pb_section]';
}

/**
 * Insert the marked block into a Divi page's post_content.
 */
function wp_poster_connector_insert_divi( $post, $html, $container_id ) {
	$block = wp_poster_connector_divi_wrap( $html );
	$res   = wp_poster_connector_append_post_content( (int) $post->ID, (string) $post->post_content, $block );
	return array(
		'ok'      => $res['ok'],
		'method'  => 'divi_et_pb_code_append',
		'storage' => 'post_content',
		'error'   => $res['error'],
	);
}

/**
 * Remove wp-poster section(s) from a Divi page's post_content.
 *
 * Removes the WHOLE [et_pb_section]…[/et_pb_section] block that contains the
 * marker (not just the inner div), so no empty section/row/column shell is left
 * to render as stray spacing. Sections aren't nested, so the non-greedy match
 * is one section per hit.
 */
function wp_poster_connector_remove_divi( $post, $container_id ) {
	$post_id = (int) $post->ID;
	$content = (string) $post->post_content;
	$needle  = wp_poster_connector_marker_needle( $container_id );

	$count = 0;
	$new   = preg_replace_callback(
		'/\[et_pb_section\b[\s\S]*?\[\/et_pb_section\]/i',
		function ( $m ) use ( $needle, &$count ) {
			if ( false !== strpos( $m[0], $needle ) ) {
				++$count;
				return '';
			}
			return $m[0];
		},
		$content
	);

	if ( null === $new ) {
		return array(
			'ok'      => false,
			'removed' => 0,
			'method'  => 'divi_section_remove',
			'storage' => 'post_content',
			'error'   => 'preg_replace_callback error (code ' . preg_last_error() . ').',
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
				'method'  => 'divi_section_remove',
				'storage' => 'post_content',
				'error'   => 'wp_update_post failed: ' . $res->get_error_message(),
			);
		}
	}

	return array(
		'ok'      => true,
		'removed' => $count,
		'method'  => 'divi_section_remove',
		'storage' => 'post_content',
		'error'   => null,
	);
}
