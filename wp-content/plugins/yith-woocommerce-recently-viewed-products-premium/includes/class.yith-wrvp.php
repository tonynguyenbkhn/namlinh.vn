<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Main class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WRVP' ) ) {
	/**
	 * YITH WooCommerce Recently Viewed Products
	 *
	 * @since 1.0.0
	 */
	class YITH_WRVP {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var YITH_WRVP
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 * @return YITH_WRVP
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
		 * @since 1.0.0
		 * @return void
		 */
		private function __construct() {

			// Load Plugin Framework.
			add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );

			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );
			
			// Register plugin to licence/update system.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			// Init plugin.
			add_action( 'init', array( $this, 'init' ) );

			// Helper common method.
			require_once 'class.yith-wrvp-helper.php';

			if ( $this->is_admin() ) {
				// Class Admin.
				include_once 'class.yith-wrvp-admin.php';
				include_once 'class.yith-wrvp-admin-premium.php';
				YITH_WRVP_Admin_Premium();
			}

			if ( $this->load_frontend() ) {
				// Class Frontend.
				include_once 'class.yith-wrvp-frontend.php';
				include_once 'class.yith-wrvp-frontend-premium.php';
				YITH_WRVP_Frontend_Premium();
			}

			// Mail handler.
			include_once 'class.yith-wrvp-mail-handler.php';
			YITH_WRVP_Mail_Handler();

			// Email actions.
			add_filter( 'woocommerce_email_classes', array( $this, 'add_woocommerce_emails' ) );
			add_action( 'woocommerce_init', array( $this, 'load_wc_mailer' ) );

			add_action( 'widgets_init', array( $this, 'register_widgets' ) );

			// GDPR actions.
			add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ) );
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ) );
		}

		/**
		 * Load Plugin Framework
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WRVP_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}

			YIT_Plugin_Licence()->register( YITH_WRVP_INIT, YITH_WRVP_SECRET_KEY, YITH_WRVP_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_WRVP_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}

			YIT_Upgrade()->register( YITH_WRVP_SLUG, YITH_WRVP_INIT );
		}

		/**
		 * Check if load admin classes
		 *
		 * @since 1.1.0
		 * @return boolean
		 */
		public function is_admin() {
			$check_ajax    = defined( 'DOING_AJAX' ) && DOING_AJAX;
			$check_context = isset( $_REQUEST['context'] ) && 'frontend' === sanitize_text_field( wp_unslash( $_REQUEST['context'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			/**
			 * APPLY_FILTERS: yith_wrvp_check_is_admin
			 *
			 * Filter whether the current request has been made for an admin page.
			 *
			 * @param bool $is_admin Whether the current request has been made for an admin page or not.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wrvp_check_is_admin', is_admin() && ! ( $check_ajax && $check_context ) );
		}

		/**
		 * Check if the frontend classes must be loaded
		 *
		 * @since 2.2.0
		 * @return boolean
		 */
		public function load_frontend() {
			global $pagenow;
			return ! $this->is_admin() || class_exists( 'ET_Builder_Plugin' ) || 'widgets.php' === $pagenow || $this->is_elementor_editor();
		}

		/**
		 * Check if current screen is elementor editor
		 *
		 * @since 2.6.0
		 * @return boolean
		 */
		public static function is_elementor_editor() {

			if ( did_action( 'admin_action_elementor' ) ) {
				return \Elementor\Plugin::$instance->editor->is_edit_mode();
			}

			return is_admin() && isset( $_REQUEST['action'] ) && in_array( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ), array( 'elementor', 'elementor_ajax' ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Filters woocommerce available mails, to add plugin related ones
		 *
		 * @since 1.0
		 * @param array $emails Array of WooCommerce emails.
		 * @return array
		 */
		public function add_woocommerce_emails( $emails ) {
			$emails['YITH_WRVP_Mail'] = include YITH_WRVP_DIR . '/includes/class.yith-wrvp-mail.php';

			return $emails;
		}

		/**
		 * Loads WC Mailer when needed
		 *
		 * @since 1.0
		 * @return void
		 */
		public function load_wc_mailer() {
			add_action( 'send_yith_wrvp_mail', array( 'WC_Emails', 'send_transactional_email' ), 10, 1 );
		}

		/**
		 * Load and register widgets
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function register_widgets() {
			register_widget( 'YITH_WRVP_Widget' );
		}

		/**
		 * Init plugin
		 *
		 * @since 2.0.0
		 */
		public function init() {
			// Add compare page.
			$this->add_page();
			// register size.
			$this->register_size();

			/**
			 * DO_ACTION: yith_wrvp_action_init_plugin
			 *
			 * Allows to trigger some action when initializing the plugin.
			 */
			do_action( 'yith_wrvp_action_init_plugin' );
		}

		/**
		 * Add a page "Recently Viewed".
		 *
		 * @since 1.0.0
		 * @return void
		 */
		protected function add_page() {
			global $wpdb;

			$option_value = get_option( 'yith-wrvp-page-id' );

			if ( $option_value && get_post( $option_value ) ) {
				return;
			}

			$page_found = $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'recently-viewed-products' LIMIT 1;" ); // phpcs:ignore
			if ( $page_found ) {
				if ( ! $option_value ) {
					update_option( 'yith-wrvp-page-id', $page_found );
				}

				return;
			}

			$page_data = array(
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => 1,
				'post_name'      => esc_sql( _x( 'recently-viewed-products', 'page_slug', 'yith-woocommerce-recently-viewed-products' ) ),
				'post_title'     => __( 'Recently Viewed Products', 'yith-woocommerce-recently-viewed-products' ),
				'post_content'   => '[yith_recenlty_viewed_page]',
				'post_parent'    => 0,
				'comment_status' => 'closed',
			);
			$page_id   = wp_insert_post( $page_data );

			update_option( 'yith-wrvp-page-id', $page_id );
		}

		/**
		 * Register new size image
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function register_size() {

			$size = get_option( 'yith-wrvp-image-size', '' );

			if ( ! $size ) {
				return;
			}

			$width  = isset( $size['width'] ) ? $size['width'] : 80;
			$height = isset( $size['height'] ) ? $size['height'] : 80;
			$crop   = isset( $size['crop'] ) ? $size['crop'] : false;

			add_image_size( 'ywrvp_image_size', $width, $height, $crop );
		}

		/**
		 * Register exporter for GDPR compliance
		 *
		 * @since 1.4.0
		 * @param array $exporters List of exporter callbacks.
		 * @return array
		 */
		public function register_exporters( $exporters = array() ) {
			$exporters['yith-wrvp-customer-data'] = array(
				'exporter_friendly_name' => __( 'YITH Recently Viewed Products', 'yith-woocommerce-recently-viewed-products' ),
				'callback'               => array( 'YITH_WRVP', 'customer_data_exporter' ),
			);

			return $exporters;
		}

		/**
		 * GDPR exporter callback
		 *
		 * @since 1.5.0
		 * @param string $email_address The user email address.
		 * @param int    $page Page.
		 * @return array
		 */
		public static function customer_data_exporter( $email_address, $page ) {
			$user           = get_user_by( 'email', $email_address );
			$data_to_export = array();
			$products_list  = array();
			// get products list if any.
			if ( $user instanceof WP_User ) {
				$products_list = get_user_meta( $user->ID, 'yith_wrvp_products_list', true );
			}

			if ( ! empty( $products_list ) ) {

				$products = array();
				foreach ( $products_list as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( $product ) {
						$products[] = $product->get_name();
					}
				}

				$data_to_export[] = array(
					'group_id'    => 'yith_wrvp_data',
					'group_label' => __( 'YITH Recently Viewed Products', 'yith-woocommerce-recently-viewed-products' ),
					'item_id'     => 'recently-viewed-products-list',
					'data'        => array(
						array(
							'name'  => __( 'Recently Viewed Products List', 'yith-woocommerce-recently-viewed-products' ),
							'value' => implode( ', ', $products ),
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
		 * Register ereaser for GDPR compliance
		 *
		 * @since 1.5.0
		 * @param array $erasers List of erasers callbacks.
		 * @return array
		 */
		public function register_erasers( $erasers = array() ) {
			$erasers['yith-wrvp-customer-data'] = array(
				'eraser_friendly_name' => __( 'YITH Recently Viewed Products', 'yith-woocommerce-recently-viewed-products' ),
				'callback'             => array( 'YITH_WRVP', 'customer_data_ereaser' ),
			);

			return $erasers;
		}

		/**
		 * GDPR ereaser callback
		 *
		 * @since 1.5.0
		 * @param string $user_email The user email.
		 * @param int    $page Page number.
		 * @return array
		 */
		public static function customer_data_ereaser( $user_email, $page ) {
			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			$user = get_user_by( 'email', $user_email ); // Check if user has an ID in the DB to load stored personal data.
			if ( ! $user instanceof WP_User ) {
				return $response;
			}

			delete_user_meta( $user->ID, 'yith_wrvp_products_list' );
			delete_user_meta( $user->ID, 'yith_wrvp_last_login' );
			$response['messages'][]    = __( 'Removed recently viewed products list for customer', 'yith-woocommerce-recently-viewed-products' );
			$response['items_removed'] = true;

			return $response;
		}

		/**
		 * Declare support for WooCommerce features.
		 */
		public function declare_wc_features_support() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_WRVP_INIT, true );
			}
		}
	}
}

/**
 * Unique access to instance of YITH_WRVP class
 *
 * @since 1.0.0
 * @return \YITH_WRVP
 */
function YITH_WRVP() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WRVP::get_instance();
}
