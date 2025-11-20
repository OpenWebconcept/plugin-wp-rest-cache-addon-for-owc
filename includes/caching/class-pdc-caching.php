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
		if ( class_exists( 'OWC\PDC\Base\RestAPI\Controllers\SettingsController' ) ) {
			if ( ! isset( $this->owc_endpoints[ $this->rest_base ] ) ) {
				$this->owc_endpoints[ $this->rest_base ] = [];
			}
			$this->owc_endpoints[ $this->rest_base ][] = 'settings';
			$this->mappings['settings']                = 'pdc-settings';

			add_action( 'update_option__owc_pdc_base_settings', [ $this, 'clear_settings_cache' ] );
		}

		// Delete cache relations on post save of specific post types.
		add_action( 'save_post', [ $this, 'delete_cache_relations_on_post_save' ], 10, 3 );
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
			parent::process_default_cache_relations( $cache_id, $data, $object_type );
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

	/**
	 * Clear the settings cache.
	 *
	 * @return void
	 */
	public function clear_settings_cache() {
		\WP_Rest_Cache_Plugin\Includes\Caching\Caching::get_instance()->delete_object_type_caches( 'pdc-settings' );
	}

	/**
	 * Delete cache relations when a post is saved.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool     $update Whether this is an existing post being updated or not.
	 *
	 * @return void
	 */
	public function delete_cache_relations_on_post_save( $post_id, $post, $update ) {
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// Check if the saved post is a new post or an update. The $update parameter isn't doing what it's supposed to be doing,
		// because of the auto-drafts that WordPress is creating.
		// Therefore, $update also returns true on new posts, therefore we check the post date.
		$post_date    = strtotime( $post->post_date );
		$current_time = time();
		$new_post     = false;
		if ( ( $current_time - $post_date ) < 10 ) {
			// New post, because the post date is within the last 10 seconds.
			$new_post = true;
		}

		// Only proceed if it's a new post.
		if ( ! $new_post ) {
			return;
		}

		$caching           = Caching::get_instance();
		$pdc_subcategories = [];

		$post_type = get_post_type( $post_id );
		switch ( $post_type ) {
			case 'pdc-item':
				// Check the related pdc-categories and pdc-subcategories.
				$pdc_categories    = $this->get_related_posts( $post_id, 'pdc-item_to_pdc-category' );
				$pdc_subcategories = $this->get_related_posts( $post_id, 'pdc-item_to_pdc-subcategory' );
				break;
			case 'pdc-subcategory':
				// Check the related pdc-categories.
				$pdc_categories = $this->get_related_posts( $post_id, 'pdc-category_to_pdc-subcategory' );
				break;
			default:
				// do nothing.
				return;
		}

		// Delete caches for related categories and subcategories.
		if ( ! empty( $pdc_categories ) && is_array( $pdc_categories ) ) {
			foreach ( $pdc_categories as $category ) {
				$caching->delete_related_caches( $category->post_id, 'pdc-category' );
			}
		}

		if ( ! empty( $pdc_subcategories ) && is_array( $pdc_subcategories ) ) {
			foreach ( $pdc_subcategories as $subcategory ) {
				$caching->delete_related_caches( $subcategory->post_id, 'pdc-subcategory' );
			}
		}
	}

	/**
	 * Get related posts via p2p connections.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $relation_type Relation type.
	 *
	 * @return array An array of related post IDs.
	 */
	private function get_related_posts( $post_id, $relation_type ) {
		switch ( $relation_type ) {
			case 'pdc-category_to_pdc-subcategory':
				// We need to get the pdc-categories that are connected to this subcategory, therefore we select p2p_from where p2p_to = $post_id.
				$select_column = 'p2p_from';
				$where_column  = 'p2p_to';
				break;
			case 'pdc-item_to_pdc-subcategory':
			case 'pdc-item_to_pdc-category':
				// We need to get the pdc-subcategories or pdc-categories that are connected to this pdc item, therefore we select p2p_to where p2p_from = $post_id.
				$select_column = 'p2p_to';
				$where_column  = 'p2p_from';
				break;
			default:
				return [];
		}

		global $wpdb;

		$related_post_ids = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $select_column and $where_column are controlled.
				"SELECT {$select_column} AS `post_id` FROM {$wpdb->prefix}p2p WHERE {$where_column} = %d AND p2p_type = %s",
				$post_id,
				$relation_type
			)
		);

		return $related_post_ids;
	}
}
