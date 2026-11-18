<?php
/**
 * WPBakery (Visual Composer) inserter/remover.
 *
 * WPBakery stores [vc_*] shortcodes in post_content and renders them via
 * the_content. We append a [vc_raw_html] shortcode whose body is the marked
 * block, encoded the way WPBakery expects: base64_encode(rawurlencode(html)).
 * WPBakery's vc_raw_html handler does the inverse (rawurldecode(base64_decode))
 * and prints the HTML raw, so our off-screen block + container_id render on the
 * front end.
 *
 * Encoding consequence for verification: because the body is base64, the
 * container_id is NOT a literal substring of post_content — DB verification
 * must decode each [vc_raw_html] body and scan it. That's why this editor
 * reports storage 'post_content_wpbakery' (see verify.php) and exposes
 * wp_poster_connector_wpbakery_contains().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Encode HTML for a [vc_raw_html] body the way WPBakery expects:
 * base64(rawurlencode(html)).
 */
function wp_poster_connector_wpbakery_encode( $html ) {
	return base64_encode( rawurlencode( $html ) );
}

/**
 * Does any [vc_raw_html] body in $content decode to contain $needle?
 *
 * Tolerant of encoding variants across WPBakery versions: checks the
 * rawurldecode(base64) form, the bare base64-decoded form, and the literal
 * body — so a match is found whether the body was rawurlencode+base64,
 * plain base64, or (defensively) unencoded.
 */
function wp_poster_connector_wpbakery_contains( $content, $needle ) {
	if ( ! preg_match_all( '/\[vc_raw_html\b[^\]]*\](.*?)\[\/vc_raw_html\]/is', (string) $content, $matches ) ) {
		return false;
	}
	foreach ( $matches[1] as $body ) {
		$body    = trim( $body );
		$decoded = base64_decode( $body, true );
		if ( false !== $decoded ) {
			if ( false !== strpos( rawurldecode( $decoded ), $needle ) || false !== strpos( $decoded, $needle ) ) {
				return true;
			}
		}
		if ( false !== strpos( $body, $needle ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Insert the marked block into a WPBakery page's post_content.
 */
function wp_poster_connector_insert_wpbakery( $post, $html, $container_id ) {
	$block = '[vc_raw_html]' . wp_poster_connector_wpbakery_encode( $html ) . '[/vc_raw_html]';
	$res   = wp_poster_connector_append_post_content( (int) $post->ID, (string) $post->post_content, $block );
	return array(
		'ok'      => $res['ok'],
		'method'  => 'wpbakery_vc_raw_html_append',
		'storage' => 'post_content_wpbakery',
		'error'   => $res['error'],
	);
}

/**
 * Remove wp-poster [vc_raw_html] shortcode(s) from a WPBakery page.
 *
 * Decodes each [vc_raw_html] body; removes the whole shortcode when its decoded
 * body contains the marker. Surgical (container_id) or bulk (any wp-poster).
 */
function wp_poster_connector_remove_wpbakery( $post, $container_id ) {
	$post_id = (int) $post->ID;
	$content = (string) $post->post_content;
	$needle  = wp_poster_connector_marker_needle( $container_id );

	$count = 0;
	$new   = preg_replace_callback(
		'/\[vc_raw_html\b[^\]]*\](.*?)\[\/vc_raw_html\]/is',
		function ( $m ) use ( $needle, &$count ) {
			$body    = trim( $m[1] );
			$decoded = base64_decode( $body, true );
			$hit     = false;
			if ( false !== $decoded ) {
				$hit = ( false !== strpos( rawurldecode( $decoded ), $needle ) || false !== strpos( $decoded, $needle ) );
			}
			if ( ! $hit && false !== strpos( $body, $needle ) ) {
				$hit = true;
			}
			if ( $hit ) {
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
			'method'  => 'wpbakery_vc_raw_html_remove',
			'storage' => 'post_content_wpbakery',
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
				'method'  => 'wpbakery_vc_raw_html_remove',
				'storage' => 'post_content_wpbakery',
				'error'   => 'wp_update_post failed: ' . $res->get_error_message(),
			);
		}
	}

	return array(
		'ok'      => true,
		'removed' => $count,
		'method'  => 'wpbakery_vc_raw_html_remove',
		'storage' => 'post_content_wpbakery',
		'error'   => null,
	);
}
