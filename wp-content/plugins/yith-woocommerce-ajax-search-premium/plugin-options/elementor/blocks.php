<?php
/**
 * This file contain the config for elementor
 *
 * @author  YITH
 * @package YITH/Search/Options
 * @version 2.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$shortcodes     = ywcas()->settings->get_shortcodes_list();
$shortcode_list = array();
foreach ( $shortcodes as $key => $shortcode ) {
	$shortcode_list[ $key ] = $shortcode['name'];
}
$blocks = array(
	'yith-ywcas-widget' => array(
		'style'                        => 'ywcas-frontend',
		'title'                        => esc_html_x( 'Classic Search', '[elementor]: block name', 'yith-woocommerce-ajax-search' ),
		'description'                  => esc_html_x( 'Add the classic search block', '[elementor]: block description', 'yith-woocommerce-ajax-search' ),
		'shortcode_name'               => 'yith_woocommerce_ajax_search',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-kit-details',
		'editor_render_cb'             => 'ywcas_show_elementor_preview',
		'do_shortcode'                 => true,
		'keywords'                     => array(
			esc_html_x( 'Search', '[elementor]: keywords', 'yith-woocommerce-ajax-search' ),
			esc_html_x( 'Ajax Search Widget', '[elementor]: keywords', 'yith-woocommerce-ajax-search' ),
		),
		'attributes'                   => array(
			'preset' => array(
				'type'    => 'select',
				'label'   => __( 'Preset', 'yith-woocommerce-ajax-search' ),
				'default' => 'default',
				'options' => $shortcode_list,
			),
		),
	),
);

return apply_filters( 'ywcas_elementor_blocks', $blocks );
