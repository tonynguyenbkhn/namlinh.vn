<?php
/**
 * Shortcode Premium class
 *
 * @author  YITH
 * @package YITH/Search
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Shortcode_Premium' ) && class_exists( 'YITH_WCAS_Shortcode' ) ) {
	/**
	 * Class definition
	 */
	class YITH_WCAS_Shortcode_Premium extends YITH_WCAS_Shortcode {

		/**
		 * Constructor
		 */
		public function __construct() {
			parent::__construct();
		}


		/**
		 * Return the legacy template
		 *
		 * @param   string $template  The template type.
		 * @param   array  $args      The args.
		 *
		 * @return false|string
		 */
		public function get_legacy_template( $template, $args ) {
			ob_start();
			$wc_get_template = function_exists( 'wc_get_template' ) ? 'wc_get_template' : 'woocommerce_get_template';
			$wc_get_template( 'yith-woocommerce-ajax-search' . $template . '.php', $args, '', YITH_WCAS_DIR . 'templates/' );

			return ob_get_clean();
		}

		/**
		 * Return the block code based on options.
		 *
		 * @param   array $options  Options to check.
		 *
		 * @return string
		 */
		public function get_classic_block_code( $options ) {

			$block_options = array(
				'size'      => $options['general']['style'],
				'className' => $options['general']['custom_class'],
			);

			$block  = '<!-- wp:yith/search-block ' . wp_json_encode( $block_options ) . '  -->';
			$block .= '<div class="wp-block-yith-search-block alignwide ' . esc_attr( $block_options['className'] ) . '">';

			// Input.
			$block .= $this->get_input_block_code_by_options( $options );

			// Filled block.
			$block .= $this->get_filled_block_code_by_options( $options );

			// Empty block.
			$block .= $this->get_empty_block_code_by_options( $options );

			$block .= '</div><!-- /wp:yith/search-block -->';

			return $block;
		}


		/**
		 * Return the block code based on options.
		 *
		 * @param   array $options  Options to check.
		 *
		 * @return string
		 */
		public function get_overlay_block_code( $options ) {

			$block_options = array(
				'className' => $options['general']['custom_class'],
			);

			$block  = '<!-- wp:yith/overlay-search-block ' . wp_json_encode( $block_options ) . '  -->';
			$block .= '<div class="wp-block-yith-overlay-search-block is-loading ' . esc_attr( $block_options['className'] ) . '">';

			// Trigger.
			$block .= $this->get_icon_trigger_block( $options );

			// Overlay Block.
			$block .= $this->get_overlay_block_by_options( $options );

			// Close.
			$block .= '</div><!-- /wp:yith/overlay-search-block -->';

			return $block;
		}

		/**
		 * Return the string to show the filled block
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_filled_block_code_by_options( $options ) {
			$block  = '<!-- wp:yith/filled-block -->';
			$block .= '<div class="wp-block-yith-filled-block">';

			$block .= $this->get_related_categories_block_code_by_options( $options );

			$block .= '<!-- wp:separator {"align":"wide","style":{"color":{"background":"#9797972e"},"spacing":{"margin":{"top":"10px","bottom":"10px"}}},"className":"is-style-wide ywcas-separator"} -->
                    <hr class="wp-block-separator alignwide has-text-color has-alpha-channel-opacity has-background is-style-wide ywcas-separator" style="margin-top:10px;margin-bottom:10px;background-color:#9797972e;color:#9797972e"/>
                    <!-- /wp:separator -->';

			$block .= $this->get_product_results_block_code_by_options( $options );
			$block .= $this->get_related_posts_block_code_by_options( $options );

			$block .= '</div><!-- /wp:yith/filled-block -->';

			return $block;
		}

		/**
		 * Return the string to show the empty block
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_empty_block_code_by_options( $options ) {
			$block  = '<!-- wp:yith/empty-block -->';
			$block .= '<div class="wp-block-yith-empty-block">';

			$block .= $this->get_history_block_code_by_options( $options );

			$block .= $this->get_popular_block_code_by_options( $options );

			$block .= '</div><!-- /wp:yith/empty-block -->';

			return $block;
		}

		/**
		 * Return the code to create related categories block
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_related_categories_block_code_by_options( $options ) {
			$extra = $this->get_extra_options( $options );

			if ( ! isset( $extra['show-related-categories'] ) || 'yes' !== $extra['show-related-categories'] ) {
				return '';
			}

			$block_options = array(
				'relatedCategoryHeading' => $extra['related-categories-label'],
				'maxCategoryRelated'     => $extra['max-related-categories-results'],
			);

			$type = isset( $options['general']['type'] ) && 'overlay' === $options['general']['type'] ? 'overlay-' : '';

			$block  = '<!-- wp:yith/' . $type . 'related-categories-block ' . wp_json_encode( $block_options ) . ' -->';
			$block .= '<div class="wp-block-yith-' . $type . 'related-categories-block"></div>';
			$block .= '<!-- /wp:yith/' . $type . 'related-categories-block -->';

			return $block;
		}


		/**
		 * Return the code to create related posts block
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_related_posts_block_code_by_options( $options ) {
			$result_options = $options['search-results'];

			if ( ! isset( $result_options['related-to-show'] ) ) {
				return '';
			}

			$block_options = array(
				'relatedPostsHeading' => $result_options['related-label'],
				'maxPostsRelated'     => $result_options['related-limit'],
				'enabledPost'         => in_array( 'post', $result_options['related-to-show'], true ),
				'enabledPage'         => in_array( 'page', $result_options['related-to-show'], true ),

			);

			$type = isset( $options['general']['type'] ) && 'overlay' === $options['general']['type'] ? 'overlay-' : '';

			if ( isset( $options['general']['type'] ) && 'overlay' === $options['general']['type'] ) {
				$block_options['background'] = '#fff';
			}

			$block  = '<!-- wp:yith/' . $type . 'related-posts-block ' . wp_json_encode( $block_options ) . ' -->';
			$block .= '<div class="wp-block-yith-' . $type . 'related-posts-block"></div>';
			$block .= '<!-- /wp:yith/' . $type . 'related-posts-block -->';

			return $block;
		}

		/**
		 * Return the code to create history block
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_history_block_code_by_options( $options ) {
			$extra = $this->get_extra_options( $options );

			if ( ! isset( $extra['show-history'] ) || 'no' === $extra['show-history'] ) {
				return '';
			}

			$block_options = array(
				'maxHistoryResults' => $extra['max-history-results'],
				'historyHeading'    => $extra['history-label'],
			);

			$type   = isset( $options['general']['type'] ) && 'overlay' === $options['general']['type'] ? 'overlay-' : '';
			$block  = '<!-- wp:yith/' . $type . 'history-block ' . wp_json_encode( $block_options ) . ' -->';
			$block .= '<div class="wp-block-yith-' . $type . 'history-block"></div>';
			$block .= '<!-- /wp:yith/' . $type . 'history-block -->';

			return $block;
		}

		/**
		 * Return the code to create popular block
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_popular_block_code_by_options( $options ) {
			$extra = $this->get_extra_options( $options );
			if ( ! isset( $extra['show-popular'] ) || 'no' === $extra['show-popular'] ) {
				return '';
			}

			$block_options = array(
				'popularHeading'    => $extra['popular-label'],
				'maxPopularResults' => $extra['max-popular-results'],
			);
			$type          = isset( $options['general']['type'] ) && 'overlay' === $options['general']['type'] ? 'overlay-' : '';
			$block         = '<!-- wp:yith/' . $type . 'popular-block ' . wp_json_encode( $block_options ) . ' -->';
			$block        .= '<div class="wp-block-yith-' . $type . 'popular-block"></div>';
			$block        .= '<!-- /wp:yith/' . $type . 'popular-block -->';

			return $block;
		}

		/**
		 * Return from shortcode option, the extra tab options
		 *
		 * @param   array $options  The options.
		 *
		 * @return array
		 */
		protected function get_extra_options( $options ) {
			if ( empty( $options['extra-options'] ) ) {
				$default = ywcas()->settings->get_default_shortcode_options();
				$extra   = $default['extra-options'];
			} else {
				$extra = $options['extra-options'];
			}

			return $extra;
		}

		/**
		 * Return the icon trigger block
		 *
		 * @param   array $options  The options.
		 *
		 * @return string
		 */
		protected function get_icon_trigger_block( $options ) {

			$extra         = $this->get_extra_options( $options );
			$block_options = array(
				'iconColor'      => $extra['icon-colors']['color'] ?? '#000',
				'iconColorHover' => $extra['icon-colors']['color-hover'] ?? '#000',
			);

			$block  = '<!-- wp:yith/icon-trigger-block ' . wp_json_encode( $block_options ) . ' -->';
			$block .= '<div class="wp-block-yith-icon-trigger-block"></div>';
			$block .= '<!-- /wp:yith/icon-trigger-block -->';

			return apply_filters( 'ywcas_icon_trigger_block', $block, $options );
		}


		/**
		 * Return the overlay block by options
		 *
		 * @param   array $options  The options.
		 *
		 * @return string
		 */
		protected function get_overlay_block_by_options( $options ) {

			$block  = '<!-- wp:yith/overlay-block -->';
			$block .= '<div class="wp-block-yith-overlay-block is-loading">';

			$block .= $this->get_overlay_input_block_code_by_options( $options );

			$block .= $this->get_overlay_filled_block_code_by_options( $options );

			$block .= $this->get_overlay_empty_block_code_by_options( $options );

			$block .= '</div><!-- /wp:yith/overlay-block -->';

			return $block;
		}

		/**
		 * Return the string to show the input block
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_overlay_input_block_code_by_options( $options ) {
			$input_options = $options['search-input'];

			$border_size   = ! isset( $input_options['border_size'] ) ? '1px' : $input_options['border_size'] . 'px';
			$border_radius = ! isset( $input_options['border_radius'] ) ? '20px' : $input_options['border_radius'] . 'px';

			$block_options = array(
				'placeholder'           => $input_options['placeholder'],
				'placeholderTextColor'  => $input_options['colors'] ['placeholder'],
				'inputTextColor'        => $input_options['colors'] ['textcolor'],
				'inputBgColor'          => $input_options['colors'] ['background'],
				'inputBgFocusColor'     => $input_options['colors'] ['background-focus'],
				'inputBorderColor'      => $input_options['colors'] ['border'],
				'inputBorderFocusColor' => $input_options['colors'] ['border-focus'],
				'inputBorderSize'       => array(
					'topLeft'     => $border_size,
					'topRight'    => $border_size,
					'bottomLeft'  => $border_size,
					'bottomRight' => $border_size,
				),
				'inputBorderRadius'     => array(
					'topLeft'     => $border_radius,
					'topRight'    => $border_radius,
					'bottomLeft'  => $border_radius,
					'bottomRight' => $border_radius,
				),

			);

			$block  = '<!-- wp:yith/overlay-input-block ' . wp_json_encode( $block_options ) . ' -->';
			$block .= '<div class="wp-block-yith-overlay-input-block"></div>';
			$block .= '<!-- /wp:yith/overlay-input-block -->';

			return $block;
		}


		/**
		 * Return the string to show the filled block in overlay
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_overlay_filled_block_code_by_options( $options ) {

			$extra   = $this->get_extra_options( $options );
			$columns = array( 20, 80 );
			if ( ! isset( $extra['related-to-show'] ) && ( ! isset( $extra['show-related-categories'] ) || 'no' === $extra['show-related-categories'] ) ) {
				$columns = array( 0, 100 );
			}

			$block[] = '<!-- wp:yith/overlay-filled-block -->';
			$block[] = '<div class="wp-block-yith-overlay-filled-block">';
			$block[] = '<!-- wp:columns --><div class="wp-block-columns">';

			$block[] = '<!-- wp:column {"width":"' . $columns[0] . '%"} -->';
			$block[] = '<div class="wp-block-column" style="flex-basis:' . $columns[0] . '%">';
			$block[] = $this->get_related_categories_block_code_by_options( $options );
			$block[] = $this->get_related_posts_block_code_by_options( $options );
			$block[] = '</div><!-- /wp:column -->';

			$block[] = '<!-- wp:column {"width":"' . $columns[1] . '%"} -->';
			$block[] = '<div class="wp-block-column" style="flex-basis:' . $columns[1] . '%">';
			$block[] = $this->get_overlay_product_results_block_code_by_options( $options );
			$block[] = '</div><!-- /wp:column -->';

			$block[] = '</div><!-- /wp:columns -->';
			$block[] = '</div><!-- /wp:yith/overlay-filled-block -->';

			$filled_block = apply_filters( 'ywcas_overlay_filled_block_code_by_options', $block, $options );

			return implode( '', $filled_block );
		}

		/**
		 * Return the string to show the empty block in overlay
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_overlay_empty_block_code_by_options( $options ) {

			$extra   = $this->get_extra_options( $options );
			$columns = array( 20, 80 );
			if ( ! isset( $extra['show-history'], $extra['show-popular'] ) || ( 'no' === $extra['show-popular'] && 'no' === $extra['show-history'] ) ) {
				$columns = array( 0, 100 );
			}

			$result_options = $options['search-results'];
			$block_options  = array(
				'columns' => $result_options['columns'],
				'rows'    => $result_options['rows'],
			);

			$block[] = '<!-- wp:yith/overlay-empty-block -->';
			$block[] = '<div class="wp-block-yith-overlay-empty-block">';
			$block[] = '<!-- wp:columns --><div class="wp-block-columns">';

			$block[] = '<!-- wp:column {"width":"' . $columns[0] . '%"} -->';
			$block[] = '<div class="wp-block-column" style="flex-basis:' . $columns[0] . '%">';

			$block[] = $this->get_history_block_code_by_options( $options );
			$block[] = $this->get_popular_block_code_by_options( $options );
			$block[] = '</div><!-- /wp:column -->';

			$block[] = '<!-- wp:column {"width":"' . $columns[1] . '%"} -->';
			$block[] = '<div class="wp-block-column" style="flex-basis:' . $columns[1] . '%">';

			$block[] = '<!-- wp:heading {"fontSize":"medium"} -->';
			$block[] = '<h2 class="wp-block-heading has-medium-font-size">' . __( 'Popular products', 'yith-woocommerce-ajax-search' ) . '</h2>';
			$block[] = '<!-- /wp:heading -->';

			$block[] = '<!-- wp:woocommerce/product-best-sellers  ' . wp_json_encode( $block_options ) . ' /--></div>';

			$block[] = '</div><!-- /wp:column -->';

			$block[] = '</div><!-- /wp:columns -->';
			$block[] = '</div><!-- /wp:yith/overlay-filled-block -->';

			$empty_block = apply_filters( 'ywcas_overlay_empty_block_code_by_options', $block, $options );

			return implode( '', $empty_block );
		}

		/**
		 * Return the string to show the product results block in overlay
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_overlay_product_results_block_code_by_options( $options ) {

			$result_options = $options['search-results'];
			$block_options  = array(
				'columns'       => $result_options['columns'],
				'rows'          => $result_options['rows'],
				'showName'      => in_array( 'name', $result_options['info-to-show-overlay'], true ),
				'showImage'     => in_array( 'image', $result_options['info-to-show-overlay'], true ),
				'showPrice'     => in_array( 'price', $result_options['info-to-show-overlay'], true ),
				'showAddToCart' => in_array( 'add-to-cart', $result_options['info-to-show-overlay'], true ),
				'noResults'     => $result_options['no-results-label'],
			);
			$block          = '<!-- wp:yith/overlay-product-results-block ' . wp_json_encode( $block_options ) . ' -->';
			$block         .= '<div class="wp-block-yith-overlay-product-results-block">';

			$block .= $this->get_overlay_grid_block_code_by_options( $options );
			$block .= $this->get_overlay_pagination_block_code_by_options( $options );

			$block .= '</div><!-- /wp:yith/overlay-product-results-block -->';

			return $block;
		}

		/**
		 * Return the string to show the products in grid in overlay layout
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_overlay_grid_block_code_by_options( $options ) {
			$block  = '<!-- wp:yith/overlay-grid-block -->';
			$block .= '<div class="wp-block-yith-overlay-grid-block">';
			$block .= '</div><!-- /wp:yith/overlay-grid-block -->';

			return $block;
		}

		/**
		 * Return the string to show the product results block in overlay
		 *
		 * @param   array $options  Options.
		 *
		 * @return string
		 */
		protected function get_overlay_pagination_block_code_by_options( $options ) {
			$block  = '<!-- wp:yith/overlay-pagination-block -->';
			$block .= '<div class="wp-block-yith-overlay-pagination-block">';
			$block .= '</div><!-- /wp:yith/overlay-pagination-block -->';

			return $block;
		}
	}

}
