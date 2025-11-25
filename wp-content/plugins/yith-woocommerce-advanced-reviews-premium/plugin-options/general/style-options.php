<?php
/**
 * General options sub tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions\General
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$load_more = 'no' === yith_ywar_get_option( 'ywar_show_load_more' ) ? '' : array(
	'name'         => esc_html_x( 'Load more button colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
	'type'         => 'yith-field',
	'yith-type'    => 'multi-colorpicker',
	'colorpickers' => array(
		array(
			'id'      => 'background',
			'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			'default' => yith_ywar_get_default( 'ywar_load_more_button_colors', 'background' ),
		),
		array(
			'id'      => 'background-hover',
			'name'    => esc_html_x( 'Background hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			'default' => yith_ywar_get_default( 'ywar_load_more_button_colors', 'background-hover' ),
		),
		array(
			'id'      => 'text',
			'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			'default' => yith_ywar_get_default( 'ywar_load_more_button_colors', 'text' ),
		),
		array(
			'id'      => 'text-hover',
			'name'    => esc_html_x( 'Text hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			'default' => yith_ywar_get_default( 'ywar_load_more_button_colors', 'text-hover' ),
		),
	),
	'id'           => 'ywar_load_more_button_colors',
);

return array(
	'general-style' => array(
		array(
			'name' => esc_html_x( 'Layout options', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Show % value on graph bars', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'Enable to show the % value for each rating in graph bars.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_summary_percentage_value',
			'default'   => yith_ywar_get_default( 'ywar_summary_percentage_value' ),
		),
		array(
			'name'      => esc_html_x( 'Show filtered reviews on modal window', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'Enable this option to display the reviews, filtered by rating, in a modal window.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_reviews_dialog',
			'default'   => yith_ywar_get_default( 'ywar_reviews_dialog' ),
		),
		array(
			'name'      => esc_html_x( 'Avatar and name position', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => esc_html_x( "Set the position of the user's avatar and name within the review box.", '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_avatar_name_position',
			'options'   => array(
				'above' => esc_html_x( 'Above review', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'below' => esc_html_x( 'Below review', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'default'   => yith_ywar_get_default( 'ywar_avatar_name_position' ),
		),
		array(
			'name'      => esc_html_x( 'Avatar style', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => esc_html_x( 'Set the user avatar style', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_avatar_type',
			'options'   => array(
				'image'    => esc_html_x( 'Image', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'initials' => esc_html_x( 'Initials', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'default'   => yith_ywar_get_default( 'ywar_avatar_type' ),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Colors', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'         => esc_html_x( 'General', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'main',
					'name'    => esc_html_x( 'Accent', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_general_color', 'main' ),
				),
				array(
					'id'      => 'hover-icons',
					'name'    => esc_html_x( 'Hover icons', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_general_color', 'hover-icons' ),
				),
			),
			'id'           => 'ywar_general_color',
		),
		array(
			'name'         => esc_html_x( 'Average rating & Graph boxes', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'background',
					'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_rating_graph_boxes', 'background' ),
				),
			),
			'id'           => 'ywar_rating_graph_boxes',
		),
		array(
			'title'        => esc_html_x( 'Graph', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'default',
					'name'    => esc_html_x( 'Default', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_graph_colors', 'default' ),
				),
				array(
					'id'      => 'accent',
					'name'    => esc_html_x( 'Accent', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_graph_colors', 'accent' ),
				),
				array(
					'id'      => 'percentage',
					'name'    => esc_html_x( '% Value', '[Admin panel] Refers to the % value of the frontend graph bars', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_graph_colors', 'percentage' ),
				),
			),
			'id'           => 'ywar_graph_colors',
		),
		array(
			'title'        => esc_html_x( 'Stars', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'default',
					'name'    => esc_html_x( 'Default', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_stars_colors', 'default' ),
				),
				array(
					'id'      => 'accent',
					'name'    => esc_html_x( 'Accent', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_stars_colors', 'accent' ),
				),
			),
			'id'           => 'ywar_stars_colors',
		),
		array(
			'title'        => esc_html_x( 'Avatar', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'background',
					'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_avatar_colors', 'background' ),
				),
				array(
					'id'      => 'initials',
					'name'    => esc_html_x( 'Initials', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_avatar_colors', 'initials' ),
				),
			),
			'id'           => 'ywar_avatar_colors',
			'deps'         => array(
				'id'    => 'ywar_avatar_type',
				'value' => 'initials',
			),
		),
		array(
			'title'        => esc_html_x( 'Like section', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'background',
					'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_like_section_colors', 'background' ),
				),
				array(
					'id'      => 'background-rated',
					'name'    => esc_html_x( 'Background rated', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_like_section_colors', 'background-rated' ),
				),
				array(
					'id'      => 'icon',
					'name'    => esc_html_x( 'Icon', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_like_section_colors', 'icon' ),
				),
				array(
					'id'      => 'icon-rated',
					'name'    => esc_html_x( 'Icon rated', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_like_section_colors', 'icon-rated' ),
				),
			),
			'id'           => 'ywar_like_section_colors',
		),
		array(
			'title'        => esc_html_x( 'Review box', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'border',
					'name'    => esc_html_x( 'Border', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_review_box_colors', 'border' ),
				),
				array(
					'id'      => 'shadow',
					'name'    => esc_html_x( 'Shadow', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_review_box_colors', 'shadow' ),
				),
			),
			'id'           => 'ywar_review_box_colors',
		),
		array(
			'title'        => esc_html_x( 'Submit button', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'         => 'yith-field',
			'yith-type'    => 'multi-colorpicker',
			'colorpickers' => array(
				array(
					'id'      => 'background',
					'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_submit_button_colors', 'background' ),
				),
				array(
					'id'      => 'background-hover',
					'name'    => esc_html_x( 'Background hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_submit_button_colors', 'background-hover' ),
				),
				array(
					'id'      => 'text',
					'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_submit_button_colors', 'text' ),
				),
				array(
					'id'      => 'text-hover',
					'name'    => esc_html_x( 'Text hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_submit_button_colors', 'text-hover' ),
				),
			),
			'id'           => 'ywar_submit_button_colors',
		),
		$load_more,
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Badges', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Show staff badge on admin replies', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( "Enable this option to show a badge for the shop admin's replies.", '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_show_staff_badge',
			'default'   => yith_ywar_get_default( 'ywar_show_staff_badge' ),
		),
		array(
			'name'      => esc_html_x( 'Staff badge', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'inline-fields',
			'fields'    => array(
				'label'            => array(
					'type'    => 'text',
					'label'   => esc_html_x( 'Label', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_staff_badge', 'label' ),
				),
				'background-color' => array(
					'type'    => 'colorpicker',
					'label'   => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_staff_badge', 'background-color' ),
				),
				'text-color'       => array(
					'type'    => 'colorpicker',
					'label'   => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_staff_badge', 'text-color' ),
				),
			),
			'id'        => 'ywar_staff_badge',
			'deps'      => array(
				'id'    => 'ywar_show_staff_badge',
				'value' => 'yes',
			),
		),
		array(
			'name'      => esc_html_x( 'Featured review badge', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'inline-fields',
			'fields'    => array(
				'label'            => array(
					'type'    => 'text',
					'label'   => esc_html_x( 'Label', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_featured_badge', 'label' ),
				),
				'background-color' => array(
					'type'    => 'colorpicker',
					'label'   => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_featured_badge', 'background-color' ),
				),
				'text-color'       => array(
					'type'    => 'colorpicker',
					'label'   => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_featured_badge', 'text-color' ),
				),
				'border-color'     => array(
					'type'    => 'colorpicker',
					'label'   => esc_html_x( 'Review border', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_featured_badge', 'border-color' ),
				),
				'shadow'           => array(
					'type'    => 'colorpicker',
					'label'   => esc_html_x( 'Shadow', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
					'default' => yith_ywar_get_default( 'ywar_featured_badge', 'shadow' ),
				),
			),
			'id'        => 'ywar_featured_badge',
		),
		array(
			'type' => 'sectionend',
		),
	),
);
