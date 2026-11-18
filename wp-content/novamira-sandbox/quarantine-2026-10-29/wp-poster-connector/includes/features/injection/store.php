<?php
/**
 * State store for site-wide header/footer HTML injection.
 *
 * All state lives in a SINGLE autoloaded option:
 *
 *   option wp_poster_connector_injections = array(
 *     'head'   => array( '<container_id>' => '<html>', ... ),
 *     'footer' => array( '<container_id>' => '<html>', ... ),
 *   )
 *
 * Keyed by container_id so surgical upsert/remove is free and multiple blocks can
 * coexist. Autoload is on: the render hooks read this on every front-end request,
 * so it should ride along with the alloptions cache.
 *
 * This module owns ALL reads/writes of the option — orchestrators and render
 * hooks go through these helpers, never get_option()/update_option() directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_POSTER_CONNECTOR_INJECTIONS_OPTION' ) ) {
	define( 'WP_POSTER_CONNECTOR_INJECTIONS_OPTION', 'wp_poster_connector_injections' );
}

/**
 * The locations we inject into. Maps a location key to the hook it renders on.
 *
 * @return array<string,string> location => hook name.
 */
function wp_poster_connector_injection_locations() {
	return array(
		'head'   => 'wp_head',
		'footer' => 'wp_footer',
	);
}

/**
 * Whether a location key is valid.
 */
function wp_poster_connector_injection_is_location( $location ) {
	return is_string( $location ) && array_key_exists( $location, wp_poster_connector_injection_locations() );
}

/**
 * Read the full injections map, normalized to { head:array, footer:array }.
 *
 * @return array<string,array<string,string>>
 */
function wp_poster_connector_injections_get_all() {
	$stored = get_option( WP_POSTER_CONNECTOR_INJECTIONS_OPTION, array() );
	$out    = array();
	foreach ( wp_poster_connector_injection_locations() as $location => $hook ) {
		$bucket = ( is_array( $stored ) && isset( $stored[ $location ] ) && is_array( $stored[ $location ] ) )
			? $stored[ $location ]
			: array();
		// Keep only string=>string entries (defensive against a corrupted option).
		$clean = array();
		foreach ( $bucket as $cid => $html ) {
			if ( is_string( $cid ) && '' !== $cid && is_string( $html ) ) {
				$clean[ $cid ] = $html;
			}
		}
		$out[ $location ] = $clean;
	}
	return $out;
}

/**
 * Read a single location's bucket (container_id => html).
 *
 * @param string $location Location key.
 * @return array<string,string>
 */
function wp_poster_connector_injections_get_location( $location ) {
	$all = wp_poster_connector_injections_get_all();
	return isset( $all[ $location ] ) ? $all[ $location ] : array();
}

/**
 * Persist the full injections map. Autoloaded — read on every front-end render.
 *
 * @param array<string,array<string,string>> $all Full map.
 * @return bool True on success (or no-op when unchanged).
 */
function wp_poster_connector_injections_save( $all ) {
	// update_option() returns false when the value is unchanged; treat that as
	// success so an idempotent re-write isn't reported as a failure.
	$current = get_option( WP_POSTER_CONNECTOR_INJECTIONS_OPTION, null );
	if ( $current === $all ) {
		return true;
	}
	return (bool) update_option( WP_POSTER_CONNECTOR_INJECTIONS_OPTION, $all, true );
}

/**
 * Upsert a block at a location (overwrites any existing block with the same id).
 */
function wp_poster_connector_injections_upsert( $location, $container_id, $html ) {
	$all                          = wp_poster_connector_injections_get_all();
	$all[ $location ][ $container_id ] = $html;
	return wp_poster_connector_injections_save( $all );
}

/**
 * Remove blocks.
 *
 * - container_id + location : remove that one block in that location.
 * - container_id only       : remove that id from every location.
 * - location only           : clear that location entirely.
 * - neither                 : clear everything.
 *
 * @return array { ok:bool, removed:int }
 */
function wp_poster_connector_injections_remove( $location, $container_id ) {
	$all       = wp_poster_connector_injections_get_all();
	$has_loc   = wp_poster_connector_injection_is_location( $location );
	$has_cid   = is_string( $container_id ) && '' !== $container_id;
	$removed   = 0;
	$locations = $has_loc ? array( $location ) : array_keys( $all );

	foreach ( $locations as $loc ) {
		if ( ! isset( $all[ $loc ] ) || ! is_array( $all[ $loc ] ) ) {
			continue;
		}
		if ( $has_cid ) {
			if ( isset( $all[ $loc ][ $container_id ] ) ) {
				unset( $all[ $loc ][ $container_id ] );
				$removed++;
			}
		} else {
			$removed += count( $all[ $loc ] );
			$all[ $loc ] = array();
		}
	}

	$ok = wp_poster_connector_injections_save( $all );
	return array(
		'ok'      => $ok,
		'removed' => $removed,
	);
}
