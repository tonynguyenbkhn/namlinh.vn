<?php
/**
 * Module functions
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Get mass schedule popup content.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_mass_schedule_popup_content(): string {

	$datepickers = yith_plugin_fw_get_field(
		array(
			'type'   => 'inline-fields',
			'id'     => 'date_range',
			'name'   => 'date_range',
			'fields' => array(
				'start_date' => array(
					'inline-label'      => esc_html_x( 'From', '[Admin panel] Datepicker label', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'datepicker',
					'custom_attributes' => array(
						'pattern'     => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
						'placeholder' => 'YYYY-MM-DD',
						'maxlenght'   => 10,
					),
					'data'              => array(
						'date-format' => 'yy-mm-dd',
					),
				),
				'end_date'   => array(
					'inline-label'      => esc_html_x( 'to', '[Admin panel] Datepicker label', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'datepicker',
					'custom_attributes' => array(
						'pattern'     => '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
						'placeholder' => 'YYYY-MM-DD',
						'maxlenght'   => 10,
					),
					'data'              => array(
						'date-format' => 'yy-mm-dd',
						'max-date'    => 0,
					),
				),
			),
		)
	);

	ob_start();
	if ( yith_ywar_booking_enabled() ) {
		echo esc_html_x( 'Schedule a review reminder email for all orders and bookings for which a request has never been generated.', '[Admin panel] Schedule email modal description', 'yith-woocommerce-advanced-reviews' );
	} else {
		echo esc_html_x( 'Schedule a review reminder email for all orders for which a request has never been generated.', '[Admin panel] Schedule email modal description', 'yith-woocommerce-advanced-reviews' );
	}
	?>
	<div class="yith-ywar-popup-radio-container">
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'      => 'schedule_request',
				'name'    => 'schedule_request',
				'type'    => 'radio',
				'options' => array(
					'all'        => yith_ywar_booking_enabled() ? esc_html_x( 'Schedule a request email for all orders and bookings', '[Admin panel] Schedule email modal option name', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Schedule a request email for all orders', '[Admin panel] Schedule email modal option name', 'yith-woocommerce-advanced-reviews' ),
					'date_range' => ( yith_ywar_booking_enabled() ? esc_html_x( 'Schedule a request email for orders and bookings placed within a certain timeframe', '[Admin panel] Schedule email modal option name', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Schedule a request email for orders placed within a certain timeframe', '[Admin panel] Schedule email modal option name', 'yith-woocommerce-advanced-reviews' ) ) . $datepickers,
				),
				'value'   => 'all',
			),
			true
		);
		?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Get single schedule popup content.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_schedule_popup_content(): string {

	$datepicker = yith_plugin_fw_get_field(
		array(
			'id'   => 'schedule_date',
			'name' => 'schedule_date',
			'type' => 'datepicker',
			'data' => array(
				'date-format' => 'yy-mm-dd',
				'min-date'    => 1,
			),
		)
	);

	ob_start();

	echo esc_html_x( 'Send this reminder email on', '[Admin panel] Order page send email modal description', 'yith-woocommerce-advanced-reviews' );

	?>
	<div class="yith-ywar-popup-radio-container">
		<?php
		yith_plugin_fw_get_field(
			array(
				'id'      => 'send_single_request',
				'name'    => 'send_single_request',
				'type'    => 'radio',
				'options' => array(
					'now'      => esc_html_x( 'Now', '[Admin panel] Order page send email modal option name', 'yith-woocommerce-advanced-reviews' ),
					'schedule' => sprintf( '%1$s<div class="datepicker-wrapper"><br/><small></small>%2$s</div>', esc_html_x( 'Choose a date', '[Admin panel] Order page send email modal option name', 'yith-woocommerce-advanced-reviews' ), $datepicker ),
				),
				'value'   => 'now',
			),
			true
		);
		?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Get add to blocklist popup content.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_blocklist_popup_content(): string {
	ob_start();

	echo esc_html_x( 'Enter the email address of the customer you want to add.', '[Admin panel] Blocklist modal description', 'yith-woocommerce-advanced-reviews' );

	yith_plugin_fw_get_field(
		array(
			'id'   => 'add_to_blocklist',
			'name' => 'add_to_blocklist',
			'type' => 'text',
		),
		true
	);

	return ob_get_clean();
}

/**
 * Check if current user is a vendor
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_vendor_check(): bool {

	$is_vendor = false;

	if ( yith_ywar_multivendor_enabled() ) {
		$vendor    = yith_wcmv_get_vendor( 'current', 'user' );
		$is_vendor = ( 0 !== $vendor->get_id() );
	}

	return $is_vendor;
}

/**
 * Get send box content
 *
 * @param int    $object_id   The order/booking object ID.
 * @param string $object_type The object type.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_get_send_box( int $object_id, string $object_type = 'order' ) {

	$schedule = yith_ywar_get_schedule_by_object( $object_id, $object_type );
	$date     = date_i18n( get_option( 'date_format' ), strtotime( $schedule['scheduled_date'] ) );

	?>
	<div class="yith-ywar-send-box" id="yith-ywar-<?php echo esc_attr( $object_id ); ?>">
		<?php
		switch ( $schedule['mail_status'] ) {
			case 'sent':
				?>
				<img src="<?php echo esc_url( yith_ywar_get_module_url( 'request-review', 'assets/images/email-sent.svg' ) ); ?>"/>
				<strong><?php echo esc_html_x( 'The request was sent on', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' ); ?>:</strong>
				<br/>
				<?php echo esc_html( $date ); ?>
				<br/>
				<?php
				$data_fields = array(
					'object-id'        => $object_id,
					'object-type'      => $object_type,
					'schedule-date'    => gmdate( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + 1 days' ) ),
					/* translators: %s send date */
					'additional-label' => sprintf( esc_html_x( 'An email was sent on %s. Pick a new date to reschedule it.', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' ), $date ),
					'schedule-id'      => $schedule['id'],
				);
				yith_ywar_actions_button( esc_html_x( 'Send a new request', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' ), 'yith-ywar-schedule-actions', $data_fields );
				break;
			case 'pending':
				?>
				<img src="<?php echo esc_url( yith_ywar_get_module_url( 'request-review', 'assets/images/email-pending.svg' ) ); ?>"/>
				<strong><?php echo esc_html_x( 'The request will be sent on', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' ); ?>:</strong>
				<br/>
				<?php
				$data_fields = array(
					'object-id'        => $object_id,
					'object-type'      => $object_type,
					'schedule-date'    => gmdate( 'Y-m-d', strtotime( $date ) ),
					/* translators: %s send date */
					'additional-label' => sprintf( esc_html_x( 'By default, the plugin will send the reminder on %s. Pick a new date to overwrite this setting.', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' ), $date ),
					'schedule-id'      => $schedule['id'],
				);
				yith_ywar_actions_button( $date, 'yith-ywar-schedule-actions', $data_fields );
				?>
				<br/>
				<a href="#" class="yith-ywar-schedule-delete" data-schedule-id="<?php echo esc_attr( $schedule['id'] ); ?>"><?php echo esc_html_x( 'Delete', '[Admin panel] Generic delete action label', 'yith-woocommerce-advanced-reviews' ); ?></a>
				<?php
				break;
			default:
				$data_fields = array(
					'object-id'     => $object_id,
					'object-type'   => $object_type,
					'schedule-date' => gmdate( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + 1 days' ) ),
					'schedule-id'   => 0,
				);
				yith_ywar_actions_button( esc_html_x( 'Send request', '[Admin panel] Order page send button', 'yith-woocommerce-advanced-reviews' ), 'yith-ywar-button-schedule', $data_fields );
		}
		?>
	</div>
	<?php
}

/**
 * Get message if reminder cannot be sent for specified item
 *
 * @param string $type The message type.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_get_noreview_message( string $type = '' ) {

	switch ( $type ) {
		case 'no-items':
			$message = esc_html_x( 'There are no reviewable items in this order', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' );
			break;
		case 'no-booking':
			$message = esc_html_x( 'This booking cannot be reviewed.', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' );
			break;
		default:
			$message = esc_html_x( "This customer doesn't want to receive any more review reminders.", '[Global] text for GDPR exporter or eraser and for order page send box description', 'yith-woocommerce-advanced-reviews' );
	}

	?>
	<div class="yith-ywar-no-review-box">
		<?php echo esc_html( $message ); ?>
	</div>
	<?php
}

/**
 * Outputs action buttons
 *
 * @param string $label       The label.
 * @param string $css_class   The CSS class.
 * @param array  $data_fields The data fields.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_actions_button( string $label, string $css_class, array $data_fields ) {
	$data = array();
	foreach ( $data_fields as $data_key => $data_value ) {
		$data[] = "data-$data_key=\"$data_value\"";
	}
	?>
	<a href="#" class="<?php echo esc_attr( $css_class ); ?>" <?php echo wp_kses_post( implode( ' ', $data ) ); ?>><?php echo esc_html( $label ); ?></a>
	<?php
}

/**
 * Get pages where load action assets
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_actions_page_assets(): array {
	$pages = array(
		'edit-shop_order',
		'shop_order',        // TODO: HPOS - remove shop_order when removing support for older WC versions.
		wc_get_page_screen_id( 'shop-order' ),
	);

	if ( yith_ywar_booking_enabled() ) {
		$pages[] = 'edit-' . YITH_WCBK_Post_Types::BOOKING;
		$pages[] = YITH_WCBK_Post_Types::BOOKING;
	}

	return $pages;
}

/**
 * Print the schedule box for orders
 *
 * @param WC_Order $order The order.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_print_schedule_box( WC_Order $order ) {

	$customer_id    = $order->get_user_id();
	$customer_email = $order->get_billing_email();

	if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {

		$is_funds    = $order->get_meta( '_order_has_deposit' ) === 'yes';
		$is_deposits = $order->get_created_via() === 'yith_wcdp_balance_order';
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

		if ( yith_ywar_check_reviewable_items( $order ) === 0 || $is_funds || $is_deposits || $is_renew || ! $can_ask_review ) {

			yith_ywar_get_noreview_message( 'no-items' );

			if ( yith_ywar_multivendor_enabled() ) {

				$suborders = YITH_Vendors_Orders::get_suborders( $order->get_id() );

				if ( ! empty( $suborders ) ) {
					?>
					<br/>
					<?php
					foreach ( $suborders as $suborder_id ) {
						$suborder = wc_get_order( $suborder_id );

						if ( yith_ywar_check_reviewable_items( $suborder ) === 0 ) {
							/* translators: %s suborder number */
							printf( esc_html_x( 'Suborder #%s has no reviewable items', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' ), esc_html( $suborder_id ) );
						} else {
							/**
							 * APPLY_FILTERS: yith_wcmv_edit_order_uri
							 *
							 * Get edit vendor order uri.
							 *
							 * @param string $uri         The order uri.
							 * @param int    $suborder_id The suborder ID.
							 *
							 * @return string
							 */
							$order_uri = apply_filters( 'yith_wcmv_edit_order_uri', wc_get_order( $suborder_id )->get_edit_order_url(), absint( $suborder_id ) );
							/* translators: %s suborder number */
							$link_text = sprintf( esc_html_x( 'Suborder %s has reviewable items', '[Admin panel] Order page send box description', 'yith-woocommerce-advanced-reviews' ), '<strong>#' . $suborder_id . '</strong>' );

							printf( '<a href="%s">%s</a><br />', esc_url( $order_uri ), wp_kses_post( $link_text ) );
						}
					}
				}
			}
		} else {
			yith_ywar_get_send_box( $order->get_id() );
		}
	} else {
		yith_ywar_get_noreview_message();
	}
}

/**
 * Print the schedule box for bookings
 *
 * @param YITH_WCBK_Booking $booking The booking.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_print_schedule_box_booking( YITH_WCBK_Booking $booking ) {
	$order = $booking->get_order();
	if ( ! $order ) {
		yith_ywar_get_noreview_message( 'no-booking' );

		return;
	}
	$customer_id    = $order->get_user_id();
	$customer_email = $order->get_billing_email();

	if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {

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

		if ( ! yith_ywar_items_has_comments_opened( $booking->get_product_id() ) || yith_ywar_user_has_commented( $booking->get_product_id(), $customer_email ) || ! $can_ask_review ) {
			yith_ywar_get_noreview_message( 'no-booking' );
		} else {
			yith_ywar_get_send_box( $booking->get_id(), 'booking' );
		}
	} else {
		yith_ywar_get_noreview_message();
	}
}

/**
 * Prepares and send the review request mail
 *
 * @param array{ id: int, object_id: int, request_items: string, order_date: string, mail_type: string } $email The email to be sent.
 *
 * @return bool
 * @throws Exception An exception.
 * @since  2.0.0
 */
function yith_ywar_send_email( array $email ): bool {

	$mail_type = '';

	if ( 'booking' === $email['mail_type'] ) {
		if ( ! yith_ywar_booking_enabled() ) {
			return false;
		} else {
			$booking   = yith_get_booking( $email['object_id'] );
			$order     = $booking->get_order();
			$mail_type = '_booking';
		}
	} else {
		$order = wc_get_order( $email['object_id'] );
	}

	$customer_id    = $order->get_user_id();
	$customer_email = $order->get_billing_email();

	if ( yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
		$scheduled = yith_ywar_get_schedule_by_object( $email['object_id'], $email['mail_type'] );
		if ( 0 !== $scheduled['id'] ) {
			yith_ywar_update_schedule_status( 'cancelled', $scheduled['id'] );
		}

		return false;
	}

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

	if ( ! $is_funds && ! $is_deposits && ! $is_renew && $can_ask_review ) {

		$list = maybe_unserialize( $email['request_items'] );

		if ( empty( $list ) ) {
			return false;
		}

		$today         = new DateTime( current_time( 'mysql' ) );
		$complete_date = new DateTime( $email['order_date'] );
		$args          = array(
			'user'           => array(
				'customer_name'      => $order->get_billing_first_name(),
				'customer_last_name' => $order->get_billing_last_name(),
				'customer_email'     => $order->get_billing_email(),
				'customer_id'        => $order->get_user_id(),
			),
			'completed_date' => $email['order_date'],
			'items'          => $list,
			'days_ago'       => $complete_date->diff( $today )->days,
			'language'       => $order->get_meta( 'wpml_language' ),
		);

		do_action( "yith_ywar_request_review$mail_type", $args );

		return true;
	}

	return false;
}

/**
 * Print the list of the items to review.
 *
 * @param string $button_text The button text.
 * @param array  $items       The items to review.
 * @param int    $customer_id The customer ID.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_items_to_review( string $button_text, array $items, int $customer_id ): string { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	ob_start();
	include yith_ywar_get_module_path( 'request-review', 'templates/emails/items-list.php' );

	return ob_get_clean();
}

/**
 * Get the possible unsbscribe page IDs
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_unsubscribe_page_ids(): array {
	return array(
		(int) get_option( 'yith-ywar-unsubscribe-page-id', 0 ),
		(int) get_option( 'ywrac_unsubscribe_page_id', 0 ),
		(int) get_option( 'ywrr_unsubscribe_page_id', 0 ), // Old Review Reminder plugin unsubscribe page, kept for legacy purpose.
	);
}

/**
 * Check if the current is an unsubscribe page.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_is_unsubscribe_page(): bool {
	global $post;
	if ( ! $post ) {
		return false;
	}

	return in_array( $post->ID, array_values( yith_ywar_get_unsubscribe_page_ids() ), true );
}

/**
 * Set the unsubscribe link in the emails.
 *
 * @param string          $text  The text of the link.
 * @param YITH_YWAR_Email $email The current email.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_set_unsubscribe_link( string $text, YITH_YWAR_Email $email ): string {

	$page_id = false;
	foreach ( yith_ywar_get_unsubscribe_page_ids() as $id ) {
		$page_status = get_post_status( $id );
		if ( $page_status && 'trash' !== $page_status ) {
			$page_id = $id;
			break;
		}
	}

	if ( ! $page_id ) {
		return '';
	}

	$query_args = array(
		'id'    => rawurlencode( base64_encode( $email->object['user']['customer_id'] ) ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		'email' => rawurlencode( base64_encode( $email->object['user']['customer_email'] ) ), //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		'type'  => 'yith_ywar',
	);

	$unsubscribe_page_id = yit_wpml_object_id( $page_id, 'page', true, $email->object['language'] );
	$unsubscribe_url     = esc_url( add_query_arg( $query_args, get_permalink( $unsubscribe_page_id ) ) );

	return sprintf( '<div class="yith-ywar-unsubscribe-link"><a href="%1$s">%2$s</a></div>', $unsubscribe_url, $text );
}

/**
 * Update sent ounter
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_update_sent_counter() {
	$count = get_option( YITH_YWAR_Request_Review::SENT_COUNTER );
	update_option( YITH_YWAR_Request_Review::SENT_COUNTER, $count + 1 );
}
