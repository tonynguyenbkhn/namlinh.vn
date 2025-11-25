<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Admin class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WRVP_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WRVP_Admin {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WRVP_Admin
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin options
		 *
		 * @var array
		 * @access public
		 * @since 1.0.0
		 */
		public $options = array();

		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0.0
		 */
		public $version = YITH_WRVP_VERSION;

		/**
		 * Panel Object
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $panel;

		/**
		 * Premium tab template file name
		 *
		 * @var string
		 */
		protected $premium = 'premium.php';

		/**
		 * Premium version landing link
		 *
		 * @var string
		 */
		protected $premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-recently-viewed-products/';

		/**
		 * Panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wrvp_panel';

		/**
		 * Various links
		 *
		 * @var string
		 * @access public
		 * @since 1.0.0
		 */
		public $doc_url = 'https://docs.yithemes.com/yith-woocommerce-recently-viewed-products/';

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WRVP_Admin
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

			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			// Add action links.
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WRVP_DIR . '/' . basename( YITH_WRVP_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			add_action( 'yith_wrvp_premium', array( $this, 'premium_tab' ) );
			add_action( 'after_setup_theme', array( $this, 'load_privacy_dpa' ), 10 );
		}

		/**
		 * Action Links
		 *
		 * Add the action links to plugin admin page
		 *
		 * @param array $links | links plugin array.
		 *
		 * @since    1.0
		 * @return array
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->panel_page, true, YITH_WRVP_SLUG );
			return $links;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs = array(
				'general' => __( 'Settings', 'yith-woocommerce-recently-viewed-products' ),
			);

			if ( ! ( defined( 'YITH_WRVP_PREMIUM' ) && YITH_WRVP_PREMIUM ) ) {
				$admin_tabs['premium'] = __( 'Premium Version', 'yith-woocommerce-recently-viewed-products' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => 'YITH WooCommerce Recently Viewed Products',
				'menu_title'       => 'Recently Viewed Products',
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'plugin_slug'      => YITH_WRVP_SLUG,
				'page'             => $this->panel_page,
				/**
				 * APPLY_FILTERS: yith_wrvp_admin_tabs
				 *
				 * Filters the available tabs in the plugin panel.
				 *
				 * @param array $admin_tabs Admin tabs.
				 *
				 * @return array
				 */
				'admin-tabs'       => apply_filters( 'yith_wrvp_admin_tabs', $admin_tabs ),
				'options-path'     => YITH_WRVP_DIR . '/plugin-options',
				'class'            => yith_set_wrapper_class(),
				'is_free'          => ! defined( 'YITH_WRVP_PREMIUM' ),
				'is_premium'       => defined( 'YITH_WRVP_PREMIUM' ),
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_WRVP_DIR . '/plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );

		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @since    1.0
		 * @return void
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_WRVP_TEMPLATE_PATH . '/admin/' . $this->premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}

		}

		/**
		 * Add the action links to plugin admin page
		 *
		 * @param array  $new_row_meta_args Array of plugin row meta.
		 * @param array  $plugin_meta Array of plugin meta data.
		 * @param string $plugin_file Plugin path file.
		 * @param array  $plugin_data Array of plugin data.
		 * @param string $status Plugin status.
		 *
		 * @return   Array
		 * @since    1.0
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status ) {
			if ( defined( 'YITH_WRVP_INIT' ) && YITH_WRVP_INIT === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_WRVP_SLUG;

				if ( defined( 'YITH_WRVP_PREMIUM' ) ) {
					$new_row_meta_args['is_premium'] = true;
				}
			}
			return $new_row_meta_args;
		}

		/**
		 * Get the premium landing uri
		 *
		 * @since   1.0.0
		 * @return  string The premium landing link
		 */
		public function get_premium_landing_uri() {
			return defined( 'YITH_REFER_ID' ) ? $this->premium_landing . '?refer_id=' . YITH_REFER_ID : $this->premium_landing . '?refer_id=1030585';
		}

		/**
		 * Load privacy DPA class
		 *
		 * @since 1.5.1
		 */
		public function load_privacy_dpa() {
			if ( class_exists( 'YITH_Privacy_Plugin_Abstract' ) ) {
				include_once 'class.yith-wrvp-privacy-dpa.php';
			}
		}
	}
}
/**
 * Unique access to instance of YITH_WRVP_Admin class
 *
 * @return \YITH_WRVP_Admin
 * @since 1.0.0
 */
function YITH_WRVP_Admin() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WRVP_Admin::get_instance();
}
