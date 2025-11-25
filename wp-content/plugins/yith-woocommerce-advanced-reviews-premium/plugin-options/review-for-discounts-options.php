<?php
/**
 * Review for discounts main tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'review-for-discounts' => array(
		'review-for-discounts-tabs' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'review-for-discounts-settings' => array(
					'title'        => esc_html_x( 'General options', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description'  => esc_html_x( 'Set the standard behavior of the "Review for discounts" module.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
					'options_path' => yith_ywar_get_module_path( 'review-for-discounts', 'options/review-for-discounts-settings.php' ),
				),
				'review-for-discounts-coupons'  => array(
					'title'        => esc_html_x( 'Coupons', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'description'  => esc_html_x( 'Create and manage coupons to reward users for their reviews.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
					'options_path' => yith_ywar_get_module_path( 'review-for-discounts', 'options/review-for-discounts-coupons.php' ),
				),
			),
		),
	),
);
