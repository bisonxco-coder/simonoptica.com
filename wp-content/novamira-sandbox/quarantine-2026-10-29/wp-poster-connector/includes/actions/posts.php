<?php
/**
 * Post actions: posts.create.
 *
 * Returns the standard flat result (never a WP_Error): `error` is null on
 * success; on failure `code`/`error` are set, which the dispatch envelope reports
 * as ok:false.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a post author user ID.
 *
 * $author may be a numeric user ID or a login/email/slug; returns 0 if an
 * explicit value matched no user. If empty, falls back to the lowest-ID
 * administrator, then any user, so posts are never authorless.
 *
 * @param mixed $author Requested author (ID or login/email/slug), or null.
 * @return int Resolved user ID, or 0 if none could be resolved.
 */
function wp_poster_connector_resolve_author( $author ) {
	if ( null !== $author && '' !== $author ) {
		if ( is_numeric( $author ) ) {
			$user = get_user_by( 'id', (int) $author );
		} else {
			$author = (string) $author;
			$user   = get_user_by( 'login', $author );
			if ( ! $user ) {
				$user = get_user_by( 'email', $author );
			}
			if ( ! $user ) {
				$user = get_user_by( 'slug', $author );
			}
		}
		return $user ? (int) $user->ID : 0;
	}

	// No author requested: pick a sensible default.
	$admins = get_users(
		array(
			'role'    => 'administrator',
			'number'  => 1,
			'orderby' => 'ID',
			'order'   => 'ASC',
			'fields'  => 'ID',
		)
	);
	if ( ! empty( $admins ) ) {
		return (int) $admins[0];
	}

	$any = get_users(
		array(
			'number'  => 1,
			'orderby' => 'ID',
			'order'   => 'ASC',
			'fields'  => 'ID',
		)
	);
	return ! empty( $any ) ? (int) $any[0] : 0;
}

/**
 * Build the standard posts.create result.
 *
 * @param int|null    $post_id   New post id, or null on failure.
 * @param string|null $status    Stored post_status, or null.
 * @param string|null $permalink Permalink, or null.
 * @param int|null    $author_id Stored author id, or null.
 * @param string|null $code      Failure code, or null on success.
 * @param string|null $error     Failure message, or null on success.
 * @return array
 */
function wp_poster_connector_post_result( $post_id, $status, $permalink, $author_id, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'post_id'   => ( $post_id ) ? (int) $post_id : null,
			'status'    => ( is_string( $status ) && '' !== $status ) ? $status : null,
			'permalink' => ( $permalink ) ? (string) $permalink : null,
			'author_id' => ( $author_id ) ? (int) $author_id : null,
		),
		$code,
		$error
	);
}

/**
 * action: posts.create — create and publish a post.
 *
 * params: { title, content, status?(default "publish"), author?(id|login|email|slug) }
 * At least one of title/content must be non-empty.
 *
 * @return array Standard flat result (never a WP_Error).
 */
function wp_poster_connector_action_create_post( $params ) {
	$title   = isset( $params['title'] ) ? sanitize_text_field( (string) $params['title'] ) : '';
	$content = isset( $params['content'] ) ? (string) $params['content'] : '';
	$status  = isset( $params['status'] ) ? sanitize_key( (string) $params['status'] ) : 'publish';

	if ( '' === trim( $title ) && '' === trim( $content ) ) {
		return wp_poster_connector_post_result( null, null, null, null, 'wp_poster_empty', 'Provide at least a non-empty title or content.' );
	}

	// Resolve the author. An explicitly requested author that doesn't match any
	// user is a client error; otherwise we auto-pick an available one.
	$author_requested = isset( $params['author'] ) ? $params['author'] : null;
	$author_id        = wp_poster_connector_resolve_author( $author_requested );
	if ( null !== $author_requested && '' !== $author_requested && 0 === $author_id ) {
		return wp_poster_connector_post_result( null, null, null, null, 'wp_poster_author_not_found', 'No user matched the requested author.' );
	}

	$postarr = array(
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => $status,
		'post_type'    => 'post',
		'post_author'  => $author_id,
	);

	// No logged-in user => WordPress would wp_kses_post() $content on save,
	// stripping data-*/inline style/<iframe>. Content is trusted, so bypass kses
	// (see includes/helpers.php). Second arg true => WP_Error on failure, not 0.
	$result = wp_poster_connector_write_without_kses(
		function () use ( $postarr ) {
			return wp_insert_post( $postarr, true );
		}
	);
	if ( is_wp_error( $result ) ) {
		return wp_poster_connector_post_result( null, null, null, null, 'wp_poster_insert_failed', 'wp_insert_post failed: ' . $result->get_error_message() );
	}

	$post_id         = (int) $result;
	$post            = get_post( $post_id );
	$author_id_final = $post ? (int) $post->post_author : $author_id;

	return wp_poster_connector_post_result(
		$post_id,
		$post ? $post->post_status : $status,
		get_permalink( $post_id ),
		$author_id_final,
		null,
		null
	);
}
