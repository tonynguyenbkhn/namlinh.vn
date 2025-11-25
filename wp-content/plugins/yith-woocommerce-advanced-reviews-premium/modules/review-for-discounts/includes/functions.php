<?php
/**
 * Module functions
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Retrieve coupon data.
 *
 * @param int|WP_Post|YITH_YWAR_Review_For_Discounts_Discount|false $discount The review.
 *
 * @return YITH_YWAR_Review_For_Discounts_Discount|false
 * @since  2.0.0
 */
function yith_ywar_get_discount( $discount = false ) {

	global $post;

	if ( false === $discount && is_a( $post, 'WP_Post' ) && get_post_type( $post ) === YITH_YWAR_Post_Types::DISCOUNTS ) {
		$discount_id = absint( $post->ID );
	} elseif ( is_numeric( $discount ) ) {
		$discount_id = $discount;
	} elseif ( $discount instanceof YITH_YWAR_Review_For_Discounts_Discount ) {
		$discount_id = $discount->get_id();
	} elseif ( ! empty( $discount->ID ) ) {
		$discount_id = $discount->ID;
	} else {
		$discount_id = false;
	}

	if ( ! $discount_id ) {
		return false;
	}

	try {
		$discount = new YITH_YWAR_Review_For_Discounts_Discount( $discount_id );

		return apply_filters( 'yith_ywar_discount_object', $discount );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );

		return false;
	}
}

/**
 * Create the coupon.
 *
 * @param YITH_YWAR_Review_For_Discounts_Discount $discount The discount.
 * @param string                                  $nickname The user nickname.
 * @param string                                  $email    The user email.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_create_coupon( YITH_YWAR_Review_For_Discounts_Discount $discount, string $nickname, string $email ): string {

	$coupon_code = uniqid( "$nickname-" );

	// Set coupon expiration date.
	$expiry_date = '';
	if ( $discount->get_expiry_days() > 0 && ! empty( $discount->get_expiry_days() ) ) {
		$expiry_date = gmdate( 'Y-m-d', strtotime( '+' . $discount->get_expiry_days() . ' days' . yith_ywar_get_time_offset() ) );
	}

	add_filter( 'woocommerce_get_shop_coupon_data', '__return_false', 100 );
	$coupon = new WC_Coupon();
	remove_filter( 'woocommerce_get_shop_coupon_data', '__return_false', 100 );

	$coupon->set_code( $coupon_code );
	$coupon->set_discount_type( $discount->get_discount_type() );
	$coupon->set_amount( $discount->get_amount() );
	$coupon->set_free_shipping( 'yes' === $discount->get_free_shipping() );
	$coupon->set_individual_use( 'yes' === $discount->get_individual_use() );
	$coupon->set_product_ids( $discount->get_product_ids() );
	$coupon->set_excluded_product_ids( $discount->get_excluded_product_ids() );
	$coupon->set_exclude_sale_items( 'yes' === $discount->get_exclude_sale_items() );
	$coupon->set_product_categories( $discount->get_product_categories() );
	$coupon->set_excluded_product_categories( $discount->get_excluded_product_categories() );
	$coupon->set_email_restrictions( $email );
	$coupon->set_minimum_amount( $discount->get_minimum_amount() );
	$coupon->set_maximum_amount( $discount->get_maximum_amount() );
	$coupon->set_date_expires( $expiry_date );
	$coupon->set_usage_limit( 1 );
	$coupon->set_usage_limit_per_user( 1 );
	$coupon->update_meta_data( 'generated_by', 'yith_ywar' );

	/**
	 * DO_ACTION: yith_ywar_additional_coupon_features
	 *
	 * Adds an action to modify the current coupon.
	 *
	 * @param int                                     $coupon_id The coupon ID.
	 * @param YITH_YWAR_Review_For_Discounts_Discount $discount  The discount object.
	 * @param WC_Coupon                               $coupon    The coupon object.
	 */
	do_action( 'yith_ywar_additional_coupon_features', $coupon->get_id(), $discount, $coupon );

	$coupon->save();

	return $coupon_code;
}

/**
 * Get the coupon description.
 *
 * @param string $coupon_code The coupon code.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_coupon_description( string $coupon_code ): string {
	if ( 'test-coupon' !== $coupon_code ) {
		$coupon = new WC_Coupon( $coupon_code );
		if ( ! $coupon ) {
			return '';
		}
		$amount_suffix = get_woocommerce_currency_symbol();

		if ( function_exists( 'wc_price' ) ) {
			$amount_suffix = null;
		}

		$discount_type = $coupon->get_discount_type();

		if ( 'percent' === $discount_type || 'percent_product' === $discount_type ) {
			$amount_suffix = '%';
		}

		$amount = $coupon->get_amount();
		if ( null === $amount_suffix ) {
			$amount        = wc_price( $amount );
			$amount_suffix = '';
		}

		$product_ids                = $coupon->get_product_ids();
		$exclude_product_ids        = $coupon->get_excluded_product_ids();
		$product_categories         = $coupon->get_product_categories();
		$exclude_product_categories = $coupon->get_excluded_product_categories();
		$minimum_amount             = $coupon->get_minimum_amount();
		$maximum_amount             = $coupon->get_maximum_amount();
		$expiry_date                = $coupon->get_date_expires();
		$individual_use             = $coupon->get_individual_use();
		$exclude_sale_items         = $coupon->get_exclude_sale_items();
		$free_shipping              = $coupon->get_free_shipping();
	} else {

		// Get fictitious data for test.
		$nickname                   = wp_get_current_user()->get( 'nickname' );
		$coupon_code                = uniqid( "$nickname-" );
		$product_ids                = array();
		$exclude_product_ids        = array();
		$product_categories         = array();
		$exclude_product_categories = array();
		$amount                     = 20;
		$amount_suffix              = '%';
		$minimum_amount             = 3;
		$maximum_amount             = 200;
		$expiry_date                = gmdate( 'Y-m-d', strtotime( '+20 days' . yith_ywar_get_time_offset() ) );
		$individual_use             = true;
		$exclude_sale_items         = true;
		$free_shipping              = true;
	}

	$products            = array();
	$products_excluded   = array();
	$categories          = array();
	$categories_excluded = array();

	if ( $product_ids && count( $product_ids ) >= 1 ) {
		foreach ( $product_ids as $product_id ) {
			$products[] = yith_ywar_render_mailbody_link( $product_id, 'product' );
		}
	}

	if ( $exclude_product_ids && count( $exclude_product_ids ) >= 1 ) {
		foreach ( $exclude_product_ids as $product_id ) {
			$products_excluded[] = yith_ywar_render_mailbody_link( $product_id, 'product' );
		}
	}

	if ( $product_categories && count( $product_categories ) >= 1 ) {
		foreach ( $product_categories as $term_id ) {
			$categories[] = yith_ywar_render_mailbody_link( $term_id, 'category' );
		}
	}

	if ( $exclude_product_categories && count( $exclude_product_categories ) >= 1 ) {
		foreach ( $exclude_product_categories as $term_id ) {
			$categories_excluded[] = yith_ywar_render_mailbody_link( $term_id, 'category' );
		}
	}

	$coupon_features = array();

	if ( $minimum_amount || $maximum_amount ) {
		if ( $minimum_amount && ! $maximum_amount ) {
			/* translators: %s minimum amount */
			$coupon_features[] = sprintf( esc_html_x( 'Valid for a minimum purchase of %s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( wc_price( $minimum_amount ) ) );
		} elseif ( ! $minimum_amount && $maximum_amount ) {
			/* translators: %s maximum amount */
			$coupon_features[] = sprintf( esc_html_x( 'Valid for a maximum purchase of %s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( wc_price( $maximum_amount ) ) );
		} else {
			/* translators: %1$s minimum amount - %2$s maximum amount */
			$coupon_features[] = sprintf( esc_html_x( 'Valid for a minimum purchase of %1$s and a maximum of %2$s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( wc_price( $minimum_amount ) ), wp_kses_post( wc_price( $maximum_amount ) ) );
		}
	}

	if ( count( $products ) > 0 ) {
		/* translators: %s products list */
		$coupon_features[] = sprintf( esc_html_x( 'Valid for the purchase of the following products: %s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( implode( ', ', $products ) ) );
	}

	if ( count( $categories ) > 0 ) {
		/* translators: %s products list */
		$coupon_features[] = sprintf( esc_html_x( 'Valid for the purchase of products in the following categories: %s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( implode( ', ', $categories ) ) );
	}

	/**
	 * APPLY_FILTERS: yith_ywar_show_excluded_items
	 *
	 * Show/Hide the items excluded from the coupon.
	 *
	 * @param bool $value The value.
	 *
	 * @return bool
	 */
	if ( apply_filters( 'yith_ywar_show_excluded_items', true ) ) {
		if ( count( $products_excluded ) > 0 ) {
			/* translators: %s products list */
			$coupon_features[] = sprintf( esc_html_x( 'Not valid for the purchase of the following products: %s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( implode( ', ', $products_excluded ) ) );
		}

		if ( count( $categories_excluded ) > 0 ) {
			/* translators: %s products list */
			$coupon_features[] = sprintf( esc_html_x( 'Not valid for the purchase of products in the following categories: %s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( implode( ', ', $categories_excluded ) ) );
		}
	}

	if ( $individual_use ) {
		$coupon_features[] = esc_html_x( 'This coupon cannot be used with other coupons.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' );
	}

	if ( $exclude_sale_items ) {
		$coupon_features[] = esc_html_x( 'This coupon will not be applied to items on sale', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' );
	}

	if ( $expiry_date ) {
		/* translators: %s categories list */
		$coupon_features[] = sprintf( esc_html_x( 'Expiration date: %s.', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( ucwords( date_i18n( get_option( 'date_format' ), wp_kses_post( yit_datetime_to_timestamp( $expiry_date ) ) ) ) ) );
	}

	ob_start();
	?>
	<div class="yith-ywar-coupon-data">
		<div class="coupon-title">
			<?php echo esc_html_x( 'Your coupon', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ); ?>:
		</div>
		<div class="coupon-code">
			<?php echo esc_attr( $coupon_code ); ?>
		</div>
		<div class="coupon-amount">
			<?php
			$free_shipping = $free_shipping ? ' + ' . esc_html_x( 'Free shipping', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ) : '';
			/* translators: %1$s coupon amount - %2$s amount suffix */
			printf( esc_html_x( 'Coupon amount: %1$s%2$s off%3$s', '[Admin panel] Part of coupon description', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( $amount ), esc_attr( $amount_suffix ), esc_html( $free_shipping ) );
			?>
		</div>
		<div class="coupon-features">
			<?php echo wp_kses_post( implode( '<br />', $coupon_features ) ); ?>
		</div>
	</div>
	<?php
	/**
	 * APPLY_FILTERS: yith_ywar_coupon_description
	 *
	 * Print the coupon description in the email.
	 *
	 * @param string $value       The coupon description HTML.
	 * @param string $coupon_code The coupon code.
	 *
	 * @return string
	 */
	return apply_filters( 'yith_ywar_coupon_description', ob_get_clean(), $coupon_code );
}

/**
 * Give funds to the customer.
 *
 * @param YITH_YWAR_Review_For_Discounts_Discount $discount  The discount object.
 * @param int                                     $user_id   The User ID.
 * @param string                                  $type      The reward type.
 * @param int                                     $data      The product ID.
 * @param string                                  $user_mail The user email.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_give_funds( YITH_YWAR_Review_For_Discounts_Discount $discount, int $user_id, string $type, int $data, string $user_mail ) {

	$fund_user   = new YITH_YWF_Customer( $user_id );
	$new_funds   = $discount->get_funds_amount() + $fund_user->get_funds();
	$description = 'single' === $discount->get_trigger() ? esc_html_x( 'Review of a product.', '[Admin panel] Funds note description', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Review of multiple products.', '[Admin panel] Funds note description', 'yith-woocommerce-advanced-reviews' );
	$fund_user->set_funds( $new_funds );

	$log_args = array(
		'user_id'        => $user_id,
		'type_operation' => 'review',
		'fund_user'      => $discount->get_funds_amount(),
		'description'    => $description,
	);

	YWF_Log()->add_log( $log_args );

	$args = array(
		'user'         => array(
			'customer_name'      => wp_get_current_user()->get( 'billing_first_name' ),
			'customer_last_name' => wp_get_current_user()->get( 'billing_last_name' ),
			'customer_email'     => $user_mail,
		),
		'funds_amount' => $discount->get_funds_amount(),
	);

	if ( 'single' === $type ) {
		$args['product_id'] = $data;
		do_action( 'yith_ywar_coupon_funds_single_review', $args );

	} else {
		$args['total_reviews'] = $data;
		do_action( 'yith_ywar_coupon_funds_multiple_review', $args );
	}
}

/**
 * Check if Comment moderation is required
 *
 * @return  bool
 * @since   2.0.0
 */
function yith_ywar_is_moderation_required(): bool {
	if ( 'yes' === yith_ywar_get_option( 'ywar_review_autoapprove' ) ) {
		return false;
	} else {
		return 'moderated' === yith_ywar_get_option( 'ywar_coupon_sending' );
	}
}
