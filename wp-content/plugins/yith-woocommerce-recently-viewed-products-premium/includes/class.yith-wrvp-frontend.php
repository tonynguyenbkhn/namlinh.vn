<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Frontend class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WRVP_Frontend' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the frontend behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WRVP_Frontend {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var YITH_WRVP_Frontend
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = YITH_WRVP_VERSION;

		/**
		 * Product list
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $products_list = array();

		/**
		 * List of product processed in same execution
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $execution_done = array();

		/**
		 * Current user id
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $user_id = '';

		/**
		 * The name of cookie name
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $cookie_name = 'yith_wrvp_products_list';

		/**
		 * The name of meta products list
		 *
		 * @since 1.0.0
		 * @var string
		 */
		protected $meta_products_list = 'yith_wrvp_products_list';

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 * @return YITH_WRVP_Frontend
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

			add_action( 'init', array( $this, 'init' ), 1 );

			add_shortcode( 'yith_similar_products', array( $this, 'similar_products' ) );

			add_action( 'template_redirect', array( $this, 'track_user_viewed_produts' ), 99 );
			add_action( 'woocommerce_before_single_product', array( $this, 'track_user_viewed_produts' ), 99 );

			add_action( 'woocommerce_after_single_product_summary', array( $this, 'print_shortcode' ), 30 );
		}

		/**
		 * Init plugin
		 *
		 * @since 1.0.0
		 * @access public
		 */
		public function init() {
			$this->user_id = get_current_user_id();

			// populate the list of products.
			$this->populate_list();
		}

		/**
		 * Populate user list
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function populate_list() {

			if ( ! $this->user_id ) {
				$this->products_list = isset( $_COOKIE[ $this->cookie_name ] ) ? json_decode( sanitize_text_field( wp_unslash( $_COOKIE[ $this->cookie_name ] ) ), true ) : array();
			} else {
				$meta                = get_user_meta( $this->user_id, $this->meta_products_list, true );
				$this->products_list = ! empty( $meta ) ? $meta : array();
			}

			if ( ! is_array( $this->products_list ) ) {
				$this->products_list = array();
			}
		}

		/**
		 * Track user viewed products
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function track_user_viewed_produts() {

			global $post;

			if ( is_null( $post ) || 'product' !== $post->post_type || ! is_product() || in_array( $post->ID, $this->execution_done, true ) ) {
				return;
			}

			$product_id = intval( $post->ID );
			$product    = wc_get_product( $product_id );
			if ( ! $product || ( get_option( 'yith-wrvp-hide-out-of-stock' ) === 'yes' && ! $product->is_in_stock() ) ) {
				return;
			}

			// if product is in list, remove it.
			/**
			 * APPLY_FILTERS: yith_wrvp_track_product_views
			 *
			 * Filters whether to track product views.
			 *
			 * @param bool               $track_product_views Whether to track product views or not.
			 * @param YITH_WRVP_Frontend $instance            Class instance.
			 *
			 * @return bool
			 */
			$key = array_search( $product_id, $this->products_list, true );
			if ( false !== $key ) {
				unset( $this->products_list[ $key ] );
			} elseif ( apply_filters( 'yith_wrvp_track_product_views', true, $this ) ) {
				global $_wp_suspend_cache_invalidation;
				$suspend_cache = empty( $_wp_suspend_cache_invalidation );
				// suspend cache invalidation.
				$suspend_cache && wp_suspend_cache_invalidation();

				$views = $product->get_meta( '_ywrvp_views', true );
				$views = ! $views ? 1 : intval( $views ) + 1;
				$product->update_meta_data( '_ywrvp_views', $views );
				$product->save();

				// restore cache invalidation.
				$suspend_cache && wp_suspend_cache_invalidation( false );
			}

			$timestamp = time();

			/**
			 * APPLY_FILTERS: yith_wrvp_track_product
			 *
			 * Filters the product ID to track.
			 *
			 * @param int $product_id Product ID.
			 *
			 * @return int
			 */
			$this->products_list[ $timestamp ] = apply_filters( 'yith_wrvp_track_product', $product_id );
			$this->execution_done[]            = $product_id;

			// set cookie and save meta.
			$this->set_cookie_meta();
		}

		/**
		 * Set cookie and save user meta with products list
		 *
		 * @access protected
		 * @since 1.0.0
		 */
		public function set_cookie_meta() {
			$duration = get_option( 'yith-wrvp-cookie-time' );
			$duration = time() + ( 86400 * $duration );

			// if user also exists add meta with products list.
			if ( $this->user_id ) {
				update_user_meta( $this->user_id, $this->meta_products_list, $this->products_list );
			} else {
				// set cookie.
				setcookie( $this->cookie_name, wp_json_encode( $this->products_list ), $duration, COOKIEPATH, COOKIE_DOMAIN, false, true );
			}
		}

		/**
		 * Get list of similar products based on user chronology
		 *
		 * @access public
		 * @since 1.0.0
		 * @param array  $cats_array Array of categories.
		 * @param string $similar_type Similar product type.
		 * @param array  $products_list Products list.
		 * @return mixed
		 */
		public function get_similar_products( $cats_array = array(), $similar_type = '', $products_list = array() ) {
			if ( empty( $products_list ) ) {
				$products_list = $this->products_list;
			}

			return YITH_WRVP_Helper::get_similar_products( $cats_array, $similar_type, $products_list );
		}

		/**
		 * Get products terms
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string  $term_name Term name.
		 * @param boolean $with_name Get term name or not.
		 * @param array   $products_list Products list.
		 * @return array
		 */
		protected function get_list_terms( $term_name, $with_name = false, $products_list = array() ) {
			if ( empty( $products_list ) ) {
				$products_list = $this->products_list;
			}

			return YITH_WRVP_Helper::get_list_terms( $term_name, $with_name, $products_list );
		}

		/**
		 * Query build for get similar products
		 *
		 * @access public
		 * @since 1.0.0
		 * @param array $cats_array Array of categories.
		 * @param array $tags_array Array of tags.
		 * @param array $excluded Array of products ID to exclude.
		 * @return array
		 */
		protected function build_query( $cats_array, $tags_array, $excluded ) {
			return YITH_WRVP_Helper::build_query( $cats_array, $tags_array, $excluded );
		}

		/**
		 * Shortcode similar products
		 *
		 * @access public
		 * @since 1.0.0
		 * @param mixed $atts Shortcode attributes.
		 * @return mixed
		 */
		public function similar_products( $atts ) {

			$num_products = get_option(
				'yith-wrvp-num-products',
				array(
					'total'   => 6,
					'per-row' => 4,
				)
			);

			extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				shortcode_atts(
					array(
						'num_post' => $num_products['total'],
						'order'    => 'rand',
						'title'    => get_option( 'yith-wrvp-section-title' ),
					),
					$atts
				)
			);

			$similar_products = $this->get_similar_products();

			if ( empty( $similar_products ) ) {
				return '';
			}

			$args = array(
				'post_type'           => 'product',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'posts_per_page'      => $num_post,
				'orderby'             => $order,
				'post__in'            => $products,
			);

			// set visibility query.
			$args = yit_product_visibility_meta( $args );
			// then let's third part filter args array.
			/**
			 * APPLY_FILTERS: yith_wrvp_similar_products_template_args
			 *
			 * Filters the array with the arguments needed for the products list template.
			 *
			 * @param array $args Array of arguments.
			 *
			 * @return array
			 */
			$args = apply_filters( 'yith_wrvp_similar_products_template_args', $args );

			$products = new WP_Query( $args );

			ob_start();

			if ( $products->have_posts() ) : ?>

				<div class="woocommerce yith-similar-products">

					<h2><?php echo esc_html( $title ); ?></h2>

					<?php woocommerce_product_loop_start(); ?>

					<?php
					while ( $products->have_posts() ) :
						$products->the_post();
						?>

						<?php wc_get_template_part( 'content', 'product' ); ?>

					<?php endwhile; // end of the loop. ?>

					<?php woocommerce_product_loop_end(); ?>

				</div>

				<?php
			endif;

			$content = ob_get_clean();

			wp_reset_postdata();

			return $content;
		}

		/**
		 * Print shortcode similar products on single product page based on user viewed products
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function print_shortcode() {

			if ( get_option( 'yith-wrvp-show-on-single', 'yes' ) === 'yes' ) {
				echo do_shortcode( '[yith_similar_products]' );
			}
		}
	}
}
/**
 * Unique access to instance of YITH_WRVP_Frontend class
 *
 * @since 1.0.0
 * @return YITH_WRVP_Frontend
 */
function YITH_WRVP_Frontend() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WRVP_Frontend::get_instance();
}
