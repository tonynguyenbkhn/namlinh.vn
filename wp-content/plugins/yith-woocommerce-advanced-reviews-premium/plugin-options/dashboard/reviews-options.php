<?php
/**
 * Reviews tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions\Dashboard
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'dashboard-reviews' => array(
		'dashboard-reviews-list' => array(
			'type'                  => 'post_type',
			'post_type'             => YITH_YWAR_Post_Types::REVIEWS,
			'wp-list-style'         => 'classic',
			'wp-list-auto-h-scroll' => true,
		),
	),
);
