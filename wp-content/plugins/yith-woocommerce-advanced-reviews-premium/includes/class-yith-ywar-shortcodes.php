<?php
/**
 * Class YITH_YWAR_Shortcodes
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Shortcodes' ) ) {
	/**
	 * Class YITH_YWAR_Shortcodes
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	abstract class YITH_YWAR_Shortcodes {

		/**
		 * YITH_YWAR_Shortcodes init
		 *
		 * @return void
		 * @since  2.0.0
		 * */
		public static function init() {
			$prefix     = 'yith_ywar_show_';
			$shortcodes = array(
				'reviews',
				'reviews_form',
				'current_user_reviews',
			);
			foreach ( $shortcodes as $shortcode ) {
				if ( is_callable( 'YITH_YWAR_Shortcodes::get_' . $shortcode ) ) {
					add_shortcode( $prefix . $shortcode, 'YITH_YWAR_Shortcodes::get_' . $shortcode );
				}
			}
			add_action( 'plugins_loaded', 'YITH_YWAR_Shortcodes::register_gutenberg_blocks', 20 );
			add_action( 'plugins_loaded', 'YITH_YWAR_Shortcodes::register_elementor_blocks', 20 );
		}

		/**
		 * Register Gutenberg Blocks
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function register_gutenberg_blocks() {
			$blocks = array(
				'yith-ywar-reviews'              => array(
					'title'                  => esc_html_x( 'Reviews', '[Gutenberg] block name', 'yith-woocommerce-advanced-reviews' ),
					'description'            => esc_html_x( 'With this block you can print the reviews of a product or all reviews with different options', '[Gutenberg] block description', 'yith-woocommerce-advanced-reviews' ),
					'use_frontend_preview'   => true,
					'render_callback'        => function ( $attributes ) {
						$attributes['pagination'] = 'on' === $attributes['pagination'] ? 'yes' : $attributes['pagination'];

						return self::reviews_shortcode_template( $attributes );
					},
					'editor_script_handlers' => array( 'yith-ywar-frontend' ),
					'editor_style_handlers'  => array( 'yith-ywar-frontend' ),
					'style'                  => 'yith-ywar-frontend',
					'attributes'             => array(
						'product_id'       => array(
							'type'    => 'text',
							'label'   => esc_html_x( 'Product ID', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
						'per_page'         => array(
							'type'    => 'number',
							'label'   => esc_html_x( 'Reviews per page', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => 0,
						),
						'pagination'       => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Enable pagination', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => 'on',
						),
						'hide_buttons'     => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Hide action buttons (Edit, Reply, Helpful, Report)', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
						'hide_attachments' => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Hide attachments', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
						'hide_replies'     => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Hide replies', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
					),
				),
				'yith-ywar-reviews-with-form'    => array(
					'title'                  => esc_html_x( 'Reviews with form', '[Gutenberg] block name', 'yith-woocommerce-advanced-reviews' ),
					'description'            => esc_html_x( 'With this block you can print the reviews of a product with the insert form', '[Gutenberg] block description', 'yith-woocommerce-advanced-reviews' ),
					'use_frontend_preview'   => true,
					'render_callback'        => function ( $attributes ) {

						if ( ! comments_open( $attributes['product_id'] ) ) {
							return '';
						}

						$product = wc_get_product( $attributes['product_id'] );

						if ( ! $product instanceof WC_Product ) {
							global $product;
						}
						if ( ! $product && defined( 'YITH_PLUGIN_FW_BLOCK_PREVIEW' ) && YITH_PLUGIN_FW_BLOCK_PREVIEW ) {
							$products = wc_get_products( array( 'limit' => 1 ) );
							if ( ! ! $products ) {
								$product = $products[0];
							}
						}
						if ( $product ) {
							ob_start();
							?>
							<div class="woocommerce yith-ywar-product-page">
								<?php YITH_YWAR_Frontend::get_instance()->show_advanced_reviews_template( $product->get_id() ); ?>
							</div>
							<?php
							$html = ob_get_clean();
						} else {
							$html = esc_html_x( 'You must provide a valid Product ID', '[Gutenberg] block error message', 'yith-woocommerce-advanced-reviews' );
						}

						return $html;
					},
					'editor_script_handlers' => array( 'yith-ywar-frontend' ),
					'editor_style_handlers'  => array( 'yith-ywar-frontend' ),
					'style'                  => 'yith-ywar-frontend',
					'attributes'             => array(
						'product_id' => array(
							'type'    => 'text',
							'label'   => esc_html_x( 'Product ID', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),

					),
				),
				'yith-ywar-current-user-reviews' => array(
					'title'                  => esc_html_x( 'Current user reviews', '[Gutenberg] block name', 'yith-woocommerce-advanced-reviews' ),
					'description'            => esc_html_x( 'With this block you can print all the reviews written by the current user', '[Gutenberg] block description', 'yith-woocommerce-advanced-reviews' ),
					'use_frontend_preview'   => true,
					'render_callback'        => function () {
						ob_start();
						YITH_YWAR_Frontend_My_Account::get_instance()->my_account_content( 1, true );

						return ob_get_clean();
					},
					'editor_script_handlers' => array( 'yith-ywar-frontend' ),
					'editor_style_handlers'  => array( 'yith-ywar-frontend' ),
					'style'                  => 'yith-ywar-frontend',
				),
			);

			yith_plugin_fw_gutenberg_add_blocks( $blocks );
		}

		/**
		 * Register Elementor Blocks
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function register_elementor_blocks() {

			$blocks = array(
				'yith-ywar-reviews'              => array(
					'title'                  => esc_html_x( 'Reviews', '[Gutenberg] block name', 'yith-woocommerce-advanced-reviews' ),
					'description'            => esc_html_x( 'With this block you can print the reviews of a product or all reviews with different options', '[Gutenberg] block description', 'yith-woocommerce-advanced-reviews' ),
					'use_frontend_preview'   => true,
					'shortcode_name'         => 'yith_ywar_show_reviews',
					'do_shortcode'           => true,
					'editor_script_handlers' => array( 'yith-ywar-frontend' ),
					'editor_style_handlers'  => array( 'yith-ywar-frontend' ),
					'style'                  => 'yith-ywar-frontend',
					'attributes'             => array(
						'product_id'       => array(
							'type'    => 'text',
							'label'   => esc_html_x( 'Product ID', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
						'per_page'         => array(
							'type'    => 'number',
							'label'   => esc_html_x( 'Reviews per page', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => 0,
						),
						'pagination'       => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Enable pagination', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => 'on',
						),
						'hide_buttons'     => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Hide action buttons (Edit, Reply, Helpful, Report)', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
						'hide_attachments' => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Hide attachments', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
						'hide_replies'     => array(
							'type'    => 'toggle',
							'label'   => esc_html_x( 'Hide replies', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),
					),
				),
				'yith-ywar-reviews-with-form'    => array(
					'title'                  => esc_html_x( 'Reviews with form', '[Gutenberg] block name', 'yith-woocommerce-advanced-reviews' ),
					'description'            => esc_html_x( 'With this block you can print the reviews of a product with the insert form', '[Gutenberg] block description', 'yith-woocommerce-advanced-reviews' ),
					'shortcode_name'         => 'yith_ywar_show_reviews_form',
					'do_shortcode'           => true,
					'editor_script_handlers' => array( 'yith-ywar-frontend' ),
					'editor_style_handlers'  => array( 'yith-ywar-frontend' ),
					'style'                  => 'yith-ywar-frontend',
					'attributes'             => array(
						'product_id' => array(
							'type'    => 'text',
							'label'   => esc_html_x( 'Product ID', '[Gutenberg] block option field', 'yith-woocommerce-advanced-reviews' ),
							'default' => '',
						),

					),
				),
				'yith-ywar-current-user-reviews' => array(
					'title'                  => esc_html_x( 'Current user reviews', '[Gutenberg] block name', 'yith-woocommerce-advanced-reviews' ),
					'description'            => esc_html_x( 'With this block you can print all the reviews written by the current user', '[Gutenberg] block description', 'yith-woocommerce-advanced-reviews' ),
					'shortcode_name'         => 'yith_ywar_show_current_user_reviews',
					'do_shortcode'           => true,
					'editor_script_handlers' => array( 'yith-ywar-frontend' ),
					'editor_style_handlers'  => array( 'yith-ywar-frontend' ),
					'style'                  => 'yith-ywar-frontend',
				),
			);

			yith_plugin_fw_register_elementor_widgets( $blocks, true );
		}

		/**
		 * Reviews with form shortcode
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public static function get_reviews( $atts ) {

			$per_page                 = isset( $atts['reviews_number'] ) ? $atts['reviews_number'] : -1;
			$atts['per_page']         = isset( $atts['per_page'] ) ? $atts['per_page'] : $per_page;
			$atts['pagination']       = isset( $atts['pagination'] ) ? $atts['pagination'] : 'yes';
			$atts['hide_buttons']     = isset( $atts['hide_buttons'] ) ? $atts['hide_buttons'] : 'yes';
			$atts['hide_attachments'] = isset( $atts['hide_attachments'] ) ? $atts['hide_attachments'] : 'yes';
			$atts['hide_replies']     = isset( $atts['hide_replies'] ) ? $atts['hide_replies'] : 'yes';

			return self::reviews_shortcode_template( $atts );
		}

		/**
		 * Reviews with form shortcode
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public static function get_reviews_form( $atts ) {
			$product_id = isset( $atts['product_id'] ) ? $atts['product_id'] : 0;

			if ( ! comments_open( $product_id ) ) {
				return '';
			}

			ob_start();
			?>
			<div class="woocommerce yith-ywar-product-page">
				<?php YITH_YWAR_Frontend::get_instance()->show_advanced_reviews_template( $product_id ); ?>
			</div>
			<?php

			return ob_get_clean();
		}

		/**
		 * Reviews written by the current user
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public static function get_current_user_reviews() {

			ob_start();
			YITH_YWAR_Frontend_My_Account::get_instance()->my_account_content( 1, true );

			return ob_get_clean();
		}

		/**
		 * Reviews shortcode template
		 *
		 * @param array $args Array of arguments.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private static function reviews_shortcode_template( array $args ): string {

			$product = false;
			if ( ! empty( $args['product_id'] ) ) {
				$product = wc_get_product( $args['product_id'] );
				if ( ! $product instanceof WC_Product ) {
					global $product;
				}
				if ( ! $product && defined( 'YITH_PLUGIN_FW_BLOCK_PREVIEW' ) && YITH_PLUGIN_FW_BLOCK_PREVIEW ) {
					$products = wc_get_products( array( 'limit' => 1 ) );
					if ( ! ! $products ) {
						$product = $products[0];
					}
				}
			}

			$data_settings = array(
				'product_id'       => $product instanceof WC_Product ? $args['product_id'] : 0,
				'per_page'         => isset( $args['per_page'] ) && 0 !== intval( $args['per_page'] ) ? $args['per_page'] : -1,
				'pagination'       => ! yith_plugin_fw_is_true( $args['pagination'] ) ? 'no' : 'yes',
				'hide_buttons'     => ! yith_plugin_fw_is_true( $args['hide_buttons'] ) ? 'no' : 'yes',
				'hide_attachments' => ! yith_plugin_fw_is_true( $args['hide_attachments'] ) ? 'no' : 'yes',
				'hide_replies'     => ! yith_plugin_fw_is_true( $args['hide_replies'] ) ? 'no' : 'yes',
			);

			ob_start()
			?>
			<div id="yith-ywar-reviews-list-shortcode-<?php echo esc_attr( uniqid() ); ?>" data-settings="<?php echo esc_html( htmlentities( wp_json_encode( $data_settings ) ) ); ?>" class="yith-ywar-reviews-list-shortcode woocommerce"></div>
			<?php
			return ob_get_clean();
		}
	}
}
