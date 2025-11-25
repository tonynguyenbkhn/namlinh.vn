<?php
/**
 * Privacy class
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Privacy' ) ) {
	/**
	 * Privacy Class
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Privacy extends YITH_Privacy_Plugin_Abstract {

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			parent::__construct( YITH_YWAR_PLUGIN_NAME );

			add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ) );
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ) );
		}

		/**
		 * Get privacy message
		 *
		 * @param string $section The section name.
		 *
		 * @return  string
		 * @since   2.0.0
		 */
		public function get_privacy_message( $section ): string {

			$section = str_replace( '_', '-', $section );

			ob_start();
			yith_ywar_get_view( "privacy/content-$section.php" );

			return ob_get_clean();
		}

		/**
		 * Registers the personal data exporter for the plugin.
		 *
		 * @param array $exporters An array of personal data exporters.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function register_exporters( array $exporters ): array {
			$exporters['yith-ywar-review-exporter']    = array(
				'exporter_friendly_name' => YITH_YWAR_PLUGIN_NAME,
				'callback'               => array( $this, 'review_data_exporter' ),
			);
			$exporters['yith-ywar-reminder-exporter']  = array(
				'exporter_friendly_name' => YITH_YWAR_PLUGIN_NAME . ': ' . esc_html_x( 'Review reminder module scheduled emails', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'callback'               => array( $this, 'reminder_data_exporter' ),
			);
			$exporters['yith-ywar-blocklist-exporter'] = array(
				'exporter_friendly_name' => YITH_YWAR_PLUGIN_NAME . ': ' . esc_html_x( 'Review reminder module blocklist', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'callback'               => array( $this, 'blocklist_data_exporter' ),
			);

			return $exporters;
		}

		/**
		 * Finds and exports personal data associated with an email address from the posts table.
		 *
		 * @param string $email_address The comment author email address.
		 * @param int    $page          The current page.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function review_data_exporter( string $email_address, int $page = 1 ): array {

			$posts_per_page = 1;
			$page           = absint( $page );

			$reviews = yith_ywar_get_reviews(
				array(
					'posts_per_page' => $posts_per_page,
					'paged'          => $page,
					'post_status'    => 'all',
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'     => '_ywar_review_author_email',
							'value'   => $email_address,
							'compare' => '=',
						),
					),
				)
			);

			if ( empty( $reviews ) ) {
				return array(
					'data' => array(),
					'done' => true,
				);
			}

			$data_to_export = array();
			$prop_to_export = array(
				'review_author'       => esc_html_x( 'Author', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'review_author_email' => esc_html_x( 'Author email', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'review_author_IP'    => esc_html_x( 'Author IP', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'date_created'        => esc_html_x( 'Date', '[Admin panel] Column name and text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'title'               => esc_html_x( 'Title', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'content'             => esc_html_x( 'Content', '[Admin panel] Column name and text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'rating'              => esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ),
			);

			foreach ( $reviews as $review ) {
				$review_data_to_export = array();

				foreach ( $prop_to_export as $key => $name ) {

					switch ( $key ) {
						case 'date_created':
							$value = date_i18n( get_option( 'date_format' ), strtotime( $review->get_date_created()->getOffsetTimestamp() ) );
							break;
						case 'rating':
							$value = $review->{"get_$key"}();
							break;
						default:
							$value = $review->{"get_$key"}();
					}

					if ( ! empty( $value ) ) {
						$review_data_to_export[] = array(
							'name'  => $name,
							'value' => $value,
						);
					}
				}
				$data_to_export[] = array(
					'group_id'    => 'yith-ywar-reviews',
					'group_label' => esc_html_x( 'Reviews and Replies written', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
					'item_id'     => "review-{$review->get_id()}",
					'data'        => $review_data_to_export,
				);

			}
			$done = count( $reviews ) < $posts_per_page;

			return array(
				'data' => $data_to_export,
				'done' => $done,
			);
		}

		/**
		 * Finds and exports personal data associated with an email address from the scheduled reminders table.
		 *
		 * @param string $email_address The user email address.
		 * @param int    $page          The current page.
		 *
		 * @return  array
		 * @since   2.0.0
		 */
		public function reminder_data_exporter( string $email_address, int $page = 1 ): array {

			global $wpdb;

			YITH_YWAR_Request_Review_DB::define_tables();

			$number         = 500;
			$page           = absint( $page );
			$offset         = $number * ( $page - 1 );
			$data_to_export = array();
			$args           = array(
				'customer' => $email_address,
				'limit'    => -1,
			);
			$orders         = wc_get_orders( $args );
			$objects        = array();

			foreach ( $orders as $order ) {
				$objects[] = $order->get_id();

				// Retrieve any possible booking ID.
				$bookings = $order->get_meta( 'yith_bookings' );
				if ( ! empty( $bookings ) ) {
					foreach ( $bookings as $booking_id ) {
						$objects[] = $booking_id;
					}
				}
			}

			if ( empty( $objects ) ) {
				return array(
					'data' => array(),
					'done' => true,
				);
			}

			$objects = implode( ', ', $objects );

			$sql       = "
				SELECT      *
				FROM        $wpdb->yith_ywar_email_schedule
				WHERE       object_id IN ($objects) 
				ORDER BY    object_id ASC
				LIMIT       $offset ,{$number}
				";
			$reminders = $wpdb->get_results( $sql ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			if ( empty( $reminders ) ) {
				return array(
					'data' => array(),
					'done' => true,
				);
			}

			$reminder_prop_to_export = array(
				'object_id'      => esc_html_x( 'Item', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'request_items'  => esc_html_x( 'Items to review', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'order_date'     => esc_html_x( 'Completed order date', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'scheduled_date' => esc_html_x( 'Scheduled email date', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'mail_status'    => esc_html_x( 'Status', '[Admin panel] column name', 'yith-woocommerce-advanced-reviews' ),
			);

			foreach ( (array) $reminders as $reminder ) {
				$reminder_data_to_export = array();

				foreach ( $reminder_prop_to_export as $key => $name ) {

					switch ( $key ) {
						case 'order_date':
						case 'scheduled_date':
							$value = date_i18n( get_option( 'date_format' ), strtotime( $reminder->{$key} ) );
							break;
						case 'request_items':
							$items       = maybe_unserialize( $reminder->request_items );
							$items_names = array();
							if ( ! empty( $items ) ) {
								foreach ( $items as $item ) {
									$product       = wc_get_product( $item );
									$items_names[] = $product ? $product->get_name() : esc_html_x( 'Deleted product', '[Admin panel] Generic label for products reviewed that were deleted', 'yith-woocommerce-advanced-reviews' );
								}
							}
							$value = implode( ', ', $items_names );
							break;
						case 'object_id':
							/* translators: %s order/booking number */
							$value = sprintf( 'booking' === $reminder->object_type ? esc_html_x( 'Booking #$s', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Order #$s', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ), $reminder->{$key} );
							break;
						default:
							$value = $reminder->{$key};
					}

					if ( ! empty( $value ) ) {
						$reminder_data_to_export[] = array(
							'name'  => $name,
							'value' => $value,
						);
					}
				}
				$data_to_export[] = array(
					'group_id'    => 'yith-ywar-review-requests',
					'group_label' => esc_html_x( 'Scheduled review reminders', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
					'item_id'     => "reminder-$reminder->id",
					'data'        => $reminder_data_to_export,
				);

			}

			$done = count( $reminders ) < $number;

			return array(
				'data' => $data_to_export,
				'done' => $done,
			);
		}

		/**
		 * Finds and exports personal data associated with an email address from the blocklist table.
		 *
		 * @param string $email_address The user email address.
		 *
		 * @return  array
		 * @since   2.0.0
		 */
		public function blocklist_data_exporter( string $email_address ): array {
			global $wpdb;

			YITH_YWAR_Request_Review_DB::define_tables();

			$data_to_export = array();

			$is_blocklist = $wpdb->get_var( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"
                    SELECT  COUNT(*)
                    FROM    $wpdb->yith_ywar_email_blocklist 
                    WHERE customer_email = %s 
                    ",
					$email_address
				)
			);

			if ( $is_blocklist ) {
				$data_to_export[] = array(
					'group_id'    => 'yith-ywar-review-request-blocklist',
					'group_label' => esc_html_x( 'Review reminder status', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
					'item_id'     => 'blocklist-0',
					'data'        => array(
						array(
							'name'  => esc_html_x( 'Blocklist', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
							'value' => esc_html_x( "This customer doesn't want to receive any more review reminders.", '[Global] text for GDPR exporter or eraser and for order page send box description', 'yith-woocommerce-advanced-reviews' ),
						),
					),
				);
			}

			return array(
				'data' => $data_to_export,
				'done' => true,
			);
		}

		/**
		 * Registers the personal data erasers for the plugin.
		 *
		 * @param array $erasers An array of personal data erasers.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function register_erasers( array $erasers ): array {
			$erasers['yith-ywar-review-eraser']    = array(
				'eraser_friendly_name' => YITH_YWAR_PLUGIN_NAME,
				'callback'             => array( $this, 'review_data_eraser' ),
			);
			$erasers['yith-ywar-reminder-eraser']  = array(
				'eraser_friendly_name' => YITH_YWAR_PLUGIN_NAME . ': ' . esc_html_x( 'Review reminder module scheduled emails', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'callback'             => array( $this, 'reminder_data_eraser' ),
			);
			$erasers['yith-ywar-blocklist-eraser'] = array(
				'eraser_friendly_name' => YITH_YWAR_PLUGIN_NAME . ': ' . esc_html_x( 'Review reminder module blocklist', '[Admin panel] text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' ),
				'callback'             => array( $this, 'blocklist_data_eraser' ),
			);

			return $erasers;
		}

		/**
		 * Anonymizes personal data associated with an email address from the posts table.
		 *
		 * @param string $email_address The comment author email address.
		 * @param int    $page          The current page.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function review_data_eraser( string $email_address, int $page = 1 ): array {

			$posts_per_page = 1;
			$page           = absint( $page );
			$items_removed  = false;
			$items_retained = false;
			$messages       = array();
			$reviews        = yith_ywar_get_reviews(
				array(
					'posts_per_page' => $posts_per_page,
					'paged'          => $page,
					'post_status'    => 'all',
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'     => '_ywar_review_author_email',
							'value'   => $email_address,
							'compare' => '=',
						),
					),
				)
			);
			if ( empty( $reviews ) ) {
				return array(
					'items_removed'  => false,
					'items_retained' => false,
					'messages'       => array(),
					'done'           => true,
				);
			}

			foreach ( $reviews as $review ) {
				$review->set_review_author( wp_privacy_anonymize_data( 'text' ) );
				$review->set_review_author_email( wp_privacy_anonymize_data( 'email' ) );
				$review->set_review_author_IP( wp_privacy_anonymize_data( 'ip' ) );
				$review->set_review_user_id( 0 );
				$review->set_review_author_custom_avatar( '' );
				$review->save();
			}

			$done = count( $reviews ) < $posts_per_page;

			return array(
				'items_removed'  => $items_removed,
				'items_retained' => $items_retained,
				'messages'       => $messages,
				'done'           => $done,
			);
		}

		/**
		 * Delete personal data associated with an email address from the scheduled reminders table.
		 *
		 * @param string $email_address The user email address.
		 * @param int    $page          The current page.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function reminder_data_eraser( string $email_address, int $page = 1 ): array {

			global $wpdb;

			YITH_YWAR_Request_Review_DB::define_tables();

			$number  = 500;
			$page    = absint( $page );
			$offset  = $number * ( $page - 1 );
			$args    = array(
				'customer' => $email_address,
				'limit'    => -1,
			);
			$orders  = wc_get_orders( $args );
			$objects = array();

			foreach ( $orders as $order ) {
				$objects[] = $order->get_id();

				// Retrieve any possible booking ID.
				$bookings = $order->get_meta( 'yith_bookings' );
				if ( ! empty( $bookings ) ) {
					foreach ( $bookings as $booking_id ) {
						$objects[] = $booking_id;
					}
				}
			}

			if ( empty( $objects ) ) {
				return array(
					'items_removed'  => false,
					'items_retained' => false,
					'messages'       => array(),
					'done'           => true,
				);
			}

			$objects = implode( ', ', $objects );

			$sql       = "
				SELECT      *
				FROM        $wpdb->yith_ywar_email_schedule
				WHERE       object_id IN ($objects) 
				ORDER BY    object_id ASC
				LIMIT       $offset ,{$number}
				";
			$reminders = $wpdb->get_results( $sql ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

			if ( empty( $reminders ) ) {
				return array(
					'items_removed'  => false,
					'items_retained' => false,
					'messages'       => array(),
					'done'           => true,
				);
			}

			foreach ( (array) $reminders as $reminder ) {
				$wpdb->delete( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->yith_ywar_email_schedule,
					array( 'id' => $reminder ),
					array( '%d' )
				);
			}

			$done = count( $reminders ) < $number;

			return array(
				'items_removed'  => true,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => $done,
			);
		}

		/**
		 * Erases personal data associated with an email address from the blocklist table.
		 *
		 * @param string $email_address The user email address.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function blocklist_data_eraser( string $email_address ): array {
			global $wpdb;

			if ( empty( $email_address ) ) {
				return array(
					'items_removed'  => false,
					'items_retained' => false,
					'messages'       => array(),
					'done'           => true,
				);
			}

			$deleted = $wpdb->delete( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->yith_ywar_email_blocklist,
				array( 'customer_email' => $email_address ),
				array( '%s' )
			);

			$items_removed = $deleted > 0;

			return array(
				'items_removed'  => $items_removed,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);
		}
	}
}

new YITH_YWAR_Privacy();
