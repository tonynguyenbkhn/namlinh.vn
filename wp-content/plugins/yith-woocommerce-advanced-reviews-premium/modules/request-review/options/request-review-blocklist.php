<?php
/**
 * Blocklist tab array
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Options
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$hide_button = yith_ywar_blocklist_not_empty() ? 'visible' : '';
$button      = sprintf( '<a href="#" class="yith-ywar-add-to-blocklist yith-plugin-fw__button--primary %1$s">%2$s</a>', $hide_button, esc_html_x( 'Add email', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ) );

return array(
	'request-review-blocklist' => array(
		'request-review-blocklist-list' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywar_print_blocklist_tab',
			'title'  => esc_html_x( 'Blocklist', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ) . $button,
		),
	),
);
