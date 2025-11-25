<?php
/**
 * Frontend class
 *
 * @package YITH\AdvancedReviews\Frontend
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Frontend' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the frontend behaviors.
	 *
	 * @class   YITH_YWAR_Frontend
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Frontend
	 */
	class YITH_YWAR_Frontend {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			add_filter( 'template_redirect', array( $this, 'force_product_stats_regeneration' ) );
			add_filter( 'body_class', array( $this, 'add_body_classes' ) );
			add_filter( 'woocommerce_product_tabs', array( $this, 'replace_woocommerce_tab' ), 9999 );
			add_filter( 'woocommerce_locate_template', array( $this, 'intercept_rating_template' ), 20, 2 );
			add_action( 'init', array( $this, 'init_template_hooks' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'add_dynamic_css' ), 99 );
			add_action( 'yith_ywar_before_reviews', array( $this, 'show_review_stats' ), 10, 2 );
			add_action( 'yith_ywar_before_reviews_list', array( $this, 'show_attachment_gallery' ), 10, 1 );
			add_action( 'yith_ywar_before_reviews_list', array( $this, 'show_helpful_tab' ), 15, 2 );
			add_action( 'yith_ywar_before_reviews_list', array( $this, 'show_sorting_options' ), 20, 2 );
			add_action( 'yith_ywar_before_reviews_list', array( $this, 'show_pending_reviews' ), 25, 2 );
			add_action( 'yith_ywar_after_reviews', array( $this, 'show_new_review_form' ), 20, 2 );
			add_action( 'wp_footer', array( $this, 'popup_templates' ) );
			add_action( 'yith_ywar_frontend_ajax_load_reviews', array( $this, 'load_reviews' ) );
			add_action( 'yith_ywar_frontend_ajax_load_reviews_shortcode', array( $this, 'load_reviews_shortcode' ) );
			add_action( 'yith_ywar_frontend_ajax_load_attachment_popup', array( $this, 'load_attachment_popup' ) );
			add_action( 'yith_ywar_frontend_ajax_load_single_review', array( $this, 'load_single_review' ) );
			add_action( 'yith_ywar_frontend_ajax_load_review_attachments', array( $this, 'load_review_attachments' ) );
			add_action( 'yith_ywar_frontend_ajax_load_reviews_with_attachments', array( $this, 'load_reviews_with_attachments' ) );
			add_action( 'yith_ywar_frontend_ajax_like_review', array( $this, 'like_review' ) );
			add_action( 'yith_ywar_frontend_ajax_report_review', array( $this, 'report_review' ) );
			add_action( 'yith_ywar_frontend_ajax_delete_review', array( $this, 'delete_review' ) );
			add_action( 'yith_ywar_frontend_ajax_restore_review', array( $this, 'restore_review' ) );
			add_action( 'yith_ywar_frontend_ajax_edit_review', array( $this, 'edit_review' ) );
			add_action( 'yith_ywar_frontend_ajax_reply_review', array( $this, 'reply_review' ) );
			add_action( 'yith_ywar_frontend_ajax_submit_new_review', array( $this, 'submit_new_review' ) );
			add_action( 'yith_ywar_frontend_ajax_submit_new_reply', array( $this, 'submit_new_reply' ) );
			add_action( 'yith_ywar_frontend_ajax_submit_edit_review', array( $this, 'submit_edit_review' ) );
			add_action( 'yith_ywar_frontend_ajax_submit_edit_reply', array( $this, 'submit_edit_reply' ) );
			add_filter( 'esc_html', array( $this, 'fix_tab_name_if_escaped' ), 10, 2 );
		}

		/**
		 * Force product stats regeneration
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function force_product_stats_regeneration() {
			if ( is_product() && isset( $_GET['ywar-stats'] ) && 'yes' === $_GET['ywar-stats'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				global $post;
				$product = wc_get_product( $post );
				yith_ywar_get_review_stats( $product, true );
			}
		}

		/**
		 * Add classes to body
		 *
		 * @param array $classes Body classes array.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_body_classes( $classes ) {

			if ( is_product() ) {
				$classes[] = 'yith-ywar-product-page';
			}

			return $classes;
		}

		/**
		 * Replace default WooCommerce reviews tab
		 *
		 * @param array $tabs The product tabs.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function replace_woocommerce_tab( array $tabs ): array {

			if ( isset( $tabs['reviews'] ) ) {
				global $product;

				/* translators: %s: reviews count */
				$tab_title         = sprintf( esc_html_x( 'Reviews (%d)', '[Frontend] Tab title with reviews count', 'yith-woocommerce-advanced-reviews' ), yith_ywar_get_review_stats( $product )['total'] );
				$tab_title_wrapper = sprintf( '<span class="yith-ywar-tab-title">%s</span>', $tab_title );
				$tabs['reviews']   = array(
					'title'    => $tab_title_wrapper,
					'priority' => 30,
					'callback' => array( $this, 'show_advanced_reviews_template' ),
				);
			}

			return $tabs;
		}

		/**
		 * Displays the reviews template.
		 *
		 * @param string $product_id Optional product ID for shortcode.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_advanced_reviews_template( string $product_id = '' ) {
			global $post, $product;

			if ( intval( $product_id ) > 0 ) {
				$product = wc_get_product( intval( $product_id ) );
			}

			if ( ! $product && isset( $post ) ) {
				$product = wc_get_product( $post->ID );
			}

			if ( ! $product instanceof WC_Product ) {
				return;
			}

			$review_box = yith_ywar_get_current_review_box( $product );
			$args       = array(
				'product'    => $product,
				'review_box' => $review_box,
			);

			wc_get_template( 'yith-ywar-wrapper.php', $args, '', YITH_YWAR_TEMPLATES_DIR );
		}

		/**
		 * Filter the template path to use the file in this plugin instead of the one in WooCommerce.
		 *
		 * @param string $template      Default template file path.
		 * @param string $template_name Template file slug.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function intercept_rating_template( string $template, string $template_name ): string {
			if ( strpos( $template, 'rating.php' ) === false ) {
				return $template;
			}

			$template_path = YITH_YWAR_TEMPLATES_DIR . $template_name;

			return file_exists( $template_path ) ? $template_path : $template;
		}

		/**
		 * Init hooks for block template
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_template_hooks() {
			add_filter( 'render_block_woocommerce/product-rating', array( $this, 'product_rating' ), 20 );
		}

		/**
		 * Render rating replacing original block HTML
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function product_rating(): string {

			$product = wc_get_product();
			$context = is_shop() || is_product_category() || is_product_tag() ? 'loop' : 'single';

			ob_start();
			yith_ywar_get_rating_html( $product, $context );

			return ob_get_clean();
		}

		/**
		 * Add Dynamic CSS rules
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_dynamic_css() {

			$ywar_general_color           = yith_ywar_get_option( 'ywar_general_color' );
			$ywar_rating_graph_boxes      = yith_ywar_get_option( 'ywar_rating_graph_boxes' );
			$ywar_graph_colors            = yith_ywar_get_option( 'ywar_graph_colors' );
			$ywar_stars_colors            = yith_ywar_get_option( 'ywar_stars_colors' );
			$ywar_avatar_colors           = yith_ywar_get_option( 'ywar_avatar_colors' );
			$ywar_like_section_colors     = yith_ywar_get_option( 'ywar_like_section_colors' );
			$ywar_review_box_colors       = yith_ywar_get_option( 'ywar_review_box_colors' );
			$ywar_submit_button_colors    = yith_ywar_get_option( 'ywar_submit_button_colors' );
			$ywar_load_more_button_colors = yith_ywar_get_option( 'ywar_load_more_button_colors' );
			$ywar_staff_badge             = yith_ywar_get_option( 'ywar_staff_badge' );
			$ywar_featured_badge          = yith_ywar_get_option( 'ywar_featured_badge' );

			$style_options = array(
				"--ywar-general-color: {$ywar_general_color['main']};",
				"--ywar-general-hover-icons: {$ywar_general_color['hover-icons']};",
				"--ywar-stats-background: {$ywar_rating_graph_boxes['background']};",
				"--ywar-graph-default: {$ywar_graph_colors['default']};",
				"--ywar-graph-accent: {$ywar_graph_colors['accent']};",
				"--ywar-graph-percentage: {$ywar_graph_colors['percentage']};",
				"--ywar-stars-default: {$ywar_stars_colors['default']};",
				"--ywar-stars-accent: {$ywar_stars_colors['accent']};",
				"--ywar-avatar-background: {$ywar_avatar_colors['background']};",
				"--ywar-avatar-initials: {$ywar_avatar_colors['initials']};",
				"--ywar-review-border: {$ywar_review_box_colors['border']};",
				"--ywar-review-shadow: {$ywar_review_box_colors['shadow']};",
				"--ywar-review-featured-background-color: {$ywar_featured_badge['background-color']};",
				"--ywar-review-featured-text-color: {$ywar_featured_badge['text-color']};",
				"--ywar-review-featured-border-color: {$ywar_featured_badge['border-color']};",
				"--ywar-review-featured-border-shadow: {$ywar_featured_badge['shadow']};",
				"--ywar-review-staff-background-color: {$ywar_staff_badge['background-color']};",
				"--ywar-review-staff-text-color: {$ywar_staff_badge['text-color']};",
				"--ywar-review-load-more-button-text: {$ywar_load_more_button_colors['text']};",
				"--ywar-review-load-more-button-background: {$ywar_load_more_button_colors['background']};",
				"--ywar-review-load-more-button-text-hover: {$ywar_load_more_button_colors['text-hover']};",
				"--ywar-review-load-more-button-background-hover: {$ywar_load_more_button_colors['background-hover']};",
				"--ywar-like-background: {$ywar_like_section_colors['background']};",
				"--ywar-like-background-rated: {$ywar_like_section_colors['background-rated']};",
				"--ywar-like-icon: {$ywar_like_section_colors['icon']};",
				"--ywar-like-icon-rated: {$ywar_like_section_colors['icon-rated']};",
				"--ywar-submit-button-text: {$ywar_submit_button_colors['text']};",
				"--ywar-submit-button-background: {$ywar_submit_button_colors['background']};",
				"--ywar-submit-button-text-hover: {$ywar_submit_button_colors['text-hover']};",
				"--ywar-submit-button-background-hover: {$ywar_submit_button_colors['background-hover']};",
			);

			$custom_css = sprintf( ":root{\n%s\n}", implode( "\n", $style_options ) );

			wp_add_inline_style( 'yith-ywar-frontend', $custom_css );
		}

		/**
		 * Collect data about reviews rating and show a summary grouped by stars
		 *
		 * @param WC_Product           $product    The current product.
		 * @param YITH_YWAR_Review_Box $review_box The current review box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_review_stats( WC_Product $product, YITH_YWAR_Review_Box $review_box ) {

			$review_stats = yith_ywar_get_review_stats( $product );

			if ( absint( $review_stats['total'] ) === 0 ) {
				return;
			}

			$elements = array(
				'average-rating' => false !== array_search( 'average-rating-box', $review_box->get_show_elements(), true ),
				'multi-criteria' => 'yes' === $review_box->get_enable_multi_criteria() && ! empty( $review_box->get_multi_criteria() ) && false !== array_search( 'average-rating-box', $review_box->get_show_elements(), true ),
				'graph-box'      => false !== array_search( 'graph-bars', $review_box->get_show_elements(), true ),
			);

			if ( count( array_filter( $elements ) ) > 0 ) {
				?>
				<div class="yith-ywar-stats-wrapper columns-<?php echo esc_attr( count( array_filter( $elements ) ) ); ?>">
					<?php
					if ( $elements['average-rating'] ) {
						$average_args = array(
							'average' => $review_stats['average']['rating'],
							'perc'    => $review_stats['average']['perc'],
							'total'   => $review_stats['total'],
						);
						yith_ywar_get_view( 'frontend/stats/average-rating-box.php', $average_args );
					}

					if ( $elements['multi-criteria'] ) {
						$multi_criteria_args = array(
							'criteria'     => $review_box->get_multi_criteria(),
							'multiratings' => $review_stats['multiratings'],
						);
						yith_ywar_get_view( 'frontend/stats/multi-criteria-box.php', $multi_criteria_args );
					}

					if ( $elements['graph-box'] ) {
						$graph_args = array(
							'ratings'   => $review_stats['ratings'],
							'show_perc' => 'yes' === yith_ywar_get_option( 'ywar_summary_percentage_value' ),
						);
						yith_ywar_get_view( 'frontend/stats/graph-box.php', $graph_args );
					}
					?>
				</div>
				<?php
			}
		}

		/**
		 * Show attachment gallery.
		 *
		 * @param WC_Product $product The current product.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_attachment_gallery( WC_Product $product ) {
			if ( 'yes' === yith_ywar_get_option( 'ywar_show_attachments_gallery' ) ) {
				$review_stats = yith_ywar_get_review_stats( $product );
				$index        = 0;

				ob_start();
				foreach ( $review_stats['attachments'] as $review_id => $data ) {
					if ( 'reply' === $data['type'] && 'yes' === yith_ywar_get_option( 'ywar_show_replies_attachments' ) ) {
						continue;
					}
					foreach ( $data['media'] as $attachment_id ) {

						if ( is_wp_error( $attachment_id ) ) {
							continue;
						}

						?>
						<div class="swiper-slide attachment-<?php echo( wp_attachment_is( 'video', $attachment_id ) ? 'video' : 'image' ); ?>" data-review-id="<?php echo esc_attr( $review_id ); ?>" data-slide-index="<?php echo esc_attr( $index ); ?>">
							<img src="<?php echo esc_url( yith_ywar_get_attachment_image( $product, $attachment_id, array( 160, 160 ) ) ); ?>"/>
						</div>
						<?php
						++$index;
					}
				}
				$attachments = ob_get_clean();

				?>
				<div class="yith-ywar-reviews-with-attachments <?php echo empty( $attachments ) ? 'empty-gallery' : ''; ?>">
					<?php if ( ! empty( $attachments ) ) : ?>
						<?php echo esc_html_x( 'Reviews with attachments', '[Frontend] Attachment section title', 'yith-woocommerce-advanced-reviews' ); ?>
						<div class="yith-ywar-swiper swiper preview-gallery" data-reviews="<?php echo wp_kses_post( implode( ',', array_keys( $review_stats['attachments'] ) ) ); ?>">
							<div class="swiper-wrapper">
								<?php echo wp_kses_post( $attachments ); ?>
							</div>
							<div class="swiper-buttons swiper-button-next"></div>
							<div class="swiper-buttons swiper-button-prev"></div>
						</div>
					<?php endif; ?>
				</div>
				<?php
			}
		}

		/**
		 * Show most helpful reviews tab.
		 *
		 * @param WC_Product           $product    The current product.
		 * @param YITH_YWAR_Review_Box $review_box The current review box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_helpful_tab( WC_Product $product, YITH_YWAR_Review_Box $review_box ) {

			$review_stats = yith_ywar_get_review_stats( $product );

			if ( absint( $review_stats['total'] ) === 0 || ( ! isset( $review_stats['helpful'] ) || ( isset( $review_stats['helpful'] ) && absint( $review_stats['helpful'] ) === 0 ) ) ) {
				return;
			}

			$helpful_tab = false !== array_search( 'most-helpful_tab', $review_box->get_show_elements(), true );

			if ( $helpful_tab ) {
				?>
				<div class="review-tabs">
					<span data-filter="all" class="tab-item selected"><?php echo esc_html_x( 'All reviews', '[Frontend] Review tab title', 'yith-woocommerce-advanced-reviews' ); ?></span>
					<span data-filter="helpful" class="tab-item"><?php echo esc_html_x( 'Most helpful reviews', '[Frontend] Review tab title', 'yith-woocommerce-advanced-reviews' ); ?></span>
				</div>
				<?php
			}
		}

		/**
		 * Show sorting options
		 *
		 * @param WC_Product           $product    The current product.
		 * @param YITH_YWAR_Review_Box $review_box The current review box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_sorting_options( WC_Product $product, YITH_YWAR_Review_Box $review_box ) {

			$review_stats    = yith_ywar_get_review_stats( $product );
			$sorting_options = false !== array_search( 'sorting-options', $review_box->get_show_elements(), true );

			if ( absint( $review_stats['total'] ) <= 1 || ! $sorting_options ) {
				return;
			}

			?>
			<label class="sorting-options-wrapper">
				<?php echo esc_html_x( 'Sort by', '[Frontend] Review sorting options title', 'yith-woocommerce-advanced-reviews' ); ?>:
				<select class="sorting-options">
					<option value="default" selected><?php echo esc_html_x( 'Default', '[Frontend] Review sorting option', 'yith-woocommerce-advanced-reviews' ); ?></option>
					<option value="most-recent"><?php echo esc_html_x( 'Most recent', '[Frontend] Review sorting option', 'yith-woocommerce-advanced-reviews' ); ?></option>
					<option value="less-recent"><?php echo esc_html_x( 'Less recent', '[Frontend] Review sorting option', 'yith-woocommerce-advanced-reviews' ); ?></option>
					<option value="best-rated"><?php echo esc_html_x( 'Best rated', '[Frontend] Review sorting option', 'yith-woocommerce-advanced-reviews' ); ?></option>
					<option value="worst-rated"><?php echo esc_html_x( 'Worst rated', '[Frontend] Review sorting option', 'yith-woocommerce-advanced-reviews' ); ?></option>
				</select>
			</label>
			<?php
		}

		/**
		 * Show pending reviews
		 *
		 * @param WC_Product           $product    The current product.
		 * @param YITH_YWAR_Review_Box $review_box The current review box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_pending_reviews( WC_Product $product, YITH_YWAR_Review_Box $review_box ) {

			$meta_query = array(
				'relation'      => 'AND',
				array(
					'key'   => '_ywar_product_id',
					'value' => $product->get_id(),
				),
				'rating_clause' => array(
					'key'     => '_ywar_rating',
					'compare' => 'EXISTS',
				),
			);

			if ( is_user_logged_in() ) {
				$meta_query[] = array(
					'key'   => '_ywar_review_user_id',
					'value' => get_current_user_id(),
				);
			} else {
				$meta_query[] = array(
					'key'   => '_ywar_guest_cookie',
					'value' => yith_ywar_set_guest_cookie( 'reviewed' ),
				);
			}

			$reviews = yith_ywar_get_reviews(
				array(
					'post_status'    => 'ywar-pending',
					'posts_per_page' => -1,
					'paginate'       => false,
					'post_parent'    => 0,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => $meta_query,
					'orderby'        => array(
						'date' => 'DESC',
					),
				)
			);

			if ( ! empty( $reviews ) ) {
				$count = count( $reviews );
				?>
				<div class="yith-ywar-pending-reviews-list has-toggle">
					<div class="wrapper-title">
						<?php
						/* translators: %d number of pending reviews */
						$toggle_title = sprintf( wp_kses_post( _nx( 'You have %d review awaiting approval.', 'You have %d reviews awaiting approval.', esc_html( $count ), '[Frontend] pending reviews toggle title', 'yith-woocommerce-advanced-reviews' ) ), esc_html( $count ) );
						$toggle_link  = esc_html_x( 'View details', '[Frontend] pending reviews toggle title', 'yith-woocommerce-advanced-reviews' );
						printf( '%1$s <span>%2$s &gt;</span>', esc_html( $toggle_title ), esc_html( $toggle_link ) );
						?>
					</div>
					<div class="wrapper-content">
						<?php
						foreach ( $reviews as $review ) {
							yith_ywar_get_view(
								'frontend/review.php',
								array(
									'review'       => $review,
									'review_box'   => $review_box,
									'is_reply'     => false,
									'css_classes'  => 'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
									'badge_label'  => '',
									'hide_buttons' => true,
								)
							);
						}
						?>
					</div>
				</div>
				<?php
			}
		}

		/**
		 * Show the new review form or a warning message.
		 *
		 * @param WC_Product           $product    The current product.
		 * @param YITH_YWAR_Review_Box $review_box The current review box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function show_new_review_form( WC_Product $product, YITH_YWAR_Review_Box $review_box ) {
			$can_review = yith_ywar_user_can_review( $product );
			if ( 'yes' !== $can_review ) {
				?>
				<div class="yith-ywar-review-form-message">
					<div class="wrapper-title"><?php echo wp_kses_post( yith_ywar_get_edit_review_form_message( $can_review ) ); ?></div>
				</div>
				<?php
			} else {
				$args = array(
					'form_id'                => 'new-review-product-' . $product->get_id(),
					'type'                   => 'review',
					'action'                 => 'create',
					'review'                 => false,
					'multi_criteria_enabled' => 'yes' === $review_box->get_enable_multi_criteria() && ! empty( $review_box->get_multi_criteria() ),
					'multi_criteria'         => $review_box->get_multi_criteria(),
					'form_title'             => esc_html_x( 'Leave your review', '[Frontend] New review form title', 'yith-woocommerce-advanced-reviews' ),
					'button_text'            => esc_html_x( 'Publish review', '[Frontend] Publish review button text', 'yith-woocommerce-advanced-reviews' ),
					'logged_user'            => is_user_logged_in(),
					'in_reply_of'            => false,
				);

				yith_ywar_get_view( 'frontend/edit-form/edit-form.php', $args );
			}
		}

		/**
		 * Load popup templates
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function popup_templates() {

			global $post, $product;

			if ( ! $product && isset( $post ) ) {
				$product = wc_get_product( $post->ID );
			}

			if ( ! $product instanceof WC_Product || is_shop() ) {
				return;
			}

			$review_box      = yith_ywar_get_current_review_box( $product );
			$product_name    = $product->get_name();
			$attachment_args = array(
				'popup_id'      => 'attachments',
				'product_name'  => $product_name,
				'popup_title'   => '',
				'popup_extra'   => '',
				'review_box_id' => $review_box->get_id(),
			);

			// Popup for attachment view.
			yith_ywar_get_view( 'frontend/popup/popup.php', $attachment_args );

			if ( 'yes' === yith_ywar_get_option( 'ywar_show_attachments_gallery' ) ) {

				$attachment_args = array(
					'popup_id'      => 'gallery',
					'product_name'  => $product_name,
					'popup_title'   => '',
					'popup_extra'   => '',
					'review_box_id' => $review_box->get_id(),
				);

				// Popup for attachment view.
				yith_ywar_get_view( 'frontend/popup/popup.php', $attachment_args );
			}

			// Popup for filtered results.
			if ( yith_ywar_get_option( 'ywar_reviews_dialog' ) === 'yes' ) {
				$review_stats = yith_ywar_get_review_stats( $product );
				$stars        = array(
					/* translators: %s amount of stars */
					'1' => sprintf( esc_html_x( '%s star', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 1 ),
					/* translators: %s amount of stars */
					'2' => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 2 ),
					/* translators: %s amount of stars */
					'3' => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 3 ),
					/* translators: %s amount of stars */
					'4' => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 4 ),
					/* translators: %s amount of stars */
					'5' => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 5 ),
				);

				ob_start();
				?>
				<label class="filter-options-wrapper">
					<select class="filter-options">
						<?php foreach ( $stars as $star => $label ) : ?>
							<option <?php echo( 0 === $review_stats['ratings'][ $star ]['count'] ? 'disabled' : '' ); ?> value="<?php echo esc_attr( $star ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<?php
				$popup_extra = ob_get_clean();
				$review_box  = yith_ywar_get_current_review_box( $product );
				$filter_args = array(
					'popup_id'      => 'filter',
					'product_name'  => $product_name,
					'popup_title'   => esc_html_x( 'Reviews filtered by rating', '[Frontend] Filtered reviews title', 'yith-woocommerce-advanced-reviews' ),
					'popup_extra'   => $popup_extra,
					'popup_kses'    => array(
						'label'  => array(
							'class' => array(),
						),
						'select' => array(
							'class' => array(),
							'id'    => array(),
							'name'  => array(),
							'value' => array(),
							'type'  => array(),
						),
						'option' => array(
							'disabled' => array(),
							'selected' => array(),
							'value'    => array(),
						),
					),
					'review_box_id' => $review_box->get_id(),
				);
				yith_ywar_get_view( 'frontend/popup/popup.php', $filter_args );
				yith_ywar_get_view( 'frontend/popup/attachments-popup.php', $filter_args );
			}
		}

		/**
		 * Load Reviews
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function load_reviews() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$product_id = ! empty( $_POST['product_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) ) : 0;
			$review_id  = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$paged      = ! empty( $_POST['page'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['page'] ) ) ) : 1;
			$review_box = yith_ywar_get_review_box( ! empty( $_POST['box_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['box_id'] ) ) ) : get_option( 'yith-ywar-default-box-id' ) );
			$rating     = ! empty( $_POST['rating'] ) && 'all' !== $_POST['rating'] ? intval( sanitize_text_field( wp_unslash( $_POST['rating'] ) ) ) : 'all';
			$sorting    = ! empty( $_POST['sorting'] ) && 'default' !== $_POST['sorting'] ? sanitize_text_field( wp_unslash( $_POST['sorting'] ) ) : 'default';
			$helpful    = ! empty( $_POST['helpful'] ) && 'no' !== $_POST['helpful'] ? sanitize_text_field( wp_unslash( $_POST['helpful'] ) ) : 'helpful';
			$in_popup   = ! empty( $_POST['popup'] ) && 'no' !== $_POST['popup'] ? sanitize_text_field( wp_unslash( $_POST['popup'] ) ) : 'no';
			//phpcs:enable
			$data           = array();
			$review_to_find = yith_ywar_get_review( $review_id );
			$fetch_review   = $review_to_find instanceof YITH_YWAR_Review ? $review_to_find->get_id() : 0;
			$args           = array(
				'product_id' => $product_id,
				'rating'     => $rating,
				'helpful'    => $helpful,
				'sorting'    => $sorting,
				'paged'      => $paged,
				'review_box' => $review_box,
				'in_popup'   => $in_popup,
			);
			$html           = $this->get_reviews_paged( '', $args, $fetch_review );

			if ( '' !== $html ) {
				if ( 'all' !== $rating ) {
					ob_start();
					yith_ywar_get_view(
						'frontend/filter-data.php',
						array(
							'rating' => $rating,
						)
					);

					$data['message'] = ob_get_clean();
				}
			} else {
				ob_start();
				?>
				<div class="yith-ywar-no-reviews">
					<img class="icon" src="<?php echo esc_url( YITH_YWAR_ASSETS_URL ); ?>/images/review-empty.svg"/>
					<span class="message"><?php echo wp_kses_post( yith_ywar_get_option( 'ywar_no_reviews_text' ) ); ?></span>
				</div>
				<?php
				$html = ob_get_clean();
			}
			$data['reviews'] = $html;

			wp_send_json_success( $data );
		}

		/**
		 * Load Reviews in the shortcode
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function load_reviews_shortcode() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( ! empty( $_POST['settings'] ) ) {
				$paged            = ! empty( $_POST['page'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['page'] ) ) ) : 1;
				$product_id       = ! empty( $_POST['settings']['product_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['settings']['product_id'] ) ) ) : 0;
				$per_page         = ! empty( $_POST['settings']['per_page'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['settings']['per_page'] ) ) ) : -1;
				$pagination       = ! empty( $_POST['settings']['pagination'] ) ? sanitize_text_field( wp_unslash( $_POST['settings']['pagination'] ) ) : 'yes';
				$hide_buttons     = ! empty( $_POST['settings']['hide_buttons'] ) ? sanitize_text_field( wp_unslash( $_POST['settings']['hide_buttons'] ) ) : 'no';
				$hide_attachments = ! empty( $_POST['settings']['hide_attachments'] ) ? sanitize_text_field( wp_unslash( $_POST['settings']['hide_attachments'] ) ) : 'no';
				$hide_replies     = ! empty( $_POST['settings']['hide_replies'] ) ? sanitize_text_field( wp_unslash( $_POST['settings']['hide_replies'] ) ) : 'no';
			} else {
				wp_send_json_error();
				exit;
			}
			//phpcs:enable
			$html       = '';
			$meta_query = array(
				'relation'        => 'AND',
				'featured_clause' => array(
					'key'     => '_ywar_featured',
					'compare' => 'EXISTS',
				),

			);

			if ( $product_id > 0 ) {
				$meta_query[] = array(
					'key'   => '_ywar_product_id',
					'value' => $product_id,

				);
			}

			$reviews = yith_ywar_get_reviews(
				array(
					'post_status'    => 'ywar-approved',
					'posts_per_page' => $per_page,
					'post_parent'    => 0,
					'paginate'       => true,
					'paged'          => $paged,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => $meta_query,
					'orderby'        => array(
						'featured_clause' => 'DESC',
						'date'            => 'DESC',
					),
				)
			);

			if ( $reviews->total > 0 ) {
				foreach ( $reviews->reviews as $review ) {

					$product     = wc_get_product( $review->get_product_id() );
					$review_box  = yith_ywar_get_current_review_box( $product );
					$css_classes = implode(
						'',
						array(
							'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
							'yes' === $review->get_featured() ? ' with-badge featured-review' : '',
							0 === $product_id ? ' in-shortcode' : '',
						)
					);

					ob_start();
					yith_ywar_get_view(
						'frontend/review.php',
						array(
							'review'           => $review,
							'review_box'       => $review_box,
							'is_reply'         => false,
							'css_classes'      => $css_classes,
							'in_shortcode'     => 0 === $product_id,
							'hide_buttons'     => 'yes' === $hide_buttons,
							'hide_attachments' => 'yes' === $hide_attachments,
							'badge_label'      => 'yes' === $review->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '',
						)
					);
					$html .= ob_get_clean();
					if ( 'yes' !== $hide_replies ) {
						$replies = yith_ywar_get_reviews(
							array(
								'post_status'    => 'ywar-approved',
								'posts_per_page' => -1,
								'paginate'       => true,
								'post_parent'    => $review->get_id(),
								//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								'meta_query'     => $meta_query,
								'orderby'        => array(
									'date' => 'DESC',
								),
							)
						);

						if ( $replies->total > 0 ) {
							// Opening replies wrapper.
							$html .= '<div class="yith-ywar-replies-wrapper replies-review-' . $review->get_id() . '">';
							foreach ( $replies->reviews as $reply ) {
								$is_staff    = yith_ywar_user_is_staff_member( $reply->get_review_user_id() ) && 'yes' === yith_ywar_get_option( 'ywar_show_staff_badge' );
								$css_classes = implode(
									'',
									array(
										'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
										' review-reply',
										$is_staff ? ' with-badge staff-review' : '',
										! $is_staff && 'yes' === $reply->get_featured() ? ' with-badge featured-review' : '',
									)
								);

								$badge = $is_staff ? yith_ywar_get_option( 'ywar_staff_badge' )['label'] : ( 'yes' === $review->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '' );

								ob_start();
								yith_ywar_get_view(
									'frontend/review.php',
									array(
										'review'           => $reply,
										'review_box'       => $review_box,
										'is_reply'         => true,
										'css_classes'      => $css_classes,
										'badge_label'      => $badge,
										'hide_buttons'     => 'yes' === $hide_buttons,
										'hide_attachments' => 'yes' === $hide_attachments,
									)
								);
								$html .= ob_get_clean();

							}
							// Closing replies wrapper.
							$html .= '</div>';
						}
					}
				}

				if ( $paged < $reviews->max_num_pages && 'yes' === $pagination ) {
					/* translators: %1$s current reviews, %2$s total reviews */
					$review_count = sprintf( esc_html_x( 'Showing %1$s of %2$s reviews', '[Frontend] Review pagination count', 'yith-woocommerce-advanced-reviews' ), ( $per_page * $paged ), $reviews->total );

					$html .= sprintf( '<div class="load-more-reviews-shortcode">%1$s<br /><span class="load-more-button-shortcode" data-page="%2$s">%3$s</span></div>', $review_count, $paged + 1, esc_html_x( 'Load more', '[Frontend] Load more button text', 'yith-woocommerce-advanced-reviews' ) );

				}
			}
			if ( '' === $html ) {
				ob_start();
				?>
				<div class="yith-ywar-no-reviews">
					<img class="icon" src="<?php echo esc_url( YITH_YWAR_ASSETS_URL ); ?>/images/review-empty.svg"/>
					<span class="message"><?php echo wp_kses_post( yith_ywar_get_option( 'ywar_no_reviews_text' ) ); ?></span>
				</div>
				<?php
				$html = ob_get_clean();
			}

			wp_send_json_success( $html );
		}

		/**
		 * Load Attachment popup
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function load_attachment_popup() {
			$review_id       = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$review          = yith_ywar_get_review( $review_id );
			$product         = wc_get_product( $review->get_product_id() );
			$review_box      = yith_ywar_get_current_review_box( $product );
			$attachment_args = array(
				'popup_id'      => 'attachments',
				'product_name'  => $product->get_name(),
				'popup_title'   => esc_html_x( 'Reviews with attachments', '[Frontend] Review overlay title', 'yith-woocommerce-advanced-reviews' ),
				'popup_extra'   => '',
				'review_box_id' => $review_box->get_id(),
			);

			ob_start();
			// Popup for attachment view.
			yith_ywar_get_view( 'frontend/popup/popup.php', $attachment_args );
			$html = ob_get_clean();

			wp_send_json_success( $html );
		}

		/**
		 * Get reviews paged.
		 *
		 * @param string $html         The HTML to fill.
		 * @param array  $args         The query args.
		 * @param int    $fetch_review Review to fetch.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function get_reviews_paged( string $html, array $args, int $fetch_review = 0 ): string {
			$found      = false;
			$meta_query = array(
				'relation'        => 'AND',
				array(
					'key'   => '_ywar_product_id',
					'value' => $args['product_id'],
				),
				'featured_clause' => array(
					'key'     => '_ywar_featured',
					'compare' => 'EXISTS',
				),
				'rating_clause'   => array(
					'key'     => '_ywar_rating',
					'compare' => 'EXISTS',
				),
			);

			if ( 'all' !== $args['rating'] ) {
				$meta_query[] = array(
					'key'   => '_ywar_rating',
					'value' => $args['rating'],
				);
			}

			if ( 'yes' === $args['helpful'] ) {
				$meta_query[] = array(
					'key'   => '_ywar_helpful',
					'value' => 'yes',
				);
			}

			switch ( $args['sorting'] ) {
				case 'most-recent':
					$order_by = array(
						'date' => 'DESC',
					);
					break;
				case 'less-recent':
					$order_by = array(
						'date' => 'ASC',
					);
					break;
				case 'best-rated':
					$order_by = array(
						'rating_clause' => 'DESC',
						'date'          => 'DESC',
					);
					break;
				case 'worst-rated':
					$order_by = array(
						'rating_clause' => 'ASC',
						'date'          => 'DESC',
					);
					break;
				default:
					$order_by = array(
						'featured_clause' => 'DESC',
						'date'            => 'DESC',
					);
			}

			$posts_per_page = 'yes' === yith_ywar_get_option( 'ywar_show_load_more' ) ? yith_ywar_get_option( 'ywar_review_per_page' ) : -1;

			$reviews = yith_ywar_get_reviews(
				array(
					'post_status'    => 'ywar-approved',
					'posts_per_page' => $posts_per_page,
					'paginate'       => true,
					'post_parent'    => 0,
					'paged'          => $args['paged'],
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => $meta_query,
					'orderby'        => $order_by,
				)
			);

			if ( $reviews->total > 0 ) {
				foreach ( $reviews->reviews as $review ) {
					$css_classes = implode(
						'',
						array(
							'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
							'yes' === $review->get_featured() ? ' with-badge featured-review' : '',
							'yes' === $args['in_popup'] ? ' in-popup' : '',
						)
					);

					ob_start();
					yith_ywar_get_view(
						'frontend/review.php',
						array(
							'review'      => $review,
							'review_box'  => $args['review_box'],
							'is_reply'    => false,
							'css_classes' => $css_classes,
							'badge_label' => 'yes' === $review->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '',
						)
					);
					$html .= ob_get_clean();

					$replies_meta_query = array(
						'relation'        => 'AND',
						array(
							'key'   => '_ywar_product_id',
							'value' => $args['product_id'],
						),
						'featured_clause' => array(
							'key'     => '_ywar_featured',
							'compare' => 'EXISTS',
						),
					);

					if ( 'yes' === $args['helpful'] ) {
						$replies_meta_query[] = array(
							'key'   => '_ywar_helpful',
							'value' => 'yes',
						);
					}

					switch ( $args['sorting'] ) {
						case 'most-recent':
							$replies_order_by = array(
								'date' => 'DESC',
							);
							break;
						case 'less-recent':
							$replies_order_by = array(
								'date' => 'ASC',
							);
							break;
						default:
							$replies_order_by = array(
								'featured_clause' => 'DESC',
								'date'            => 'ASC',
							);
					}

					$replies = yith_ywar_get_reviews(
						array(
							'post_status'    => 'ywar-approved',
							'posts_per_page' => -1,
							'paginate'       => true,
							'post_parent'    => $review->get_id(),
							//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							'meta_query'     => $replies_meta_query,
							'orderby'        => $replies_order_by,
						)
					);

					if ( $replies->total > 0 ) {
						// Opening replies wrapper.
						$html .= '<div class="yith-ywar-replies-wrapper replies-review-' . $review->get_id() . '">';
						foreach ( $replies->reviews as $reply ) {
							$is_staff    = yith_ywar_user_is_staff_member( $reply->get_review_user_id() ) && 'yes' === yith_ywar_get_option( 'ywar_show_staff_badge' );
							$css_classes = implode(
								'',
								array(
									'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
									' review-reply',
									$is_staff ? ' with-badge staff-review' : '',
									! $is_staff && 'yes' === $reply->get_featured() ? ' with-badge featured-review' : '',
									'yes' === $args['in_popup'] ? ' in-popup' : '',
								)
							);

							$badge = $is_staff ? yith_ywar_get_option( 'ywar_staff_badge' )['label'] : ( 'yes' === $review->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '' );

							ob_start();
							yith_ywar_get_view(
								'frontend/review.php',
								array(
									'review'      => $reply,
									'review_box'  => $args['review_box'],
									'is_reply'    => true,
									'css_classes' => $css_classes,
									'badge_label' => $badge,
								)
							);
							$html .= ob_get_clean();
							if ( $fetch_review > 0 && $fetch_review === $review->get_id() ) {
								$found = true;
							}
						}
						// Closing replies wrapper.
						$html .= '</div>';
					}

					if ( $fetch_review > 0 && $fetch_review === $review->get_id() ) {
						$found = true;
					}
				}

				if ( ! $found && $fetch_review > 0 ) {
					$args['paged'] = $args['paged'] + 1;

					$html .= $this->get_reviews_paged( $html, $args, $fetch_review );
				} elseif ( $args['paged'] < $reviews->max_num_pages ) {
					/* translators: %1$s current reviews, %2$s total reviews */
					$review_count = sprintf( esc_html_x( 'Showing %1$s of %2$s reviews', '[Frontend] Review pagination count', 'yith-woocommerce-advanced-reviews' ), ( $posts_per_page * $args['paged'] ), $reviews->total );

					$html .= sprintf( '<div class="load-more-reviews%4$s">%1$s<br /><span class="load-more-button%4$s" data-page="%2$s">%3$s</span></div>', $review_count, $args['paged'] + 1, esc_html_x( 'Load more', '[Frontend] Load more button text', 'yith-woocommerce-advanced-reviews' ), ( 'yes' === $args['in_popup'] ? '-popup' : '' ) );

				}
			}

			return $html;
		}

		/**
		 * Prepare attachments
		 *
		 * @param YITH_YWAR_Review $review      The current review.
		 * @param WC_Product       $product     The current product.
		 * @param array            $attachments The array of existing attachments.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		private function prepare_attachment( YITH_YWAR_Review $review, WC_Product $product, array $attachments = array() ): array {
			foreach ( array_filter( $review->get_thumb_ids() ) as $attachment_id ) {

				if ( is_wp_error( $attachment_id ) ) {
					continue;
				}

				$file_type     = wp_attachment_is( 'video', $attachment_id ) ? 'video' : 'foto';
				$attachments[] = array(
					'full'      => 'video' === $file_type ? wp_get_attachment_url( $attachment_id ) : yith_ywar_get_attachment_image( $product, $attachment_id, array( 700, 700 ), false ),
					'thumb'     => yith_ywar_get_attachment_image( $product, $attachment_id ),
					'type'      => $file_type,
					'review_id' => $review->get_id(),
				);
			}

			return $attachments;
		}

		/**
		 * Load single review
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function load_single_review() {
			$review_id   = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$review      = yith_ywar_get_review( $review_id );
			$product     = wc_get_product( $review->get_product_id() );
			$review_box  = yith_ywar_get_current_review_box( $product );
			$attachments = $this->prepare_attachment( $review, $product );

			ob_start();
			yith_ywar_get_view(
				'frontend/popup/reviews-attachments-gallery.php',
				array(
					'reviews'       => array( $review ),
					'attachments'   => $attachments,
					'review_box'    => $review_box,
					'active_review' => $review->get_id(),
				)
			);
			$html = ob_get_clean();

			wp_send_json_success( $html );
		}

		/**
		 * Load review attachments
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function load_review_attachments() {
			$review_id   = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$review      = yith_ywar_get_review( $review_id );
			$product     = wc_get_product( $review->get_product_id() );
			$attachments = $this->prepare_attachment( $review, $product );

			ob_start();
			yith_ywar_get_view(
				'frontend/popup/attachments-gallery.php',
				array(
					'type'        => 'lightbox',
					'attachments' => $attachments,
				)
			);
			$html = ob_get_clean();

			wp_send_json_success( $html );
		}

		/**
		 * Load reviews with attachments
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function load_reviews_with_attachments() {
			$review_id     = ! empty( $_POST['active_review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['active_review_id'] ) ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$active_review = yith_ywar_get_review( $review_id );
			$product       = wc_get_product( $active_review->get_product_id() );
			$review_box    = yith_ywar_get_current_review_box( $product );
			$review_stats  = yith_ywar_get_review_stats( $product );
			$attachments   = array();
			$reviews       = array();

			foreach ( $review_stats['attachments'] as $review_id => $data ) {
				if ( 'reply' === $data['type'] && 'yes' === yith_ywar_get_option( 'ywar_show_replies_attachments' ) ) {
					continue;
				}
				$review      = yith_ywar_get_review( $review_id );
                if( $review instanceof YITH_YWAR_Review ) {
					$reviews[]   = $review;
					$attachments = $this->prepare_attachment( $review, $product, $attachments );
                }
			}

			ob_start();
			yith_ywar_get_view(
				'frontend/popup/reviews-attachments-gallery.php',
				array(
					'reviews'       => $reviews,
					'attachments'   => $attachments,
					'review_box'    => $review_box,
					'active_review' => $active_review->get_id(),
				)
			);
			$html = ob_get_clean();

			wp_send_json_success( $html );
		}

		/**
		 * Manages the review "Like" button
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function like_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$review_id = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$user_id   = ! empty( $_POST['user_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) ) : 0;
			//phpcs:enable

			$review = yith_ywar_get_review( $review_id );
			if ( $review ) {
				$votes_list  = $review->get_votes();
				$votes_count = $review->get_upvotes_count();

				if ( isset( $votes_list[ $user_id ] ) && 1 === $votes_list[ $user_id ] ) {
					unset( $votes_list[ $user_id ] );
					--$votes_count;
					$selected = false;
					/* translators: %s upvotes count */
					$message = sprintf( _nx( '%s person found this helpful', '%s people found this helpful', esc_html( $votes_count ), '[Frontend] Review helpful text', 'yith-woocommerce-advanced-reviews' ), esc_html( $votes_count ) );
				} else {
					$votes_list[ $user_id ] = 1;
					++$votes_count;
					$selected = true;
					if ( $votes_count > 1 ) {
						/* translators: %s upvotes count */
						$message = sprintf( _nx( 'You and %s person found this helpful', 'You and %s people found this helpful', esc_html( $votes_count - 1 ), '[Frontend] Review helpful text', 'yith-woocommerce-advanced-reviews' ), esc_html( $votes_count - 1 ) );
					} else {
						$message = esc_html_x( 'You found this helpful', '[Frontend] Review helpful text', 'yith-woocommerce-advanced-reviews' );
					}
				}
				$review->set_votes( $votes_list );
				$review->set_upvotes_count( $votes_count );

				if ( $votes_count >= yith_ywar_get_option( 'ywar_highlight_helpful_review' ) ) {
					$review->set_helpful( 'yes' );
				}

				$review->save();

				$data = array(
					'selected' => $selected,
					'message'  => $message,
				);

				wp_send_json_success( $data );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Manages the review "Report" button
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function report_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$review_id = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$user_id   = ! empty( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;
			//phpcs:enable

			$review = yith_ywar_get_review( $review_id );
			if ( $review ) {
				$votes_list  = $review->get_inappropriate_list();
				$votes_count = $review->get_inappropriate_count();

				if ( isset( $votes_list[ $user_id ] ) && 1 === $votes_list[ $user_id ] ) {
					unset( $votes_list[ $user_id ] );
					--$votes_count;
					$selected = false;
					$message  = false;
				} else {
					$votes_list[ $user_id ] = 1;
					++$votes_count;
					$selected = true;
					$message  = esc_html_x( 'Thanks for reporting, we will check the content of the review.', '[Frontend] Review report confirmation text', 'yith-woocommerce-advanced-reviews' );
				}
				$review->set_inappropriate_list( $votes_list );
				$review->set_inappropriate_count( $votes_count );

				if ( $votes_count >= yith_ywar_get_option( 'ywar_hide_inappropriate_review' ) ) {
					$review->set_status( 'reported' );
				}

				$review->save();

				$data = array(
					'selected' => $selected,
					'message'  => $message,
				);

				$mail_args = array(
					'review' => array(
						'id' => $review->get_id(),
					),
				);
				do_action( 'yith_ywar_reported_review_email', $mail_args );

				wp_send_json_success( $data );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Manages the review "Delete" button
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function delete_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$review_id = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$context   = ! empty( $_POST['button_context'] ) ? sanitize_text_field( wp_unslash( $_POST['button_context'] ) ) : 'default';
			//phpcs:enable

			$review = yith_ywar_get_review( $review_id );
			if ( $review ) {
				$review->delete();
				$data = array(
					'message' => '<div class="yith-ywar-review-form-message review-' . $review_id . '" data-review-id="' . $review_id . '" data-context="' . $context . '"><div class="wrapper-title">' . yith_ywar_get_edit_review_form_message( 'deleted' ) . '</div></div>',
				);
				wp_send_json_success( $data );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Restores the review
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function restore_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$review_id = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$context   = ! empty( $_POST['button_context'] ) ? sanitize_text_field( wp_unslash( $_POST['button_context'] ) ) : 'default';
			//phpcs:enable

			$review = yith_ywar_get_review( $review_id );
			if ( $review ) {
				$status = substr( $review->get_meta( '_wp_trash_meta_status' ), 5 );
				$review->update_status( $status );
				$product     = wc_get_product( $review->get_product_id() );
				$review_box  = yith_ywar_get_current_review_box( $product );
				$css_classes = implode(
					'',
					array(
						'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
						'yes' === $review->get_featured() ? ' with-badge featured-review' : '',
						'popup' === $context ? ' in-popup' : '',
						'shortcode' === $context ? ' in-shortcode' : '',
					)
				);

				ob_start();
				yith_ywar_get_view(
					'frontend/review.php',
					array(
						'review'       => $review,
						'review_box'   => $review_box,
						'is_reply'     => false,
						'css_classes'  => $css_classes,
						'in_shortcode' => 'shortcode' === $context,
						'badge_label'  => 'yes' === $review->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '',
					)
				);
				$html = ob_get_clean();
				$data = array(
					'message' => $html,
				);
				wp_send_json_success( $data );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Manages the review "Edit" button
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function edit_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$review_id  = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$review_box = yith_ywar_get_review_box( ! empty( $_POST['box_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['box_id'] ) ) ) : get_option( 'yith-ywar-default-box-id' ) );
			//phpcs:enable

			$review = yith_ywar_get_review( $review_id );
			if ( $review ) {
				$type = $review->get_post_parent() === 0 ? 'review' : 'reply';
				$args = array(
					'form_id'                => "edit-$type-$review_id",
					'type'                   => $type,
					'action'                 => 'edit',
					'review'                 => $review,
					'multi_criteria_enabled' => 'yes' === $review_box->get_enable_multi_criteria() && ! empty( $review_box->get_multi_criteria() ),
					'multi_criteria'         => $review_box->get_multi_criteria(),
					'form_title'             => 'review' === $type ? esc_html_x( 'Edit review', '[Frontend] Edit review form title', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Edit reply', '[Frontend] Edit reply form title', 'yith-woocommerce-advanced-reviews' ),
					'button_text'            => esc_html_x( 'Update', '[Frontend] Update review/reply button text', 'yith-woocommerce-advanced-reviews' ),
					'logged_user'            => is_user_logged_in(),
					'in_reply_of'            => false,
				);

				ob_start();
				yith_ywar_get_view( 'frontend/edit-form/edit-form.php', $args );
				$template = ob_get_clean();
				wp_send_json_success( $template );
			} else {
				wp_send_json_error();
			}
		}

		/**
		 * Manages the review "Edit" button
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function reply_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$review_id   = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$in_reply_of = ! empty( $_POST['in_reply_of'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['in_reply_of'] ) ) ) : 0;
			$review_box  = yith_ywar_get_review_box( ! empty( $_POST['box_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['box_id'] ) ) ) : get_option( 'yith-ywar-default-box-id' ) );
			//phpcs:enable

			$args = array(
				'form_id'                => "new-reply-$review_id",
				'type'                   => 'reply',
				'action'                 => 'create',
				'review'                 => false,
				'multi_criteria_enabled' => 'yes' === $review_box->get_enable_multi_criteria() && ! empty( $review_box->get_multi_criteria() ),
				'multi_criteria'         => $review_box->get_multi_criteria(),
				'form_title'             => esc_html_x( 'Your reply', '[Frontend] New reply form title', 'yith-woocommerce-advanced-reviews' ),
				'button_text'            => esc_html_x( 'Reply', '[Global] Review reply button', 'yith-woocommerce-advanced-reviews' ),
				'logged_user'            => is_user_logged_in(),
				'in_reply_of'            => $in_reply_of,
			);

			ob_start();
			yith_ywar_get_view( 'frontend/edit-form/edit-form.php', $args );
			$template = ob_get_clean();
			wp_send_json_success( $template );
		}

		/**
		 * Creates a new review
		 *
		 * @return void
		 * @throws Exception Validation error.
		 * @since  2.0.0
		 */
		public function submit_new_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$title              = ! empty( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$content            = ! empty( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';
			$rating             = ! empty( $_POST['rating'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['rating'] ) ) ) : 0;
			$multi_rating       = ! empty( $_POST['multi_rating'] ) ? json_decode( wc_clean( wp_unslash( $_POST['multi_rating'] ) ), true ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$product_id         = ! empty( $_POST['product_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) ) : 0;
			$user_id            = ! empty( $_POST['user_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) ) : 0;
			$user_name          = ! empty( $_POST['user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['user_name'] ) ) : '';
			$user_email         = ! empty( $_POST['user_email'] ) ? sanitize_text_field( wp_unslash( $_POST['user_email'] ) ) : '';
			$recaptcha_response = ! empty( $_POST['recaptcha_response'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response'] ) ) : '';
			//phpcs:enable

			$data = array(
				'message'   => false,
				'review_id' => 0,
			);

			try {

				if ( ! empty( $multi_rating ) ) {
					foreach ( $multi_rating as $rating ) {
						if ( 0 === intval( $rating ) ) {
							throw new Exception( 'Validation failed!' );
						}
					}
					$rating = yith_ywar_calculate_avg_rating( $multi_rating );
				} elseif ( 0 === $rating ) {
					throw new Exception( 'Validation failed!' );
				}

				$multiple_review = false;

				if ( $user_id > 0 ) {
					$user       = get_user_by( 'id', $user_id );
					$user_name  = trim( sprintf( '%1$s %2$s', $user->first_name, $user->last_name ) );
					$user_name  = empty( $user_name ) ? $user->nickname : $user_name;
					$user_email = $user->user_email;
				} else {

					if ( '' === $user_name || '' === $user_email ) {
						throw new Exception( 'Validation failed!' );
					}

					$multiple_review = ! yith_ywar_check_user_permissions( 'multiple-reviews' ) && yith_ywar_user_has_commented( $product_id, $user_email, true );
				}

				if ( '' === $content ) {
					throw new Exception( 'Validation failed!' );
				}

				if ( yith_ywar_is_recaptcha_enabled() ) {

					if ( ! $recaptcha_response ) {
						throw new Exception( 'Validation failed!' );
					}

					$secret_key = yith_ywar_get_option( 'ywar_recaptcha_secret_key' );
					$response   = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_response );

					if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
						throw new Exception( 'Validation failed!' );
					} else {
						$response_keys = json_decode( $response['body'], true );
						if ( 1 !== intval( $response_keys['success'] ) ) {
							throw new Exception( 'Validation failed!' );
						}
					}
				}

				$duplicated = yith_ywar_get_reviews(
					array(
						'post_status'    => 'all',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'post_parent'    => 0,
						's'              => $content,
						'search_columns' => 'post_content',
						'exact'          => true,
						//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'meta_query'     => array(
							'relation' => 'AND',
							array(
								'key'     => '_ywar_product_id',
								'value'   => $product_id,
								'compare' => '=',
							),
							array(
								'key'     => '_ywar_review_author_email',
								'value'   => $user_email,
								'compare' => '=',
							),

						),
					)
				);
				if ( ! empty( $duplicated ) ) {
					$data['message'] = '<div class="yith-ywar-review-form-message"><div class="wrapper-title">' . yith_ywar_get_edit_review_form_message( 'duplicated' ) . '</div></div>';

					wp_send_json_success( $data );
					exit;
				}
				if ( ! $multiple_review ) {
					$thumb_ids        = ! empty( $_FILES ) ? $this->upload_attachments( $_FILES ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
					$auto_approvation = 'yes' === yith_ywar_get_option( 'ywar_review_autoapprove' );
					$guest_cookie     = 0 === $user_id ? yith_ywar_set_guest_cookie( 'reviewed' ) : '';

					$fields = array(
						'title'                 => $title,
						'content'               => $content,
						'rating'                => $rating,
						'multi_rating'          => $multi_rating,
						'product_id'            => $product_id,
						'status'                => $auto_approvation || yith_ywar_user_is_staff_member( $user_id ) ? 'approved' : 'pending',
						'review_user_id'        => $user_id,
						'review_author'         => $user_name,
						'review_author_email'   => $user_email,
						'review_author_IP'      => WC_Geolocation::get_external_ip_address(),
						'review_author_country' => WC_Geolocation::geolocate_ip( '', true )['country'],
						'thumb_ids'             => $thumb_ids,
						'guest_cookie'          => $guest_cookie,
					);
					$review = new YITH_YWAR_Review();

					foreach ( $fields as $field => $value ) {
						$review->{"set_$field"}( $value );
					}

					$review->save();

					$this->update_admin_widget_transient( $review->get_id() );

					$mail_args = array(
						'reviewer_info' => array(
							'name'  => $user_name,
							'email' => $user_email,
						),
						'review'        => array(
							'id'         => $review->get_id(),
							'product_id' => $product_id,
							'text'       => $review->get_content(),
						),
					);
					do_action( 'yith_ywar_new_review_email', $mail_args );

					if ( ! $auto_approvation && ! yith_ywar_user_is_staff_member( $review->get_review_user_id() ) ) {
						$review_box = yith_ywar_get_current_review_box( wc_get_product( $product_id ) );
						ob_start();
						?>
						<div class="yith-ywar-review-form-message">
							<div class="wrapper-title">
								<?php echo wp_kses_post( yith_ywar_get_edit_review_form_message( 'pending-approval' ) ); ?>
							</div>
							<div class="wrapper-content">
								<?php
								yith_ywar_get_view(
									'frontend/review.php',
									array(
										'review'       => $review,
										'review_box'   => $review_box,
										'is_reply'     => false,
										'css_classes'  => 'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
										'badge_label'  => '',
										'hide_buttons' => true,
									)
								);
								?>
							</div>
						</div>
						<?php
						$html            = ob_get_clean();
						$data['message'] = $html;
					} else {
						$data['review_id'] = $review->get_id();
					}
				} else {
					$data['message'] = '<div class="yith-ywar-review-form-message"><div class="wrapper-title">' . yith_ywar_get_edit_review_form_message( 'already-reviewed' ) . '</div></div>';
				}
				wp_send_json_success( $data );

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		}

		/**
		 * Creates a new reply
		 *
		 * @return void
		 * @throws Exception Validation error.
		 * @since  2.0.0
		 */
		public function submit_new_reply() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$title              = ! empty( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$content            = ! empty( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';
			$review_id          = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$in_reply_of        = ! empty( $_POST['in_reply_of'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['in_reply_of'] ) ) ) : 0;
			$user_id            = ! empty( $_POST['user_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) ) : 0;
			$user_name          = ! empty( $_POST['user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['user_name'] ) ) : '';
			$user_email         = ! empty( $_POST['user_email'] ) ? sanitize_text_field( wp_unslash( $_POST['user_email'] ) ) : '';
			$recaptcha_response = ! empty( $_POST['recaptcha_response'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response'] ) ) : '';
			$in_popup           = ! empty( $_POST['popup'] ) && 'no' !== $_POST['popup'] ? sanitize_text_field( wp_unslash( $_POST['popup'] ) ) : 'no';
			//phpcs:enable

			try {

				if ( $user_id > 0 ) {
					$user       = get_user_by( 'id', $user_id );
					$user_name  = sprintf( '%1$s %2$s', $user->first_name, $user->last_name );
					$user_email = $user->user_email;
				} elseif ( 0 === $user_id && ( '' === $user_name || '' === $user_email ) ) {
					throw new Exception( 'Validation failed!' );
				}

				if ( '' === $content ) {
					throw new Exception( 'Validation failed!' );
				}

				if ( yith_ywar_is_recaptcha_enabled() ) {

					if ( ! $recaptcha_response ) {
						throw new Exception( 'Validation failed!' );
					}

					$secret_key = yith_ywar_get_option( 'ywar_recaptcha_secret_key' );
					$response   = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_response );

					if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
						throw new Exception( 'Validation failed!' );
					} else {
						$response_keys = json_decode( $response['body'], true );
						if ( 1 !== intval( $response_keys['success'] ) ) {
							throw new Exception( 'Validation failed!' );
						}
					}
				}

				$parent_review = yith_ywar_get_review( $review_id );

				$duplicated = yith_ywar_get_reviews(
					array(
						'post_status'    => 'all',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'post_parent'    => $review_id,
						's'              => $content,
						'search_columns' => 'post_content',
						'exact'          => true,
						//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'meta_query'     => array(
							'relation' => 'AND',
							array(
								'key'     => '_ywar_product_id',
								'value'   => $parent_review->get_product_id(),
								'compare' => '=',
							),
							array(
								'key'     => '_ywar_review_author_email',
								'value'   => $user_email,
								'compare' => '=',
							),

						),
					)
				);
				if ( ! empty( $duplicated ) ) {
					$data = array(
						'message' => '<div class="yith-ywar-review-form-message"><div class="wrapper-title">' . yith_ywar_get_edit_review_form_message( 'duplicated' ) . '</div></div>',
					);

					wp_send_json_success( $data );
					exit;
				}
				$thumb_ids        = ! empty( $_FILES ) ? $this->upload_attachments( $_FILES ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$auto_approvation = 'yes' === yith_ywar_get_option( 'ywar_review_autoapprove' );
				$guest_cookie     = 0 === $user_id ? yith_ywar_set_guest_cookie( 'reviewed' ) : '';

				$fields = array(
					'title'                 => $title,
					'content'               => $content,
					'status'                => $auto_approvation || yith_ywar_user_is_staff_member( $user_id ) ? 'approved' : 'pending',
					'post_parent'           => $review_id,
					'parent_comment_id'     => $parent_review->get_comment_id(),
					'review_user_id'        => $user_id,
					'review_author'         => $user_name,
					'review_author_email'   => $user_email,
					'review_author_IP'      => WC_Geolocation::get_external_ip_address(),
					'review_author_country' => WC_Geolocation::geolocate_ip( '', true )['country'],
					'product_id'            => $parent_review->get_product_id(),
					'thumb_ids'             => $thumb_ids,
					'in_reply_of'           => $in_reply_of,
					'guest_cookie'          => $guest_cookie,
				);
				$reply  = new YITH_YWAR_Review();

				foreach ( $fields as $field => $value ) {
					$reply->{"set_$field"}( $value );
				}

				$reply->save();

				$this->update_admin_widget_transient( $reply->get_id(), true );

				$mail_args = array(
					'user'          => array(
						'customer_name'      => $parent_review->get_review_author(),
						'customer_last_name' => '',
						'customer_email'     => $parent_review->get_review_author_email(),
					),
					'reviewer_info' => array(
						'name'  => $user_name,
						'email' => $user_email,
					),
					'review'        => array(
						'id'         => $reply->get_id(),
						'product_id' => $reply->get_product_id(),
						'text'       => $reply->get_content(),
					),
				);
				do_action( 'yith_ywar_new_reply_email', $mail_args );

				if ( ! $auto_approvation && ! yith_ywar_user_is_staff_member( $reply->get_review_user_id() ) ) {
					$data = array(
						'message' => '<div class="yith-ywar-review-form-message"><div class="wrapper-title">' . yith_ywar_get_edit_review_form_message( 'pending-approval' ) . '</div></div>',
					);
				} else {
					$is_staff    = yith_ywar_user_is_staff_member( $reply->get_review_user_id() ) && 'yes' === yith_ywar_get_option( 'ywar_show_staff_badge' );
					$css_classes = implode(
						'',
						array(
							'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
							' review-reply',
							$is_staff ? ' with-badge staff-review' : '',
							! $is_staff && 'yes' === $reply->get_featured() ? ' with-badge featured-review' : '',
							'yes' === $in_popup ? ' in-popup' : '',
						)
					);
					$badge       = $is_staff ? yith_ywar_get_option( 'ywar_staff_badge' )['label'] : ( 'yes' === $reply->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '' );
					$product     = wc_get_product( $reply->get_product_id() );
					$review_box  = yith_ywar_get_current_review_box( $product );
					ob_start();
					yith_ywar_get_view(
						'frontend/review.php',
						array(
							'review'      => $reply,
							'review_box'  => $review_box,
							'is_reply'    => true,
							'css_classes' => $css_classes,
							'badge_label' => $badge,
						)
					);
					$html = ob_get_clean();
					$data = array(
						'review_id' => "#review-{$reply->get_id()}",
						'html'      => $html,
					);
				}
				wp_send_json_success( $data );

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		}

		/**
		 * Updates an existing review
		 *
		 * @return void
		 * @throws Exception Validation error.
		 * @since  2.0.0
		 */
		public function submit_edit_review() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$title              = ! empty( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$content            = ! empty( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';
			$rating             = ! empty( $_POST['rating'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['rating'] ) ) ) : 0;
			$multi_rating       = ! empty( $_POST['multi_rating'] ) ? json_decode( sanitize_textarea_field( wp_unslash( $_POST['multi_rating'] ) ), true ) : array();
			$review_id          = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$attachments        = ! empty( $_POST['attachments'] ) ? sanitize_text_field( wp_unslash( $_POST['attachments'] ) ) : '';
			$recaptcha_response = ! empty( $_POST['recaptcha_response'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response'] ) ) : '';
			$review_box         = yith_ywar_get_review_box( ! empty( $_POST['box_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['box_id'] ) ) ) : get_option( 'yith-ywar-default-box-id' ) );
			$in_popup           = ! empty( $_POST['popup'] ) && 'no' !== $_POST['popup'] ? sanitize_text_field( wp_unslash( $_POST['popup'] ) ) : 'no';
			//phpcs:enable

			try {

				if ( ! empty( $multi_rating ) ) {
					foreach ( $multi_rating as $rating ) {
						if ( 0 === intval( $rating ) ) {
							throw new Exception( 'Validation failed!' );
						}
					}
					$rating = yith_ywar_calculate_avg_rating( $multi_rating );
				} elseif ( 0 === $rating ) {
					throw new Exception( 'Validation failed!' );
				}

				if ( '' === $content ) {
					throw new Exception( 'Validation failed!' );
				}

				if ( yith_ywar_is_recaptcha_enabled() ) {

					if ( ! $recaptcha_response ) {
						throw new Exception( 'Validation failed!' );
					}

					$secret_key = yith_ywar_get_option( 'ywar_recaptcha_secret_key' );
					$response   = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_response );

					if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
						throw new Exception( 'Validation failed!' );
					} else {
						$response_keys = json_decode( $response['body'], true );
						if ( 1 !== intval( $response_keys['success'] ) ) {
							throw new Exception( 'Validation failed!' );
						}
					}
				}
				$old_attachments = '' !== $attachments ? array_filter( explode( ',', $attachments ) ) : array();
				$new_attachments = ! empty( $_FILES ) ? $this->upload_attachments( $_FILES ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$thumb_ids       = array_merge( $old_attachments, $new_attachments );
				$fields          = array(
					'title'        => $title,
					'content'      => $content,
					'rating'       => $rating,
					'multi_rating' => $multi_rating,
					'thumb_ids'    => $thumb_ids,
				);
				$review          = yith_ywar_get_review( $review_id );

				foreach ( $fields as $field => $value ) {
					$review->{"set_$field"}( $value );
				}

				$review->save();

				// Due to an issue while performing the "array_replace_recursive" function inside te "apply_changes" method of WC_Data class, it's necessary to re-instantiate the Review object in order to retrieve the correct data.
				$review      = yith_ywar_get_review( $review_id );
				$css_classes = implode(
					'',
					array(
						'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
						'yes' === $review->get_featured() ? ' with-badge featured-review' : '',
						'yes' === $in_popup ? ' in-popup' : '',
					)
				);

				ob_start();
				yith_ywar_get_view(
					'frontend/review.php',
					array(
						'review'      => $review,
						'review_box'  => $review_box,
						'is_reply'    => false,
						'css_classes' => $css_classes,
						'badge_label' => 'yes' === $review->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '',
					)
				);

				$html = ob_get_clean();
				$data = array(
					'review_id' => "#review-$review_id",
					'html'      => $html,
				);
				wp_send_json_success( $data );

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		}

		/**
		 * Updates an existing reply
		 *
		 * @return void
		 * @throws Exception Validation error.
		 * @since  2.0.0
		 */
		public function submit_edit_reply() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing
			$title              = ! empty( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
			$content            = ! empty( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';
			$review_id          = ! empty( $_POST['review_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) ) : 0;
			$recaptcha_response = ! empty( $_POST['recaptcha_response'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_response'] ) ) : '';
			$attachments        = ! empty( $_POST['attachments'] ) ? sanitize_text_field( wp_unslash( $_POST['attachments'] ) ) : '';
			$review_box         = yith_ywar_get_review_box( ! empty( $_POST['box_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['box_id'] ) ) ) : get_option( 'yith-ywar-default-box-id' ) );
			$in_popup           = ! empty( $_POST['popup'] ) && 'no' !== $_POST['popup'] ? sanitize_text_field( wp_unslash( $_POST['popup'] ) ) : 'no';
			//phpcs:enable

			try {

				if ( '' === $content ) {
					throw new Exception( 'Validation failed!' );
				}

				if ( yith_ywar_is_recaptcha_enabled() ) {

					if ( ! $recaptcha_response ) {
						throw new Exception( 'Validation failed!' );
					}

					$secret_key = yith_ywar_get_option( 'ywar_recaptcha_secret_key' );
					$response   = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_response );

					if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
						throw new Exception( 'Validation failed!' );
					} else {
						$response_keys = json_decode( $response['body'], true );
						if ( 1 !== intval( $response_keys['success'] ) ) {
							throw new Exception( 'Validation failed!' );
						}
					}
				}

				$old_attachments = '' !== $attachments ? array_filter( explode( ',', $attachments ) ) : array();
				$new_attachments = ! empty( $_FILES ) ? $this->upload_attachments( $_FILES ) : array(); //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$thumb_ids       = array_merge( $old_attachments, $new_attachments );
				$fields          = array(
					'title'     => $title,
					'content'   => $content,
					'thumb_ids' => $thumb_ids,
				);
				$reply           = yith_ywar_get_review( $review_id );

				foreach ( $fields as $field => $value ) {
					$reply->{"set_$field"}( $value );
				}

				$reply->save();

				$is_staff    = yith_ywar_user_is_staff_member( $reply->get_review_user_id() ) && 'yes' === yith_ywar_get_option( 'ywar_show_staff_badge' );
				$css_classes = implode(
					'',
					array(
						'avatar-' . esc_attr( yith_ywar_get_option( 'ywar_avatar_name_position' ) ) . '-review',
						' review-reply',
						$is_staff ? ' with-badge staff-review' : '',
						! $is_staff && 'yes' === $reply->get_featured() ? ' with-badge featured-review' : '',
						'yes' === $in_popup ? ' in-popup' : '',
					)
				);

				$badge = $is_staff ? yith_ywar_get_option( 'ywar_staff_badge' )['label'] : ( 'yes' === $reply->get_featured() ? yith_ywar_get_option( 'ywar_featured_badge' )['label'] : '' );

				ob_start();
				yith_ywar_get_view(
					'frontend/review.php',
					array(
						'review'      => $reply,
						'review_box'  => $review_box,
						'is_reply'    => true,
						'css_classes' => $css_classes,
						'badge_label' => $badge,
					)
				);

				$html = ob_get_clean();
				$data = array(
					'review_id' => "#review-{$reply->get_id()}",
					'html'      => $html,
				);
				wp_send_json_success( $data );

			} catch ( Exception $e ) {
				wp_send_json_error( $e->getMessage() );
			}
		}

		/**
		 * Update admin widget transient
		 *
		 * @param int  $id       Review/Reply ID.
		 * @param bool $is_reply Check if it is a reply.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function update_admin_widget_transient( int $id, bool $is_reply = false ) {
			$index                 = $is_reply ? 'replies' : 'reviews';
			$transient             = get_transient( YITH_YWAR::TRANSIENT );
			$transient['total']    = $transient['total'] + 1;
			$transient[ $index ][] = $id;

			set_transient( YITH_YWAR::TRANSIENT, $transient );
		}

		/**
		 * Upload review attachments
		 *
		 * @param array $file_list The list of files to upload.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		private function upload_attachments( array $file_list ): array {
			$attachments = array();

			foreach ( $file_list as $key => $file ) {
				add_filter( 'upload_dir', array( $this, 'attachment_folder' ) );
				$att_id = media_handle_upload( $key, 0 );
				remove_filter( 'upload_dir', array( $this, 'attachment_folder' ) );

				if ( ! is_wp_error( $att_id ) ) {
					$attachments[] = $att_id;
				}
			}

			return $attachments;
		}

		/**
		 * Set plugin attachment folder
		 *
		 * @param array $dir_data The default upload directory.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function attachment_folder( array $dir_data ): array {
			$custom_dir = 'ywar_reviews_uploaded_files';

			return array(
				'path'    => "{$dir_data['basedir']}/$custom_dir",
				'url'     => "{$dir_data['baseurl']}/$custom_dir",
				'subdir'  => $dir_data['subdir'],
				'basedir' => $dir_data['basedir'],
				'baseurl' => $dir_data['baseurl'],
				'error'   => $dir_data['error'],
			);
		}

		/**
		 * Fixes the tab name if handled by the esc_html() function.
		 *
		 * @param string|null $safe_text The escaped text.
		 * @param string|null $text      The original text.
		 *
		 * @return string|null
		 * @since  2.0.5
		 */
		public function fix_tab_name_if_escaped( $safe_text, $text ) {
			if ( strpos( $text, 'yith-ywar-tab-title' ) !== -1 ) {
				$safe_text = $text;
			}

			return $safe_text;
		}
	}
}
