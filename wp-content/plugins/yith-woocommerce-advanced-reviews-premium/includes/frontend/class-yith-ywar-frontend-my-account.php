<?php
/**
 * Frontend My Account class
 *
 * @package YITH\AdvancedReviews\Frontend
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Frontend_My_Account' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the frontend behaviors.
	 *
	 * @class   YITH_YWAR_Frontend_My_Account
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Frontend
	 */
	class YITH_YWAR_Frontend_My_Account {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * My Account endpoint
		 *
		 * @var string
		 */
		public static $endpoint = '';

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'add_my_account_endpoint' ), 15 );
			add_filter( 'option_rewrite_rules', array( $this, 'rewrite_rules' ), 1 );
			add_action( 'template_redirect', array( $this, 'check_flush_rewrite_rules' ) );
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_reviews_endpoint_my_account' ) );
			add_filter( 'woocommerce_endpoint_ywar-reviews_title', array( $this, 'set_reviews_my_account_endpoint_title' ) );
			add_action( 'woocommerce_account_ywar-reviews_endpoint', array( $this, 'my_account_content' ) );
			add_action( 'yith_ywar_frontend_ajax_load_user_reviews', array( $this, 'load_user_reviews' ) );
		}

		/**
		 * Register a new endpoint to be customized
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_my_account_endpoint() {

			/**
			 * APPLY_FILTERS: yith_ywar_reviews_endpoint_my_account
			 *
			 * Filter the endpoint of the plugin.
			 *
			 * @param string 'my-reviews' by default.
			 */
			self::$endpoint = apply_filters( 'yith_ywar_reviews_endpoint_my_account', 'my-reviews' );

			$endpoints = array(
				'ywar-reviews' => self::$endpoint,
			);

			foreach ( $endpoints as $key => $endpoint ) {
				WC()->query->query_vars[ $key ] = $endpoint;
				add_rewrite_endpoint( $endpoint, WC()->query->get_endpoints_mask() );
			}
		}

		/**
		 * Sets the rewite rules for the endpoint.
		 *
		 * @param array $rules Rewrite Rules.
		 *
		 * @return array|boolean
		 * @since  2.0.0
		 */
		public function rewrite_rules( $rules ) {
			$ep    = self::$endpoint;
			$regex = "(.?.+?)/$ep(/(.*))?/?$";

			return isset( $rules[ $regex ] ) ? $rules : false;
		}

		/**
		 * Check if the permalink should be flushed.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function check_flush_rewrite_rules() {
			global $sitepress;
			if ( is_account_page() && ! $sitepress ) {
				function_exists( 'get_home_path' ) && flush_rewrite_rules();
			}
		}

		/**
		 * Insert a new endpoint in the my account page.
		 *
		 * @param array $items Default endpoints.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_reviews_endpoint_my_account( array $items ): array {

			$item_position = ( array_search( 'orders', array_keys( $items ), true ) );
			$items_part1   = array_slice( $items, 0, $item_position + 1 );
			$items_part2   = array_slice( $items, $item_position );

			/**
			 * APPLY_FILTERS: yith_ywar_my_account_menu_item_title,
			 *
			 * Filter the title text of the endpoint.
			 *
			 * @param string Text of the title endpoint.
			 */
			$items_part1[ self::$endpoint ] = apply_filters( 'yith_ywar_my_account_menu_item_title', esc_html_x( 'Reviews', '[Frontend] My account menu title', 'yith-woocommerce-advanced-reviews' ) );

			$items = array_merge( $items_part1, $items_part2 );

			return $items;
		}

		/**
		 * Sets the title of the endpoint.
		 *
		 * @return string
		 * @since 2.0.0
		 */
		public function set_reviews_my_account_endpoint_title(): string {
			global $wp;

			if ( ! empty( $wp->query_vars[ self::$endpoint ] ) ) {
				/* translators: %s: page */
				$title = sprintf( esc_html_x( 'Reviews (page %d)', '[Frontend] My account section title', 'yith-woocommerce-advanced-reviews' ), intval( $wp->query_vars[ self::$endpoint ] ) );
			} else {
				$title = esc_html_x( 'Reviews', '[Frontend] My account section title', 'yith-woocommerce-advanced-reviews' );
			}

			return $title;
		}

		/**
		 * Create content for the new endpoint created.
		 *
		 * @param string $current_page Current page number.
		 * @param bool   $in_shortcode Check if the function is used by the shortcode (Optional).
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function my_account_content( string $current_page, bool $in_shortcode = false ) {

			$current_page = empty( $current_page ) ? 1 : absint( $current_page );
			/**
			 * APPLY_FILTERS: yith_ywar_reviews_page_size_my_account
			 *
			 * Page size of the user reviews list.
			 *
			 * @param int '10' by default.
			 */
			$page_size = apply_filters( 'yith_ywar_reviews_page_size_my_account', 10 );
			$reviews   = yith_ywar_get_reviews(
				array(
					'posts_per_page' => $page_size,
					'paginate'       => true,
					'paged'          => $current_page,
					'post_status'    => 'ywar-approved',
					'post_parent'    => 0,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						array(
							'key'     => '_ywar_review_user_id',
							'value'   => wp_get_current_user()->ID,
							'compare' => '=',
						),
					),
					'order_by'       => array( 'date' => 'DESC' ),
				)
			);

			$args = array(
				'reviews'      => $reviews,
				'in_shortcode' => $in_shortcode,
				'current_page' => $current_page,
				'next'         => wc_get_endpoint_url( self::$endpoint, $current_page + 1 ),
				'prev'         => wc_get_endpoint_url( self::$endpoint, $current_page - 1 ),

			);
			yith_ywar_get_view( 'frontend/my-account/user-reviews.php', $args );
		}

		/**
		 * Load user reviews
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function load_user_reviews() {
			$page = ! empty( $_POST['page'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['page'] ) ) ) : 1; //phpcs:ignore WordPress.Security.NonceVerification.Missing

			ob_start();
			$this->my_account_content( $page, true );
			$html = ob_get_clean();
			wp_send_json_success( $html );
		}
	}
}
