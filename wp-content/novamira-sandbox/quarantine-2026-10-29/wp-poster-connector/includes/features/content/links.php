<?php
/**
 * content.add orchestrator — the one-shot "add a content block to a page" workflow.
 *
 * Resolves the target page (an explicit page_id, otherwise the site's static
 * front page), runs the content.insert pipeline wholesale (no insert logic is
 * duplicated here), and returns the standard flat result.
 *
 * Reply: { content_type:"link", page_id, container_id, detected_builder,
 * final_url, step, code, error }. `error` is null on success; on failure
 * `code`/`error` are set (and `step` names the failed pipeline stage), which the
 * dispatch envelope reports as ok:false. NEVER returns a WP_Error.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the standard content.add result (success and failure share this shape).
 *
 * @param int|null    $page_id          Resolved target page id, or null.
 * @param string|null $container_id     Block id, or null.
 * @param string|null $detected_builder Builder detected for the page, or null.
 * @param string|null $final_url        Page permalink (success), or null.
 * @param string|null $step             Failed pipeline step, or null.
 * @param string|null $code             Failure code, or null on success.
 * @param string|null $error            Failure message, or null on success.
 * @return array
 */
function wp_poster_connector_links_result( $page_id, $container_id, $detected_builder, $final_url, $step, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'content_type'     => 'link',
			'page_id'          => ( null !== $page_id ) ? (int) $page_id : null,
			'container_id'     => ( is_string( $container_id ) && '' !== $container_id ) ? $container_id : null,
			'detected_builder' => ( is_string( $detected_builder ) && '' !== $detected_builder ) ? $detected_builder : null,
			'final_url'        => ( is_string( $final_url ) && '' !== $final_url ) ? $final_url : null,
			'step'             => ( is_string( $step ) && '' !== $step ) ? $step : null,
		),
		$code,
		$error
	);
}

/**
 * Find the first errored step in a content.insert step trace.
 *
 * @param array $steps Ordered step trace from content.insert.
 * @return array { step:string|null, error:string|null }
 */
function wp_poster_connector_links_first_error_step( $steps ) {
	if ( is_array( $steps ) ) {
		foreach ( $steps as $s ) {
			if ( isset( $s['status'] ) && 'error' === $s['status'] ) {
				return array(
					'step'  => isset( $s['step'] ) ? (string) $s['step'] : null,
					'error' => isset( $s['detail'] ) ? (string) $s['detail'] : null,
				);
			}
		}
	}
	return array(
		'step'  => null,
		'error' => null,
	);
}

/**
 * Derive a stable failure code from a finished (success:false) content.insert
 * result, so the service can branch without parsing the message.
 *
 * @param array $result content.insert result array.
 * @return string
 */
function wp_poster_connector_links_failure_code( $result ) {
	$builder = isset( $result['builder'] ) ? (string) $result['builder'] : '';
	if ( ! in_array( $builder, wp_poster_connector_content_supported_builders(), true ) ) {
		return 'wp_poster_unsupported_builder';
	}
	// Success === saved now (no public-render tier), so any other failure in this
	// branch means the write did not land in the database.
	return 'wp_poster_insert_failed';
}

/**
 * content.add — resolve the target page (explicit page_id, else the static front
 * page) and insert a marked content block into it, with DB-save verification.
 *
 * params: {
 *   html,                // (string) full service-built marked block HTML (required)
 *   container_id,        // (string) the block's id; must match the data-wp-poster marker in html
 *   page_id?,            // (int) target a specific page; omit to use the static front page
 *   builder?             // (string) optional: force a builder path (passed to content.insert)
 * }
 *
 * @param array $params Action parameters.
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_links_add( $params ) {
	$front         = wp_poster_connector_detect_front_page();
	$front_page_id = $front['front_page_id'] ? (int) $front['front_page_id'] : null;

	// Resolve the target page: an explicit page_id wins; otherwise the static
	// front page. If neither exists, the site lists posts on its home URL and
	// there is no single page to target.
	$explicit = isset( $params['page_id'] ) ? (int) $params['page_id'] : 0;
	if ( $explicit > 0 ) {
		$target_id = $explicit;
	} elseif ( null !== $front_page_id ) {
		$target_id = $front_page_id;
	} else {
		return wp_poster_connector_links_result(
			null,
			isset( $params['container_id'] ) ? trim( (string) $params['container_id'] ) : null,
			null,
			null,
			null,
			'wp_poster_no_front_page',
			'This site has no static front page (show_on_front=posts). Pass an explicit "page_id", or set a static front page under Settings -> Reading.'
		);
	}

	// Reuse the content.insert pipeline wholesale — same builder detection,
	// insert, and DB-save verification. We only resolve the target id and trim
	// the result; no insert logic is duplicated here.
	$params['page_id'] = $target_id;
	$result            = wp_poster_connector_content_insert( $params );

	// content.insert returns a WP_Error for request-validation problems (empty
	// html, missing/mismatched container_id, post not found). Flatten it into the
	// minimal failure shape so content.add has ONE response contract.
	if ( is_wp_error( $result ) ) {
		return wp_poster_connector_links_result(
			$target_id,
			isset( $params['container_id'] ) ? trim( (string) $params['container_id'] ) : null,
			null,
			null,
			null,
			$result->get_error_code(),
			$result->get_error_message()
		);
	}

	// Pipeline ran to completion — success.
	if ( ! empty( $result['success'] ) ) {
		return wp_poster_connector_links_result(
			$target_id,
			$result['container_id'],
			$result['detected_builder'],
			$result['permalink'], // clean permalink (no cache-bust arg)
			null,
			null,
			null
		);
	}

	// Pipeline failure — report the failed step + a derived code.
	$failed = wp_poster_connector_links_first_error_step( $result['steps'] );
	return wp_poster_connector_links_result(
		$target_id,
		$result['container_id'],
		$result['detected_builder'],
		null, // not confirmed live -> no final_url
		$failed['step'],
		wp_poster_connector_links_failure_code( $result ),
		$failed['error']
	);
}
