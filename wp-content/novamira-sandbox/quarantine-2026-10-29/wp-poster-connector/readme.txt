=== WP Poster Connector ===
Contributors: posterservice
Tags: content, publishing, integration
Requires at least: 5.2
Tested up to: 6.9
Requires PHP: 7.0
Stable tag: 2.21.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Securely connects this site to its content service so the owner's team can keep posts and page content up to date remotely.

== Description ==

WP Poster Connector links this WordPress site to the content service the site
owner uses, so their team can publish and maintain content without signing in to
the dashboard each time.

The plugin runs quietly in the background. It has no settings screen and nothing
for visitors to interact with — there is no public-facing interface. Requests are
accepted only when they are authenticated against the owner's account; everything
else is ignored.

This plugin is intended for sites managed through the poster service. If it was
not installed as part of that setup, you can safely deactivate and remove it.

== Privacy ==

The plugin only responds to authenticated requests from the content service this
site is connected to. It does not collect information from visitors and adds no
tracking to the front end.

== Frequently Asked Questions ==

= Do I need to configure anything? =

No. The connection is managed entirely from the content service; there is nothing
to set up on the site itself.

= Is it safe to remove? =

Yes. Deactivating or deleting the plugin simply ends the connection. Your existing
posts and pages are not affected.

== Changelog ==

= 2.21.0 =
* Maintenance, reliability, and security improvements.
