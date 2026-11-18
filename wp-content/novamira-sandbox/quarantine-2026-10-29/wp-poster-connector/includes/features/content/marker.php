<?php
/**
 * Marker helpers for the WP Poster Connector content feature.
 *
 * The poster SERVICE builds the full off-screen link block and embeds the
 * canonical marker itself (attribute name = WP_POSTER_CONNECTOR_MARKER_ATTR):
 *
 *   <div data-wp-poster="<container_id>" style="position: absolute; left: -48592px;">...</div>
 *
 * The connector treats that HTML as opaque/ready-to-insert. These helpers only
 * (a) recover the container_id so we can echo it back + drive verification, and
 * (b) provide the substring/regex used to locate the block in storage and on the
 * rendered page. The attribute name lives in ONE place — the
 * WP_POSTER_CONNECTOR_MARKER_ATTR constant (bootstrap) — so the plugin has a
 * single source of truth for it (kept in sync with the service as a wire contract).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recover the container_id from pre-built marked HTML.
 *
 * @param string $html The service-built block HTML.
 * @return string|null The container_id, or null if the marker is absent.
 */
function wp_poster_connector_extract_container_id( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return null;
	}
	$attr = preg_quote( WP_POSTER_CONNECTOR_MARKER_ATTR, '/' );
	if ( preg_match( '/' . $attr . '\s*=\s*"([^"]+)"/i', $html, $m ) ) {
		return $m[1];
	}
	return null;
}

/**
 * The substring used to locate our block in storage / rendered HTML.
 *
 * Prefer the bare container_id (a uuid4 hex — unique and identical whether it
 * sits in raw post_content, in JSON-escaped _elementor_data, or in rendered
 * front-end HTML). Falls back to the bare marker attribute name when no
 * container_id was supplied (bulk operations).
 *
 * @param string|null $container_id The recovered container_id, or null.
 * @return string Substring to search for.
 */
function wp_poster_connector_marker_needle( $container_id ) {
	return ( is_string( $container_id ) && '' !== $container_id ) ? $container_id : WP_POSTER_CONNECTOR_MARKER_ATTR;
}

/**
 * Compiled-pattern (PCRE string) matching wp-poster wrapper divs in raw HTML.
 *
 * Surgical when $container_id is given — matches only the wrapper carrying that
 * exact id. Bulk when omitted — matches every marker wrapper.
 *
 * The lazy body ([\s\S]*?) stops at the first </div>, so this is correct only
 * while the wrapper has NO nested <div> (the service builder emits <p><a>…</a></p>
 * blocks — no inner div — so the invariant holds).
 *
 * @param string|null $container_id Surgical id, or null/empty for bulk.
 * @return string PCRE pattern (with delimiters + /i).
 */
function wp_poster_connector_wrapper_regex( $container_id ) {
	$attr = preg_quote( WP_POSTER_CONNECTOR_MARKER_ATTR, '/' );
	if ( is_string( $container_id ) && '' !== $container_id ) {
		return '/<div[^>]*' . $attr . '\s*=\s*"' . preg_quote( $container_id, '/' ) . '"[^>]*>[\s\S]*?<\/div>/i';
	}
	return '/<div[^>]*' . $attr . '[^>]*>[\s\S]*?<\/div>/i';
}
