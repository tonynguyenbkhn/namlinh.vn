<?php
/**
 * Module database functions
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Get list of all schedules
 *
 * @param string $mail_status  The mail status.
 * @param string $search_param Optional search parameter.
 * @param int    $limit        The limit of items.
 * @param int    $paged        The page.
 *
 * @return array|object|stdClass[]|null
 * @since  2.0.0
 */
function yith_ywar_list_schedules( string $mail_status = '', string $search_param = '', int $limit = 0, int $paged = 0 ) {
	return YITH_YWAR_Request_Review_DB::list_schedules( $mail_status, $search_param, $limit, $paged );
}

/**
 * Check if schedule list is not empty.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_schedule_list_not_empty(): bool {
	return YITH_YWAR_Request_Review_DB::count_total_schedules( '', '' ) > 0;
}

/**
 * Set the status of a scheduled email.
 *
 * @param string $status      The schedule status.
 * @param int    $schedule_id The ID of the schedule.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_update_schedule_status( string $status, int $schedule_id ) {
	YITH_YWAR_Request_Review_DB::update_schedule(
		array( 'mail_status' => $status ),
		array( 'id' => $schedule_id ),
		array( '%s' ),
		array( '%d' )
	);
}

/**
 * Get IDs of scheduled orders.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_scheduled_orders(): array {
	return YITH_YWAR_Request_Review_DB::get_schedules_for_type( 'order' );
}

/**
 * Get IDs of scheduled orders.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_scheduled_bookings(): array {
	return YITH_YWAR_Request_Review_DB::get_schedules_for_type( 'booking' );
}

/**
 * Check if an object is scheduled.
 *
 * @param int    $object_id   The object ID.
 * @param string $object_type The object type.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_check_object_scheduled( int $object_id, string $object_type ): bool {
	return YITH_YWAR_Request_Review_DB::check_object_scheduled( $object_id, $object_type );
}

/**
 * Get schedule of a specific object
 *
 * @param int    $object_id   The object ID.
 * @param string $object_type The object type.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_schedule_by_object( int $object_id, string $object_type ): array {
	return YITH_YWAR_Request_Review_DB::get_schedule_by_object( $object_id, $object_type );
}

/**
 * Get schedule of a specific object
 *
 * @param int $schedule_id The schedule ID.
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_schedule_by_id( int $schedule_id ): array {
	return YITH_YWAR_Request_Review_DB::get_schedule_by_id( $schedule_id );
}

/**
 * Set a schedule.
 *
 * @param int    $object_id     The object ID.
 * @param string $order_date    The order date.
 * @param string $schedule_date The scheduled date.
 * @param string $items_list    The items to review.
 * @param string $type          The object type.
 *
 * @return bool
 */
function yith_ywar_add_schedule( int $object_id, string $order_date, string $schedule_date, string $items_list, string $type ): bool {
	return YITH_YWAR_Request_Review_DB::add_schedule( $object_id, $order_date, $schedule_date, $items_list, $type );
}

/**
 * Set the status of a scheduled email.
 *
 * @param int    $schedule_id     The ID of the schedule.
 * @param string $schedule_date   The date of the schedule.
 * @param string $schedule_status The status of the schedule.
 * @param string $items_list      The items to review.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_update_schedule( int $schedule_id, string $schedule_date, string $schedule_status, string $items_list = '' ): bool {

	if ( '' !== $items_list ) {
		return YITH_YWAR_Request_Review_DB::update_schedule(
			array(
				'mail_status'    => $schedule_status,
				'scheduled_date' => $schedule_date,
				'request_items'  => $items_list,
			),
			array( 'id' => $schedule_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
	} else {
		return YITH_YWAR_Request_Review_DB::update_schedule(
			array(
				'mail_status'    => $schedule_status,
				'scheduled_date' => $schedule_date,
			),
			array( 'id' => $schedule_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}
}

/**
 * Count the email that should be sent today
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_daily_schedules(): array {
	return YITH_YWAR_Request_Review_DB::get_daily_schedules();
}

/**
 * Check if the customer is in blocklist table
 *
 * @param int    $customer_id    The customer ID, 0 if is a guest.
 * @param string $customer_email The customer email.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_check_blocklist( int $customer_id, string $customer_email ): bool {
	return YITH_YWAR_Request_Review_DB::check_blocklist( $customer_id, $customer_email );
}

/**
 * Deletes an entry from the blocklist table
 *
 * @param int $item_id The item ID.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_delete_from_blocklist( int $item_id ): bool {
	return YITH_YWAR_Request_Review_DB::delete_blocklist( $item_id );
}

/**
 * Deletes an entry from the blocklist table by Customer ID
 *
 * @param int $customer_id The Customer ID.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_delete_from_blocklist_by_customer( int $customer_id ): bool {
	return YITH_YWAR_Request_Review_DB::delete_blocklist_by_customer( $customer_id );
}

/**
 * Add a customer to the blocklist.
 *
 * @param int    $customer_id    The customer ID, 0 if is a guest.
 * @param string $customer_email The customer email.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_add_to_blocklist( int $customer_id, string $customer_email ): bool {
	return YITH_YWAR_Request_Review_DB::add_to_blocklist( $customer_id, $customer_email );
}

/**
 * Check if blocklist is not empty.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_blocklist_not_empty(): bool {
	return YITH_YWAR_Request_Review_DB::count_total_blocklist( '' ) > 0;
}
