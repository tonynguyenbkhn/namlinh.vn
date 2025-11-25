<?php
/**
 * Emails tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'emails' => array(
		'emails-list' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_ywar_print_emails_tab',
		),
	),
);
