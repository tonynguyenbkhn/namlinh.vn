<?php

/**
 * The plugin bootstrap file
 *
 * @wordpress-plugin
 * Plugin Name:       Product Gallery Slider for WooCommerce PRO
 * Plugin URI:        https://codeixer.com/product-gallery-slider-for-woocommerce/
 * Description:       Fully customizable image gallery slider & additional variation images for the product page.comes with vertical and horizontal gallery layouts, clicking, sliding, image navigation, fancybox 3 & many more exciting features.
 * Version:           3.5.10
 * Author:            Codeixer
 * Author URI:        https://codeixer.com
 * Text Domain:       wpgs-td
 * Domain Path:       /languages
 * Tested up to: 6.7.0
 * WC requires at least: 4.0
 * WC tested up to: 9.4.2
 * Requires PHP: 7.2
 * Requires Plugin: WooCommerce
 * @package           twist
 *
 * @link              http://codeixer.com
 * @since             1.0.0
 */
update_option( 'Twist_lic_Key', '****************' );
update_option( 'Twist_lic_email', 'info@codexinh.com.com' );

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// Check if the free version is enabled, and if so, disable it
if ( in_array( 'woo-product-gallery-slider/woo-product-gallery-slider.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	deactivate_plugins( 'woo-product-gallery-slider/woo-product-gallery-slider.php' );
}



define( 'WPGS', 'TRUE' );
define( 'WPGS_VERSION', '3.5.10' );
define( 'WPGS_NAME', 'Product Gallery Slider for WooCommerce' );
define( 'WPGS_INC', plugin_dir_path( __FILE__ ) . 'inc/' );
define( 'WPGS_ROOT', plugin_dir_path( __FILE__ ) . '' );
define( 'WPGS_ROOT_URL', plugin_dir_url( __FILE__ ) . '' );
define( 'WPGS_INC_URL', plugin_dir_url( __FILE__ ) . 'inc/' );
define( 'WPGS_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'WPGS_PLUGIN_FILE', __FILE__ );
// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
if ( ! defined( 'CDX_STORE_URL' ) ) {
	define( 'CDX_STORE_URL', 'https://codeixer.com' );
}
require __DIR__ . '/vendor/autoload.php';

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);


class cix_wpgs {
	/**
	 * @var mixed
	 */
	private $divi_builder;
	/**
	 * The unique instance of the plugin.
	 */
	private static $instance;

		/**
		 * Gets an instance of our plugin.
		 *
		 * @return Class Instance.
		 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	private function __construct() {

		add_action( 'plugins_loaded', array( $this, 'core_files' ) );
		add_action( 'plugins_loaded', array( $this, 'after_woo_hooks' ) );
		add_action( 'after_setup_theme', array( $this, 'remove_woo_support' ), 20 );
		$this->divi_builder = ( self::option( 'check_divi_builder' ) == '1' ) ? 'true' : 'false';
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		// Switch for Divi Page builder conflict Issue
		if ( 'false' == $this->divi_builder ) {
			add_action( 'woocommerce_before_single_product_summary', array( $this, 'wpgs_templates' ) );
		}

		add_action( 'plugins_loaded', array( $this, 'load_plugin_code' ) );
		add_action( 'admin_notices', array( $this, 'add_wpgs_licese_admin_notice' ) );

		add_action( 'init', array( $this, 'pp_sample_plugin_updater' ) );
	}
	/**
	 * Initialize the updater. Hooked into `init` to work with the
	 * wp_version_check cron job, which allows auto-updates.
	 */
	function pp_sample_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'Twist_lic_Key' ) );

		// setup the updater
		$edd_updater = new Alledia\EDD_SL_Plugin_Updater(
			CDX_STORE_URL,
			__FILE__,
			array(
				'version' => WPGS_VERSION,       // current version number
				'license' => $license_key,    // license key (used get_option above to retrieve from DB)
				'item_id' => 32502,  // id of this plugin
				'author'  => 'Codeixer',   // author of this plugin
				'beta'    => false,
			)
		);
	}

	public function add_wpgs_licese_admin_notice() {
		if ( get_option( 'Twist_lic_Key' ) ) {
			return;
		}
		?>
		<div class="notice notice-error codeixer-notice">

			<p><?php _e( ' Would you like to receive automatic updates, awesome support? Please', 'twist' ); ?> <a href="<?php echo admin_url( 'admin.php?page=cix-gallery-settings#tab=license-managment' ); ?>"><?php _e( 'activate your copy', 'twist' ); ?></a> 
			<?php
			_e( 'of ', 'twist' );
				echo '<b>' . WPGS_NAME . ' PRO</b>';
			?>
			</p>

		</div>
		<?php
	}

	/**
	 * Run code on plugin activation
	 *
	 * @return void
	 */
	public function activation() {
		if ( ! get_option( 'twist_activation_time' ) ) {
			update_option( 'twist_activation_time', current_time( 'timestamp' ) );

		}
	}
	public function deactivation() {
		wp_clear_scheduled_hook( 'cix_plugin_list_cron' );
	}

	public function remove_woo_support() {
		remove_theme_support( 'wc-product-gallery-lightbox' );
		remove_theme_support( 'wc-product-gallery-slider' );
		remove_theme_support( 'wc-product-gallery-zoom' );
		if ( function_exists( 'woostify_version' ) ) {
			remove_action( 'woocommerce_before_single_product_summary', array( $this, 'wpgs_templates' ) );
		}
		add_filter( 'blocksy:woocommerce:product-view:use-default', '__return_true' );
		add_filter( 'astra_addon_override_single_product_layout', '__return_false' );
	}

	public function after_woo_hooks() {

		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 ); // Remove Default Image Gallery
	}

	public static function wpgs_templates() {

		// Override with 'wpgs_get_template' Filter for displays
		// Custom Page
		wc_get_template( 'single-product/product-image.php' );
	}

	public function core_files() {
		require WPGS_ROOT . 'core/codestar-framework/codestar-framework.php';
		require WPGS_ROOT . 'core/codeixer-core.php';
		require WPGS_INC . 'admin/class-variation-images.php';
		require WPGS_INC . 'admin/class-delete-cache.php';
		require WPGS_INC . 'admin/class-image-sizes.php';
		require WPGS_INC . 'admin/admin.php';
		require WPGS_INC . 'admin/options.php';
		require WPGS_INC . 'admin/elementor-twist.php';
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param  string $option  settings field name
	 * @param  string $default default text if it's not found
	 * @return mixed
	 */
	public static function option( $option, $default = '' ) {
		$options = get_option( 'wpgs_form' );

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}

	public function load_plugin_code() {

		require WPGS_INC . 'public/public.php';
	}
}

$cix_wpgs = cix_wpgs::init();
