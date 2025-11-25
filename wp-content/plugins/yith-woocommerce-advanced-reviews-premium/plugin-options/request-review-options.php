<?php
/**
 * Review for discounts main tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'request-review' => array(
		'request-review-tabs' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'request-review-settings'  => array(
					'title'        => esc_html_x( 'General options', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description'  => esc_html_x( 'Set the standard behavior of the "Review reminder" module.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
					'options_path' => yith_ywar_get_module_path( 'request-review', 'options/request-review-settings.php' ),
				),
				'request-review-list'      => array(
					'title'        => esc_html_x( 'Email list', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description'  => esc_html_x( 'Track and manage review reminder emails.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
					'options_path' => yith_ywar_get_module_path( 'request-review', 'options/request-review-list.php' ),
				),
				'request-review-blocklist' => array(
					'title'        => esc_html_x( 'Blocklist', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ),
					'description'  => esc_html_x( 'Manage users who do not wish to receive review reminders.', '[Admin panel] Section description', 'yith-woocommerce-advanced-reviews' ),
					'options_path' => yith_ywar_get_module_path( 'request-review', 'options/request-review-blocklist.php' ),
				),
			),
		),
	),
);
