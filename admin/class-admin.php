<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link:      https://www.acato.nl
 * @since      1.0.0
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Admin
 */

namespace WPRC_OWC\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Admin
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Check if WP REST Cache is installed and activated.
	 *
	 * @return void
	 */
	public function check_requirements() {
		if ( ! class_exists( 'WP_Rest_Cache_Plugin\Includes\Plugin' ) ) {
			deactivate_plugins( 'wp-rest-cache-addon-for-owc/wp-rest-cache-addon-for-owc.php' );
			add_action( 'admin_notices', [ $this, 'requirements_notice' ] );

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['activate'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				unset( $_GET['activate'] );
			}
		}
	}

	/**
	 * Show a notice that WP REST Cache and WooCommerce are required for this plugin.
	 *
	 * @return void
	 */
	public function requirements_notice() {
		echo '<div class="error"><p>' . esc_html__( '"WP REST Cache - AddOn for OpenWebConcept" requires "WP REST Cache" to be installed and activated', 'wp-rest-cache-addon-for-owc' ) . '</p></div>';
	}

}
