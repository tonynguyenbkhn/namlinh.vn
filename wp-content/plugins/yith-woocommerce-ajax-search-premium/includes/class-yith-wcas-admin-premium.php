<?php
/**
 * Admin Premium class
 *
 * @author  YITH
 * @package YITH/Search
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Admin_Premium' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 2.0.0
	 */
	class YITH_WCAS_Admin_Premium extends YITH_WCAS_Admin {
		/**
		 * Constructor
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __construct() {
			parent::__construct();

			add_action( 'ywcas_dashboard_tab', array( $this, 'dashboard_tab' ), 10, 2 );

			add_action( 'wp_ajax_yith_plugin_search_custom_fields', array( $this, 'search_custom_fields' ) );
			add_action( 'wp_ajax_yith_plugin_search_product_attributes', array( $this, 'search_product_attributes' ) );

			add_filter( 'ywcas_custom_option_types', array( $this, 'add_new_custom_types' ) );
			// Add tab.
			add_filter( 'ywcas_admin_tabs', array( $this, 'add_premium_tab' ) );
			add_filter( 'ywcas_register_panel_arguments', array( $this, 'add_store_tools_tab' ) );
			// Add option in the tabs.
			add_filter( 'ywcas_general_options_tab', array( $this, 'add_premium_option_in_general_tab' ) );
			add_filter( 'ywcas_search_fields_type', array( $this, 'add_premium_search_field_type' ) );
			add_filter(
				'yith_wcas_search_fields_saved_option',
				array(
					$this,
					'save_premium_search_field_option',
				),
				10,
				2
			);
			// Create ,delete and clone the shortcode.
			add_action( 'wp_ajax_yith_wcas_add_new_shortcode', array( $this, 'add_new_shortcode' ) );
			add_action( 'wp_ajax_yith_wcas_delete_shortcode', array( $this, 'delete_shortcode' ) );
			add_action( 'wp_ajax_yith_wcas_clone_shortcode', array( $this, 'clone_shortcode' ) );

			// Privacy.
			add_action( 'plugins_loaded', array( $this, 'load_privacy_dpa' ), 20 );

			// Add action for register and update plugin.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

		}

		/**
		 * Dashboard tab
		 *
		 * @param array $options Options.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function dashboard_tab( $options ) {
			// close the wrap div and open the Rood div.
			echo '</div><!-- /.wrap -->';
			echo "<div class='woocommerce-page' >";

			if ( file_exists( YITH_WCAS_INC . '/admin/views/panel/dashboard.php' ) ) {
				include YITH_WCAS_INC . '/admin/views/panel/dashboard.php';
			}
		}


		/**
		 * Return the product name
		 */
		protected function get_product_name() {
			return 'YITH WooCommerce Ajax Search Premium';
		}

		/**
		 * Return the custom fields requested inside the admin panel option.
		 *
		 * @return void
		 */
		public function search_custom_fields() {
			check_ajax_referer( 'search-terms', 'security' );

			$term = isset( $_REQUEST['term'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : false;

			if ( empty( $term ) ) {
				die();
			}

			global $wpdb;

			$terms         = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT meta_key
                FROM $wpdb->postmeta as meta
                INNER JOIN $wpdb->posts as posts ON posts.ID = meta.post_id
                WHERE posts.post_type = 'product' AND meta_key LIKE %s
				AND meta_key NOT IN ( '_sku', '__product_image_gallery', '_thumbnail_id', '_product_attributes', 'total_sales', '_manage_stock', '_backorders', '_sold_individually',
				                    '_virtual', '_downloadable', '_download_limit', '_stock', '_wc_average_rating', '_wc_review_count', '_product_version')
               ",
					'%' . $term . '%'
				)
			);
			$terms         = apply_filters( 'ywcas_custom_meta_field_list', $terms, $term );
			$custom_fields = array();
			foreach ( $terms as $term ) {
				$custom_fields[ $term ] = $term;
			}
			wp_send_json( $custom_fields );
		}

		/**
		 * Return the custom fields requested inside the admin panel option.
		 *
		 * @return void
		 */
		public function search_product_attributes() {
			check_ajax_referer( 'search-terms', 'security' );

			$term = isset( $_REQUEST['term'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : false;

			if ( empty( $term ) ) {
				die();
			}

			global $wpdb;
			$attributes = array();
			$results    = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT attribute_name, attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name OR attribute_label LIKE %s ",
					'%' . $term . '%'
				)
			);

			if ( $results ) {
				foreach ( $results as $attribute ) {
					$attributes[ wc_attribute_taxonomy_name( $attribute->attribute_name ) ] = $attribute->attribute_label;
				}
			}

			$attributes_results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key  LIKE %s ",
					wc_variation_attribute_name( $term ) . '%'
				)
			);

			if ( $attributes_results ) {
				foreach ( $attributes_results as $attribute ) {
					$attributes[ $attribute->meta_key ] = str_replace( 'attribute_', '', $attribute->meta_key );
				}
			}
			wp_send_json( $attributes );
		}

		/**
		 * Add the action links to plugin admin page.
		 *
		 * @param array  $new_row_meta_args Plugin Meta New args.
		 * @param array  $plugin_meta Plugin Meta.
		 * @param string $plugin_file Plugin file.
		 * @param array  $plugin_data Plugin data.
		 * @param string $status Status.
		 * @param string $init_file Init file.
		 *
		 * @return   Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCAS_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug']       = YITH_WCAS_SLUG;
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}


		/**
		 * Add custom field type
		 *
		 * @param array $types The custom types.
		 *
		 * @return array
		 */
		public function add_new_custom_types( $types ) {
			$types[] = 'ajax-wcas-custom-fields';
			$types[] = 'ajax-wcas-product-attributes';

			return $types;
		}

		/**
		 * Add the tab for the premium version
		 *
		 * @param array $admin_tab The admin tab.
		 *
		 * @return array
		 */
		public function add_premium_tab( $admin_tab ) {

			$admin_tab['customization'] = array(
				'title' => __( 'Customization', 'yith-woocommerce-ajax-search' ),
				'icon'  => '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  						     <path stroke-linecap="round" stroke-linejoin="round" d="M15 11.25l1.5 1.5.75-.75V8.758l2.276-.61a3 3 0 10-3.675-3.675l-.61 2.277H12l-.75.75 1.5 1.5M15 11.25l-8.47 8.47c-.34.34-.8.53-1.28.53s-.94.19-1.28.53l-.97.97-.75-.75.97-.97c.34-.34.53-.8.53-1.28s.19-.94.53-1.28L12.75 9M15 11.25L12.75 9"></path>
						  </svg>',
			);

			$offset     = array_search( 'search-fields', array_keys( $admin_tab ), true );
			$boost_menu = array(
				'boost' => array(
					'title' => _x( 'Boost Results', 'Admin tab name', 'yith-woocommerce-ajax-search' ),
					'icon'  => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"></path>
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"></path>
</svg>',
				),
			);

			$admin_tab = array_slice( $admin_tab, 0, $offset + 1 ) + $boost_menu + array_slice( $admin_tab, count( $boost_menu ) - 1, null, true );

			return $admin_tab;
		}

		/**
		 * Add store tools in premium menu and remove the premium tab
		 *
		 * @param array $args The args.
		 *
		 * @return array
		 */
		public function add_store_tools_tab( $args ) {
			if ( isset( $args['premium_tab'] ) ) {
				unset( $args['premium_tab'] );
			}
			$args['your_store_tools'] = array(
				'items' => array(
					'membership'             => array(
						'name'           => 'Membership',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/membership.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-membership/',
						'description'    => _x(
							'Activate some sections of your e-commerce with restricted access so as to create memberships in your store.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Membership',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_WCMBS_PREMIUM' ),
						'is_recommended' => true,
					),
					'multi-vendor'           => array(
						'name'           => 'Multi Vendor / Marketplace',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/multi-vendor.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-multi-vendor/',
						'description'    => _x(
							'Turn your e-commerce store into a marketplace (a multi-vendor platform) and earn commissions on orders generated by your vendors.',
							'[YOUR STORE TOOLS TAB] Description for plugin MultiVendor',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_WPV_PREMIUM' ),
						'is_recommended' => true,
					),
					'brand-addon'            => array(
						'name'           => 'Brands Add-On',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/brands-addon.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/',
						'description'    => _x(
							'Create unlimited brands to assign to your products to generate reliability and improve user experience by helping users easily find products of specific brands.',
							'[YOUR STORE TOOLS TAB] Description for plugin Brands Add-On',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_WCBR_PREMIUM_INIT' ),
						'is_recommended' => true,
					),
					'wishlist'               => array(
						'name'           => 'Wishlist',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/wishlist.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-wishlist/',
						'description'    => _x(
							'Allow your customers to create lists of products they want and share them with family and friends.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Wishlist',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_WCWL_PREMIUM' ),
						'is_recommended' => true,
					),
					'ajax-product-filter'    => array(
						'name'           => 'Ajax Product Filter',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/ajax-product-filter.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/',
						'description'    => _x(
							'Help your customers to easily find the products they are looking for and improve the user experience of your shop.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Ajax Product Filter',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_WCAN_PREMIUM' ),
						'is_recommended' => true,
					),
					'booking'                => array(
						'name'           => 'Booking and Appointment',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/booking.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-booking/',
						'description'    => _x(
							'Enable a booking/appointment system to manage renting or booking of services, rooms, houses, cars, accommodation facilities and so on.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH Bookings',
							'ywpdp'
						),
						'is_active'      => defined( 'YITH_WCBK_PREMIUM' ),
						'is_recommended' => false,

					),
					'request-a-quote'        => array(
						'name'           => 'Request a Quote',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/request-a-quote.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
						'description'    => _x(
							'Hide prices and/or the "Add to cart" button and let your customers request a custom quote for every product.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Request a Quote',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_YWRAQ_PREMIUM' ),
						'is_recommended' => false,
					),
					'product-addons'         => array(
						'name'           => 'Product Add-Ons & Extra Options',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/product-add-ons.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
						'description'    => _x(
							'Add paid or free advanced options to your product pages using fields like radio buttons, checkboxes, drop-downs, custom text inputs, and more.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Product Add-Ons',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_WAPO_PREMIUM' ),
						'is_recommended' => false,
					),
					'gift-cards'             => array(
						'name'           => 'Gift Cards',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/gift-card.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-gift-cards/',
						'description'    => _x(
							'Sell gift cards to increase your store\'s revenue and win new customers.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Gift Cards',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_YWGC_PREMIUM' ),
						'is_recommended' => false,
					),
					'customize-my-account'   => array(
						'name'           => 'Customize My Account Page',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/customize-myaccount-page.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-customize-my-account-page/',
						'description'    => _x(
							'Customize the My Account page of your customers by creating custom sections with promotions and ad-hoc content based on your needs.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Customize My Account',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_WCMAP_PREMIUM' ),
						'is_recommended' => false,
					),
					'recover-abandoned-cart' => array(
						'name'           => 'Recover Abandoned Cart',
						'icon_url'       => YITH_WCAS_ASSETS_URL . '/images/plugins/recover-abandoned-cart.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-recover-abandoned-cart/',
						'description'    => _x(
							'Contact users who have added products to the cart without completing the order and try to recover lost sales.',
							'[YOUR STORE TOOLS TAB] Description for plugin Recover Abandoned Cart',
							'yith-woocommerce-ajax-search'
						),
						'is_active'      => defined( 'YITH_YWRAC_PREMIUM' ),
						'is_recommended' => false,
					),
				),
			);
			$args['help_tab'] = array(
				'hc_url'     => 'https://support.yithemes.com/hc/en-us/categories/360003474658-YITH-WOOCOMMERCE-AJAX-SEARCH',
				'doc_url'    => $this->get_doc_url(),
				'main_video' => array(
					'desc' => _x( 'Check this video to learn how to <b>configure your search form</b>', '[HELP TAB] Video title', 'yith-woocommerce-ajax-search' ),
					'url'  => array(
						'en' => '//youtube.com/embed/Y5Dz4X0uOtY',
						'it' => '//youtube.com/embed/nTPUaWslCgU',
						'es' => '//youtube.com/embed/BAMwT92OgWw',
					),
				),
				'playlists'  => array(
					'en' => '//youtube.com/watch?v=Y5Dz4X0uOtY&list=PLDriKG-6905kPHRG552b1fh3hlVBkyWAN&ab_channel=YITH',
					'it' => '//youtube.com/watch?v=nTPUaWslCgU&list=PL9c19edGMs0-YOGLxht9DUfALDq_oN2h1&ab_channel=YITHITALIA',
					'es' => '//youtube.com/watch?v=BAMwT92OgWw&list=PL9Ka3j92PYJMm1TNxKAxYifflDcyitwpT&ab_channel=YITHESPA%C3%91A',
				),
			);

			$args['welcome_modals'] = array(
				'on_close' => function () {
					update_option( 'ywcas-plugin-welcome-modal', 'no' );
				},
				'modals'   => array(
					'welcome' => array(
						'type'        => 'welcome',
						'description' => __( 'The ultimate search plugin to help your customers quickly search, find, and buy products from your shop.', 'yith-woocommerce-ajax-search' ),
						'show'        => get_option( 'ywcas-plugin-welcome-modal', 'welcome' ) === 'welcome',
						'items'       => array(
							'documentation'  => array(),
							'how-to-video'   => array(
								'url' => array(
									'en' => '//www.youtube.com/watch?v=Y5Dz4X0uOtY',
									'it' => '//www.youtube.com/watch?v=nTPUaWslCgU',
									'es' => '//www.youtube.com/watch?v=BAMwT92OgWw',
								),
							),
							'add-shortcode'  => array(
								'title'       => __( 'Copy the <mark>default search field shortcode</mark> to use it in your shop', 'yith-woocommerce-ajax-search' ),
								'description' => __( 'and customize it to fit your needs', 'yith-woocommerce-ajax-search' ),
								'url'         => add_query_arg(
									array(
										'page' => 'yith_wcas_panel',
										'tab'  => 'shortcodes',
									),
									admin_url( 'admin.php' )
								),
							),
							'create-product' => array(
								'title'       => __( '… or add instead the <mark>YITH Search block</mark> to try it', 'yith-woocommerce-ajax-search' ),
								'description' => __( 'because blocks work better!', 'yith-woocommerce-ajax-search' ),
								'url'         => admin_url( 'widgets.php' ),
							),
						),
					),

				),
			);

			return $args;
		}

		/**
		 * Add the premium option in the tab
		 *
		 * @param array $options The options.
		 *
		 * @return array
		 */
		public function add_premium_option_in_general_tab( $options ) {
			$results_section_options = array(
				'section_search_results_settings'     => array(
					'name' => _x( 'Search results', 'Admin section title', 'yith-woocommerce-ajax-search' ),
					'type' => 'title',
					'id'   => 'ywcas_section_search_results_settings',
				),
				'include_variations'                  => array(
					'id'        => 'yith_wcas_include_variations',
					'name'      => _x( 'Show variations as separate products', 'Admin option label', 'yith-woocommerce-ajax-search' ),
					'desc'      => _x( 'Enable to show each single variation as a separate product in search results.', 'Admin option description', 'yith-woocommerce-ajax-search' ),
					'type'      => 'yith-field',
					'yith-type' => 'onoff',
					'default'   => 'no',
					'deps'      => array(
						'id'    => 'yith_wcas_enable_autocomplete',
						'value' => 'yes',
					),
				),
				'hide_out_of_stock'                   => array(
					'id'        => 'yith_wcas_hide_out_of_stock',
					'name'      => _x( 'Hide out-of-stock products', 'Admin option label', 'yith-woocommerce-ajax-search' ),
					'desc'      => _x( 'Enable this option if you don\'t want to show out-of-stock products in the results.', 'Admin option description', 'yith-woocommerce-ajax-search' ),
					'type'      => 'yith-field',
					'yith-type' => 'onoff',
					'default'   => 'no',
				),
				'section_end_search_results_settings' => array(
					'type' => 'sectionend',
					'id'   => 'ywcas_section_search_results_settings_end',
				),
			);

			$offset = array_search( 'section_end_search_settings', array_keys( $options['general'] ), true );

			$options['general'] = array_slice( $options['general'], 0, $offset + 1, true ) + $results_section_options + array_slice( $options['general'], count( $results_section_options ) - 1, null, true );
			unset( $options['general']['trending_searches_source']['is_option_disabled'] );
			unset( $options['general']['trending_searches_source']['option_tags'] );

			unset( $options['general']['trending_searches_keywords']['is_option_disabled'] );
			unset( $options['general']['trending_searches_keywords']['option_tags'] );

			return $options;
		}

		/**
		 * Add premium search field type
		 *
		 * @param array $fields_type The search type.
		 *
		 * @return array
		 */
		public function add_premium_search_field_type( $fields_type ) {
			$premium_type = array(
				'sku'                => _x( 'SKU', '[Admin]search field type', 'yith-woocommerce-ajax-search' ),
				'product_categories' => _x( 'Product categories', '[Admin]search field type', 'yith-woocommerce-ajax-search' ),
				'product_tags'       => _x( 'Product tags', '[Admin]search field type', 'yith-woocommerce-ajax-search' ),
				'product_attributes' => _x( 'Product attributes', '[Admin]search field type', 'yith-woocommerce-ajax-search' ),
				'custom_fields'      => _x( 'Custom fields', '[Admin]search field type', 'yith-woocommerce-ajax-search' ),
			);

			return array_merge( $fields_type, $premium_type );
		}

		/**
		 * Prepare the field before save it
		 *
		 * @param array $option The option.
		 * @param array $field The field.
		 *
		 * @return array
		 */
		public function save_premium_search_field_option( $option, $field ) {
			switch ( $field['type'] ) {
				case 'sku':
					$option[] = array(
						'type'     => $field['type'],
						'priority' => $field['priority'],
					);
					break;
				case 'product_categories':
					$option[] = array(
						'type'                       => $field['type'],
						'priority'                   => $field['priority'],
						'product_category_condition' => $field['product_category_condition'] ?? 'all',
						'category-list'              => $field['category-list'] ?? array(),
					);
					break;
				case 'product_tags':
					$option[] = array(
						'type'                  => $field['type'],
						'priority'              => $field['priority'],
						'product_tag_condition' => $field['product_tag_condition'] ?? 'all',
						'tag-list'              => $field['tag-list'] ?? array(),
					);
					break;
				case 'product_attributes':
					$option[] = array(
						'type'                   => $field['type'],
						'priority'               => $field['priority'],
						'product_attribute_list' => $field['product_attribute_list'] ?? array(),
					);
					break;
				case 'custom_fields':
					$option[] = array(
						'type'              => $field['type'],
						'priority'          => $field['priority'],
						'custom_field_list' => $field['custom_field_list'] ?? array(),
					);
					break;

			}

			return $option;
		}

		/**
		 * Return the section to add a new shortcode
		 *
		 * @return void
		 */
		public function add_new_shortcode() {
			check_ajax_referer( 'ywcas-search-shortcode', 'security' );
			if ( ! isset( $_REQUEST['slug'] ) ) {
				wp_send_json_error();
			}

			$new_name                   = __( 'New Preset', 'yith-woocommerce-ajax-search' );
			$slug                       = sanitize_text_field( wp_unslash( $_REQUEST['slug'] ) );
			$options                    = ywcas()->settings->get_default_shortcode_options();
			$options['general']['name'] = $new_name;
			$shortcode                  = array(
				'name'    => __( 'New Preset', 'yith-woocommerce-ajax-search' ),
				'code'    => "[yith_woocommerce_ajax_search preset='{$slug}']",
				'options' => $options,
			);
			$can_be_cloned              = false;
			ob_start();
			include_once YITH_WCAS_INC . 'admin/views/panel/shortcode-configuration.php';
			$newshortcode = ob_get_clean();

			wp_send_json_success( array( 'content' => $newshortcode ) );
		}

		/**
		 * Delete a shortcode preset
		 *
		 * @return void
		 */
		public function delete_shortcode() {
			check_ajax_referer( 'ywcas-search-shortcode', 'security' );
			if ( ! isset( $_POST['slug'] ) ) {
				wp_send_json_error();
			}
			$slug       = sanitize_text_field( wp_unslash( $_POST['slug'] ) );
			$shortcodes = ywcas()->settings->get_shortcodes_list();
			unset( $shortcodes[ $slug ] );
			ywcas()->settings->update_shortcodes_list( $shortcodes );
			wp_send_json_success();
		}

		/**
		 * Return the section to clone shortcode
		 *
		 * @return void
		 */
		public function clone_shortcode() {
			check_ajax_referer( 'ywcas-search-shortcode', 'security' );
			if ( ! isset( $_REQUEST['slug'], $_REQUEST['newSlug'] ) ) {
				wp_send_json_error();
			}

			$slug_to_clone = sanitize_text_field( wp_unslash( $_REQUEST['slug'] ) );
			$slug          = sanitize_text_field( wp_unslash( $_REQUEST['newSlug'] ) );
			$shortcodes    = ywcas()->settings->get_shortcodes_list();
			if ( isset( $shortcodes[ $slug_to_clone ] ) ) {
				$shortcode_to_clone         = $shortcodes[ $slug_to_clone ];
				$options                    = $shortcode_to_clone['options'];
				$options['general']['name'] = $options['general']['name'] . ' - ' . _x( '(Copy)', 'suffix added to a shortcode duplicate', 'yith-woocommerce-ajax-search' );
				$shortcode                  = array(
					'name'    => $options['general']['name'],
					'code'    => "[yith_woocommerce_ajax_search preset='{$slug}']",
					'options' => $options,
				);

				$can_be_cloned = false;
				ob_start();
				include_once YITH_WCAS_INC . 'admin/views/panel/shortcode-configuration.php';
				$newshortcode = ob_get_clean();
				wp_send_json_success( array( 'content' => $newshortcode ) );
			}

			wp_send_json_error();
		}

		/**
		 * Includes Privacy DPA Class.
		 */
		public function load_privacy_dpa() {
			YITH_WCAS_Privacy_DPA::get_instance();
		}

		/**
		 * Register plugins for activation tab.
		 *
		 * @since 1.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {

				require_once YITH_WCAS_DIR . 'plugin-fw/licence/lib/yit-licence.php';
				require_once YITH_WCAS_DIR . 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YITH_WCAS_INIT, YITH_WCAS_SECRET_KEY, YITH_WCAS_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @since    1.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {

				require_once YITH_WCAS_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_WCAS_SLUG, YITH_WCAS_INIT );
		}
	}
}
