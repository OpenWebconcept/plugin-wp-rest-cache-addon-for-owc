<?php
/**
 * Class responsible for caching and saving cache relations of OpenWoo endpoints.
 *
 * @link  http://www.acato.nl
 * @since 1.0.0
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 */

namespace WPRC_OWC\Includes\Caching;

use WP_Rest_Cache_Plugin\Includes\Caching\Caching;

/**
 * Class responsible for caching and saving cache relations of OpenWoo endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Openwoo_Caching extends Owc_Caching {

	const OPENWOO_BASE = 'owc/openwoo/v1';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openwoo_Caching|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Openwoo_Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openwoo_Caching();
		}

		return self::$instance;
	}

	/**
	 * Set up the necessary variables.
	 */
	protected function setup() {
		$this->rest_base = self::OPENWOO_BASE;
		if ( class_exists( 'Yard\OpenWOO\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'items';
			$this->mappings['items']                   = 'openwoo-item';
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
		if ( ! in_array( $object_type, $this->mappings, true ) || ! isset( $data['data']['WOOverzoeken'] ) || ! is_array( $data['data']['WOOverzoeken'] ) ) {
			return;
		}

		foreach ( $data['data']['WOOverzoeken'] as $record ) {
			if ( ! is_array( $record ) ) {
				return;
			}

			$record = array_change_key_case( $record, CASE_LOWER );
			if ( array_key_exists( 'object_id', $record ) ) {
				$caching = Caching::get_instance();

				$caching->insert_cache_relation( $cache_id, $record['object_id'], $object_type );
			}
		}
	}
}
