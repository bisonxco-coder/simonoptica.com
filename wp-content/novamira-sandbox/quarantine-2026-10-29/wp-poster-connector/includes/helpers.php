<?php
/**
 * Shared cross-cutting utilities for the WP Poster Connector (loaded first).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run a post-saving write with WordPress's kses HTML sanitisation disabled.
 *
 * Our dispatch-endpoint requests have NO logged-in user, so unfiltered_html is false and
 * WordPress runs post_content through wp_kses_post() on save (the content_save_pre
 * filter from kses_init_filters()), silently stripping data-* attributes, inline
 * style, <iframe>, etc. The HTML we write is service-built and trusted, so this
 * removes the kses save filters for the duration of $write and ALWAYS restores them
 * (try/finally) so a failed write can't leave sanitisation disabled. Both
 * posts.create and content.insert route their writes through here.
 *
 * @param callable $write Performs the wp_insert_post()/wp_update_post() call.
 * @return mixed Whatever $write returns.
 */
function wp_poster_connector_write_without_kses( callable $write ) {
	kses_remove_filters();
	try {
		return $write();
	} finally {
		kses_init_filters();
	}
}

/**
 * Build a standard action result (the single source of truth for the reply shape).
 *
 * Every action returns its inner `result` through here so the contract is
 * uniform: the caller branches on `error` — null means success; a stable `code`
 * plus a human-readable `error` describe a failure. The dispatch envelope reads
 * this `error` to set `ok` (a non-null error => ok:false, still HTTP 200). There
 * is deliberately NO `success` field — `error === null` IS success.
 *
 * Action-specific fields (and, for the injection family, `content_type` + `step`)
 * are passed in $fields; this guarantees `code` + `error` are always present.
 *
 * @param array       $fields Action-specific result fields.
 * @param string|null $code   Stable machine-readable error code, or null on success.
 * @param string|null $error  Human-readable explanation, or null on success.
 * @return array
 */
function wp_poster_connector_result( $fields, $code = null, $error = null ) {
	$fields['code']  = $code;
	$fields['error'] = $error;
	return $fields;
}
