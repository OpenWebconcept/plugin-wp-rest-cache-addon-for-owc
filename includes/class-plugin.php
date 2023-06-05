<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * the admin area and the caching functionality.
 *
 * @link:      https://www.acato.nl
 * @since      1.0.0
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes
 */

namespace WPRC_OWC\Includes;

use WPRC_OWC\Admin\Admin;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and caching hooks.
 *
 * @since      1.0.0
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Plugin {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Define the locale, and set the hooks for the admin area and the caching of endpoints.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->set_locale();
		$this->define_admin_hooks();

		Caching\Openconvenanten_Caching::get_instance();
		Caching\Openpub_Caching::get_instance();
		Caching\Openwob_Caching::get_instance();
		Caching\Openwoo_Caching::get_instance();
		Caching\Pdc_Caching::get_instance();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WPRC_OWC\Includes\I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function set_locale() {
		$plugin_i18n = new I18n();

		add_action( 'plugins_loaded', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register all the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		$admin = new Admin();

		add_action( 'admin_init', [ $admin, 'check_requirements' ] );
	}
}
