<?php
/**
 * Update Data version 1.1
 *  - Rename the meta key for warranty shipping label.
 *  - Removing item meta with key = '_item_warranty'.
 *
 * @package WooCommerce_Warranty
 */

global $wpdb;

set_time_limit( 0 );

// Rename the warranty_shipping_label meta key.
$wpdb->query( "UPDATE {$wpdb->postmeta} SET meta_key = '_warranty_shipping_label' WHERE meta_key = 'warranty_shipping_label'" );

// Remove warranty order item meta.
$items = $wpdb->get_results( "SELECT order_item_id, meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key = '_item_warranty'" );

foreach ( $items as $item ) {
	$warranty = maybe_unserialize( $item->meta_value );
	$value    = false;

	if ( ! is_array( $warranty ) || ! isset( $warranty['type'] ) ) {
		continue;
	}

	if ( 'addon_warranty' === $warranty['type'] ) {
		$addons = $warranty['addons'];

		$warranty_index = isset( $values['warranty_index'] ) ? $values['warranty_index'] : false;

		if ( false !== $warranty_index && isset( $addons[ $warranty_index ] ) && ! empty( $addons[ $warranty_index ] ) ) {
			$addon = $addons[ $warranty_index ];
			$value = $GLOBALS['wc_warranty']->get_warranty_string( $addon['value'], $addon['duration'] );

			if ( $addon['amount'] > 0 ) {
				$value .= ' (' . wp_strip_all_tags( wc_price( $addon['amount'] ) ) . ')';
			}
		}
	} elseif ( 'included_warranty' === $warranty['type'] ) {
		if ( 'lifetime' === $warranty['length'] ) {
			$value = __( 'Lifetime', 'woocommerce-warranty' );
		} elseif ( 'limited' === $warranty['length'] ) {
			$value = $GLOBALS['wc_warranty']->get_warranty_string( $warranty['value'], $warranty['duration'] );
		}
	}

	if ( ! $value ) {
		continue;
	}

	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_value = %s", $item->order_item_id, $value ) );
}
