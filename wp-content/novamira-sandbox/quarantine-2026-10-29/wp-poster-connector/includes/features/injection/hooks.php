<?php
/**
 * Front-end render hooks for header/footer injection.
 *
 * Registers wp_head / wp_footer callbacks that echo every stored block for that
 * location on every front-end page (never in wp-admin) — no builder detection,
 * no kses.
 *
 * Each block is wrapped in an HTML-comment marker carrying its container_id:
 *
 *   <!-- wpp:CID -->
 *   ...block html...
 *   <!-- /wpp:CID -->
 *
 * The comment is valid in both <head> and <body> and gives the loopback verifier
 * a stable needle (the container_id) even when the block HTML doesn't contain the
 * id. Aggressive comment-stripping minifiers can remove the marker; that degrades
 * verification to a possible false-negative, never a crash.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrap a block in its container_id comment marker. Single source of truth for
 * the rendered form, so insert.php can derive the verification needle from it.
 */
function wp_poster_connector_injection_wrap( $container_id, $html ) {
	return "\n<!-- wpp:" . $container_id . " -->\n" . $html . "\n<!-- /wpp:" . $container_id . " -->\n";
}

/**
 * The substring the loopback verifier searches for in the rendered page.
 */
function wp_poster_connector_injection_needle( $container_id ) {
	return 'wpp:' . $container_id;
}

/**
 * Echo all stored blocks for a location ('head'|'footer').
 */
function wp_poster_connector_render_injections( $location ) {
	$blocks = wp_poster_connector_injections_get_location( $location );
	foreach ( $blocks as $cid => $html ) {
		// Intentionally NOT escaped: the service sends trusted, ready-to-render
		// HTML/JS/CSS — that is the whole point of the head/footer feature.
		echo wp_poster_connector_injection_wrap( $cid, $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * wp_head callback — render the 'head' bucket.
 */
function wp_poster_connector_render_injections_head() {
	wp_poster_connector_render_injections( 'head' );
}

/**
 * wp_footer callback — render the 'footer' bucket.
 */
function wp_poster_connector_render_injections_footer() {
	wp_poster_connector_render_injections( 'footer' );
}

// Late priority so our blocks land after the theme's own head/footer output
// (analytics/verification tags conventionally go near the end of <head>).
add_action( 'wp_head', 'wp_poster_connector_render_injections_head', 100 );
add_action( 'wp_footer', 'wp_poster_connector_render_injections_footer', 100 );
