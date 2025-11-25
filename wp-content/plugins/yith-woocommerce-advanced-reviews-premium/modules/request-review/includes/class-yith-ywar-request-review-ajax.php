<?php
/**
 * Class YITH_YWAR_Request_Review_AJAX
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Request_Review_AJAX' ) ) {
	/**
	 * Class YITH_YWAR_Request_Review_AJAX
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview
	 */
	class YITH_YWAR_Request_Review_AJAX {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * YITH_YWAR_Request_Review_AJAX constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			add_action( 'yith_ywar_admin_ajax_clear_sent_emails', array( $this, 'ajax_clear_sent_emails' ) );
			add_action( 'yith_ywar_admin_ajax_clear_cancelled_emails', array( $this, 'ajax_clear_cancelled_emails' ) );
			add_action( 'yith_ywar_admin_ajax_set_email_cancelled', array( $this, 'ajax_set_email_cancelled' ) );
			add_action( 'yith_ywar_admin_ajax_mass_schedule', array( $this, 'ajax_mass_schedule' ) );
			add_action( 'yith_ywar_admin_ajax_add_to_blocklist', array( $this, 'ajax_add_to_blocklist' ) );
			add_action( 'yith_ywar_admin_ajax_delete_from_blocklist', array( $this, 'ajax_delete_from_blocklist' ) );
			add_action( 'yith_ywar_admin_ajax_schedule_single_email', array( $this, 'ajax_schedule_single_email' ) );
			add_action( 'yith_ywar_admin_ajax_reschedule_single_email', array( $this, 'ajax_reschedule_single_email' ) );
			add_action( 'yith_ywar_admin_ajax_send_request_mail', array( $this, 'ajax_send_request_mail' ) );
			add_action( 'yith_ywar_frontend_ajax_unsubscribe_user', array( $this, 'ajax_unsubscribe_user' ) );
		}

		/**
		 * Clear "Sent" emails.
		 *
		 * @return void
		 * @throws Exception An Exception.
		 * @since  2.0.0
		 */
		public function ajax_clear_sent_emails() {
			$this->delete_emails_per_status( 'sent' );
		}

		/**
		 * Clear "Cancelled" emails.
		 *
		 * @return void
		 * @throws Exception An Exception.
		 * @since  2.0.0
		 */
		public function ajax_clear_cancelled_emails() {
			$this->delete_emails_per_status( 'cancelled' );
		}

		/**
		 * Delete emails with a specific status
		 *
		 * @param string $status The status ID.
		 *
		 * @return void
		 * @throws Exception An Exception.
		 * @since  2.0.0
		 */
		private function delete_emails_per_status( string $status ) {
			try {
				check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

				$deleted = YITH_YWAR_Request_Review_DB::delete_schedules( $status );

				if ( is_wp_error( $deleted ) ) {
					throw new Exception( esc_html_x( 'An error occurred.', '[Admin panel] generic error message', 'yith-woocommerce-advanced-reviews' ) );
				}

				wp_send_json_success(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'success',
							/* translators: %s number of items */
							'message'     => $deleted > 0 ? sprintf( _nx( '%s email deleted.', '%s emails deleted.', $deleted, '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ), $deleted ) : esc_html_x( 'No email deleted.', '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ),
						),
						false
					)
				);
			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $e->getMessage(),
						),
						false
					)
				);
			}
		}

		/**
		 * Set the status of a schedule to cancelled
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_set_email_cancelled() {

			isset( $_POST['id'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			yith_ywar_update_schedule_status( 'cancelled', sanitize_text_field( wp_unslash( $_POST['id'] ) ) );
			wp_send_json_success();
		}

		/**
		 * Mass schedule emails
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_mass_schedule() {

			isset( $_POST['schedule_type'], $_POST['start_date'], $_POST['end_date'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			try {
				$scheduled_orders = yith_ywar_get_scheduled_orders();
				$schedule_type    = sanitize_text_field( wp_unslash( $_POST['schedule_type'] ) );
				$start_date       = sanitize_text_field( wp_unslash( $_POST['start_date'] ) );
				$end_date         = sanitize_text_field( wp_unslash( $_POST['end_date'] ) );
				$order_args       = array(
					'status'  => array( 'wc-completed' ),
					'type'    => 'shop_order',
					'exclude' => $scheduled_orders,
					'parent'  => 0,
					'limit'   => -1,
				);

				if ( 'all' !== $schedule_type ) {
					switch ( true ) {
						case ( 'false' !== $start_date && 'false' === $end_date ):
							// Only start date set.
							$order_args['date_completed'] = '>' . $start_date;
							break;
						case ( 'false' === $start_date && 'false' !== $end_date ):
							// Only end date set.
							$order_args['date_completed'] = '<' . $end_date;
							break;
						case ( 'false' !== $start_date && 'false' !== $end_date ):
							$end_date = strtotime( $end_date ) + DAY_IN_SECONDS;
							// Both dates set.
							$order_args['date_completed'] = "$start_date...$end_date";
							break;
					}
				}

				// Get never scheduled orders.
				$orders = wc_get_orders( $order_args );
				$count  = 0;

				if ( ! empty( $orders ) ) {
					foreach ( $orders as $order ) {
						$customer_id    = $order->get_user_id();
						$customer_email = $order->get_billing_email();

						if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
							if ( yith_ywar_schedule_mail( $order ) ) {
								++$count;
							}
						}
					}
				}

				if ( yith_ywar_booking_enabled() ) {
					$scheduled_bookings = yith_ywar_get_scheduled_bookings();
					$booking_args       = array(
						'status'         => array( 'bk-completed' ),
						'exclude'        => $scheduled_bookings,
						'items_per_page' => -1,
						'return'         => 'bookings',
					);

					if ( 'all' !== $schedule_type ) {
						switch ( true ) {
							case ( 'false' !== $start_date && 'false' === $end_date ):
								// Only start date set.
								$booking_args['data_query'] = array(
									array(
										'key'      => 'to',
										'value'    => strtotime( $start_date ),
										'operator' => '>=',
									),
								);
								break;
							case ( 'false' === $start_date && 'false' !== $end_date ):
								// Only end date set.
								$booking_args['data_query'] = array(
									array(
										'key'      => 'to',
										'value'    => strtotime( $end_date ) + DAY_IN_SECONDS,
										'operator' => '<',
									),
								);
								break;
							case ( 'false' !== $start_date && 'false' !== $end_date ):
								// Both dates set.
								$booking_args['data_query'] = array(
									array(
										'key'      => 'to',
										'value'    => strtotime( $start_date ),
										'operator' => '>=',
									),
									array(
										'key'      => 'to',
										'value'    => strtotime( $end_date ) + DAY_IN_SECONDS,
										'operator' => '<',
									),
								);
								break;
						}
					}

					$bookings = yith_wcbk_get_bookings( $booking_args );

					if ( ! empty( $bookings ) ) {
						foreach ( $bookings as $booking ) {

							$order = $booking->get_order();
							if ( ! $order ) {
								continue;
							}
							$customer_id    = $order->get_user_id();
							$customer_email = $order->get_billing_email();

							if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
								if ( yith_ywar_schedule_booking_mail( $booking, $order ) ) {
									++$count;
								}
							}
						}
					}
				}

				if ( $count > 0 ) {
					wp_send_json_success(
						yith_plugin_fw_get_component(
							array(
								'type'        => 'notice',
								'notice_type' => 'success',
								/* translators: %s number of items */
								'message'     => sprintf( _nx( '%s email scheduled.', '%s emails scheduled.', $count, '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ), $count ),
							),
							false
						)
					);
				} else {
					wp_send_json_error(
						yith_plugin_fw_get_component(
							array(
								'type'        => 'notice',
								'notice_type' => 'warning',
								'message'     => esc_html_x( 'No actions are scheduled. This may be because there are no orders matching the conditions or reminder emails are already scheduled. ', '[Admin panel] Message to display if no order can be scheduled', 'yith-woocommerce-advanced-reviews' ),
							),
							false
						)
					);
				}
			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $e->getMessage(),
						),
						false
					)
				);
			}
		}

		/**
		 * Add a customer to the blocklist
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_add_to_blocklist() {
			isset( $_POST['email'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$customer_email = sanitize_text_field( wp_unslash( $_POST['email'] ) );
			$user           = get_user_by( 'email', $customer_email );
			$customer_id    = ( ! $user ? 0 : $user->ID );

			if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {

				try {
					yith_ywar_add_to_blocklist( $customer_id, $customer_email );

					wp_send_json_success(
						yith_plugin_fw_get_component(
							array(
								'type'        => 'notice',
								'notice_type' => 'success',
								/* translators: %s user email */
								'message'     => sprintf( esc_html_x( 'User %s added successfully', '[Admin panel] Bulk action success message', 'yith-woocommerce-advanced-reviews' ), '<b>' . $customer_email . '</b>' ),
							),
							false
						)
					);
				} catch ( Exception $e ) {
					yith_ywar_error( $e->getMessage() );
					wp_send_json_error(
						yith_plugin_fw_get_component(
							array(
								'type'        => 'notice',
								'notice_type' => 'error',
								'message'     => $e->getMessage(),
							),
							false
						)
					);
				}
			} else {
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							/* translators: %s user email */
							'message'     => sprintf( esc_html_x( 'User %s is already in the blocklist', '[Admin panel] Bulk action error message', 'yith-woocommerce-advanced-reviews' ), '<b>' . $customer_email . '</b>' ),
						),
						false
					)
				);
			}
		}

		/**
		 * Delete a customer from blocklist
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_delete_from_blocklist() {

			isset( $_POST['id'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );
			yith_ywar_delete_from_blocklist( sanitize_text_field( wp_unslash( $_POST['id'] ) ) );
			wp_send_json_success();
		}

		/**
		 * Schedule mail from order/booking details page
		 *
		 * @return void
		 * @throws Exception An exception.
		 * @since  2.0.0
		 */
		public function ajax_schedule_single_email() {

			isset( $_POST['object_id'], $_POST['object_type'], $_POST['schedule_date'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			try {
				$object_id     = sanitize_text_field( wp_unslash( $_POST['object_id'] ) );
				$object_type   = sanitize_text_field( wp_unslash( $_POST['object_type'] ) );
				$schedule_date = sanitize_text_field( wp_unslash( $_POST['schedule_date'] ) );

				if ( 'booking' === $object_type ) {
					$booking = yith_get_booking( $object_id );
					$order   = $booking->get_order();
					yith_ywar_schedule_booking_mail( $booking, $order, $schedule_date );
				} else {
					$order = wc_get_order( $object_id );
					yith_ywar_schedule_mail( $order, $schedule_date );
				}

				wp_send_json_success();

			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $e->getMessage(),
						),
						false
					)
				);
			}
		}

		/**
		 * Reschedule mail from order/booking details page
		 *
		 * @return void
		 * @throws Exception An exception.
		 * @since  2.0.0
		 */
		public function ajax_reschedule_single_email() {

			isset( $_POST['schedule_id'], $_POST['schedule_date'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			try {
				$schedule_id   = sanitize_text_field( wp_unslash( $_POST['schedule_id'] ) );
				$schedule_date = sanitize_text_field( wp_unslash( $_POST['schedule_date'] ) );

				yith_ywar_update_schedule( $schedule_id, $schedule_date, 'pending' );
				wp_send_json_success();

			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $e->getMessage(),
						),
						false
					)
				);
			}
		}

		/**
		 * Send mail from order/booking details page
		 *
		 * @return void
		 * @throws Exception An exception.
		 * @since  2.0.0
		 */
		public function ajax_send_request_mail() {

			isset( $_POST['object_id'], $_POST['object_type'], $_POST['schedule_id'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			try {
				$email       = false;
				$schedule_id = sanitize_text_field( wp_unslash( $_POST['schedule_id'] ) );
				if ( 0 === (int) $schedule_id ) {
					$today       = new DateTime( current_time( 'mysql' ) );
					$object_id   = sanitize_text_field( wp_unslash( $_POST['object_id'] ) );
					$object_type = sanitize_text_field( wp_unslash( $_POST['object_type'] ) );

					if ( 'booking' === $object_type ) {
						$booking   = yith_get_booking( $object_id );
						$order     = $booking->get_order();
						$scheduled = yith_ywar_schedule_booking_mail( $booking, $order, $today->format( 'Y-m-d' ) );
					} else {
						$order     = wc_get_order( $object_id );
						$scheduled = yith_ywar_schedule_mail( $order, $today->format( 'Y-m-d' ) );
					}

					if ( $scheduled ) {
						$email = yith_ywar_get_schedule_by_object( $object_id, $object_type );
					}
				} else {
					$email = yith_ywar_get_schedule_by_id( $schedule_id );
				}

				if ( ! $email ) {
					throw new Exception( 'Error' );
				}

				$email_result = yith_ywar_send_email( $email );
				if ( $email_result ) {
					$today = new DateTime( current_time( 'mysql' ) );

					yith_ywar_update_schedule( $email['id'], $today->format( 'Y-m-d' ), 'sent' );
					yith_ywar_update_sent_counter();
					wp_send_json_success();
				}
			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $e->getMessage(),
						),
						false
					)
				);
			}
		}

		/**
		 * Handles the unsubscribe form on frontend
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_unsubscribe_user() {

			$success = false;

			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$customer_id    = ! empty( $_POST['user_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;
			$customer_email = ! empty( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
			$decoded_email  = ! empty( $_POST['email_hash'] ) ? urldecode( base64_decode( sanitize_text_field( wp_unslash( $_POST['email_hash'] ) ) ) ) : ''; //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

			if ( empty( $customer_email ) || ! is_email( $customer_email ) ) {
				wc_add_notice( esc_html_x( 'Please insert a valid email address.', '[Global] Generic error message', 'yith-woocommerce-advanced-reviews' ), 'error' );
			} elseif ( $decoded_email !== $customer_email ) {
				wc_add_notice( esc_html_x( 'Please re-type the email address as provided.', '[Frontend] Unsubscribe page error message', 'yith-woocommerce-advanced-reviews' ), 'error' );
			} elseif ( $decoded_email === $customer_email ) {
				if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
					try {
						yith_ywar_add_to_blocklist( $customer_id, $customer_email );
						wc_add_notice( esc_html_x( 'Unsubscribe was successful.', '[Frontend] Unsubscribe page success message', 'yith-woocommerce-advanced-reviews' ) );
						$success = true;
					} catch ( Exception $e ) {
						/* translators: %s error message */
						wc_add_notice( esc_html_x( 'An error occurred.', '[Admin panel] generic error message', 'yith-woocommerce-advanced-reviews' ), 'error' );
						yith_ywar_error( $e->getMessage() );
					}
				} else {
					wc_add_notice( esc_html_x( 'You already unsubscribed.', '[Frontend] Unsubscribe page error message', 'yith-woocommerce-advanced-reviews' ), 'error' );
				}
			}

			if ( $success ) {
				wp_send_json_success( wc_print_notices( true ) );
			} else {
				wp_send_json_error( wc_print_notices( true ) );
			}
			//phpcs:enable
		}
	}
}
