<?php
/**
 * Class responsible for caching and saving cache relations of OpenPub endpoints.
 *
 * @link  http://www.acato.nl
 * @since 1.0.0
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 */

namespace WPRC_OWC\Includes\Caching;

/**
 * Class responsible for caching and saving cache relations of OpenPub endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Openpub_Caching extends Owc_Caching {

	const OPENPUB_BASE = 'owc/openpub/v1';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openpub_Caching|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Openpub_Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openpub_Caching();
		}

		return self::$instance;
	}

	/**
	 * Set up the necessary variables.
	 */
	protected function setup() {
		$this->rest_base = self::OPENPUB_BASE;

		if ( class_exists( 'OWC\OpenPub\Base\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'items';
			$this->mappings['items']                   = 'openpub-item';
			$this->owc_endpoints[ $this->rest_base ][] = 'themes';
			$this->mappings['themes']                  = 'openpub-theme';
		}
		if ( class_exists( 'OWC\Persberichten\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'persberichten';
			$this->mappings['persberichten']           = 'press-item';
		}
		if ( class_exists( 'OWC\RuimtelijkePlannen\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'ruimtelijke-plannen';
			$this->mappings['ruimtelijke-plannen']     = 'spatial_plan';
		}
		if ( class_exists( 'OWC\Besluiten\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'besluiten';
			$this->mappings['besluiten']               = 'public-decision';
		}
		if ( class_exists( 'OWC\OpenPub\Base\RestAPI\Controllers\SettingsController' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'settings';
			$this->mappings['settings']                = 'openpub-settings';

			add_action( 'update_option__owc_openpub_base_settings', [ $this, 'clear_settings_cache' ] );
		}
	}

	/**
	 * Process all cache relations for the current cache record.
	 *
	 * @param int    $cache_id The row id of the current cache.
	 * @param mixed  $data The data that is to be cached.
	 * @param string $object_type Object type.
	 * @param string $uri The requested URI.
	 *
	 * @return void
	 */
	public function process_cache_relations( $cache_id, $data, $object_type, $uri ) {
		if ( ! in_array( $object_type, $this->mappings, true ) ) {
			return;
		}

		parent::process_default_cache_relations( $cache_id, $data, $object_type );
	}

	/**
	 * Clear the settings cache.
	 *
	 * @return void
	 */
	public function clear_settings_cache() {
		\WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->delete_object_type_caches( 'openpub-settings' );
	}
}
