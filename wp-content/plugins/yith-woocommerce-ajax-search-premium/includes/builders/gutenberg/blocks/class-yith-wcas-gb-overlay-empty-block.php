<?php
/**
 * YITH_WCAS_Gb_Overlay_Empty_Block is class to initialize Results Block
 *
 * @author  YITH
 * @package YITH/Builders/Gutenberg
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Gb_Overlay_Empty_Block' ) ) {
	/**
	 * Class YITH_WCAS_Gb_Overlay_Empty_Block
	 */
	class YITH_WCAS_Gb_Overlay_Empty_Block extends Abstract_YITH_WCAS_Gb_InnerBlock {

		/**
		 * Block name.
		 *
		 * @var string
		 */
		protected $block_name = 'overlay-empty-block';

		/**
		 * Constructor.
		 */
		public function __construct() {
			parent::__construct();
			add_filter( 'ywcas_block_data_attributes', array( $this, 'add_attributes' ), 10, 2 );
		}

		/**
		 * Add a dynamic attribute to empty block.
		 *
		 * @param array $attributes Attributes to filter.
		 * @param array $block Block.
		 *
		 * @return array
		 */
		public function add_attributes( $attributes, $block ) {
			if ( $this->get_block_type() === $block['blockName'] ) {

				$inner_blocks                   = isset( $block['innerBlocks'] ) ? count( $block['innerBlocks'] ) : 0;
				$attributes['has_innerBlock']   = $inner_blocks;
				$attributes['has_historyBlock'] = $inner_blocks > 0 && $this->has_inner_block( 'yith/history-block', $block['innerBlocks'] ) ? 'yes' : 'no';
				$attributes['has_popularBlock'] = $inner_blocks > 0 && $this->has_inner_block( 'yith/popular-block', $block['innerBlocks'] ) ? 'yes' : 'no';
			}

			return $attributes;
		}

	}
}
