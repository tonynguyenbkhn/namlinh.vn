<?php
/**
 * Class YITH_YWAR_Assets
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Assets' ) ) {
	/**
	 * Class YITH_YWAR_Assets
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Assets {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * The constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 11 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ), 11 );
			add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_ids' ), 99, 1 );
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function enqueue_admin_scripts() {
			$this->enqueue_styles( 'admin' );
			$this->enqueue_scripts( 'admin' );
		}

		/**
		 * Enqueue scripts and styles.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function enqueue_frontend_scripts() {
			$this->enqueue_styles( 'frontend' );
			$this->enqueue_scripts( 'frontend' );
		}

		/**
		 * Get scripts.
		 *
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_scripts( string $context ): array {
			$common_scripts = array();

			if ( 'admin' === $context ) {
				$admin_scripts = array(
					'yith-ywar-admin-ajax'            => array(
						'src'              => YITH_YWAR_ASSETS_URL . '/js/admin/admin-ajax.js',
						'context'          => 'admin',
						'deps'             => array( 'jquery', 'jquery-blockui' ),
						'localize_globals' => array( 'ywar_admin' ),
					),
					'yith-ywar-admin'                 => array(
						'src'              => YITH_YWAR_ASSETS_URL . '/js/admin/admin.js',
						'context'          => 'admin',
						'deps'             => array( 'jquery' ),
						'localize_globals' => array( 'ywar_admin' ),
						'enqueue'          => 'all-plugin-pages',
					),
					'yith-ywar-admin-modules'         => array(
						'src'              => YITH_YWAR_ASSETS_URL . '/js/admin/modules.js',
						'context'          => 'admin',
						'deps'             => array( 'jquery' ),
						'localize_globals' => array( 'ywar_admin' ),
						'enqueue'          => 'panel/modules',
					),
					'yith-ywar-admin-emails'          => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/admin/email-settings.js',
						'context' => 'admin',
						'deps'    => array( 'jquery', 'yith-ywar-admin-ajax' ),
						'enqueue' => array( 'panel/emails', 'woocommerce_page_wc-settings' ),
					),
					'yith-ywar-admin-review-boxes'    => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/admin/review-boxes.js',
						'context' => 'admin',
						'deps'    => array( 'jquery', 'yith-ywar-admin-ajax' ),
						'enqueue' => array( 'panel/review-boxes/boxes' ),
					),
					'yith-ywar-admin-review-criteria' => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/admin/review-criteria.js',
						'context' => 'admin',
						'deps'    => array( 'jquery', 'yith-ywar-admin-ajax' ),
						'enqueue' => array( 'edit-' . YITH_YWAR_Post_Types::CRITERIA_TAX ),
					),
					'yith-ywar-admin-bar-widget'      => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/admin/admin-bar-widget.js',
						'context' => 'admin',
						'deps'    => array( 'jquery', 'yith-ywar-admin-ajax' ),
						'enqueue' => true,
					),
					'yith-ywar-admin-reviews'         => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/admin/reviews.js',
						'context' => 'admin',
						'deps'    => array( 'jquery', 'yith-ywar-admin-ajax' ),
						'enqueue' => array( 'edit-' . YITH_YWAR_Post_Types::REVIEWS, YITH_YWAR_Post_Types::REVIEWS ),
					),
				);
				$scripts       = $common_scripts + $admin_scripts;
			} else {

				if ( yith_ywar_is_recaptcha_enabled() && ( ! class_exists( 'WP_reCaptcha' ) || ( class_exists( 'WP_reCaptcha' ) && is_user_logged_in() ) ) ) {
					$frontend_deps = array( 'jquery', 'yith-ywar-ajax', 'selectWoo', 'yith-ywar-simplebar', 'yith-ywar-swiper', 'yith-ywar-recaptcha' );
				} else {
					$frontend_deps = array( 'jquery', 'yith-ywar-ajax', 'selectWoo', 'yith-ywar-simplebar', 'yith-ywar-swiper' );
				}

				$frontend_scripts = array(
					'yith-ywar-ajax'      => array(
						'src'              => YITH_YWAR_ASSETS_URL . '/js/ajax.js',
						'context'          => 'frontend',
						'deps'             => array( 'jquery', 'jquery-blockui' ),
						'localize_globals' => array( 'ywar_frontend' ),
					),
					'yith-ywar-simplebar' => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/simplebar/simplebar.js',
						'context' => 'frontend',
					),
					'yith-ywar-swiper'    => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/swiper/swiper.js',
						'context' => 'frontend',
					),
					'yith-ywar-recaptcha' => array(
						'src'     => '//www.google.com/recaptcha/api.js?' . ( 'v2' === yith_ywar_get_option( 'ywar_recaptcha_version' ) ? 'render=onload' : 'render=' . yith_ywar_get_option( 'ywar_recaptcha_site_key' ) ),
						'context' => 'frontend',
						'use_min' => false,
					),
					'yith-ywar-frontend'  => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/js/frontend.js',
						'context' => 'frontend',
						'deps'    => $frontend_deps,
						'enqueue' => ! is_shop() && ! is_product_category() && ! is_product_tag(),
					),
				);
				$scripts          = $common_scripts + $frontend_scripts;
			}

			/**
			 * APPLY_FILTERS: yith_ywar_scripts
			 *
			 * Manages additional scripts.
			 *
			 * @param array  $scripts The array of scripts to enqueue.
			 * @param string $context The context of the scripts.
			 *
			 * @return array
			 */
			$scripts = (array) apply_filters( 'yith_ywar_scripts', $scripts, $context );

			return $this->filter_assets_by_context( $scripts, $context );
		}

		/**
		 * Enqueue Styles
		 *
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_styles( string $context ): array {

			$common_styles = array();

			if ( 'admin' === $context ) {
				$admin_styles = array(
					'yith-ywar-admin-modules'         => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/modules.css',
						'context' => 'admin',
						'enqueue' => 'panel/modules',
					),
					'yith-ywar-admin-emails'          => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/email-settings.css',
						'context' => 'admin',
						'enqueue' => array( 'panel/emails', 'woocommerce_page_wc-settings' ),
					),
					'yith-ywar-admin-reports'         => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/reports.css',
						'context' => 'admin',
						'enqueue' => array( 'panel', 'panel/dashboard' ),
					),
					'yith-ywar-admin-review-boxes'    => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/review-boxes.css',
						'context' => 'admin',
						'enqueue' => array( 'panel/review-boxes/boxes' ),
					),
					'yith-ywar-admin-review-criteria' => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/review-criteria.css',
						'context' => 'admin',
						'enqueue' => array( 'edit-' . YITH_YWAR_Post_Types::CRITERIA_TAX, 'panel/review-boxes/boxes' ),
					),
					'yith-ywar-admin-bar-widget'      => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/admin-bar-widget.css',
						'context' => 'admin',
						'enqueue' => true,
					),
					'yith-ywar-admin'                 => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/admin.css',
						'context' => 'admin',
						'enqueue' => 'all-plugin-pages',
					),
					'yith-ywar-admin-reviews'         => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/admin/reviews.css',
						'context' => 'admin',
						'enqueue' => array( 'edit-' . YITH_YWAR_Post_Types::REVIEWS, YITH_YWAR_Post_Types::REVIEWS ),
					),
				);
				$styles       = $common_styles + $admin_styles;
			} else {
				$frontend_styles = array(
					'yith-ywar-simplebar' => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/simplebar/simplebar.css',
						'context' => 'frontend',
					),
					'yith-ywar-swiper'    => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/swiper/swiper.css',
						'context' => 'frontend',
					),
					'yith-ywar-frontend'  => array(
						'src'     => YITH_YWAR_ASSETS_URL . '/css/frontend.css',
						'context' => 'frontend',
						'enqueue' => true,
						'deps'    => array( 'select2', 'yith-ywar-swiper', 'yith-ywar-simplebar' ),
					),
				);
				$styles          = $common_styles + $frontend_styles;
			}

			/**
			 * APPLY_FILTERS: yith_ywar_styles
			 *
			 * Manages additional styles.
			 *
			 * @param array  $styles  The array of styles to enqueue.
			 * @param string $context The context of the styles.
			 *
			 * @return array
			 */
			$styles = (array) apply_filters( 'yith_ywar_styles', $styles, $context );

			return $this->filter_assets_by_context( $styles, $context );
		}

		/**
		 * Get styles.
		 *
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		protected function get_globals( string $context ): array {

			$globals = array(
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'blockParams' => array(
					'message'         => '',
					'blockMsgClass'   => 'yith-ywar-block-ui-element',
					'css'             => array(
						'border'     => 'none',
						'background' => 'transparent',
					),
					'overlayCSS'      => array(
						'background' => '#ffffff',
						'opacity'    => '0.7',
					),
					'ignoreIfBlocked' => false,
				),

			);

			if ( 'admin' === $context ) {
				$admin = array(
					'adminAjaxAction' => YITH_YWAR_AJAX::ADMIN_AJAX_ACTION,
					'nonces'          => array(
						'adminAjax'     => wp_create_nonce( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION ),
						'modulesAction' => wp_create_nonce( YITH_YWAR_Modules::AJAX_ACTION ),
					),
					'messages'        => array(
						'after_send_test_email' => esc_html_x( 'Test email has been sent successfully!', '[Admin panel] Success message', 'yith-woocommerce-advanced-reviews' ),
						'test_mail_wrong'       => esc_html_x( 'Please insert a valid email address.', '[Global] Generic error message', 'yith-woocommerce-advanced-reviews' ),
						'test_mail_error'       => esc_html_x( 'An error occurred while sending the email.', '[Admin panel] Error message', 'yith-woocommerce-advanced-reviews' ),
						'required_field'        => esc_html_x( 'This field is required!', '[Admin panel] Error message', 'yith-woocommerce-advanced-reviews' ),
					),
					'modals'          => array(
						'delete_review_box' => array(
							'title'   => esc_html_x( 'Delete review box', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
							'message' => esc_html_x( 'Are you sure you want to delete this review box?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
							'button'  => esc_html_x( 'Yes, proceed.', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
						),
						'delete_criteria'   => array(
							'title'   => esc_html_x( 'Delete criterion', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
							'message' => esc_html_x( 'Are you sure you want to delete this criterion?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
							'button'  => esc_html_x( 'Yes, proceed.', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
						),
						'add_criteria'      => array(
							'title'   => esc_html_x( 'New criterion', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
							'content' => yith_ywar_criteria_popup_content(),
							'button'  => esc_html_x( 'Confirm', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
						),
						'edit_criteria'     => array(
							'title'   => esc_html_x( 'New criterion', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
							'content' => yith_ywar_criteria_popup_content( true ),
							'button'  => esc_html_x( 'Confirm', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
						),
					),
					'notifications'   => get_transient( YITH_YWAR::TRANSIENT ),
				);
				/**
				 * APPLY_FILTERS: yith_ywar_assets_globals_admin
				 *
				 * Manages additional globals on admin side.
				 *
				 * @param array $admin The array of globals.
				 *
				 * @return array
				 */
				$admin   = apply_filters( 'yith_ywar_assets_globals_admin', $admin );
				$globals = $globals + $admin;
			} else {
				global $post;
				$product    = wc_get_product( $post );
				$review_box = yith_ywar_get_current_review_box( $product );
				$frontend   = array(
					'frontendAjaxAction'  => YITH_YWAR_AJAX::FRONTEND_AJAX_ACTION,
					'messages'            => array(
						'mail_wrong'       => esc_html_x( 'Please insert a valid email address.', '[Global] Generic error message', 'yith-woocommerce-advanced-reviews' ),
						'required_field'   => esc_html_x( 'This is a mandatory field!', '[Frontend] Mandatory field error message', 'yith-woocommerce-advanced-reviews' ),
						'required_rating'  => esc_html_x( 'You must select a rating!', '[Frontend] Missing rating error message', 'yith-woocommerce-advanced-reviews' ),
						'required_captcha' => esc_html_x( 'You must do the reCaptcha!', '[Frontend] Missing recaptcha error message', 'yith-woocommerce-advanced-reviews' ),
						/* translators: %s maximum image number */
						'too_many_images'  => sprintf( esc_html_x( 'You cannot upload more than %s images. The following images will not be uploaded', '[Frontend] Too many images error message', 'yith-woocommerce-advanced-reviews' ), yith_ywar_get_option( 'ywar_max_attachments' ) ) . ': ',
						/* translators: %s maximum video number */
						'too_many_videos'  => sprintf( esc_html_x( 'You cannot upload more than %s videos. The following videos will not be uploaded', '[Frontend] Too many videos error message', 'yith-woocommerce-advanced-reviews' ), yith_ywar_get_option( 'ywar_max_attachments_video' ) ) . ': ',
						/* translators: %s maximum image size */
						'image_too_big'    => sprintf( esc_html_x( 'Images cannot be bigger than %sMB. The following images will not be uploaded', '[Frontend] Too big images error message', 'yith-woocommerce-advanced-reviews' ), yith_ywar_get_option( 'ywar_attachment_max_size' ) ) . ': ',
						/* translators: %s maximum video size */
						'video_too_big'    => sprintf( esc_html_x( 'Videos cannot be bigger than %sMB. The following videos will not be uploaded', '[Frontend] Too big videos error message', 'yith-woocommerce-advanced-reviews' ), yith_ywar_get_option( 'ywar_attachment_max_size_video' ) ) . ': ',
					),
					'filter_dialog'       => yith_ywar_get_option( 'ywar_reviews_dialog' ) === 'yes',
					'user_id'             => wp_get_current_user()->ID,
					'use_recaptcha'       => yith_ywar_is_recaptcha_enabled(),
					'recaptcha_version'   => yith_ywar_get_option( 'ywar_recaptcha_version' ),
					'recaptcha_sitekey'   => yith_ywar_get_option( 'ywar_recaptcha_site_key' ),
					'is_block_editor'     => defined( 'YITH_PLUGIN_FW_BLOCK_PREVIEW' ) && YITH_PLUGIN_FW_BLOCK_PREVIEW,
					/**
					 * APPLY_FILTERS: yith_ywar_frontend_scroll_offset
					 *
					 * Manages the offset of the scroll
					 *
					 * @param int $offset The array of globals.
					 *
					 * @return int
					 */
					'scroll_offset'       => apply_filters( 'yith_ywar_frontend_scroll_offset', 50 ),
					'file_upload'         => array(
						'allowed_quantity'  => array(
							'image' => yith_ywar_get_option( 'ywar_max_attachments' ),
							'video' => yith_ywar_get_option( 'ywar_max_attachments_video' ),
						),
						'allowed_size'      => array(
							'image' => yith_ywar_get_option( 'ywar_attachment_max_size' ),
							'video' => yith_ywar_get_option( 'ywar_attachment_max_size_video' ),
						),
						'video_placeholder' => $product ? wp_get_attachment_image_url( $product->get_image_id(), array( 80, 80 ), true ) : '#',
					),
					'attachments_gallery' => 'yes' === yith_ywar_get_option( 'ywar_show_attachments_gallery' ),
					'graph_bars'          => false !== array_search( 'graph-bars', $review_box->get_show_elements(), true ),
				);
				/**
				 * APPLY_FILTERS: yith_ywar_assets_globals_frontend
				 *
				 * Manages additional globals on frontend side.
				 *
				 * @param array $frontend The array of globals.
				 *
				 * @return array
				 */
				$frontend = apply_filters( 'yith_ywar_assets_globals_frontend', $frontend );
				$globals  = $globals + $frontend;
			}

			return array(
				"ywar_$context" => $globals,
			);
		}

		/**
		 * Retrieve an asset prop by context for common assets.
		 *
		 * @param array  $asset         The asset array.
		 * @param string $prop          The prop.
		 * @param string $context       The context [admin or frontend].
		 * @param mixed  $default_value The default value.
		 *
		 * @return mixed
		 * @since  2.0.0
		 */
		protected function get_common_asset_prop( array $asset, string $prop, string $context, $default_value = false ) {
			$context_prop  = $context . '_' . $prop;
			$asset_context = $asset['context'] ?? false;
			$value         = $asset[ $prop ] ?? $default_value;
			if ( 'common' === $asset_context ) {
				$value = $asset[ $context_prop ] ?? $value;
			}

			return $value;
		}

		/**
		 * Should enqueue script/style?
		 *
		 * @param array  $asset   The asset info.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		protected function should_enqueue( array $asset, string $context ): bool {
			$enqueue        = $this->get_common_asset_prop( $asset, 'enqueue', $context, false );
			$should_enqueue = true === $enqueue;

			if ( ! $should_enqueue ) {
				if ( $enqueue ) {
					$should_enqueue = 'admin' === $context ? yith_ywar_is_admin_page( $enqueue ) : $this->frontend_should_enqueue();
				}
			}

			return $should_enqueue;
		}

		/**
		 * Filter styles/scripts by context.
		 *
		 * @param array  $assets  Assets.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		protected function filter_assets_by_context( array $assets, string $context ): array {
			return array_filter(
				$assets,
				function ( $asset ) use ( $context ) {
					$asset_context = $asset['context'] ?? '';

					return in_array( $asset_context, array( $context, 'common' ), true );
				}
			);
		}

		/**
		 * Enqueue Styles
		 *
		 * @param string $context The context [admin or frontend].
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function enqueue_styles( string $context ) {
			$styles = $this->get_styles( $context );

			// Register.
			foreach ( $styles as $handle => $style ) {
				$src     = $style['src'] ?? '';
				$deps    = $style['deps'] ?? array();
				$version = $style['version'] ?? $this->get_default_version();

				if ( $src ) {
					wp_register_style( $handle, $src, $deps, $version );
				}
			}

			// Enqueue.
			foreach ( $styles as $handle => $style ) {
				if ( $this->should_enqueue( $style, $context ) ) {
					wp_enqueue_style( $handle );
				}
			}
		}

		/**
		 * Enqueue scripts
		 *
		 * @param string $context The context [admin or frontend].
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function enqueue_scripts( string $context ) {
			$globals = $this->get_globals( $context );
			$scripts = $this->get_scripts( $context );

			// Register.
			foreach ( $scripts as $handle => $script ) {
				$src       = $script['src'] ?? '';
				$use_min   = $script['use_min'] ?? true;
				$deps      = $script['deps'] ?? array();
				$version   = $script['version'] ?? $this->get_default_version();
				$in_footer = $script['in_footer'] ?? true;

				if ( $src ) {
					if ( $use_min && ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
						$src = str_replace( '.js', '.min.js', $src );
					}
					wp_register_script( $handle, $src, $deps, $version, $in_footer );
				}
			}

			// Localize.
			foreach ( $scripts as $handle => $script ) {
				$localize         = $this->get_common_asset_prop( $script, 'localize', $context, array() );
				$localize_globals = $this->get_common_asset_prop( $script, 'localize_globals', $context, array() );

				foreach ( $localize as $object_name => $object ) {
					wp_localize_script( $handle, $object_name, $object );
				}

				foreach ( $localize_globals as $global ) {
					if ( isset( $globals[ $global ] ) ) {
						wp_localize_script( $handle, $global, $globals[ $global ] );
					}
				}
			}

			// Enqueue.
			foreach ( $scripts as $handle => $script ) {
				if ( $this->should_enqueue( $script, $context ) ) {
					wp_enqueue_script( $handle );
				}
			}
		}

		/**
		 * Should I enqueue the asset on frontend?
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		private function frontend_should_enqueue(): bool {
			return true;
		}

		/**
		 * Add custom screen ids to standard WC
		 *
		 * @access public
		 *
		 * @param array $screen_ids Screen IDs.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_screen_ids( array $screen_ids ): array {
			return $screen_ids;
		}

		/**
		 * Get default version for script and styles.
		 * If SCRIPT_DEBUG is enabled it will return the current time in order to avoid caching
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function get_default_version(): string {
			return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : YITH_YWAR_VERSION;
		}
	}
}
