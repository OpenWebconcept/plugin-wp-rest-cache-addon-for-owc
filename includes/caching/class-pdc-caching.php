<?php
/**
 * Class responsible for caching and saving cache relations of PDC endpoints.
 *
 * @link: http://www.acato.nl
 * @since 1.0.0
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 */

namespace WPRC_OWC\Includes\Caching;

use WP_Rest_Cache_Plugin\Includes\Caching\Caching;

/**
 * Class responsible for caching and saving cache relations of PDC endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author:    Richard Korthuis <richardkorthuis@acato.nl>
 */
class Pdc_Caching extends Owc_Caching {

	const PDC_BASE = 'owc/pdc/v1';

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Pdc_Caching|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Pdc_Caching
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Pdc_Caching();
		}

		return self::$instance;
	}

	/**
	 * Set up the necessary variables.
	 */
	protected function setup() {
		$this->rest_base = self::PDC_BASE;

		if ( class_exists( 'OWC\PDC\Base\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'items';
			$this->mappings['items']                   = 'pdc-item';
			$this->owc_endpoints[ $this->rest_base ][] = 'themas';
			$this->mappings['themas']                  = 'pdc-category';
			$this->owc_endpoints[ $this->rest_base ][] = 'themes';
			$this->mappings['themes']                  = 'pdc-category';
			$this->owc_endpoints[ $this->rest_base ][] = 'subthemas';
			$this->mappings['subthemas']               = 'pdc-subcategory';
			$this->owc_endpoints[ $this->rest_base ][] = 'subthemes';
			$this->mappings['subthemes']               = 'pdc-subcategory';
			$this->owc_endpoints[ $this->rest_base ][] = 'groups';
			$this->mappings['groups']                  = 'pdc-group';
			$this->owc_endpoints[ $this->rest_base ][] = 'sdg';
			$this->mappings['sdg']                     = 'pdc-item';
			$this->owc_endpoints[ $this->rest_base ][] = 'sdg-kiss';
			$this->mappings['sdg-kiss']                = 'pdc-item';
		}
		if ( class_exists( 'OWC\PDC\Locations\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'locations';
			$this->mappings['locations']               = 'pdc-location';
		}
		if ( class_exists( 'OWC\PDC\InternalProducts\Foundation\Plugin' ) ) {
			if ( ! isset( $this->disallowed_owc_endpoints[ $this->rest_base ] ) ) {
				$this->disallowed_owc_endpoints[ $this->rest_base ] = [];
			}
			$this->disallowed_owc_endpoints[ $this->rest_base ][] = 'items/internal'; // This endpoint needs authentication.
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

		if ( false !== strpos( $uri, $this->rest_base . '/sdg' ) ) {
			$this->process_sdg_cache_relations( $cache_id, $data, $object_type, $uri );
		} else {
			parent::process_default_cache_relations( $cache_id, $data, $object_type, $uri );
		}
	}

	/**
	 * Process all cache relations for the current SDG cache record.
	 *
	 * @param int    $cache_id The row id of the current cache.
	 * @param mixed  $data The data that is to be cached.
	 * @param string $object_type Object type.
	 * @param string $uri The requested URI.
	 *
	 * @return void
	 */
	private function process_sdg_cache_relations( $cache_id, $data, $object_type, $uri ) {
		if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return;
		}

		$this->process_recursive_sdg_cache_relations( $cache_id, $object_type, $data['data'] );
	}

	/**
	 * Process all SDG cache relations recursively.
	 *
	 * @param int          $cache_id The row id of the current cache.
	 * @param string       $object_type Object type.
	 * @param array<mixed> $record An array of data for which the relations need to be determined.
	 *
	 * @return void
	 */
	private function process_recursive_sdg_cache_relations( $cache_id, $object_type, $record ) {
		if ( ! is_array( $record ) ) {
			return;
		}

		$record = array_change_key_case( $record, CASE_LOWER );
		if ( array_key_exists( 'uuid', $record ) ) {
			global $wpdb;

			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_owc_enrichment_uuid' AND meta_value = %s", $record['uuid'] ) );

			if ( $post_id ) {
				$caching = Caching::get_instance();
				$caching->insert_cache_relation( $cache_id, $post_id, $object_type );
			}
		} else {
			foreach ( $record as $subrecord ) {
				$this->process_recursive_sdg_cache_relations( $cache_id, $object_type, $subrecord );
			}
		}
	}
}
