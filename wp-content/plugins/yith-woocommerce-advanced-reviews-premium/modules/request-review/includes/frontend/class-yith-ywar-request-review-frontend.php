<?php
/**
 * Class YITH_YWAR_Request_Review_Frontend
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Frontend
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Request_Review_Frontend' ) ) {
	/**
	 * Class YITH_YWAR_Request_Review_Frontend
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview\Frontend
	 */
	class YITH_YWAR_Request_Review_Frontend {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * Unsubscribe page IDs (comprehensive of other YITH plugins)
		 *
		 * @var array
		 */
		private $pages = array();

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->pages = yith_ywar_get_unsubscribe_page_ids();

			add_action( 'admin_init', array( $this, 'create_unsubscribe_page' ) );
			add_action( 'admin_notices', array( $this, 'protect_unsubscribe_page_notice' ) );
			add_action( 'wp_trash_post', array( $this, 'protect_unsubscribe_page' ) );
			add_action( 'before_delete_post', array( $this, 'protect_unsubscribe_page' ) );
			add_filter( 'wp_get_nav_menu_items', array( $this, 'hide_unsubscribe_page' ) );
			add_shortcode( 'yith_ywar_unsubscribe', array( $this, 'unsubscribe' ) );
			add_shortcode( 'ywrac_unsubscribe', array( $this, 'unsubscribe' ) );
			add_shortcode( 'ywrr_unsubscribe', array( $this, 'unsubscribe' ) );
			add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'show_request_option' ) );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_request_option' ) );
			add_action( 'woocommerce_edit_account_form', array( $this, 'show_request_option_my_account' ) );
			add_action( 'woocommerce_save_account_details', array( $this, 'save_request_option_my_account' ) );
		}

		/**
		 * Creates the unsubscribe page
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function create_unsubscribe_page() {

			foreach ( $this->pages as $page_id ) {
				$page_status = get_post_status( $page_id );
				if ( $page_status && 'trash' !== $page_status ) {
					return;
				}
			}

			$page_id = wp_insert_post(
				array(
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'post_author'    => 1,
					'post_name'      => esc_html_x( 'unsubscribe', '[Frontend] Unsubscribe page slug', 'yith-woocommerce-advanced-reviews' ),
					'post_title'     => esc_html_x( 'Unsubscribe', '[Frontend] Unsubscribe page', 'yith-woocommerce-advanced-reviews' ),
					'post_content'   => '<!-- wp:shortcode -->[yith_ywar_unsubscribe]<!-- /wp:shortcode -->',
					'post_parent'    => 0,
					'comment_status' => 'closed',
				)
			);

			update_option( 'yith-ywar-unsubscribe-page-id', $page_id );
		}

		/**
		 * Notifies the inability to delete the page
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function protect_unsubscribe_page_notice() {

			global $post_type, $pagenow;

			if ( 'edit.php' === $pagenow && 'page' === $post_type && isset( $_GET['impossible'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				printf( '<div id="message" class="error"><p>%s</p></div>', esc_html_x( 'The Unsubscribe page cannot be deleted', '[Admin panel] Message to trigger when someone tries to delete the unsubscribe page', 'yith-woocommerce-advanced-reviews' ) );
			}
		}

		/**
		 * Prevent the deletion of unsubscribe page
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function protect_unsubscribe_page( int $post_id ) {

			if ( in_array( $post_id, $this->pages, true ) ) {

				$query_args = array(
					'post_type'  => 'page',
					'impossible' => '1',
				);
				$error_url  = esc_url( add_query_arg( $query_args, admin_url( 'edit.php' ) ) );

				wp_safe_redirect( $error_url );
				exit();

			}
		}

		/**
		 * Hides unsubscribe page from menus
		 *
		 * @param array $items Pages array.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function hide_unsubscribe_page( array $items ): array {
			foreach ( $items as $key => $value ) {
				if ( in_array( $value->object_id, $this->pages, true ) ) {
					unset( $items[ $key ] );
				}
			}

			return $items;
		}

		/**
		 * Unsubscribe page shortcode.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function unsubscribe(): string {
			$email  = '';
			$path   = '';
			$prefix = '';
			$getted = $_GET; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$type   = isset( $getted['type'] ) ? $getted['type'] : false;

			if ( ! $type ) {
				$type = isset( $getted['action'] ) ? $getted['action'] : false;
			}

			ob_start();
			?>
			<div class="woocommerce yith-ywar-unsubscribe-form yith-ywar-edit-forms">
				<?php
				switch ( $type ) {
					case 'ywrr':
						// Old Review Reminder plugin unsubscribe page, kept for legacy purpose.
						$path   = function_exists( 'YITH_WRR' ) ? YWRR_TEMPLATE_PATH : false;
						$email  = isset( $getted['email'] ) ? $getted['email'] : false;
						$prefix = 'ywrr-';
						break;
					case 'ywrac':
					case '_ywrac_unsubscribe_from_mail':
						$path   = defined( 'YITH_YWRAC_PREMIUM' ) && YITH_YWRAC_PREMIUM ? YITH_YWRAC_TEMPLATE_PATH : false;
						$email  = isset( $getted['customer'] ) && is_email( $getted['customer'] ) ? $getted['customer'] : false;
						$prefix = 'ywrac-';
						break;
					case 'yith_ywar':
						$path  = yith_ywar_get_module_path( 'request-review', 'templates/' );
						$email = isset( $getted['email'] ) ? $getted['email'] : false;
						break;
				}

				if ( $path && $email ) {
					wc_get_template( $prefix . 'unsubscribe.php', array(), $path, $path );
				} else {
					?>
					<p class="return-to-shop"><a class="button wc-backward" href="<?php echo esc_url( get_home_url() ); ?>"><?php echo esc_html_x( 'Back to Home Page', '[Frontend] Unsubscribe page action button', 'yith-woocommerce-advanced-reviews' ); ?></a></p>
					<?php
				}
				?>
			</div>
			<?php

			return ob_get_clean();
		}

		/**
		 * Show email request checkbox in checkout page
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_request_option() {

			if ( 'yes' !== yith_ywar_get_option( 'ywar_refuse_requests' ) || apply_filters( 'yith_ywar_check_user_block_list_checkout', yith_ywar_check_blocklist( wp_get_current_user()->ID, '' ) ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_ywar_checkout_option_label
			 *
			 * Checkout label text.
			 *
			 * @param string $value The checkout label.
			 *
			 * @return string
			 */
			$label = apply_filters( 'yith_ywar_checkout_option_label', yith_ywar_get_option( 'ywar_refuse_requests_label' ) );

			if ( ! empty( $label ) ) {
				woocommerce_form_field(
					'ywar_receive_requests',
					array(
						'type'  => 'checkbox',
						'class' => array( 'form-row-wide' ),
						'label' => $label,
					),
					0
				);
			}
		}

		/**
		 * Save email request checkbox in checkout page
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function save_request_option() {
			if ( 'yes' !== yith_ywar_get_option( 'ywar_refuse_requests' ) ) {
				return;
			}

			//phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( empty( $_POST['ywar_receive_requests'] ) && isset( $_POST['billing_email'] ) && '' !== $_POST['billing_email'] ) {
				yith_ywar_add_to_blocklist( wp_get_current_user()->ID, sanitize_text_field( wp_unslash( $_POST['billing_email'] ) ) );
			}
			//phpcs:enable
		}

		/**
		 * Add customer request option to edit account page
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_request_option_my_account() {
			if ( 'yes' !== yith_ywar_get_option( 'ywar_refuse_requests' ) ) {
				return;
			}

			/**
			 * APPLY_FILTERS: yith_ywar_checkout_option_label
			 *
			 * Checkout label text.
			 *
			 * @param string $value The checkout label.
			 *
			 * @return string
			 */
			$label = apply_filters( 'yith_ywar_checkout_option_label', yith_ywar_get_option( 'ywar_refuse_requests_label' ) );

			?>
			<fieldset>
				<legend><?php echo esc_html_x( 'Review reminders', '[Frontend] Checkout option', 'yith-woocommerce-advanced-reviews' ); ?></legend>
				<label for="ywar_receive_requests">
					<input
							name="ywar_receive_requests"
							type="checkbox"
							class=""
							value="1"
						<?php checked( ! yith_ywar_check_blocklist( get_current_user_id(), '' ) ); ?>
					/> <?php echo esc_html( $label ); ?>
				</label>
			</fieldset>
			<?php
		}

		/**
		 * Save customer request option from edit account page
		 *
		 * @param integer $customer_id Customer ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function save_request_option_my_account( $customer_id ) {
			if ( 'yes' !== yith_ywar_get_option( 'ywar_refuse_requests' ) ) {
				return;
			}

			if ( isset( $_POST['ywar_receive_requests'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				yith_ywar_delete_from_blocklist_by_customer( $customer_id );
			} else {
				yith_ywar_add_to_blocklist( $customer_id, wp_get_current_user()->get( 'billing_email' ) );
			}
		}
	}

}

