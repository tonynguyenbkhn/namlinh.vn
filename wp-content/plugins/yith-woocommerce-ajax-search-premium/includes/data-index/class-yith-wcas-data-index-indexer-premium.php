<?php
/**
 * Indexer class
 *
 * @author  YITH
 * @package YITH/Search/DataIndex
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;
if ( class_exists( 'YITH_WCAS_Data_Index_Indexer' ) && ! class_exists( 'YITH_WCAS_Data_Index_Indexer_Premium' ) ) {
	/**
	 * Store the data to database
	 *
	 * @since 2.0.0
	 */
	class YITH_WCAS_Data_Index_Indexer_Premium extends YITH_WCAS_Data_Index_Indexer {



		/**
		 * The tag list
		 *
		 * @var array
		 */
		protected $tags_in_setting_list = array();

		/**
		 * The categories list
		 *
		 * @var array
		 */
		protected $categories_in_setting_list = array();

		/**
		 * The attributes list
		 *
		 * @var array
		 */
		protected $attributes_in_setting_list = array();
		/**
		 * Constuctor
		 */
		public function __construct() {
			parent::__construct();
			add_action( 'yith_wcas_data_index_taxonomy', array( $this, 'add_scheduled_taxonomy' ), 10, 2 );

		}


		/**
		 * Start to fill the data table.
		 *
		 * @return string
		 */
		public function process_data() {
			$process_id = uniqid( 'WCAS' );
			$this->process_posts( $process_id );
			$this->process_product_categories( $process_id );

			return $process_id;
		}

		/**
		 * Init the process truncate the index table and un-schedule current process
		 *
		 * @param   string  $process_id  Current process id.
		 * @param   array   $data        Data to index.
		 * @param   boolean $taxonomy    Processing taxonomies.
		 *
		 * @return void
		 * @since 2.0.0
		 */
		protected function init_process( $process_id, $data, $taxonomy = false ) {
			$num_of_items = count( $data );
			update_option( 'ywcas_last_index_process', $process_id );
			if ( $taxonomy ) {
				$this->logger->log( 'Init Taxonomies index process. Found ' . $num_of_items . ' terms', 'data-index' );
				YITH_WCAS_Data_Index_Taxonomy::get_instance()->clear_table();
			} else {
				$this->logger->log( 'Init Data index process. Found ' . $num_of_items . ' objects', 'data-index' );
				YITH_WCAS_Data_Index_Lookup::get_instance()->clear_table();
				YITH_WCAS_Data_Index_Token::get_instance()->clear_table();
				YITH_WCAS_Data_Index_Relationship::get_instance()->clear_table();
				YITH_WCAS_Data_Index_Scheduler::get_instance()->unschedule( 'yith_wcas_data_index_lookup' );
			}

			$this->set_process_transient( $process_id, $num_of_items, $taxonomy );
		}

		/**
		 * Init the process truncate the index table and un-schedule current process
		 *
		 * @param   string $process_id  Current process id.
		 * @param   string $taxonomy  Taxonomy.
		 *
		 * @return void
		 * @since 2.0.0
		 */
		protected function complete_process( $process_id, $taxonomy = false ) {
			$this->logger->log( 'Completed indexing with process id ' . $process_id . ' objects', 'data-index' );
			if ( $taxonomy ) {
				YITH_WCAS_Data_Index_Taxonomy::get_instance()->index_table();
			} else {
				YITH_WCAS_Data_Index_Lookup::get_instance()->index_table();
				YITH_WCAS_Data_Index_Token::get_instance()->index_table();
				YITH_WCAS_Data_Index_Relationship::get_instance()->index_table();
			}
		}



		/**
		 * Add the taxonomy
		 *
		 * @param   string $chunk  Current chunk.
		 *
		 * @return void
		 */
		public function add_scheduled_taxonomy( $chunk ) {
			$items = get_transient( $chunk );
			if ( $items ) {
				foreach ( $items as $item ) {
					$term = get_term( $item );
					if ( $term ) {
						$this->add_taxonomy( $term );
					}
				};
				$this->update_process_transient( $chunk, count( $items ), true );
				delete_transient( $chunk );
			}
		}

		/**
		 * Start to schedule index process for products, post or pages.
		 *
		 * @param   int $process_id  Process id.
		 *
		 * @return void
		 */
		public function process_posts( $process_id ) {
			$data_query = new YITH_WCAS_Data_Index_Query( array( 'product', 'post' ) );
			$data       = $data_query->get_data();
			$chunk      = 1;

			if ( $data ) {
				$this->init_process( $process_id, $data );

				$data_to_process = array();
				foreach ( $data as $object_id ) {
					$data_to_process[] = $object_id;
					if ( count( $data_to_process ) === $this->limit ) {
						$this->schedule( $process_id, $chunk ++, $data_to_process );
						$data_to_process = array();
					}
				}

				if ( ! empty( $data_to_process ) ) {
					$this->schedule( $process_id, $chunk, $data_to_process );
				}
			}
		}

		/**
		 * Start to schedule index process for products, post or pages.
		 *
		 * @param   int $process_id  Process id.
		 *
		 * @return void
		 */
		public function process_product_categories( $process_id ) {

			$field = ywcas()->settings->get_search_field_by_type( 'product_categories' );

			$args = array(
				'taxonomy'        => 'product_cat',
				'fields'          => 'ids',
				'suppress_filter' => true,
			);

			if ( $field && isset( $field['product_category_condition'] ) && 'all' !== $field['product_category_condition'] ) {
				$condition          = 'include' === $field['product_category_condition'] ? 'include' : 'exclude';
				$args[ $condition ] = apply_filters( 'ywcas_wpml_add_multi_language_terms_list', $field['category-list'], 'product_cat' );
			}

			$terms = get_terms( $args );

			if ( $terms ) {
				$this->init_process( $process_id, $terms, true );
				$chunk           = 1;
				$data_to_process = array();
				foreach ( $terms as $term_id ) {
					$data_to_process[] = $term_id;
					if ( count( $data_to_process ) === $this->limit ) {
						$this->schedule_taxonomy( $process_id, $chunk ++, $data_to_process );
						$data_to_process = array();
					}
				}

				if ( ! empty( $data_to_process ) ) {
					$this->schedule_taxonomy( $process_id, $chunk, $data_to_process );
				}
			}
		}


		/**
		 * Schedule index of taxonomies
		 *
		 * @param   string $process_id       Current process id.
		 * @param   int    $chunk            Current chunk.
		 * @param   array  $data_to_process  List of elements to process.
		 */
		public function schedule_taxonomy( $process_id, $chunk, $data_to_process ) {
			$transient_name = "yith_wcas_taxonomy_index_{$process_id}_{$chunk}";
			if ( ! get_transient( $transient_name ) ) {
				set_transient( $transient_name, $data_to_process, DAY_IN_SECONDS * 7 );
				YITH_WCAS_Data_Index_Scheduler::get_instance()->schedule( 'yith_wcas_data_index_taxonomy', $transient_name, 'data_index_taxonomy' );
			}
		}

		/**
		 * Return the formatted data to insert on table
		 *
		 * @param   Object $data  Data.
		 *
		 * @return array|boolean
		 */
		protected function get_formatted_data( $data ) {
			$import_data = false;
			if ( in_array( $data->post_type, array( 'product', 'product_variation' ), true ) ) {
				$import_data = $this->get_formatted_product( $data );

			} elseif ( in_array( $data->post_type, array( 'post', 'page' ), true ) ) {
				$import_data = $this->get_formatted_post( $data );

			} else {
				$import_data = apply_filters( 'ywcas_data_index_formatted_data', $import_data, $data );
			}

			return $import_data;
		}



		/**
		 * Return custom fields to tokenize
		 *
		 * @param   int $post_id  Post id.
		 *
		 * @return string
		 */
		public function get_post_custom_fields( $post_id ) {
			$selected_custom_fields = ywcas()->settings->get_search_field_by_type( 'custom_fields' );
			$custom_field_values    = array();
			if ( ! empty( $selected_custom_fields['custom_field_list'] ) ) {
				foreach ( $selected_custom_fields['custom_field_list'] as $custom_field_name ) {
					$custom_field_values[] = get_post_meta( $post_id, $custom_field_name, true );
				}
			}

			return implode( ',', array_filter( $custom_field_values ) );
		}


		/**
		 * Return the product to insert on table
		 *
		 * @param   WP_Post $post  Data content.
		 *
		 * @return array
		 */
		protected function get_formatted_post( $post ) {
			$formatted_post = array(
				'post_id'         => $post->ID,
				'name'            => $post->post_title,
				'description'     => $post->post_content,
				'summary'         => $post->post_excerpt,
				'url'             => get_permalink( $post ),
				'sku'             => '',
				'thumbnail'       => get_the_post_thumbnail( $post ),
				'min_price'       => '',
				'max_price'       => '',
				'onsale'          => '',
				'instock'         => '',
				'stock_quantity'  => '',
				'is_purchasable'  => 0,
				'rating_count'    => '',
				'average_rating'  => '',
				'total_sales'     => '',
				'post_type'       => $post->post_type,
				'post_parent'     => $post->post_parent,
				'product_type'    => '',
				'parent_category' => '',
				'tags'            => '',
				'custom_fields'   => $this->get_post_custom_fields( $post->ID ),
				'lang'            => ywcas_get_language( $post->ID ),
				'featured'        => is_sticky( $post->ID ),
			);

			return apply_filters( 'yith_wcas_data_index_loockup_formatted_post', $formatted_post, $post );
		}


		/**
		 * Add the taxonomy on database
		 *
		 * @param   WP_Term $data  Data to index.
		 *
		 * @return void
		 */
		public function add_taxonomy( $data ) {
			if ( $data ) {
				$data = $this->get_formatted_taxonomy( $data );

				YITH_WCAS_Data_Index_Taxonomy::get_instance()->insert( $data );
			}
		}

		/**
		 * Return the formatted data to insert on table
		 *
		 * @param   WP_Term $taxonomy  Taxonomy.
		 *
		 * @return array|boolean
		 */
		protected function get_formatted_taxonomy( $taxonomy ) {
			$import_data = false;
			if ( $taxonomy ) {
				$import_data = array(
					'term_id'   => $taxonomy->term_id,
					'term_name' => $taxonomy->name,
					'taxonomy'  => $taxonomy->taxonomy,
					'url'       => get_term_link( $taxonomy->term_id ),
					'parent'    => $taxonomy->parent,
					'count'     => $taxonomy->count,
					'lang'      => ywcas_get_taxonomy_language( $taxonomy->term_id, $taxonomy->taxonomy ),
				);
			}

			return $import_data;
		}


		/**
		 * Get additional data to tokenize
		 *
		 * @param   WP_Post $data  Data to index.
		 * @param   string  $lang  Current language.
		 *
		 * @return array|boolean
		 */
		public function get_additional_data_to_tokenize( $data, $lang ) {
			$additional_fields = array();

			if ( ! in_array( $data->post_type, array( 'product', 'product_variation' ), true ) ) {
				return $additional_fields;
			}
			// Check if indexing of categories are required.
			$product_categories = $this->maybe_add_product_categories_to_index( $data, $lang );

			if ( ! empty( $product_categories ) ) {
				$additional_fields['product_categories'] = $product_categories;
			}

			// Check if indexing of tag are required.
			$product_tags = $this->maybe_add_product_tags_to_index( $data, $lang );

			if ( ! empty( $product_tags ) ) {
				$additional_fields['product_tags'] = $product_tags;
			}

			$product_attributes = $this->maybe_add_product_attributes_to_index( $data, $lang );
			if ( ! empty( $product_attributes ) ) {
				$additional_fields['product_attributes'] = $product_attributes;
			}

			return apply_filters( 'ywcas_additional_data_to_tokenizer', $additional_fields, $data, $lang );
		}

		/**
		 * Return the list of enabled categories
		 *
		 * @param   string $lang  Current Language.
		 *
		 * @return array
		 */
		public function get_categories_in_setting_list( $lang ) {
			if ( isset( $this->categories_in_setting_list[ $lang ] ) ) {
				return $this->categories_in_setting_list[ $lang ];
			}
			$this->categories_in_setting_list[ $lang ] = array();
			$field_category                            = ywcas()->settings->get_search_field_by_type( 'product_categories' );
			if ( 'all' !== $field_category['product_category_condition'] && ! empty( $field_category['category-list'] ) ) {
				$this->categories_in_setting_list[ $lang ] = apply_filters( 'ywcas_wpml_add_multi_language_terms_list', $field_category['category-list'], 'product_cat' );
			}

			return $this->categories_in_setting_list[ $lang ];
		}

		/**
		 * Return the list of tag to index for a product
		 *
		 * @param   WP_Post $data  Data.
		 * @param   string  $lang  Current languages.
		 *
		 * @return string
		 */
		public function maybe_add_product_categories_to_index( $data, $lang ) {
			$product_category = '';
			$field_category   = ywcas()->settings->get_search_field_by_type( 'product_categories' );

			if ( ! $field_category ) {
				return $product_category;
			}
			// filter the enabled categories for multi-language.
			$field_category['category-list'] = $this->get_categories_in_setting_list( $lang );

			$terms = apply_filters( 'ywcas_wpml_get_translated_terms_list', get_the_terms( $data->ID, 'product_cat' ), 'product_cat', $lang );
			if ( $terms ) {
				$enabled_categories = array();
				foreach ( $terms as $term ) {
					if ( 'all' === $field_category['product_category_condition'] ||
					     ( 'include' === $field_category['product_category_condition'] && in_array( $term->term_id, $field_category['category-list'] ) ) || ( 'exclude' === $field_category['product_category_condition'] && ! in_array( $term->term_id, $field_category['category-list'] ) ) //phpcs:ignore
					) {
						$enabled_categories[] = $term;
					}
				}

				if ( ! empty( $enabled_categories ) ) {
					$product_category = implode( ', ', wp_list_pluck( $enabled_categories, 'name' ) );
				}
			}

			return $product_category;
		}

		/**
		 * Return the list of enabled tags
		 *
		 * @param   string $lang  Current Language.
		 *
		 * @return array
		 */
		public function get_tags_in_setting_list( $lang ) {
			if ( isset( $this->tags_in_setting_list[ $lang ] ) ) {
				return $this->tags_in_setting_list[ $lang ];
			}
			$this->tags_in_setting_list[ $lang ] = array();
			$field_tag                           = ywcas()->settings->get_search_field_by_type( 'product_tags' );
			if ( 'all' !== $field_tag['product_tag_condition'] && ! empty( $field_tag['tag-list'] ) ) {
				$this->tags_in_setting_list[ $lang ] = apply_filters( 'ywcas_wpml_add_multi_language_terms_list', $field_tag['tag-list'], 'product_tag' );
			}

			return $this->tags_in_setting_list[ $lang ];
		}

		/**
		 * Return the list of tag to index for a product
		 *
		 * @param   WP_Post $data  Data.
		 * @param   string  $lang  Language.
		 *
		 * @return string
		 */
		public function maybe_add_product_tags_to_index( $data, $lang ) {
			$product_tag = '';
			$field_tag   = ywcas()->settings->get_search_field_by_type( 'product_tags' );
			if ( ! $field_tag ) {
				return $product_tag;
			}

			// filter the enabled tags for multi-language.
			$field_tag['tag-list'] = $this->get_tags_in_setting_list( $lang );
			$terms                 = apply_filters( 'ywcas_wpml_get_translated_terms_list', get_the_terms( $data->ID, 'product_tag' ), 'product_tag', $lang );
			if ( $terms ) {
				$enabled_tags = array();
				foreach ( $terms as $term ) {
					if ( 'all' === $field_tag['product_tag_condition'] ||
					     ( 'include' === $field_tag['product_tag_condition'] && in_array( $term->term_id, $field_tag['tag-list'] ) ) || ( 'exclude' === $field_tag['product_tag_condition'] && ! in_array( $term->term_id, $field_tag['tag-list'] ) ) //phpcs:ignore
					) {
						$enabled_tags[] = $term;
					}
				}

				if ( ! empty( $enabled_tags ) ) {
					$product_tag = implode( ', ', wp_list_pluck( $enabled_tags, 'name' ) );
				}
			}

			return $product_tag;
		}


		/**
		 * Return the list of enabled tags
		 *
		 * @param   string $lang  Current Language.
		 *
		 * @return array
		 */
		public function get_attributes_in_setting_list( $lang ) {
			if ( isset( $this->attributes_in_setting_list[ $lang ] ) ) {
				return $this->attributes_in_setting_list[ $lang ];
			}
			$this->attributes_in_setting_list[ $lang ] = array();
			$field                                     = ywcas()->settings->get_search_field_by_type( 'product_attributes' );
			if ( ! empty( $field['product_attribute_list'] ) ) {
				$this->attributes_in_setting_list[ $lang ] = $field['product_attribute_list'];
			}

			return $this->attributes_in_setting_list[ $lang ];
		}


		/**
		 * Add attributes to the tokens
		 *
		 * @param   WP_Post $data  Data.
		 * @param   string  $lang  Language.
		 *
		 * @return string
		 */
		public function maybe_add_product_attributes_to_index( $data, $lang ) {
			$attributes = array();
			$product    = wc_get_product( $data->ID );
			$field      = ywcas()->settings->get_search_field_by_type( 'product_attributes' );
			if ( ! $field ) {
				return '';
			}
			$attribute_list     = $this->get_attributes_in_setting_list( $lang );
			$product_attributes = $product->get_attributes();

			if ( $product_attributes ) {
				foreach ( $product_attributes as $key => $attribute ) {

					if ( in_array( $key, $attribute_list, true ) || in_array( wc_variation_attribute_name( $key ), $attribute_list, true ) ) {
						if ( $product->get_type() === 'variation' ) {
							$attributes[] = $attribute;
						} elseif ( $attribute instanceof WC_Product_Attribute ) {

							if ( $attribute->is_taxonomy() ) {
								$attribute_taxonomy = $attribute->get_taxonomy_object();
								$attribute_values   = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );

								foreach ( $attribute_values as $attribute_value ) {
									$attributes[] = $attribute_value->name;
								}
							} else {
								$values = $attribute->get_options();
								foreach ( $values as &$value ) {
									$attributes[] = $value;
								}
							}
						} else {
							$attributes[] = $attribute;
						}
					}
				}
			}

			return implode( ', ', array_unique( $attributes ) );

		}

	}
}
