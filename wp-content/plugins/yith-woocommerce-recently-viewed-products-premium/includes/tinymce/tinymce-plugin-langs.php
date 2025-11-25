<?php
/**
 * TinyMCE langs
 *
 * @package YITH\RecentlyViewedProducts\Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '_WP_Editors' ) ) {
	require ABSPATH . WPINC . '/class-wp-editor.php';
}
/**
 * Return tinyMCE plugin translation
 */
function ywrvp_tinymce_plugin_translation() {
	$strings = array(
		'products'        => __( 'Customer product list', 'yith-woocommerce-recently-viewed-products' ),
		'custom_products' => __( 'Custom product list', 'yith-woocommerce-recently-viewed-products' ),
		'coupon_code'     => __( 'Coupon code', 'yith-woocommerce-recently-viewed-products' ),
		'coupon_expire'   => __( 'Coupon expire', 'yith-woocommerce-recently-viewed-products' ),
	);

	$locale     = _WP_Editors::$mce_locale;
	$translated = 'tinyMCE.addI18n("' . $locale . '.tc_button", ' . wp_json_encode( $strings ) . ");\n";

	return $translated;
}

$strings = ywrvp_tinymce_plugin_translation();
