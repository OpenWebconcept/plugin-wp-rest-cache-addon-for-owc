<?php
/**
 * Abstract class for caching OWC endpoints.
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
 * Abstract class for caching OWC endpoints.
 *
 * @package    WPRC_OWC
 * @subpackage WPRC_OWC/Includes/Caching
 * @author:    Richard Korthuis <richardkorthuis@acato.nl>
 */
abstract class Owc_Caching {

	/**
	 * The REST base for OWC endpoints.
	 *
	 * @access private
	 * @var    string $rest_base REST base for OWC endpoints.
	 */
	protected $rest_base;

	/**
	 * An array of known OWC endpoints.
	 *
	 * @access private
	 * @var    array<string,array<int,string>> $owc_endpoints An array of known OWC endpoints.
	 */
	protected $owc_endpoints = [];

	/**
	 * An array of OWC endpoints that should not be cached.
	 *
	 * @access private
	 * @var    array<string,array<int,string>> $disallowed_owc_endpoints An array of OWC endpoints that should not be cached.
	 */
	protected $disallowed_owc_endpoints = [];

	/**
	 * An array mapping endpoint keys to post-types.
	 *
	 * @access private
	 * @var    array<string,string> $mappings An array mapping endpoint keys to post-types.
	 */
	protected $mappings = [];

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->setup();

		add_filter( 'wp_rest_cache/allowed_endpoints', [ $this, 'add_owc_endpoints' ], 10, 1 );
		add_filter( 'wp_rest_cache/disallowed_endpoints', [ $this, 'disallow_owc_endpoints' ], 10, 1 );
		add_filter( 'wp_rest_cache/determine_object_type', [ $this, 'determine_object_type' ], 10, 4 );
		add_action( 'wp_rest_cache/process_cache_relations', [ $this, 'process_cache_relations' ], 10, 4 );
	}

	/**
	 * Set up the variables for OWC caching.
	 *
	 * @return void
	 */
	abstract protected function setup();

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
	abstract public function process_cache_relations( $cache_id, $data, $object_type, $uri );

	/**
	 * Get the REST base for OWC endpoints.
	 *
	 * @return string REST base for OWC endpoints
	 */
	protected function get_rest_base() {
		return $this->rest_base;
	}

	/**
	 * Get an array of known OWC endpoints that should be cached.
	 *
	 * @return array<string,array<int,string>> An array of known OWC endpoints.
	 */
	protected function get_owc_endpoints() {
		return $this->owc_endpoints;
	}

	/**
	 * Get an array of OWC endpoints that should not be cached
	 *
	 * @return array<string,array<int,string>> An array of OWC endpoints that should not be cached.
	 */
	protected function get_disallowed_owc_endpoints() {
		return $this->disallowed_owc_endpoints;
	}

	/**
	 * Get an array mapping endpoint keys to post-types.
	 *
	 * @return array<string,string> An array mapping endpoint keys to post-types.
	 */
	protected function get_mappings() {
		return $this->mappings;
	}

	/**
	 * Filter which REST endpoints should be cached.
	 *
	 * @param array<string,array<int,string>> $allowed_endpoints An array of allowed endpoints.
	 *
	 * @return array<string,array<int,string>> An array of allowed endpoints.
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
	 * @param array<string,array<int,string>> $disallowed_endpoints An array of disallowed endpoints.
	 *
	 * @return array<string,array<int,string>> An array of disallowed endpoints.
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
		$uri_parts = wp_parse_url( $uri );
		if ( ! isset( $uri_parts['path'] ) ) {
			return $type;
		}
		$request_path = rtrim( $uri_parts['path'], '/' );

		// Make sure we only apply to allowed api calls.
		$rest_prefix = sprintf( '/%s/%s/', get_option( 'wp_rest_cache_rest_prefix', 'wp-json' ), $this->rest_base );
		if ( strpos( $request_path, $rest_prefix ) === false ) {
			return $type;
		}

		$rest_path       = substr( $request_path, strlen( $rest_prefix ) );
		$rest_path_parts = explode( '/', $rest_path );

		if ( isset( $this->mappings[ $rest_path_parts[0] ] ) ) {
			return $this->mappings[ $rest_path_parts[0] ];
		}

		return $type;
	}

	/**
	 * Process all cache relations for the current cache record.
	 *
	 * @param int    $cache_id The row id of the current cache.
	 * @param mixed  $data The data that is to be cached.
	 * @param string $object_type Object type.
	 *
	 * @return void
	 */
	public function process_default_cache_relations( $cache_id, $data, $object_type ) {
		if ( ! isset( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return;
		}

		$this->process_recursive_cache_relations( $cache_id, $object_type, $data['data'] );
	}

	/**
	 * Process all cache relations recursively.
	 *
	 * @param int          $cache_id The row id of the current cache.
	 * @param string       $object_type Object type.
	 * @param array<mixed> $record An array of data for which the relations need to be determined.
	 *
	 * @return void
	 */
	private function process_recursive_cache_relations( $cache_id, $object_type, $record ) {
		if ( ! is_array( $record ) ) {
			return;
		}

		$record = array_change_key_case( $record, CASE_LOWER );
		if ( array_key_exists( 'id', $record ) ) {
			$caching = Caching::get_instance();

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
					$connection = explode( '_to_', $type );
					if ( 2 === count( $connection ) ) {
						if ( $object_type === $connection[1] ) {
							$this->process_recursive_cache_relations( $cache_id, $connection[0], $items );
						} elseif ( $object_type === $connection[0] ) {
							$this->process_recursive_cache_relations( $cache_id, $connection[1], $items );
						}
					} elseif ( post_type_exists( $type ) || taxonomy_exists( $type ) ) {
						$this->process_recursive_cache_relations( $cache_id, $type, $items );
					}
				}
			}
		} else {
			foreach ( $record as $subrecord ) {
				$this->process_recursive_cache_relations( $cache_id, $object_type, $subrecord );
			}
		}
	}
}
