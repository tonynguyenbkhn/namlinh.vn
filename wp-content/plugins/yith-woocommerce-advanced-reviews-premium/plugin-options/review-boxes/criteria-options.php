<?php
/**
 * Review box tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'review-boxes-criteria' => array(
		'review-boxes-criteria-list' => array(
			'type'          => 'taxonomy',
			'taxonomy'      => YITH_YWAR_Post_Types::CRITERIA_TAX,
			'wp-list-style' => 'boxed',
		),
	),
);
