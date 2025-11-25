<?php
/**
 * Class YITH_YWAR_Review_Post_Type_Admin
 *
 * @package YITH\AdvancedReviews\Admin
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Review_Post_Type_Admin' ) ) {
	/**
	 * Class YITH_YWAR_Review_Post_Type_Admin
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Admin
	 */
	class YITH_YWAR_Review_Post_Type_Admin extends YITH_YWAR_Post_Type_Admin {

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = YITH_YWAR_Post_Types::REVIEWS;

		/**
		 * YITH_YWAR_Review_Post_Type_Admin constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			parent::__construct();

			add_action( 'yith_ywar_post_process_reviews_meta', array( $this, 'save' ), 10, 1 );
			add_filter( 'bulk_post_updated_messages', array( $this, 'bulk_messages' ), 10, 2 );
			add_filter( "views_edit-$this->post_type", array( $this, 'edit_views' ) );
			add_action( "add_meta_boxes_$this->post_type", array( $this, 'manage_meta_boxes' ), 15 );
			add_action( 'edit_form_after_title', array( $this, 'print_review_content' ) );
			add_action( 'admin_footer', array( $this, 'flush_transient' ) );
			add_filter( 'screen_options_show_screen', array( $this, 'screen_options_show' ), 10, 2 );
			add_filter( 'post_class', array( $this, 'set_row_post_class' ), 10, 3 );
			add_action( 'yith_ywar_admin_ajax_change_review_status', array( $this, 'ajax_change_review_status' ) );
			add_action( 'yith_ywar_admin_ajax_get_attachment_image', array( $this, 'ajax_get_attachment_image' ) );
			add_action( 'yith_ywar_admin_ajax_get_product_rating', array( $this, 'ajax_get_product_rating' ) );
			add_action( 'yith_ywar_admin_ajax_get_user_data', array( $this, 'ajax_get_user_data' ) );
			add_action( 'yith_plugin_fw_get_field_after', array( $this, 'add_yith_ui' ) );
		}

		/**
		 * Filters whether to show the Screen Options tab.
		 *
		 * @param bool      $show_screen Whether to show Screen Options tab.
		 * @param WP_Screen $screen      Current WP_Screen instance.
		 *
		 * @return bool
		 * @since 2.0.4
		 */
		public function screen_options_show( bool $show_screen, WP_Screen $screen ): bool {

			if ( isset( $screen->post_type ) && $screen->post_type === $this->post_type ) {
				$show_screen = false;
			}

			return $show_screen;
		}

		/**
		 * Add additional element after print the field.
		 *
		 * @param array $field The field.
		 *
		 * @return void
		 * @since  2.1.0
		 */
		public function add_yith_ui( $field ) {

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;

			if ( isset( $screen->post_type ) && $screen->post_type === $this->post_type ) {
				switch ( $field['type'] ) {
					case 'datepicker':
						echo '<span class="yith-icon yith-icon-calendar yith-icon--right-overlay"></span>';
						break;
					default:
						break;
				}
			}
		}

		/**
		 * Checks if the list is filtered
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		protected function is_the_list_filtered(): bool {
			return ! empty( $_GET['rating'] ) || ! empty( $_GET['product_id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Add additional CSS class if the review/reply is new.
		 *
		 * @param array $classes   An array of post class names.
		 * @param array $css_class An array of additional class names added to the post (Unused).
		 * @param int   $post_id   The post ID.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function set_row_post_class( array $classes, array $css_class, int $post_id ): array {

			if ( ! $post_id || get_post_type( $post_id ) !== $this->post_type ) {
				return $classes;
			}

			$new_elements = get_transient( YITH_YWAR::TRANSIENT );

			if ( ( isset( $new_elements['reviews'] ) && in_array( $post_id, $new_elements['reviews'], true ) ) || ( isset( $new_elements['replies'] ) && in_array( $post_id, $new_elements['replies'], true ) ) ) {
				$classes[] = 'yith-ywar-new-element';
			}

			return $classes;
		}

		/**
		 * Flush the new reviews/replies transient
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function flush_transient() {
			if ( $this->is_post_type_list() ) {
				set_transient(
					YITH_YWAR::TRANSIENT,
					array(
						'total'   => 0,
						'reviews' => array(),
						'replies' => array(),
					)
				);
			}
		}

		/**
		 * Remove "mine" view element
		 *
		 * @param array $views The list of views.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function edit_views( array $views ): array {

			if ( isset( $views['mine'] ) ) {
				unset( $views['mine'] );
			}

			return $views;
		}

		/**
		 * Render any custom filters and search inputs for the list table.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_filters() {

			$user           = ! empty( $_REQUEST['user'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$product_id     = ! empty( $_REQUEST['product_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['product_id'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$rating         = ! empty( $_REQUEST['rating'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['rating'] ) ) : 'all'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$rating_filter  = array(
				'id'      => 'rating',
				'name'    => 'rating',
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'value'   => $rating,
				'options' => array(
					'all' => esc_html_x( 'All ratings', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ),
					/* translators: %s amount of stars */
					'1'   => sprintf( esc_html_x( '%s star', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 1 ),
					/* translators: %s amount of stars */
					'2'   => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 2 ),
					/* translators: %s amount of stars */
					'3'   => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 3 ),
					/* translators: %s amount of stars */
					'4'   => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 4 ),
					/* translators: %s amount of stars */
					'5'   => sprintf( esc_html_x( '%s stars', '[Global] Rating description', 'yith-woocommerce-advanced-reviews' ), 5 ),
				),
			);
			$product_filter = array(
				'id'       => 'product_id',
				'name'     => 'product_id',
				'type'     => 'ajax-products',
				'value'    => $product_id,
				'multiple' => false,
				'data'     => array(
					'placeholder' => esc_html_x( 'Search for a product', '[Global] Ajax field placeholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
					'allow_clear' => true,
				),
			);

			$user_filter = array(
				'id'                => 'user',
				'name'              => 'user',
				'type'              => 'text',
				'value'             => $user,
				'custom_attributes' => array(
					'placeholder' => esc_html_x( 'Search by username or email', '[Admin panel] Filter placheholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
				),
			);

			?>
			<div class="yith-ywar-review-list-filters yith-plugin-ui">
				<?php yith_plugin_fw_get_field( $rating_filter, true ); ?>
				<?php yith_plugin_fw_get_field( $product_filter, true ); ?>
				<?php yith_plugin_fw_get_field( $user_filter, true ); ?>
			</div>
			<?php
		}

		/**
		 * Handle any custom filters.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		protected function query_filters( $query_vars ): array {

			$user       = ! empty( $_REQUEST['user'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['user'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$product_id = ! empty( $_REQUEST['product_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['product_id'] ) ) : false; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$rating     = ! empty( $_REQUEST['rating'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['rating'] ) ) : 'all'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$meta_query = ! empty( $query_vars['meta_query'] ) ? $query_vars['meta_query'] : array();
			$changed    = false;

			if ( 'all' !== $rating ) {
				$changed = true;

				switch ( $rating ) {
					case '1':
						$min_value = 0.5;
						$max_value = 1.5;
						break;
					case '2':
						$min_value = 1.5;
						$max_value = 2.5;
						break;
					case '3':
						$min_value = 2.5;
						$max_value = 3.5;
						break;
					case '4':
						$min_value = 3.5;
						$max_value = 4.5;
						break;
					default:
						$min_value = 4.5;
						$max_value = 5;
				}

				$meta_query[] = array(
					'key'     => '_ywar_rating',
					'value'   => $min_value,
					'compare' => '>',
				);
				$meta_query[] = array(
					'key'     => '_ywar_rating',
					'value'   => $max_value,
					'compare' => '<=',
				);
			}

			if ( $product_id ) {
				$changed      = true;
				$meta_query[] = array(
					'key'   => '_ywar_product_id',
					'value' => absint( $product_id ),
				);
			}

			if ( $user ) {
				$changed      = true;
				$meta_query[] = array(
					'relation' => 'OR',
					array(
						'key'     => '_ywar_review_author',
						'value'   => $user,
						'compare' => 'LIKE',
					),
					array(
						'key'     => '_ywar_review_author_email',
						'value'   => $user,
						'compare' => 'LIKE',
					),
				);
			}

			if ( $changed ) {
				$query_vars['meta_query'] = $meta_query; //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}

			$query_vars['orderby']                = array( 'date' => 'DESC' );
			$query_vars['posts_per_page']         = 20;
			$query_vars['posts_per_archive_page'] = 20;
			$query_vars['fields']                 = 'all';

			return apply_filters( 'yith_ywar_admin_query_filters_vars', $query_vars );
		}

		/**
		 * Return the post_type settings placeholder.
		 *
		 * @return array Array of settings: title_placeholder, title_description, updated_messages.
		 * @since  2.0.0
		 */
		protected function get_post_type_settings(): array {
			return array(
				'updated_messages' => array(
					1  => esc_html_x( 'Review updated.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					4  => esc_html_x( 'Review updated.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					6  => esc_html_x( 'Review created.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					7  => esc_html_x( 'Review saved.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					8  => esc_html_x( 'Review submitted.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
					10 => esc_html_x( 'Review draft updated.', '[Admin panel] Post updated message', 'yith-woocommerce-advanced-reviews' ),
				),
				'hide_views'       => true,
			);
		}

		/**
		 * Retrieve an array of parameters for blank state.
		 *
		 * @return array{
		 * @type string $icon_url The icon URL.
		 * @type string $message  The message to be shown.
		 * @type string $cta      The call-to-action button title.
		 * @type string $cta_icon The call-to-action button icon.
		 * @type string $cta_url  The call-to-action button URL.
		 *                        }
		 * @since  2.0.0
		 */
		protected function get_blank_state_params(): array {
			return array(
				'icon_url' => YITH_YWAR_ASSETS_URL . '/images/review-empty.svg',
				/* translators: %s BR tag */
				'message'  => sprintf( esc_html_x( 'No customer reviews yet.%sCreate your first review and publish it!', '[Admin panel] Empty state text', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
				'cta'      => array(
					'title' => esc_html_x( 'Create review', '[Admin panel] Button Label', 'yith-woocommerce-advanced-reviews' ),
					'url'   => add_query_arg( array( 'post_type' => $this->post_type ), admin_url( 'post-new.php' ) ),
				),
			);
		}

		/**
		 * Define which columns to show on this screen.
		 *
		 * @param array $columns Existing columns.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function define_columns( $columns ): array {
			if ( isset( $columns['title'] ) ) {
				unset( $columns['title'] );
			}

			if ( isset( $columns['date'] ) ) {
				unset( $columns['date'] );
			}

			$columns['product']     = esc_html_x( 'Product', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' );
			$columns['reviewer']    = esc_html_x( 'User', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' );
			$columns['content']     = esc_html_x( 'Content', '[Admin panel] Column name and text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' );
			$columns['rating']      = esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' );
			$columns['review_date'] = esc_html_x( 'Date', '[Admin panel] Column name and text for GDPR exporter or eraser', 'yith-woocommerce-advanced-reviews' );
			$columns['status']      = esc_html_x( 'Status', '[Admin panel] column name', 'yith-woocommerce-advanced-reviews' );
			$columns['actions']     = esc_html_x( 'Actions', '[Admin panel] Column name', 'yith-woocommerce-advanced-reviews' );

			return $columns;
		}

		/**
		 * Render product column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_product_column() {
			$review  = yith_ywar_get_review( $this->post_id );
			$product = wc_get_product( $review->get_product_id() );
			if ( $product ) {
				?>
				<a class="reviewed-product" href="<?php echo esc_url( get_edit_post_link( $review->get_id() ) ); ?>">
					<span class="img-wrapper"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
					<span><?php echo wp_kses_post( $product->get_name() ); ?></span>
				</a>
				<?php
			} else {
				?>
				<a class="reviewed-product" href="<?php echo esc_url( get_edit_post_link( $review->get_id() ) ); ?>">
					<span class="img-wrapper"><?php echo wp_kses_post( wc_placeholder_img( 'thumbnail' ) ); ?></span>
					<span><?php echo esc_html_x( 'Deleted product', '[Admin panel] Generic label for products reviewed that were deleted', 'yith-woocommerce-advanced-reviews' ); ?></span>
				</a>
				<?php
			}
		}

		/**
		 * Render reviewer column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_reviewer_column() {
			$review = yith_ywar_get_review( $this->post_id );
			printf( '%1$s<br /><small>(%2$s)</small>', esc_html( $review->get_review_author() ), esc_html( $review->get_review_author_email() ) );
		}

		/**
		 * Render content column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_content_column() {
			$review            = yith_ywar_get_review( $this->post_id );
			$limit             = 100;
			$show_more_message = esc_html_x( 'Read more', '[Admin panel] Displayed in reviews list if the review text is longer than 100 characters', 'yith-woocommerce-advanced-reviews' );
			$hide_more_message = esc_html_x( 'Hide', '[Admin panel] Displayed in reviews list if the review text is longer than 100 characters', 'yith-woocommerce-advanced-reviews' );
			$full_text         = $review->get_content();
			$hidden            = strlen( $full_text ) > $limit;
			$short_text        = $hidden ? substr( $full_text, 0, $limit ) . '...' : $full_text;

			?>
			<div class="yith-ywar-show-more">
				<div class="short-text"><?php echo wp_kses_post( $short_text ); ?></div>
				<div class="long-text"><?php echo wp_kses_post( $full_text ); ?></div>
				<?php if ( $hidden ) : ?>
					<div class="clear"></div>
					<span class="show-more"><?php echo esc_html( $show_more_message ); ?></span>
					<span class="hide-more"><?php echo esc_html( $hide_more_message ); ?></span>
				<?php endif; ?>
			</div>
			<?php
			if ( $review->get_post_parent() > 0 ) {
				$parent_review_id = $review->get_in_reply_of() > 0 ? $review->get_in_reply_of() : $review->get_post_parent();
				$parent_review    = yith_ywar_get_review( $parent_review_id );
				?>
				<small class="in-reply-to">
					<?php
					/* translators: %s link to parent review */
					printf( esc_html_x( 'In reply to: %s', '[Global] Indicates the user to whom the review is addressed', 'yith-woocommerce-advanced-reviews' ), '<a href="' . esc_url( get_edit_post_link( $parent_review->get_id() ) ) . '">' . esc_attr( $parent_review->get_review_author() ) . '</a>' );
					?>
				</small>
				<?php
			}
		}

		/**
		 * Render rating column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_rating_column() {
			$review = yith_ywar_get_review( $this->post_id );

			if ( $review->get_post_parent() ) {
				return;
			}

			$product           = wc_get_product( $review->get_product_id() );
			$review_box        = yith_ywar_get_current_review_box( $product );
			$multi_criteria_on = $review_box->get_enable_multi_criteria();
			$criteria          = $review_box->get_multi_criteria();

			?>
			<div class="rating-wrapper">
				<div class="overall">
					<span class="single-rating"><?php echo esc_html( $review->get_rating() ); ?></span><br/>
					<?php
					if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) {
						printf( '(%s)', esc_html_x( 'avg.', '[Admin panel] Abbrevation for "average"', 'yith-woocommerce-advanced-reviews' ) );
					}
					?>
				</div>
				<?php if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) : ?>
					<?php $multi_rating = $review->get_multi_rating(); ?>
					<div class="multi-criteria">
						<?php foreach ( $criteria as $criterion_id ) : ?>
							<?php $criterion = get_term_by( 'term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX ); ?>
							<div class="single-criterion">
								<span class="criterion-label"><?php echo esc_html( $criterion->name ); ?>:</span>
								<span class="single-rating criterion-rating"><?php echo isset( $multi_rating[ $criterion_id ] ) ? esc_html( $multi_rating[ $criterion_id ] ) : esc_html( $review->get_rating() ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Render review date column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_review_date_column() {
			$review = yith_ywar_get_review( $this->post_id );

			if ( ! $review || ! $review->get_date_created() ) {
				return;
			}

			echo wp_kses_post( ucwords( date_i18n( get_option( 'date_format' ), $review->get_date_created()->getOffsetTimestamp() ) ) );
		}

		/**
		 * Render status column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_status_column() {
			$review = yith_ywar_get_review( $this->post_id );
			$status = 'ywar-' === substr( $review->get_status(), 0, 5 ) ? substr( $review->get_status(), 5 ) : $review->get_status();

			if ( 'trash' === $status ) {
				// If the review is in the trash we will show the previous status.
				$status = substr( $review->get_meta( '_wp_trash_meta_status' ), 5 );
			}

			$this->print_status_label( $status );
		}

		/**
		 * Render Actions column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_actions_column() {
			$review = yith_ywar_get_review( $this->post_id );

			if ( $review->has_status( 'trash' ) ) {
				$actions = array(
					'type'   => 'action-button',
					'action' => 'show-more',
					'icon'   => 'more',
					'title'  => esc_html_x( 'Manage', '[Admin panel] Action menu title', 'yith-woocommerce-advanced-reviews' ),
					'menu'   => array(
						array(
							'name'   => esc_html_x( 'Restore', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ),
							'action' => 'untrash',
							'url'    => wp_nonce_url( sprintf( get_edit_post_link() . '&amp;action=untrash', $review->get_id() ), 'untrash-post_' . $review->get_id() ),
						),
						array(
							'name'         => esc_html_x( 'Delete permanently', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ),
							'action'       => 'delete',
							'url'          => get_delete_post_link( $review->get_id(), '', true ),
							'confirm_data' => array(
								'title'               => esc_html_x( 'Confirm delete', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
								'message'             => esc_html_x( 'Are you sure you want to delete this review?', '[Admin panel] modal message', 'yith-woocommerce-advanced-reviews' ),
								'confirm-button'      => esc_html_x( 'Yes, delete', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
								'confirm-button-type' => 'delete',
							),
						),
					),
				);
			} else {
				$approved      = $review->has_status( 'approved' );
				$in_reply_of   = $review->get_post_parent() > 0 ? $review->get_id() : 0;
				$parent_review = $review->get_post_parent() === 0 ? $review->get_id() : $review->get_post_parent();
				$product       = wc_get_product( $review->get_product_id() );
				$actions       = array(
					'type'   => 'action-button',
					'action' => 'show-more',
					'icon'   => 'more',
					'title'  => esc_html_x( 'Manage', '[Admin panel] Action menu title', 'yith-woocommerce-advanced-reviews' ),
					'menu'   => array(
						array(
							'name'   => esc_html_x( 'View/Edit', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ),
							'action' => 'edit',
							'url'    => get_edit_post_link( $review->get_id() ),
						),
						array(
							'name'   => esc_html_x( 'Reply', '[Global] Review reply button', 'yith-woocommerce-advanced-reviews' ),
							'action' => 'new',
							'url'    => add_query_arg(
								array(
									'post_type'     => $this->post_type,
									'parent_review' => $parent_review,
									'in_reply_of'   => $in_reply_of,
								),
								admin_url( 'post-new.php' )
							),
						),
						array(
							'name'            => esc_html_x( 'View product', '[Global] Review view product button', 'yith-woocommerce-advanced-reviews' ),
							'open_in_new_tab' => true,
							'url'             => $product ? get_edit_post_link( $product->get_id() ) : '#',
						),
						array(
							'name'  => $approved ? esc_html_x( 'Unapprove', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ) : esc_html_x( 'Approve', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ),
							'data'  => array(
								'action' => $approved ? 'unapprove' : 'approve',
								'id'     => $review->get_id(),
							),
							'class' => 'yith-ywar-change-review-status',
						),
						array(
							'name'         => esc_html_x( 'Move to trash', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ),
							'action'       => 'trash',
							'url'          => get_delete_post_link( $review->get_id(), '', false ),
							'confirm_data' => array(
								'title'               => esc_html_x( 'Confirm trash', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
								'message'             => esc_html_x( 'Are you sure you want to trash this review?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
								'confirm-button'      => esc_html_x( 'Yes, move to trash', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
								'confirm-button-type' => 'delete',
							),
						),
					),
				);
			}

			yith_plugin_fw_get_component( $actions, true );
		}

		/**
		 * Define bulk actions.
		 *
		 * @param array $actions Existing actions.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function define_bulk_actions( $actions ): array {
			if ( isset( $actions['edit'] ) ) {
				unset( $actions['edit'] );

			}
			$actions['approve']   = esc_html_x( 'Approve', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' );
			$actions['unapprove'] = esc_html_x( 'Unapprove', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' );

			return $actions;
		}

		/**
		 * Filters the bulk action updated messages.
		 * By default, custom post types use the messages for the 'post' post type.
		 *
		 * @param array $bulk_messages   Arrays of messages, each keyed by the corresponding post type. Messages are
		 *                               keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
		 * @param array $bulk_counts     Array of item counts for each message, used to build internationalized strings.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function bulk_messages( $bulk_messages, $bulk_counts ) {

			// Since there is no way to add custom actions, use the updated one.
			$action = isset( $_REQUEST['yith_ywar_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['yith_ywar_action'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			switch ( $action ) {
				case 'approve':
					/* translators: %s: Number of review. */
					$updated = _nx( '%s review approved.', '%s reviews approved.', $bulk_counts['updated'], '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' );
					break;
				case 'unapprove':
					/* translators: %s: Number of review. */
					$updated = _nx( '%s review unapproved.', '%s reviews unapproved.', $bulk_counts['updated'], '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' );
					break;
				default:
					/* translators: %s: Number of review. */
					$updated = _nx( '%s review updated.', '%s reviews updated.', $bulk_counts['updated'], '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' );
			}

			$bulk_messages[ YITH_YWAR_Post_Types::REVIEWS ] = array(
				'updated'   => $updated,
				/* translators: %s: Number of review. */
				'deleted'   => _nx( '%s review permanently deleted.', '%s reviews permanently deleted.', $bulk_counts['deleted'], '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ),
				/* translators: %s: Number of review. */
				'trashed'   => _nx( '%s review moved to the Trash.', '%s reviews moved to the Trash.', $bulk_counts['trashed'], '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ),
				/* translators: %s: Number of review. */
				'untrashed' => _nx( '%s review restored from the Trash.', '%s reviews restored from the Trash.', $bulk_counts['untrashed'], '[Admin panel] bulk action message', 'yith-woocommerce-advanced-reviews' ),
			);

			return $bulk_messages;
		}

		/**
		 * Trigger bulk actions to orders
		 *
		 * @param string $redirect_to The redirect address.
		 * @param string $action      The current action.
		 * @param array  $ids         The IDs to process.
		 *
		 * @return  string
		 * @since   2.0.0
		 */
		public function handle_bulk_actions( $redirect_to, $action, $ids ) {

			$count     = 0;
			$processed = false;
			foreach ( $ids as $id ) {
				$review     = yith_ywar_get_review( $id );
				$new_status = 'approve' === $action ? 'approved' : 'pending';

				$review->update_status( $new_status );

				if ( $processed ) {
					++$count;
				}
				$processed = false;
			}

			$redirect_to = add_query_arg(
				array(
					'yith_ywar_action' => $action,
					'updated'          => $count,
				),
				$redirect_to
			);

			return esc_url_raw( $redirect_to );
		}

		/**
		 * Render blank state. Extend to add content.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_blank_state() {
			parent::render_blank_state();

			echo '<style>.page-title-action{ display: none !important; }</style>';
		}

		/**
		 * Disable the screen layout columns, by setting it to 1 column.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function disable_screen_layout_columns() {
			if ( $this->is_post_type_edit() ) {
				get_current_screen()->remove_option( 'layout_columns' );
			}
		}

		/**
		 * Force using the single column layout.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function force_single_column_screen_layout(): int {
			return 2;
		}

		/**
		 * Remove useless metaboxes and add needed ones.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function manage_meta_boxes() {

			global $wp_meta_boxes;

			// Remove all metaboxes to avoid interface pollution by other plugins.
			unset( $wp_meta_boxes[ $this->post_type ] );

			add_meta_box( 'yith-ywar-review-options', esc_html_x( 'Review options', '[Admin panel] Metabox title', 'yith-woocommerce-advanced-reviews' ), array( $this, 'add_review_options_metabox' ), $this->post_type, 'normal', 'high' );
			add_meta_box( 'yith-ywar-review-author', esc_html_x( 'Review author', '[Admin panel] Metabox title', 'yith-woocommerce-advanced-reviews' ), array( $this, 'add_review_author_metabox' ), $this->post_type, 'normal', 'low' );
			add_meta_box( 'yith-ywar-review-files', esc_html_x( 'Review files', '[Admin panel] Metabox title', 'yith-woocommerce-advanced-reviews' ), array( $this, 'add_review_files_metabox' ), $this->post_type, 'advanced', 'default' );
			add_meta_box( 'yith-ywar-review-info', esc_html_x( 'Review info', '[Admin panel] Metabox title', 'yith-woocommerce-advanced-reviews' ), array( $this, 'add_review_info_metabox' ), $this->post_type, 'side', 'high' );
			add_meta_box( 'yith-ywar-review-status', esc_html_x( 'Review status', '[Admin panel] Metabox title', 'yith-woocommerce-advanced-reviews' ), array( $this, 'add_review_status_metabox' ), $this->post_type, 'side', 'default' );
		}

		/**
		 * Print the status label
		 *
		 * @param string $status The status.
		 * @param bool   $big    Check if the "Big" version shoud be printed.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function print_status_label( string $status, bool $big = false ) {
			switch ( $status ) {
				case 'approved':
					$label = esc_html_x( 'Approved', '[Admin panel] Review status label', 'yith-woocommerce-advanced-reviews' );
					break;
				case 'reported':
					$label = esc_html_x( 'Reported', '[Admin panel] Review status label', 'yith-woocommerce-advanced-reviews' );
					break;
				case 'spam':
					$label = esc_html_x( 'Spam', '[Admin panel] Review status label', 'yith-woocommerce-advanced-reviews' );
					break;
				default:
					$status = 'pending';
					$label  = esc_html_x( 'Pending', '[Admin panel] Review status label', 'yith-woocommerce-advanced-reviews' );
			}

			$status = $big ? $status . ' big' : $status;

			printf( '<span class="review-status review-%1$s">%2$s</span>', esc_html( $status ), esc_html( $label ) );
		}

		/**
		 * Get the review's parent ID
		 *
		 * @param YITH_YWAR_Review $review The current review.
		 *
		 * @return false|int
		 * @since  2.0.0
		 */
		private function get_parent_id( YITH_YWAR_Review $review ) {
			return isset( $_REQUEST['parent_review'] ) && '' !== $_REQUEST['parent_review'] ? (int) sanitize_text_field( wp_unslash( $_REQUEST['parent_review'] ) ) : ( $review->get_post_parent() > 0 ? $review->get_post_parent() : false ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Get the in reply of ID
		 *
		 * @param YITH_YWAR_Review $review The current review.
		 *
		 * @return false|int
		 * @since  2.0.0
		 */
		private function get_in_reply_of_id( YITH_YWAR_Review $review ) {
			return isset( $_REQUEST['in_reply_of'] ) && '' !== $_REQUEST['in_reply_of'] ? (int) sanitize_text_field( wp_unslash( $_REQUEST['in_reply_of'] ) ) : ( $review->get_in_reply_of() > 0 ? $review->get_in_reply_of() : false ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Print review content
		 *
		 * @param WP_Post $post The current post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function print_review_content( WP_Post $post ) {

			if ( $this->post_type !== $post->post_type ) {
				return;
			}

			$review            = yith_ywar_get_review( $post );
			$validate_fields   = $this->get_parent_id( $review ) ? array( '#content', '#review_author', '#review_author_email' ) : array( '#content', '#review_author', '#review_author_email', '#rating', '#product_id' );
			$product           = wc_get_product( $review->get_product_id() );
			$review_box        = yith_ywar_get_current_review_box( $product );
			$multi_criteria_on = $review_box->get_enable_multi_criteria();
			$criteria          = $review_box->get_multi_criteria();

			if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) {
				foreach ( $criteria as $criterion_id ) {
					$criterion         = get_term_by( 'term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX );
					$validate_fields[] = "#multi_rating_$criterion->slug";
				}
			}

			$fields = array(
				array(
					'id'    => 'title',
					'title' => esc_html_x( 'Review title (optional)', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'text',
					'value' => $review->has_status( 'auto-draft' ) ? '' : $review->get_title(),
				),
				array(
					'id'    => 'content',
					'title' => esc_html_x( 'Review content', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'textarea',
					'value' => $review->get_content(),
				),
			);
			$this->print_options( $fields );

			?>
			<input type="hidden" id="validate_fields" name="validate_fields" value="<?php echo esc_html( wp_json_encode( $validate_fields ) ); ?>">
			<?php
		}

		/**
		 * Output author metabox
		 *
		 * @param WP_Post $post The current post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_review_author_metabox( WP_Post $post ) {

			if ( $this->post_type !== $post->post_type ) {
				return;
			}

			$review           = yith_ywar_get_review( $post );
			$review_author_id = $review->has_status( 'auto-draft' ) ? wp_get_current_user()->ID : $review->get_review_user_id();
			$author_user      = get_user_by( 'id', $review_author_id );
			$user_info        = ! $author_user ? esc_html_x( 'Guest user', '[Admin panel] label to use when the user is not registered', 'yith-woocommerce-advanced-reviews' ) : sprintf( '<a href="%1$s" target="_blank">%2$s %3$s</a>', esc_url( get_edit_user_link( $author_user->ID ) ), $author_user->first_name, $author_user->last_name );
			$fields           = array(
				array(
					'id'    => 'user_info',
					'title' => esc_html_x( 'User', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'html',
					'html'  => sprintf( '<span class="user-info">%s</span>', $user_info ),
				),
				array(
					'id'    => 'review_user_id',
					'title' => esc_html_x( 'User', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'ajax-customers',
					'value' => $review_author_id,
					'data'  => array(
						'placeholder' => esc_html_x( 'Guest user', '[Admin panel] label to use when the user is not registered', 'yith-woocommerce-advanced-reviews' ),
						'allow_clear' => true,
					),
				),
				array(
					'id'                => 'review_author',
					'title'             => esc_html_x( 'Name', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'text',
					'value'             => $review->has_status( 'auto-draft' ) ? sprintf( '%1$s %2$s', wp_get_current_user()->first_name, wp_get_current_user()->last_name ) : $review->get_review_author(),
					'custom_attributes' => array( 'readonly' => true ),
				),
				array(
					'id'                => 'review_author_email',
					'title'             => esc_html_x( 'Email', '[Admin panel] Column name - Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'email',
					'value'             => $review->has_status( 'auto-draft' ) ? wp_get_current_user()->user_email : $review->get_review_author_email(),
					'custom_attributes' => array( 'readonly' => true ),
				),
				array(
					'id'                => 'review_author_custom_avatar',
					'title'             => esc_html_x( 'Custom avatar', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'media',
					'allow_custom_url'  => false,
					'class'             => empty( $review->get_review_author_custom_avatar() ) ? 'empty' : '',
					'value'             => $review->get_review_author_custom_avatar(),
					'custom_attributes' => array( 'readonly' => true ),
				),
				array(
					'id'                => 'review_author_country',
					'title'             => esc_html_x( 'User country', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'options'           => WC()->countries->get_countries(),
					'value'             => $review->has_status( 'auto-draft' ) ? substr( get_option( 'woocommerce_default_country' ), 0, 2 ) : $review->get_review_author_country(),
					'custom_attributes' => array( 'disabled' => true ),
					'data'              => array(
						'value' => $review->has_status( 'auto-draft' ) ? substr( get_option( 'woocommerce_default_country' ), 0, 2 ) : $review->get_review_author_country(),
					),
				),
			);
			?>
			<div class="editable-fields-wrapper locked">
				<div class="yith-icon yith-icon-edit unlock-edit-fields"></div>
				<?php $this->print_options( $fields ); ?>
			</div>
			<?php
		}

		/**
		 * Output review options metabox
		 *
		 * @param WP_Post $post The current post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_review_options_metabox( WP_Post $post ) {

			if ( $this->post_type !== $post->post_type ) {
				return;
			}

			$review = yith_ywar_get_review( $post );
			$fields = array(
				array(
					'id'    => 'helpful',
					'type'  => 'onoff',
					'title' => esc_html_x( 'Set as helpful', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'value' => $review->get_helpful(),
					'desc'  => esc_html_x( 'Set the review as helpful. This will display the review in the "Most helpful reviews" tab, if enabled.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
				),
				array(
					'id'    => 'featured',
					'title' => esc_html_x( 'Set as featured', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'onoff',
					'value' => $review->get_featured(),
					'desc'  => esc_html_x( 'Set the review as featured. This will make the review appear before all other reviews.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
				),
				array(
					'id'    => 'verified_owner',
					'title' => esc_html_x( 'Set as "Verified buyer"', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'onoff',
					'value' => $review->get_verified_owner(),
					'desc'  => esc_html_x( 'Set the review author as "Verified buyer".', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
				),
				array(
					'id'    => 'stop_reply',
					'title' => esc_html_x( 'Block replies', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'onoff',
					'value' => $review->get_stop_reply(),
					'desc'  => esc_html_x( 'Prevent other users from replying to this review.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
				),
				array(
					'id'    => 'review_edit_blocked',
					'title' => esc_html_x( 'Block review editing', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'onoff',
					'value' => $review->get_review_edit_blocked(),
					'desc'  => esc_html_x( 'Prevent the user from editing this review.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
				),
			);

			if ( $this->get_parent_id( $review ) ) {
				$rating = array(
					'id'    => 'rating',
					'type'  => 'hidden',
					'value' => 0,
				);
			} else {
				$product           = wc_get_product( $review->get_product_id() );
				$review_box        = yith_ywar_get_current_review_box( $product );
				$multi_criteria_on = $review_box->get_enable_multi_criteria();
				$criteria          = $review_box->get_multi_criteria();

				if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) {
					$rating = array(
						'id'       => 'multi_rating',
						'type'     => 'yith-ywar-multi-rating',
						'title'    => esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ),
						'value'    => $review->get_multi_rating(),
						'criteria' => $criteria,
					);
				} else {
					$rating = array(
						'id'    => 'rating',
						'type'  => 'yith-ywar-rating',
						'title' => esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ),
						'value' => ( 0 === $review->get_rating() ) ? '' : $review->get_rating(),
					);
				}
			}
			array_unshift( $fields, $rating );

			$this->print_options( $fields );
		}

		/**
		 * Output reviewed_product metabox
		 *
		 * @param WP_Post $post The current post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_review_info_metabox( WP_Post $post ) {

			if ( $this->post_type !== $post->post_type ) {
				return;
			}

			$review = yith_ywar_get_review( $post );
			$fields = array(
				array(
					'id'    => 'comment_id',
					'type'  => 'hidden',
					'value' => $review->get_comment_id(),
				),
				array(
					'id'    => 'post_parent',
					'type'  => 'hidden',
					'value' => $this->get_parent_id( $review ),
				),
				array(
					'id'    => 'in_reply_of',
					'type'  => 'hidden',
					'value' => $this->get_in_reply_of_id( $review ),
				),
			);

			if ( $review->has_status( 'auto-draft' ) && ! $this->get_parent_id( $review ) ) {
				$fields[] = array(
					'id'    => 'product_id',
					'title' => esc_html_x( 'Product to review', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'ajax-products',
					'class' => 'yith-post-search reviewed-product',
				);
				$fields[] = array(
					'id'    => 'date_created',
					'type'  => 'datepicker',
					'title' => esc_html_x( 'Review date', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'value' => date_i18n( get_option( 'date_format' ), time() ),
					'data'  => array(
						'date-format' => 'yy-mm-dd',
						'max-date'    => 0,
					),
				);
			} elseif ( $this->get_parent_id( $review ) ) {
				$parent_review = yith_ywar_get_review( $this->get_parent_id( $review ) );
				$product       = wc_get_product( $parent_review->get_product_id() );
				ob_start();
				?>
				<br/>
				<br/>
				<a class="reviewed-product" href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank">
					<span class="img-wrapper"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
					<span><?php echo wp_kses_post( $product->get_name() ); ?></span>
				</a>
				<?php
				$reviewed_product = ob_get_clean();
				$fields[]         = array(
					'id'    => 'date_created',
					'type'  => 'datepicker',
					'title' => esc_html_x( 'Review date', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'value' => date_i18n( get_option( 'date_format' ), ( $review->get_date_created() ? $review->get_date_created()->getOffsetTimestamp() : time() ) ),
					'data'  => array(
						'date-format' => 'yy-mm-dd',
						'max-date'    => 0,
					),
				);
				$fields[]         = array(
					'id'    => 'product_id',
					'type'  => 'hidden',
					'value' => $parent_review->get_product_id(),
				);
				$fields[]         = array(
					'id'    => 'parent_comment_id',
					'type'  => 'hidden',
					'value' => $review->get_parent_comment_id() !== 0 ? $review->get_parent_comment_id() : $parent_review->get_comment_id(),
				);
				$fields[]         = array(
					'id'   => 'note',
					'type' => 'html',
					/* translators: %1$s opening tag, %2$s closing tag */
					'html' => sprintf( esc_html_x( 'You are replying to %1$sa review%2$s on the following product: %3$s', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ), '<a target="_blank" href="' . esc_url( get_edit_post_link( $this->get_parent_id( $review ) ) ) . '">', '</a>', $reviewed_product ),
				);
			} else {
				$product = wc_get_product( $review->get_product_id() );

				ob_start();
				if ( $product ) {
					?>
					<a class="reviewed-product" href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank">
						<span class="img-wrapper"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
						<span><?php echo wp_kses_post( $product->get_name() ); ?></span>
					</a>
					<?php
				} else {
					?>
					<span class="reviewed-product">
					<span class="img-wrapper"><?php echo wp_kses_post( wc_placeholder_img( 'thumbnail' ) ); ?></span>
					<span><?php esc_html_x( 'Deleted product', '[Admin panel] Generic label for products reviewed that were deleted', 'yith-woocommerce-advanced-reviews' ); ?></span>
				</span>
					<?php
				}
				$fields[] = array(
					'id'    => 'product',
					'title' => esc_html_x( 'Reviewed product', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'html',
					'html'  => ob_get_clean(),
				);
				$fields[] = array(
					'id'    => 'date_created',
					'type'  => 'datepicker',
					'title' => esc_html_x( 'Review date', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'value' => date_i18n( get_option( 'date_format' ), $review->get_date_created()->getOffsetTimestamp() ),
					'data'  => array(
						'date-format' => 'yy-mm-dd',
						'max-date'    => 0,
					),
				);
				$fields[] = array(
					'id'    => 'product_id',
					'type'  => 'hidden',
					'value' => $review->get_product_id(),
				);

			}

			$upvotes_count = $review->get_upvotes_count();
			if ( $upvotes_count > 0 ) {
				$fields[] = array(
					'id'    => 'upvotes',
					'title' => esc_html_x( 'Upvotes', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'html',
					/* translators: %s number of upvotes */
					'html'  => sprintf( _nx( '%s person found this review helpful', '%s people found this review helpful', $upvotes_count, '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ), "<b>$upvotes_count</b>" ),
				);
			}

			$inappropriate_count = $review->get_inappropriate_count();
			if ( $inappropriate_count > 0 ) {
				$fields[] = array(
					'id'    => 'reportings',
					'title' => esc_html_x( 'Reportings', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'html',
					/* translators: %s number of upvotes */
					'html'  => sprintf( _nx( '%s person found this review inappropriate', '%s people found this review inappropriate', $inappropriate_count, '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ), "<b>$inappropriate_count</b>" ),
				);
			}

			$this->print_options( $fields );
		}

		/**
		 * Output review status metabox
		 *
		 * @param WP_Post $post The current post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_review_status_metabox( WP_Post $post ) {

			if ( $this->post_type !== $post->post_type ) {
				return;
			}

			$review = yith_ywar_get_review( $post );
			$status = 'ywar-' === substr( $review->get_status(), 0, 5 ) ? substr( $review->get_status(), 5 ) : $review->get_status();

			$this->print_status_label( $status, true );

			$fields = array(
				array(
					'id'      => 'status',
					'title'   => esc_html_x( 'Set status', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => yith_ywar_get_review_statuses(),
					'value'   => $review->get_status(),
				),

			);
			$this->print_options( $fields );

			global $post_id;
			$is_updating = ! ! $post_id;

			?>
			<div class="yith-ywar-post-type__actions yith-plugin-ui">
				<?php if ( $is_updating ) : ?>
					<?php
					$confirm_trash = array(
						'title'               => esc_html_x( 'Confirm trash', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
						'message'             => esc_html_x( 'Are you sure you want to trash this review?', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
						'confirm-button'      => esc_html_x( 'Yes, move to trash', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
						'confirm-button-type' => 'delete',
					);
					?>
					<button id="yith-ywar-post-type__save" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl"><?php echo esc_html_x( 'Update review', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ); ?></button>
					<a class="trash-button yith-plugin-fw__require-confirmation-link" <?php yith_plugin_fw_html_data_to_string( $confirm_trash, true ); ?> href="<?php echo esc_url( get_delete_post_link( $review->get_id(), '', false ) ); ?>"><?php echo esc_html_x( 'Move to trash', '[Admin panel] action name', 'yith-woocommerce-advanced-reviews' ); ?></a>
				<?php else : ?>
					<input id="yith-ywar-post-type__save" type="submit" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl" name="publish" value="<?php echo esc_html_x( 'Save review', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ); ?>">
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * Overrides the standard function.
		 *
		 * @param WP_Post $post The post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function print_save_button_in_edit_page( WP_Post $post ) {
			// Silence is golden.
		}

		/**
		 * Output review files metabox
		 *
		 * @param WP_Post $post The current post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_review_files_metabox( WP_Post $post ) {

			if ( $this->post_type !== $post->post_type ) {
				return;
			}
			$review = yith_ywar_get_review( $post );
			$fields = array(
				array(
					'id'    => 'thumb_ids',
					'type'  => 'yith-ywar-attachments',
					'value' => $review->get_thumb_ids(),
				),
			);
			$this->print_options( $fields );
		}

		/**
		 * Print metabox options
		 *
		 * @param array $fields The fields to print.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function print_options( array $fields ) {
			?>
			<div class="yith-plugin-ui yith-plugin-fw">
				<div class="yith-plugin-fw__panel__section__content">
					<?php foreach ( $fields as $field ) : ?>
						<?php
						$field['name'] = $this->get_name_field( $field['id'] );
						$row_classes   = array(
							'yith-plugin-fw__panel__option',
							'yith-plugin-fw__panel__option--' . $field['type'],
						);
						$row_classes   = implode( ' ', array_filter( $row_classes ) );
						?>
						<div class="<?php echo esc_attr( $row_classes ); ?>" <?php echo wp_kses_post( yith_field_deps_data( $field ) ); ?>>
							<?php if ( isset( $field['title'] ) && '' !== $field['title'] ) : ?>
								<div class="yith-plugin-fw__panel__option__label">
									<label for="<?php echo esc_attr( ( $field['id'] ) ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
								</div>
							<?php endif; ?>
							<div class="yith-plugin-fw__panel__option__content">
								<?php yith_plugin_fw_get_field( $field, true, true ); ?>
							</div>
							<?php if ( ! empty( $field['desc'] ) ) : ?>
								<div class="yith-plugin-fw__panel__option__description">
									<?php echo wp_kses_post( $field['desc'] ); ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Set field name
		 *
		 * @param string $name The field name.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function get_name_field( string $name = '' ): string {
			return $this->post_type . '[' . $name . ']';
		}

		/**
		 * Save meta on save post
		 *
		 * @param int $post_id The post ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function save( int $post_id ) {
			// Disable nonce verification notice, since the nonce is already checked!
			if ( isset( $_POST[ $this->post_type ] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$form_fields = wc_clean( wp_unslash( $_POST[ $this->post_type ] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$form_fields = is_array( $form_fields ) ? $form_fields : array();

				if ( ! empty( $form_fields ) ) {
					$review = yith_ywar_get_review( $post_id );

					if ( isset( $form_fields['multi_rating'] ) ) {
						$rating       = yith_ywar_calculate_avg_rating( $form_fields['multi_rating'] );
						$multi_rating = $form_fields['multi_rating'];
					} else {
						$rating       = $form_fields['rating'];
						$multi_rating = array();
					}

					$data = array(
						'title'                       => $form_fields['title'],
						'content'                     => sanitize_textarea_field( wp_unslash( $_POST[ $this->post_type ]['content'] ) ), //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
						'date_created'                => strtotime( $form_fields['date_created'] ),
						'date_modified'               => strtotime( $form_fields['date_created'] ),
						'post_parent'                 => (int) $form_fields['post_parent'] ?? 0,
						'in_reply_of'                 => (int) $form_fields['in_reply_of'] ?? 0,
						'comment_id'                  => $form_fields['comment_id'],
						'parent_comment_id'           => $form_fields['parent_comment_id'] ?? 0,
						'rating'                      => $rating,
						'multi_rating'                => $multi_rating,
						'product_id'                  => $form_fields['product_id'] ?? 0,
						'status'                      => $form_fields['status'],
						'helpful'                     => isset( $form_fields['helpful'] ) ? 'yes' : 'no',
						'featured'                    => isset( $form_fields['featured'] ) ? 'yes' : 'no',
						'verified_owner'              => isset( $form_fields['verified_owner'] ) ? 'yes' : 'no',
						'stop_reply'                  => isset( $form_fields['stop_reply'] ) ? 'yes' : 'no',
						'review_user_id'              => isset( $form_fields['review_user_id'] ) ? $form_fields['review_user_id'] : 0,
						'review_author'               => $form_fields['review_author'],
						'review_author_email'         => $form_fields['review_author_email'],
						'review_author_custom_avatar' => $form_fields['review_author_custom_avatar'],
						'review_author_country'       => isset( $form_fields['review_author_country'] ) ? $form_fields['review_author_country'] : substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
						'review_edit_blocked'         => isset( $form_fields['review_edit_blocked'] ) ? 'yes' : 'no',
						'thumb_ids'                   => ! empty( $form_fields['thumb_ids'] ) ? explode( ',', $form_fields['thumb_ids'] ) : array(),
					);

					foreach ( $data as $field => $value ) {
						$review->{"set_$field"}( $value );
					}

					$review->save();

				}
			}
		}

		/**
		 * Changes the status of a review
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_change_review_status() {

			isset( $_POST['id'], $_POST['set_status'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$review_id = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			$review    = yith_ywar_get_review( $review_id );

			if ( 'approve' === $_POST['set_status'] ) {
				$review->update_status( 'approved' );
			} else {
				$review->update_status( 'pending' );
			}

			wp_send_json_success();
		}

		/**
		 * Get the image of an attachment
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_get_attachment_image() {

			isset( $_POST['attachment_id'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$attachment_id = sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) );

			ob_start();
			?>
			<div class="single-attachment attachment-<?php echo esc_attr( $attachment_id ); ?>">
				<a target="_blank" href="<?php echo esc_url( get_edit_post_link( $attachment_id ) ); ?>">
					<?php echo wp_get_attachment_image( $attachment_id, array( 80, 80 ), true ); ?>
				</a>
				<span class="delete-button yith-icon-trash yith-plugin-fw__tips" data-item_id="<?php echo esc_attr( $attachment_id ); ?>" data-tip="<?php echo esc_html_x( 'Delete attachment', '[Admin panel] button label', 'yith-woocommerce-advanced-reviews' ); ?>"></span>
			</div>
			<?php

			$data = array(
				'html' => ob_get_clean(),
				'id'   => $attachment_id,
			);

			wp_send_json_success( $data );
		}

		/**
		 * Get the image of an attachment
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_get_product_rating() {

			isset( $_POST['product_id'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$product_id        = sanitize_text_field( wp_unslash( $_POST['product_id'] ) );
			$product           = wc_get_product( $product_id );
			$review_box        = yith_ywar_get_current_review_box( $product );
			$multi_criteria_on = $review_box->get_enable_multi_criteria();
			$criteria          = $review_box->get_multi_criteria();
			$validate_fields   = array( '#content', '#review_author', '#review_author_email', '#rating', '#product_id' );

			if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) {
				$rating  = array(
					'id'       => 'multi_rating',
					'type'     => 'yith-ywar-multi-rating',
					'title'    => esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ),
					'value'    => '',
					'criteria' => $criteria,
				);
				$search  = 'yith-ywar-rating';
				$replace = 'yith-ywar-multi-rating';
				foreach ( $criteria as $criterion_id ) {
					$criterion         = get_term_by( 'term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX );
					$validate_fields[] = "#multi_rating_$criterion->slug";
				}
			} else {
				$rating  = array(
					'id'    => 'rating',
					'type'  => 'yith-ywar-rating',
					'title' => esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ),
					'value' => '',
				);
				$search  = 'yith-ywar-multi-rating';
				$replace = 'yith-ywar-rating';
			}

			$rating['name'] = $this->get_name_field( $rating['id'] );

			$data = array(
				'html'            => yith_plugin_fw_get_field( $rating, false, true ),
				'search'          => $search,
				'replace'         => $replace,
				'validate_fields' => wp_json_encode( $validate_fields ),
			);

			wp_send_json_success( $data );
		}

		/**
		 * Get the user data
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_get_user_data() {
			isset( $_POST['user_id'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$user_id = sanitize_text_field( wp_unslash( $_POST['user_id'] ) );

			if ( $user_id > 0 ) {
				$user      = get_user_by( 'id', $user_id );
				$user_name = trim( sprintf( '%1$s %2$s', $user->first_name, $user->last_name ) );
				$user_name = empty( $user_name ) ? $user->nickname : $user_name;
				$data      = array(
					'user_name'  => $user_name,
					'user_email' => $user->user_email,
					'user_info'  => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( get_edit_user_link( $user_id ) ), $user_name ),
				);
			} else {
				$data = array(
					'guest' => esc_html_x( 'Guest user', '[Admin panel] label to use when the user is not registered', 'yith-woocommerce-advanced-reviews' ),
				);
			}

			wp_send_json_success( $data );
		}
	}
}

return YITH_YWAR_Review_Post_Type_Admin::instance();
