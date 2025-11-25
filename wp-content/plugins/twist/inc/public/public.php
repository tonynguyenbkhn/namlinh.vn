<?php

class WpgsPublicCode {

	/**
	 * @var boolen
	 */
	public $gallery_carousel_mode;
	private $wpgs_variation_images;
	/**
	 * The unique instance of the plugin.
	 */
	private static $instance;

		/**
		 * Gets an instance of our plugin.
		 *
		 * @return Class Instance.
		 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	private function __construct() {
		$this->gallery_carousel_mode = apply_filters( 'wpgs_carousel_mode', true );
		add_action( 'wp_enqueue_scripts', array( $this, 'wpgs_enqueue_files' ), 10 );
		add_action( 'elementor_twist_preview_scripts', array( $this, 'wpgs_enqueue_files' ) );

		if ( cix_wpgs::option( 'slider_lazy_laod', 'disable' ) != 'disable' && $this->gallery_carousel_mode ) {
			// Remove SRC FROM GALLERY IMAGES because we have data-lazy attr for render
			add_filter( 'wpgs_lazyload_src', 'wpgs_remove_src' );
			function wpgs_remove_src( $args ) {
				return esc_url( wc_placeholder_img_src( 'woocommerce_single' ) );
			}
		}
		$this->wpgs_variation_images = \WPGS_Variation_images::init();
		$this->wpgs_variation_images->init_actions();
		add_action( 'wp_ajax_twist_variation_ajax', array( $this, 'product_variation' ) );
		add_action( 'wp_ajax_nopriv_twist_variation_ajax', array( $this, 'product_variation' ) );
	}

	/**
	 * @return null
	 */
	public function wpgs_enqueue_files() {

		$product_id = apply_filters( 'wpgs_product_id', get_the_ID() );
		global $post;

		if ( $post && has_shortcode( $post->post_content, 'product_page' ) ) {
			// The page has the 'product_page' shortcode
			/**
			 * Extracts the product ID from the post content if a shortcode is being used.
			 *
			 * @param WP_Post $post The post object.
			 * @return void
			 */
			$pattern = '/\[product_page\s+id="(\d+)"\]/';
			preg_match( $pattern, $post->post_content, $matches );
			if ( isset( $matches[1] ) ) {

				$product_id = apply_filters( 'wpgs_product_id', $matches[1] );
			}
		}

		if ( 'product' !== get_post_type( $product_id ) ) {
			return;
		}

		$twist_product  = new WC_Product( $product_id );
		$attachment_ids = $twist_product->get_gallery_image_ids();

		/* Plugin Options */
		$lightbox                          = ( cix_wpgs::option( 'lightbox_picker' ) == 1 ) ? 'true' : 'false';
		$lightbox_bg                       = cix_wpgs::option( 'lightbox_bg' );
		$lightbox_txt_color                = cix_wpgs::option( 'lightbox_txt_color' );
		$icon_bg_color                     = cix_wpgs::option( 'lightbox_icon_bg_color' );
		$icon_link_color                   = cix_wpgs::option( 'lightbox_icon_color' );
		$slider_rtl                        = ( is_rtl() ) ? 'true' : 'false';
		$slider_dragging                   = ( cix_wpgs::option( 'slider_dragging' ) == 1 ) ? 'true' : 'false';
		$slider_infinity                   = ( cix_wpgs::option( 'slider_infinity' ) == 1 ) ? 'true' : 'false';
		$slider_adaptiveHeight             = ( cix_wpgs::option( 'slider_adaptiveHeight' ) == 1 ) ? 'true' : 'false';
		$slider_nav                        = ( cix_wpgs::option( 'slider_nav' ) == 1 ) ? 'true' : 'false';
		$slider_nav_animation              = ( cix_wpgs::option( 'slider_nav_animation' ) == 1 ) ? 'true' : 'false';
		$slider_nav_bg                     = cix_wpgs::option( 'slider_nav_bg' );
		$slider_nav_icon                   = cix_wpgs::option( 'slider_nav_color' );
		$slider_icon                       = cix_wpgs::option( 'slider_icon' );
		$slider_animation                  = ( cix_wpgs::option( 'slider_animation' ) );
		$slider_animation_speed            = ( cix_wpgs::option( 'gallery_animation_speed', '500' ) );
		$thumbnail_animation_speed         = ( cix_wpgs::option( 'thumbnail_animation_speed', '500' ) );
		$slider_lazyload                   = cix_wpgs::option( 'slider_lazy_laod', 'disable' );
		$slider_autoplay                   = ( cix_wpgs::option( 'slider_autoplay' ) == 1 ) ? 'true' : 'false';
		$slider_autoplay_time              = cix_wpgs::option( 'autoplay_timeout', '4000' );
		$slider_autoplay_pause_on_hover    = ( cix_wpgs::option( 'slider_autoplay_pause' ) == 1 ) ? 'true' : 'false';
		$zoom                              = ( cix_wpgs::option( 'image_zoom' ) == 1 ) ? 'true' : 'false';
		$thumbnails_active                 = ( cix_wpgs::option( 'thumbnails' ) == 1 ) ? 'true' : 'false';
		$thumbnails_id                     = ( 'true' == $thumbnails_active ? '\'.wpgs-thumb\'' : 'false' );
		$thumb_to_show                     = cix_wpgs::option( 'thumb_to_show' );
		$thumb_scroll_by                   = cix_wpgs::option( 'thumb_scroll_by' );
		$thumbnails_mobile_thumb_to_show   = cix_wpgs::option( 'thumbnails_mobile_thumb_to_show' );
		$thumbnails_mobile_thumb_scroll_by = cix_wpgs::option( 'thumbnails_mobile_thumb_scroll_by' );
		$thumbnails_tabs_thumb_to_show     = cix_wpgs::option( 'thumbnails_tabs_thumb_to_show' );
		$thumbnails_tabs_thumb_scroll_by   = cix_wpgs::option( 'thumbnails_tabs_thumb_scroll_by' );
		$thumb_position                    = cix_wpgs::option( 'thumb_position' );
		$thumb_position_mobile             = cix_wpgs::option( 'thumbnails_mobile_thumb_position' );
		$thumb_position_tablet             = cix_wpgs::option( 'thumbnails_tabs_thumb_position' );
		$thumbnails_style                  = cix_wpgs::option( 'thumbnails_layout' );
		$slider_dots                       = ( cix_wpgs::option( 'dots' ) == 1 ) ? 'true' : 'false';
		$wpgs_setting_css                  = ''; // CSS

		if ( apply_filters( 'wpgs_enqueue_slick_js', true ) ) {
			wp_enqueue_script( 'slick', WPGS_ROOT_URL . 'assets/js/slick.min.js', array( 'jquery' ), WPGS_VERSION, false );
		}

		if ( 'true' == $lightbox ) {
			$fp_deps = null;
			if ( defined( 'PORTO_VERSION' ) ) {
				$fp_deps = array( 'porto-plugins' );
			} // Fix fancybox conflict with porto theme

			wp_enqueue_script( 'fancybox', WPGS_ROOT_URL . 'assets/js/jquery.fancybox.min.js', array( 'jquery' ), WPGS_VERSION, false );
			wp_enqueue_style( 'fancybox', WPGS_ROOT_URL . 'assets/css/jquery.fancybox.min.css', $fp_deps, WPGS_VERSION );
		}

		// Check if zoom is enable

		$mobile_zoom = ( cix_wpgs::option( 'image_zoom_mobile' ) == 1 ) ? 'true' : 'false';
		if ( 'true' == $zoom && cix_wpgs::option( 'image_zoom_mode' ) != 'inner' ) {
			// TODO: add more options later
			// wp_enqueue_script( 'ez-plus', WPGS_ROOT_URL . 'assets/js/jquery.ez-plus.js', array( 'jquery' ), WPGS_VERSION, false );
		} elseif ( 'true' == $zoom && cix_wpgs::option( 'image_zoom_mode' ) == 'inner' ) {
			wp_enqueue_script( 'twist-imageZoom', WPGS_ROOT_URL . 'assets/js/imageZoom.js', array( 'jquery' ), WPGS_VERSION, false );
		}

			wp_dequeue_script( 'photoswipe-ui-default' );
			wp_dequeue_script( 'photoswipe' );
			wp_dequeue_style( 'photoswipe' );
			wp_dequeue_style( 'photoswipe-default-skin' );

			wp_enqueue_script( 'wpgs-public', WPGS_ROOT_URL . 'assets/js/public.js', array( 'jquery', 'slick' ), WPGS_VERSION, true );
			$variableWidth = false;
			$centerMode    = false;

		if ( count( $attachment_ids ) + 1 > 2 && count( $attachment_ids ) + 1 < $thumb_to_show - 1 && 'bottom' == $thumb_position ) {
			$variableWidth = true;
			$centerMode    = true;
		}

			// Localize the script with new data
			$wpgs_js_data = array(
				'thumb_axis'                        => cix_wpgs::option( 'lightbox_thumb_axis', 'y' ),
				'thumb_autoStart'                   => cix_wpgs::option( 'lightbox_thumb_autoStart', '' ),
				'variation_mode'                    => cix_wpgs::option( 'variation_slide', '' ),
				'zoom'                              => cix_wpgs::option( 'image_zoom', 0 ),
				'zoom_action'                       => cix_wpgs::option( 'image_zoom_action', 'mouseover' ),
				'zoom_action'                       => cix_wpgs::option( 'image_zoom_action', 'mouseover' ),
				'zoom_level'                        => cix_wpgs::option( 'image_zoom_level', '1' ),
				'lightbox_icon'                     => cix_wpgs::option( 'lightbox_icon' ),
				'thumbnails_lightbox'               => cix_wpgs::option( 'thumbnails_lightbox' ),
				'slider_caption'                    => cix_wpgs::option( 'slider_caption' ),
				'mobile_zoom'                       => $mobile_zoom,
				'is_mobile'                         => wp_is_mobile(),
				'ajax_url'                          => admin_url( 'admin-ajax.php', 'relative' ),
				'ajax_nonce'                        => wp_create_nonce( 'wcavi_nonce' ),
				'product_id'                        => $product_id,
				'slider_animation'                  => $slider_animation,
				'thumbnails_id'                     => $thumbnails_id,
				'slider_lazyload'                   => $slider_lazyload,
				'slider_adaptiveHeight'             => $slider_adaptiveHeight,
				'slider_dots'                       => $slider_dots,
				'slider_dots_viewport'              => cix_wpgs::option( 'dots_viewport', array( 'desktop', 'tablet', 'mobile' ) ),
				'slider_rtl'                        => $slider_rtl,
				'slider_infinity'                   => $slider_infinity,
				'slider_dragging'                   => $slider_dragging,
				'slider_nav'                        => $slider_nav,
				'slider_animation_speed'            => $slider_animation_speed,
				'slider_autoplay'                   => $slider_autoplay,
				'slider_autoplay_pause_on_hover'    => $slider_autoplay_pause_on_hover,
				'slider_autoplay_pause_on_hover'    => $slider_autoplay_pause_on_hover,
				'slider_autoplay_time'              => $slider_autoplay_time,
				'thumb_to_show'                     => $thumb_to_show,
				'thumb_scroll_by'                   => $thumb_scroll_by,
				'thumb_v'                           => $thumb_position,
				'variableWidth'                     => apply_filters( 'wpgs_variable_width', $variableWidth ),
				'thumbnails_nav'                    => cix_wpgs::option( 'thumb_nav' ),
				'thumbnail_animation_speed'         => $thumbnail_animation_speed,
				'centerMode'                        => $centerMode,
				'thumb_v_tablet'                    => $thumb_position_tablet,
				'thumbnails_tabs_thumb_to_show'     => $thumbnails_tabs_thumb_to_show,
				'thumbnails_tabs_thumb_scroll_by'   => $thumbnails_tabs_thumb_scroll_by,
				'thumbnails_mobile_thumb_to_show'   => $thumbnails_mobile_thumb_to_show,
				'thumbnails_mobile_thumb_scroll_by' => $thumbnails_mobile_thumb_scroll_by,
				'carousel_mode'                     => cix_wpgs::option( 'thumbnails_lightbox' ),
				'thumb_position_mobile'             => $thumb_position_mobile,
				'variation_data'                    => $this->get_variaton_markup( $product_id ),

			);
			wp_localize_script( 'wpgs-public', 'wpgs_js_data', $wpgs_js_data );
			wp_enqueue_style( 'slick-theme', WPGS_ROOT_URL . 'assets/css/slick-theme.css' );
			wp_enqueue_style( 'slick', WPGS_ROOT_URL . 'assets/css/slick.css' );
			wp_enqueue_style( 'wpgs', WPGS_ROOT_URL . 'assets/css/wpgs-style.css', array(), WPGS_VERSION );

			// deregister scripts
			wp_dequeue_script( 'photoswipe' );
			wp_dequeue_script( 'photoswipe-ui-default' );

			// Inline CSS for WPGS Start

			if ( 'true' == $slider_dots ) {
				$wpgs_setting_css .= '

			.wpgs-dots li button{
				background: ' . cix_wpgs::option( 'dots_color' )['color'] . ';
			}
			.wpgs-dots li button:hover{
				background: ' . cix_wpgs::option( 'dots_color' )['hover'] . ';
			}
			.wpgs-dots li.slick-active button {
				background: ' . cix_wpgs::option( 'dots_color' )['active'] . ';
			}
			';
				if ( cix_wpgs::option( 'dots_placement' ) == 'inside' ) {
					$wpgs_setting_css .= '
				.wpgs-dots{
					bottom: ' . cix_wpgs::option( 'dots_placement_inside_margin' ) . 'px;
				}
				';
				}
				if ( cix_wpgs::option( 'dots_shape' ) == 'circle' ) {
					$wpgs_setting_css .= '
				.wpgs-dots li button{
					border-radius:50px;
				}
				';
				} elseif ( cix_wpgs::option( 'dots_shape' ) == 'line' ) {
					$wpgs_setting_css .= '
				.wpgs-dots li button {
				border-radius:0px;
				width: 16px;
				height: 6px;
				}
				.wpgs-image.slick-dotted {
					margin-bottom: 30px !important;
				}
				.wpgs-dots li {
				width: 16px;
				height: 6px;
				overflow:hidden;
				}
				';
				}
			}

			if ( cix_wpgs::option( 'lightbox_icon' ) == 'none' ) {
				$wpgs_setting_css .= '
			a.woocommerce-product-gallery__lightbox {
				width: 100%;
				height: 100%;
				opacity: 0 !important;
			}
			';
			}
			if ( 'false' == $mobile_zoom && wp_is_mobile() ) {
				$wpgs_setting_css .= '
			a.woocommerce-product-gallery__lightbox {
				display:block !important;
			}
			';
			}

			if ( cix_wpgs::option( 'lightbox_thumb_axis' ) == 'x' ) {
				$wpgs_setting_css .= '
			.fancybox-thumbs {
				top: auto;
				width: auto;
				bottom: 0;
				left: 0;
				right : 0;
				height: 95px;
				padding: 10px 10px 5px 10px;
				box-sizing: border-box;
				background: rgba(0, 0, 0, 0.3);

			}

			.fancybox-show-thumbs .fancybox-inner {
				right: 0;
				bottom: 95px;
			}
			.fancybox-thumbs-x .fancybox-thumbs__list{
			margin:0 auto;
			}

		';
			} else {
				$wpgs_setting_css .= '.fancybox-thumbs{
				width:115px;
			}
			.fancybox-thumbs__list a{
				 max-width: calc(100% - 4px);
				 margin:3px;
			} ';
			}

			// Thumbnails CSS
			if ( ! cix_wpgs::option( 'thumbnails' ) ) {
				$wpgs_setting_css .= '
				.wpgs-thumb{
					display:none;
				}';
			}
			$thumbnails_viewports = ( is_array( cix_wpgs::option( 'thumbnails_viewport' ) ) ) ? cix_wpgs::option( 'thumbnails_viewport', array( 'desktop', 'tablet', 'mobile' ) ) : array();
			if ( ! in_array( 'desktop', $thumbnails_viewports ) ) {
				$wpgs_setting_css .= '
				@media (min-width: 1025px) {
				.wpgs-thumb{
					display:none;
				};
				}';

			}
			if ( ! in_array( 'tablet', $thumbnails_viewports ) ) {
				$wpgs_setting_css .= '
				@media (min-width: 768px) and (max-width: 1024px)  {
				.wpgs-thumb{
					display:none;
				};
				}';

			}
			if ( ! in_array( 'mobile', $thumbnails_viewports ) ) {
				$wpgs_setting_css .= '
				@media only screen and (max-width: 767px){
				.wpgs-thumb{
					display:none;
				};
				}';

			}

			if ( 'left' == $thumb_position || 'right' == $thumb_position && 'true' == $thumbnails_active ) {
				$wpgs_setting_css .= '
			.images.wpgs-wrapper .wpgs-image{
				margin-bottom:0px ;
			}
			@media (min-width: 1025px) {


			.wpgs-image {
				width: 79%;
				float: right;

    			margin-left: 1%;
			}
			.wpgs-thumb {
				width: 20%;
			}
			.thumbnail_image {
				margin: 3px 0px;
			}

			}';
			}
			if ( 'right' == $thumb_position && 'true' == $thumbnails_active ) {
				$wpgs_setting_css .= '
			@media (min-width: 1025px) {

			.wpgs-image {
				float: left;
				margin-left: 0%;
    			margin-right: 1%;
			}
			.wpgs-thumb {
				width: 20%;
				float:right
			}
			}';
			} elseif ( 'left' == $thumb_position && 'true' == $thumbnails_active ) {
				$wpgs_setting_css .= '
			@media (min-width: 1025px) {
			.wpgs-thumb {
				width: 20%;
				float: left;
			}
			}';
			}

			if ( 'true' == $thumbnails_active && 'opacity' == $thumbnails_style ) {
				$wpgs_setting_css .= '

			.thumbnail_image:after{
				background: ' . cix_wpgs::option( 'thumb_non_active_color' ) . ';
			}

			';
			} elseif ( 'true' == $thumbnails_active && 'border' == $thumbnails_style ) {
				// code...
				$wpgs_setting_css .= '

			.thumbnail_image{
				border: 1px solid ' . cix_wpgs::option( 'thumb_border_non_active_color' ) . ' !important;
			}
			.thumbnail_image.slick-current{
				border: 1px solid ' . cix_wpgs::option( 'thumb_border_active_color' ) . '!important;
				box-shadow: 0px 0px 3px 0px ' . cix_wpgs::option( 'thumb_border_active_color' ) . ';
			}

			';
			} else {
			}

			// Slider Navigation css
			if ( 'false' == $slider_nav_animation ) {
				$wpgs_setting_css .= '
			.wpgs-image .slick-prev{
				opacity:1;
				left:0;
			}
			.wpgs-image .slick-next{
				opacity:1;
				right:0;
			}
			';
			}
			$wpgs_setting_css .= "
                 .wpgs-wrapper .slick-prev:before, .wpgs-wrapper .slick-next:before,.wpgs-image button:not(.toggle){

				color: {$slider_nav_icon};
				}
                .wpgs-wrapper .slick-prev,.wpgs-wrapper .slick-next{
				background: {$slider_nav_bg} !important;

				}

				.woocommerce-product-gallery__lightbox {
					 background: {$icon_bg_color};
					 color: {$icon_link_color};
				}

				.fancybox-bg,.fancybox-button{
					background: {$lightbox_bg};
				}
				.fancybox-caption__body,.fancybox-infobar{
					 color: {$lightbox_txt_color};
				}

				.thumbnail_image{
					margin: " . cix_wpgs::option( 'thumb_padding' ) . 'px;
				}
				';

			switch ( $slider_icon ) {
				case 'icon-right-bold':
					$wpgs_setting_css .= "
				[dir='rtl'] .slick-next:before {
					content: '\\e807';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e806';
				}
				.arrow-next:before,
				.slick-next:before{
				content: '\\e806';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e807';
				}
				";

					break;
				case 'icon-right-dir':
					$wpgs_setting_css .= "
				.arrow-next:before,
				.slick-next:before{
				content: '\\e801';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e802';
				}
				[dir='rtl'] .slick-next:before {
					content: '\\e802';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e801';
				}
				";

					break;
				case 'icon-right-open-big':
					$wpgs_setting_css .= "
				.arrow-next:before,
				.slick-next:before{
				content: '\\e804';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e805';
				}
				[dir='rtl'] .slick-next:before {
					content: '\\e805';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e804';
				}
				";
					break;

				default:
					$wpgs_setting_css .= "
				.arrow-next:before,
				.slick-next:before{
				content: '\\e80a';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e80b';
				}
				[dir='rtl'] .slick-next:before {
					content: '\\e80b';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e80a';
				}
				";
			}

			// Thumbnails CSS for min-width: 767px to 1024px
			if ( 'left' == $thumb_position_tablet || 'right' == $thumb_position_tablet ) {
				$wpgs_setting_css .= '

			@media (min-width: 768px) and (max-width: 1024px)  {

			.wpgs-image {
				width: 79%;
				float: right;

    			margin-left: 1%;
			}
			.wpgs-thumb {
				width: 20%;
			}
			.thumbnail_image {
				margin: 3px 0px;
			}

			}';
			}
			if ( 'right' == $thumb_position_tablet ) {
				$wpgs_setting_css .= '
			@media (min-width: 768px) and (max-width: 1024px)  {

			.wpgs-image {
				float: left;
				margin-left: 0%;
    			margin-right: 1%;
			}
			.wpgs-thumb {
				width: 20%;
				float:right
			}
			}';
			} elseif ( 'left' == $thumb_position_tablet ) {
				$wpgs_setting_css .= '
			@media (min-width: 768px) and (max-width: 1024px) {
			.wpgs-thumb {
				width: 20%;
				float: left;
			}
			}';
			}

			// Thumbnails CSS for max-width: 767px
			if ( 'left' == $thumb_position_mobile || 'right' == $thumb_position_mobile ) {
				$wpgs_setting_css .= '
			@media only screen and (max-width: 767px)  {


			.wpgs-image {
				width: 79%;
				float: right;

    			margin-left: 1%;
			}
			.wpgs-thumb {
				width: 20%;
			}
			.thumbnail_image {
				margin: 3px 0px;
			}

			}';
			}
			if ( cix_wpgs::option( 'lightbox_icon' ) == 'none' ) {
				$wpgs_setting_css .= '
			@media only screen and (max-width: 767px)  {

			a.woocommerce-product-gallery__lightbox {
			width: auto !important;
    		height: auto !important;
    		opacity: 1 !important;
			}
			}';

			}

			if ( 'right' == $thumb_position_mobile ) {
				$wpgs_setting_css .= '
			@media only screen and (max-width: 767px)  {

			.wpgs-image {
				float: left;
				margin-left: 0%;
    			margin-right: 1%;
			}
			.wpgs-thumb {
				width: 20%;
				float:right
			}
			}';
			} elseif ( 'left' == $thumb_position_mobile ) {
				$wpgs_setting_css .= '
			@media only screen and (max-width: 767px)  {
			.wpgs-thumb {
				width: 20%;
				float: left;
			}
			}';
			}

			if ( empty( $attachment_ids ) ) {
				$wpgs_setting_css .= '
					.wpgs-dots {
						display:none;
					}
				';

			}

			// Plugin custom css option
			$wpgs_setting_css .= cix_wpgs::option( 'custom_css' );
			wp_add_inline_style( 'wpgs', $wpgs_setting_css );
			// Inline CSS for WPGS END
	}
	public function product_variation() {
		if ( ! DOING_AJAX ) {
			wp_die();
		} // Not Ajax

			// Check for nonce security
			$nonce            = sanitize_text_field( $_POST['nonce'] );
			$product_id       = sanitize_text_field( $_POST['product_id'] );
			$variation_id     = sanitize_text_field( $_POST['variation_id'] );
			$expire_time      = DAY_IN_SECONDS * 7;
			$variation_images = get_post_meta( $variation_id, 'wavi_value', true );

			$product            = wc_get_product( $product_id );
			$variation          = wc_get_product( $variation_id );
			$variation_image_id = $variation->get_image_id();
			$thumbnails         = implode( ',', $product->get_gallery_image_ids() );
			$data               = array();

		if ( ! wp_verify_nonce( $nonce, 'wcavi_nonce' ) ) {
			wp_die( 'Oops! nonce error' );
		}
		$variation_cache_data = $this->wpgs_variation_images->get_cache( 'wpgs_product_variation_' . $product_id );

		if ( ! empty( $variation_images ) ) {
				$variation_markup = $this->wpgs_variation_images->html_markup( $variation_image_id, $variation_images );

		} elseif ( $product->get_image_id() != $variation_image_id ) {

			$variation_markup = $this->wpgs_variation_images->html_markup( $variation_image_id, $thumbnails );

		} else {
			$variation_markup = $this->wpgs_variation_images->html_markup( $product->get_image_id(), $thumbnails );
		}

		if ( $variation_cache_data && ! array_key_exists( $variation_id, $variation_cache_data ) ) {

			$variation_cache_data[ $variation_id ] = $variation_markup;

			set_transient( 'wpgs_product_variation_' . $product_id, $variation_cache_data, apply_filters( 'wpgs_clear_variation_cache', $expire_time ) );

		}
		$data['variation_images'] = $variation_markup;

			wp_send_json_success( $data );
			wp_die(); // this is required to terminate immediately and return a proper response
	}
	/**
	 * @param $product_id
	 * @return mixed
	 */
	public function get_variaton_markup( $product_id ) {

		$variation_cache_data = $this->wpgs_variation_images->get_cache( 'wpgs_product_variation_' . $product_id );
		$data                 = $this->wpgs_variation_images->get_variation_data( $product_id );
		if ( $variation_cache_data ) {
			return $variation_cache_data;
		} else {
			$expire_time = DAY_IN_SECONDS * 7;
			$this->wpgs_variation_images->set_cache( 'wpgs_product_variation_' . $product_id, $data, apply_filters( 'wpgs_clear_variation_cache', $expire_time ) );
			return $data;

		}
	}
}

WpgsPublicCode::init();

if ( ! function_exists( 'wpgs_video_in_inner_gallery_html' ) ) {
	/**
	 * Show Video html markup in product gallery
	 *
	 * @param $product_video_url
	 * @param $type
	 */
	function wpgs_video_in_inner_gallery_html( $product_video_url, $attachment_id, $thumb = false, $size = '' ) {
		$video_id = '';
		if ( strpos( $product_video_url, 'youtube' ) > 0 || strpos( $product_video_url, 'youtu' ) > 0 ) {
			// The YouTube video URL

			// Extract the video ID from the URL

			if ( preg_match( '/[\\?\\&]v=([^\\?\\&]+)/', $product_video_url, $matches ) ) {
				$video_id = $matches[1];
			}
			if ( $thumb ) {
				$thumbnail_url = "https://img.youtube.com/vi/$video_id/maxresdefault.jpg";
				return '<img src="' . $thumbnail_url . '" alt="Video Thumbnail">';
			} else {
				// Construct the embed URL
				$embed_url = 'https://www.youtube.com/embed/' . $video_id;
				return '<div class="wpgs-video-wrapper"><iframe id="wpgs-inner-video-' . $attachment_id . '" loading="lazy" data-skip-lazy="true" class="product_video_iframe" src="' . esc_url( $embed_url ) . '" frameborder="0" allow="autoplay; accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
			}
		} elseif ( strpos( $product_video_url, 'vimeo' ) > 0 ) {
			if ( preg_match( '/vimeo\.com\/([0-9]+)/', $product_video_url, $matches ) ) {
				$video_id = $matches[1];
			}
			if ( $thumb ) {
				// Get thumbnail details using Vimeo Simple API
				$data = file_get_contents( "http://vimeo.com/api/v2/video/$video_id.json" );
				$data = json_decode( $data );

				return '<img src="' . $data[0]->thumbnail_medium . '" alt="Video Thumbnail">';
			} else {
				// Construct the embed URL
				$embed_url = "https://player.vimeo.com/video/$video_id";
				return '<div class="wpgs-video-wrapper"><iframe id="wpgs-inner-video-' . $attachment_id . '" loading="lazy" data-skip-lazy="true"  class="product_video_iframe fitvidsignore" src="' . esc_url( $embed_url ) . '" frameborder="0" allow="autoplay; accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
			}
		} elseif ( $thumb ) {
				$image = wp_get_attachment_image(
					$attachment_id,
					$size,
					false,
					array(

						'alt'        => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
						'class'      => esc_attr( 'img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) ),
						'src'        => apply_filters( 'wpgs_lazyload_src', wp_get_attachment_image_url( $attachment_id, $size ) ),
						'data-lazy'  => wp_get_attachment_image_url( $attachment_id, $size ),
						'data-thumb' => wp_get_attachment_image_url( $attachment_id, $size ),

					),
					$attachment_id
				);
				return $image;
		} else {
			return '<div class="wpgs-video-wrapper-selfhost">
				<video width="100%" controls>
				<source src="' . esc_url( $product_video_url ) . '" type="video/mp4">
				Your browser does not support HTML video.
				</video>
			</div>';
		}
	}
}
if ( ! function_exists( 'wpgs_get_image_gallery_html' ) ) {

	// Custom HTML layout
	/**
	 * @param $attachment_id
	 * @param $main_image
	 */
	function wpgs_get_image_gallery_html( $attachment_id, $main_image = false ) {
		$size = apply_filters( 'wpgs_new_main_img_size', cix_wpgs::option( 'slider_image_size' ) );
		/* Plugin Options */
		$lightbox = ( cix_wpgs::option( 'lightbox_picker' ) == 1 ) ? 'true' : 'false';
		// Zoom Icon
		$zoom_icon_class  = cix_wpgs::option( 'lightbox_icon' );
		$lightbox_img_alt = ( cix_wpgs::option( 'lightbox_alt_text' ) == 1 ) ? 'true' : 'false';
		$img_caption      = ( cix_wpgs::option( 'slider_caption' ) == 'caption' ) ? wp_get_attachment_caption( $attachment_id ) : get_the_title( $attachment_id );
		( 'true' == $lightbox_img_alt ) ? $img_caption : $img_caption = '';
		// Check if Gallery have Video URL

		$zoom_image_size = cix_wpgs::option( 'zoom_image_size', 'large' );

		$lightbox_animation        = cix_wpgs::option( 'lightbox_oc_effect' );
		$lightbox_slides_animation = cix_wpgs::option( 'lightbox_slide_effect' );
		$lightbox_img_count        = ( cix_wpgs::option( 'lightbox_img_count' ) == 1 ) ? 'true' : 'false';

		$img_has_video            = get_post_meta( $attachment_id, 'twist_video_url', true );
		$gallery_first_item_class = ( cix_wpgs::option( 'variation_slide' ) == 'default' ) ? 'woocommerce-product-gallery__image' : 'wpgs1';
		$video_class              = $img_has_video ? 'wpgs-video' : '';
		$gallery__image           = ( $main_image ) ? 'class="' . $gallery_first_item_class . ' wpgs_image"' : 'class="wpgs_image"';

		$img_lightbox_url    = $img_has_video ? $img_has_video : wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_lightbox_image_size', cix_wpgs::option( 'lightbox_image_size', 'full' ) ) );
		$img_lightbox_srcset = wp_get_attachment_image_srcset( $attachment_id );
		$caption_html        = ( cix_wpgs::option( 'slider_alt_text' ) == 1 ) ? '<span class="wpgs-gallery-caption">' . $img_caption . '</span>' : '';
		$image               = wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(
				// 'title'            => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'alt'              => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
				'class'            => esc_attr( $main_image ? 'wp-post-image img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) : 'img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) ),
				'src'              => apply_filters( 'wpgs_lazyload_src', wp_get_attachment_image_url( $attachment_id, $size ) ),
				'data-lazy'        => wp_get_attachment_image_url( $attachment_id, $size ),
				'data-o_img'       => wp_get_attachment_image_url( $attachment_id, $size ),
				'data-large_image' => wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', $zoom_image_size ) ),
				'data-zoom-image'  => wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', $zoom_image_size ) ),
				'data-caption'     => $img_caption,

			),
			$attachment_id,
			$main_image
		);
		if ( cix_wpgs::option( 'video_render', 'lightbox_section' ) == 'inner_section' && $img_has_video ) {
			$image = wpgs_video_in_inner_gallery_html( $img_has_video, $attachment_id );
		}
		if ( 'true' == $lightbox ) {
			$markup = '<div ' . $gallery__image . ' data-attachment-id=' . $attachment_id . ' >' . $image . '<a aria-label="Zoom Icon" class=" woocommerce-product-gallery__lightbox ' . $video_class . '"
			href = "' . $img_lightbox_url . '"
			data-elementor-open-lightbox="no"
			data-caption="' . $img_caption . '"
			data-thumb="' . wp_get_attachment_image_url( $attachment_id, apply_filters( 'wpgs_new_thumb_img_size', 'woocommerce_gallery_thumbnail' ) ) . '"
			data-fancybox="wpgs"
			data-zoom-image=' . wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', $zoom_image_size ) ) . '
			data-animation-effect="' . $lightbox_animation . '"
			data-transition-effect="' . $lightbox_slides_animation . '"
			data-infobar="' . $lightbox_img_count . '"
			data-loop="true"
			data-hash="false"
			data-click-slide="close"
			data-options=\'{"buttons": ["zoom","slideShow","fullScreen","thumbs","close"] }\'

			>
			<i class="' . $zoom_icon_class . '"></i>
			</a>' . $caption_html . '</div>';
			return $markup;
		} elseif ( 'false' == $lightbox ) {
			$markup = '<div ' . $gallery__image . ' data-attachment-id=' . $attachment_id . '>' . $image . $caption_html . '</div>';

			return $markup;
		}
	}
}

if ( ! function_exists( 'wpgs_get_image_gallery_thumb_html' ) ) {

	// Custom HTML layout
	/**
	 * @param $attachment_id
	 * @param $main_image
	 */
	function wpgs_get_image_gallery_thumb_html( $attachment_id, $main_image = false ) {

		$size = apply_filters( 'wpgs_new_thumb_img_size', cix_wpgs::option( 'slider_image_thumb_size' ) );

		/* Plugin Options */

		$lightbox_img_alt = ( cix_wpgs::option( 'lightbox_alt_text' ) == 1 ) ? 'true' : 'false';
		$img_caption      = ( empty( wp_get_attachment_caption( $attachment_id ) ) ) ? get_the_title( $attachment_id ) : wp_get_attachment_caption( $attachment_id );
		( 'true' == $lightbox_img_alt ) ? $img_caption : $img_caption = '';
		// Check if Gallery have Video URL

		$lightbox_animation        = cix_wpgs::option( 'lightbox_oc_effect' );
		$lightbox_slides_animation = cix_wpgs::option( 'lightbox_slide_effect' );
		$lightbox_img_count        = ( cix_wpgs::option( 'lightbox_img_count' ) == 1 ) ? 'true' : 'false';

		$img_has_video = get_post_meta( $attachment_id, 'twist_video_url', true );
		$video_class   = $img_has_video ? 'wpgs-video' : '';

		$gallery_thumb_image = $main_image ? 'class="gallery_thumbnail_first thumbnail_image ' . $video_class . ' "' : 'class="thumbnail_image ' . $video_class . '"';

		$img_lightbox_url = $img_has_video ? $img_has_video : wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_lightbox_image_size', cix_wpgs::option( 'lightbox_image_size', 'full' ) ) );

		$image = wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(

				'src'        => apply_filters( 'wpgs_lazyload_src', wp_get_attachment_image_url( $attachment_id, $size ) ),
				'title'      => $img_caption,
				'alt'        => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
				'class'      => esc_attr( $main_image ? 'wp-post-image img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) : 'img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) ),
				'data-lazy'  => wp_get_attachment_image_url( $attachment_id, $size ),
				'data-thumb' => wp_get_attachment_image_url( $attachment_id, $size ),

			),
			$attachment_id,
			$main_image
		);
		if ( cix_wpgs::option( 'video_thumb', 'image_thumb' ) == 'video_thumb' && $img_has_video ) {
			$image = wpgs_video_in_inner_gallery_html( $img_has_video, $attachment_id, true, $size );
		}
		if ( apply_filters( 'wpgs_carousel_mode', true ) != true ) {
			$markup = '<a ' . $gallery_thumb_image . '
			href = "' . $img_lightbox_url . '"
			data-elementor-open-lightbox="no"
			data-caption="' . $img_caption . '"
			data-thumb="' . wp_get_attachment_image_url( $attachment_id, $size ) . '"
			data-fancybox="wpgs" aria-label="Zoom Icon" data-animation-effect="' . $lightbox_animation . '" data-transition-effect="' . $lightbox_slides_animation . '"
			data-infobar="' . $lightbox_img_count . '"
			data-loop="true"
			data-hash="false"
			data-click-slide="close"
			data-options=\'{"buttons": ["zoom","slideShow","fullScreen","thumbs","close"] }\'

			>
			' . $image . '
			</a>';
			return $markup;
		} else {
			// the thumbnail markup
			return '<div ' . $gallery_thumb_image . '>' . $image . '</div>';
		}
	}
}
