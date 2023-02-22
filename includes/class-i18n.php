<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link:      https://www.acato.nl
 * @since      1.0.0
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes
 */

namespace WPRC_OWC\Includes;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wp-rest-cache-addon-for-owc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
