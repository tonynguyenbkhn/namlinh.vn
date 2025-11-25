<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Mail Handler class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WRVP_Mail_Handler' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WRVP_Mail_Handler {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WRVP_Mail_Handler
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * User meta exclude from mailing list
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $user_meta_exclude = 'yith_wrvp_exclude_mail';

		/**
		 * User meta failed attempts email
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $failed_attempts = 'yith_wrvp_failed_emails';

		/**
		 * User meta mail sent
		 *
		 * @var string
		 * @since 1.0.0
		 */
		protected $mail_sent = 'yith_wrvp_mail_sent';

		/**
		 * Send test email action
		 *
		 * @var string
		 * @since 2.0.0
		 */
		protected $test_email_action = 'yith_wrvp_send_test_email';

		/**
		 * Validate a coupon code
		 *
		 * @var string
		 * @since 2.0.0
		 */
		protected $validate_coupon_action = 'yith_wrvp_validate_coupon';


		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WRVP_Mail_Handler
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

			// add actions and nonce for AJAX request.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 15 );

			// unsubscribe user from mailing list.
			add_action( 'init', array( $this, 'unsubscribe_from_mailing_list' ), 1 );

			// save user last visit time.
			add_action( 'init', array( $this, 'update_user_metas' ), 1 );

			add_action( 'init', array( $this, 'mail_setup_schedule' ), 20 );
			add_action( 'yith_wrvp_mail_action_schedule', array( $this, 'mail_do_action_schedule' ) );

			// Email Templates custom Styles.
			add_action( 'yith_wcet_after_email_styles', array( $this, 'email_templates_custom_css' ), 10, 3 );

			add_action( 'yith_wrvp_mail_sent_correctly', array( $this, 'set_meta_mail_sent' ), 10 );
			add_action( 'yith_wrvp_mail_sent_error', array( $this, 'set_meta_mail_error' ), 10 );

			// AJAX action.
			add_action( 'wp_ajax_' . $this->test_email_action, array( $this, 'send_test_mail' ), 10 );
			add_action( 'wp_ajax_' . $this->validate_coupon_action, array( $this, 'validate_coupon' ) );
		}

		/**
		 * Add localize data to admin script
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_localize_script(
				'yith-wrvp-admin',
				'ywrvp',
				array(
					'testEmailAction'      => $this->test_email_action,
					'testEmailNonce'       => wp_create_nonce( $this->test_email_action ),
					'validateCouponAction' => $this->validate_coupon_action,
					'validateCouponNonce'  => wp_create_nonce( $this->validate_coupon_action ),
				)
			);
		}

		/**
		 * Add custom styles for Email Templates
		 *
		 * @param int      $premium_style Email styles.
		 * @param array    $meta Array of metas.
		 * @param WC_Email $current_email Current email.
		 */
		public function email_templates_custom_css( $premium_style, $meta, $current_email ) {
			if ( empty( $current_email ) || 'yith_wrvp_mail' !== $current_email->id ) {
				return;
			}

			$args = array( 'hide_style_for_email_templates' => true );
			wc_get_template( 'ywrvp-mail-style.php', $args, '', YITH_WRVP_TEMPLATE_PATH . '/email/' );
		}

		/**
		 * Update user metas based on last active timestamp
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function update_user_metas() {
			$user_id    = get_current_user_id();
			$now        = time();
			$last_login = get_user_meta( $user_id, 'wc_last_active', true );

			if ( ! $user_id || ( intval( $last_login ) + DAY_IN_SECONDS ) > $now ) {
				return;
			}

			version_compare( '3.4.0', WC()->version ) === 1 && update_user_meta( $user_id, 'wc_last_active', $now ); // backward compatibility with version pre 3.4.0.
			delete_user_meta( $user_id, $this->failed_attempts );
			// reset mail sent.
			delete_user_meta( $user_id, $this->mail_sent );
		}

		/**
		 * Set user meta mail sent
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string|WP_User $customer Customer email address or WP_User object.
		 */
		public function set_meta_mail_sent( $customer ) {
			// get customer if needed.
			if ( ! $customer instanceof WP_User ) {
				$customer = get_user_by( 'email', $customer );
			}

			if ( ! $customer ) {
				return;
			}

			update_user_meta( $customer->ID, $this->mail_sent, true );
			delete_user_meta( $customer->ID, $this->failed_attempts );
		}

		/**
		 * Set user meta failed mail attempts, if greater then 3 set mail sent and delete failed attempts
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string|WP_User $customer Customer email address or WP_User object.
		 */
		public function set_meta_mail_error( $customer ) {
			// get customer if needed.
			if ( ! $customer instanceof WP_User ) {
				$customer = get_user_by( 'email', $customer );
			}

			if ( ! $customer ) {
				return;
			}

			$c = get_user_meta( $customer->ID, $this->failed_attempts, true );
			if ( intval( $c ) < 3 ) {
				update_user_meta( $customer->ID, $this->failed_attempts, ++$c );
			} else {
				$this->set_meta_mail_sent( $customer );
			}
		}

		/**
		 * Schedule event to send mail to users
		 *
		 * @return void
		 */
		public function mail_setup_schedule() {
			if ( ! wp_next_scheduled( 'yith_wrvp_mail_action_schedule' ) ) {
				wp_unschedule_hook( 'mail_action_schedule' );
				wp_schedule_event( time(), 'hourly', 'yith_wrvp_mail_action_schedule' );
			}
		}

		/**
		 * Action send mail to users
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function mail_do_action_schedule() {

			global $wpdb;

			$data = array();

			// first get users and products list.
			$query   = $this->build_query();
			$results = $wpdb->get_results( implode( ' ', $query ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			if ( empty( $results ) ) {
				return;
			}

			foreach ( $results as $result ) {
				/**
				 * APPLY_FILTERS: yith_wrvp_customer_skip_mail
				 *
				 * Filters whether to skip sending the email.
				 *
				 * @param bool $skip_mail Whether to skip sending the email or not.
				 * @param int  $user_id   User ID.
				 *
				 * @return bool
				 */
				if ( user_can( $result->ID, 'administrator' ) || apply_filters( 'yith_wrvp_customer_skip_mail', false, $result->ID ) ) {
					update_user_meta( $result->ID, $this->mail_sent, true ); // skip this email and set it as sent.
					continue;
				}

				$data[ $result->user_email ] = maybe_unserialize( $result->meta_value );
			}

			/**
			 * DO_ACTION: send_yith_wrvp_mail
			 *
			 * Allows to fire some action when the email is sent.
			 *
			 * @param array $data Email data.
			 */
			do_action( 'send_yith_wrvp_mail', $data );
		}

		/**
		 * Get users mail and products list from DB
		 *
		 * @access protected
		 * @since 1.0.0
		 */
		protected function build_query() {

			global $wpdb;

			// period.
			$period = absint( get_option( 'yith-wrvp-email-period', 7 ) );

			/**
			 * APPLY_FILTERS: yith_wrvp_email_period_in_seconds
			 *
			 * Filters the time interval to send the email.
			 *
			 * @param int $interval Interval to send the email.
			 *
			 * @return int
			 */
			$period_in_seconds = apply_filters( 'yith_wrvp_email_period_in_seconds', $period * DAY_IN_SECONDS );
			$time              = time() - $period_in_seconds;

			$query           = array();
			$query['fields'] = 'SELECT a.ID, a.user_email, b.meta_value';
			$query['from']   = "FROM {$wpdb->users} AS a";
			$query['join']   = "INNER JOIN {$wpdb->usermeta} AS b ON ( a.ID = b.user_id )";
			$query['join']  .= "INNER JOIN {$wpdb->usermeta} AS c ON ( c.user_id = b.user_id AND c.meta_key = 'wc_last_active' )";
			$query['where']  = "WHERE a.ID NOT IN ( SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$this->mail_sent}' OR meta_key = '{$this->user_meta_exclude}')";
			$query['where'] .= "AND b.meta_key = 'yith_wrvp_products_list'";
			$query['where'] .= "AND b.meta_value NOT LIKE 'a:0:{}'";
			$query['where'] .= "AND c.meta_value > 0 AND c.meta_value < {$time}";
			$query['limit']  = 'LIMIT 20';

			$query['group'] = '';

			return $query;
		}

		/**
		 * Send test mail action
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function send_test_mail() {

			check_ajax_referer( $this->test_email_action, 'security' );

			$email = ! empty( $_POST['yith-wrvp-test-mail'] ) ? sanitize_text_field( wp_unslash( $_POST['yith-wrvp-test-mail'] ) ) : '';
			if ( ! $email || ! is_email( $email ) ) {
				wp_send_json_error();
			}

			// Collect temp options.
			$temp_options = ! empty( $_POST['woocommerce_yith_wrvp_mail_settings'] ) ? wp_unslash( $_POST['woocommerce_yith_wrvp_mail_settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$mailer = WC()->mailer();
			$class  = $mailer->emails['YITH_WRVP_Mail'];

			$class->set_temp_options( $temp_options ); // Set temp options to use in test email.
			if ( $class->trigger( array( $email => array() ) ) ) {
				wp_send_json_success();
			}

			wp_send_json_error();
		}

		/**
		 * Create coupon for the email
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string $user User email address.
		 * @param array  $products Products.
		 * @param string $expire Expire coupon date.
		 * @param string $value Coupon value.
		 * @return string
		 */
		public function add_coupon_to_mail( $user, $products, $expire, $value ) {

			if ( ! $value || ! $expire ) {
				return '';
			}

			// make sure expire and value is number positive.
			$value  = abs( $value );
			$expire = abs( $expire );

			/**
			 * APPLY_FILTERS: yith_wrvp_prefix_coupon
			 *
			 * Filters the prefix for the coupon code.
			 *
			 * @param string $prefix Prefix.
			 *
			 * @return string
			 */
			$prefix      = apply_filters( 'yith_wrvp_prefix_coupon', 'ywrvp_' );
			$coupon_code = uniqid( $prefix ); // Code.

			if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
				$coupon_data   = array(
					'code'                   => $coupon_code,
					'amount'                 => $value,
					'date_expires'           => gmdate( 'Y-m-d', $expire ),
					'discount_type'          => 'percent',
					/**
					 * APPLY_FILTERS: yith_wrvp_coupon_individual_use
					 *
					 * Filters whether to allow individual uses when using the coupon.
					 *
					 * @param bool $allow_individual_use Whether to allow individual uses when using the coupon or not.
					 *
					 * @return bool
					 */
					'individual_use'         => apply_filters( 'yith_wrvp_coupon_individual_use', true ),
					'product_ids'            => implode( ',', $products ),
					'usage_limit'            => 1,
					'limit_usage_to_x_items' => 1,
				);
				$coupon_object = new WC_Coupon( $coupon_code );
				$coupon_object->read_manual_coupon( $coupon_code, $coupon_data );
				$coupon_object->save();
			} else {

				$new_coupon_id = wp_insert_post(
					array(
						'post_title'   => $coupon_code,
						'post_content' => '',
						'post_status'  => 'publish',
						'post_author'  => 1,
						'post_type'    => 'shop_coupon',
					)
				);

				update_post_meta( $new_coupon_id, 'discount_type', 'percent_product' );
				update_post_meta( $new_coupon_id, 'coupon_amount', $value );
				update_post_meta( $new_coupon_id, 'individual_use', 'yes' );
				update_post_meta( $new_coupon_id, 'product_ids', implode( ',', $products ) );
				update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
				update_post_meta( $new_coupon_id, 'usage_limit', '1' );
				update_post_meta( $new_coupon_id, 'usage_limit_per_user', '0' );
				update_post_meta( $new_coupon_id, 'limit_usage_to_x_items', '1' );
				update_post_meta( $new_coupon_id, 'expiry_date', gmdate( 'Y-m-d', $expire ) );
				update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
			}

			return $coupon_code;

		}

		/**
		 * Unsubscribe user from mailing list
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function unsubscribe_from_mailing_list() {
			if ( ! isset( $_GET['action'] ) || 'yith_wrvp_unsubscribe_from_list' !== $_GET['action'] || ! isset( $_GET['customer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			$user_id = $this->find_user_md5( sanitize_text_field( wp_unslash( $_GET['customer'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( is_null( $user_id ) ) {
				return;
			}

			update_user_meta( $user_id, $this->user_meta_exclude, true );

			wc_add_notice( __( 'You have been unsubscribed from our mailing list successfully.', 'yith-woocommerce-recently-viewed-products' ), 'success' );
			$url = is_user_logged_in() ? wc_get_page_permalink( 'myaccount' ) : wc_get_page_permalink( 'shop' );

			wp_safe_redirect( $url );
			exit();
		}

		/**
		 * Find user by md5 id
		 *
		 * @since 1.0.0
		 * @param string $md5_id Customer MD5 ID.
		 */
		public function find_user_md5( $md5_id ) {

			global $wpdb;

			$query           = array();
			$query['fields'] = "SELECT a.ID FROM {$wpdb->users} a";
			$query['where']  = " WHERE MD5(a.ID) = '$md5_id'";

			/**
			 * APPLY_FILTERS: yith_wrvp_query_md5user_param
			 *
			 * Filters the query params to find user by md5.
			 *
			 * @param array $query Query params.
			 *
			 * @return array
			 */
			$query = apply_filters( 'yith_wrvp_query_md5user_param', $query );

			$results = $wpdb->get_var( implode( ' ', $query ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			return $results;
		}

		/**
		 * Get products list html
		 *
		 * @access public
		 * @since 1.2.0
		 * @param array       $products Array of products.
		 * @param bool        $is_custom Is custom list.
		 * @param string|bool $cat_id Category ID.
		 * @param WC_Email    $email Email object.
		 * @return mixed
		 */
		public function get_products_list_html( $products, $is_custom, $cat_id, $email ) {
			/**
			 * APPLY_FILTERS: yith_wrvp_similar_products_template_args
			 *
			 * Filters the array with the arguments needed for the products list template.
			 *
			 * @param array $args Array of arguments.
			 *
			 * @return array
			 */
			$args = apply_filters(
				'yith_wrvp_similar_products_template_args',
				array(
					'post_type'           => 'product',
					'ignore_sticky_posts' => 1,
					'post_status'         => 'publish',
					'no_found_rows'       => 1,
					'posts_per_page'      => $email->get_option( 'number_products', '5' ),
					'order'               => 'DESC',
				)
			);

			if ( ! empty( $products ) ) {
				$args['post__in'] = $products;
			}

			if ( $cat_id ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => $cat_id,
					),
				);
			}

			// hide free.
			if ( 'yes' === get_option( 'yith-wrvp-hide-free' ) ) {
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => '_price',
						'value'   => 0,
						'compare' => '>',
						'type'    => 'DECIMAL',
					),
				);
			}

			if ( 'yes' === get_option( 'yith-wrvp-hide-out-of-stock' ) ) {
				$args['meta_query'][] = array(
					array(
						'key'     => '_stock_status',
						'value'   => 'instock',
						'compare' => '=',
					),
				);
			}

			$order = $email->get_option( 'products_order', 'rand' );

			switch ( $order ) {
				case 'sales':
					$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$args['orderby']  = 'meta_value_num';
					break;
				case 'newest':
					$args['orderby'] = 'date';
					break;
				case 'high-low':
					$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$args['orderby']  = 'meta_value_num';
					break;
				case 'low-high':
					$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'ASC';
					break;
				default:
					$args['orderby'] = 'rand';
					break;
			}

			// visibility query condition.
			$args = yit_product_visibility_meta( $args );

			$products = new WP_Query( $args );

			$template_name = $is_custom ? 'ywrvp-mail-custom-products-list.php' : 'ywrvp-mail-products-list.php';

			ob_start();

			if ( $products->have_posts() ) {
				wc_get_template( $template_name, array( 'products' => $products ), '', YITH_WRVP_TEMPLATE_PATH . '/email/' );
			}

			return ob_get_clean();
		}

		/**
		 * Get unsubscribe from mailing list link
		 *
		 * @access public
		 * @since 1.2.0
		 * @param string $customer_mail Customer email.
		 * @param string $label Unsubscribe label.
		 * @param bool   $is_test Is a test email?.
		 * @return string
		 */
		public function get_unsubscribe_link( $customer_mail, $label = '', $is_test = false ) {

			$customer = get_user_by( 'email', $customer_mail );
			// if customer not exists return empty string.
			if ( ! $customer || $is_test ) {
				return '<a href="#">' . $label . '</a>';
			}

			$id = md5( $customer->ID );

			/**
			 * APPLY_FILTERS: yith_wrvp_unsubscribe_link_url
			 *
			 * Filters the URL to unsubscribe from the emails.
			 *
			 * @param string $unsubscribe_url URL to unsubscribe from emails.
			 *
			 * @return string
			 */
			$url = apply_filters( 'yith_wrvp_unsubscribe_link_url', home_url() );

			$url = esc_url_raw(
				add_query_arg(
					array(
						'action'   => 'yith_wrvp_unsubscribe_from_list',
						'customer' => $id,
					),
					$url
				)
			);

			return '<a href="' . $url . '">' . $label . '</a>';
		}

		/**
		 * Validate coupon in ajax
		 *
		 * @since 1.4.2
		 * @return void
		 */
		public function validate_coupon() {

			check_ajax_referer( $this->validate_coupon_action, 'security' );

			if ( empty( $_REQUEST['code'] ) || ! class_exists( 'WC_Coupon' ) ) {
				wp_send_json(
					array(
						'valid' => false,
					)
				);
			}

			$code   = wc_format_coupon_code( sanitize_text_field( wp_unslash( $_REQUEST['code'] ) ) );
			$id     = wc_get_coupon_id_by_code( $code );
			$coupon = new WC_Coupon( $code );
			$expire = $coupon->get_date_expires();

			wp_send_json(
				array(
					'valid' => $id && ( is_null( $expire ) || $expire->getTimestamp() > time() ),
				)
			);
		}
	}
}
/**
 * Unique access to instance of YITH_WRVP_Mail_Handler class
 *
 * @return YITH_WRVP_Mail_Handler
 * @since 1.0.0
 */
function YITH_WRVP_Mail_Handler() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WRVP_Mail_Handler::get_instance();
}
