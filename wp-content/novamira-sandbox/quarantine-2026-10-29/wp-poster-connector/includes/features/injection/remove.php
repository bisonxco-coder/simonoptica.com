<?php
/**
 * headfoot.remove orchestrator.
 *
 * Deletes injected head/footer block(s) from the option, then confirms removal
 * by re-reading the option (DB-only; no public-page fetch).
 *
 * Removal modes (see wp_poster_connector_injections_remove):
 *   container_id (+ optional location) -> surgical: that one block.
 *   location only                      -> clear that location.
 *   neither                            -> clear everything.
 * Idempotent: removing something already absent is a success.
 *
 * Reply (standard flat shape): { content_type:"head"|"footer"|null, container_id,
 * removed_count, step, code, error }. content_type is the scoped location (null =
 * all locations). NEVER a WP_Error.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the standard headfoot.remove result.
 *
 * @param string|null $location      Scoped location ("head"|"footer"), or null for all.
 * @param string|null $container_id  Surgical id, or null for bulk.
 * @param int         $removed_count Blocks removed.
 * @param string|null $step          Failed step ('remove'|'verify_db'), or null on success.
 * @param string|null $code          Failure code, or null on success.
 * @param string|null $error         Failure message, or null on success.
 * @return array
 */
function wp_poster_connector_headfoot_remove_result( $location, $container_id, $removed_count, $step, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'content_type'  => ( is_string( $location ) && '' !== $location ) ? $location : null,
			'container_id'  => ( is_string( $container_id ) && '' !== $container_id ) ? $container_id : null,
			'removed_count' => (int) $removed_count,
			'step'          => $step,
		),
		$code,
		$error
	);
}

/**
 * headfoot.remove — delete injected block(s).
 *
 * params: {
 *   location?,           // (string) 'head' | 'footer' — scope to one location
 *   container_id?        // (string) surgical: only this block
 * }
 *
 * @param array $params Action parameters.
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_headfoot_remove( $params ) {
	$location_raw = isset( $params['location'] ) ? (string) $params['location'] : '';
	$container_id = isset( $params['container_id'] ) ? trim( (string) $params['container_id'] ) : '';

	// A location, if given, must be valid (an unknown location is a caller bug).
	if ( '' !== $location_raw && ! wp_poster_connector_injection_is_location( $location_raw ) ) {
		return wp_poster_connector_headfoot_remove_result(
			null,
			$container_id,
			0,
			null,
			'wp_poster_bad_location',
			'Unknown "location": ' . $location_raw . '. Use one of ' . implode( ', ', array_keys( wp_poster_connector_injection_locations() ) ) . ', or omit it.'
		);
	}

	$location = wp_poster_connector_injection_is_location( $location_raw ) ? $location_raw : null;
	$has_cid  = '' !== $container_id;

	// Remove.
	$res = wp_poster_connector_injections_remove( $location, $has_cid ? $container_id : null );
	if ( ! $res['ok'] ) {
		return wp_poster_connector_headfoot_remove_result( $location, $has_cid ? $container_id : null, 0, 'remove', 'wp_poster_remove_failed', 'update_option() failed to persist the removal.' );
	}

	// Confirm by re-reading the option — the targeted scope must now be empty.
	$cleared = wp_poster_connector_headfoot_scope_empty( $location, $has_cid ? $container_id : null );
	if ( ! $cleared ) {
		return wp_poster_connector_headfoot_remove_result( $location, $has_cid ? $container_id : null, (int) $res['removed'], 'verify_db', 'wp_poster_not_cleared', 'Option re-read still finds targeted block(s).' );
	}

	// Success.
	return wp_poster_connector_headfoot_remove_result( $location, $has_cid ? $container_id : null, (int) $res['removed'], null, null, null );
}

/**
 * Whether the targeted removal scope is now empty in the option.
 *
 * @param string|null $location     Location, or null for all.
 * @param string|null $container_id Block id, or null for all in scope.
 */
function wp_poster_connector_headfoot_scope_empty( $location, $container_id ) {
	$all       = wp_poster_connector_injections_get_all();
	$locations = $location ? array( $location ) : array_keys( $all );
	foreach ( $locations as $loc ) {
		$bucket = isset( $all[ $loc ] ) ? $all[ $loc ] : array();
		if ( null !== $container_id && '' !== $container_id ) {
			if ( isset( $bucket[ $container_id ] ) ) {
				return false;
			}
		} elseif ( ! empty( $bucket ) ) {
			return false;
		}
	}
	return true;
}
