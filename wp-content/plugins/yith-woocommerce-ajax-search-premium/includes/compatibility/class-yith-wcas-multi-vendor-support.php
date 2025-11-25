<?php
/**
 * YITH_WCAS_Multi_Vendor_Support class
 *
 * @since      2.0.0
 * @author     YITH
 * @package    YITH/Search
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Multi_Vendor_Support' ) ) {
	/**
	 * YITH WooCommerce Multi Vendor support class
	 *
	 * @since 2.0.0
	 */
	class YITH_WCAS_Multi_Vendor_Support {
		use YITH_WCAS_Trait_Singleton;

		/**
		 * The vendor list
		 *
		 * @var array
		 */
		protected $vendors_in_setting_list = array();

		/**
		 * Constructor
		 *
		 * @since 2.0.0
		 */
		protected function __construct() {
			add_action( 'wp_loaded', array( $this, 'check_retro_compatibility' ), 30 );
			add_filter( 'ywcas_additional_data_to_tokenizer', array( $this, 'add_vendors_to_tokenizer' ), 10, 3 );
			add_filter( 'yith_wcas_index_arguments', array( $this, 'add_vendors_to_index_arguments' ), 10, 2 );
			add_filter( 'ywcas_product_data_index_tax_query', array( $this, 'add_vendors_to_product_data_index_tax_query' ) );
			// Admin panel.
			add_filter( 'ywcas_search_fields_type', array( $this, 'add_vendors_to_search_field_type' ) );
			add_filter( 'ywcas_search_input_field_template_conditions', array( $this, 'add_vendors_template_conditions' ), 10, 2 );
			add_filter( 'ywcas_search_input_field_template_list', array( $this, 'add_custom_option_list' ), 10, 2 );
			add_filter( 'yith_wcas_search_fields_saved_option', array( $this, 'save_option' ), 10, 2 );

			add_filter( 'ywcas_index_custom_taxonomies', array( $this, 'index_taxonomy' ), 10, 2 );
			// Boost rule conditions.
			add_filter( 'ywcas_get_boost_rule_condition_fields', array( $this, 'add_boost_vendor_condition' ) );
			add_filter(
				'ywcas_get_condition_list_html',
				array(
					$this,
					'add_boost_vendor_condition_in_list_table',
				),
				10,
				3
			);
			add_filter( 'ywcas_boost_rule_condition_is_valid', array( $this, 'rule_condition_is_valid' ), 10, 4 );
		}

		/**
		 * Add vendors to the tax query to the product data index.
		 *
		 * @param array $tax_query Tax query.
		 *
		 * @return array
		 */
		public function add_vendors_to_product_data_index_tax_query( $tax_query ) {
			$product_vendors = ywcas()->settings->get_search_field_by_type( YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			if ( $product_vendors && 'all' !== $product_vendors['yith_vendors_condition'] ) {
				$tax_query[] = array(
					'taxonomy' => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
					'fields'   => 'term_id',
					'terms'    => $product_vendors['yith_vendors-list'],
					'operator' => 'include' === $product_vendors['yith_vendors_condition'] ? 'IN' : 'NOT IN',
				);
			}
			return $tax_query;
		}

		/**
		 * Add vendor taxonomy to tokenizer.
		 *
		 * @param   array   $additional_fields  Fields to tokenize.
		 * @param   WP_Post $data               Object to tokenize.
		 * @param   string  $lang               Current language.
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function add_vendors_to_tokenizer( $additional_fields, $data, $lang ) {

			$vendors = $this->maybe_add_vendor_to_index( $data, $lang );

			if ( ! empty( $vendors ) ) {
				$additional_fields[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] = $vendors;
			}

			return $additional_fields;
		}


		/**
		 * Return the list of enabled tags
		 *
		 * @param   string $lang  Current Language.
		 *
		 * @return array
		 */
		public function vendors_in_setting_list( $lang ) {
			if ( isset( $this->vendors_in_setting_list[ $lang ] ) ) {
				return $this->vendors_in_setting_list[ $lang ];
			}

			$this->vendors_in_setting_list[ $lang ] = array();
			$field_vendor                           = ywcas()->settings->get_search_field_by_type( YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			if ( 'all' !== $field_vendor['yith_vendors_condition'] && ! empty( $field_vendor['yith_vendors-list'] ) ) {
				$this->vendors_in_setting_list[ $lang ] = apply_filters( 'ywcas_wpml_add_multi_language_terms_list', $field_vendor['yith_vendors-list'], YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			}

			return $this->vendors_in_setting_list[ $lang ];
		}

		/**
		 * Find vendor to index
		 *
		 * @param   WP_Post $data  Formatted data of object.
		 * @param   string  $lang  Current language.
		 *
		 * @return string
		 * @since 2.0.0
		 */
		public function maybe_add_vendor_to_index( $data, $lang ) {

			$vendors       = '';
			$field_vendors = ywcas()->settings->get_search_field_by_type( YITH_Vendors_Taxonomy::TAXONOMY_NAME );
			if ( ! $field_vendors ) {
				return $vendors;
			}
			$field_vendors['yith_vendors-list'] = $this->vendors_in_setting_list( $lang );
			$terms                              = apply_filters( 'ywcas_wpml_get_translated_terms_list', get_the_terms( $data->ID, YITH_Vendors_Taxonomy::TAXONOMY_NAME ), YITH_Vendors_Taxonomy::TAXONOMY_NAME, $lang );

			if ( $terms ) {
				$enabled_vendors = array();
				foreach ( $terms as $term ) {
					if ( 'all' === $field_vendors['yith_vendors_condition'] ||
					     ( 'include' === $field_vendors['yith_vendors_condition'] && in_array( $term->term_id, $field_vendors['yith_vendors-list'] ) ) || ( 'exclude' === $field_vendors['yith_vendors_condition'] && ! in_array( $term->term_id, $field_vendors['yith_vendors-list'] ) ) //phpcs:ignore
					) {
						$enabled_vendors[] = $term;
					}
				}

				if ( ! empty( $enabled_vendors ) ) {
					$vendors = implode( ', ', wp_list_pluck( $enabled_vendors, 'name' ) );
				}
			}

			return $vendors;
		}

		/**
		 * Add vendor taxonomy inside the index list
		 *
		 * @param   array $list  List of index arguments.
		 *
		 * @return array
		 */
		public function add_vendors_to_index_arguments( $list ) {
			if ( ! in_array( YITH_Vendors_Taxonomy::TAXONOMY_NAME, $list, true ) ) {
				$list[] = YITH_Vendors_Taxonomy::TAXONOMY_NAME;
			}

			return $list;
		}

		/**
		 * Add vendors to the list of search field types
		 *
		 * @param   array $types  List of types.
		 *
		 * @return array
		 */
		public function add_vendor_to_search_field_type( $types ) {
			$types[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] = _x( 'Vendors', '[Admin]search field type', 'yith-woocommerce-ajax-search' );

			return $types;
		}


		/**
		 * Add vendors to the list of search field types
		 *
		 * @param   array $types  List of types.
		 *
		 * @return array
		 */
		public function add_vendors_to_search_field_type( $types ) {
			$types[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] = _x( 'Vendors', '[Admin]search field type', 'yith-woocommerce-ajax-search' );

			return $types;
		}

		/**
		 * Add the template conditions
		 *
		 * @param   int   $curr_id  Current element id.
		 * @param   array $field    Current field.
		 *
		 * @return void
		 */
		public function add_vendors_template_conditions( $curr_id, $field ) {
			?>
			<span class="search-field-type-condition" data-type="<?php echo esc_attr( YITH_Vendors_Taxonomy::TAXONOMY_NAME ); ?>">
			<?php
			yith_plugin_fw_get_field(
				array(
					'id'      => 'ywcas-search-field-yith_vendors-condition__' . $curr_id,
					'name'    => 'ywcas-search-fields[' . $curr_id . '][yith_vendors_condition]',
					'class'   => 'ywcas-search-condition yith_vendors-condition wc-enhanced-select',
					'type'    => 'select',
					'options' => array(
						'all'     => _x( 'Enable all vendors', '[admin]option to select', 'yith-woocommerce-ajax-search' ),
						'include' => _x( 'Enable specific vendors', '[admin]option to select', 'yith-woocommerce-ajax-search' ),
						'exclude' => _x( 'Disable specific vendors', '[admin]option to select', 'yith-woocommerce-ajax-search' ),
					),
					'value'   => $field['yith_vendors_condition'] ?? 'all',
				),
				true,
				false
			);
			?>
		</span>
			<?php
		}


		/**
		 * Add the list of vendors
		 *
		 * @param   int   $curr_id  Current element id.
		 * @param   array $field    Current field.
		 *
		 * @return void
		 */
		public function add_custom_option_list( $curr_id, $field ) {
			?>
			<span class="search-field-type-list search-field-type-yith_vendors-list" data-subtype="<?php echo esc_attr( YITH_Vendors_Taxonomy::TAXONOMY_NAME ); ?>">
			<?php

			yith_plugin_fw_get_field(
				array(
					'id'       => 'ywcas-search-field-yith_vendors-list_' . $curr_id,
					'name'     => 'ywcas-search-fields[' . $curr_id . '][yith_vendors-list]',
					'class'    => 'yith-term-search select-yith_vendors',
					'type'     => 'ajax-terms',
					'data'     => array(
						'taxonomy'    => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
						'placeholder' => __( 'Search for a vendor...', 'yith-woocommerce-ajax-search' ),
					),
					'multiple' => true,
					'value'    => $field['yith_vendors-list'] ?? array(),
				),
				true,
				false
			);
			?>
		</span>
			<?php
		}

		/**
		 * Save the option in case the type is vendors
		 *
		 * @param   array $options  Array of options.
		 * @param   array $field    Type of field.
		 *
		 * @return array
		 */
		public function save_option( $options, $field ) {
			if ( YITH_Vendors_Taxonomy::TAXONOMY_NAME === $field['type'] ) {
				$options[] = array(
					'type'                   => $field['type'],
					'priority'               => $field['priority'],
					'yith_vendors_condition' => $field['yith_vendors_condition'] ?? 'all',
					'yith_vendors-list'      => $field['yith_vendors-list'] ?? array(),
				);
			}

			return $options;
		}

		/**
		 * Check the old option to search for brands.
		 */
		public function check_retro_compatibility() {
			$old_option          = 'yith_wcas_search_in_vendor';
			$prev_version_option = get_option( $old_option );
			if ( 'yes' === $prev_version_option && ywcas()->settings->need_to_be_checked( $old_option ) ) {
				$new_option = (array) get_option( 'yith_wcas_search_fields', array() );
				if ( ! array_search( YITH_Vendors_Taxonomy::TAXONOMY_NAME, array_column( $new_option, 'type' ), true ) ) {
					$priority     = max( array_column( $new_option, 'priority' ) );
					$new_option[] = array(
						'type'                  => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
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
		 * Add vendor taxonomy on lookup table
		 *
		 * @param   array      $custom_taxonomies  Array of taxonomies.
		 * @param   WC_Product $product            Product.
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function index_taxonomy( $custom_taxonomies, $product ) {
			$parent     = $product->get_parent_id();
			$product_id = 0 === $parent ? $product->get_id() : $parent;
			$vendors    = wp_get_object_terms(
				$product_id,
				YITH_Vendors_Taxonomy::TAXONOMY_NAME,
				array(
					'fields'       => 'ids',
					'exclude_tree' => true,
				)
			);

			$custom_taxonomies[ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] = $vendors;

			return $custom_taxonomies;
		}

		/**
		 * Add the vendor condition type in the boost rule condition
		 *
		 * @param array $conditions The conditions config.
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function add_boost_vendor_condition( $conditions ) {
			$conditions['condition_config']['fields']['condition_for']['options']['product_vendor'] = __( 'Product Vendor', 'yith-woocommerce-ajax-search' );
			$data                   = json_decode( $conditions['condition_config']['fields']['condition_type']['data']['ywcas-valid-options-deps'], ARRAY_A );
			$data['product_vendor'] = array(
				'is',
				'is-not',
			);
			$conditions['condition_config']['fields']['condition_type']['data']['ywcas-valid-options-deps'] = wp_json_encode( $data );
			$vendor_condition = array(
				'product_vendor' => array(
					'type'     => 'ajax-terms',
					'data'     => array(
						'taxonomy'              => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
						'placeholder'           => __( 'Search for product vendor', 'yith-woocommerce-ajax-search' ),
						'ywcas-conditions-deps' => wp_json_encode(
							array(
								array(
									'id'    => 'ywcas-condition-for',
									'value' => 'product_vendor',
								),
							)
						),
					),
					'multiple' => true,
					'default'  => array(),
				),
			);

			return array_merge( $conditions, $vendor_condition );
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
		public function add_boost_vendor_condition_in_list_table( $li, $condition_for, $condition ) {
			if ( 'product_vendor' === $condition_for ) {
				$terms = get_terms(
					array(
						'taxonomy'   => YITH_Vendors_Taxonomy::TAXONOMY_NAME,
						'include'    => $condition[ $condition_for ],
						'fields'     => 'names',
						'hide_empty' => false,
					)
				);

				$li = array(
					'label'   => __( 'Product vendor', 'yith-woocommerce-ajax-search' ),
					'content' => implode( ',', $terms ),
				);
			}

			return $li;
		}

		/**
		 * Check if the condition is valid for vendor
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

			if ( 'product_vendor' === $condition_for ) {
				$vendors        = ! isset( $result['custom_taxonomies'][ YITH_Vendors_Taxonomy::TAXONOMY_NAME ] ) ? array() : $result['custom_taxonomies'][ YITH_WCBR::$brands_taxonomy ];
				$vendor_set     = array_map( 'intval', $condition['product_vendor'] );
				$condition_type = $condition['condition_config']['condition_type'];
				$check          = count( array_intersect( $vendor_set, $vendors ) ) > 0;
				$is_valid       = 'is' === $condition_type ? $check : ! $check;
			}

			return $is_valid;
		}
	}

}
