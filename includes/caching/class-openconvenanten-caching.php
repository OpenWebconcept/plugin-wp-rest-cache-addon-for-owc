<?php
/**
 * Class responsible for caching and saving cache relations of OpenConvenanten endpoints.
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
 * Class responsible for caching and saving cache relations of OpenConvenanten endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Openconvenanten_Caching extends Owc_Caching {

	const OPENCONVENANTEN_BASE = 'owc/openconvenanten/v1';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openconvenanten_Caching|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Openconvenanten_Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openconvenanten_Caching();
		}

		return self::$instance;
	}

	/**
	 * Set up the necessary variables.
	 */
	protected function setup() {
		$this->rest_base = self::OPENCONVENANTEN_BASE;
		if ( class_exists( 'Yard\OpenConvenanten\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'items';
			$this->mappings['items']                   = 'openconvenant-item';
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
		if ( ! in_array( $object_type, $this->mappings, true ) || ! isset( $data['data']['Convenantenverzoeken'] ) || ! is_array( $data['data']['Convenantenverzoeken'] ) ) {
			return;
		}

		foreach ( $data['data']['Convenantenverzoeken'] as $record ) {
			if ( ! is_array( $record ) ) {
				return;
			}

			$record = array_change_key_case( $record, CASE_LOWER );
			if ( array_key_exists( 'identifier', $record ) ) {
				$caching = Caching::get_instance();

				$caching->insert_cache_relation( $cache_id, $record['identifier'], $object_type );
			}
		}
	}
}
