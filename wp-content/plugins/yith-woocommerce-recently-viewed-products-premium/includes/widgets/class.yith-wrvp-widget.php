<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * YITH WooCommerce Recently Viewed Products List Widget
 *
 * @author        YITH <plugins@yithemes.com>
 * @category      Widgets
 * @package       YITH WooCommerce Recently Viewed Products
 * @version       1.0.0
 * @extends    WP_Widget
 */

if ( ! defined( 'YITH_WRVP' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WRVP_Widget' ) ) {

	/**
	 * YITH_WRVP_Widget class
	 */
	class YITH_WRVP_Widget extends WP_Widget {

		/**
		 * Constructor
		 */
		public function __construct() {
			$widget_ops = array(
				'classname'   => 'woocommerce yith-wrvp-widget widget_products',
				'description' => __( 'The widget shows the list of products added in the compare table.', 'yith-woocommerce-recently-viewed-products' ),
			);

			parent::__construct( 'yith-wrvp-widget', __( 'YITH WooCommerce Recently Viewed Products Widget', 'yith-woocommerce-recently-viewed-products' ), $widget_ops );
		}

		/**
		 * Widget function.
		 *
		 * @param array $args Widget args.
		 * @param array $instance Wdiget instance.
		 * @return void
		 * @see WP_Widget
		 * @access public
		 */
		public function widget( $args, $instance ) {

			global $post;

			ob_start();

			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'YITH Recently Viewed Products', 'yith-woocommerce-recently-viewed-products' ) : $instance['title'], $instance, $this->id_base );

			// Get similar products.
			/**
			 * APPLY_FILTERS: yith_wrvp_widget_product_list
			 *
			 * Filters the products list to use in the widget.
			 *
			 * @param array $products_list Products list.
			 *
			 * @return array
			 */
			$products_list = apply_filters( 'yith_wrvp_widget_product_list', ( isset( $instance['prod_type'] ) && 'similar' === $instance['prod_type'] ) ? YITH_WRVP_Frontend_Premium()->get_similar_products() : YITH_WRVP_Frontend_Premium()->get_the_products_list() );

			// Remove current product from products list.
			if ( $post && 'product' === get_post_type( $post ) && is_product() ) {
				$product_id = intval( $post->ID );
				$key        = array_search( $product_id, $products_list, true );

				if ( false !== $key ) {
					unset( $products_list[ $key ] );
				}
			}

			if ( empty( $products_list ) ) {
				/**
				 * APPLY_FILTERS: yith_wrvp_widget_contentesc_html_empty_html
				 *
				 * Filters the HTML content in the widget when there are not products in the list.
				 *
				 * @param string           $content  HTML content.
				 * @param array            $instance Widget instance.
				 * @param YITH_WRVP_Widget $widget   Widget object.
				 *
				 * @return string
				 */
				$content = apply_filters( 'yith_wrvp_widget_contentesc_html_empty_html', ob_get_clean(), $instance, $this );

				echo wp_kses_post( $content );

				return;
			}

			// Sort array.
			krsort( $products_list );

			$query_args = array(
				'post_type'           => 'product',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'posts_per_page'      => isset( $instance['num_prod'] ) ? absint( $instance['num_prod'] ) : 5,
				'post__in'            => $products_list,
				'order'               => 'DESC',
			);

			$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : '';

			switch ( $orderby ) {
				case 'sales':
					$query_args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$query_args['orderby']  = 'meta_value_num';
					break;
				case 'rand':
					$query_args['orderby'] = 'rand';
					break;
				case 'newest':
					$query_args['orderby'] = 'date';
					break;
				case 'high-low':
					$query_args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$query_args['orderby']  = 'meta_value_num';
					break;
				case 'low-high':
					$query_args['meta_key'] = '_price'; // phpcs:ignore WordPress.DB.SlowDBQuery
					$query_args['orderby']  = 'meta_value_num';
					$query_args['order']    = 'ASC';
					break;
				default:
					$query_args['orderby'] = 'post__in';
					break;
			}

			// visibility condition.
			$query_args = yit_product_visibility_meta( $query_args );

			$results = new WP_Query( $query_args );

			if ( $results->have_posts() ) {

				echo wp_kses_post( $before_widget );

				if ( $title ) {
					echo wp_kses_post( $before_title ) . wp_kses_post( $title ) . wp_kses_post( $after_title );
				}

				echo '<div class="clear"></div>';

				/**
				 * APPLY_FILTERS: woocommerce_before_ywrvp_widget_product_list
				 *
				 * Filters the HTML content before the widget.
				 *
				 * @param string $before_widget HTML before the widget content.
				 *
				 * @return string
				 */
				echo wp_kses_post( apply_filters( 'woocommerce_before_ywrvp_widget_product_list', '<ul class="product_list_widget">' ) );

				while ( $results->have_posts() ) {

					$results->the_post();

					/**
					 * Fix issue with Visual Composer: print reviews template
					 */
					$results->post->comment_status = false;

					wc_get_template( 'content-widget-product.php' );
				}

				/**
				 * APPLY_FILTERS: woocommerce_after_ywrvp_widget_product_list
				 *
				 * Filters the HTML content after the widget.
				 *
				 * @param string $after_widget HTML after the widget content.
				 *
				 * @return string
				 */
				echo wp_kses_post( apply_filters( 'woocommerce_after_ywrvp_widget_product_list', '</ul>' ) );

				echo wp_kses_post( $after_widget );

				wp_reset_postdata();

			}

			$content = ob_get_clean();

			echo wp_kses_post( $content );
		}


		/**
		 * Update function.
		 *
		 * @param array $new_instance New widget instance.
		 * @param array $old_instance Current widget instance.
		 * @return array
		 * @see WP_Widget->update
		 * @access public
		 */
		public function update( $new_instance, $old_instance ) {
			$instance             = $old_instance;
			$instance['title']    = wp_strip_all_tags( $new_instance['title'] );
			$instance['num_prod'] = intval( $new_instance['num_prod'] );
			$instance['orderby']  = $new_instance['orderby'];

			$instance['prod_type'] = $new_instance['prod_type'];

			return $instance;
		}

		/**
		 * Form function.
		 *
		 * @param array $instance Widget instance.
		 * @return void
		 * @see WP_Widget->form
		 * @access public
		 */
		public function form( $instance ) {

			$defaults = array(
				'title'     => __( 'You may be interested in', 'yith-woocommerce-recently-viewed-products' ),
				'num_prod'  => 4,
				'orderby'   => 'viewed',
				'prod_type' => 'similar',
			);

			$instance = wp_parse_args( $instance, $defaults );

			?>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'yith-woocommerce-recently-viewed-products' ); ?>
					<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
							value="<?php echo esc_attr( $instance['title'] ); ?>"/>
				</label>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'num_prod' ) ); ?>"><?php esc_html_e( 'Number of products', 'yith-woocommerce-recently-viewed-products' ); ?>
					<br><input type="number" id="<?php echo esc_attr( $this->get_field_id( 'num_prod' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'num_prod' ) ); ?>"
							value="<?php echo esc_attr( $instance['num_prod'] ); ?>" min="0"/>
				</label>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order by', 'yith-woocommerce-recently-viewed-products' ); ?>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
						<option value="viewed" <?php selected( $instance['orderby'], 'rand' ); ?>><?php esc_html_e( 'Viewed Order', 'yith-woocommerce-recently-viewed-products' ); ?></option>
						<option value="rand" <?php selected( $instance['orderby'], 'rand' ); ?>><?php esc_html_e( 'Random', 'yith-woocommerce-recently-viewed-products' ); ?></option>
						<option value="newest" <?php selected( $instance['orderby'], 'rand' ); ?>><?php esc_html_e( 'Newest', 'yith-woocommerce-recently-viewed-products' ); ?></option>
						<option value="sales" <?php selected( $instance['orderby'], 'sales' ); ?>><?php esc_html_e( 'Sales', 'yith-woocommerce-recently-viewed-products' ); ?></option>
						<option value="high-low" <?php selected( $instance['orderby'], 'high-low' ); ?>><?php esc_html_e( 'Price: High to Low', 'yith-woocommerce-recently-viewed-products' ); ?></option>
						<option value="low-high" <?php selected( $instance['orderby'], 'low-high' ); ?>><?php esc_html_e( 'Price: Low to High', 'yith-woocommerce-recently-viewed-products' ); ?></option>
					</select>
				</label>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'prod_type' ) ); ?>"><?php esc_html_e( 'Products to show', 'yith-woocommerce-recently-viewed-products' ); ?>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'prod_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'prod_type' ) ); ?>">
						<option value="viewed" <?php selected( $instance['prod_type'], 'rand' ); ?>><?php esc_html_e( 'Only viewed products', 'yith-woocommerce-recently-viewed-products' ); ?></option>
						<option value="similar" <?php selected( $instance['prod_type'], 'sales' ); ?>><?php esc_html_e( 'Include similar products', 'yith-woocommerce-recently-viewed-products' ); ?></option>
					</select>
				</label>
			</p>

			<?php
		}
	}
}
