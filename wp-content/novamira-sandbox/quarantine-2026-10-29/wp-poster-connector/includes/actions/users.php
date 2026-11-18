<?php
/**
 * User actions: users.create.
 *
 * Returns the standard flat result (never a WP_Error). The password is NEVER
 * returned. `error` is null on success; on failure `code`/`error` are set.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the standard users.create result.
 *
 * @param int|null    $user_id  New user id, or null on failure.
 * @param string|null $role     Requested role, or null.
 * @param bool|null   $is_admin Has manage_options, or null.
 * @param bool|null   $verified Requested role actually stuck, or null.
 * @param string|null $code     Failure code, or null on success.
 * @param string|null $error    Failure message, or null on success.
 * @return array
 */
function wp_poster_connector_user_result( $user_id, $role, $is_admin, $verified, $code, $error ) {
	return wp_poster_connector_result(
		array(
			'user_id'          => ( $user_id ) ? (int) $user_id : null,
			'role'             => ( is_string( $role ) && '' !== $role ) ? $role : null,
			'is_administrator' => ( null !== $is_admin ) ? (bool) $is_admin : null,
			'verified'         => ( null !== $verified ) ? (bool) $verified : null,
		),
		$code,
		$error
	);
}

/**
 * action: users.create — create a WordPress user with a given role.
 *
 * params: {
 *   username, email, password,      (required)
 *   role?(default "subscriber"),    must be an installed role (e.g. "administrator")
 *   display_name?, first_name?, last_name?
 * }
 *
 * SECURITY: wp_insert_user() performs no current_user_can() check, so an
 * anonymous dispatch request can create a user — including an administrator.
 *
 * @return array Standard flat result (never a WP_Error). The password is NEVER returned.
 */
function wp_poster_connector_action_create_user( $params ) {
	$username = isset( $params['username'] ) ? sanitize_user( (string) $params['username'], true ) : '';
	$email    = isset( $params['email'] ) ? sanitize_email( (string) $params['email'] ) : '';
	$password = isset( $params['password'] ) ? (string) $params['password'] : '';
	$role     = isset( $params['role'] ) ? sanitize_key( (string) $params['role'] ) : 'subscriber';

	if ( '' === trim( $username ) ) {
		return wp_poster_connector_user_result( null, null, null, null, 'wp_poster_bad_username', 'Provide a non-empty "username".' );
	}
	if ( '' === $email || ! is_email( $email ) ) {
		return wp_poster_connector_user_result( null, null, null, null, 'wp_poster_bad_email', 'Provide a valid "email".' );
	}
	if ( '' === $password ) {
		return wp_poster_connector_user_result( null, null, null, null, 'wp_poster_missing_password', 'Provide a non-empty "password".' );
	}

	$roles = wp_roles();
	if ( ! $roles->is_role( $role ) ) {
		return wp_poster_connector_user_result(
			null,
			null,
			null,
			null,
			'wp_poster_bad_role',
			sprintf( 'Unknown role "%s". Valid roles: %s.', $role, implode( ', ', array_keys( $roles->get_names() ) ) )
		);
	}

	if ( username_exists( $username ) ) {
		return wp_poster_connector_user_result( null, null, null, null, 'wp_poster_user_exists', sprintf( 'A user with the login "%s" already exists.', $username ) );
	}
	if ( email_exists( $email ) ) {
		return wp_poster_connector_user_result( null, null, null, null, 'wp_poster_email_exists', sprintf( 'A user with the email "%s" already exists.', $email ) );
	}

	$userdata = array(
		'user_login' => $username,
		'user_pass'  => $password,
		'user_email' => $email,
		'role'       => $role,
	);
	if ( isset( $params['display_name'] ) ) {
		$userdata['display_name'] = sanitize_text_field( (string) $params['display_name'] );
	}
	if ( isset( $params['first_name'] ) ) {
		$userdata['first_name'] = sanitize_text_field( (string) $params['first_name'] );
	}
	if ( isset( $params['last_name'] ) ) {
		$userdata['last_name'] = sanitize_text_field( (string) $params['last_name'] );
	}

	$result = wp_insert_user( $userdata );
	if ( is_wp_error( $result ) ) {
		return wp_poster_connector_user_result( null, null, null, null, 'wp_poster_user_insert_failed', 'wp_insert_user failed: ' . $result->get_error_message() );
	}

	$user_id      = (int) $result;
	$user         = get_userdata( $user_id );
	$actual_roles = $user ? array_values( (array) $user->roles ) : array();
	$verified     = in_array( $role, $actual_roles, true );
	$is_admin     = user_can( $user_id, 'manage_options' );

	return wp_poster_connector_user_result( $user_id, $role, $is_admin, $verified, null, null );
}
