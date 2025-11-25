<?php
/**
 * Handle the Review Request module.
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Request_Review' ) ) {
	/**
	 * YITH_YWAR_Request_Review class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview
	 */
	class YITH_YWAR_Request_Review extends YITH_YWAR_Module {

		const KEY = 'request-review';

		const SENT_COUNTER = 'yith-ywar-sent-requests';

		/**
		 * On load.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_load() {
			YITH_YWAR_Request_Review_DB::define_tables();

			add_filter( 'yith_ywar_modules_admin_tabs', array( $this, 'add_settings_tab' ), 10 );
			add_filter( 'yith_ywar_emails', array( $this, 'add_module_emails' ), 20 );
			add_filter( 'woocommerce_email_styles', array( $this, 'email_style' ), 1001, 2 ); // use 1000 as priority to allow support for YITH  Email Templates.
			add_filter( 'woocommerce_email_actions', array( $this, 'add_email_actions' ) );
			add_filter( 'yith_ywar_email_settings_content_tab', array( $this, 'add_email_custom_content_fields' ), 10, 2 );
			add_filter( 'yith_ywar_email_settings_layout_tab', array( $this, 'add_email_custom_layout_fields' ), 10, 2 );
			add_filter( 'yith_ywar_assets_globals_admin', array( $this, 'assets_globals_admin' ) );
			add_action( 'yith_ywar_request_review_daily_check', array( $this, 'daily_schedule' ) );
			add_action( 'yith_ywar_request_review_process', array( $this, 'daily_schedule_process' ) );
			add_action( 'wp_loaded', array( $this, 'init_daily_schedule' ) );
			add_action( 'init', array( $this, 'set_plugin_requirements' ), 20 );
			add_action( 'woocommerce_update_option', array( $this, 'set_mass_reschedule' ), 10 );
			add_action( 'yit_panel_wc_after_update', array( $this, 'perform_mass_reschedule' ), 10 );
			add_filter( 'yith_ywar_product_permalink', array( $this, 'product_permalink' ), 10, 2 );
		}

		/**
		 * Set globals JS vars.
		 *
		 * @param array $globals The globals JS vars.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function assets_globals_admin( array $globals ): array {
			$globals['modals']['set_cancelled']          = array(
				'title'   => esc_html_x( 'Remove scheduled email', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'message' => esc_html_x( 'This email will be removed from the list. Do you wish to continue?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
				'button'  => esc_html_x( 'Yes, proceed.', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['modals']['clear_cancelled_emails'] = array(
				'title'   => esc_html_x( 'Remove canceled emails', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'message' => esc_html_x( 'Do you want to delete all canceled emails from the database?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
				'button'  => esc_html_x( 'Yes, proceed.', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['modals']['clear_sent_emails']      = array(
				'title'   => esc_html_x( 'Remove sent emails', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'message' => esc_html_x( 'Do you want to delete all sent emails from the database?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
				'button'  => esc_html_x( 'Yes, proceed.', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['modals']['schedule_new_emails']    = array(
				'title'   => esc_html_x( 'Schedule review reminder emails', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'content' => yith_ywar_get_mass_schedule_popup_content(),
				'button'  => esc_html_x( 'Schedule now', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['modals']['delete_from_blocklist']  = array(
				'title'   => esc_html_x( 'Remove customer from blocklist', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'message' => esc_html_x( 'This customer will be removed from the blocklist. Do you wish to continue?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
				'button'  => esc_html_x( 'Yes, proceed.', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['modals']['add_to_blocklist']       = array(
				'title'   => esc_html_x( 'Add customer to the blocklist', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'content' => yith_ywar_get_blocklist_popup_content(),
				'button'  => esc_html_x( 'Add customer', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['modals']['schedule_new_email']     = array(
				'title'   => esc_html_x( 'Send an email to request a review', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'content' => yith_ywar_get_schedule_popup_content(),
				'button'  => esc_html_x( 'Confirm', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['messages']['missing_date_error']   = esc_html_x( 'Please, select a date.', '[Admin panel] error message', 'yith-woocommerce-advanced-reviews' );
			$globals['bulk_actions']                     = array(
				'send_label'       => esc_html_x( 'Review reminder: Send email', '[Admin panel] bulk action name', 'yith-woocommerce-advanced-reviews' ),
				'reschedule_label' => esc_html_x( 'Review reminder: Reschedule email', '[Admin panel] bulk action name', 'yith-woocommerce-advanced-reviews' ),
				'cancel_label'     => esc_html_x( 'Review reminder: Cancel email', '[Admin panel] bulk action name', 'yith-woocommerce-advanced-reviews' ),
			);

			return $globals;
		}

		/**
		 * Custom email styles.
		 *
		 * @param string        $style WooCommerce style.
		 * @param WC_Email|null $email The current email.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function email_style( string $style, WC_Email $email = null ): string {

			if ( isset( $email ) && in_array( $email->id, array( 'yith-ywar-request-review', 'yith-ywar-request-review-booking' ), true ) ) {
				ob_start();
				include yith_ywar_get_module_path( 'request-review', 'assets/css/emails.css' );

				$button_colors = maybe_unserialize( $email->get_custom_option( 'button_colors' ) );

				?>
				.yith-ywar-items-table td.title-column .review-button {
				background-color : <?php echo esc_attr( $button_colors['background'] ); ?>;
				color            : <?php echo esc_attr( $button_colors['text'] ); ?>;
				}

				.yith-ywar-items-table td.title-column .review-button:hover {
				background-color : <?php echo esc_attr( $button_colors['background_hover'] ); ?> !important;
				color            : <?php echo esc_attr( $button_colors['text_hover'] ); ?> !important
				}
				<?php

				$style .= ob_get_clean();

			}

			return $style;
		}

		/**
		 * Add the module emails.
		 *
		 * @param array $emails Other plugin emails.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_module_emails( array $emails ): array {
			$emails['YITH_YWAR_Request_Review_Email'] = yith_ywar_get_module_path( 'request-review', 'includes/emails/class-yith-ywar-request-review-email.php' );
			if ( yith_ywar_booking_enabled() ) {
				$emails['YITH_YWAR_Request_Review_Booking_Email'] = yith_ywar_get_module_path( 'request-review', 'includes/emails/class-yith-ywar-request-review-booking-email.php' );
			}

			return $emails;
		}

		/**
		 * Add email actions to WooCommerce email actions
		 *
		 * @param array $actions Actions.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_email_actions( array $actions ): array {

			$actions[] = 'yith_ywar_request_review';
			$actions[] = 'yith_ywar_request_review_booking';

			return $actions;
		}

		/**
		 * Add option fields for the specifc email.
		 *
		 * @param array           $fields The email fields.
		 * @param YITH_YWAR_Email $email  The current email.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_email_custom_content_fields( array $fields, YITH_YWAR_Email $email ): array {

			if ( isset( $email ) && in_array( $email->id, array( 'yith-ywar-request-review', 'yith-ywar-request-review-booking' ), true ) ) {

				$fields['unsubscribe'] = array(
					'title'             => esc_html_x( 'Unsubscribe text', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'text',
					'custom_attributes' => array(
						'placeholder' => $email->get_default_unsubscribe_text(),
					),
					'default'           => '',
					'class'             => 'unsubscribe-text',
				);
			}

			return $fields;
		}

		/**
		 * Add option fields for the specifc email.
		 *
		 * @param array           $fields The email fields.
		 * @param YITH_YWAR_Email $email  The current email.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_email_custom_layout_fields( array $fields, YITH_YWAR_Email $email ): array {

			if ( isset( $email ) && in_array( $email->id, array( 'yith-ywar-request-review', 'yith-ywar-request-review-booking' ), true ) ) {
				$fields['button_colors'] = array(
					'title'             => esc_html_x( 'Button colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'multi-colorpicker',
					'colorpickers'      => array(
						array(
							'id'      => 'background',
							'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'default' => '#2E3A59',
							'data'    => array( 'prop' => 'background-color' ),
						),
						array(
							'id'      => 'text',
							'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'default' => '#FFFFFF',
							'data'    => array( 'prop' => 'color' ),
						),
						array(
							'id'      => 'background_hover',
							'name'    => esc_html_x( 'Background hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'default' => '#E5E8F0',
							'data'    => array( 'prop' => 'hover_bg' ),
						),
						array(
							'id'      => 'text_hover',
							'name'    => esc_html_x( 'Text hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'default' => '#2E3A59',
							'data'    => array( 'prop' => 'hover_text' ),
						),
					),
					'class'             => 'button-colors',
					'sanitize_callback' => array( $email, 'validate_yith_field' ),
				);
				$fields['button_label']  = array(
					'title'             => esc_html_x( 'Button label', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'text',
					'custom_attributes' => array(
						'placeholder' => $email->get_default_button_text(),
					),
					'default'           => '',
					'class'             => 'button-text',
				);
			}

			return $fields;
		}

		/**
		 * On activate.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_activate() {
			if ( YITH_YWAR_Request_Review_DB::DB_VERSION !== get_option( 'yith-ywar-request-review-db-version', '' ) ) {
				YITH_YWAR_Request_Review_DB::create_db_tables();
			}
		}

		/**
		 * Add admin scripts.
		 *
		 * @param array  $scripts The scripts.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function filter_scripts( array $scripts, string $context ): array {
			if ( 'admin' === $context ) {
				$scripts['yith-ywar-request-options'] = array(
					'src'     => $this->get_url( 'assets/js/admin/admin.js' ),
					'context' => 'admin',
					'deps'    => array(
						'jquery',
						'yith-ywar-admin-ajax',
						'jquery-ui-dialog',
					),
					'enqueue' => array(
						'panel/request-review/settings',
						'panel/request-review/list',
						'panel/request-review/blocklist',
					),
				);
				$scripts['yith-ywar-order-actions']   = array(
					'src'     => $this->get_url( 'assets/js/admin/actions.js' ),
					'context' => 'admin',
					'deps'    => array(
						'jquery',
						'yith-ywar-admin-ajax',
						'wc-backbone-modal',
						'yith-plugin-fw-fields',
					),
					'enqueue' => yith_ywar_actions_page_assets(),
				);
			} else {
				$scripts['yith-ywar-unsubscribe'] = array(
					'src'     => $this->get_url( 'assets/js/unsubscribe.js' ),
					'context' => 'frontend',
					'deps'    => array(
						'jquery',
						'yith-ywar-ajax',
					),
					'enqueue' => yith_ywar_is_unsubscribe_page(),
				);
			}

			return $scripts;
		}

		/**
		 * Add admin styles.
		 *
		 * @param array  $styles  The styles.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function filter_styles( array $styles, string $context ): array {

			if ( 'admin' === $context ) {
				$styles['yith-ywar-request-options'] = array(
					'src'     => $this->get_url( 'assets/css/admin/admin.css' ),
					'context' => 'admin',
					'enqueue' => array(
						'panel/request-review/settings',
						'panel/request-review/list',
						'panel/request-review/blocklist',
					),
				);
				$styles['yith-ywar-order-actions']   = array(
					'src'     => $this->get_url( 'assets/css/admin/actions.css' ),
					'context' => 'admin',
					'deps'    => array( 'yith-plugin-fw-fields' ),
					'enqueue' => yith_ywar_actions_page_assets(),
				);
			}

			return $styles;
		}

		/**
		 * Add admin panel tabs
		 *
		 * @param array $tabs The panel tab.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_settings_tab( array $tabs ): array {
			$tabs['request-review'] = array(
				'title'       => 'Review reminder',
				'icon'        => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"></path></svg>',
				'description' => esc_html_x( 'Enable the following modules to unlock additional features for your reviews.', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' ),
			);

			return $tabs;
		}

		/**
		 * Init daily scheduled actions.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_daily_schedule() {
			if ( ! wc()->queue()->get_next( 'yith_ywar_request_review_daily_check' ) ) {
				wc()->queue()->schedule_single(
					strtotime( '00:00 + 1 day' . yith_ywar_get_time_offset() ),
					'yith_ywar_request_review_daily_check',
					array(),
					'yith-ywar-request-review'
				);
			}
		}

		/**
		 * Check if emails should be sent today and set the scheduled actions.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function daily_schedule() {
			$total_schedules = yith_ywar_get_daily_schedules();
			if ( ! empty( $total_schedules ) ) {
				$group_schedules = array_chunk( $total_schedules, 25, false );
				$time            = 0;
				foreach ( $group_schedules as $schedule ) {
					wc()->queue()->schedule_single(
						time() + $time,
						'yith_ywar_request_review_process',
						array( 'emails' => $schedule ),
						'yith-ywar-request-review'
					);
					$time += ( MINUTE_IN_SECONDS * 10 );
				}
			}
		}

		/**
		 * Process daily emails
		 *
		 * @param array $emails The emails to be sent.
		 *
		 * @return void
		 * @throws Exception An exception.
		 * @since  2.0.0
		 */
		public function daily_schedule_process( $emails ) {
			foreach ( $emails as $email ) {
				$email_result = yith_ywar_send_email( $email );
				if ( $email_result ) {
					yith_ywar_update_schedule_status( 'sent', $email['id'] );
					yith_ywar_update_sent_counter();
				}
			}
		}

		/**
		 * Add Plugin Requirements
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_plugin_requirements() {

			$plugin_name  = YITH_YWAR_PLUGIN_NAME . ': ' . esc_html_x( 'Review reminder module', '[Admin panel] Module description for system status panel', 'yith-woocommerce-advanced-reviews' );
			$requirements = array(
				'wp_cron_enabled' => true,
			);
			yith_plugin_fw_add_requirements( $plugin_name, $requirements );
		}

		/**
		 * Check if scheduled emails should be rescheduled
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_mass_reschedule() {

			$actions = isset( $_POST['ywar_mail_reschedule'] ) ? wp_unslash( $_POST['ywar_mail_reschedule'] ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( false !== array_search( 'reschedule', $actions, true ) ) {

				$days_changed     = isset( $_POST['ywar_mail_schedule_day'] ) && yith_ywar_get_option( 'ywar_mail_schedule_day' ) !== sanitize_text_field( wp_unslash( $_POST['ywar_mail_schedule_day'] ) );//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$type_changed     = isset( $_POST['ywar_request_type'] ) && yith_ywar_get_option( 'ywar_request_type' ) !== sanitize_text_field( wp_unslash( $_POST['ywar_request_type'] ) );//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$number_changed   = isset( $_POST['ywar_request_number'] ) && yith_ywar_get_option( 'ywar_request_number' ) !== sanitize_text_field( wp_unslash( $_POST['ywar_request_number'] ) );//phpcs:ignore WordPress.Security.NonceVerification.Missing
				$criteria_changed = isset( $_POST['ywar_request_criteria'] ) && yith_ywar_get_option( 'ywar_request_criteria' ) !== sanitize_text_field( wp_unslash( $_POST['ywar_request_criteria'] ) );//phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( $days_changed || $type_changed || $number_changed || $criteria_changed ) {
					set_transient( 'yith-ywar-mass-reschedule', $actions );
				}
			}
		}

		/**
		 * Performms re-schedulation of the emails
		 *
		 * @return void
		 * @throws Exception The exception.
		 * @since  2.0.0
		 */
		public function perform_mass_reschedule() {
			$actions = get_transient( 'yith-ywar-mass-reschedule' );

			if ( is_array( $actions ) && false !== array_search( 'reschedule', $actions, true ) ) {

				$schedules = yith_ywar_list_schedules( 'pending' );

				foreach ( $schedules as $schedule ) {

					$list               = '';
					$new_scheduled_date = gmdate( 'Y-m-d', strtotime( $schedule->order_date . ' + ' . yith_ywar_get_option( 'ywar_mail_schedule_day' ) . ' days' ) );

					if ( 'order' === $schedule->mail_type ) {
						$order = wc_get_order( $schedule->object_id );
						$list  = maybe_serialize( yith_ywar_get_review_list( $order ) );
					}

					$rescheduled = yith_ywar_update_schedule( $schedule->id, $new_scheduled_date, 'pending', $list );

					if ( $rescheduled && false !== array_search( 'reschedule', $actions, true ) ) {

						$today     = new DateTime( current_time( 'mysql' ) );
						$send_date = new DateTime( $new_scheduled_date );
						if ( $send_date <= $today ) {
							$email        = yith_ywar_get_schedule_by_id( $schedule->id );
							$email_result = yith_ywar_send_email( $email );
							if ( $email_result ) {
								$today = new DateTime( current_time( 'mysql' ) );
								yith_ywar_update_schedule( $schedule->id, $today->format( 'Y-m-d' ), 'sent' );
								yith_ywar_update_sent_counter();
							}
						}
					}
				}

				delete_option( 'ywar_mail_reschedule' );
				delete_transient( 'yith-ywar-mass-reschedule' );

			}
		}

		/**
		 * Adjust product permalink
		 *
		 * @param string $permalink The product permalink.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function product_permalink( string $permalink ): string {

			$permalink .= '#reviews';

			if ( yith_ywar_get_option( 'ywar_enable_analytics' ) === 'yes' ) {
				$query_args = array(
					'utm_source'   => str_replace( ' ', '%20', yith_ywar_get_option( 'ywar_campaign_source' ) ),
					'utm_medium'   => str_replace( ' ', '%20', yith_ywar_get_option( 'ywar_campaign_medium' ) ),
					'utm_campaign' => str_replace( ' ', '%20', yith_ywar_get_option( 'ywar_campaign_name' ) ),
				);

				$campaign_term    = str_replace( ',', '+', yith_ywar_get_option( 'ywar_campaign_term', '' ) );
				$campaign_content = str_replace( ' ', '%20', yith_ywar_get_option( 'ywar_campaign_content', '' ) );

				if ( '' !== $campaign_term ) {
					$query_args['utm_term'] = $campaign_term;
				}

				if ( '' !== $campaign_content ) {
					$query_args['utm_content'] = $campaign_content;
				}

				$permalink = add_query_arg( $query_args, $permalink );

			}

			return $permalink;
		}
	}
}
