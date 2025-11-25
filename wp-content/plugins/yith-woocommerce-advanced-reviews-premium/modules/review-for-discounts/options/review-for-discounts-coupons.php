<?php
/**
 * Review for discounts coupons tab array
 *
 * @package YITH\AdvancedReviews\Modules\ReviewforDiscounts\Options
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'review-for-discounts-coupons' => array(
		'review-for-discounts-coupons-list' => array(
			'type'                  => 'post_type',
			'post_type'             => YITH_YWAR_Post_Types::DISCOUNTS,
			'wp-list-style'         => 'classic',
			'wp-list-auto-h-scroll' => true,
		),
	),
);
