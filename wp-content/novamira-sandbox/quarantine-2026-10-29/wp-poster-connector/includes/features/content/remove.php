<?php
/**
 * content.remove orchestrator.
 *
 * Deletes wp-poster link block(s) from a page, builder-aware, then confirms the
 * marker is gone by re-reading the database (DB-only; no public-page fetch).
 *
 * Two modes, chosen by whether container_id is supplied:
 *   - surgical: container_id given -> remove ONLY that wrapper.
 *   - bulk:     container_id omitted -> remove EVERY data-wp-poster wrapper.
 * Idempotent: nothing matching -> removed_count=0, still a success (the target
 * being absent is the goal).
 *
 * Reply (the standard flat shape — see wp_poster_connector_result):
 *   { content_type:"link", page_id, container_id, removed_count, detected_builder,
 *     step, code, error }
 * `error` is null on success; on failure `code`/`error` are set (and `step` names
 * the stage that failed), which the dispatch envelope reports as ok:false. This
 * action NEVER returns a WP_Error — every outcome is the flat result.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the standard content.remove result.
 *
 * @param int|null    $page_id          Target page id (null if unresolved).
 * @param string      $container_id     Requested surgical id ('' for bulk).
 * @param int         $removed_count    Wrappers/widgets removed.
 * @param string|null $detected_builder Builder detected for the page.
 * @param string|null $step             Failed step ('detect_builder'|'remove'|'db_persist'), or null on success.
 * @param string|null $code             Failure code, or null on success.
 * @param string|null $error            Failure message, or null on success.
 * @return array
 */
function wp_poster_connector_content_remove_result( $page_id, $container_id, $removed_count, $detected_builder, $step, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'content_type'     => 'link',
			'page_id'          => ( null !== $page_id ) ? (int) $page_id : null,
			'container_id'     => ( is_string( $container_id ) && '' !== $container_id ) ? $container_id : null,
			'removed_count'    => (int) $removed_count,
			'detected_builder' => $detected_builder,
			'step'             => $step,
		),
		$code,
		$error
	);
}

/**
 * content.remove — delete wp-poster link block(s) from a page, builder-aware.
 *
 * params: {
 *   page_id,             // (int) target page id (legacy alias: id)
 *   container_id?,       // (string) surgical: remove only this block. Omit for bulk.
 *   builder?             // (string) optional: force a builder path (testing); else auto-detected
 * }
 *
 * @param array $params Action parameters.
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_content_remove( $params ) {
	$post_id      = isset( $params['page_id'] ) ? (int) $params['page_id'] : ( isset( $params['id'] ) ? (int) $params['id'] : 0 );
	$container_id = isset( $params['container_id'] ) ? trim( (string) $params['container_id'] ) : '';

	if ( $post_id <= 0 ) {
		return wp_poster_connector_content_remove_result( null, $container_id, 0, null, null, 'wp_poster_bad_id', 'Provide a positive integer "page_id".' );
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return wp_poster_connector_content_remove_result( $post_id, $container_id, 0, null, null, 'wp_poster_post_not_found', sprintf( 'No post found with id %d.', $post_id ) );
	}

	$needle    = wp_poster_connector_marker_needle( '' !== $container_id ? $container_id : null );
	$detected  = wp_poster_connector_detect_builder( $post_id );
	$requested = isset( $params['builder'] ) ? sanitize_key( (string) $params['builder'] ) : '';
	$used      = ( '' !== $requested ) ? $requested : (string) $detected['builder'];

	if ( ! in_array( $used, wp_poster_connector_content_supported_builders(), true ) ) {
		return wp_poster_connector_content_remove_result(
			$post_id,
			$container_id,
			0,
			$detected['builder'],
			'detect_builder',
			'wp_poster_unsupported_builder',
			sprintf(
				'Builder "%s" is not supported by content.remove (supported: %s).',
				$used ? $used : 'unknown',
				implode( ', ', wp_poster_connector_content_supported_builders() )
			)
		);
	}

	// Dispatch to the editor remover (explicit — no silent fallback).
	switch ( $used ) {
		case 'elementor':
			$rem = wp_poster_connector_remove_elementor( $post, $container_id );
			break;
		case 'gutenberg':
			$rem = wp_poster_connector_remove_gutenberg( $post, $container_id );
			break;
		case 'classic':
			$rem = wp_poster_connector_remove_classic( $post, $container_id );
			break;
		case 'oxygen':
			$rem = wp_poster_connector_remove_oxygen( $post, $container_id );
			break;
		case 'divi':
			$rem = wp_poster_connector_remove_divi( $post, $container_id );
			break;
		case 'wpbakery':
			$rem = wp_poster_connector_remove_wpbakery( $post, $container_id );
			break;
		default:
			$rem = array( 'ok' => false, 'removed' => 0, 'method' => 'none', 'storage' => 'post_content', 'error' => 'No remover for builder "' . $used . '".' );
			break;
	}

	if ( empty( $rem['ok'] ) ) {
		return wp_poster_connector_content_remove_result(
			$post_id,
			$container_id,
			0,
			$detected['builder'],
			'remove',
			'wp_poster_remove_failed',
			'Remove failed: ' . ( $rem['error'] ? $rem['error'] : 'unknown error' )
		);
	}

	$removed_count = (int) $rem['removed'];

	// Confirm removal by re-reading the database (marker should now be ABSENT).
	clean_post_cache( $post_id );
	$db      = wp_poster_connector_verify_db( $post_id, $rem['storage'], $needle );
	$cleared = ! $db['marker_found'];

	if ( ! $cleared ) {
		return wp_poster_connector_content_remove_result(
			$post_id,
			$container_id,
			$removed_count,
			$detected['builder'],
			'db_persist',
			'wp_poster_not_cleared',
			sprintf( 'Marker still present in %s after removal — the write did not persist.', $db['storage'] )
		);
	}

	// Success — marker cleared from storage. error stays null => ok:true.
	return wp_poster_connector_content_remove_result( $post_id, $container_id, $removed_count, $detected['builder'], null, null, null );
}
