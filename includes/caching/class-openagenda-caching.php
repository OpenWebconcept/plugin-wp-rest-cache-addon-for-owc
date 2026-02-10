<?php
/**
 * Class responsible for caching and saving cache relations of OpenAgenda endpoints.
 *
 * @link  http://www.acato.nl
 * @since 1.0.0
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 */

namespace WPRC_OWC\Includes\Caching;

/**
 * Class responsible for caching and saving cache relations of OpenAgenda endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author     Eyal Beker <eyal@acato.nl>
 */
class Openagenda_Caching extends Owc_Caching {

	const OPENAGENDA_BASE = 'owc/openagenda/v1';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Openagenda_Caching|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Openagenda_Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Openagenda_Caching();
		}

		return self::$instance;
	}

	/**
	 * Set up the necessary variables.
	 */
	protected function setup() {
		$this->rest_base = self::OPENAGENDA_BASE;

		if ( class_exists( 'Openagenda_Base_Plugin\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'items';
			$this->mappings['items']                   = 'event';
			$this->owc_endpoints[ $this->rest_base ][] = 'locations';
			$this->mappings['locations']               = 'location';
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
}
