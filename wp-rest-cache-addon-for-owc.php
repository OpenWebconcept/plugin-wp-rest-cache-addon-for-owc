<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and starts the plugin.
 *
 * @link:             https://www.acato.nl
 * @since             1.0.0
 * @package           WPRC_OWC
 *
 * @wordpress-plugin
 * Plugin Name:       WP REST Cache - AddOn for OpenWebConcept
 * Plugin URI:        https://www.openwebconcept.nl/
 * Description:       Adds caching of the OpenWebConcept endpoints to the WP REST Cache.
 * Version:           1.1.0
 * Author:            Acato
 * Author URI:        https://www.acato.nl
 * Text Domain:       wp-rest-cache-addon-for-owc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPRC_OWC_VERSION', '1.1.0' );

require_once plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-autoloader.php';
spl_autoload_register( array( '\WPRC_OWC\Includes\Autoloader', 'autoload' ) );

/**
 * Begins execution of the plugin.
 */
new \WPRC_OWC\Includes\Plugin();
