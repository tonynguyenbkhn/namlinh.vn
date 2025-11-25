<?php
/**
 * Settings class
 *
 * @author  YITH
 * @package YITH/Search/Options
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Settings_Premium' ) && class_exists( 'YITH_WCAS_Settings' ) ) {
	/**
	 * Class definition
	 */
	class YITH_WCAS_Settings_Premium extends YITH_WCAS_Settings {

		/**
		 * Constructor
		 *
		 * @return void
		 */
		protected function __construct() {
			parent::__construct();
			add_filter( 'ywcas_related_content_post_type', array( $this, 'show_related_content_post_type' ) );
		}

		/**
		 * Get if the variations must be showed on search results.
		 *
		 * @return string
		 */
		public function get_include_variations() {
			return $this->get( 'include_variations', 'no' );
		}

		/**
		 * Get the source of popular searches
		 *
		 * @return string
		 */
		public function get_trending_searches_source() {
			return $this->get( 'trending_searches_source', 'popular' );
		}

		/**
		 * Get if out of stock
		 *
		 * @return string
		 */
		public function get_hide_out_of_stock() {
			return $this->get( 'hide_out_of_stock', 'no' );
		}

		/**
		 * Get the source of popular searches
		 *
		 * @return array
		 */
		public function get_trending_searches_keywords() {
			$keys = explode( ',', $this->get( 'trending_searches_keywords', '' ) );

			return array_map( 'trim', $keys );
		}

		/**
		 * Return the fields for the shortcode by the tab
		 *
		 * @param   string $key   The tab key.
		 * @param   string $slug  The shortcode slug.
		 *
		 * @return array
		 */
		public function get_shortcode_fields( $key, $slug ) {
			switch ( $key ) {
				case 'general':
					$fields = array(
						'name'         => array(
							'id'                => 'ywcas-name-' . $key . '_' . $slug,
							'type'              => 'text',
							'label'             => __( 'Name', 'yith-woocommerce-ajax-search' ),
							'class'             => 'ywcas-shortcode-field',
							'custom_attributes' => array(
								'placeholder' => __( 'Enter a name...', 'yith-woocommerce-ajax-search' ),
							),
							'desc'              => __( 'Set a name for this shortcode.', 'yith-woocommerce-ajax-search' ),
						),
						'type'         => array(
							'id'      => 'ywcas-type-' . $key . '_' . $slug,
							'type'    => 'select',
							'label'   => __( 'Style', 'yith-woocommerce-ajax-search' ),
							'options' => array(
								'classic' => __( 'Classic', 'yith-woocommerce-ajax-search' ),
								'overlay' => __( 'Fullscreen overlay', 'yith-woocommerce-ajax-search' ),
							),
							'class'   => 'ywcas-shortcode-field wc-enhanced-select',
							'desc'    => __( 'Choose the search type.', 'yith-woocommerce-ajax-search' ),

						),
						'style'        => array(
							'id'      => 'ywcas-style-' . $key . '_' . $slug,
							'type'    => 'select',
							'label'   => __( 'Size', 'yith-woocommerce-ajax-search' ),
							'options' => array(
								'sm' => __( 'Small', 'yith-woocommerce-ajax-search' ),
								'lg' => __( 'Large', 'yith-woocommerce-ajax-search' ),
							),
							'class'   => 'ywcas-shortcode-field wc-enhanced-select',
							'desc'    => __( 'Choose the search size.', 'yith-woocommerce-ajax-search' ),
							'deps'    => array(
								'id'    => 'ywcas-type-' . $key . '_' . $slug,
								'value' => 'classic',
								'type'  => 'show',
							),
						),
						'custom_class' => array(
							'id'    => 'ywcas-custom-class-' . $key . '_' . $slug,
							'type'  => 'text',
							'label' => __( 'CSS class', 'yith-woocommerce-ajax-search' ),
							'class' => 'ywcas-shortcode-field',
							'desc'  => __(
								'Enter additional CSS classes to customize this search. 
Separate multiple classes with spaces.',
								'yith-woocommerce-ajax-search'
							),
						),
					);
					break;
				case 'extra-options':
					$fields = array(
						'icon-colors'                    => array(
							'id'           => 'ywcas-input-colors-' . $key . '_' . $slug,
							'label'        => __( 'Icon Colors', 'yith-woocommerce-ajax-search' ),
							'type'         => 'multi-colorpicker',
							'deps'         => array(
								'id'    => 'ywcas-type-general_' . $slug,
								'value' => 'overlay',
								'type'  => 'show',
							),
							'colorpickers' => array(
								array(
									'id'      => 'color',
									'name'    => __( 'Color', 'yith-woocommerce-ajax-search' ),
									'default' => 'rgb(136, 136, 136)',
								),
								array(
									'id'      => 'color-hover',
									'name'    => __( 'Color Focus', 'yith-woocommerce-ajax-search' ),
									'default' => 'rgb(87, 87, 87)',
								),
							),
						),
						'show-history'                   => array(
							'id'    => 'ywcas-show-history-' . $key . '_' . $slug,
							'label' => _x( 'Show last searches', 'Admin option label', 'yith-woocommerce-ajax-search' ),
							'type'  => 'onoff',
							'desc'  => _x( 'Enable to show the last searches made by the user.', 'Admin option description', 'yith-woocommerce-ajax-search' ),
						),
						'max-history-results'            => array(
							'id'    => 'ywcas-max-history-results-' . $key . '_' . $slug,
							'label' => __( 'Max searches to show', 'yith-woocommerce-ajax-search' ),
							'type'  => 'number',
							'min'   => 1,
							'step'  => 1,
							'max'   => 10,
							'desc'  => __( 'Set how many searches to show.', 'yith-woocommerce-ajax-search' ),
							'class' => 'ywcas-shortcode-field',
							'deps'  => array(
								'id'    => 'ywcas-show-history-' . $key . '_' . $slug,
								'value' => 'yes',
								'type'  => 'hide',
							),
						),
						'history-label'                  => array(
							'id'    => 'ywcas-history-label-' . $key . '_' . $slug,
							'label' => __( 'Label', 'yith-woocommerce-ajax-search' ),
							'type'  => 'text',
							'class' => 'ywcas-shortcode-field',
							'desc'  => __( 'Set the label to show before the last searches.', 'yith-woocommerce-ajax-search' ),
							'deps'  => array(
								'id'    => 'ywcas-show-history-' . $key . '_' . $slug,
								'value' => 'yes',
								'type'  => 'hide',
							),
						),
						'show-popular'                   => array(
							'id'    => 'ywcas-show-popular-' . $key . '_' . $slug,
							'label' => _x( 'Show trending searches', 'Admin option label', 'yith-woocommerce-ajax-search' ),
							'type'  => 'onoff',
							'desc'  => _x( 'Enable to show the trending searches.', 'Admin option description', 'yith-woocommerce-ajax-search' ),
						),
						'max-popular-results'            => array(
							'id'    => 'ywcas-max-popular-results-' . $key . '_' . $slug,
							'label' => __( 'Max searches to show', 'yith-woocommerce-ajax-search' ),
							'type'  => 'number',
							'min'   => 1,
							'step'  => 1,
							'max'   => 10,
							'desc'  => __( 'Set how many searches to show.', 'yith-woocommerce-ajax-search' ),
							'class' => 'ywcas-shortcode-field',
							'deps'  => array(
								'id'    => 'ywcas-show-popular-' . $key . '_' . $slug,
								'value' => 'yes',
								'type'  => 'hide',
							),
						),
						'popular-label'                  => array(
							'id'    => 'ywcas-popular-label-' . $key . '_' . $slug,
							'label' => __( 'Label', 'yith-woocommerce-ajax-search' ),
							'type'  => 'text',
							'class' => 'ywcas-shortcode-field',
							'desc'  => __( 'Set the label to show before the trending searches.', 'yith-woocommerce-ajax-search' ),
							'deps'  => array(
								'id'    => 'ywcas-show-popular-' . $key . '_' . $slug,
								'value' => 'yes',
								'type'  => 'hide',
							),
						),
						'show-related-categories'        => array(
							'id'    => 'ywcas-show-related-categories-' . $key . '_' . $slug,
							'label' => _x( 'Show related categories', 'Admin option label', 'yith-woocommerce-ajax-search' ),
							'type'  => 'onoff',
							'desc'  => _x( 'Enable to show the related categories in search results.', 'Admin option description', 'yith-woocommerce-ajax-search' ),
						),
						'max-related-categories-results' => array(
							'id'    => 'ywcas-max-related-categories-results-' . $key . '_' . $slug,
							'label' => __( 'Max categories to show', 'yith-woocommerce-ajax-search' ),
							'type'  => 'number',
							'min'   => 1,
							'step'  => 1,
							'max'   => 10,
							'desc'  => __( 'Set how many related categories to show.', 'yith-woocommerce-ajax-search' ),
							'class' => 'ywcas-shortcode-field',
							'deps'  => array(
								'id'    => 'ywcas-show-related-categories-' . $key . '_' . $slug,
								'value' => 'yes',
								'type'  => 'hide',
							),
						),
						'related-categories-label'       => array(
							'id'    => 'ywcas-related-categories-label-' . $key . '_' . $slug,
							'label' => __( 'Label', 'yith-woocommerce-ajax-search' ),
							'type'  => 'text',
							'class' => 'ywcas-shortcode-field',
							'desc'  => __( 'Set the label to show before the related categories.', 'yith-woocommerce-ajax-search' ),
							'deps'  => array(
								'id'    => 'ywcas-show-related-categories-' . $key . '_' . $slug,
								'value' => 'yes',
								'type'  => 'hide',
							),
						),
					);
					break;
				default:
					$fields = parent::get_shortcode_fields( $key, $slug );
			}

			return $fields;
		}

		/**
		 * Get all shortcode tabs
		 *
		 * @return array
		 */
		public function get_shortcode_tabs() {
			$free_tab                  = parent::get_shortcode_tabs();
			$free_tab['extra-options'] = esc_html_x( 'Extra options', 'Settings tab header', 'yith-woocommerce-ajax-search' );

			return $free_tab;

		}

		/**
		 * Get the options for search input tab
		 *
		 * @param   string $key   The key.
		 * @param   string $slug  The slug.
		 *
		 * @return array[]
		 */
		public function get_shortcode_search_input_field( $key, $slug ) {
			$free_options = parent::get_shortcode_search_input_field( $key, $slug );

			$new_options = array(
				'border_size'   => array(
					'id'    => 'ywcas-border_size-' . $key . '_' . $slug,
					'label' => __( 'Border size (px)', 'yith-woocommerce-ajax-search' ),
					'type'  => 'number',
					'min'   => 0,
					'step'  => 1,
					'desc'  => __( 'Set the border size.', 'yith-woocommerce-ajax-search' ),
					'class' => 'ywcas-shortcode-field',
				),
				'border_radius' => array(
					'id'    => 'ywcas-border_radius-' . $key . '_' . $slug,
					'label' => __( 'Border radius (px)', 'yith-woocommerce-ajax-search' ),
					'type'  => 'number',
					'min'   => 0,
					'step'  => 1,
					'desc'  => __( 'Set the border radius. Higher values generate a rounded-style form.', 'yith-woocommerce-ajax-search' ),
					'class' => 'ywcas-shortcode-field',
				),
			);

			return array_slice( $free_options, 0, 1, true ) + $new_options + array_slice( $free_options, count( $new_options ) - 1, null, true );
		}

		/**
		 * Get the options for the submit button field
		 *
		 * @param   string $key   The key.
		 * @param   string $slug  The slug.
		 *
		 * @return array
		 */
		public function get_shortcode_submit_button_field( $key, $slug ) {
			$free_options                            = parent::get_shortcode_submit_button_field( $key, $slug );
			$free_options['search-style']['type']    = 'select';
			$free_options['search-style']['class']   = 'wc-enhanced-select';
			$free_options['search-style']['label']   = __( 'Submit search style', 'yith-woocommerce-ajax-search' );
			$free_options['search-style']['options'] = array(
				'icon' => __( 'Icon', 'yith-woocommerce-ajax-search' ),
				'text' => __( 'Text', 'yith-woocommerce-ajax-search' ),
				'both' => __( 'Icon + Text', 'yith-woocommerce-ajax-search' ),
			);
			$free_options['search-style']['desc']    = __( 'Choose the style for the submit search button.', 'yith-woocommerce-ajax-search' );
			$deep_classic                            = array(
				'id'    => 'ywcas-type-general_' . $slug,
				'value' => 'classic',
				'type'  => 'show',
			);
			$free_options['search-style']['deps']    = $deep_classic;
			$free_options['icon-colors']['deps']     = $deep_classic;

			$free_options['icon-position']['data'] = array(
				'ywcas-deps' => wp_json_encode(
					array(
						array(
							'id'    => 'ywcas-search-style-' . $key . '_' . $slug,
							'value' => 'icon',
						),
						array(
							'id'    => 'ywcas-type-general_' . $slug,
							'value' => 'classic',
						),
					)
				),
			);

			$premium_options = array(
				'button-label'  => array(
					'id'                => 'ywcas-button-label-' . $key . '_' . $slug,
					'type'              => 'text',
					'label'             => __( 'Text', 'yith-woocommerce-ajax-search' ),
					'custom_attributes' => array(
						'placeholder' => __( 'Enter a text', 'yith-woocommerce-ajax-search' ),
					),
					'desc'              => __( 'Set a label for the search button.', 'yith-woocommerce-ajax-search' ),
					'deps'              => array(
						'id'    => 'ywcas-search-style-' . $key . '_' . $slug,
						'value' => 'text,both',
						'type'  => 'show',
					),
				),
				'border-radius' => array(
					'id'    => 'ywcas-submit-border_radius-' . $key . '_' . $slug,
					'label' => __( 'Border radius (px)', 'yith-woocommerce-ajax-search' ),
					'type'  => 'number',
					'min'   => 0,
					'step'  => 1,
					'desc'  => __( 'Set the border radius. Higher values generate a rounded-style button.', 'yith-woocommerce-ajax-search' ),
					'class' => 'ywcas-shortcode-field',
					'deps'  => array(
						'id'    => 'ywcas-search-style-' . $key . '_' . $slug,
						'value' => 'text,both',
						'type'  => 'show',
					),
				),
			);

			return array_slice( $free_options, 0, 1, true ) + $premium_options + array_slice( $free_options, count( $premium_options ) - 1, null, true );
		}

		/**
		 * Get the options for the search results tab
		 *
		 * @param   string $key   The key.
		 * @param   string $slug  The slug.
		 *
		 * @return array[]
		 */
		public function get_shortcode_search_results_field( $key, $slug ) {
			$free_options                           = parent::get_shortcode_search_results_field( $key, $slug );
			$deep_classic                           = array(
				'id'    => 'ywcas-type-general_' . $slug,
				'value' => 'classic',
				'type'  => 'show',
			);
			$free_options['max-results']['deps']    = $deep_classic;
			$free_options['name-color']['deps']     = $deep_classic;
			$free_options['show-view-all']['deps']  = $deep_classic;
			$free_options['view-all-label']['deps'] = $deep_classic;
			$free_options['results-layout']['deps'] = $deep_classic;

			$info_to_show                                       = $free_options['info-to-show']['options'];
			$free_options['info-to-show']['options']            = array_merge(
				$info_to_show,
				array(
					'price'       => __( 'Price', 'yith-woocommerce-ajax-search' ),
					'stock'       => __( 'Stock', 'yith-woocommerce-ajax-search' ),
					'sku'         => __( 'SKU', 'yith-woocommerce-ajax-search' ),
					'excerpt'     => __( 'Summary', 'yith-woocommerce-ajax-search' ),
					'add-to-cart' => __( 'Add to cart', 'yith-woocommerce-ajax-search' ),
					'categories'  => __( 'Categories', 'yith-woocommerce-ajax-search' ),
				)
			);
			$free_options['info-to-show']['class']              = 'ywcas-info-to-show';
			$free_options['info-to-show']['data']['ywcas-deps'] = wp_json_encode(
				array(
					array(
						'id'    => 'ywcas-type-general_' . $slug,
						'value' => 'classic',
					),
				)
			);

			$info_overlay['info-to-show-overlay']                       = $free_options['info-to-show'];
			$info_overlay['info-to-show-overlay']  ['id']               = 'ywcas-info-to-show-overlay-search-results' . $key . '_' . $slug;
			$info_overlay['info-to-show-overlay']  ['options']          = array_merge(
				$info_to_show,
				array(
					'price'       => __( 'Price', 'yith-woocommerce-ajax-search' ),
					'add-to-cart' => __( 'Add to cart', 'yith-woocommerce-ajax-search' ),
				)
			);
			$info_overlay['info-to-show-overlay']['data']['ywcas-deps'] = wp_json_encode(
				array(
					array(
						'id'    => 'ywcas-type-general_' . $slug,
						'value' => 'overlay',
					),
				)
			);

			$info_overlay['columns'] = array(
				'id'      => 'ywcas-columns-' . $key . '_' . $slug,
				'type'    => 'number',
				'label'   => __( 'Columns to show', 'yith-woocommerce-ajax-search' ),
				'min'     => 1,
				'step'    => 1,
				'default' => 4,
				'desc'    => __( 'Set how many columns to show.', 'yith-woocommerce-ajax-search' ),
				'data'    => array(
					'ywcas-deps' => wp_json_encode(
						array(
							array(
								'id'    => 'ywcas-type-general_' . $slug,
								'value' => 'overlay',
							),
						)
					),
				),
			);

			$info_overlay['rows'] = array(
				'id'      => 'ywcas-rows-' . $key . '_' . $slug,
				'type'    => 'number',
				'label'   => __( 'Rows to show', 'yith-woocommerce-ajax-search' ),
				'min'     => 1,
				'step'    => 1,
				'default' => 3,
				'desc'    => __( 'Set how many rows to show.', 'yith-woocommerce-ajax-search' ),
				'data'    => array(
					'ywcas-deps' => wp_json_encode(
						array(
							array(
								'id'    => 'ywcas-type-general_' . $slug,
								'value' => 'overlay',
							),
						)
					),
				),
			);

			$free_options = array_merge( $info_overlay, $free_options );

			$free_options['results-layout']['type']    = 'select';
			$free_options['results-layout']['options'] = array(
				'grid' => __( 'Grid', 'yith-woocommerce-ajax-search' ),
				'list' => __( 'List', 'yith-woocommerce-ajax-search' ),
			);
			$free_options['results-layout']['class']   = 'wc-enhanced-select';

			$free_options['image-size']['data']['ywcas-deps'] =
				wp_json_encode(
					array(
						array(
							'id'    => 'ywcas-info-to-show-' . $key . '_' . $slug,
							'value' => 'image',
						),
						array(
							'id'    => 'ywcas-type-general_' . $slug,
							'value' => 'classic',
						),
					)
				);

			$free_options['image-position']['data'] ['ywcas-deps'] = wp_json_encode(
				array(
					array(
						'id'    => 'ywcas-results-layout-' . $key . '_' . $slug,
						'value' => 'list',
					),
					array(
						'id'    => 'ywcas-info-to-show-' . $key . '_' . $slug,
						'value' => 'image',
					),
					array(
						'id'    => 'ywcas-type-general_' . $slug,
						'value' => 'classic',
					),
				)
			);

			$new_options_1 = array(
				'price-label'       => array(
					'id'    => 'ywcas-price-label-' . $key . '_' . $slug,
					'label' => __( 'Price label', 'yith-woocommerce-ajax-search' ),
					'type'  => 'text',
					'desc'  => __( 'Leave empty if you want to show only the price without a label.', 'yith-woocommerce-ajax-search' ),
					'class' => 'ywcas-shortcode-field',
					'data'  => array(
						'ywcas-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-info-to-show-' . $key . '_' . $slug,
									'value' => 'price',
								),
								array(
									'id'    => 'ywcas-type-general_' . $slug,
									'value' => 'classic',
								),
							)
						),
					),
				),
				'set-summary-limit' => array(
					'id'    => 'ywcas-set-summary-limit-' . $key . '_' . $slug,
					'label' => __( 'Limit summary length', 'yith-woocommerce-ajax-search' ),
					'type'  => 'onoff',
					'desc'  => __( 'Enable to set a max number of words in the summary.', 'yith-woocommerce-ajax-search' ),
					'data'  => array(
						'ywcas-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-info-to-show-' . $key . '_' . $slug,
									'value' => 'excerpt',
								),
								array(
									'id'    => 'ywcas-type-general_' . $slug,
									'value' => 'classic',
								),
							)
						),
					),
				),
				'max-summary'       => array(
					'id'    => 'ywcas-max-summary-' . $key . '_' . $slug,
					'type'  => 'number',
					'label' => __( 'Max words', 'yith-woocommerce-ajax-search' ),
					'min'   => 1,
					'step'  => 1,
					'data'  => array(
						'ywcas-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-set-summary-limit-' . $key . '_' . $slug,
									'value' => 'yes',
								),
								array(
									'id'    => 'ywcas-info-to-show-' . $key . '_' . $slug,
									'value' => 'excerpt',
								),
								array(
									'id'    => 'ywcas-type-general_' . $slug,
									'value' => 'classic',
								),
							)
						),
					),
				),
			);
			$offset_1      = array_search( 'image-position', array_keys( $free_options ), true );
			$new_options_2 = array(
				'badges-to-show'                => array(
					'id'      => 'ywcas-badges-to-show-' . $key . '_' . $slug,
					'label'   => __( 'Badges to show', 'yith-woocommerce-ajax-search' ),
					'type'    => 'checkbox-array',
					'desc'    => _x( 'Choose the badges that you can show in products.', 'Admin option description', 'yith-woocommerce-ajax-search' ),
					'options' => array(
						'sale'         => __( 'On Sale', 'yith-woocommerce-ajax-search' ),
						'out-of-stock' => __( 'Out of stock', 'yith-woocommerce-ajax-search' ),
						'featured'     => __( 'Featured', 'yith-woocommerce-ajax-search' ),
					),
					'data'    => array(
						'ywcas-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-info-to-show-' . $key . '_' . $slug,
									'value' => 'image',
								),
								array(
									'id'    => 'ywcas-type-general_' . $slug,
									'value' => 'classic',
								),
							)
						),
					),
				),
				'show-hide-featured-if-on-sale' => array(
					'id'    => 'ywcas-hide-featured-if-on-sale-' . $key . '_' . $slug,
					'label' => _x( 'Hide "Featured" badge if the product is on sale', 'Admin option label', 'yith-woocommerce-ajax-search' ),
					'type'  => 'onoff',
					'desc'  => _x( 'Hide the "Featured" badge if the "On Sale" badge is visible.', 'Admin option description', 'yith-woocommerce-ajax-search' ),
					'data'  => array(
						'ywcas-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-info-to-show-' . $key . '_' . $slug,
									'value' => 'image',
								),
								array(
									'id'    => 'ywcas-badges-to-show-' . $key . '_' . $slug,
									'value' => 'featured',
								),
								array(
									'id'    => 'ywcas-type-general_' . $slug,
									'value' => 'classic',
								),
							)
						),
					),
				),
				'related-to-show'               => array(
					'id'      => 'ywcas-related-to-show-' . $key . '_' . $slug,
					'type'    => 'checkbox-array',
					'label'   => __( 'Show also results related to:', 'yith-woocommerce-ajax-search' ),
					'options' => array(
						'post' => __( 'Posts', 'yith-woocommerce-ajax-search' ),
						'page' => __( 'Pages', 'yith-woocommerce-ajax-search' ),
					),
					'desc'    => __( 'Choose if you want to extend the search to posts and pages.', 'yith-woocommerce-ajax-search' ),
				),
				'related-label'                 => array(
					'id'    => 'ywcas-related-label-' . $key . '_' . $slug,
					'label' => __( 'Related content label', 'yith-woocommerce-ajax-search' ),
					'type'  => 'text',
					'class' => 'ywcas-shortcode-field',
					'desc'  => __( 'Set the label to show before the related content.', 'yith-woocommerce-ajax-search' ),
					'data'  => array(
						'ywcas-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-related-to-show-' . $key . '_' . $slug,
									'value' => 'post,page',
								),
							)
						),
					),
				),
				'related-limit'                 => array(
					'id'    => 'ywcas-related-limit-' . $key . '_' . $slug,
					'type'  => 'number',
					'label' => __( 'Max related content to show', 'yith-woocommerce-ajax-search' ),
					'min'   => 1,
					'step'  => 1,
					'max'   => 10,
					'desc'  => __( 'Set how many related results (pages or posts) to show.', 'yith-woocommerce-ajax-search' ),
					'data'  => array(
						'ywcas-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-related-to-show-' . $key . '_' . $slug,
									'value' => 'post,page',
								),
							)
						),
					),
				),
			);

			$free_options = array_slice( $free_options, 0, $offset_1 + 1 ) + $new_options_1 + array_slice( $free_options, count( $new_options_1 ) - 1, null, true );

			$offset_2 = array_search( 'view-all-label', array_keys( $free_options ), true );

			return array_slice( $free_options, 0, $offset_2 + 1 ) + $new_options_2 + array_slice( $free_options, count( $new_options_2 ) - 1, null, true );

		}

		/**
		 * Add related content to results
		 *
		 * @param   array $content  Content.
		 *
		 * @return array
		 */
		public function show_related_content_post_type( $content ) {
			return array( 'post', 'page' );
		}

		/**
		 * Return synonymous list
		 *
		 * @return array
		 */
		public function get_synonymous() {
			return apply_filters( 'ywcas_synonymous', $this->get( 'synonymous', false ) );
		}

		/**
		 * Get the boost rule fields
		 *
		 * @return array[]
		 * @since 2.1.0
		 */
		public function get_boost_rule_panel_options() {
			return array(
				'name'              => array(
					'type'              => 'text',
					'label'             => __( 'Rule name', 'yith-woocommerce-ajax-search' ),
					'default'           => '',
					'desc'              => __( 'Enter a name to identify the rule', 'yith-woocommerce-ajax-search' ),
					'custom_attributes' => array(
						'autocomplete' => 'off',
					),
					'required'          => true,
				),
				'boost'             => array(
					'type'    => 'number',
					'label'   => __( 'Boost value', 'yith-woocommerce-ajax-search' ),
					'desc'    => __(
						'Enter a value (min 0.1 - max 50) to use as multiplier for search scores. 
A higher value will boost the result.',
						'yith-woocommerce-ajax-search'
					),
					'default' => 20,
					'min'     => 0.1,
					'step'    => 0.1,
					'max'     => 50,
				),
				'enable_for_terms'  => array(
					'type'    => 'onoff',
					'label'   => __( 'Enable for a specific keyword', 'yith-woocommerce-ajax-search' ),
					'desc'    => __( 'Enable to apply the rule if the user types a specific keyword or terms chain.', 'yith-woocommerce-ajax-search' ),
					'default' => 'no',
				),
				'check_term_type'   => array(
					'type'    => 'radio',
					'label'   => __( 'Keyword match', 'yith-woocommerce-ajax-search' ),
					'desc'    => sprintf(/* translators: %s is a html tag */
						__(
							'Exact: to apply the rule, the entered text must exactly match the search term below%s
Partial: the entered text can be different but it must contain the search term for the rule to be applied.',
							'yith-woocommerce-ajax-search'
						),
						'<br/>'
					),
					'options' => array(
						'exact'   => __( 'Exact', 'yith-woocommerce-ajax-search' ),
						'partial' => __( 'Partial', 'yith-woocommerce-ajax-search' ),
					),
					'default' => 'exact',
					'class'   => 'ywcas-toggle-button',
					'data'    => array(
						'ywcas-boost-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'enable_for_terms',
									'value' => 'yes',
								),
							)
						),
					),
				),
				'terms'             => array(
					'type'     => 'text',
					'label'    => __( 'Search terms', 'yith-woocommerce-ajax-search' ),
					'default'  => '',
					'desc'     => __( 'Use single words or phrases separated by commas.', 'yith-woocommerce-ajax-search' ),
					'data'     => array(
						'ywcas-boost-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'enable_for_terms',
									'value' => 'yes',
								),
							)
						),
					),
					'required' => true,
				),
				'conditions'        => array(
					'type'       => 'custom',
					'ywcas_type' => 'boost-conditions',
					'action'     => 'ywcas_show_custom_field',
					'label'      => __( 'Conditions', 'yith-woocommerce-ajax-search' ),
					'default'    => array(),
					'required'   => true,
				),
				'validation_method' => array(
					'type'    => 'radio',
					'label'   => __( 'Apply rule', 'yith-woocommerce-ajax-search' ),
					'options' => array(
						'and' => __( 'When results match all conditions', 'yith-woocommerce-ajax-search' ),
						'or'  => __( 'When results match any condition', 'yith-woocommerce-ajax-search' ),
					),
					'default' => 'and',
				),
			);
		}

		/**
		 * Return the condition fields
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function get_boost_condition_fields() {
			return apply_filters(
				'ywcas_get_boost_rule_condition_fields',
				array(
					'condition_config' => array(
						'type'    => 'inline-fields',
						'class'   => 'ywcas-condition-config',
						'fields'  => array(
							'condition_for'  => array(
								'type'    => 'select',
								'class'   => 'ywcas-condition-for',
								'options' => array(
									'product_cat'          => __( 'Product category', 'yith-woocommerce-ajax-search' ),
									'product_tag'          => __( 'Product tag', 'yith-woocommerce-ajax-search' ),
									'product_stock_status' => __( 'Stock status', 'yith-woocommerce-ajax-search' ),
									'product_price'        => __( 'Product price', 'yith-woocommerce-ajax-search' ),
								),
							),
							'condition_type' => array(
								'type'    => 'select',
								'class'   => 'ywcas-condition-type',
								'options' => array(
									'is'           => __( 'Is', 'yith-woocommerce-ajax-search' ),
									'is-not'       => __( 'Is not', 'yith-woocommerce-ajax-search' ),
									'in-range'     => __( 'In range', 'yith-woocommerce-ajax-search' ),
									'not-in-range' => __( 'Not in range', 'yith-woocommerce-ajax-search' ),
									'lower'        => __( 'Lower than', 'yith-woocommerce-ajax-search' ),
									'greater'      => __( 'Greater than', 'yith-woocommerce-ajax-search' ),
								),
								'default' => 'is',
								'data'    => array(
									'ywcas-valid-options-deps' => wp_json_encode(
										array(
											'product_cat'          => array( 'is', 'is-not' ),
											'product_tag'          => array( 'is', 'is-not' ),
											'product_stock_status' => array( 'is', 'is-not' ),
											'product_price'        => array(
												'in-range',
												'not-in-range',
												'lower',
												'greater',
											),
										)
									),
								),
							),
						),
						'default' => array(
							'condition_for'  => 'product_cat',
							'condition_type' => 'is',
						),
					),
					'product_cat'      => array(
						'type'     => 'ajax-terms',
						'data'     => array(
							'taxonomy'              => 'product_cat',
							'placeholder'           => __( 'Search for product categories', 'yith-woocommerce-ajax-search' ),
							'ywcas-conditions-deps' => wp_json_encode(
								array(
									array(
										'id'    => 'ywcas-condition-for',
										'value' => 'product_cat',
									),
								)
							),
						),
						'multiple' => true,
						'default'  => array(),
					),
					'product_tag'      => array(
						'type'     => 'ajax-terms',
						'data'     => array(
							'taxonomy'              => 'product_tag',
							'placeholder'           => __( 'Search for product tag', 'yith-woocommerce-ajax-search' ),
							'ywcas-conditions-deps' => wp_json_encode(
								array(
									array(
										'id'    => 'ywcas-condition-for',
										'value' => 'product_tag',
									),
								)
							),
						),
						'multiple' => true,
						'default'  => array(),
					),
					'stock_status'     => array(
						'type'    => 'select',
						'class'   => 'wc-enhanced-select',
						'options' => array(
							'instock'    => __( 'In Stock', 'yith-woocommerce-ajax-search' ),
							'outofstock' => __( 'Out of Stock', 'yith-woocommerce-ajax-search' ),
						),
						'default' => 'instock',
						'data'    => array(
							'ywcas-conditions-deps' => wp_json_encode(
								array(
									array(
										'id'    => 'ywcas-condition-for',
										'value' => 'product_stock_status',
									),
								)
							),
						),
					),
					'product_price'    => array(
						'type'    => 'inline-fields',
						'fields'  => array(
							'min_price' => array(
								'label' => __( 'Min. price', 'yith-woocommerce-ajax-search' ),
								'type'  => 'number',
								'min'   => 0,
								'step'  => 1 / pow( 10, wc_get_price_decimals() ),
							),
							'max_price' => array(
								'label' => __( 'Max. price', 'yith-woocommerce-ajax-search' ),
								'type'  => 'number',
								'class' => 'ywcas-max-price',
								'min'   => 0,
								'step'  => 1 / pow( 10, wc_get_price_decimals() ),
								'data'  => array(
									'ywcas-price-range-deps' => wp_json_encode(
										array(
											array(
												'id'    => 'ywcas-condition-type',
												'value' => 'in-range,not-in-range',
											),
										)
									),
								),
							),
						),
						'default' => array(
							'min_price' => 20,
							'max_price' => '',
						),
						'data'    => array(
							'ywcas-conditions-deps' => wp_json_encode(
								array(
									array(
										'id'    => 'ywcas-condition-for',
										'value' => 'product_price',
									),
								)
							),
						),
					),
				)
			);
		}

		/**
		 * Return the field that should be checked before save the shortcode
		 *
		 * @return array[]
		 */
		public function get_shortcode_fields_to_check() {
			return array(
				'search-results' => array(
					'badges-to-show'  => array(),
					'info-to-show'    => array(),
					'related-to-show' => array(),
				),
				'extra-options'  => array(
					'show-history'            => 'no',
					'show-popular'            => 'no',
					'show-related-categories' => 'no',
				),
			);
		}
	}

}
