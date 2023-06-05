<?php
/**
 * Class responsible for caching and saving cache relations of OpenWob endpoints.
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
 * Class responsible for caching and saving cache relations of OpenWob endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Openwob_Caching extends Owc_Caching {

	const OPENWOB_BASE = 'owc/openwob/v1';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openwob_Caching|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Openwob_Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openwob_Caching();
		}

		return self::$instance;
	}

	/**
	 * Set up the necessary variables.
	 */
	protected function setup() {
		$this->rest_base = self::OPENWOB_BASE;
		if ( class_exists( 'Yard\OpenWOB\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'items';
			$this->mappings['items']                   = 'openwob-item';
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
		if ( ! in_array( $object_type, $this->mappings, true ) || ! isset( $data['data']['WOBverzoeken'] ) || ! is_array( $data['data']['WOBverzoeken'] ) ) {
			return;
		}

		foreach ( $data['data']['WOBverzoeken'] as $record ) {
			if ( ! is_array( $record ) ) {
				return;
			}

			$record = array_change_key_case( $record, CASE_LOWER );
			if ( array_key_exists( 'uuid', $record ) ) {
				global $wpdb;

				$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wob_UUID' AND meta_value = %s", $record['uuid'] ) );

				if ( $post_id ) {
					$caching = Caching::get_instance();
					$caching->insert_cache_relation( $cache_id, $post_id, $object_type );
				}
			}
		}
	}
}
