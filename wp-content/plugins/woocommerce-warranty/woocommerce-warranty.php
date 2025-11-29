<?php
/**
 * Plugin Name: WooCommerce Warranty Requests
 * Plugin URI: https://woocommerce.com/products/warranty-requests/
 * Description: Set warranties for your products (free and paid), and allow customers to purchase warranties when buying a product, and to initiate a return request right from their account. Manage RMA numbers, return status, email communications, and track return shipping easily with this extension.
 * Version: 2.7.0
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * Text domain: woocommerce-warranty
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.7
 * Tested up to: 6.8
 * WC requires at least: 10.1
 * WC tested up to: 10.3
 *
 * Copyright: © 2025 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Woo: 228315:9b4c41102e6b61ea5f558e16f9b63e25
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOOCOMMERCE_WARRANTY_VERSION', '2.7.0' ); // WRCS: DEFINED_VERSION.
define( 'WOOCOMMERCE_WARRANTY_FILE', __FILE__ );
define( 'WOOCOMMERCE_WARRANTY_ABSPATH', trailingslashit( __DIR__ ) );
define( 'WOOCOMMERCE_WARRANTY_INCLUDES_PATH', WOOCOMMERCE_WARRANTY_ABSPATH . 'includes' );

// Plugin init hook.
add_action( 'plugins_loaded', 'wc_warranty_init' );

/**
 * Initialize plugin.
 */
function wc_warranty_init() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wc_warranty_woocommerce_deactivated' );

		return;
	}

	require_once WOOCOMMERCE_WARRANTY_INCLUDES_PATH . '/class-woocommerce-warranty.php';

	$GLOBALS['wc_warranty'] = new WooCommerce_Warranty();
}

/**
 * WooCommerce Deactivated Notice.
 */
function wc_warranty_woocommerce_deactivated() {
	/* translators: %s: WooCommerce link */
	echo '<div class="error"><p>' . sprintf( esc_html__( 'WooCommerce Warranty Requests requires %s to be installed and active.', 'woocommerce-warranty' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
}
