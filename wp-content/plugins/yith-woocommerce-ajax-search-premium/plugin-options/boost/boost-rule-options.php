<?php
/**
 * Plugin Options : Boost rule subtab
 *
 * @package YITH\AjaxSearh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'boost-boost-rule' => array(
		'boost-rule-list' => array(
			'type'          => 'post_type',
			'post_type'     => 'ywcas_boost',
			'wp-list-style' => 'boxed',
			'wrapper-class' => 'ywcas_boost_rules_table',
		),
	),
);
