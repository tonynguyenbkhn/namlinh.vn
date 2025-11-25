<?php
/**
 * GENERAL ARRAY OPTIONS
 *
 * @package YITH\RecentlyViewedProducts\PluginOptions
 */

$general = array(
	'general' => array(
		array(
			'title' => _x( 'General Settings', 'Admin section title', 'yith-woocommerce-recently-viewed-products' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith-wrvp-general-options',
		),
		array(
			'id'        => 'yith-wrvp-cookie-time',
			'title'     => __( 'Set cookie time', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Set the duration (days) of the cookie that tracks the user\'s viewed products.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'default'   => '30',
			'min'       => '1',
		),
		array(
			'id'        => 'yith-wrvp-type-products',
			'title'     => __( 'Select which products to show', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Choose whether to only show viewed products or also similar items.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'viewed'  => __( 'Only viewed products', 'yith-woocommerce-recently-viewed-products' ),
				'similar' => __( 'Viewed products and similar items', 'yith-woocommerce-recently-viewed-products' ),
			),
			'default'   => 'viewed',
		),
		array(
			'id'        => 'yith-wrvp-type-similar-products',
			'title'     => __( 'Get similar products by', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Choose to get similar products by categories, tags or both', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'cats' => __( 'Categories', 'yith-woocommerce-recently-viewed-products' ),
				'tags' => __( 'Tags', 'yith-woocommerce-recently-viewed-products' ),
				'both' => __( 'Both', 'yith-woocommerce-recently-viewed-products' ),
			),
			'default'   => 'both',
			'deps'      => array(
				'id'    => 'yith-wrvp-type-products',
				'value' => 'similar',
				'type'  => 'hide',
			),
		),
		array(
			'id'        => 'yith-wrvp-num-products',
			'title'     => __( 'Set how many products to show', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Choose how many products to show and how many of them to show in each row.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'inline-fields',
			'fields'    => array(
				'html'    => array(
					'type' => 'html',
					'html' => __( 'Show', 'yith-woocommerce-recently-viewed-products' ),
				),
				'total'   => array(
					'type'    => 'number',
					'default' => 6,
					'min'     => 1,
				),
				'html1'   => array(
					'type' => 'html',
					'html' => __( 'products and show', 'yith-woocommerce-recently-viewed-products' ),
				),
				'per-row' => array(
					'type'    => 'number',
					'default' => 4,
					'min'     => 1,
				),
				'html2'   => array(
					'type' => 'html',
					'html' => __( 'products for each row', 'yith-woocommerce-recently-viewed-products' ),
				),
			),
			'default'   => array(
				'total'   => 6,
				'per-row' => 4,
			),
		),
		array(
			'id'        => 'yith-wrvp-order-products',
			'title'     => __( 'Order products by', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Choose in which order the products should be shown.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'rand'     => __( 'Random', 'yith-woocommerce-recently-viewed-products' ),
				'viewed'   => __( 'Latest viewed', 'yith-woocommerce-recently-viewed-products' ),
				'sales'    => __( 'Sales', 'yith-woocommerce-recently-viewed-products' ),
				'newest'   => __( 'Newest', 'yith-woocommerce-recently-viewed-products' ),
				'high-low' => __( 'Price: High to Low', 'yith-woocommerce-recently-viewed-products' ),
				'low-high' => __( 'Price: Low to High', 'yith-woocommerce-recently-viewed-products' ),
			),
			'default'   => 'rand',
		),
		array(
			'title'         => __( 'Set which products to hide', 'yith-woocommerce-recently-viewed-products' ),
			'desc'          => __( 'Hide out-of-stock products', 'yith-woocommerce-recently-viewed-products' ),
			'type'          => 'checkbox',
			'default'       => 'no',
			'id'            => 'yith-wrvp-hide-out-of-stock',
			'checkboxgroup' => 'start',
		),
		array(
			'id'            => 'yith-wrvp-hide-free',
			'title'         => '',
			'desc'          => __( 'Hide free products', 'yith-woocommerce-recently-viewed-products' ),
			'type'          => 'checkbox',
			'default'       => 'no',
			'checkboxgroup' => '',
		),
		array(
			'id'            => 'yith-wrvp-excluded-purchased',
			'title'         => '',
			'desc'          => __( 'Hide products already bought by the customer', 'yith-woocommerce-recently-viewed-products' ),
			'type'          => 'checkbox',
			'default'       => 'no',
			'checkboxgroup' => 'end',
		),
		array(
			'id'        => 'yith-wrvp-cat-most-viewed',
			'title'     => __( 'Show only products from the most viewed category', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Enable to show only products of the most viewed category by the user.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
		),
		array(
			'id'        => 'yith-wrvp-slider',
			'title'     => __( 'Enable products slider', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Choose if you want to show a slider of products.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'yes',
		),
		array(
			'id'        => 'yith-wrvp-slider-autoplay',
			'title'     => __( 'Enable products slider autoplay', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Choose whether to enable autoplay for sliders.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'yes',
			'deps'      => array(
				'id'    => 'yith-wrvp-slider',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),
		array(
			'id'        => 'yith-wrvp-slider-dots',
			'title'     => __( 'Show dot indicators', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Choose whether to show dot indicators for sliders.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'deps'      => array(
				'id'    => 'yith-wrvp-slider',
				'value' => 'yes',
				'type'  => 'hide',
			),
		),
		array(
			'id'        => 'yith-wrvp-show-on-single',
			'title'     => __( 'Show shortcode in all product pages', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Enable to automatically show the shortcode in all product pages', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'yes',
		),
		array(
			'type' => 'sectionend',
			'id'   => 'yith-wrvp-end-general-options',
		),
		array(
			'title' => _x( 'Texts and Labels', 'Admin section title', 'yith-woocommerce-recently-viewed-products' ),
			'type'  => 'title',
			'desc'  => '',
			'id'    => 'yith-wrvp-labels-options',
		),
		array(
			'id'        => 'yith-wrvp-nofound-msg',
			'title'     => __( 'Text for no product found', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => __( 'Set which text to show in the Recently Viewed Products page when no product has been found.', 'yith-woocommerce-recently-viewed-products' ),
			'default'   => __( 'You have not viewed any product yet.', 'yith-woocommerce-recently-viewed-products' ),
		),
		array(
			'id'        => 'yith-wrvp-section-title',
			'title'     => __( 'Section title', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Enter a title for the viewed product section', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => __( 'Recently viewed products', 'yith-woocommerce-recently-viewed-products' ),
		),
		array(
			'id'        => 'yith-wrvp-view-all-text',
			'title'     => __( 'Text for "View All" link', 'yith-woocommerce-recently-viewed-products' ),
			'desc'      => __( 'Enter a custom text for the "View all" link.', 'yith-woocommerce-recently-viewed-products' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'default'   => __( 'View all >', 'yith-woocommerce-recently-viewed-products' ),
		),
		array(
			'type' => 'sectionend',
			'id'   => 'yith-wrvp-end-labels-options',
		),
	),
);

/**
 * APPLY_FILTERS: yith_wrvp_panel_general_options
 *
 * Filters the options available in the 'Settings' tab.
 *
 * @param array $options Array with options.
 *
 * @return array
 */
return apply_filters( 'yith_wrvp_panel_general_options', $general );
