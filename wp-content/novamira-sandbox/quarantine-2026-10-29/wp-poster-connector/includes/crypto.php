<?php
/**
 * Ed25519 request-signature verification for the WP Poster Connector.
 *
 * The poster service holds the PRIVATE key; this plugin ships the matching
 * PUBLIC key in public_key.php (base64 of the raw 32 bytes).
 *
 * NOTE: this verifier is wired as the dispatch route's permission_callback (see
 * includes/dispatch.php), so every request must carry a valid X-WPP-Signature.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the baked-in Ed25519 public key (raw 32 bytes), cached per request.
 *
 * public_key.php returns the key as base64 of the raw 32-byte Ed25519 key.
 *
 * @return string Raw public key bytes, or '' if missing/malformed.
 */
function wp_poster_connector_public_key() {
	static $key = null;
	if ( null !== $key ) {
		return $key;
	}
	$key  = '';
	$file = dirname( __DIR__ ) . '/public_key.php';
	if ( is_readable( $file ) ) {
		$b64 = (string) include $file;
		$raw = base64_decode( trim( $b64 ), true );
		if ( false !== $raw ) {
			$key = $raw;
		}
	}
	return $key;
}

/**
 * This site's stable identity, signed into every request to bind it to THIS site.
 *
 * Derived from home_url() — the site address stored in the DB — and NEVER from
 * the request's Host header. The Host header is attacker-controlled: if we keyed
 * off it, a replayer could resend site A's signed request to site B while spoofing
 * "Host: site-a.com", and it would verify — defeating the entire point. home_url()
 * cannot be influenced by the incoming request, so site B always computes its own
 * identity and the foreign signature fails.
 *
 * MUST stay byte-for-byte identical to canonical_site_id() in
 * wp_plugin/connector_signer.py (the two are compared via the signature): scheme
 * dropped, host lowercased, a leading "www." stripped, path kept (subdir installs)
 * with the trailing slash removed, port/query/fragment dropped.
 *
 * @return string e.g. "example.com" or "example.com/blog".
 */
function wp_poster_connector_canonical_site_id() {
	$parsed = wp_parse_url( home_url() );
	$host   = isset( $parsed['host'] ) ? strtolower( $parsed['host'] ) : '';
	if ( 0 === strpos( $host, 'www.' ) ) {
		$host = substr( $host, 4 );
	}
	$path = isset( $parsed['path'] ) ? rtrim( $parsed['path'], '/' ) : '';
	return $host . $path;
}

/**
 * Permission callback: verify the request's Ed25519 signature.
 *
 * The service sends one header:
 *   X-WPP-Signature  base64( ed25519_sign( CANONICAL_SITE_ID "\n" sha256_hex(body) ) )
 *
 * CANONICAL_SITE_ID is derived HERE from this site's own home_url() (see
 * wp_poster_connector_canonical_site_id()), and the service signs the matching
 * value for whichever site it is targeting. Because every install ships the same
 * public key, binding the site is what stops a request signed for site A from
 * also verifying on site B (cross-site replay). Proves authenticity + integrity
 * (body, incl. action/params, can't be tampered) and binds the request to THIS
 * site. Does NOT protect against same-site replay (no nonce/timestamp).
 *
 * @param WP_REST_Request $request Incoming request.
 * @return bool|WP_Error True if the signature verifies, WP_Error otherwise.
 */
function wp_poster_connector_verify_signature( WP_REST_Request $request ) {
	if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) ) {
		return new WP_Error(
			'wp_poster_no_sodium',
			'libsodium is not available on this host; cannot verify signatures.',
			array( 'status' => 501 )
		);
	}

	$pub = wp_poster_connector_public_key();
	if ( SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES !== strlen( $pub ) ) {
		return new WP_Error(
			'wp_poster_no_key',
			'Connector public key is missing or malformed.',
			array( 'status' => 500 )
		);
	}

	$sig_b64 = $request->get_header( 'X-WPP-Signature' );
	if ( ! $sig_b64 ) {
		return new WP_Error(
			'wp_poster_missing_signature',
			'Missing the X-WPP-Signature header.',
			array( 'status' => 401 )
		);
	}

	$sig = base64_decode( $sig_b64, true );
	if ( false === $sig || SODIUM_CRYPTO_SIGN_BYTES !== strlen( $sig ) ) {
		return new WP_Error(
			'wp_poster_malformed_signature',
			'Signature is not valid base64 or has the wrong length.',
			array( 'status' => 401 )
		);
	}

	$body = $request->get_body();
	if ( ! is_string( $body ) ) {
		$body = '';
	}
	$signing_string = wp_poster_connector_canonical_site_id() . "\n" . hash( 'sha256', $body );

	if ( ! sodium_crypto_sign_verify_detached( $sig, $signing_string, $pub ) ) {
		return new WP_Error(
			'wp_poster_unauthorized',
			'Signature verification failed.',
			array( 'status' => 401 )
		);
	}

	return true;
}
