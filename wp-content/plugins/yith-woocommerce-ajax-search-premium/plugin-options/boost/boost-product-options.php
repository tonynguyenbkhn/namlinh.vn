<?php
/**
 * Boost product page
 *
 * @author  YITH
 * @package YITH/Search/Options
 * @version 2.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return array(
	'boost-boost-product' => array(
		'boost-product' => array(
			'type'          => 'custom_tab',
			'action'        => 'ywcas_product_boost_tab',
			'hide_sidebar'  => true,
			'wp-list-style' => 'classic',
		),
	),
);
