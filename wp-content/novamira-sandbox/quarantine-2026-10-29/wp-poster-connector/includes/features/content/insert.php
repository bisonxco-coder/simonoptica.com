<?php
/**
 * content.insert orchestrator.
 *
 * Validates the request, resolves the builder, dispatches to the matching
 * editor inserter (includes/features/content/editors/*.php), then confirms the
 * write by re-reading the database. Success means the block is SAVED in storage
 * — we rely on the DB only and no longer fetch the public page to confirm it
 * rendered. Everything is recorded as an ordered `steps` trace so a caller can
 * see exactly where a run succeeded or failed.
 *
 * Result-vs-envelope contract: input-validation problems (bad id, empty html,
 * post not found) return a WP_Error (the dispatcher maps it to ok:false with an
 * HTTP status). Once the insert pipeline starts, we ALWAYS return the full
 * result array carrying `success` + the step trace — so the trace is never lost
 * to an exception-style error. Read result.success / result.saved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builders content.insert currently supports.
 *
 * @return string[]
 */
function wp_poster_connector_content_supported_builders() {
	return array( 'elementor', 'gutenberg', 'classic', 'oxygen', 'divi', 'wpbakery' );
}

/**
 * Append a block to post_content with kses bypassed.
 *
 * A signed request has NO logged-in user, so current_user_can('unfiltered_html')
 * is false and wp_update_post() would run our content through wp_kses_post(),
 * stripping the data-wp-poster attribute and the inline off-screen style. The
 * HTML is trusted (built and signed by our own service), so the write goes
 * through wp_poster_connector_write_without_kses() (see includes/helpers.php),
 * which removes the kses save filters around it and restores them in a finally.
 *
 * @param int    $post_id Target post.
 * @param string $current Current post_content.
 * @param string $block   Block to append (already builder-wrapped if needed).
 * @return array { ok:bool, error:string|null }
 */
function wp_poster_connector_append_post_content( $post_id, $current, $block ) {
	if ( '' === $current ) {
		$new = $block;
	} else {
		$sep = ( "\n" === substr( $current, -1 ) ) ? '' : "\n\n";
		$new = $current . $sep . $block;
	}

	$res = wp_poster_connector_write_without_kses(
		function () use ( $post_id, $new ) {
			return wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => $new,
				),
				true
			);
		}
	);

	if ( is_wp_error( $res ) ) {
		return array(
			'ok'    => false,
			'error' => 'wp_update_post failed: ' . $res->get_error_message(),
		);
	}
	return array(
		'ok'    => true,
		'error' => null,
	);
}

/**
 * Assemble the uniform content.insert result.
 *
 * Success = the block is saved in storage (DB re-read confirmed). There is no
 * public-render tier anymore — we rely on the database only.
 *
 * @param WP_Post     $post             Target post.
 * @param string|null $container_id     Recovered container_id.
 * @param string      $used_builder     Builder actually used.
 * @param string|null $detected_builder Builder detected from meta.
 * @param bool        $saved            DB re-read confirmed storage (= success).
 * @param array       $steps            Ordered step trace.
 * @return array
 */
function wp_poster_connector_content_result( $post, $container_id, $used_builder, $detected_builder, $saved, $steps ) {
	return array(
		'success'          => (bool) $saved,
		'saved'            => (bool) $saved,
		'builder'          => $used_builder,
		'detected_builder' => $detected_builder,
		'container_id'     => $container_id,
		'post_id'          => (int) $post->ID,
		'permalink'        => get_permalink( $post->ID ),
		'steps'            => $steps,
	);
}

/**
 * content.insert — embed a ready-to-post link block into a page, builder-aware.
 *
 * params: {
 *   page_id,             // (int) target post/page id
 *   html,                // (string) full service-built marked block HTML
 *   container_id?,       // (string) block id; authoritative when given, else
 *                        //          recovered from the html marker (must match)
 *   builder?             // (string) optional: force a builder path (testing);
 *                        //          else auto-detected from post meta
 * }
 *
 * @param array $params Action parameters.
 * @return array|WP_Error
 */
function wp_poster_connector_content_insert( $params ) {
	// Prefer "page_id"; accept legacy "id" as a fallback so older callers keep working.
	$post_id = isset( $params['page_id'] ) ? (int) $params['page_id'] : ( isset( $params['id'] ) ? (int) $params['id'] : 0 );
	$html    = isset( $params['html'] ) ? (string) $params['html'] : '';

	if ( $post_id <= 0 ) {
		return new WP_Error( 'wp_poster_bad_id', 'Provide a positive integer "page_id".', array( 'status' => 400 ) );
	}
	if ( '' === trim( $html ) ) {
		return new WP_Error( 'wp_poster_empty', 'Provide non-empty "html" (the marked link block).', array( 'status' => 400 ) );
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return new WP_Error( 'wp_poster_post_not_found', sprintf( 'No post found with id %d.', $post_id ), array( 'status' => 404 ) );
	}

	// container_id: an explicit param is authoritative — the service generates the
	// id, records it in its own DB, and sends it here. Fall back to the id baked
	// into the service-built marker for older callers. Unlike header.insert /
	// footer.insert (whose render hook wraps the block in a <!-- wpp:CID --> marker at output
	// time), the content marker data-wp-poster="CID" must physically live in the
	// stored html, or neither verification nor a later surgical content.remove can
	// find it — so the resolved id MUST be present in the html.
	$param_cid    = isset( $params['container_id'] ) ? trim( (string) $params['container_id'] ) : '';
	$html_cid     = wp_poster_connector_extract_container_id( $html ); // string|null
	$container_id = ( '' !== $param_cid ) ? $param_cid : $html_cid;

	if ( null === $container_id || '' === $container_id ) {
		return new WP_Error(
			'wp_poster_no_container_id',
			'Provide a "container_id" (the service generates it, sends it, and stores it). A container cannot be created without an id.',
			array( 'status' => 400 )
		);
	}
	if ( $container_id !== $html_cid ) {
		return new WP_Error(
			'wp_poster_container_id_mismatch',
			sprintf(
				'container_id "%s" is not present in the html — the block must carry data-wp-poster="%s" (found: %s).',
				$container_id,
				$container_id,
				( null === $html_cid || '' === $html_cid ) ? 'no data-wp-poster marker' : '"' . $html_cid . '"'
			),
			array( 'status' => 400 )
		);
	}

	$needle = wp_poster_connector_marker_needle( $container_id );

	$steps = array();
	$add   = function ( $step, $status, $detail, $extra = array() ) use ( &$steps ) {
		$steps[] = array_merge( array(
			'step'   => $step,
			'status' => $status,
			'detail' => $detail,
		), $extra );
	};

	$add(
		'validate',
		'ok',
		sprintf( 'post_id=%d, html_length=%d, container_id=%s', $post_id, strlen( $html ), $container_id )
	);

	// Resolve builder.
	$detected  = wp_poster_connector_detect_builder( $post_id );
	$requested = isset( $params['builder'] ) ? sanitize_key( (string) $params['builder'] ) : '';
	$used      = ( '' !== $requested ) ? $requested : (string) $detected['builder'];
	$mismatch  = ( '' !== $requested && $requested !== $detected['builder'] );

	$add(
		'detect_builder',
		'ok',
		sprintf(
			'detected=%s (%s); requested=%s; using=%s%s',
			$detected['builder'] ? $detected['builder'] : 'null',
			$detected['source'],
			'' !== $requested ? $requested : '(none)',
			$used,
			$mismatch ? ' [MISMATCH — using requested]' : ''
		),
		array(
			'detected_builder' => $detected['builder'],
			'used_builder'     => $used,
		)
	);

	if ( ! in_array( $used, wp_poster_connector_content_supported_builders(), true ) ) {
		$add(
			'insert',
			'error',
			sprintf(
				'Builder "%s" is not supported by content.insert yet (supported: %s).',
				$used ? $used : 'unknown',
				implode( ', ', wp_poster_connector_content_supported_builders() )
			)
		);
		return wp_poster_connector_content_result( $post, $container_id, $used, $detected['builder'], false, $steps );
	}

	// Dispatch to the editor inserter (explicit — no silent fallback).
	switch ( $used ) {
		case 'elementor':
			$ins = wp_poster_connector_insert_elementor( $post, $html, $container_id );
			break;
		case 'gutenberg':
			$ins = wp_poster_connector_insert_gutenberg( $post, $html, $container_id );
			break;
		case 'classic':
			$ins = wp_poster_connector_insert_classic( $post, $html, $container_id );
			break;
		case 'oxygen':
			$ins = wp_poster_connector_insert_oxygen( $post, $html, $container_id );
			break;
		case 'divi':
			$ins = wp_poster_connector_insert_divi( $post, $html, $container_id );
			break;
		case 'wpbakery':
			$ins = wp_poster_connector_insert_wpbakery( $post, $html, $container_id );
			break;
		default:
			// Unreachable (the supported-builders gate above filters $used), but
			// fail loud rather than silently mis-routing if that ever changes.
			$ins = array( 'ok' => false, 'method' => 'none', 'storage' => 'post_content', 'error' => 'No inserter for builder "' . $used . '".' );
			break;
	}

	if ( empty( $ins['ok'] ) ) {
		$add(
			'insert',
			'error',
			'Insert failed: ' . ( $ins['error'] ? $ins['error'] : 'unknown error' ),
			array( 'method' => $ins['method'], 'storage' => $ins['storage'] )
		);
		return wp_poster_connector_content_result( $post, $container_id, $used, $detected['builder'], false, $steps );
	}

	$add(
		'insert',
		'ok',
		'Wrote block via ' . $ins['method'] . '.',
		array( 'method' => $ins['method'], 'storage' => $ins['storage'] )
	);

	// Confirm the write by re-reading the database. This is the success signal —
	// it proves the block persisted (and, for post_content, that the kses bypass
	// worked). We rely on the DB only; no public-page fetch.
	clean_post_cache( $post_id );
	$db    = wp_poster_connector_verify_db( $post_id, $ins['storage'], $needle );
	$saved = (bool) $db['marker_found'];

	$add(
		'db_persist',
		$saved ? 'ok' : 'error',
		$saved
			? sprintf( 'Marker present in %s (stored_length=%d).', $db['storage'], $db['stored_length'] )
			: sprintf( 'Marker NOT found in %s after a successful write (stored_length=%d) — content was stripped (kses?) or the write did not persist.', $db['storage'], $db['stored_length'] ),
		array( 'marker_found' => $saved, 'storage' => $db['storage'] )
	);

	return wp_poster_connector_content_result( $post, $container_id, $used, $detected['builder'], $saved, $steps );
}
