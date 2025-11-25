<?php
/**
 * Dashboard options
 *
 * @package YITH\AdvancedReviews\PluginOptions\Dashboard
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'dashboard-dashboard' => array(
		'dashboard-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywar_print_reports_tab',
		),
	),
);
