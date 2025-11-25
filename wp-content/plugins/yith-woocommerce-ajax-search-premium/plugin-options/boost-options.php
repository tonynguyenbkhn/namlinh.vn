<?php
/**
 * Boost option page
 *
 * @author  YITH
 * @package YITH/Search/Options
 * @version 2.1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return array(
	'boost' => array(
		'boost-options' => array(
			'type'       => 'multi_tab',
			'nav-layout' => 'horizontal',
			'sub-tabs'   => array(
				'boost-boost-rule'    => array(
					'title' => esc_html_x( 'Boost Rules', 'Admin tab title', 'yith-woocommerce-ajax-search' ),
					'description' => esc_html_x('Set advanced rules to boost specific products, categories or brands in search results', 'Admin tab description', 'yith-woocommerce-ajax-search')
				),
				'boost-boost-product' => array(
					'title'       => esc_html_x( 'Boost Products', 'Admin tab title', 'yith-woocommerce-ajax-search' ),
					'description' => esc_html_x( 'Set a default boost value to promote specific products in the search results', 'Admin tab description', 'yith-woocommerce-ajax-search' ),
				),
			),
		),
	),
);
