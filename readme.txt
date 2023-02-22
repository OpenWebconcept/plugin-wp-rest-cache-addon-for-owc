=== WP REST Cache - AddOn for OpenWebConcept ===
Contributors: acato, rockfire
Tags: cache, wp-rest-api, api, rest, rest cache, rest api cache, openwebconcept, owc
Requires at least: 4.7
Tested up to: 6.0
Stable tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Adds caching of the OpenWebConcept endpoints to the WP REST Cache.

== Description ==

Adds caching of the OpenWebConcept endpoints to the WP REST Cache. See readme.md for all supported OWC plugins / endpoints.

== Installation ==

1. Unzip and/or move all files to the /wp-contents/plugins/wp-rest-cache-addon-for-owc directory.
2. Log into the WordPress admin and make sure the WP REST Cache plugin (https://wordpress.org/plugins/wp-rest-cache/) is installed and activated.
3. Activate the 'WP REST Cache - AddOn for OpenWebConcept' plugin through the 'Plugins' menu.

or install using Composer:

1. `composer config repositories.openwebconcept/wp-rest-cache-addon-for-owc git git@github.com:OpenWebconcept/plugin-wp-rest-cache-addon-for-owc.git`
2. `composer require openwebconcept/wp-rest-cache-addon-for-owc`

== Changelog ==

= 1.0.0 =
* First release.