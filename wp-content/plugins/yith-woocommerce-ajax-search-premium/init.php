<?php
/**
 * Plugin Name: YITH WooCommerce Ajax Search Premium
 * Plugin URI: https://yithemes.com/themes/plugins/yith-woocommerce-ajax-search/
 * Description: <code><strong>YITH WooCommerce Ajax Search Premium</strong></code> is the plugin that allows you to search for a specific product by inserting a few characters. Thanks to <strong>Ajax Search</strong>, users can quickly find the contents they are interested in without wasting time among site pages. <a href="https://yithemes.com/" target="_blank">Get more plugins for your e-commerce shop on <strong>YITH</strong></a>.
 * Version: 2.2.0
 * Author: YITH
 * Author URI: https://yithemes.com/
 * Text Domain: yith-woocommerce-ajax-search
 * Domain Path: /languages/
 * WC requires at least: 8.5
 * WC tested up to: 8.7
 *
 * Init file
 *
 * @author YITH
 * @package YITH/Search
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( ! function_exists( 'yith_deactivate_plugins' ) ) {
	require_once 'plugin-fw/yit-deactive-plugin.php';
}
yith_deactivate_plugins( 'YITH_WCAS_FREE_INIT', plugin_basename( __FILE__ ) );

! defined( 'YITH_WCAS_PREMIUM' ) && define( 'YITH_WCAS_PREMIUM', '1' );
! defined( 'YITH_WCAS_DIR' ) && define( 'YITH_WCAS_DIR', plugin_dir_path( __FILE__ ) );
! defined( 'YITH_WCAS_VERSION' ) && define( 'YITH_WCAS_VERSION', '2.2.0' );
! defined( 'YITH_WCAS' ) && define( 'YITH_WCAS', 1 );
! defined( 'YITH_WCAS_FILE' ) && define( 'YITH_WCAS_FILE', __FILE__ );
! defined( 'YITH_WCAS_URL' ) && define( 'YITH_WCAS_URL', plugin_dir_url( __FILE__ ) );
! defined( 'YITH_WCAS_TEMPLATE_PATH' ) && define( 'YITH_WCAS_TEMPLATE_PATH', YITH_WCAS_DIR . 'templates/yith-wcas-search/' );
! defined( 'YITH_WCAS_ASSETS_URL' ) && define( 'YITH_WCAS_ASSETS_URL', YITH_WCAS_URL . 'assets' );
! defined( 'YITH_WCAS_BLOCK_PATH' ) && define( 'YITH_WCAS_BLOCK_PATH', YITH_WCAS_DIR . 'assets/js/blocks/src/blocks/' );
! defined( 'YITH_WCAS_BUILD_BLOCK_PATH' ) && define( 'YITH_WCAS_BUILD_BLOCK_PATH', YITH_WCAS_DIR . 'assets/js/blocks/build/' );
! defined( 'YITH_WCAS_ASSETS_IMAGES_URL' ) && define( 'YITH_WCAS_ASSETS_IMAGES_URL', YITH_WCAS_ASSETS_URL . '/images/' );
! defined( 'YITH_WCAS_INIT' ) && define( 'YITH_WCAS_INIT', plugin_basename( __FILE__ ) );
! defined( 'YITH_WCAS_INC' ) && define( 'YITH_WCAS_INC', YITH_WCAS_DIR . 'includes/' );
! defined( 'YITH_WCAS_SLUG' ) && define( 'YITH_WCAS_SLUG', 'yith-woocommerce-ajax-search' );
! defined( 'YITH_WCAS_SECRET_KEY' ) && define( 'YITH_WCAS_SECRET_KEY', 'SyKDKcXuRIOqRW6Aag5z' );

// Require plugin autoload.
if ( ! class_exists( 'YITH_WCAS_Autoloader' ) ) {
	require_once YITH_WCAS_INC . 'class-yith-wcas-autoloader.php';
}

if ( ! function_exists( 'yith_plugin_onboarding_registration_hook' ) ) {
	include_once 'plugin-upgrade/functions-yith-licence.php';
}
register_activation_hook( __FILE__, 'yith_plugin_onboarding_registration_hook' );

/* Plugin Framework Version Check */
if ( ! function_exists( 'yit_maybe_plugin_fw_loader' ) && file_exists( YITH_WCAS_DIR . 'plugin-fw/init.php' ) ) {
	require_once YITH_WCAS_DIR . 'plugin-fw/init.php';
}
yit_maybe_plugin_fw_loader( YITH_WCAS_DIR );


// Load plugin's functions.
require_once YITH_WCAS_DIR . '/includes/deprecated-functions-yith-wcas.php';
require_once YITH_WCAS_DIR . '/includes/functions-yith-wcas.php';
require_once YITH_WCAS_DIR . '/includes/functions-yith-wcas-premium.php';
require_once YITH_WCAS_DIR . '/includes/functions-yith-wcas-update.php';


if ( ! function_exists( 'yith_ajax_search_premium_install' ) ) {
	/**
	 * Check WC installation and initialize the installation if needed
	 */
	function yith_ajax_search_premium_install() {
		if ( ! function_exists( 'WC' ) ) {
			add_action( 'admin_notices', 'ywcas_premium_install_woocommerce_admin_notice' );
		} else {
			require_once YITH_WCAS_DIR . '/includes/class-yith-wcas-post-type.php';

			// Add support with HPOS system for WooCommerce 8.
			add_action(
				'before_woocommerce_init',
				function () {
					if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_WCAS_INIT );
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', YITH_WCAS_INIT );
					}
				}
			);

			global $yith_wcas;
			$yith_wcas = ywcas();
			ywcas()->get_class_name( 'YITH_WCAS_Install' )::init();
		}
	}
}
add_action( 'plugins_loaded', 'yith_ajax_search_premium_install', 11 );


if ( ! function_exists( 'ywcas' ) ) {
	/**
	 * Return the instance of the main class
	 *
	 * @return YITH_WCAS
	 */
	function ywcas() {
		return YITH_WCAS::get_instance();
	}
}
