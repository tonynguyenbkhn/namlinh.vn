<?php
/**
 * General options tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$part_one = esc_html_x( 'Create advanced review boxes and choose which products to display them in.', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' );
/* translators: %1$s: open link tag - %2$s: close link tag */
$part_two = sprintf( esc_html_x( 'Please note: review boxes shown in products have a higher priority by default. %1$sRead the documentation to better understand how review boxes work >%2$s', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' ), '<a target="_blank" href="https://docs.yithemes.com/yith-woocommerce-advanced-reviews/category/review-boxes/">', '</a>' );

return array(
	'review-boxes' => array(
		'review-boxes-tabs' => array(
			'type'       => 'multi_tab',
			'nav-layout' => 'horizontal',
			'sub-tabs'   => array(
				'review-boxes-boxes'    => array(
					'title'       => esc_html_x( 'Review boxes', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'description' => sprintf( '%1$s<br />%2$s', $part_one, $part_two ),
				),
				'review-boxes-criteria' => array(
					'title'       => esc_html_x( 'Review criteria', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description' => esc_html_x( 'Specify the criteria to be used in the reviews when the multi-criteria feature is enabled.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
				),
			),
		),
	),
);
