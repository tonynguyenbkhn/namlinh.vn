<?php
/**
 * Review box tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$button = '<a href="#" class="yith-ywar-add-box yith-plugin-fw__button--primary">' . esc_html_x( 'Add new', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ) . '</a>';

return array(
	'review-boxes-boxes' => array(
		'review-boxes-boxes-list' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywar_print_review_box_tab',
			'title'  => esc_html_x( 'Review boxes', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ) . $button,
		),
	),
);
