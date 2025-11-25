<?php
/**
 * Review request emails tab array
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Options
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$hide_button = yith_ywar_schedule_list_not_empty() ? 'visible' : '';
$button      = sprintf( '<a href="#" class="yith-ywar-add-emails yith-plugin-fw__button--primary %1$s">%2$s</a>', $hide_button, esc_html_x( 'Add new', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ) );

return array(
	'request-review-list' => array(
		'request-review-list-list' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywar_print_scheduled_emails_tab',
			'title'  => esc_html_x( 'Email list', '[Admin panel] Section name', 'yith-woocommerce-advanced-reviews' ) . $button,
		),
	),
);
