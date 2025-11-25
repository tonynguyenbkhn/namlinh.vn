<?php
/**
 * Modules options
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'modules' => array(
		'modules-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywar_print_modules_tab',
		),
	),
);
