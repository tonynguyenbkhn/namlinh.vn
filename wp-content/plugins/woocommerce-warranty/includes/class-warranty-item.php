<?php
/**
 * File of Warranty item class.
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Warranty_Item class
 */
class Warranty_Item {

	/**
	 * Item ID.
	 *
	 * @var string
	 */
	public $item_id;

	/**
	 * Type of warranty.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Label of warranty item.
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Warranty item addons.
	 *
	 * @var array
	 */
	public $addons;

	/**
	 * Selected addons.
	 *
	 * @var string
	 */
	public $addon_selected;

	/**
	 * Default addons from settings.
	 *
	 * @var string
	 */
	public $addon_default;

	/**
	 * Duration length.
	 *
	 * @var string
	 */
	public $length;

	/**
	 * Duration value.
	 *
	 * @var int
	 */
	public $duration_value;

	/**
	 * Duration type.
	 *
	 * @var string
	 */
	public $duration_type;

	/**
	 * No warranty option.
	 *
	 * @var string
	 */
	public $no_warranty_option;

	/**
	 * Order ID.
	 *
	 * @var int
	 */
	public $order_id;

	/**
	 * Initialize the object
	 *
	 * @param int $item_id The WooCommerce Item ID.
	 */
	public function __construct( $item_id ) {
		$warranty = wc_get_order_item_meta( $item_id, '_item_warranty', true );
		$selected = wc_get_order_item_meta( $item_id, '_item_warranty_selected', true );

		$this->item_id        = $item_id;
		$this->addon_selected = ( $selected ) ? $selected : false;

		if ( ! $warranty ) {
			$this->type = 'no_warranty';

			return;
		}

		foreach ( $warranty as $key => $value ) {
			switch ( $key ) {
				case 'value':
					$this->duration_value = $value;
					break;

				case 'duration':
					$this->duration_type = $value;
					break;

				case 'default':
					$this->addon_default = $value;
					break;

				default:
					$this->$key = $value;
					break;
			}
		}
	}

	/**
	 * Get order ID from the item id.
	 */
	public function get_order_id() {
		global $wpdb;

		if ( ! $this->order_id ) {
			// phpcs:ignore --- Need to use WPDB to get order id faster
			$this->order_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT order_id
                FROM {$wpdb->prefix}woocommerce_order_items
                WHERE order_item_id = %d",
					$this->item_id
				)
			);
		}

		return $this->order_id;
	}

	/**
	 * Get the available number of RMAs for the current order item.
	 *
	 * The available number is determined by the quantity of order item minus
	 * the number of warranty requests made against the same order item.
	 *
	 * @return int
	 */
	public function get_quantity_remaining() {
		global $wpdb;

		// phpcs:ignore --- Need to use WPDB::get_row to get warranty product data more efficient
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
            FROM {$wpdb->prefix}wc_warranty_products
            WHERE order_item_index = %d",
				$this->item_id
			)
		);

		$qty = wc_get_order_item_meta( $this->item_id, '_qty', true );

		$product_id   = wc_get_order_item_meta( $this->item_id, '_product_id', true );
		$variation_id = wc_get_order_item_meta( $this->item_id, '_variation_id', true );

		if ( $variation_id ) {
			$product_id = $variation_id;
		}

		$product         = wc_get_product( $product_id );
		$real_product_id = $product instanceof WC_Product ? $product->get_id() : 0;

		$requests = warranty_search( $this->get_order_id(), $real_product_id, $this->item_id );

		if ( $requests ) {
			$used = 0;
			foreach ( $requests as $request ) {
				$request_items = warranty_get_request_items( $request->ID );

				if ( empty( $request_items ) || ! is_array( $request_items ) ) {
					continue;
				}

				foreach ( $request_items as $request_item ) {
					if ( intval( $request_item['order_item_index'] ) === $this->item_id ) {
						$used += $request_item['quantity'];
					}
				}
			}

			$qty -= $used;
		}

		return $qty;
	}

	/**
	 * Check if the item can send a warranty request
	 *
	 * This will return true for the following scenarios:
	 *  - Type is 'included' and duration is 'lifetime' and remaining quantity > 0
	 *  - Type is 'included' or 'addon' and expiry is in a future date and remainig quantity > 0
	 *
	 * @return bool
	 */
	public function has_warranty() {
		$has_warranty = false;
		$remaining    = $this->get_quantity_remaining();

		if ( 1 > $remaining ) {
			return $has_warranty;
		}

		if ( 'included_warranty' === $this->type ) {
			if ( 'lifetime' === $this->length ) {
				$has_warranty = true;
			} else {
				$now    = wp_date( 'U' );
				$expiry = $this->get_expiry();

				if ( ! $expiry || $now < $expiry ) {
					$has_warranty = true;
				}
			}
		} elseif ( 'addon_warranty' === $this->type ) {
			if ( isset( $this->addons[ $this->addon_selected ] ) ) {
				$addon  = $this->addons[ $this->addon_selected ];
				$now    = wp_date( 'U' );
				$expiry = $this->get_expiry( $addon['value'], $addon['duration'] );

				if ( ! $expiry || $now < $expiry ) {
					$has_warranty = true;
				}
			}
		}

		return $has_warranty;
	}

	/**
	 * Get the warranty's expiration date.
	 *
	 * @param string $duration_value Duration value.
	 * @param string $duration_type Duration type.
	 *
	 * @return bool|int
	 */
	public function get_expiry( $duration_value = '', $duration_type = '' ) {
		$expiry = false;
		$order  = wc_get_order( $this->get_order_id() );

		$completed_date = $order->get_date_completed() ? $order->get_date_completed()->date( 'Y-m-d H:i:s' ) : false;

		if ( empty( $duration_value ) ) {
			$duration_value = $this->duration_value;
		}

		if ( empty( $duration_type ) ) {
			$duration_type = $this->duration_type;
		}

		if ( $completed_date ) {
			$expiry = strtotime( $completed_date . ' +' . $duration_value . ' ' . $duration_type );
		}

		return $expiry;
	}

	/**
	 * Is the warranty expired?
	 *
	 * @return bool
	 */
	public function is_expired() {
		$expiration_date = $this->get_expiry();

		// Lifetime warranty doesn't have expired date. Thus it will always return false.
		if ( ! $expiration_date || 'lifetime' === $this->length ) {
			return false;
		}

		return wp_date( 'U' ) > $expiration_date;
	}
}
