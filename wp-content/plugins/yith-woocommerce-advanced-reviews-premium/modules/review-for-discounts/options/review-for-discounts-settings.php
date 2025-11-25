<?php
/**
 * Review for discounts options tab array
 *
 * @package YITH\AdvancedReviews\Modules\ReviewforDiscounts\Options
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'review-for-discounts-settings' => array(
		array(
			'name' => esc_html_x( 'Coupon delivery', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Send coupon', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => esc_html_x( 'Choose when to send the coupon email to reward the user who left the review.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'options'   => array(
				'written'   => esc_html_x( 'After review creation', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'moderated' => esc_html_x( 'After review approval', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'default'   => yith_ywar_get_default( 'ywar_coupon_sending' ),
			'id'        => 'ywar_coupon_sending',
		),
		array(
			'type' => 'sectionend',
		),
	),
);
