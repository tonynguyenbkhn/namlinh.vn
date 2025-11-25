<?php
/**
 * Migration tools options
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'migration-tools' => array(
		'migration-tools-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywar_print_migration_tab',
		),
	),
);
