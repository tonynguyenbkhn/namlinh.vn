<?php
/**
 * Plugin Utility Functions
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'ywrvp_campaign_build_link' ) ) {
	/**
	 * Build link of the product with Google Analytics options
	 *
	 * @since 1.0.4
	 * @param string $link The product permalink.
	 * @return string
	 */
	function ywrvp_campaign_build_link( $link ) {

		if ( 'yes' === get_option( 'yith-wrvp-enable-analytics', 'no' ) ) {

			$campaign_source  = str_replace( ' ', '%20', get_option( 'yith-wrvp-campaign-source' ) );
			$campaign_medium  = str_replace( ' ', '%20', get_option( 'yith-wrvp-campaign-medium' ) );
			$campaign_term    = str_replace( ',', '+', get_option( 'yith-wrvp-campaign-term' ) );
			$campaign_content = str_replace( ' ', '%20', get_option( 'yith-wrvp-campaign-content' ) );
			$campaign_name    = str_replace( ' ', '%20', get_option( 'yith-wrvp-campaign-name' ) );

			$query_args = array(
				'utm_source' => $campaign_source,
				'utm_medium' => $campaign_medium,
			);

			if ( '' !== $campaign_term ) {
				$query_args['utm_term'] = $campaign_term;
			}

			if ( '' !== $campaign_content ) {
				$query_args['utm_content'] = $campaign_content;
			}

			$query_args['utm_name'] = $campaign_name;
			$link                   = add_query_arg( $query_args, $link );
		}

		/**
		 * APPLY_FILTERS: yith_wrvp_campaign_build_link
		 *
		 * Filters the product link with the Google Analytics options.
		 *
		 * @param string $link Link.
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wrvp_campaign_build_link', $link );
	}
}

if ( ! function_exists( 'ywrvp_parse_with_default' ) ) {
	/**
	 * Parse args with default options for options type
	 *
	 * @since 1.2.0
	 * @param array $data An array of options data.
	 * @return array
	 * @deprecated
	 */
	function ywrvp_parse_with_default( $data ) {

		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		return wp_parse_args( $data, $defaults );
	}
}

if ( ! function_exists( 'yith_wrvp_get_mail_copuon_code_html' ) ) {
	/**
	 * Get coupon code html
	 *
	 * @since 1.2.0
	 * @param string $coupon_code The coupon code.
	 * @return string
	 */
	function yith_wrvp_get_mail_copuon_code_html( $coupon_code ) {

		if ( ! $coupon_code ) {
			return '';
		}

		/**
		 * APPLY_FILTERS: yith_wrvp_coupon_code_image_email
		 *
		 * Filters the URL of the coupon code image in the email.
		 *
		 * @param string $image_url   Image URL.
		 * @param string $coupon_code Coupon code.
		 *
		 * @return string
		 */
		$coupon_image = apply_filters( 'yith_wrvp_coupon_code_image_email', YITH_WRVP_ASSETS_URL . '/images/coupon-code.png', $coupon_code );

		ob_start();
		?>
		<div id="coupon-code">
			<span style="background-image: url('<?php echo esc_url( $coupon_image ); ?>');"><?php echo esc_html( $coupon_code ); ?></span>
		</div>
		<?php
		$return = ob_get_clean();

		/**
		 * APPLY_FILTERS: yith_wrvp_coupon_code_html_email
		 *
		 * Filters the HTML content for the coupon code in the email.
		 *
		 * @param string $return       HTML content.
		 * @param string $coupon_code  Coupon code.
		 * @param string $coupon_image Coupon image.
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wrvp_coupon_code_html_email', $return, $coupon_code, $coupon_image );
	}
}

if ( ! function_exists( 'yith_wrvp_get_mail_product_image' ) ) {
	/**
	 * Get product image html for plugin email
	 *
	 * @since 1.2.0
	 * @param WC_Product $product The product instance.
	 * @param string     $product_link The product permalink.
	 * @return string
	 */
	function yith_wrvp_get_mail_product_image( $product, $product_link = '' ) {

		if ( ! $product_link ) {
			$product_link = $product->get_permalink();
		}

		/**
		 * APPLY_FILTERS: yith_wrvp_email_image_size
		 *
		 * Filters the size to use for the product image in the email.
		 *
		 * @param string $image_size Image size.
		 *
		 * @return string
		 */
		$size       = apply_filters( 'yith_wrvp_email_image_size', 'ywrvp_image_size' );
		$dimensions = ( 'ywrvp_image_size' === $size && get_option( 'yith-wrvp-image-size', '' ) ) ? get_option( 'yith-wrvp-image-size' ) : wc_get_image_size( $size );
		// Get image id.
		$image_id = is_callable( array( $product, 'get_image_id' ) ) ? $product->get_image_id() : get_post_thumbnail_id( $product );
		// Build image html.
		$src   = ( $image_id && wp_get_attachment_image_src( $image_id, $size ) ) ? current( wp_get_attachment_image_src( $image_id, $size ) ) : wc_placeholder_img_src();
		$image = '<a href="' . esc_url( $product_link ) . '"><img src="' . esc_url( $src ) . '" height="' . esc_attr( $dimensions['height'] ) . '" width="' . esc_attr( $dimensions['width'] ) . '" /></a>';

		/**
		 * APPLY_FILTERS: yith_wrvp_get_mail_product_image_filter
		 *
		 * Filters the HTML content to show the product image in the email.
		 *
		 * @param string     $image        HTML content for the image.
		 * @param WC_Product $product      Product object.
		 * @param string     $product_link Product link.
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wrvp_get_mail_product_image_filter', $image, $product, $product_link );
	}
}

if ( ! function_exists( 'yith_wrvp_set_transient' ) ) {
	/**
	 * Set transient using custom function or WP function
	 *
	 * @since 1.5.0
	 * @param string         $transient The transient key.
	 * @param mixed          $value The transient label.
	 * @param string|integer $expire The expire date.
	 * @deprecated
	 */
	function yith_wrvp_set_transient( $transient, $value, $expire ) {
		set_transient( $transient, $value, $expire );
	}
}

if ( ! function_exists( 'yith_wrvp_get_transient' ) ) {
	/**
	 * Get transient using custom function or WP function
	 *
	 * @since 1.5.0
	 * @param string $transient The transient key.
	 * @return mixed
	 * @depreacted
	 */
	function yith_wrvp_get_transient( $transient ) {
		return get_transient( $transient );
	}
}

if ( ! function_exists( 'yith_wrvp_get_categories_list' ) ) {
	/**
	 * Get a list of product categories
	 *
	 * @since 1.4.5
	 * @return array
	 */
	function yith_wrvp_get_categories_list() {

		$transient_name = 'yith_wrvp_categories_list';
		$categories     = yith_wrvp_get_transient( $transient_name );
		if ( false === $categories ) {
			$categories = array();
			$terms      = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
				)
			);

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$categories[ $term->term_id ] = $term->name;
				}
			}

			yith_wrvp_set_transient( $transient_name, $categories, WEEK_IN_SECONDS );
		}

		return $categories;
	}
}
