<?php
/**
 * YITH_WCAS_Gb_Overlay_Related_Categories_Block is class to initialize Input Block
 *
 * @author  YITH
 * @package YITH/Builders/Gutenberg
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'YITH_WCAS_Gb_Overlay_Related_Categories_Block' ) ) {
	/**
	 * Class YITH_WCAS_Gb_Overlay_Related_Categories_Block
	 */
	class YITH_WCAS_Gb_Overlay_Related_Categories_Block extends Abstract_YITH_WCAS_Gb_InnerBlock {
		/**
		 * Block name.
		 *
		 * @var string
		 */
		protected $block_name = 'overlay-related-categories-block';

	}
}
