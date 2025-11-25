<?php
/**
 * YITH_WCAS_Brands_Add_On_Support class
 *
 * @since      2.0.0
 * @author     YITH
 * @package    YITH/Search
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Brands_Add_On_Support' ) ) {
	/**
	 * YITH WooCommerce Brands Add-On support class
	 *
	 * @since 2.0.0
	 */
	class YITH_WCAS_Brands_Add_On_Support {
		use YITH_WCAS_Trait_Singleton;

		/**
		 * The brand list
		 *
		 * @var array
		 */
		protected $brands_in_setting_list = array();

		/**
		 * Constructor
		 *
		 * @since 2.0.0
		 */
		protected function __construct() {
			add_action( 'wp_loaded', array( $this, 'check_retro_compatibility' ), 30 );
			add_filter( 'ywcas_additional_data_to_tokenizer', array( $this, 'add_brands_to_tokenizer' ), 10, 3 );
			add_filter( 'yith_wcas_index_arguments', array( $this, 'add_brands_to_index_arguments' ), 10, 2 );
			add_filter(
				'ywcas_product_data_index_tax_query',
				array(
					$this,
					'add_brands_to_product_data_index_tax_query',
				)
			);
			// Admin panel.
			add_filter( 'ywcas_search_fields_type', array( $this, 'add_brands_to_search_field_type' ) );
			add_filter(
				'ywcas_search_input_field_template_conditions',
				array(
					$this,
					'add_brands_template_conditions',
				),
				10,
				2
			);
			add_filter( 'ywcas_search_input_field_template_list', array( $this, 'add_custom_option_list' ), 10, 2 );
			add_filter( 'yith_wcas_search_fields_saved_option', array( $this, 'save_option' ), 10, 2 );
			add_filter( 'ywcas_index_custom_taxonomies', array( $this, 'index_taxonomy' ), 10, 2 );

			// Boost rule conditions.
			add_filter( 'ywcas_get_boost_rule_condition_fields', array( $this, 'add_boost_brand_condition' ) );
			add_filter(
				'ywcas_get_condition_list_html',
				array(
					$this,
					'add_boost_brand_condition_in_list_table',
				),
				10,
				3
			);
			add_filter( 'ywcas_boost_rule_condition_is_valid', array( $this, 'rule_condition_is_valid' ), 10, 4 );
		}

		/**
		 * Add brands to the tax query to the product data index.
		 *
		 * @param array $tax_query Tax query.
		 *
		 * @return array
		 */
		public function add_brands_to_product_data_index_tax_query( $tax_query ) {
			$product_brands = ywcas()->settings->get_search_field_by_type( YITH_WCBR::$brands_taxonomy );
			if ( $product_brands && 'all' !== $product_brands['yith_brands_condition'] ) {
				$tax_query[] = array(
					'taxonomy' => YITH_WCBR::$brands_taxonomy,
					'fields'   => 'term_id',
					'terms'    => $product_brands['yith_brands-list'],
					'operator' => 'include' === $product_brands['yith_brands_condition'] ? 'IN' : 'NOT IN',
				);
			}

			return $tax_query;
		}

		/**
		 * Add brands taxonomy to tokenizer.
		 *
		 * @param array   $additional_fields Fields to tokenize.
		 * @param WP_Post $data Object to tokenize.
		 * @param string  $lang Current language.
		 *
		 * @return array
		 *
		 * @since 2.0.0
		 */
		public function add_brands_to_tokenizer( $additional_fields, $data, $lang ) {

			$brands = $this->maybe_add_brands_to_index( $data, $lang );

			if ( ! empty( $brands ) ) {
				$additional_fields[ YITH_WCBR::$brands_taxonomy ] = $brands;
			}

			return $additional_fields;
		}

		/**
		 * Return the list of enabled tags
		 *
		 * @param string $lang Current languages.
		 *
		 * @return array
		 */
		public function get_brands_in_setting_list( $lang ) {
			if ( isset( $this->brands_in_setting_list[ $lang ] ) ) {
				return $this->brands_in_setting_list[ $lang ];
			}
			$this->brands_in_setting_list[ $lang ] = array();
			$field_brand                           = ywcas()->settings->get_search_field_by_type( YITH_WCBR::$brands_taxonomy );
			if ( 'all' !== $field_brand['yith_brands_condition'] && ! empty( $field_brand['yith_brands-list'] ) ) {
				$this->brands_in_setting_list[ $lang ] = apply_filters( 'ywcas_wpml_add_multi_language_terms_list', $field_brand['yith_brands-list'], YITH_WCBR::$brands_taxonomy );
			}

			return $this->brands_in_setting_list[ $lang ];
		}

		/**
		 * Find brand to index
		 *
		 * @param array  $data Formatted data of object.
		 * @param string $lang Current languages.
		 *
		 * @return string
		 * @since 2.0.0
		 */
		public function maybe_add_brands_to_index( $data, $lang ) {
			$brands       = '';
			$field_brands = ywcas()->settings->get_search_field_by_type( YITH_WCBR::$brands_taxonomy );
			if ( ! $field_brands ) {
				return $brands;
			}
			$field_brands['yith_brands-list'] = $this->get_brands_in_setting_list( $lang );
			$terms                            = apply_filters( 'ywcas_wpml_get_translated_terms_list', get_the_terms( $data->ID, YITH_WCBR::$brands_taxonomy ), YITH_WCBR::$brands_taxonomy, $lang );
			if ( $terms ) {
				$enabled_brands = array();
				foreach ( $terms as $term ) {
					if ( 'all' === $field_brands['yith_brands_condition'] ||
					     ( 'include' === $field_brands['yith_brands_condition'] && in_array( $term->term_id, $field_brands['yith_brands-list'] ) ) || ( 'exclude' === $field_brands['yith_brands_condition'] && ! in_array( $term->term_id, $field_brands['yith_brands-list'] ) ) //phpcs:ignore
					) {
						$enabled_brands[] = $term;
					}
				}

				if ( ! empty( $enabled_brands ) ) {
					$brands = implode( ', ', wp_list_pluck( $enabled_brands, 'name' ) );
				}
			}

			return $brands;
		}

		/**
		 * Add brand taxonomy inside the index list
		 *
		 * @param array $list List of index arguments.
		 *
		 * @return array
		 */
		public function add_brands_to_index_arguments( $list ) {
			if ( ! in_array( YITH_WCBR::$brands_taxonomy, $list, true ) ) {
				$list[] = YITH_WCBR::$brands_taxonomy;
			}

			return $list;
		}

		/**
		 * Add brands to the list of search field types
		 *
		 * @param array $types List of types.
		 *
		 * @return array
		 */
		public function add_brands_to_search_field_type( $types ) {
			$types[ YITH_WCBR::$brands_taxonomy ] = _x( 'Brands', '[Admin]search field type', 'yith-woocommerce-ajax-search' );

			return $types;
		}

		/**
		 * Add the template conditions
		 *
		 * @param int   $curr_id Current element id.
		 * @param array $field Current field.
		 *
		 * @return void
		 */
		public function add_brands_template_conditions( $curr_id, $field ) {
			?>
			<span class="search-field-type-condition" data-type="<?php echo esc_attr( YITH_WCBR::$brands_taxonomy ); ?>">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'ywcas-search-field-yith_brands-condition__' . $curr_id,
					'name'    => 'ywcas-search-fields[' . $curr_id . '][yith_brands_condition]',
					'class'   => 'ywcas-search-condition yith_brands-condition wc-enhanced-select',
					'type'    => 'select',
					'options' => array(
						'all'     => _x( 'Enable all brands', '[admin]option to select', 'yith-woocommerce-ajax-search' ),
						'include' => _x( 'Enable specific brands', '[admin]option to select', 'yith-woocommerce-ajax-search' ),
						'exclude' => _x( 'Disable specific brands', '[admin]option to select', 'yith-woocommerce-ajax-search' ),
					),
					'value'   => $field['yith_brands_condition'] ?? 'all',
				),
				true,
				false
			);
			?>
		</span>
			<?php
		}


		/**
		 * Add the list of brands
		 *
		 * @param int   $curr_id Current element id.
		 * @param array $field Current field.
		 *
		 * @return void
		 */
		public function add_custom_option_list( $curr_id, $field ) {
			?>
			<span class="search-field-type-list search-field-type-yith_brands-list" data-subtype="<?php echo esc_attr( YITH_WCBR::$brands_taxonomy ); ?>">
			<?php

			yith_plugin_fw_get_field(
				array(
					'id'       => 'ywcas-search-field-yith_brands-list_' . $curr_id,
					'name'     => 'ywcas-search-fields[' . $curr_id . '][yith_brands-list]',
					'class'    => 'yith-term-search select-yith_brands',
					'type'     => 'ajax-terms',
					'data'     => array(
						'taxonomy'    => YITH_WCBR::$brands_taxonomy,
						'placeholder' => __( 'Search for a brand...', 'yith-woocommerce-ajax-search' ),
					),
					'multiple' => true,
					'value'    => $field['yith_brands-list'] ?? array(),
				),
				true,
				false
			);
			?>
		</span>
			<?php
		}

		/**
		 * Save the option in case the type is brand
		 *
		 * @param array $options Array of options.
		 * @param array $field Type of field.
		 *
		 * @return array
		 */
		public function save_option( $options, $field ) {
			if ( YITH_WCBR::$brands_taxonomy === $field['type'] ) {
				$options[] = array(
					'type'                  => $field['type'],
					'priority'              => $field['priority'],
					'yith_brands_condition' => $field['yith_brands_condition'] ?? 'all',
					'yith_brands-list'      => $field['yith_brands-list'] ?? array(),
				);
			}

			return $options;
		}

		/**
		 * Check the old option to search for brands.
		 */
		public function check_retro_compatibility() {
			$old_option          = 'yith_wcas_search_in_product_brands';
			$prev_version_option = get_option( $old_option );
			if ( 'yes' === $prev_version_option && ywcas()->settings->need_to_be_checked( $old_option ) ) {
				$new_option = (array) get_option( 'yith_wcas_search_fields', array() );
				if ( ! array_search( YITH_WCBR::$brands_taxonomy, array_column( $new_option, 'type' ), true ) ) {
					$priority     = max( array_column( $new_option, 'priority' ) );
					$new_option[] = array(
						'type'                  => YITH_WCBR::$brands_taxonomy,
						'priority'              => $priority + 1,
						'yith_brands_condition' => 'all',
						'yith_brands-list'      => array(),
					);
					update_option( 'yith_wcas_search_fields', $new_option );
				}

				ywcas()->settings->add_check_on_old_settings( $old_option );
			}
		}

		/**
		 * Add brands taxonomy on lookup table
		 *
		 * @param array      $custom_taxonomies Array of taxonomies.
		 * @param WC_Product $product Product.
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function index_taxonomy( $custom_taxonomies, $product ) {
			$parent     = $product->get_parent_id();
			$product_id = 0 === $parent ? $product->get_id() : $parent;
			$brands     = wp_get_object_terms(
				$product_id,
				YITH_WCBR::$brands_taxonomy,
				array(
					'fields'       => 'ids',
					'exclude_tree' => true,
				)
			);

			if ( ! empty( $brands ) ) {
				$custom_taxonomies[ YITH_WCBR::$brands_taxonomy ] = $brands;
			}

			return $custom_taxonomies;
		}

		/**
		 * Add the brand condition type in the boost rule condition
		 *
		 * @param array $conditions The conditions config.
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function add_boost_brand_condition( $conditions ) {
			$conditions['condition_config']['fields']['condition_for']['options']['product_brand'] = __( 'Product Brand', 'yith-woocommerce-ajax-search' );
			$data                  = json_decode( $conditions['condition_config']['fields']['condition_type']['data']['ywcas-valid-options-deps'], ARRAY_A );
			$data['product_brand'] = array(
				'is',
				'is-not',
			);
			$conditions['condition_config']['fields']['condition_type']['data']['ywcas-valid-options-deps'] = wp_json_encode( $data );
			$brand_condition = array(
				'product_brand' => array(
					'type'     => 'ajax-terms',
					'data'     => array(
						'taxonomy'              => YITH_WCBR::$brands_taxonomy,
						'placeholder'           => __( 'Search for product brands', 'yith-woocommerce-ajax-search' ),
						'ywcas-conditions-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-condition-for',
									'value' => 'product_brand',
								),
							)
						),
					),
					'multiple' => true,
					'default'  => array(),
				),
			);

			return array_merge( $conditions, $brand_condition );
		}

		/**
		 * Show the specific configuration in the list table
		 *
		 * @param array  $li The element to show.
		 * @param string $condition_for The condition type.
		 * @param array  $condition The condition.
		 *
		 * @return array
		 */
		public function add_boost_brand_condition_in_list_table( $li, $condition_for, $condition ) {
			if ( 'product_brand' === $condition_for ) {
				$terms = get_terms(
					array(
						'taxonomy'   => YITH_WCBR::$brands_taxonomy,
						'include'    => $condition[ $condition_for ],
						'fields'     => 'names',
						'hide_empty' => false,
					)
				);

				$li = array(
					'label'   => __( 'Product brand', 'yith-woocommerce-ajax-search' ),
					'content' => implode( ',', $terms ),
				);
			}

			return $li;
		}

		/**
		 * Check if the condition is valid for brand
		 *
		 * @param bool            $is_valid Is valid or not.
		 * @param array           $condition The condition.
		 * @param array           $result The result set.
		 * @param YITH_WCAS_Boost $rule The rule.
		 *
		 * @return bool
		 */
		public function rule_condition_is_valid( $is_valid, $condition, $result, $rule ) {
			$condition_for = $condition['condition_config']['condition_for'];

			if ( 'product_brand' === $condition_for ) {
				$brands         = ! isset( $result['custom_taxonomies'][ YITH_WCBR::$brands_taxonomy ] ) ? array() : $result['custom_taxonomies'][ YITH_WCBR::$brands_taxonomy ];
				$brand_set      = array_map( 'intval', $condition['product_brand'] );
				$condition_type = $condition['condition_config']['condition_type'];
				$check          = count( array_intersect( $brand_set, $brands ) ) > 0;
				$is_valid       = 'is' === $condition_type ? $check : ! $check;
			}

			return $is_valid;
		}
	}

}
