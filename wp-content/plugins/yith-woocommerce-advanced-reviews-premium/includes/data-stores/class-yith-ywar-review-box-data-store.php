<?php
/**
 * Class YITH_YWAR_Review_Box_Data_Store
 * Data store for Discounts
 *
 * @package YITH\AdvancedReviews\DataStores
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Class YITH_YWAR_Review_Box_Data_Store
 *
 * @since   2.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\AdvancedReviews\DataStores
 */
class YITH_YWAR_Review_Box_Data_Store extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Meta keys and how they transfer to CRUD props.
	 *
	 * @var array
	 */
	protected $meta_keys_to_props = array(
		'ywar_review_box_show_on'               => 'show_on',
		'ywar_review_box_tag_ids'               => 'tag_ids',
		'ywar_review_box_category_ids'          => 'category_ids',
		'ywar_review_box_product_ids'           => 'product_ids',
		'ywar_review_box_active'                => 'active',
		'ywar_review_box_enable_multi_criteria' => 'enable_multi_criteria',
		'ywar_review_box_multi_criteria'        => 'multi_criteria',
		'ywar_review_box_show_elements'         => 'show_elements',
	);

	/**
	 * Data stored in meta keys, but not considered "meta".
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'ywar_review_box_show_on',
		'ywar_review_box_tag_ids',
		'ywar_review_box_category_ids',
		'ywar_review_box_product_ids',
		'ywar_review_box_active',
		'ywar_review_box_enable_multi_criteria',
		'ywar_review_box_multi_criteria',
		'ywar_review_box_show_elements',
	);

	/**
	 * Stores updated props.
	 *
	 * @var array
	 */
	protected $updated_props = array();

	/**
	 * YITH_YWAR_Review_Box_Data_Store constructor.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function __construct() {
		if ( is_callable( array( parent::class, '__construct' ) ) ) {
			parent::__construct();
		}

		$this->internal_meta_keys = apply_filters( 'yith_ywar_review_box_data_store_internal_meta_keys', $this->internal_meta_keys, $this );
	}

	/**
	 * Create
	 *
	 * @param YITH_YWAR_Review_Box $review_box The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function create( &$review_box ) {

		$id = wp_insert_post(
			apply_filters(
				'yith_ywar_new_review_box_data',
				array(
					'post_type'   => YITH_YWAR_Post_Types::BOXES,
					'post_status' => 'publish',
					'post_title'  => $review_box->get_title(),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$review_box->set_id( $id );

			$this->update_post_meta( $review_box, true );
			$this->handle_updated_props( $review_box );
			$this->clear_caches( $review_box );

			$review_box->save_meta_data();
			$review_box->apply_changes();

			do_action( 'yith_ywar_review_box_created', $review_box );
		}
	}

	/**
	 * Read
	 *
	 * @param YITH_YWAR_Review_Box $review_box The Review.
	 *
	 * @return void
	 * @throws Exception If passed review_box is invalid.
	 * @since  2.0.0
	 */
	public function read( &$review_box ) {
		$review_box->set_defaults();
		$post_object = get_post( $review_box->get_id() );
		if ( ! $review_box->get_id() || ! $post_object || YITH_YWAR_Post_Types::BOXES !== $post_object->post_type ) {
			throw new Exception(
				esc_html_x( 'Invalid review box.', '[Error log] Error message', 'yith-woocommerce-advanced-reviews' ) .
				esc_html(
					print_r( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
						array(
							'id'                      => $review_box->get_id(),
							'$post_object'            => $post_object,
							'$post_object->post_type' => ! ! $post_object ? $post_object->post_type : false,
						),
						true
					)
				)
			);
		}

		$review_box->set_props(
			array(
				'title' => $post_object->post_title,
			)
		);

		$this->read_review_box_data( $review_box );
		$review_box->set_object_read( true );

		do_action( 'yith_ywar_review_box_read', $review_box );
	}

	/**
	 * Update
	 *
	 * @param YITH_YWAR_Review_Box $review_box The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function update( &$review_box ) {
		$review_box->save_meta_data();
		$changes = $review_box->get_changes();

		// Only update the post when the post data changes.
		if ( array_intersect( array( 'title' ), array_keys( $changes ) ) ) {
			$post_data = array(
				'post_type'  => YITH_YWAR_Post_Types::BOXES,
				'post_title' => $review_box->get_title( 'edit' ),
			);

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $review_box->get_id() ) );
				clean_post_cache( $review_box->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $review_box->get_id() ), $post_data ) );
			}
			$review_box->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		}

		$special_post_props = array( 'title' );
		foreach ( $special_post_props as $prop ) {
			if ( in_array( $prop, array_keys( $changes ), true ) ) {
				$this->updated_props[] = $prop;
			}
		}

		$this->update_post_meta( $review_box );
		$this->handle_updated_props( $review_box );
		$this->clear_caches( $review_box );

		$review_box->apply_changes();

		do_action( 'yith_ywar_review_box_updated', $review_box );
	}

	/**
	 * Delete
	 *
	 * @param YITH_YWAR_Review_Box $review_box The Review.
	 * @param array                $args       Arguments.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function delete( &$review_box, $args = array() ) {
		$id = $review_box->get_id();

		if ( ! $id ) {
			return;
		}

		// We don't need to clear product data cache here, since it's done when deleting/trashing the post.
		do_action( 'yith_ywar_before_delete_review_box', $id, $review_box );
		wp_delete_post( $id );
		$review_box->set_id( 0 );
		do_action( 'yith_ywar_delete_review_box', $id );
	}

	/**
	 * Read review_box data.
	 *
	 * @param YITH_YWAR_Review_Box $review_box The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function read_review_box_data( &$review_box ) {
		$id               = $review_box->get_id();
		$post_meta_values = get_post_meta( $id );
		$set_props        = array();

		foreach ( $this->meta_keys_to_props as $meta_key => $prop ) {
			$meta_value         = $post_meta_values[ $meta_key ][0] ?? null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value ); // get_post_meta only un-serializes single values.
		}

		$review_box->set_props( $set_props );

		do_action( 'yith_ywar_review_box_data_store_read_data', $review_box, $this );
	}

	/**
	 * Helper method that updates all the post meta.
	 *
	 * @param YITH_YWAR_Review_Box $review_box Discount object.
	 * @param bool                 $force      Force update. Used during create.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function update_post_meta( &$review_box, $force = false ) {
		$meta_key_to_props = $this->meta_keys_to_props;
		$props_to_update   = $force ? $meta_key_to_props : $this->get_props_to_update( $review_box, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $review_box->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'active':
				case 'enable_multi_criteria':
					$value = wc_bool_to_string( $value );
					break;
			}

			$updated = update_post_meta( $review_box->get_id(), $meta_key, $value );

			if ( $updated ) {
				$this->updated_props[] = $prop;
			}
		}

		/**
		 * This filter allows third-party plugins (and plugin modules) to update custom props.
		 * Important: you MUST add the props you updated to the first param.
		 */
		$extra_updated_props = apply_filters( 'yith_ywar_review_box_data_store_update_props', array(), $review_box, $force, $this );
		if ( $extra_updated_props ) {
			$this->updated_props = array_merge( $this->updated_props, $extra_updated_props );
		}
	}

	/**
	 * Handle updated meta props after updating meta data.
	 *
	 * @param YITH_YWAR_Review_Box $review_box Product Object.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function handle_updated_props( &$review_box ) {
		// Trigger action so 3rd parties can deal with updated props.
		do_action( 'yith_ywar_review_box_data_store_updated_props', $review_box, $this->updated_props );

		// After handling, we can reset the props array.
		$this->updated_props = array();
	}

	/**
	 * Clear any caches.
	 *
	 * @param YITH_YWAR_Review_Box $review_box Discount object.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function clear_caches( &$review_box ) {
		do_action( 'yith_ywar_review_box_data_store_clear_caches', $review_box, $this );
	}

	/**
	 * Query for review boxes.
	 *
	 * @param array $args Arguments.
	 *
	 * @return YITH_YWAR_Review_Box[]|object
	 * @since  2.0.0
	 */
	public function query( array $args ) {
		$args = $this->get_wp_query_args( $args );

		if ( ! empty( $args['errors'] ) ) {
			$query = (object) array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			);
		} else {
			$query = new WP_Query( $args );
		}

		if ( isset( $args['fields'] ) && 'ids' === $args['fields'] ) {
			$results = $query->posts;
		} else {

			update_post_caches( $query->posts ); // We already fetching posts, might as well hydrate some caches.
			$boxes_ids = wp_list_pluck( $query->posts, 'ID' );
			$boxes     = array();
			if ( ! empty( $boxes_ids ) ) {
				foreach ( $query->posts as $post ) {
					$box = yith_ywar_get_review_box( $post );

					// If the review returns false, don't add it to the list.
					if ( false === $box ) {
						continue;
					}

					$boxes[] = $box;
				}
			}
			$results = $boxes;

		}

		if ( isset( $args['paginate'] ) && $args['paginate'] ) {
			return (object) array(
				'reviews'       => $results,
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			);
		}

		return $results;
	}

	/**
	 * Get valid WP_Query args from a WC_Object_Query's query variables.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 * @since  2.0.0
	 */
	protected function get_wp_query_args( $args ): array {

		$args['post_type'] = YITH_YWAR_Post_Types::BOXES;

		$wp_query_args = parent::get_wp_query_args( $args );

		$meta_query = $wp_query_args['meta_query'] ?? array();

		if ( isset( $args['meta_query'] ) ) {
			$meta_query = array_merge( $meta_query, $args['meta_query'] );
		}

		if ( $meta_query ) {
			$wp_query_args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		return $wp_query_args;
	}
}
