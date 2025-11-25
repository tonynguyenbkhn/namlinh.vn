<?php
/**
 * General search option page
 *
 * @author  YITH
 * @package YITH/Search/Options
 * @version 2.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


$customization_tab = array(
	'customization' => array(
		'section_badge'             => array(
			'name' => _x( 'Colors', 'Admin section label', 'yith-woocommerce-ajax-search' ),
			'type' => 'title',
			'id'   => 'ywcas_section_badge_settings',
		),

		'sale_badge'                => array(
			'id'           => 'yith_wcas_sale_badge',
			'name'         => _x( '"Sale" badge colors', 'Admin option label', 'yith-woocommerce-ajax-search' ),
			'desc'         => '',
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'            => 'bgcolor',
					'name'          => _x( 'Background color', 'Color variant', 'yith-woocommerce-ajax-search' ),
					'alpha_enabled' => true,
					'default'       => '#7eb742',
				),
				array(
					'id'            => 'color',
					'name'          => _x( 'Text color', 'Color variant', 'yith-woocommerce-ajax-search' ),
					'alpha_enabled' => false,
					'default'       => '#ffffff',
				),
			),
		),

		'outofstock'                => array(
			'id'           => 'yith_wcas_outofstock',
			'name'         => _x( '"Out of stock" badge colors', 'Admin option label', 'yith-woocommerce-ajax-search' ),
			'desc'         => '',
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'            => 'bgcolor',
					'name'          => _x( 'Background color', 'Color variant', 'yith-woocommerce-ajax-search' ),
					'alpha_enabled' => true,
					'default'       => '#7a7a7a',
				),
				array(
					'id'            => 'color',
					'name'          => _x( 'Text color', 'Color variant', 'yith-woocommerce-ajax-search' ),
					'alpha_enabled' => false,
					'default'       => '#ffffff',
				),
			),
		),

		'featured_badge'            => array(
			'id'           => 'yith_wcas_featured_badge',
			'name'         => _x( '"Featured" badge colors', 'Admin option label', 'yith-woocommerce-ajax-search' ),
			'desc'         => '',
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'            => 'bgcolor',
					'name'          => _x( 'Background color', 'Color variant', 'yith-woocommerce-ajax-search' ),
					'alpha_enabled' => true,
					'default'       => '#c0392b',
				),
				array(
					'id'            => 'color',
					'name'          => _x( 'Text color', 'Color variant', 'yith-woocommerce-ajax-search' ),
					'alpha_enabled' => false,
					'default'       => '#ffffff',
				),
			),
		),

		'related_bg_color'          => array(
			'id'           => 'yith_wcas_related_bg_color',
			'name'         => __( 'Related content background colors', 'yith-woocommerce-ajax-search' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'            => 'bgcolor',
					'name'          => _x( 'Background color', 'Color variant', 'yith-woocommerce-ajax-search' ),
					'alpha_enabled' => true,
					'default'       => '#f1f1f1',
				),
			),
		),

		'section_end_section_badge' => array(
			'type' => 'sectionend',
			'id'   => 'ywcas_section_badge_end',
		),
	),
);

/**
 * APPLY_FILTERS: ywcas_customization_options_tab
 *
 * This filter allow to manage the customization options tab
 *
 * @param array $customization_tab List of options.
 *
 * @return array
 */
return apply_filters( 'ywcas_customization_options_tab', $customization_tab );
