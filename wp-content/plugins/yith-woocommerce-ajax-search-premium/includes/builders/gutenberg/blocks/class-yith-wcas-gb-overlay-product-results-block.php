<?php
/**
 * YITH_WCAS_Gb_Overlay_Product_Results_Block is class to initialize Results Block
 *
 * @author  YITH
 * @package YITH/Builders/Gutenberg
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Gb_Overlay_Product_Results_Block' ) ) {
	/**
	 * Class YITH_WCAS_Gb_Search_Block
	 */
	class YITH_WCAS_Gb_Overlay_Product_Results_Block extends Abstract_YITH_WCAS_Gb_InnerBlock {
		/**
		 * Block name.
		 *
		 * @var string
		 */
		protected $block_name = 'overlay-product-results-block';

		/**
		 * Constructor.
		 */
		public function __construct() {
			parent::__construct();

		}
	}
}
