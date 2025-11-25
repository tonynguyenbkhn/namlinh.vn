<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Frontend Premium class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WRVP_Frontend_Premium' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WRVP_Frontend_Premium extends YITH_WRVP_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var \YITH_WRVP_Frontend_Premium
		 */
		protected static $instance;

		/**
		 * Page id
		 *
		 * @since 1.0.0
		 * @var \YITH_WRVP_Frontend_Premium
		 */
		protected $recently_viewed_page;

		/**
		 * Plugin version
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = YITH_WRVP_VERSION;

		/**
		 * The name of meta purchased products
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $meta_purchased_products = 'yith_wrvp_purchased_products';

		/**
		 * The name of filter by categories action
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $filter_cat_action = 'ywrvp_filter_by_cat_action';

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 * @return \YITH_WRVP_Frontend_Premium
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

			parent::__construct();

			$this->recently_viewed_page = get_option( 'yith-wrvp-page-id' );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ), 10 );

			add_action( 'wp_login', array( $this, 'init_products_list' ), 10, 2 );

			// remove products form list.
			add_action( 'init', array( $this, 'remove_product' ), 5 );

			// action in recently viewed page.
			add_action( 'template_redirect', array( $this, 'page_actions' ) );

			add_action( 'init', array( $this, 'create_meta_purchased_products' ), 10 );
			add_action( 'init', array( $this, 'remove_products' ), 20 );

			add_action( 'woocommerce_order_status_completed', array( $this, 'update_meta_purchased_products' ), 99, 1 );
			add_action( 'woocommerce_order_status_processing', array( $this, 'update_meta_purchased_products' ), 99, 1 );

			add_shortcode( 'yith_recenlty_viewed_page', array( $this, 'recently_viewed_page' ) );
			add_shortcode( 'yith_most_viewed_products', array( $this, 'most_viewed_products' ) );
		}

		/**
		 * Recently viewed products page actions
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function page_actions() {

			if ( ! is_page( $this->recently_viewed_page ) ) {
				return;
			}

			// remove link.
			add_action( 'woocommerce_before_shop_loop_item', array( $this, 'add_remove_link' ), 5 );
			// filter by cat.
			add_action( 'yith_wrvp_before_products_loop', array( $this, 'filter_by_cat_template' ), 10 );
		}

		/**
		 * Enqueue scripts
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function enqueue_script() {

			$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			wp_register_script( 'yith-wrvp-frontend', YITH_WRVP_ASSETS_URL . '/js/yith-wrvp-frontend' . $min . '.js', array( 'jquery' ), YITH_WRVP_VERSION, true );
			wp_register_style( 'yith-wrvp-frontend', YITH_WRVP_ASSETS_URL . '/css/yith-wrvp-frontend.css', array(), YITH_WRVP_VERSION );

			// slider jquery plugin.
			wp_register_script( 'slick', YITH_WRVP_ASSETS_URL . '/js/slick.min.js', array( 'jquery' ), YITH_WRVP_VERSION, true );
			wp_register_style( 'ywrvp_slick', YITH_WRVP_ASSETS_URL . '/css/slick.css', array(), YITH_WRVP_VERSION );

			wp_enqueue_script( 'yith-wrvp-frontend' );
			wp_enqueue_style( 'yith-wrvp-frontend' );
			wp_enqueue_script( 'slick' );
			wp_enqueue_style( 'ywrvp_slick' );

			wp_localize_script(
				'yith-wrvp-frontend',
				'ywrvp',
				array(
					'ajaxurl'                         => get_permalink( $this->recently_viewed_page ),
					/**
					 * APPLY_FILTERS: yith_wrvp_products_selector
					 *
					 * Filters the selector to initialize the slider in the Recently Viewed Products page.
					 *
					 * @param string $selector Selector.
					 *
					 * @return string
					 */
					'products_selector'               => apply_filters( 'yith_wrvp_products_selector', '.products' ),
					/**
					 * APPLY_FILTERS: yith_wrvp_slider_n_columns_breakpoint_480
					 *
					 * Filters the number of columns to show in the slider in mobile devices.
					 *
					 * @param int $columns Number of columns.
					 *
					 * @return int
					 */
					'slider_n_columns_breakpoint_480' => apply_filters( 'yith_wrvp_slider_n_columns_breakpoint_480', 1 ),
				)
			);
		}

		/**
		 * Init cookie for users after a login action
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string  $username Username.
		 * @param WP_User $user User instance.
		 */
		public function init_products_list( $username, $user ) {

			$this->user_id = $user->data->ID;

			// exit if admin.
			if ( user_can( $this->user_id, 'administrator' ) ) {
				return;
			}

			$meta_products_list  = get_user_meta( $this->user_id, $this->meta_products_list, true );
			$this->products_list = isset( $_COOKIE[ $this->cookie_name ] ) ? unserialize( $_COOKIE[ $this->cookie_name ] ) : array(); // phpcs:ignore

			if ( ! empty( $meta_products_list ) ) {
				// merge with cookie value.
				foreach ( $meta_products_list as $key => $value ) {
					if ( is_array( $this->products_list ) && in_array( $value, $this->products_list, true ) ) {
						continue;
					}

					$this->products_list[ $key ] = $value;
				}
			}

			// remove general cookie.
			setcookie( $this->cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, false, true );

			// then save.
			$this->set_cookie_meta();
		}

		/**
		 * Create an user meta with all purchased products
		 *
		 * @since 1.0.0
		 */
		public function create_meta_purchased_products() {

			// Check first if option is enabled or meta already exists.
			if ( get_option( 'yith-wrvp-excluded-purchased' ) !== 'yes' || metadata_exists( 'user', $this->user_id, $this->meta_purchased_products ) ) {
				return;
			}

			$purchased = $this->get_purchased_products();
			update_user_meta( $this->user_id, $this->meta_purchased_products, $purchased );
		}

		/**
		 * Get purchased products for user
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return array
		 */
		protected function get_purchased_products() {

			global $wpdb;

			$user = $this->user_id;

			$query           = array();
			$query['fields'] = "SELECT DISTINCT a.meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta a";
			$query['join']   = " LEFT JOIN {$wpdb->prefix}woocommerce_order_items b ON ( b.order_item_id = a.order_item_id )";
			$query['join']  .= " LEFT JOIN {$wpdb->postmeta} c ON ( c.post_id = b.order_id )";
			$query['join']  .= " LEFT JOIN {$wpdb->posts} d ON ( d.ID = c.post_id )";

			$query['where']  = " WHERE a.meta_key = '_product_id'";
			$query['where'] .= " AND c.meta_key = '_customer_user' AND c.meta_value = {$user}";
			$query['where'] .= " AND d.post_status IN ( 'wc-processing', 'wc-completed' )";

			$results = $wpdb->get_col( implode( ' ', $query ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared

			return $results;
		}

		/**
		 * Update user purchased products
		 *
		 * @access public
		 * @since 1.0.0
		 * @param int $order_id Order ID.
		 */
		public function update_meta_purchased_products( $order_id ) {

			// first check option.
			if ( 'yes' !== get_option( 'yith-wrvp-excluded-purchased' ) ) {
				return;
			}

			// get order.
			$order = wc_get_order( $order_id );

			$items      = $order->get_items();
			$to_exclude = array();

			foreach ( $items as $item ) {
				if ( 'line_item' === $item['type'] && isset( $item['item_meta'] ) ) {
					$to_exclude[] = intval( $item['item_meta']['_product_id'][0] );
				}
			}

			$user_id       = yit_get_prop( $order, 'customer_user', true );
			$excluded_list = get_user_meta( $user_id, $this->meta_purchased_products, true );
			$excluded_list = empty( $excluded_list ) ? array() : $excluded_list;

			foreach ( $to_exclude as $exclusion ) {
				if ( ! in_array( $exclusion, $excluded_list, true ) ) {
					$excluded_list[] = $exclusion;
				}
			}

			update_user_meta( $user_id, $this->meta_purchased_products, $excluded_list );
		}

		/**
		 * Shortcode similar products
		 *
		 * @access public
		 * @since 1.0.0
		 * @param array $atts Shortcode attributes.
		 * @return mixed
		 */
		public function similar_products( $atts ) {

			global $product, $woocommerce_loop;

			$num_products = get_option(
				'yith-wrvp-num-products',
				array(
					'total'   => 6,
					'per-row' => 4,
				)
			);

			$atts = shortcode_atts(
				array(
					'num_post'        => $num_products['total'],
					'order'           => get_option( 'yith-wrvp-order-products', 'rand' ),
					'title'           => get_option( 'yith-wrvp-section-title', '' ),
					'slider'          => get_option( 'yith-wrvp-slider', 'yes' ),
					'dots'            => get_option( 'yith-wrvp-slider-dots', 'no' ),
					'autoplay'        => get_option( 'yith-wrvp-slider-autoplay', 'yes' ),
					'autoplay_speed'  => '3000',
					'prod_type'       => get_option( 'yith-wrvp-type-products', 'similar' ),
					'similar_type'    => get_option( 'yith-wrvp-type-similar-products', 'both' ),
					'cat_most_viewed' => get_option( 'yith-wrvp-cat-most-viewed', 'no' ),
					'view_all'        => get_option( 'yith-wrvp-view-all-text', '' ),
					'view_all_link'   => '',
					'num_columns'     => $num_products['per-row'],
					'cats_id'         => '',
					'class'           => '',
				),
				$atts
			);

			// extract $atts.
			extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			$args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'posts_per_page'      => $num_post,
				'order'               => 'DESC',
			);

			if ( 'yes' === $cat_most_viewed ) {
				// get cat id.
				$category = $this->most_viewed_cat();
				$cats_id  = array( $category );
			} elseif ( $cats_id ) {
				$cats_id = explode( ',', $cats_id );
				$cats_id = array_filter( $cats_id );
			} else {
				$cats_id = array();
			}

			$products_list = array();
			if ( 'similar' === $prod_type ) {
				$products_list = $this->get_similar_products( $cats_id, $similar_type );
			} else {
				$products_list = array_reverse( array_values( $this->products_list ) );
				// set tax query.
				if ( ! empty( $cats_id ) ) {
					$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'id',
							'terms'    => $cats_id,
							'operator' => 'IN',
						),
					);
				}
			}

			// remove current product from products list.
			/**
			 * APPLY_FILTERS: yith_wrvp_exclude_current_product
			 *
			 * Filters whether to exclude the current product from the products list.
			 *
			 * @param bool $exclude_current_product Whether to exclude the current product or not.
			 *
			 * @return bool
			 */
			if ( $product && is_product() && apply_filters( 'yith_wrvp_exclude_current_product', true ) ) {
				$key = array_search( $product->get_id(), $products_list, true );

				if ( false !== $key ) {
					unset( $products_list[ $key ] );
				}
			}

			// also set variable for shop.
			$page_url = $view_all_link ? esc_url( $view_all_link ) : get_permalink( $this->recently_viewed_page );

			/**
			 * APPLY_FILTERS: yith_wrvp_view_all_link
			 *
			 * Filters the link of the Recently Viewed Products page.
			 *
			 * @param string $link Link of the Recently Viewed Products page.
			 * @param array  $atts Array of attributes.
			 *
			 * @return string
			 */
			$page_url = apply_filters( 'yith_wrvp_view_all_link', $page_url, $atts );

			// set post__in param with products list.
			/**
			 * APPLY_FILTERS: yith_wrvp_product_list_shortcode
			 *
			 * Filters the products to display in the shortcode for the similar products.
			 *
			 * @param array $products_list Array of product IDs.
			 * @param array $atts          Array of attributes.
			 * @param array $cats_id       Array of category IDs.
			 *
			 * @return array
			 */
			$args['post__in'] = apply_filters( 'yith_wrvp_product_list_shortcode', $products_list, $atts, $cats_id );

			if ( empty( $args['post__in'] ) ) {
				$no_found = $this->get_not_found_html();

				/**
				 * APPLY_FILTERS: yith_wrvp_shortcode_return_html
				 *
				 * Filters the HTML content displayed in the shortcode when there are no products in the products list.
				 *
				 * @param string                     $no_found      HTML content.
				 * @param array                      $products_list Products list.
				 * @param YITH_WRVP_Frontend_Premium $instance      Class instance.
				 *
				 * @return string
				 */
				return apply_filters( 'yith_wrvp_shortcode_return_html', $no_found, $this->products_list, $this );
			}

			// hide free.
			if ( 'yes' === get_option( 'yith-wrvp-hide-free' ) ) {
				$args['meta_query'][] = array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'DECIMAL',
				);
			}

			// visibility meta query.
			$args = yit_product_visibility_meta( $args );

			if ( 'yes' === get_option( 'yith-wrvp-hide-out-of-stock' ) ) {
				$args['meta_query'][] = array(
					'key'     => '_stock_status',
					'value'   => 'instock',
					'compare' => '=',
				);
			}

			switch ( $order ) {
				case 'sales':
					$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$args['orderby']  = 'meta_value_num';
					break;
				case 'newest':
					$args['orderby'] = 'date';
					break;
				case 'high-low':
					$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$args['orderby']  = 'meta_value_num';
					break;
				case 'low-high':
					$args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$args['orderby']  = 'meta_value_num';
					$args['order']    = 'ASC';
					break;
				case 'viewed':
					$args['orderby'] = 'post__in';
					break;
				default:
					$args['orderby'] = 'rand';
					break;
			}

			/**
			 * APPLY_FILTERS: yith_wrvp_shortcode_query_args
			 *
			 * Filters the query args to get the similar products in the shortcode.
			 *
			 * @param array $args Query args.
			 *
			 * @return array
			 */
			$args = apply_filters( 'yith_wrvp_shortcode_query_args', $args );

			// TRANSIENT.
			$transient_name = 'yith_wrvp_' . md5( 'Similar: ' . wp_json_encode( $args ) );
			$products_ids   = yith_wrvp_get_transient( $transient_name );

			if ( false === $products_ids ) {
				$args['fields'] = 'ids';
				$products_ids   = get_posts( $args );
				yith_wrvp_set_transient( $transient_name, $products_ids, WEEK_IN_SECONDS );
			}

			/**
			 * APPLY_FILTERS: yith_wrvp_product_ids
			 *
			 * Filters the product IDs to include in the similar products shortcode.
			 *
			 * @param array $products_ids Array of product IDs.
			 *
			 * @return array
			 */
			$products_ids = apply_filters( 'yith_wrvp_product_ids', $products_ids );

			ob_start();

			if ( ! empty( $products_ids ) ) {
				// set main query.
				$products = new WP_Query();
				$products->init();
				$products->query_vars  = wp_parse_args( $args );
				$products->query       = $products->query_vars;
				$products->posts       = array_map( 'get_post', $products_ids );
				$products->post_count  = count( $products->posts );
				$products->found_posts = $products->post_count;
				update_post_caches( $products->posts, 'product' );

				// Force slider if needed. To be removed.
				/**
				 * APPLY_FILTERS: yith_wrvp_force_slider_view
				 *
				 * Filters whether to force the slider view in the shortcode.
				 *
				 * @param bool $force_slider_view Whether to force the slider view or not.
				 * @param int  $products_count    Count of products.
				 *
				 * @return bool
				 */
				$slider = apply_filters( 'yith_wrvp_force_slider_view', 'yes' === $slider && $products->post_count > $num_columns, $products->post_count ) ? 'yes' : 'no';

				// template args.
				/**
				 * APPLY_FILTERS: yith_wrvp_templates_query_args
				 *
				 * Filters the array with the arguments needed for the loop template.
				 *
				 * @param array $args Array of arguments.
				 *
				 * @return array
				 */
				$templates_args = apply_filters(
					'yith_wrvp_templates_query_args',
					array(
						'products'       => $products,
						'title'          => $title,
						'slider'         => $slider,
						'autoplay'       => $autoplay,
						'dots'           => $dots,
						'page_url'       => $page_url,
						'view_all'       => $view_all,
						'columns'        => $num_columns,
						'class'          => $class,
						'autoplay_speed' => $autoplay_speed,
					)
				);

				wc_get_template( 'ywrvp-loop-template.php', $templates_args, '', YITH_WRVP_DIR . 'templates/' );
			}

			$content = ob_get_clean();

			wp_reset_postdata();
			// reset woocommerce loop data.
			unset( $GLOBALS['woocommerce_loop'] );

			return apply_filters( 'yith_wrvp_shortcode_return_html', $content, $this->products_list, $this );
		}

		/**
		 * Add remove link for product in list
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function add_remove_link() {

			global $product;

			$query_args = array(
				'_yith_remove_product' => $product->get_id(),
			);

			$url = esc_url_raw( add_query_arg( $query_args, get_permalink( get_option( 'yith-wrvp-page-id' ) ) ) );

			/**
			 * APPLY_FILTERS: yith_wrvp_remove_link
			 *
			 * Filters the HTML link to remove the product from the products list.
			 *
			 * @param string $link Link to remove the product.
			 * @param string $url  URL to remove the product.
			 *
			 * @return string
			 */
			echo wp_kses_post( apply_filters( 'yith_wrvp_remove_link', '<a href="' . $url . '" class="remove-product">X ' . esc_html__( 'Remove', 'yith-woocommerce-recently-viewed-products' ) . '</a>', $url ) );
		}

		/**
		 * Remove products from product list and update cookie and meta
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function remove_product() {

			if ( ! isset( $_GET['_yith_remove_product'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			$id  = intval( $_GET['_yith_remove_product'] ); // phpcs:ignore WordPress.Security.NonceVerification
			$key = array_search( $id, $this->products_list, true );

			if ( false !== $key ) {
				unset( $this->products_list[ $key ] );
			}

			// set meta and cookie with new products list.
			$this->set_cookie_meta();

			// the redirect to shop.
			$url = esc_url_raw( remove_query_arg( array( '_yith_remove_product', '_yith_nonce' ) ) );
			wp_safe_redirect( $url );
			exit;
		}


		/**
		 * Add categories filter in custom shop page
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function filter_by_cat_template() {

			$categories = $this->get_list_terms( 'product_cat', true );

			/**
			 * APPLY_FILTERS: yith_wrvp_filter_by_cat_args
			 *
			 * Filters the array with the arguments needed for the template to filter by category.
			 *
			 * @param array $args Array of arguments.
			 *
			 * @return array
			 */
			$args = apply_filters(
				'yith_wrvp_filter_by_cat_args',
				array(
					'categories' => $categories,
				)
			);

			wc_get_template( 'ywrvp-loop-filter.php', $args, '', YITH_WRVP_DIR . 'templates/' );
		}

		/**
		 * Update products list with purchased products and expired
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function remove_products() {

			if ( ! $this->user_id || empty( $this->products_list ) ) {
				return;
			}

			$duration   = get_option( 'yith-wrvp-cookie-time' );
			$expiration = time() - ( 86400 * $duration );

			// purchased products.
			$purchased = 'yes' === get_option( 'yith-wrvp-excluded-purchased' ) ? get_user_meta( $this->user_id, $this->meta_purchased_products, true ) : array();

			// remove.
			foreach ( $this->products_list as $key => $product_id ) {

				if ( $key < $expiration ) {
					unset( $this->products_list[ $key ] );
					continue;
				}

				if ( ! empty( $purchased ) ) {
					foreach ( $purchased as $item ) {
						if ( $product_id === $item ) {
							unset( $this->products_list[ $key ] );
						}
					}
				}
			}

			// save new list.
			$this->set_cookie_meta();
		}

		/**
		 * Get products list
		 *
		 * @since 1.0.0
		 */
		public function get_the_products_list() {
			return $this->products_list;
		}

		/**
		 * Get the id of the most viewed category
		 *
		 * @access public
		 * @since 1.0.0
		 * @param array $products_list Array of products.
		 * @return string | boolean if not found
		 */
		public function most_viewed_cat( $products_list = array() ) {

			if ( empty( $products_list ) ) {
				$products_list = $this->products_list;
			}

			return YITH_WRVP_Helper::most_viewed_cat( $products_list );
		}

		/**
		 * Recently viewed page shortcode
		 *
		 * @access public
		 * @since 1.0.0
		 * @param array $atts Shortcode attributes.
		 * @return mixed
		 */
		public function recently_viewed_page( $atts ) {

			global $wp_query;

			$atts = shortcode_atts(
				array(
					'columns' => wc_get_loop_prop( 'columns', wc_get_default_products_per_row() ),
				),
				$atts
			);

			// extract $atts.
			extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			$products_list = $this->products_list;

			if ( empty( $products_list ) ) {
				return $this->get_not_found_html();
			}

			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

			/**
			 * APPLY_FILTERS: yith_recently_viewed_page_query_args
			 *
			 * Filters the query args needed for the Recently Viewed Products page.
			 *
			 * @param array $query_args Query args.
			 *
			 * @return array
			 */
			$args = apply_filters(
				'yith_recently_viewed_page_query_args',
				array(
					'post_type'      => 'product',
					'posts_per_page' => apply_filters( 'loop_shop_per_page', get_option( 'posts_per_page' ) ),
					'paged'          => $paged,
					'post__in'       => $products_list,
				)
			);

			if ( ! empty( $_GET['ywrvp_cat_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => intval( $_GET['ywrvp_cat_id'] ), // phpcs:ignore WordPress.Security.NonceVerification
					),
				);
			}

			$wp_query = new WP_Query( $args ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			ob_start();

			if ( $wp_query->have_posts() ) {
				// template args.
				$templates_args = array(
					'products'       => $wp_query,
					'title'          => '',
					'slider'         => 'no',
					'dots'           => 'no',
					'autoplay'       => 'no',
					'autoplay_speed' => '3000',
					'page_url'       => '',
					'view_all'       => '',
					'class'          => 'in-page',
					'columns'        => $columns,
				);

				?>
				<div class="woocommerce">
					<?php

					if ( function_exists( 'wc_print_notices' ) ) {
						wc_print_notices();
					}

					/**
					 * DO_ACTION: yith_wrvp_before_products_loop
					 *
					 * Allows to render some content before the product loop in Recently Viewed Products page.
					 */
					do_action( 'yith_wrvp_before_products_loop' );

					wc_get_template( 'ywrvp-loop-template.php', $templates_args, '', YITH_WRVP_DIR . 'templates/' );

					woocommerce_pagination();

					/**
					 * DO_ACTION: yith_wrvp_after_products_loop
					 *
					 * Allows to render some content after the product loop in Recently Viewed Products page.
					 */
					do_action( 'yith_wrvp_after_products_loop' );

					?>
				</div>
				<?php
			}

			$content = ob_get_clean();

			wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query

			return $content;
		}

		/**
		 * Get not found product html message
		 *
		 * @since 1.1.0
		 * @return string
		 */
		public function get_not_found_html() {
			$message = get_option( 'yith-wrvp-nofound-msg', '' );
			if ( ! empty( $message ) ) {
				return '<p class="woocommerce-info">' . $message . '</p>';
			} else {
				return '';
			}
		}

		/**
		 * Shortcode that show products based on views globally
		 *
		 * @since 1.5.0
		 * @param array $atts Shortcode attributes.
		 * @return mixed
		 */
		public function most_viewed_products( $atts ) {

			$num_products = get_option(
				'yith-wrvp-num-products',
				array(
					'total'   => 6,
					'per-row' => 4,
				)
			);

			$atts = shortcode_atts(
				array(
					'num_post'       => $num_products['total'],
					'title'          => __( 'Most Viewed Products', 'yith-woocommerce-recently-viewed-products' ),
					'slider'         => get_option( 'yith-wrvp-slider', 'yes' ),
					'autoplay'       => get_option( 'yith-wrvp-slider-autoplay', 'yes' ),
					'dots'           => get_option( 'yith-wrvp-slider-dots', 'no' ),
					'autoplay_speed' => '3000',
					'num_columns'    => $num_products['per-row'],
					'class'          => '',
					'cats_id'        => '',
					'category'       => '', // deprecated, to remove.
				),
				$atts
			);

			// extract $atts.
			extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			$args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'posts_per_page'      => $num_post,
				'meta_key'            => '_ywrvp_views', // phpcs:ignore WordPress.DB.SlowDBQuery
				'orderby'             => 'meta_value_num',
				'order'               => 'DESC',
			);

			if ( ! empty( $category ) ) {
				if ( 'current' === $category ) {
					global $product;
					$terms = $product ? get_the_terms( $product->get_id(), 'product_cat' ) : '';
					foreach ( $terms as $term ) {
						$category_slug[] = $term->term_id;
						break;
					}
				} else {
					$category_slug = explode( ',', $category );
				}

				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $category_slug,
						'operator' => 'IN',
					),
				);
			} elseif ( ! empty( $cats_id ) ) {
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => $cats_id,
						'operator' => 'IN',
					),
				);
			}

			// hide free.
			if ( 'yes' === get_option( 'yith-wrvp-hide-free' ) ) {
				$args['meta_query'][] = array(
					'key'     => '_price',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'DECIMAL',
				);
			}

			// visibility meta query.
			$args = yit_product_visibility_meta( $args );

			if ( 'yes' === get_option( 'yith-wrvp-hide-out-of-stock' ) ) {
				$args['meta_query'][] = array(
					'key'     => '_stock_status',
					'value'   => 'instock',
					'compare' => '=',
				);
			}

			/**
			 * APPLY_FILTERS: yith_wrvp_shortcode_most_viewed_query_args
			 *
			 * Filters the query args to get the products for the most viewed shortcode.
			 *
			 * @param array $args Query args.
			 *
			 * @return array
			 */
			$args = apply_filters( 'yith_wrvp_shortcode_most_viewed_query_args', $args );
			// set main query.
			$products = new WP_Query( $args );

			// Force slider if needed. To be removed.
			$slider = apply_filters( 'yith_wrvp_force_slider_view', 'yes' === $slider && $products->post_count > $num_columns, $products->post_count ) ? 'yes' : 'no';

			ob_start();

			if ( ! empty( $products ) ) {

				// template args.
				/**
				 * APPLY_FILTERS: yith_wrvp_templates_most_viewed_query_args
				 *
				 * Filters the array with the arguments needed for the shortcode template.
				 *
				 * @param array $args Array of arguments.
				 *
				 * @return array
				 */
				$templates_args = apply_filters(
					'yith_wrvp_templates_most_viewed_query_args',
					array(
						'products'       => $products,
						'title'          => $title,
						'slider'         => $slider,
						'autoplay'       => $autoplay,
						'dots'           => $dots,
						'columns'        => $num_columns,
						'class'          => $class,
						'autoplay_speed' => $autoplay_speed,
					)
				);

				wc_get_template( 'ywrvp-most-viewed-template.php', $templates_args, '', YITH_WRVP_DIR . 'templates/' );
			}

			$content = ob_get_clean();

			wp_reset_postdata();
			// reset woocommerce loop data.
			unset( $GLOBALS['woocommerce_loop'] );

			/**
			 * APPLY_FILTERS: yith_wrvp_shortcode_most_viewed_return_html
			 *
			 * Filters the HTML content for the most viewed products shortcode.
			 *
			 * @param string                     $content       HTML content.
			 * @param array                      $products_list Products list.
			 * @param YITH_WRVP_Frontend_Premium $instance      Class instance.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wrvp_shortcode_most_viewed_return_html', $content, $this->products_list, $this );
		}
	}
}
/**
 * Unique access to instance of YITH_WRVP_Frontend_Premium class
 *
 * @since 1.0.0
 * @return \YITH_WRVP_Frontend_Premium
 */
function YITH_WRVP_Frontend_Premium() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WRVP_Frontend_Premium::get_instance();
}
