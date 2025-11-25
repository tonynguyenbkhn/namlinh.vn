<?php
/**
 * Handle the Review Request admin functions.
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Admin
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Request_Review_Admin' ) ) {
	/**
	 * YITH_YWAR_Request_Review_Admin class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview\Admin
	 */
	class YITH_YWAR_Request_Review_Admin {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * YITH_YWAR_Request_Review_Admin constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			add_action( 'yith_ywar_print_scheduled_emails_tab', array( $this, 'add_email_list_table' ) );
			add_action( 'yith_ywar_print_blocklist_tab', array( $this, 'add_blocklist_table' ) );
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_column' ), 11 );
			add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_column' ), 11 );
			add_filter( 'manage_yith_booking_posts_columns', array( $this, 'add_column' ), 11 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_column' ), 3, 2 );
			add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_column' ), 3, 2 );
			add_action( 'manage_yith_booking_posts_custom_column', array( $this, 'render_column_bookings' ), 3, 2 );
			add_action( 'handle_bulk_actions-edit-yith_booking', array( $this, 'process_bulk_actions_booking' ), 10, 3 );
			add_action( 'handle_bulk_actions-edit-shop_order', array( $this, 'process_bulk_actions' ), 10, 3 );
			add_action( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'process_bulk_actions' ), 10, 3 );
			add_action( 'admin_notices', array( $this, 'bulk_admin_notices' ) );
			add_action( 'woocommerce_order_status_completed', array( $this, 'schedule_email_on_complete' ), 10, 2 );
			add_action( 'yith_wcbk_booking_status_completed', array( $this, 'schedule_email_on_complete' ), 10, 2 );
			add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
			add_filter( 'yith_wcbk_booking_metaboxes_array', array( $this, 'add_metabox_booking' ) );
			add_filter( 'yith_wcbk_booking_yith-ywar-metabox_print', array( $this, 'booking_output' ) );
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'unschedule_email_on_cancel_or_refund' ), 10 );
			add_action( 'woocommerce_order_status_refunded', array( $this, 'unschedule_email_on_cancel_or_refund' ), 10 );
		}

		/**
		 * Add scheduled emails table
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_email_list_table() {
			if ( yith_ywar_is_admin_page( 'panel/request-review/list' ) ) {
				$args = array(
					'list_table_class'     => 'YITH_YWAR_Scheduled_Emails_Admin_Table',
					'list_table_class_dir' => yith_ywar_get_module_path( 'request-review', 'includes/admin/admin-tables/class-yith-ywar-scheduled-emails-admin-table.php' ),
					'id'                   => 'scheduled-emails',
					'search_form'          => array(
						'text'     => yith_ywar_booking_enabled() ? esc_html_x( 'Order or Booking', '[Admin panel] Generic description. Refer to a WC Order or YITH Booking', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Order', '[Admin panel] Generic description. Refer to a WC Order', 'yith-woocommerce-advanced-reviews' ),
						'input_id' => 'schedule',
					),
				);

				$this->print_table_template( $args );
			}
		}

		/**
		 * Add blocklist table
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_blocklist_table() {
			if ( yith_ywar_is_admin_page( 'panel/request-review/blocklist' ) ) {
				$args = array(
					'list_table_class'     => 'YITH_YWAR_Blocklist_Admin_Table',
					'list_table_class_dir' => yith_ywar_get_module_path( 'request-review', 'includes/admin/admin-tables/class-yith-ywar-blocklist-admin-table.php' ),
					'id'                   => 'blocklist',
					'search_form'          => array(
						'text'     => esc_html_x( 'Search customer email', '[Admin panel] Search form placeholder', 'yith-woocommerce-advanced-reviews' ),
						'input_id' => 'customer',
					),
				);

				$this->print_table_template( $args );
			}
		}

		/**
		 * Print table template
		 *
		 * @param array $table_args The data that generates the table.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function print_table_template( array $table_args ) {
			list ( $table_id, $list_table_class, $list_table_class_dir, $search_form ) = yith_plugin_fw_extract( $table_args, 'id', 'list_table_class', 'list_table_class_dir', 'search_form' );

			include_once $list_table_class_dir;

			$list_table = new $list_table_class();

			?>
			<div id="<?php echo esc_attr( $table_id ); ?>" class="yith-plugin-fw-list-table yith-plugin-ui--classic-wp-list-style">
				<div class="yith-plugin-fw-list-table-container">
					<?php
					$list_table->prepare_items();
					$list_table->views();
					?>
					<form method="post">
						<?php
						if ( isset( $search_form ) ) {
							$list_table->search_box( $search_form['text'], $search_form['input_id'] );
						}
						$list_table->display();
						?>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Add the schedule column
		 *
		 * @param array $columns Table columns.
		 *
		 * @return  array
		 * @since   2.0.0
		 */
		public function add_column( array $columns ): array {
			if ( ! yith_ywar_vendor_check() ) {
				$columns['yith_request_status'] = esc_html_x( 'Review reminder', '[Admin panel] Order/booking page column name and option name', 'yith-woocommerce-advanced-reviews' );
			}

			return $columns;
		}

		/**
		 * Render the schedule column in orders page
		 *
		 * @param string       $column Column name.
		 * @param int|WC_Order $order  Order ID/Order Object.
		 *
		 * @return  void
		 * @since   2.0.0
		 */
		public function render_column( string $column, $order ) {

			if ( ! yith_ywar_vendor_check() && 'yith_request_status' === $column ) {

				if ( ! $order instanceof WC_Order ) {
					$order = wc_get_order( $order );
				}

				if ( ! $order ) {
					return;
				}

				yith_ywar_print_schedule_box( $order );
			}
		}

		/**
		 * Render the schedule column in bookings page
		 *
		 * @param string $column     Column name.
		 * @param int    $booking_id Post ID.
		 *
		 * @return  void
		 * @since   2.0.0
		 */
		public function render_column_bookings( string $column, int $booking_id ) {
			if ( ! yith_ywar_vendor_check() && 'yith_request_status' === $column ) {

				$booking = yith_get_booking( $booking_id );

				if ( ! $booking ) {
					return;
				}

				yith_ywar_print_schedule_box_booking( $booking );
			}
		}

		/**
		 * Trigger bulk actions to orders
		 *
		 * @param string $redirect_to The redirect address.
		 * @param string $action      The current action.
		 * @param array  $ids         The IDs to process.
		 *
		 * @return string
		 * @throws Exception An exception.
		 * @since  2.0.0
		 */
		public function process_bulk_actions( string $redirect_to, string $action, array $ids ): string {

			if ( yith_ywar_vendor_check() || strpos( $action, 'yith_ywar_' ) === false ) {
				return $redirect_to;
			}

			$count     = 0;
			$processed = false;
			foreach ( $ids as $id ) {
				$scheduled = yith_ywar_get_schedule_by_object( $id, 'order' );
				switch ( $action ) {
					case 'yith_ywar_send':
						if ( 0 === (int) $scheduled['id'] ) {
							$today        = new DateTime( current_time( 'mysql' ) );
							$order        = wc_get_order( $id );
							$is_scheduled = yith_ywar_schedule_mail( $order, $today->format( 'Y-m-d' ) );
							if ( $is_scheduled ) {
								$scheduled = yith_ywar_get_schedule_by_object( $id, 'order' );
							}
						}

						$email_result = yith_ywar_send_email( $scheduled );
						if ( $email_result ) {
							$today = new DateTime( current_time( 'mysql' ) );
							yith_ywar_update_schedule( $scheduled['id'], $today->format( 'Y-m-d' ), 'sent' );
							yith_ywar_update_sent_counter();
							$processed = true;
						}
						break;
					case 'yith_ywar_reschedule':
						$order          = wc_get_order( $id );
						$customer_id    = $order->get_user_id();
						$customer_email = $order->get_billing_email();

						if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
							if ( 0 === $scheduled['id'] ) {
								$processed = yith_ywar_schedule_mail( $order );
							} else {
								$schedule_date = gmdate( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + ' . yith_ywar_get_option( 'ywar_mail_schedule_day' ) . ' days' ) );
								$processed     = yith_ywar_update_schedule( $scheduled['id'], $schedule_date, 'pending' );
							}
						}
						break;
					case 'yith_ywar_cancel':
						if ( 0 !== $scheduled['id'] ) {
							yith_ywar_update_schedule_status( 'cancelled', $scheduled['id'] );
							$processed = true;
						}

						break;
				}
				if ( $processed ) {
					++$count;
				}
				$processed = false;

			}

			$redirect_to = add_query_arg(
				array(
					'yith_ywar_action' => $action,
					'changed'          => $count,
				),
				$redirect_to
			);

			return esc_url_raw( $redirect_to );
		}

		/**
		 * Trigger bulk actions to orders
		 *
		 * @param string $redirect_to The redirect address.
		 * @param string $action      The current action.
		 * @param array  $ids         The IDs to process.
		 *
		 * @return string
		 * @throws Exception An exception.
		 * @since  2.0.0
		 */
		public function process_bulk_actions_booking( string $redirect_to, string $action, array $ids ): string {

			if ( yith_ywar_vendor_check() ) {
				return $redirect_to;
			}

			$count     = 0;
			$processed = false;
			foreach ( $ids as $id ) {
				$scheduled = yith_ywar_get_schedule_by_object( $id, 'booking' );
				switch ( $action ) {
					case 'yith_ywar_send':
						if ( 0 === (int) $scheduled['id'] ) {
							$today        = new DateTime( current_time( 'mysql' ) );
							$booking      = yith_get_booking( $id );
							$order        = $booking->get_order();
							$is_scheduled = yith_ywar_schedule_booking_mail( $booking, $order, $today->format( 'Y-m-d' ) );
							if ( $is_scheduled ) {
								$scheduled = yith_ywar_get_schedule_by_object( $id, 'order' );
							}
						}

						$email_result = yith_ywar_send_email( $scheduled );
						if ( $email_result ) {
							$today = new DateTime( current_time( 'mysql' ) );
							yith_ywar_update_schedule( $scheduled['id'], $today->format( 'Y-m-d' ), 'sent' );
							yith_ywar_update_sent_counter();
							$processed = true;
						}
						break;
					case 'yith_ywar_reschedule':
						$booking        = yith_get_booking( $id );
						$order          = $booking->get_order();
						$customer_id    = $order->get_user_id();
						$customer_email = $order->get_billing_email();

						if ( ! yith_ywar_check_blocklist( $customer_id, $customer_email ) ) {
							if ( 0 === $scheduled['id'] ) {
								$processed = yith_ywar_schedule_mail( $order );
							} else {
								$schedule_date = gmdate( 'Y-m-d', strtotime( current_time( 'mysql' ) . ' + ' . yith_ywar_get_option( 'ywar_mail_schedule_day' ) . ' days' ) );
								$processed     = yith_ywar_update_schedule( $scheduled['id'], $schedule_date, 'pending' );
							}
						}
						break;
					case 'yith_ywar_cancel':
						if ( 0 !== $scheduled['id'] ) {
							yith_ywar_update_schedule_status( 'cancelled', $scheduled['id'] );
							$processed = true;
						}

						break;
				}
				if ( $processed ) {
					++$count;
				}
				$processed = false;

			}

			$redirect_to = add_query_arg(
				array(
					'yith_ywar_action' => $action,
					'changed'          => $count,
				),
				$redirect_to
			);

			return esc_url_raw( $redirect_to );
		}

		/**
		 * Show admin notices after bulk actions
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function bulk_admin_notices() {

			if ( empty( $_REQUEST['yith_ywar_action'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return;
			}

			$message = '';
			$number  = isset( $_REQUEST['changed'] ) ? absint( $_REQUEST['changed'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			switch ( $_REQUEST['yith_ywar_action'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				case 'yith_ywar_send':
					/* translators: %s emails number */
					$message = sprintf( _nx( 'Review reminder: %s email sent.', 'Review reminder: %s emails sent', $number, '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ), number_format_i18n( $number ) );
					break;
				case 'yith_ywar_reschedule':
					/* translators: %s emails number */
					$message = sprintf( _nx( 'Review reminder: %s email rescheduled.', 'Review reminder: %s emails rescheduled.', $number, '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ), number_format_i18n( $number ) );
					break;
				case 'yith_ywar_cancel':
					/* translators: %s emails number */
					$message = sprintf( _nx( 'Review reminder: %s email cancelled.', 'Review reminder: %s emails cancelled.', $number, '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ), number_format_i18n( $number ) );
					break;

			}

			if ( ! empty( $message ) ) {
				echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
			}
		}

		/**
		 * Schedule email on order complete.
		 *
		 * @param int                             $obj_id The object ID.
		 * @param WC_Order|YITH_WCBK_Booking|null $obj    The order/booking object.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function schedule_email_on_complete( int $obj_id, $obj = null ) {
			if ( 'YITH_WCBK_Booking' === get_class( $obj ) ) {
				$order = $obj->get_order();
				yith_ywar_schedule_booking_mail( $obj, $order );
			} else {
				yith_ywar_schedule_mail( $obj );
			}
		}

		/**
		 * Unchedule email on order cancelled/refunded.
		 *
		 * @param int $order_id The order ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function unschedule_email_on_cancel_or_refund( int $order_id ) {

			$scheduled = yith_ywar_get_schedule_by_object( $order_id, 'order' );
			if ( 0 !== $scheduled['id'] ) {
				yith_ywar_update_schedule_status( 'cancelled', $scheduled['id'] );
			}
		}

		/**
		 * Add a metabox on order page
		 *
		 * @param string $post_type The current post type.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_metabox( string $post_type ) {
			// TODO: HPOS - remove shop_order when removing support for older WC versions.
			if ( ! yith_ywar_vendor_check() && in_array( $post_type, array( wc_get_page_screen_id( 'shop-order' ), 'shop_order' ), true ) ) {
				add_meta_box( 'yith-ywar-metabox', esc_html_x( 'Ask for a review', '[Admin panel] Metabox title', 'yith-woocommerce-advanced-reviews' ), array( $this, 'output' ), $post_type, 'side', 'high' );
			}
		}

		/**
		 * Output Meta Box on order page
		 *
		 * @param WC_Order|WP_Post $post The post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function output( $post ) {

			if ( ! $post ) {
				return;
			}

			$order = $post instanceof WC_Order ? $post : wc_get_order( $post );

			if ( ! $order ) {
				return;
			}

			yith_ywar_print_schedule_box( $order );
		}

		/**
		 * Add a metabox on booking page
		 *
		 * @param array $metaboxes THe metaboxes array.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_metabox_booking( array $metaboxes ): array {

			$metaboxes[5] = array(
				'id'       => 'yith-ywar-metabox',
				'title'    => esc_html_x( 'Ask for a review', '[Admin panel] Metabox title', 'yith-woocommerce-advanced-reviews' ),
				'context'  => 'side',
				'priority' => 'high',
			);

			return $metaboxes;
		}

		/**
		 * Output Meta Box on booking page
		 *
		 * @param WP_Post $post The current Booking.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function booking_output( WP_Post $post ) {

			$booking = yith_get_booking( $post->ID );

			if ( ! $booking ) {
				return;
			}

			yith_ywar_print_schedule_box_booking( $booking );
		}
	}
}
