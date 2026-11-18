<?php
/**
 * Elementor inserter — THE critical path.
 *
 * Elementor does NOT render from post_content. It stores the page as a JSON
 * tree in the _elementor_data post_meta and renders the front end from that
 * meta via a the_content filter. So writing post_content would be saved but
 * never shown. We append a new element to the _elementor_data tree directly.
 *
 * _elementor_data is "protected" meta the REST API won't let an external client
 * write, but inside WordPress update_post_meta() writes it directly. Meta is
 * NOT kses-filtered, so (unlike Classic/Gutenberg) there's no stripping risk.
 *
 * Structure inserted — the universally-compatible section > column > widget
 * shape so it renders on every Elementor 2.x/3.x version regardless of whether
 * flexbox containers are enabled:
 *
 *   section
 *     └── column (_column_size 100)
 *           └── widget (widgetType "html", settings.html = <our marked block>)
 *
 * Slash handling (the well-known Elementor gotcha): _elementor_data is stored
 * as clean JSON in the DB. update_post_meta() runs the value through
 * wp_unslash() internally, so to store valid JSON we pre-slash with wp_slash().
 * Reading back, get_post_meta() returns the clean JSON string, decodable with
 * json_decode() directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate an Elementor element id (7 hex chars, matching Elementor's own ids).
 */
function wp_poster_connector_elementor_id() {
	try {
		return substr( bin2hex( random_bytes( 4 ) ), 0, 7 );
	} catch ( Exception $e ) {
		// CSPRNG unavailable — fall back to a non-crypto unique-ish id. Collision
		// risk is negligible for our single appended element.
		return substr( md5( uniqid( (string) mt_rand(), true ) ), 0, 7 );
	}
}

/**
 * Insert the marked block into an Elementor page's _elementor_data.
 */
function wp_poster_connector_insert_elementor( $post, $html, $container_id ) {
	$post_id = (int) $post->ID;

	// Read + decode the existing tree.
	// Decode to OBJECTS (not associative arrays). Elementor distinguishes empty
	// settings as a JSON object {} ; decoding with assoc=true turns {} into a
	// PHP array() that re-encodes to [] , silently corrupting existing elements.
	// Objects preserve the {} vs [] distinction on round-trip.
	$raw  = get_post_meta( $post_id, '_elementor_data', true );
	$data = array();
	if ( is_string( $raw ) && '' !== trim( $raw ) ) {
		$decoded = json_decode( $raw );
		if ( ! is_array( $decoded ) ) {
			// Top-level _elementor_data is always a JSON array of elements.
			return array(
				'ok'      => false,
				'method'  => 'elementor_data_append',
				'storage' => '_elementor_data',
				'error'   => 'Existing _elementor_data is not a decodable JSON array (json_decode: ' . json_last_error_msg() . ').',
			);
		}
		$data = $decoded;
	}

	// Build section > column > html-widget.
	// settings are cast to objects so empty ones encode as {} (not []), matching
	// Elementor's own format. `elements` stays a PHP array (encodes as [ ]).
	$widget = (object) array(
		'id'         => wp_poster_connector_elementor_id(),
		'elType'     => 'widget',
		'widgetType' => 'html',
		'settings'   => (object) array( 'html' => $html ),
		'elements'   => array(),
	);
	$column = (object) array(
		'id'       => wp_poster_connector_elementor_id(),
		'elType'   => 'column',
		'settings' => (object) array(
			'_column_size' => 100,
			'_inline_size' => null,
		),
		'elements' => array( $widget ),
		'isInner'  => false,
	);
	$section = (object) array(
		'id'       => wp_poster_connector_elementor_id(),
		'elType'   => 'section',
		'settings' => (object) array(),
		'elements' => array( $column ),
		'isInner'  => false,
	);

	$data[] = $section;

	// Encode + persist (slash so update_post_meta's unslash yields clean JSON).
	$json = wp_json_encode( $data );
	if ( false === $json ) {
		return array(
			'ok'      => false,
			'method'  => 'elementor_data_append',
			'storage' => '_elementor_data',
			'error'   => 'wp_json_encode failed: ' . json_last_error_msg() . '.',
		);
	}

	// Return value is intentionally not trusted as the success signal —
	// update_post_meta() returns false when the value is unchanged, and the
	// authoritative check is the DB re-read in the orchestrator.
	update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );

	// Defensive: ensure the post is flagged as Elementor-built (detection
	// already confirmed it, but a stale flag would suppress rendering).
	if ( 'builder' !== get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	}

	// Bust Elementor's generated-CSS cache so the new element renders on the
	// next public view (the HTML widget has no CSS of its own, but a stale
	// per-post CSS file can keep Elementor from regenerating markup).
	wp_poster_connector_elementor_clear_cache( $post_id );

	return array(
		'ok'      => true,
		'method'  => 'elementor_data_append',
		'storage' => '_elementor_data',
		'error'   => null,
	);
}

/**
 * Bust Elementor's generated-CSS cache for a post (best-effort).
 */
function wp_poster_connector_elementor_clear_cache( $post_id ) {
	delete_post_meta( $post_id, '_elementor_css' );
	if ( class_exists( '\\Elementor\\Plugin' )
		&& isset( \Elementor\Plugin::$instance )
		&& isset( \Elementor\Plugin::$instance->files_manager ) ) {
		try {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		} catch ( Exception $e ) {
			// Best-effort; ignore.
		}
	}
}

/**
 * Recursively prune wp-poster HTML widgets from an Elementor element tree.
 *
 * Drops every html widget whose settings.html contains $needle, and cascades:
 * a section/column/container that had children but is left empty by the removal
 * is dropped too (so we don't leave empty wrappers that render as whitespace).
 * Pre-existing empty containers are preserved. $count is a by-ref tally.
 */
function wp_poster_connector_elementor_prune( $elements, $needle, &$count ) {
	$kept = array();
	foreach ( $elements as $el ) {
		if ( ! is_object( $el ) ) {
			$kept[] = $el;
			continue;
		}

		$el_type = isset( $el->elType ) ? $el->elType : '';

		// Match: an HTML widget whose html setting contains the marker.
		if ( 'widget' === $el_type && isset( $el->widgetType ) && 'html' === $el->widgetType ) {
			$html = ( isset( $el->settings ) && is_object( $el->settings ) && isset( $el->settings->html ) )
				? (string) $el->settings->html
				: '';
			if ( '' !== $html && false !== strpos( $html, $needle ) ) {
				++$count;
				continue; // drop this widget
			}
		}

		// Recurse into children, then cascade-prune a container we emptied.
		if ( isset( $el->elements ) && is_array( $el->elements ) ) {
			$had_children = count( $el->elements ) > 0;
			$el->elements = wp_poster_connector_elementor_prune( $el->elements, $needle, $count );
			if ( $had_children && 0 === count( $el->elements )
				&& in_array( $el_type, array( 'section', 'column', 'container' ), true ) ) {
				continue; // drop the now-empty wrapper we created
			}
		}

		$kept[] = $el;
	}
	return $kept;
}

/**
 * Remove wp-poster widget(s) from an Elementor page's _elementor_data.
 *
 * Surgical when $container_id is given; bulk otherwise. No-op when nothing matches.
 */
function wp_poster_connector_remove_elementor( $post, $container_id ) {
	$post_id = (int) $post->ID;
	$raw     = get_post_meta( $post_id, '_elementor_data', true );

	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return array(
			'ok'      => true,
			'removed' => 0,
			'method'  => 'elementor_data_remove',
			'storage' => '_elementor_data',
			'error'   => null,
		);
	}

	$data = json_decode( $raw );
	if ( ! is_array( $data ) ) {
		return array(
			'ok'      => false,
			'removed' => 0,
			'method'  => 'elementor_data_remove',
			'storage' => '_elementor_data',
			'error'   => 'Existing _elementor_data is not a decodable JSON array (json_decode: ' . json_last_error_msg() . ').',
		);
	}

	$needle = wp_poster_connector_marker_needle( $container_id );
	$count  = 0;
	$data   = wp_poster_connector_elementor_prune( $data, $needle, $count );

	if ( $count > 0 ) {
		$json = wp_json_encode( $data );
		if ( false === $json ) {
			return array(
				'ok'      => false,
				'removed' => 0,
				'method'  => 'elementor_data_remove',
				'storage' => '_elementor_data',
				'error'   => 'wp_json_encode failed: ' . json_last_error_msg() . '.',
			);
		}
		update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );
		wp_poster_connector_elementor_clear_cache( $post_id );
	}

	return array(
		'ok'      => true,
		'removed' => $count,
		'method'  => 'elementor_data_remove',
		'storage' => '_elementor_data',
		'error'   => null,
	);
}
