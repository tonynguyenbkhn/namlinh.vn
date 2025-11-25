<?php
/**
 * Shortcode tab options
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\PluginOptions\Shortcode
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WRVP' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'shortcode-similar-products' => array(
		'shortcode-similar-products-tab' => array(
			'type'   => 'custom_tab',
			'action' => 'yith_wrvp_shortcode_tab',
		),
	),
);
