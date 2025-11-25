<?php
/**
 * Dashboard options tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'dashboard' => array(
		'dashboard-tabs' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'dashboard-dashboard' => array(
					'title'       => esc_html_x( 'Dashboard', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description' => esc_html_x( 'A quick look at your store reviews.', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' ),
				),
				'dashboard-reviews'   => array(
					'title'       => esc_html_x( 'All reviews', '[Admin panel] Plugin options section name', 'yith-woocommerce-advanced-reviews' ),
					'description' => esc_html_x( 'Check and manage all reviews created in your shop.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
				),
			),
		),
	),
);
