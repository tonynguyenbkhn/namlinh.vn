<?php
/**
 * File of Warranty completed reports list table.
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Warranty_Coupons class
 */
class Warranty_Coupons {

	/**
	 * Class initiation.
	 */
	public static function init() {
		// refund order item.
		add_action( 'admin_post_warranty_send_coupon', 'Warranty_Coupons::send_coupon' );
	}

	/**
	 * Send the coupon based on warranty ID.
	 */
	public static function send_coupon() {
		check_admin_referer( 'warranty_send_coupon' );

		$warranty_id = ( isset( $_REQUEST['id'] ) ) ? absint( $_REQUEST['id'] ) : 0;
		$is_ajax     = ( isset( $_REQUEST['ajax'] ) ) ? true : false;

		if ( 0 === $warranty_id ) {
			$return_message = __( 'Warranty is not valid', 'woocommerce-warranty' );

			if ( ! $is_ajax ) {
				wp_safe_redirect( admin_url( 'admin.php?page=warranties&updated=' . rawurlencode( $return_message ) ) );
				exit;
			}

			wp_send_json(
				array(
					'status'  => 'not OK',
					'message' => $return_message,
				)
			);
		}

		$order_item_key = get_post_meta( $warranty_id, '_index', true );
		$order_id       = get_post_meta( $warranty_id, '_order_id', true );
		$order          = wc_get_order( $order_id );
		$email          = $order ? $order->get_billing_email() : '';

		$coupon_amount = ! empty( $_REQUEST['amount'] ) ? filter_var( wp_unslash( $_REQUEST['amount'] ), FILTER_VALIDATE_FLOAT ) : wc_get_order_item_meta( $order_item_key, '_line_total', true );

		$coupon_code = self::generate_coupon_code();
		$coupon_desc = get_option( 'warranty_coupon_desc' );
		$new_coupon  = new WC_Coupon();

		$new_coupon->set_code( $coupon_code );
		$new_coupon->set_description( $coupon_desc );
		$new_coupon->set_discount_type( 'fixed_cart' );
		$new_coupon->set_amount( $coupon_amount );
		$new_coupon->set_individual_use( false );
		$new_coupon->set_usage_count( 0 );
		$new_coupon->set_usage_limit( 1 );
		$new_coupon->set_usage_limit_per_user( 1 );
		$new_coupon->set_free_shipping( false );
		$new_coupon->set_exclude_sale_items( false );

		if ( ! empty( $email ) ) {
			$new_coupon->set_email_restrictions( array( $email ) );
		}

		$new_coupon->save();

		$coupon_id = $new_coupon->get_id();

		/**
		 * Action to let third party do something after coupon is created.
		 *
		 * @since 1.9.33
		 */
		do_action( 'after_warranty_create_coupon', $coupon_id, $order_id, $warranty_id );

		$refunded_amount = get_post_meta( $warranty_id, '_refund_amount', true );

		if ( ! $refunded_amount ) {
			$refunded_amount = 0;
		}
		$refunded_amount += $coupon_amount;

		/**
		 * Filter to modify the request data.
		 *
		 * @since 1.9.33
		 */
		$data = apply_filters(
			'warranty_update_request_data',
			array(
				'refund_amount' => $refunded_amount,
				'coupon_sent'   => 'yes',
				'coupon_code'   => $coupon_code,
				'coupon_amount' => $coupon_amount,
				'coupon_date'   => current_time( 'mysql' ),
			),
			$warranty_id,
			$coupon_id,
			$order_id
		);

		warranty_update_request( $warranty_id, $data );

		$return_message = __( 'Coupon sent', 'woocommerce-warranty' );

		warranty_send_emails( $warranty_id, 'coupon_sent' );

		if ( ! $is_ajax ) {
			wp_safe_redirect( admin_url( 'admin.php?page=warranties&updated=' . rawurlencode( $return_message ) ) );
			exit;
		}

		wp_send_json(
			array(
				'status'  => 'OK',
				'message' => $return_message,
			)
		);
	}

	/**
	 * Generate a random 8-character unique string that's to used as a coupon code
	 *
	 * @return string
	 */
	public static function generate_coupon_code() {
		global $wpdb;

		$prefix = get_option( 'warranty_coupon_prefix', '' );
		$chars  = 'abcdefghijklmnopqrstuvwxyz01234567890';
		do {
			$code = '';
			for ( $x = 0; $x < 8; $x++ ) {
				$code .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
			}

			$code = $prefix . $code;

			$check = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_title = %s AND post_type = 'shop_coupon'", $code ) );

			if ( 0 === intval( $check ) ) {
				break;
			}
		} while ( true );

		return $code;
	}
}

Warranty_Coupons::init();
