<?php
/**
 * General options tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'general' => array(
		'general-tabs' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'general-general' => array(
					'title'       => esc_html_x( 'General options', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description' => esc_html_x( 'Set the general options related to how reviews work in your shop.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
				),
				'general-user'    => array(
					'title'       => esc_html_x( 'User permission', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description' => esc_html_x( 'Set user permissions for publishing reviews.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
				),
				'general-style'   => array(
					'title'       => esc_html_x( 'Style and customization', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description' => esc_html_x( 'Customize the display of review boxes on your site.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
				),
			),
		),
	),
);
