<?php
/**
 * Class YITH_YWAR_Scheduled_Emails_Admin_Table.
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Admin\AdminTables
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Scheduled_Emails_Admin_Table' ) ) {
	/**
	 * Class YITH_YWAR_Scheduled_Emails_Admin_Table.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview\Admin\AdminTables
	 */
	class YITH_YWAR_Scheduled_Emails_Admin_Table extends WP_List_Table {

		/**
		 * Is the table filtered?
		 *
		 * @var bool
		 */
		private $filtered = false;

		/**
		 * YITH_YWAR_Scheduled_Emails_Admin_Table constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'schedule',  // singular name of the listed records.
					'plural'   => 'schedules', // plural name of the listed records.
					'ajax'     => false,       // does this table support ajax?.
				)
			);

			$this->handle_bulk_action();
		}

		/**
		 * Returns columns available in table
		 *
		 * @return array Array of columns of the table.
		 * @since  2.0.0
		 */
		public function get_columns(): array {
			return array(
				'cb'             => '<input type="checkbox" />',
				'object_id'      => yith_ywar_booking_enabled() ? esc_html_x( 'Order or Booking', '[Admin panel] Generic description. Refer to a WC Order or YITH Booking', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Order', '[Admin panel] Generic description. Refer to a WC Order', 'yith-woocommerce-advanced-reviews' ),
				'order_date'     => esc_html_x( 'Completed on', '[Admin panel] column name', 'yith-woocommerce-advanced-reviews' ) . ':',
				'scheduled_date' => esc_html_x( 'Email delivery date', '[Admin panel] column name', 'yith-woocommerce-advanced-reviews' ) . ':',
				'mail_status'    => esc_html_x( 'Status', '[Admin panel] column name', 'yith-woocommerce-advanced-reviews' ),
				'actions'        => '',
			);
		}

		/**
		 * Print default column content
		 *
		 * @param object $item        Item of the row.
		 * @param string $column_name Column name.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function column_default( $item, $column_name ): string {
			$column = '';
			switch ( $column_name ) {
				case 'object_id':
					$order            = false;
					$object_edit_link = '';
					$object_desc      = '';
					$object_id        = '';

					if ( 'order' === $item->mail_type ) {
						$order = wc_get_order( $item->$column_name );
						if ( $order ) {
							$object_edit_link = esc_url( $order->get_edit_order_url() );
							$object_desc      = esc_html_x( 'Order', '[Admin panel] Generic description. Refer to a WC Order', 'yith-woocommerce-advanced-reviews' );
							$object_id        = esc_attr( $order->get_order_number() );
						}
					} elseif ( 'booking' === $item->mail_type && yith_ywar_booking_enabled() ) {
						$booking = yith_get_booking( $item->$column_name );
						if ( $booking ) {
							$order = $booking->get_order();
							if ( $order ) {
								$object_edit_link = esc_url( $booking->get_edit_link() );
								$object_desc      = esc_html_x( 'Booking', '[Admin panel] Schedule list object description', 'yith-woocommerce-advanced-reviews' );
								$object_id        = esc_attr( $booking->get_id() );
							}
						}
					}

					if ( $order ) {
						$first_name = $order->get_billing_first_name();
						$last_name  = $order->get_billing_last_name();

						if ( $first_name || $last_name ) {
							$username = trim( sprintf( '%1$s %2$s', $first_name, $last_name ) );
						} else {
							$username = esc_html_x( 'Guest user', '[Admin panel] label to use when the user is not registered', 'yith-woocommerce-advanced-reviews' );
						}

						$object_number = sprintf( '<a target="_blank" href="%1$s">%2$s #%3$s</a>', $object_edit_link, $object_desc, $object_id );

						/* translators: %1$s order/booking number, %2$s customer name, %3$s customer email - Example: Order #1234 by John Doe - johndoe@foo.bar */
						$column = sprintf( esc_html_x( '%1$s by %2$s - %3$s', '[Admin panel] String for joining other strings', 'yith-woocommerce-advanced-reviews' ), $object_number, $username, esc_html( $order->get_billing_email() ) );
					}

					break;
				case 'order_date':
				case 'scheduled_date':
					$column = ucwords( date_i18n( get_option( 'date_format' ), wp_kses_post( yit_datetime_to_timestamp( $item->$column_name ) ) ) );
					break;
				case 'mail_status':
					switch ( $item->$column_name ) {
						case 'sent':
							$class  = 'sent';
							$status = esc_html_x( 'Sent', '[Admin panel] Scheduled email status', 'yith-woocommerce-advanced-reviews' );
							break;
						case 'cancelled':
							$class  = 'cancelled';
							$status = esc_html_x( 'Canceled', '[Admin panel] Scheduled email status', 'yith-woocommerce-advanced-reviews' );
							break;
						default:
							$class  = 'on-hold';
							$status = esc_html_x( 'On hold', '[Admin panel] Scheduled email status', 'yith-woocommerce-advanced-reviews' );
					}

					$column = sprintf( '<span class="mail-status mail-%1$s">%2$s</span>', $class, $status );
					break;
				case 'actions':
					$actions['delete'] = array(
						'type'   => 'action-button',
						'title'  => esc_html_x( 'Cancel schedule', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ),
						'action' => 'delete',
						'icon'   => 'trash',
						'url'    => '',
						'class'  => 'action__set_cancelled',
						'data'   => array(
							'id' => $item->id,
						),
					);

					$column = yith_plugin_fw_get_action_buttons( $actions );
					break;
			}

			return $column;
		}

		/**
		 * Column cb.
		 *
		 * @param object $item .
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function column_cb( $item ): string {
			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->id );
		}

		/**
		 * Get bulk actions
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_bulk_actions(): array {
			return array( 'set_cancelled' => esc_html_x( 'Cancel schedule', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ) );
		}

		/**
		 * Process Bulk Actions
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function handle_bulk_action() {
			$action = $this->current_action();

			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-' . $this->_args['plural'] ) ) {
				return;
			}

			$items = isset( $_REQUEST[ $this->_args['singular'] ] ) ? wp_unslash( $_REQUEST[ $this->_args['singular'] ] ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( $action && ! empty( $items ) ) {
				if ( 'set_cancelled' === $action ) {
					foreach ( $items as $item ) {
						yith_ywar_update_schedule_status( 'cancelled', $item );
					}
					isset( $_REQUEST['_wp_http_referer'] ) && wp_safe_redirect( sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ) );
				}
			}
		}

		/**
		 * Prepare items for table
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function prepare_items() {

			if ( ! isset( $_REQUEST['s'] ) && isset( $_REQUEST['_wp_http_referer'] ) && ! empty( $_REQUEST['_wp_http_referer'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter.
				wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'action', 'action2' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
				exit;
			}

			$mail_status      = isset( $_REQUEST['mail_status'] ) && 'all' !== $_REQUEST['mail_status'] ? sanitize_text_field( wp_unslash( $_REQUEST['mail_status'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$search_param     = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$limit            = $this->get_items_per_page( 'mails_per_page', 10 );
			$this->filtered   = ( '' !== $mail_status || '' !== $search_param ) ?? false;
			$view_columns     = $this->get_columns();
			$hidden_columns   = array();
			$sortable_columns = $this->get_sortable_columns();

			// Here we configure table headers, defined in our methods.
			$this->_column_headers = array( $view_columns, $hidden_columns, $sortable_columns );

			// Will be used in pagination settings.
			$total_items = YITH_YWAR_Request_Review_DB::count_total_schedules( $mail_status, $search_param );

			// Prepare query params, as usual current page, order by and order direction.
			$paged = isset( $_GET['paged'] ) ? $limit * ( intval( $_GET['paged'] ) - 1 ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$this->items = YITH_YWAR_Request_Review_DB::list_schedules( $mail_status, $search_param, $limit, $paged );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $limit,
					'total_pages' => ceil( $total_items / $limit ),
				)
			);
		}

		/**
		 * Display the table
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function display() {
			if ( ! $this->has_items() && ! $this->filtered ) {

				yith_plugin_fw_get_component(
					array(
						'type'     => 'list-table-blank-state',
						'icon_url' => YITH_YWAR_ASSETS_URL . '/images/email.svg',
						'message'  => esc_html_x( 'No email scheduled.', '[Admin panel] message to display if no email is scheduled', 'yith-woocommerce-advanced-reviews' ),
						'cta'      => array(
							'title' => esc_html_x( 'Add new', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ),
							'class' => 'yith-ywar-add-emails',
							'url'   => '#',
							'icon'  => 'plus',
						),
					)
				);
			} else {
				$singular = $this->_args['singular'];

				$this->display_tablenav( 'top' );
				$this->screen->render_screen_reader_content( 'heading_list' );
				?>
				<div class="yith-plugin-ui__wp-list-auto-h-scroll">
					<table class="wp-list-table boxed <?php echo esc_html( implode( ' ', $this->get_table_classes() ) ); ?>">
						<?php $this->print_table_description(); ?>
						<thead>
						<tr>
							<?php $this->print_column_headers(); ?>
						</tr>
						</thead>
						<tbody id="the-list" <?php echo ( $singular ) ? wp_kses_post( " data-wp-lists='list:$singular'" ) : ''; ?>>
						<?php $this->display_rows_or_placeholder(); ?>
						</tbody>
						<tfoot>
						<tr>
							<?php $this->print_column_headers( false ); ?>
						</tr>
						</tfoot>
					</table>
				</div>
				<?php
				$this->display_tablenav( 'bottom' );
			}
		}

		/**
		 * Display the search box.
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );

			if ( ! $this->has_items() ) {
				return;
			}

			?>
			<input type="hidden" name="page" value="<?php echo esc_attr( YITH_YWAR_Admin::PANEL_PAGE ); ?>"/>
			<input type="hidden" name="tab" value="<?php echo esc_attr( YITH_YWAR_Request_Review::KEY ); ?>"/>
			<input type="hidden" name="sub_tab" value="<?php echo esc_attr( YITH_YWAR_Request_Review::KEY ); ?>-list"/>
			<?php
			$button_cancelled = '<a href="#" class="yith-ywar-bulk-button yith-plugin-fw__button--secondary" data-action="clear_cancelled_emails">' . esc_html_x( 'Remove Canceled', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ) . '</a>';
			$button_sent      = '<a href="#" class="yith-ywar-bulk-button yith-plugin-fw__button--secondary" data-action="clear_sent_emails">' . esc_html_x( 'Remove Sent', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ) . '</a>';
			?>
			<div class="yith-ywar-bulk-buttons">
				<?php
				/* translators: %1$s, %2$s buttons */
				printf( esc_html_x( 'Clear table: %1$s or %2$s', '[Admin panel] Action buttons with conjunction text', 'yith-woocommerce-advanced-reviews' ), wp_kses_post( $button_cancelled ), wp_kses_post( $button_sent ) );
				?>
			</div>
			<?php
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @param string $which The placement, one of 'top' or 'bottom'.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				$current_type = isset( $_REQUEST['mail_status'] ) && ! empty( $_REQUEST['mail_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mail_status'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$options      = array(
					'all'       => esc_html_x( 'All emails', '[Admin panel] Scheduled email status option', 'yith-woocommerce-advanced-reviews' ),
					'pending'   => esc_html_x( 'On hold', '[Admin panel] Scheduled email status', 'yith-woocommerce-advanced-reviews' ),
					'cancelled' => esc_html_x( 'Canceled', '[Admin panel] Scheduled email status', 'yith-woocommerce-advanced-reviews' ),
					'sent'      => esc_html_x( 'Sent', '[Admin panel] Scheduled email status', 'yith-woocommerce-advanced-reviews' ),
				);
				?>
				<div class="alignleft actions">
					<select name="mail_status" id="mail_status" class="wc-enhanced-select">
						<?php foreach ( $options as $key => $option ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_type, $key ); ?>><?php echo esc_html( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php
				submit_button(
					esc_html_x( 'Filter', '[Admin panel] Filter button label', 'yith-woocommerce-advanced-reviews' ),
					'filter-button',
					false,
					false,
					array(
						'id' => 'yith-ywar-schedule-filter-submit',
					)
				);

				if ( $this->filtered ) {
					?>
					<a id="yith-plugin-fw__wp-list__reset-filters" class="yith-plugin-fw__button--tertiary" href="<?php echo esc_url( add_query_arg( array() ) ); ?>"><?php echo esc_html_x( 'Reset filters', '[Admin panel] Reset filter button label', 'yith-woocommerce-advanced-reviews' ); ?></a>
					<?php
				}
			}
		}
	}
}
