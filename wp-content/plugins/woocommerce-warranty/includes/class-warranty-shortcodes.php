<?php
/**
 * Warranty shortcodes class file.
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin shortcodes class.
 */
class Warranty_Shortcodes {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Account Shortcode.
		add_shortcode( 'warranty_request', array( $this, 'render_warranty_request_shortcode' ) );

		// Generic Return Form Shortcode.
		add_shortcode( 'warranty_return_form', array( $this, 'render_return_form_shortcode' ) );
	}

	/**
	 * Returns the content for the shortcode [warranty_request]
	 *
	 * @return string The HTML content
	 */
	public function render_warranty_request_shortcode() {
		wp_enqueue_script( 'wc-warranty-order-items' );
		$request_data = warranty_request_data();
		$get_data     = warranty_request_get_data();
		$order_id     = ! empty( $request_data['order'] ) ? absint( trim( $request_data['order'] ) ) : 0;

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return __( 'No order selected.', 'woocommerce-warranty' );
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `view_order` is a native capability from WooCommerce
		if ( ! is_user_logged_in() || ! current_user_can( 'view_order', $order_id ) ) {
			return __( 'If you are a guest customer, you can make a request by using claim link on your order completed email notification.', 'woocommerce-warranty' );
		}

		$args = array(
			'order'              => $order,
			'order_id'           => $order_id,
			'order_status'       => $order->get_status(),
			'order_has_warranty' => Warranty_Order::order_has_warranty( $order ),
			'items'              => $order->get_items(),
			'completed'          => $order->get_date_completed() ? $order->get_date_completed()->date( 'Y-m-d H:i:s' ) : false,
			'product_id'         => ! empty( $request_data['product_id'] ) ? absint( $request_data['product_id'] ) : false,
			'idxs'               => ! empty( $get_data['idx'] ) ? $get_data['idx'] : array(),
			'updated'            => ! empty( $get_data['updated'] ) ? $get_data['updated'] : '',
		);

		ob_start();
		wc_get_template( 'shortcode-content.php', $args, 'warranty', WooCommerce_Warranty::$base_path . '/templates/' );

		return ob_get_clean();
	}

	/**
	 * Generates and returns the form for generic return requests
	 *
	 * @return string
	 */
	public function render_return_form_shortcode() {
		global $current_user, $wpdb;

		$get_data = warranty_request_get_data();

		$defaults = array(
			'first_name' => ( ! empty( $current_user->billing_first_name ) ) ? $current_user->billing_first_name : '',
			'last_name'  => ( ! empty( $current_user->billing_last_name ) ) ? $current_user->billing_last_name : '',
			'email'      => ( ! empty( $current_user->billing_email ) ) ? $current_user->billing_email : $current_user->user_email,
		);
		$args     = array(
			'defaults'     => $defaults,
			'order_id'     => ( ! empty( $get_data['order'] ) ) ? $get_data['order'] : '',
			'product_id'   => ( ! empty( $get_data['product_id'] ) ) ? $get_data['product_id'] : '',
			'idx'          => ( ! empty( $get_data['idx'] ) ) ? $get_data['idx'] : '',
			'warranty_key' => ( ! empty( $get_data['wak'] ) ) ? $get_data['wak'] : '---', // Use '---' to make sure it returns empty array if no warranty key in GET parameter.
		);

		$orders = wc_get_orders(
			array(
				'warranty_key' => $args['warranty_key'],
				'limit'        => 1,
				'status'       => 'wc-completed',
			)
		);

		if ( ! empty( $orders ) ) {
			$order                          = array_shift( $orders );
			$args['order_id']               = $order->get_id();
			$args['defaults']['first_name'] = $order->get_billing_first_name();
			$args['defaults']['last_name']  = $order->get_billing_last_name();
			$args['defaults']['email']      = $order->get_billing_email();

			foreach ( $order->get_items() as $item_idx => $item ) {
				$item_warranty = new Warranty_Item( $item_idx );
				$warranty      = warranty_get_order_item_warranty( $item );

				if ( ! empty( $item['item_meta']['_bundled_by'] ) ) {
					continue;
				}

				$item_has_rma = $item_warranty->has_warranty();

				if ( $item_has_rma ) {
					$args['items'][ $item_idx ] = $item;
				}
			}
		}

		ob_start();
		wc_get_template( 'shortcode-return-form.php', $args, 'warranty', WooCommerce_Warranty::$base_path . '/templates/' );

		return ob_get_clean();
	}
}

new Warranty_Shortcodes();
