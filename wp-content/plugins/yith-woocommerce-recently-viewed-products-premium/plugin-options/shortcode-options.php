<?php
/**
 * Shortcode tab options
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\PluginOptions
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WRVP' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'shortcode' => array(
		'shortcode-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'shortcode-similar-products'     => array(
					'title' => _x( 'Recently Viewed Products', 'Shortcode section title', 'yith-woocommerce-recently-viewed-products' ),
				),
				'shortcode-most-viewed-products' => array(
					'title' => _x( 'Most Viewed Products', 'Shortcode section title', 'yith-woocommerce-recently-viewed-products' ),
				),
			),
		),
	),
);
