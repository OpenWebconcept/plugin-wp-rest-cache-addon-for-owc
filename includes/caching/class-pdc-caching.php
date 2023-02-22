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

/**
 * Class responsible for caching and saving cache relations of PDC endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author:    Richard Korthuis <richardkorthuis@acato.nl>
 */
class Pdc_Caching {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Caching $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * The REST base for PDC endpoints.
	 *
	 * @access private
	 * @var    string $pdc_base REST base for PDC endpoints.
	 */
	private $pdc_base = 'owc/pdc/v1';

	/**
	 * An array of known OWC endpoints.
	 *
	 * @access private
	 * @var    array $owc_endpoints An array of known OWC endpoints.
	 */
	private $owc_endpoints = [];

	/**
	 * An array of OWC endpoints that should not be cached.
	 *
	 * @access private
	 * @var    array $disallowed_owc_endpoints An array of OWC endpoints that should not be cached.
	 */
	private $disallowed_owc_endpoints = [];

	/**
	 * An array mapping endpoint keys to post-types.
	 *
	 * @access private
	 * @var    array $mappings An array mapping endpoint keys to post-types.
	 */
	private $mappings = [
		'items'     => 'pdc-item',
		'sdg'       => 'pdc-item',
		'sdg-kiss'  => 'pdc-item',
		'themes'    => 'pdc-category',
		'themas'    => 'pdc-category',
		'subthemes' => 'pdc-subcategory',
		'subthemas' => 'pdc-subcategory',
		'groups'    => 'pdc-group',
	];

	/**
	 * Constructor.
	 */
	private function __construct() {
		if ( class_exists( 'OWC\PDC\Base\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->pdc_base ] ) ) {
				$this->owc_endpoints[ $this->pdc_base ] = [];
			}
			$this->owc_endpoints[ $this->pdc_base ][] = 'items';
			$this->owc_endpoints[ $this->pdc_base ][] = 'themas';
			$this->owc_endpoints[ $this->pdc_base ][] = 'themes';
			$this->owc_endpoints[ $this->pdc_base ][] = 'subthemas';
			$this->owc_endpoints[ $this->pdc_base ][] = 'subthemes';
			$this->owc_endpoints[ $this->pdc_base ][] = 'groups';
			$this->owc_endpoints[ $this->pdc_base ][] = 'sdg';
			$this->owc_endpoints[ $this->pdc_base ][] = 'sdg-kiss';
		}
		if ( class_exists( 'OWC\PDC\Locations\Foundation\Plugin' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->pdc_base ] ) ) {
				$this->owc_endpoints[ $this->pdc_base ] = [];
			}
			$this->owc_endpoints[ $this->pdc_base ][] = 'locations';
		}
		if ( class_exists( 'OWC\PDC\InternalProducts\Foundation\Plugin' ) ) {
			if ( ! isset( $this->disallowed_owc_endpoints[ $this->pdc_base ] ) ) {
				$this->disallowed_owc_endpoints[ $this->pdc_base ] = [];
			}
			$this->disallowed_owc_endpoints[ $this->pdc_base ][] = 'items/internal'; // This endpoint needs authentication.
		}
	}

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
	 * Filter which REST endpoints should be cached.
	 *
	 * @param array $allowed_endpoints An array of allowed endpoints.
	 *
	 * @return array An array of allowed endpoints.
	 */
	public function add_owc_endpoints( $allowed_endpoints ) {
		foreach ( $this->owc_endpoints as $namespace => $endpoints ) {
			if ( ! isset( $allowed_endpoints[ $namespace ] ) && is_array( $endpoints ) && count( $endpoints ) ) {
				$allowed_endpoints[ $namespace ] = [];
			}

			foreach ( $endpoints as $endpoint ) {
				if ( ! in_array( $endpoint, $allowed_endpoints[ $namespace ], true ) ) {
					$allowed_endpoints[ $namespace ][] = $endpoint;
				}
			}
		}

		return $allowed_endpoints;
	}

	/**
	 * Filter which REST endpoints should not be cached.
	 *
	 * @param array $disallowed_endpoints An array of disallowed endpoints.
	 *
	 * @return array An array of disallowed endpoints.
	 */
	public function disallow_owc_endpoints( $disallowed_endpoints ) {
		foreach ( $this->disallowed_owc_endpoints as $namespace => $endpoints ) {
			if ( ! isset( $disallowed_endpoints[ $namespace ] ) && is_array( $endpoints ) && count( $endpoints ) ) {
				$disallowed_endpoints[ $namespace ] = [];
			}

			foreach ( $endpoints as $endpoint ) {
				if ( ! in_array( $endpoint, $disallowed_endpoints[ $namespace ], true ) ) {
					$disallowed_endpoints[ $namespace ][] = $endpoint;
				}
			}
		}

		return $disallowed_endpoints;
	}

	/**
	 * Determine the correct object type for the current cache record.
	 *
	 * @param string $type Object type.
	 * @param string $cache_key Cache key.
	 * @param mixed  $data The data that is to be cached.
	 * @param string $uri The requested URI.
	 *
	 * @return string The object type of the current cache record.
	 */
	public function determine_object_type( $type, $cache_key, $data, $uri ) {
		$uri_parts    = wp_parse_url( $uri );
		$request_path = rtrim( $uri_parts['path'], '/' );

		// Make sure we only apply to allowed api calls.
		$rest_prefix = sprintf( '/%s/%s/', get_option( 'wp_rest_cache_rest_prefix', 'wp-json' ), $this->pdc_base );
		if ( strpos( $request_path, $rest_prefix ) === false ) {
			return $type;
		}

		$rest_path       = substr( $request_path, strlen( $rest_prefix ) );
		$rest_path_parts = explode( '/', $rest_path );

		if ( count( $rest_path_parts ) ) {
			if ( isset( $this->mappings[ $rest_path_parts[0] ] ) ) {
				return $this->mappings[ $rest_path_parts[0] ];
			}
		}

		return $type;
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
		if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return;
		}

		$this->process_recursive_cache_relations( $cache_id, $object_type, $data['data'] );
	}

	/**
	 * Process all cache relations recursively.
	 *
	 * @param int    $cache_id The row id of the current cache.
	 * @param string $object_type Object type.
	 * @param array  $record An array of data for which the relations need to be determined.
	 *
	 * @return void
	 */
	private function process_recursive_cache_relations( $cache_id, $object_type, $record ) {
		if ( ! is_array( $record ) ) {
			return;
		}

		$record = array_change_key_case( $record, CASE_LOWER );
		if ( array_key_exists( 'id', $record ) ) {
			$caching = \WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance();

			$caching->insert_cache_relation( $cache_id, $record['id'], $object_type );

			foreach ( $this->mappings as $key => $type ) {
				if ( isset( $record[ $key ] ) && is_array( $record[ $key ] ) && count( $record[ $key ] ) ) {
					$this->process_recursive_cache_relations( $cache_id, $type, $record[ $key ] );
				}
			}

			if ( array_key_exists( 'taxonomies', $record ) ) {
				foreach ( $record['taxonomies'] as $taxonomy => $items ) {
					$this->process_recursive_cache_relations( $cache_id, $taxonomy, $items );
				}
			}

			if ( array_key_exists( 'connected', $record ) ) {
				foreach ( $record['connected'] as $type => $items ) {
					$this->process_recursive_cache_relations( $cache_id, $type, $items );
				}
			}
		} else {
			foreach ( $record as $subrecord ) {
				$this->process_recursive_cache_relations( $cache_id, $object_type, $subrecord );
			}
		}
	}
}
