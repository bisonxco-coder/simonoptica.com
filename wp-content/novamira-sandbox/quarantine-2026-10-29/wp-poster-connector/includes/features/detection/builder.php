<?php
/**
 * Page-builder detection for the WP Poster Connector.
 *
 * Detects which builder owns a given post by reading AUTHORITATIVE per-post meta
 * (and, as a last resort, the post_content) server-side. Reading the per-post
 * activation flags directly avoids false positives from builder PLUGINS that are
 * merely installed: a site with Elementor installed but a specific page built in
 * Gutenberg is classified correctly.
 *
 * Builder slugs:
 *   divi, oxygen, beaver_builder, elementor, bricks, breakdance, brizy, thrive,
 *   wpbakery, gutenberg, classic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is a post-meta value "set"? Treats '', '0', 0, null, false, array() as empty.
 */
function wp_poster_connector_meta_has_value( $value ) {
	if ( is_array( $value ) ) {
		return ! empty( $value );
	}
	$value = (string) $value;
	return '' !== trim( $value ) && '0' !== $value;
}

/**
 * Detect the page builder that owns a post.
 *
 * Detection order: third-party page builders first (each identified by its own
 * per-post activation flag, mutually exclusive in practice), then the looser
 * content-based checks (WPBakery, Gutenberg block markers), then Classic as the
 * catch-all.
 *
 * @return array {
 *     @type string|null $builder One of the builder slugs, or null if the post
 *                                doesn't exist.
 *     @type string      $source  Short tag describing the evidence that matched
 *                                (e.g. "meta:_et_pb_use_builder", "fallback").
 * }
 */
function wp_poster_connector_detect_builder( $post_id ) {
	$post_id = (int) $post_id;
	$post    = get_post( $post_id );
	if ( ! $post ) {
		return array(
			'builder' => null,
			'source'  => 'post_not_found',
		);
	}

	$content = (string) $post->post_content;

	// Divi. Activation flag: _et_pb_use_builder = 'on'. Layout lives as [et_pb_*]
	// shortcodes in post_content.
	if ( 'on' === get_post_meta( $post_id, '_et_pb_use_builder', true ) ) {
		return array(
			'builder' => 'divi',
			'source'  => 'meta:_et_pb_use_builder',
		);
	}

	// Oxygen. Layout in ct_builder_shortcodes (3.x) or ct_builder_json (4.x)
	// post_meta; NOT post_content.
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, 'ct_builder_shortcodes', true ) ) ) {
		return array(
			'builder' => 'oxygen',
			'source'  => 'meta:ct_builder_shortcodes',
		);
	}
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, 'ct_builder_json', true ) ) ) {
		return array(
			'builder' => 'oxygen',
			'source'  => 'meta:ct_builder_json',
		);
	}

	// Beaver Builder. Activation flag: _fl_builder_enabled. Layout in
	// _fl_builder_data (PHP-serialized), which the_content renders INSTEAD of
	// post_content.
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, '_fl_builder_enabled', true ) ) ) {
		return array(
			'builder' => 'beaver_builder',
			'source'  => 'meta:_fl_builder_enabled',
		);
	}

	// Elementor. _elementor_edit_mode = 'builder' when Elementor owns the page;
	// layout in _elementor_data (JSON).
	if ( 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
		return array(
			'builder' => 'elementor',
			'source'  => 'meta:_elementor_edit_mode',
		);
	}
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, '_elementor_data', true ) ) ) {
		return array(
			'builder' => 'elementor',
			'source'  => 'meta:_elementor_data',
		);
	}

	// Bricks. Activation: _bricks_editor_mode = 'bricks' (vs 'wordpress'); layout
	// stored as an element array in _bricks_page_content_2 post_meta.
	if ( 'bricks' === get_post_meta( $post_id, '_bricks_editor_mode', true ) ) {
		return array(
			'builder' => 'bricks',
			'source'  => 'meta:_bricks_editor_mode',
		);
	}
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, '_bricks_page_content_2', true ) ) ) {
		return array(
			'builder' => 'bricks',
			'source'  => 'meta:_bricks_page_content_2',
		);
	}

	// Breakdance. Layout stored as JSON in _breakdance_data post_meta.
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, '_breakdance_data', true ) ) ) {
		return array(
			'builder' => 'breakdance',
			'source'  => 'meta:_breakdance_data',
		);
	}

	// Brizy. Editor data stored in the 'brizy' post_meta (serialized array holding
	// the base64 editor_data). A per-post uid lives in 'brizy-post-uid'.
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, 'brizy', true ) ) ) {
		return array(
			'builder' => 'brizy',
			'source'  => 'meta:brizy',
		);
	}

	// Thrive Architect. Landing pages set tve_landing_page (the template name);
	// inline Architect content on a normal page/post is stored in tve_updated_post.
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, 'tve_landing_page', true ) ) ) {
		return array(
			'builder' => 'thrive',
			'source'  => 'meta:tve_landing_page',
		);
	}
	if ( wp_poster_connector_meta_has_value( get_post_meta( $post_id, 'tve_updated_post', true ) ) ) {
		return array(
			'builder' => 'thrive',
			'source'  => 'meta:tve_updated_post',
		);
	}

	// WPBakery (Visual Composer). Stores [vc_*] shortcodes in post_content.
	// _wpb_vc_js_status = 'true' is set once the Backend Editor has been used on
	// the post.
	if ( 'true' === (string) get_post_meta( $post_id, '_wpb_vc_js_status', true ) ) {
		return array(
			'builder' => 'wpbakery',
			'source'  => 'meta:_wpb_vc_js_status',
		);
	}
	if ( false !== strpos( $content, '[vc_row' ) ) {
		return array(
			'builder' => 'wpbakery',
			'source'  => 'content:[vc_row]',
		);
	}

	// Gutenberg (block editor). Core check: post_content carries block delimiters
	// (<!-- wp: -->).
	if ( function_exists( 'has_blocks' ) && has_blocks( $content ) ) {
		return array(
			'builder' => 'gutenberg',
			'source'  => 'has_blocks',
		);
	}

	// Classic. No builder flag and no block markers: plain post_content (Classic
	// Editor).
	return array(
		'builder' => 'classic',
		'source'  => 'fallback',
	);
}
