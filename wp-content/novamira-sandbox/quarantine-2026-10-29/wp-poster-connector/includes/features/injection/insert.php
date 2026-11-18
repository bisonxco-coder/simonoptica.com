<?php
/**
 * header.insert / footer.insert orchestrator.
 *
 * Stores a block in the head/footer injections option, then confirms it by
 * re-reading the option (DB-only; no public-page fetch).  The target location
 * is fixed by the action (header.insert -> head, footer.insert -> footer), so
 * there is no "location" param.
 *
 * Reply (standard flat shape): { content_type:"head"|"footer", container_id,
 * step, code, error }. `content_type` is the target location. `error` is null on
 * success; on failure `code`/`error` are set (and `step` names the stage that
 * failed), which the dispatch envelope reports as ok:false. NEVER a WP_Error.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the standard header.insert / footer.insert result.
 *
 * @param string|null $location     Target location ("head"|"footer"), or null if unresolved.
 * @param string      $container_id Block id ('' when not resolved).
 * @param string|null $step         Failed step ('store'|'verify_db'), or null on success/validation.
 * @param string|null $code         Failure code, or null on success.
 * @param string|null $error        Failure message, or null on success.
 * @return array
 */
function wp_poster_connector_headfoot_result( $location, $container_id, $step, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'content_type' => ( is_string( $location ) && '' !== $location ) ? $location : null,
			'container_id' => ( is_string( $container_id ) && '' !== $container_id ) ? $container_id : null,
			'step'         => $step,
		),
		$code,
		$error
	);
}

/**
 * header.insert / footer.insert — store a block for site-wide head/footer injection.
 *
 * The target location is set by the action (header.insert -> 'head',
 * footer.insert -> 'footer'); callers no longer pass a "location" param.
 *
 * params: {
 *   html,                // (string) raw HTML/JS/CSS to inject
 *   container_id?        // (string) optional explicit id; else recovered from the html marker
 * }
 *
 * @param string $location Target location ('head'|'footer'), set by the action.
 * @param array  $params   Action parameters.
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_headfoot_insert( $location, $params ) {
	$html = isset( $params['html'] ) ? (string) $params['html'] : '';

	// Location is chosen by the action, not the caller; guard defensively in
	// case the dispatch table is ever wired to an unknown location.
	if ( ! wp_poster_connector_injection_is_location( $location ) ) {
		return wp_poster_connector_headfoot_result(
			null,
			'',
			null,
			'wp_poster_bad_location',
			'Internal: unknown injection location "' . $location . '".'
		);
	}
	if ( '' === trim( $html ) ) {
		return wp_poster_connector_headfoot_result( $location, '', null, 'wp_poster_empty', 'Provide non-empty "html".' );
	}

	// container_id: explicit param wins; else recover from the html marker.
	$container_id = isset( $params['container_id'] ) ? trim( (string) $params['container_id'] ) : '';
	if ( '' === $container_id ) {
		$container_id = (string) wp_poster_connector_extract_container_id( $html );
	}
	if ( '' === $container_id ) {
		return wp_poster_connector_headfoot_result(
			$location,
			'',
			null,
			'wp_poster_no_container_id',
			'Provide a "container_id" or embed a data-wp-poster marker in the html.'
		);
	}

	// Store.
	$ok = wp_poster_connector_injections_upsert( $location, $container_id, $html );
	if ( ! $ok ) {
		return wp_poster_connector_headfoot_result( $location, $container_id, 'store', 'wp_poster_store_failed', 'update_option() failed to persist the injection.' );
	}

	// Confirm by re-reading the option — the success signal (DB only, no fetch).
	$bucket = wp_poster_connector_injections_get_location( $location );
	$saved  = isset( $bucket[ $container_id ] ) && $bucket[ $container_id ] === $html;
	if ( ! $saved ) {
		return wp_poster_connector_headfoot_result( $location, $container_id, 'verify_db', 'wp_poster_not_saved', 'Option re-read did not find the stored block.' );
	}

	// Success.
	return wp_poster_connector_headfoot_result( $location, $container_id, null, null, null );
}
