<?php
/**
 * Module schedule functions
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Create a schedule record
 *
 * @param WC_Order $order         The order object.
 * @param string   $schedule_date The schedule date (Optional).
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_schedule_mail( WC_Order $order, string $schedule_date = '' ): bool {
	$customer_id    = $order->get_user_id();
	$customer_email = $order->get_billing_email();

	if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
		$was_quote   = $order->get_meta( 'ywraq_raq' ) === 'yes'; // Check if the order was a quote.
		$is_funds    = $order->get_meta( '_order_has_deposit' ) === 'yes'; // Check if the order is a Funds purchase.
		$is_deposits = $order->get_created_via() === 'yith_wcdp_balance_order'; // Check if the order is a balance order.

		/**
		 * APPLY_FILTERS: yith_ywar_skip_renewal_orders
		 *
		 * Check if plugin should skip subscription renewal orders.
		 *
		 * @param bool $value Value to check if renewals should be skipped.
		 *
		 * @return bool
		 */
		$is_renew = $order->get_meta( 'is_a_renew' ) === 'yes' && apply_filters( 'yith_ywar_skip_renewal_orders', true );

		/**
		 * APPLY_FILTERS: yith_ywar_can_ask_for_review
		 *
		 * Check if plugin can ask for a review.
		 *
		 * @param bool     $value Value to check if the review can be asked.
		 * @param WC_Order $order The order to check.
		 *
		 * @return bool
		 */
		$can_ask_review = apply_filters( 'yith_ywar_can_ask_for_review', true, $order );

		if ( ( ! $order->get_parent_id() || ( $order->get_parent_id() && $was_quote ) ) && ! $is_funds && ! $is_deposits && ! $is_renew && $can_ask_review ) {
			$list = yith_ywar_get_review_list( $order );

			if ( ! empty( $list ) ) {
				$schedule_date  = '' !== $schedule_date ? $schedule_date : ( current_time( 'mysql' ) . ' + ' . yith_ywar_get_option( 'ywar_mail_schedule_day' ) . ' days' );
				$date           = $order->get_date_completed() ?? $order->get_date_modified();
				$scheduled_date = gmdate( 'Y-m-d', strtotime( $schedule_date ) );
				$order_date     = gmdate( 'Y-m-d', yit_datetime_to_timestamp( $date ) );
				$scheduled      = yith_ywar_get_schedule_by_object( $order->get_id(), 'order' );
				if ( 0 !== $scheduled['id'] ) {
					$result = yith_ywar_update_schedule( $scheduled['id'], $schedule_date, 'pending', maybe_serialize( $list ) );
				} else {
					$result = yith_ywar_add_schedule( $order->get_id(), $order_date, $scheduled_date, maybe_serialize( $list ), 'order' );
				}

				return $result;
			}
		}
	}

	return false;
}

/**
 * Create a schedule record for the booking
 *
 * @param YITH_WCBK_Booking $booking       The booking object.
 * @param WC_Order          $order         The order object.
 * @param string            $schedule_date The schedule date (Optional).
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_schedule_booking_mail( YITH_WCBK_Booking $booking, WC_Order $order, string $schedule_date = '' ): bool {
	$customer_id    = $order->get_user_id();
	$customer_email = $order->get_billing_email();

	if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
		$was_quote   = $order->get_meta( 'ywraq_raq' ) === 'yes'; // Check if the order was a quote.
		$is_funds    = $order->get_meta( '_order_has_deposit' ) === 'yes'; // Check if the order is a Funds purchase.
		$is_deposits = $order->get_created_via() === 'yith_wcdp_balance_order'; // Check if the order is a balance order.

		/**
		 * APPLY_FILTERS: yith_ywar_skip_renewal_orders
		 *
		 * Check if plugin should skip subscription renewal orders.
		 *
		 * @param bool $value Value to check if renewals should be skipped.
		 *
		 * @return bool
		 */
		$is_renew = $order->get_meta( 'is_a_renew' ) === 'yes' && apply_filters( 'yith_ywar_skip_renewal_orders', true );

		/**
		 * APPLY_FILTERS: yith_ywar_can_ask_for_review
		 *
		 * Check if plugin can ask for a review.
		 *
		 * @param bool     $value Value to check if the review can be asked.
		 * @param WC_Order $order The order to check.
		 *
		 * @return bool
		 */
		$can_ask_review = apply_filters( 'yith_ywar_can_ask_for_review', true, $order );

		if ( ( ! $order->get_parent_id() || ( $order->get_parent_id() && $was_quote ) ) && ! $is_funds && ! $is_deposits && ! $is_renew && $can_ask_review ) {
			$list = array();

			if ( ! yith_ywar_skip_product( $booking->get_product_id(), $order->get_billing_email(), 'booking' ) ) {
				$list = array( $booking->get_product_id() );
			}

			if ( ! empty( $list ) ) {
				$schedule_date  = '' !== $schedule_date ? $schedule_date : ( current_time( 'mysql' ) . ' + ' . yith_ywar_get_option( 'ywar_mail_schedule_day' ) . ' days' );
				$scheduled_date = gmdate( 'Y-m-d', strtotime( $schedule_date ) );
				$booking_date   = gmdate( 'Y-m-d', yit_datetime_to_timestamp( $booking->get_to() ) );
				$scheduled      = yith_ywar_get_schedule_by_object( $booking->get_id(), 'booking' );
				if ( 0 !== $scheduled['id'] ) {
					$result = yith_ywar_update_schedule( $scheduled['id'], $schedule_date, 'pending', maybe_serialize( $list ) );
				} else {
					$result = yith_ywar_add_schedule( $booking->get_id(), $booking_date, $scheduled_date, maybe_serialize( $list ), 'booking' );
				}

				return $result;
			}
		}
	}

	return false;
}

/**
 * Check if order has reviewable items
 *
 * @param WC_Order $order The order.
 *
 * @return int
 * @since  2.0.0
 */
function yith_ywar_check_reviewable_items( WC_Order $order ): int {

	$order_items      = $order->get_items();
	$reviewable_items = 0;

	foreach ( $order_items as $item ) {
		if ( ! yith_ywar_skip_product( $item['product_id'], $order->get_billing_email() ) ) {
			++$reviewable_items;
		}
	}

	return $reviewable_items;
}

/**
 * Check if product can be reviewed
 *
 * @param int    $product_id  The product ID.
 * @param string $user_email  The user email.
 * @param string $object_type The object tipe from where the product comes.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_skip_product( int $product_id, string $user_email, string $object_type = 'order' ): bool {

	$excluded_items = array();
	if ( yith_ywar_booking_enabled() && 'order' === $object_type ) {
		// Exclude booking products.
		$product = wc_get_product( $product_id );
		if ( $product && $product->is_type( 'booking' ) ) {
			$excluded_items[] = $product_id;
		}
	}
	/**
	 * APPLY_FILTERS: yith_ywar_excluded_items
	 *
	 * Get list of excluded items.
	 *
	 * @param array $excluded_items The list of excluded items.
	 * @param int   $product_id     The product ID.
	 *
	 * @return array
	 */
	$excluded_items = apply_filters( 'yith_ywar_excluded_items', $excluded_items, $product_id );

	return ( ! yith_ywar_items_has_comments_opened( $product_id ) || yith_ywar_user_has_commented( $product_id, $user_email ) || in_array( $product_id, $excluded_items, true ) );
}

/**
 * Check if product has reviews enabled
 *
 * @param int $product_id The product ID.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_items_has_comments_opened( int $product_id ): bool {
	/**
	 * APPLY_FILTERS: yith_ywar_comment_status
	 *
	 * Check if comments are opened for a specific product.
	 *
	 * @param bool $comment_status Value to check if comments are opened for that product.
	 *
	 * @return bool
	 */
	return apply_filters( 'yith_ywar_comment_status', comments_open( $product_id ) );
}

/**
 * Prepares the list of items to review from stored options
 *
 * @param WC_Order $order The order.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_review_list( WC_Order $order ): array {

	$user_email = $order->get_billing_email();
	$criteria   = ( yith_ywar_get_option( 'ywar_request_type' ) ) !== 'all' ? yith_ywar_get_option( 'ywar_request_criteria' ) : 'default';
	$items      = call_user_func( 'yith_ywar_criteria_' . $criteria, $order, $user_email );

	return $items;
}

/**
 * Get all products in the order that can be reviewed
 *
 * @param WC_Order $order      The Order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_default( WC_Order $order, string $user_email ): array {

	$items = array();

	foreach ( $order->get_items() as $item ) {

		$product_id = $item->get_data()['product_id'];

		if ( yith_ywar_skip_product( $product_id, $user_email ) ) {
			continue;
		}

		$items[] = $product_id;

	}

	return $items;
}

/**
 * Get the first X items in the order that can be reviewed
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_first( WC_Order $order, string $user_email ): array {

	$order_items = $order->get_items();

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the last X items in the order that can be reviewed
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_last( WC_Order $order, string $user_email ): array {

	$order_items = array_reverse( $order->get_items() );

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get X random items in the order that can be reviewed
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_random( WC_Order $order, string $user_email ): array {

	$order_items = $order->get_items();
	shuffle( $order_items );

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the last X items in the order that can be reviewed ordered by quantity
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_highest_quantity( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {
		$order_items[] = $item->get_data();
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return $b['quantity'] - $a['quantity'];
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the last X items in the order that can be reviewed ordered by quantity
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_lowest_quantity( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {
		$order_items[] = $item->get_data();
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return $a['quantity'] - $b['quantity'];
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the first X items in the order that can be reviewed ordered by price
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_highest_priced( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {
		$order_items[] = $item->get_data();
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return ( $b['subtotal'] / $b['quantity'] ) - ( $a['subtotal'] / $a['quantity'] );
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the last X items in the order that can be reviewed ordered by price
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_lowest_priced( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {
		$order_items[] = $item->get_data();
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return ( $a['subtotal'] / $a['quantity'] ) - ( $b['subtotal'] / $b['quantity'] );
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the first X items in the order that can be reviewed ordered by subtotal
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_highest_total_value( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {
		$order_items[] = $item->get_data();
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return $b['subtotal'] - $a['subtotal'];
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the last X items in the order that can be reviewed ordered by subtotal
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_lowest_total_value( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {
		$order_items[] = $item->get_data();
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return $a['subtotal'] - $b['subtotal'];
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the first X items in the order that can be reviewed ordered by number of reviews
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_most_reviewed( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {

		$item_data = $item->get_data();
		$product   = wc_get_product( $item_data['product_id'] );
		if ( $product ) {
			$review_count  = array( 'reviews' => yith_ywar_get_review_stats( $product )['total'] );
			$item_data     = array_merge( $item_data, $review_count );
			$order_items[] = $item_data;
		}
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return $b['reviews'] - $a['reviews'];
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Get the last X items in the order that can be reviewed ordered by number of reviews
 *
 * @param WC_Order $order      The order.
 * @param string   $user_email The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_criteria_least_reviewed( WC_Order $order, string $user_email ): array {

	$order_items = array();
	foreach ( $order->get_items() as $item ) {

		$item_data = $item->get_data();
		$product   = wc_get_product( $item_data['product_id'] );
		if ( $product ) {
			$review_count  = array( 'reviews' => yith_ywar_get_review_stats( $product )['total'] );
			$item_data     = array_merge( $item_data, $review_count );
			$order_items[] = $item_data;
		}
	}

	usort(
		$order_items,
		function ( $a, $b ) {
			return $a['reviews'] - $b['reviews'];
		}
	);

	return yith_ywar_get_items_to_review( $order_items, $user_email );
}

/**
 * Filters the items to review
 *
 * @param array  $order_items The order items.
 * @param string $user_email  The user email.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_items_to_review( array $order_items, string $user_email ): array {
	$items  = array();
	$amount = yith_ywar_get_option( 'ywar_request_number' );
	$count  = 0;
	foreach ( $order_items as $item ) {

		$product_id = $item instanceof WC_Order_Item ? $item->get_data()['product_id'] : $item['product_id'];

		if ( yith_ywar_skip_product( $product_id, $user_email ) ) {
			continue;
		}

		$items[] = $product_id;
		++$count;
		if ( absint( $amount ) === $count ) {
			break;
		}
	}

	return $items;
}
