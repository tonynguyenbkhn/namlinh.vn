<?php
/**
 * Shortcodes data
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\PluginOptions
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WRVP' ) ) {
	exit;
} // Exit if accessed directly

$shortcodes = array(
	'yith_similar_products'     => array(
		'title'       => _x( 'Recently Viewed Products', '[gutenberg]: block name', 'yith-woocommerce-recently-viewed-products' ),
		'description' => _x( 'With this block you can do something....', '[gutenberg]: block description', 'yith-woocommerce-recently-viewed-products' ),
		'block_id'    => 'yith-wrvp-block',
		'attributes'  => array(
			'title'           => array(
				'type'    => 'text',
				'title'   => _x( 'The section title', '[gutenberg]: attributes description', 'yith-woocommerce-recently-viewed-products' ),
				'default' => get_option( 'yith-wrvp-section-title', __( 'You may be interested in', 'yith-woocommerce-recently-viewed-products' ) ),
			),
			'view_all'        => array(
				'type'    => 'text',
				'title'   => _x( 'The "View All" link text', '[gutenberg]: attribute description', 'yith-woocommerce-recently-viewed-products' ),
				'default' => get_option( 'yith-wrvp-view-all-text', __( 'View All', 'yith-woocommerce-recently-viewed-products' ) ),
			),
			'prod_type'       => array(
				'type'    => 'radio',
				'title'   => _x( 'Select which products to show', '[gutenberg]: attribute description', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'viewed'  => _x( 'Only viewed products', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'similar' => _x( 'Viewed products and similar items', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-type-products', 'viewed' ),
			),
			'similar_type'    => array(
				'type'    => 'radio',
				'title'   => _x( 'If you choose to show similar products select how to get them', '[gutenberg]: attribute description', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'cats' => _x( 'By categories', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'tags' => _x( 'By tags', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'both' => _x( 'By both categories and tags', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => 'both',
			),
			'num_post'        => array(
				'type'    => 'number',
				'title'   => _x( 'Set how many products to show in plugin section (set -1 to display all)', '[gutenberg]: block description', 'yith-woocommerce-recently-viewed-products' ),
				'default' => 6,
				'min'     => -1,
			),
			'num_columns'     => array(
				'type'    => 'number',
				'title'   => _x( 'Set how many products to show per row', '[gutenberg]: block description', 'yith-woocommerce-recently-viewed-products' ),
				'default' => 4,
				'min'     => 1,
			),
			'order'           => array(
				'type'    => 'radio',
				'title'   => _x( 'Choose in which order the products should be shown', '[gutenberg]: attribute description', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'rand'     => _x( 'Random', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'viewed'   => _x( 'Latest viewed', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'sales'    => _x( 'Sales', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'newest'   => _x( 'Newest', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'high-low' => _x( 'Price: High to Low', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'low-high' => _x( 'Price: Low to High', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-order-products', 'rand' ),
			),
			'cat_most_viewed' => array(
				'type'    => 'onoff',
				'title'   => _x( 'Show only the most viewed category', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'yes' => _x( 'Yes', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'no'  => _x( 'No', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-cat-most-viewed', 'yes' ),
			),
			'cats_id'         => array(
				'type'     => 'select',
				'title'    => _x( 'Select the categories to show', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options'  => yith_wrvp_get_categories_list(),
				'multiple' => true,
			),
			'slider'          => array(
				'type'    => 'onoff',
				'title'   => _x( 'Enable slider', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'yes' => _x( 'Yes', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'no'  => _x( 'No', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-slider', 'no' ),
			),
			'autoplay'        => array(
				'type'    => 'onoff',
				'title'   => _x( 'Enable slider autoplay', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'yes' => _x( 'Yes', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'no'  => _x( 'No', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-slider-autoplay', 'no' ),
			),
			'dots'            => array(
				'type'    => 'onoff',
				'title'   => _x( 'Show dot indicators', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'yes' => _x( 'Yes', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'no'  => _x( 'No', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-slider-dots', 'no' ),
			),
		),
	),
	'yith_most_viewed_products' => array(
		'title'       => _x( 'Most Viewed Products', '[gutenberg]: block name', 'yith-woocommerce-recently-viewed-products' ),
		'description' => _x( 'With this block you can do something....', '[gutenberg]: block description', 'yith-woocommerce-recently-viewed-products' ),
		'block_id'    => 'yith-wrvp-most-viewed-block',
		'attributes'  => array(
			'title'       => array(
				'type'    => 'text',
				'title'   => _x( 'The section title', '[gutenberg]: attributes description', 'yith-woocommerce-recently-viewed-products' ),
				'default' => __( 'Most Viewed Products', 'yith-woocommerce-recently-viewed-products' ),
			),
			'num_post'    => array(
				'type'    => 'number',
				'title'   => _x( 'Set how many products to show in plugin section (set -1 to display all)', '[gutenberg]: block description', 'yith-woocommerce-recently-viewed-products' ),
				'default' => 6,
				'min'     => -1,
			),
			'num_columns' => array(
				'type'    => 'number',
				'title'   => _x( 'Set how many products to show per row', '[gutenberg]: block description', 'yith-woocommerce-recently-viewed-products' ),
				'default' => 4,
				'min'     => 1,
			),
			'cats_id'     => array(
				'type'     => 'select',
				'title'    => _x( 'Select the categories to show', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options'  => yith_wrvp_get_categories_list(),
				'multiple' => true,
			),
			'slider'      => array(
				'type'    => 'onoff',
				'title'   => _x( 'Enable slider', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'yes' => _x( 'Yes', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'no'  => _x( 'No', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-slider', 'no' ),
			),
			'autoplay'    => array(
				'type'    => 'onoff',
				'title'   => _x( 'Enable slider autoplay', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'yes' => _x( 'Yes', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'no'  => _x( 'No', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-slider-autoplay', 'no' ),
			),
			'dots'        => array(
				'type'    => 'onoff',
				'title'   => _x( 'Show dot indicators', '[gutenberg]: Option title', 'yith-woocommerce-recently-viewed-products' ),
				'options' => array(
					'yes' => _x( 'Yes', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
					'no'  => _x( 'No', '[gutenberg]: shortcode attribute option', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default' => get_option( 'yith-wrvp-slider-dots', 'no' ),
			),
		),
	),
);

/**
 * APPLY_FILTERS: yith_wrvp_shortcodes_data
 *
 * Filters the shortcode options for the Gutenberg blocks.
 *
 * @param array $shortcodes Shortcode options.
 *
 * @return array
 */
return apply_filters( 'yith_wrvp_shortcodes_data', $shortcodes );
