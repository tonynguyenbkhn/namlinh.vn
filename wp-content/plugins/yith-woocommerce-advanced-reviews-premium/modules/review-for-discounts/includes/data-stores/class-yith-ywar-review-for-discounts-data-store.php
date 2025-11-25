<?php
/**
 * Class YITH_YWAR_Review_For_Discounts_Data_Store
 * Data store for Discounts
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\DataStores
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Class YITH_YWAR_Review_For_Discounts_Data_Store
 *
 * @since   2.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\DataStores
 */
class YITH_YWAR_Review_For_Discounts_Data_Store extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Meta keys and how they transfer to CRUD props.
	 *
	 * @var array
	 */
	protected $meta_keys_to_props = array(
		'ywar_discount_trigger'                     => 'trigger',
		'ywar_discount_trigger_product_ids'         => 'trigger_product_ids',
		'ywar_discount_trigger_product_categories'  => 'trigger_product_categories',
		'ywar_discount_trigger_threshold'           => 'trigger_threshold',
		'ywar_discount_trigger_enable_notify'       => 'trigger_enable_notify',
		'ywar_discount_trigger_threshold_notify'    => 'trigger_threshold_notify',
		'ywar_discount_discount_type'               => 'discount_type',
		'ywar_discount_amount'                      => 'amount',
		'ywar_discount_free_shipping'               => 'free_shipping',
		'ywar_discount_expiry_days'                 => 'expiry_days',
		'ywar_discount_funds_amount'                => 'funds_amount',
		'ywar_discount_minimum_amount'              => 'minimum_amount',
		'ywar_discount_maximum_amount'              => 'maximum_amount',
		'ywar_discount_individual_use'              => 'individual_use',
		'ywar_discount_exclude_sale_items'          => 'exclude_sale_items',
		'ywar_discount_product_ids'                 => 'product_ids',
		'ywar_discount_excluded_product_ids'        => 'excluded_product_ids',
		'ywar_discount_product_categories'          => 'product_categories',
		'ywar_discount_excluded_product_categories' => 'excluded_product_categories',
	);

	/**
	 * Data stored in meta keys, but not considered "meta".
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'ywar_discount_trigger',
		'ywar_discount_trigger_product_ids',
		'ywar_discount_trigger_product_categories',
		'ywar_discount_trigger_threshold',
		'ywar_discount_trigger_enable_notify',
		'ywar_discount_trigger_threshold_notify',
		'ywar_discount_discount_type',
		'ywar_discount_amount',
		'ywar_discount_free_shipping',
		'ywar_discount_expiry_days',
		'ywar_discount_funds_amount',
		'ywar_discount_minimum_amount',
		'ywar_discount_maximum_amount',
		'ywar_discount_individual_use',
		'ywar_discount_exclude_sale_items',
		'ywar_discount_product_ids',
		'ywar_discount_excluded_product_ids',
		'ywar_discount_product_categories',
		'ywar_discount_excluded_product_categories',
	);

	/**
	 * Stores updated props.
	 *
	 * @var array
	 */
	protected $updated_props = array();

	/**
	 * YITH_YWAR_Review_For_Discounts_Data_Store constructor.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function __construct() {
		if ( is_callable( array( parent::class, '__construct' ) ) ) {
			parent::__construct();
		}

		$this->internal_meta_keys = apply_filters( 'yith_ywar_discount_data_store_internal_meta_keys', $this->internal_meta_keys, $this );
	}

	/**
	 * Create
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function create( &$discount ) {

		$id = wp_insert_post(
			apply_filters(
				'yith_ywar_new_discount_data',
				array(
					'post_type'   => YITH_YWAR_Post_Types::DISCOUNTS,
					'post_status' => 'publish',
					'post_title'  => $discount->get_title(),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$discount->set_id( $id );

			$this->update_post_meta( $discount, true );
			$this->handle_updated_props( $discount );
			$this->clear_caches( $discount );

			$discount->save_meta_data();
			$discount->apply_changes();

			do_action( 'yith_ywar_discount_created', $discount );
		}
	}

	/**
	 * Read
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount The Review.
	 *
	 * @return void
	 * @throws Exception If passed discount is invalid.
	 * @since  2.0.0
	 */
	public function read( &$discount ) {
		$discount->set_defaults();
		$post_object = get_post( $discount->get_id() );
		if ( ! $discount->get_id() || ! $post_object || YITH_YWAR_Post_Types::DISCOUNTS !== $post_object->post_type ) {
			throw new Exception(
				esc_html_x( 'Invalid discount.', '[Error log] Error message', 'yith-woocommerce-advanced-reviews' ) .
				esc_html(
					print_r( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
						array(
							'id'                      => $discount->get_id(),
							'$post_object'            => $post_object,
							'$post_object->post_type' => ! ! $post_object ? $post_object->post_type : false,
						),
						true
					)
				)
			);
		}

		$discount->set_props(
			array(
				'title' => $post_object->post_title,
			)
		);

		$this->read_discount_data( $discount );
		$discount->set_object_read( true );

		do_action( 'yith_ywar_discount_read', $discount );
	}

	/**
	 * Update
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function update( &$discount ) {
		$discount->save_meta_data();
		$changes = $discount->get_changes();

		// Only update the post when the post data changes.
		if ( array_intersect( array( 'title' ), array_keys( $changes ) ) ) {
			$post_data = array(
				'post_type' => YITH_YWAR_Post_Types::DISCOUNTS,
			);

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $discount->get_id() ) );
				clean_post_cache( $discount->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $discount->get_id() ), $post_data ) );
			}
			$discount->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		}

		$special_post_props = array( 'title' );
		foreach ( $special_post_props as $prop ) {
			if ( in_array( $prop, array_keys( $changes ), true ) ) {
				$this->updated_props[] = $prop;
			}
		}

		$this->update_post_meta( $discount );
		$this->handle_updated_props( $discount );
		$this->clear_caches( $discount );

		$discount->apply_changes();

		do_action( 'yith_ywar_discount_updated', $discount );
	}

	/**
	 * Delete
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount The Review.
	 * @param array                                   $args     Arguments.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	public function delete( &$discount, $args = array() ) {
		$id = $discount->get_id();

		if ( ! $id ) {
			return;
		}

		// We don't need to clear product data cache here, since it's done when deleting/trashing the post.
		do_action( 'yith_ywar_before_delete_discount', $id, $discount );
		wp_delete_post( $id );
		$discount->set_id( 0 );
		do_action( 'yith_ywar_delete_discount', $id );
	}

	/**
	 * Read discount data.
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount The Review.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function read_discount_data( &$discount ) {
		$id               = $discount->get_id();
		$post_meta_values = get_post_meta( $id );
		$set_props        = array();

		foreach ( $this->meta_keys_to_props as $meta_key => $prop ) {
			$meta_value         = $post_meta_values[ $meta_key ][0] ?? null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value ); // get_post_meta only un-serializes single values.
		}

		$discount->set_props( $set_props );

		do_action( 'yith_ywar_discount_data_store_read_data', $discount, $this );
	}

	/**
	 * Helper method that updates all the post meta.
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount Discount object.
	 * @param bool                                    $force    Force update. Used during create.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function update_post_meta( &$discount, $force = false ) {
		$meta_key_to_props = $this->meta_keys_to_props;
		$props_to_update   = $force ? $meta_key_to_props : $this->get_props_to_update( $discount, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $discount->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'trigger_enable_notify':
				case 'free_shipping':
				case 'individual_use':
				case 'exclude_sale_items':
					$value = wc_bool_to_string( $value );
					break;
			}

			$updated = update_post_meta( $discount->get_id(), $meta_key, $value );

			if ( $updated ) {
				$this->updated_props[] = $prop;
			}
		}

		/**
		 * This filter allows third-party plugins (and plugin modules) to update custom props.
		 * Important: you MUST add the props you updated to the first param.
		 */
		$extra_updated_props = apply_filters( 'yith_ywar_discount_data_store_update_props', array(), $discount, $force, $this );
		if ( $extra_updated_props ) {
			$this->updated_props = array_merge( $this->updated_props, $extra_updated_props );
		}
	}

	/**
	 * Handle updated meta props after updating meta data.
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount Product Object.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function handle_updated_props( &$discount ) {
		// Trigger action so 3rd parties can deal with updated props.
		do_action( 'yith_ywar_discount_data_store_updated_props', $discount, $this->updated_props );

		// After handling, we can reset the props array.
		$this->updated_props = array();
	}

	/**
	 * Clear any caches.
	 *
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount Discount object.
	 *
	 * @return void
	 * @since  2.0.0
	 */
	protected function clear_caches( &$discount ) {
		do_action( 'yith_ywar_discount_data_store_clear_caches', $discount, $this );
	}
}
