<?php
/**
 * Plugin Name: YITH WooCommerce Advanced Reviews Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-advanced-reviews/
 * Description: <code><strong>YITH WooCommerce Advanced Reviews</strong></code>extends the basic functionality of WooCommerce reviews and adds a histogram table to your product's reviews, as seen in most popular e-commerce sites. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce on <strong>YITH</strong></a>.
 * Version: 2.8.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-advanced-reviews
 * Domain Path: /languages/
 * WC requires at least: 9.6.0
 * WC tested up to: 9.8.x
 * Requires Plugins: woocommerce
 * Requires at least: 6.6.0
 * Tested up to: 6.8.x
 *
 * @package YITH\AdvancedReviews
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'yith_plugin_registration_hook' ) ) {
	require_once 'plugin-fw/yit-plugin-registration-hook.php';
}
register_activation_hook( __FILE__, 'yith_plugin_registration_hook' );

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

! defined( 'YITH_YWAR_VERSION' ) && define( 'YITH_YWAR_VERSION', '2.8.0' );
! defined( 'YITH_YWAR_PREMIUM' ) && define( 'YITH_YWAR_PREMIUM', '1' );
! defined( 'YITH_YWAR' ) && define( 'YITH_YWAR', true );
! defined( 'YITH_YWAR_FILE' ) && define( 'YITH_YWAR_FILE', __FILE__ );
! defined( 'YITH_YWAR_URL' ) && define( 'YITH_YWAR_URL', plugins_url( '/', __FILE__ ) );
! defined( 'YITH_YWAR_DIR' ) && define( 'YITH_YWAR_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_YWAR_INCLUDES_DIR' ) && define( 'YITH_YWAR_INCLUDES_DIR', YITH_YWAR_DIR . 'includes/' );
! defined( 'YITH_YWAR_TEMPLATES_DIR' ) && define( 'YITH_YWAR_TEMPLATES_DIR', YITH_YWAR_DIR . 'templates/' );
! defined( 'YITH_YWAR_VIEWS_PATH' ) && define( 'YITH_YWAR_VIEWS_PATH', YITH_YWAR_DIR . 'views/' );
! defined( 'YITH_YWAR_ASSETS_URL' ) && define( 'YITH_YWAR_ASSETS_URL', YITH_YWAR_URL . 'assets' );
! defined( 'YITH_YWAR_MODULES_PATH' ) && define( 'YITH_YWAR_MODULES_PATH', YITH_YWAR_DIR . 'modules/' );
! defined( 'YITH_YWAR_MODULES_URL' ) && define( 'YITH_YWAR_MODULES_URL', YITH_YWAR_URL . 'modules/' );
! defined( 'YITH_YWAR_INIT' ) && define( 'YITH_YWAR_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_YWAR_SLUG' ) && define( 'YITH_YWAR_SLUG', 'yith-woocommerce-advanced-reviews' );
! defined( 'YITH_YWAR_PLUGIN_NAME' ) && define( 'YITH_YWAR_PLUGIN_NAME', 'YITH WooCommerce Advanced Reviews' );
! defined( 'YITH_YWAR_SECRET_KEY' ) && define( 'YITH_YWAR_SECRET_KEY', '' );

// Plugin Framework Loader.
if ( file_exists( plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'plugin-fw/init.php';
}

add_action( 'plugins_loaded', 'yith_ywar_install', 11 );
add_action( 'yith_ywar_init', 'yith_ywar_init' );

/**
 * Plugin install process
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_install() {
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'yith_ywar_install_woocommerce_admin_notice' );
	} else {
		do_action( 'yith_ywar_init' );
	}
}

/**
 * Install error admin notice
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_install_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p>
			<?php
			/* translators: %s name of the plugin */
			printf( esc_html_x( '%s is enabled but not effective. It requires WooCommerce in order to work.', '[Admin panel] Message displayed if WooCommerce is not enabled', 'yith-woocommerce-advanced-reviews' ), esc_html( YITH_YWAR_PLUGIN_NAME ) );
			?>
		</p>
	</div>
	<?php
}

/**
 * Init plugin
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_init() {
    /**
     * Load text domain
     */
    if ( function_exists( 'yith_plugin_fw_load_plugin_textdomain' ) ) {
        yith_plugin_fw_load_plugin_textdomain( 'yith-woocommerce-advanced-reviews', basename( dirname( __FILE__ ) ) . '/languages' );
    }
	// Load required classes and functions.
	require_once YITH_YWAR_INCLUDES_DIR . 'class-yith-ywar-autoloader.php';

	YITH_YWAR_Post_Types::init();

	// Let's start the game!
	YITH_YWAR();

	do_action( 'yith_ywar_loaded' );
}

/**
 * Unique access to instance of YITH_YWAR
 *
 * @return  YITH_YWAR
 * @since   2.0.0
 */
function YITH_YWAR() { //phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_YWAR::get_instance();
}
