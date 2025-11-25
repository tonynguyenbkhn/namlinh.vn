<?php
/**
 * Class YITH_YWAR_Review_Data_Store
 * Data store for Reviews
 *
 * @package YITH\AdvancedReviews\DataStores
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Class YITH_YWAR_Review_Data_Store
 *
 * @since   2.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\AdvancedReviews\DataStores
 */
class YITH_YWAR_Review_Data_Store extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Meta keys and how they transfer to CRUD props.
	 *
	 * @var array
	 */
	protected $meta_keys_to_props = array(
		'_ywar_comment_id'                  => 'comment_id',
		'_ywar_parent_comment_id'           => 'parent_comment_id',
		'_ywar_rating'                      => 'rating',
		'_ywar_multi_rating'                => 'multi_rating',
		'_ywar_product_id'                  => 'product_id',
		'_ywar_votes'                       => 'votes',
		'_ywar_upvotes_count'               => 'upvotes_count',
		'_ywar_downvotes_count'             => 'downvotes_count',
		'_ywar_inappropriate_list'          => 'inappropriate_list',
		'_ywar_inappropriate_count'         => 'inappropriate_count',
		'_ywar_helpful'                     => 'helpful',
		'_ywar_featured'                    => 'featured',
		'_ywar_verified_owner'              => 'verified_owner',
		'_ywar_stop_reply'                  => 'stop_reply',
		'_ywar_in_reply_of'                 => 'in_reply_of',
		'_ywar_review_user_id'              => 'review_user_id',
		'_ywar_review_author'               => 'review_author',
		'_ywar_review_author_email'         => 'review_author_email',
		'_ywar_review_author_custom_avatar' => 'review_author_custom_avatar',
		'_ywar_review_author_IP'            => 'review_author_IP',
		'_ywar_review_author_country'       => 'review_author_country',
		'_ywar_review_edit_blocked'         => 'review_edit_blocked',
		'_ywar_thumb_ids'                   => 'thumb_ids',
		'_ywar_guest_cookie'                => 'guest_cookie',
	);

	/**
	 * Data stored in meta keys, but not considered "meta".
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_ywar_comment_id',
		'_ywar_parent_comment_id',
		'_ywar_rating',
		'_ywar_multi_rating',
		'_ywar_product_id',
		'_ywar_votes',
		'_ywar_upvotes_count',
		'_ywar_downvotes_count',
		'_ywar_inappropriate_list',
		'_ywar_inappropriate_count',
		'_ywar_helpful',
		'_ywar_featured',
		'_ywar_verified_owner',
		'_ywar_stop_reply',
		'_ywar_in_reply_of',
		'_ywar_review_user_id',
		'_ywar_review_author',
		'_ywar_review_author_email',
		'_ywar_review_author_custom_avatar',
		'_ywar_review_author_IP',
		'_ywar_review_author_country',
		'_ywar_review_edit_blocked',
		'_ywar_thumb_ids',
		'_ywar_guest_cookie',
		'_edit_last',
		'_edit_lock',
	);

	/**
	 * Stores updated props.
	 *
	 * @var array
	 */
	protected $updated_props = array();

	/**
	 * YITH_YWAR_Review_Data_Store constructor.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function __construct() {
		if ( is_callable( array( parent::class, '__construct' ) ) ) {
			parent::__construct();
		}

		$this->internal_meta_keys = apply_filters( 'yith_ywar_review_data_store_internal_meta_keys', $this->internal_meta_keys, $this );
	}

	/**
	 * Create
	 *
	 * @param YITH_YWAR_Review $review The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function create( &$review ) {
		if ( ! $review->get_date_created( 'edit' ) ) {
			$review->set_date_created( time() );
		}

		$id = wp_insert_post(
			apply_filters(
				'yith_ywar_new_review_data',
				array(
					'post_type'     => YITH_YWAR_Post_Types::REVIEWS,
					'post_status'   => $this->validate_review_status( $review->get_status() ),
					'post_title'    => $review->get_title(),
					'post_date'     => gmdate( 'Y-m-d H:i:s', $review->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $review->get_date_created( 'edit' )->getTimestamp() ),
					'post_content'  => $review->get_content(),
					'post_parent'   => $review->get_post_parent(),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$review->set_id( $id );

			$this->update_post_meta( $review, true );
			$this->handle_updated_props( $review );
			$this->clear_caches( $review );

			$review->save_meta_data();
			$review->apply_changes();

			/**
			 * DO_ACTION: yith_ywar_review_created
			 *
			 * Adds an action when the review is created.
			 *
			 * @param YITH_YWAR_Review $review The current review.
			 */
			do_action( 'yith_ywar_review_created', $review );

			/**
			 * DO_ACTION: yith_ywar_new_review
			 *
			 * Adds an action when the review is created (Useful for mail purposes).
			 *
			 * @param int              $id     The current review ID.
			 * @param YITH_YWAR_Review $review The current review.
			 */
			do_action( 'yith_ywar_new_review', $id, $review );
		}
	}

	/**
	 * Read
	 *
	 * @param YITH_YWAR_Review $review The Review.
	 *
	 * @return void
	 * @throws Exception If passed review is invalid.
	 * @since  2.0.0
	 */
	public function read( &$review ) {
		$review->set_defaults();
		$post_object = get_post( $review->get_id() );
		if ( ! $review->get_id() || ! $post_object || YITH_YWAR_Post_Types::REVIEWS !== $post_object->post_type ) {
			throw new Exception(
				esc_html_x( 'Invalid review.', '[Error log] error message', 'yith-woocommerce-advanced-reviews' ) .
				esc_html(
					print_r( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
						array(
							'id'                      => $review->get_id(),
							'$post_object'            => $post_object,
							'$post_object->post_type' => ! ! $post_object ? $post_object->post_type : false,
						),
						true
					)
				)
			);
		}

		$review->set_props(
			array(
				'title'         => $post_object->post_title,
				'date_created'  => $this->string_to_timestamp( $post_object->post_date_gmt ),
				'date_modified' => $this->string_to_timestamp( $post_object->post_modified_gmt ),
				'status'        => $post_object->post_status,
				'content'       => $post_object->post_content,
				'post_parent'   => $post_object->post_parent,
			)
		);

		$this->read_review_data( $review );
		$review->set_object_read( true );

		do_action( 'yith_ywar_review_read', $review );
	}

	/**
	 * Update
	 *
	 * @param YITH_YWAR_Review $review The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function update( &$review ) {
		$review->save_meta_data();
		$changes = $review->get_changes();

		// Only update the post when the post data changes.
		if ( array_intersect( array( 'title', 'status', 'content', 'date_created', 'date_modified', 'post_parent' ), array_keys( $changes ) ) ) {
			$post_data = array(
				'post_type'    => YITH_YWAR_Post_Types::REVIEWS,
				'post_status'  => $this->validate_review_status( $review->get_status( 'edit' ) ),
				'post_title'   => $review->get_title( 'edit' ),
				'post_content' => $review->get_content( 'edit' ),
				'post_parent'  => $review->get_post_parent( 'edit' ),
			);
			if ( $review->get_date_created( 'edit' ) ) {
				$post_data['post_date']     = gmdate( 'Y-m-d H:i:s', $review->get_date_created( 'edit' )->getOffsetTimestamp() );
				$post_data['post_date_gmt'] = gmdate( 'Y-m-d H:i:s', $review->get_date_created( 'edit' )->getTimestamp() );
			}
			if ( isset( $changes['date_modified'] ) && $review->get_date_modified( 'edit' ) ) {
				$post_data['post_modified']     = gmdate( 'Y-m-d H:i:s', $review->get_date_modified( 'edit' )->getOffsetTimestamp() );
				$post_data['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', $review->get_date_modified( 'edit' )->getTimestamp() );
			} else {
				$post_data['post_modified']     = current_time( 'mysql' );
				$post_data['post_modified_gmt'] = current_time( 'mysql', 1 );
			}

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $review->get_id() ) );
				clean_post_cache( $review->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $review->get_id() ), $post_data ) );
			}
			$review->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		} else { // Only update post modified time to record this save event.
			$GLOBALS['wpdb']->update(
				$GLOBALS['wpdb']->posts,
				array(
					'post_modified'     => current_time( 'mysql' ),
					'post_modified_gmt' => current_time( 'mysql', 1 ),
				),
				array(
					'ID' => $review->get_id(),
				)
			);
			clean_post_cache( $review->get_id() );
		}

		$special_post_props = array( 'title', 'status', 'content', 'date_created', 'date_modified', 'post_parent' );
		foreach ( $special_post_props as $prop ) {
			if ( in_array( $prop, array_keys( $changes ), true ) ) {
				$this->updated_props[] = $prop;
			}
		}

		$this->update_post_meta( $review );
		$this->handle_updated_props( $review );
		$this->clear_caches( $review );

		$review->apply_changes();

		/**
		 * DO_ACTION: yith_ywar_review_updated
		 *
		 * Adds an action when the review is updated.
		 *
		 * @param YITH_YWAR_Review $review The current review.
		 */
		do_action( 'yith_ywar_review_updated', $review );

		/**
		 * DO_ACTION: yith_ywar_new_review
		 *
		 * Adds an action when the review is created (Useful for mail purposes).
		 *
		 * @param int              $id     The current review ID.
		 * @param YITH_YWAR_Review $review The current review.
		 */
		do_action( 'yith_ywar_update_review', $review->get_id(), $review );
	}

	/**
	 * Delete
	 *
	 * @param YITH_YWAR_Review $review The Review.
	 * @param array            $args   Arguments.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function delete( &$review, $args = array() ) {
		$id = $review->get_id();

		$args = wp_parse_args(
			$args,
			array(
				'force_delete' => false,
			)
		);

		if ( ! $id ) {
			return;
		}

		// We don't need to clear product data cache here, since it's done when deleting/trashing the post.
		if ( $args['force_delete'] ) {
			/**
			 * DO_ACTION: yith_ywar_before_delete_review
			 *
			 * Adds an action before review is deleted.
			 *
			 * @param int              $id     The current review ID.
			 * @param YITH_YWAR_Review $review The current review.
			 */
			do_action( 'yith_ywar_before_delete_review', $id, $review );
			wp_delete_post( $id );
			$review->set_id( 0 );
			/**
			 * DO_ACTION: yith_ywar_delete_review
			 *
			 * Adds an action after review is deleted.
			 *
			 * @param int $id The current review ID.
			 */
			do_action( 'yith_ywar_delete_review', $id );
		} else {
			wp_trash_post( $id );
			$review->set_status( 'trash' );
			/**
			 * DO_ACTION: yith_ywar_trash_review
			 *
			 * Adds an action before review is trashed.
			 *
			 * @param int              $id     The current review ID.
			 * @param YITH_YWAR_Review $review The current review.
			 */
			do_action( 'yith_ywar_trash_review', $id, $review );
		}
	}

	/**
	 * Read review data.
	 *
	 * @param YITH_YWAR_Review $review The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function read_review_data( &$review ) {
		$id               = $review->get_id();
		$post_meta_values = get_post_meta( $id );
		$set_props        = array();

		foreach ( $this->meta_keys_to_props as $meta_key => $prop ) {
			$meta_value         = $post_meta_values[ $meta_key ][0] ?? null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value ); // get_post_meta only un-serializes single values.
		}

		$review->set_props( $set_props );

		do_action( 'yith_ywar_review_data_store_read_data', $review, $this );
	}

	/**
	 * Helper method that updates all the post meta.
	 *
	 * @param YITH_YWAR_Review $review Review object.
	 * @param bool             $force  Force update. Used during create.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function update_post_meta( &$review, $force = false ) {
		$meta_key_to_props = $this->meta_keys_to_props;
		$props_to_update   = $force ? $meta_key_to_props : $this->get_props_to_update( $review, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $review->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'featured':
				case 'stop_reply':
				case 'helpful':
				case 'review_edit_blocked':
					$value = wc_bool_to_string( $value );
					break;
			}

			$updated = update_post_meta( $review->get_id(), $meta_key, $value );

			if ( $updated ) {
				$this->updated_props[] = $prop;
			}
		}

		/**
		 * This filter allows third-party plugins (and plugin modules) to update custom props.
		 * Important: you MUST add the props you updated to the first param.
		 */
		$extra_updated_props = apply_filters( 'yith_ywar_review_data_store_update_props', array(), $review, $force, $this );
		if ( $extra_updated_props ) {
			$this->updated_props = array_merge( $this->updated_props, $extra_updated_props );
		}
	}

	/**
	 * Handle updated meta props after updating meta data.
	 *
	 * @param YITH_YWAR_Review $review Product Object.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function handle_updated_props( &$review ) {
		// Trigger action so 3rd parties can deal with updated props.
		do_action( 'yith_ywar_review_data_store_updated_props', $review, $this->updated_props );

		// After handling, we can reset the props array.
		$this->updated_props = array();
	}

	/**
	 * Clear any caches.
	 *
	 * @param YITH_YWAR_Review $review Review object.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function clear_caches( &$review ) {
		do_action( 'yith_ywar_review_data_store_clear_caches', $review, $this );
	}

	/**
	 * Validate a review status
	 *
	 * @param string $status The status.
	 *
	 * @return string
	 * @since  2.0.0
	 */
	protected function validate_review_status( string $status ): string {
		$status = ! ! $status ? $status : 'pending';
		$status = 'ywar-' === substr( $status, 0, 5 ) ? substr( $status, 5 ) : $status;
		if ( yith_ywar_is_a_review_status( $status ) ) {
			$status = 'ywar-' . $status;
		} elseif ( 'trash' !== $status ) {
			$status = 'ywar-pending';
		}

		return $status;
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

		$args['post_type'] = YITH_YWAR_Post_Types::REVIEWS;

		if ( ! isset( $args['post_status'] ) || ( isset( $args['post_status'] ) && 'all' === $args['post_status'] ) ) {
			$args['post_status'] = array( 'ywar-pending', 'ywar-reported', 'ywar-approved', 'ywar-spam' );
		}
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

	/**
	 * Query for reviews matching specific criteria.
	 *
	 * @param array $args Arguments.
	 *
	 * @return YITH_YWAR_Review[]|object
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
			$review_ids = wp_list_pluck( $query->posts, 'ID' );
			$reviews    = array();
			if ( ! empty( $review_ids ) ) {
				foreach ( $query->posts as $post ) {
					$review = yith_ywar_get_review( $post );

					// If the review returns false, don't add it to the list.
					if ( false === $review ) {
						continue;
					}

					$reviews[] = $review;
				}
			}
			$results = $reviews;

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
}
