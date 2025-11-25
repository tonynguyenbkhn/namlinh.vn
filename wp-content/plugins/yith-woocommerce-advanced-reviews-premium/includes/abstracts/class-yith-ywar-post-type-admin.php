<?php
/**
 * Class YITH_YWAR_Post_Type_Admin
 *
 * @package YITH\AdvancedReviews\Abstracts
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Post_Type_Admin' ) ) {
	/**
	 * Class YITH_YWAR_Post_Type_Admin
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Abstracts
	 */
	abstract class YITH_YWAR_Post_Type_Admin extends YITH_Post_Type_Admin {

		/**
		 * YITH_YWAR_Post_Type_Admin constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			parent::__construct();

			if ( $this->post_type && $this->is_enabled() ) {
				$settings          = $this->get_post_type_settings();
				$title_placeholder = $settings['title_placeholder'] ?? '';
				$title_description = $settings['title_description'] ?? '';
				$updated_messages  = $settings['updated_messages'] ?? array();

				if ( $title_placeholder ) {
					add_filter( 'enter_title_here', array( $this, 'set_title_placeholder' ) );
				}

				if ( $title_description ) {
					add_action( 'edit_form_after_title', array( $this, 'add_title_description' ) );
				}

				if ( $updated_messages ) {
					add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
				}

				add_filter( 'yith_ywar_admin_screen_ids', array( $this, 'add_admin_screen_ids' ), 10, 1 );
				add_filter( 'woocommerce_screen_ids', array( $this, 'add_admin_screen_ids' ), 100, 1 );
				add_filter( 'admin_body_class', array( $this, 'add_admin_body_classes' ), 10, 1 );
				add_action( 'dbx_post_sidebar', array( $this, 'print_save_button_in_edit_page' ), 10, 1 );
				add_action( 'admin_menu', array( $this, 'remove_publish_box' ) );

				if ( $this->use_single_column_in_edit_page() ) {
					add_action( 'admin_head', array( $this, 'disable_screen_layout_columns' ) );
					add_filter( "get_user_option_screen_layout_$this->post_type", array( $this, 'force_single_column_screen_layout' ), 10, 1 );
				}
			}
		}

		/**
		 * Return true to use only one column in edit page.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		protected function use_single_column_in_edit_page(): bool {
			return true;
		}

		/**
		 * Disable the screen layout columns, by setting it to 1 column.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function disable_screen_layout_columns() {
			if ( $this->is_post_type_edit() ) {
				get_current_screen()->add_option(
					'layout_columns',
					array(
						'max'     => 1,
						'default' => 1,
					)
				);
			}
		}

		/**
		 * Force using the single column layout.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function force_single_column_screen_layout(): int {
			return 1;
		}

		/**
		 * Initialize the WP List handlers.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_wp_list_handlers() {
			parent::init_wp_list_handlers();
			if ( $this->should_wp_list_handlers_be_loaded() ) {
				$this->maybe_redirect_to_main_list();
			}
		}

		/**
		 * Return the post_type settings placeholder.
		 *
		 * @return array Array of settings: title_placeholder, title_description, updated_messages, hide_views.
		 * @since  2.0.0
		 */
		protected function get_post_type_settings(): array {
			return array();
		}

		/**
		 * Return true if you want to use the object. False otherwise.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		protected function use_object(): bool {
			return false;
		}

		/**
		 * Has the months' dropdown enabled?
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		protected function has_months_dropdown_enabled(): bool {
			return false;
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
			if ( isset( $columns['date'] ) ) {
				unset( $columns['date'] );
			}

			$columns['actions'] = esc_html_x( 'Actions', '[Admin panel] Column name', 'yith-woocommerce-advanced-reviews' );

			return $columns;
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

			if ( isset( $actions['trash'] ) ) {
				unset( $actions['trash'] );
			}
			$post_type_object = get_post_type_object( $this->post_type );

			if ( current_user_can( $post_type_object->cap->delete_posts ) ) {
				$actions['delete'] = esc_html_x( 'Delete', '[Admin panel] Generic delete action label', 'yith-woocommerce-advanced-reviews' );
			}

			return $actions;
		}

		/**
		 * Render Actions column
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_actions_column() {
			$actions = yith_plugin_fw_get_default_post_actions( $this->post_id, array( 'delete-directly' => true ) );

			yith_plugin_fw_get_action_buttons( $actions, true );
		}

		/**
		 * Set the "title" placeholder.
		 *
		 * @param string $placeholder Title placeholder.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function set_title_placeholder( string $placeholder ): string {
			global $post_type;

			$settings          = $this->get_post_type_settings();
			$title_placeholder = $settings['title_placeholder'] ?? '';

			if ( $post_type === $this->post_type && $title_placeholder ) {
				$placeholder = $title_placeholder;
			}

			return $placeholder;
		}

		/**
		 * Add title description
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_title_description() {
			global $post_type;

			$settings          = $this->get_post_type_settings();
			$title_description = $settings['title_description'] ?? '';

			if ( $post_type === $this->post_type && $title_description ) {
				?>
				<div id="yith-ywar-cpt-title__wrapper">
					<div id="yith-ywar-cpt-title__field"></div>
					<div id="yith-ywar-cpt-title__description">
						<?php echo wp_kses_post( $title_description ); ?>
					</div>
				</div>

				<script type="text/javascript">
					(function () {
						document.getElementById( 'yith-ywar-cpt-title__field' ).appendChild( document.getElementById( 'title' ) );
						document.getElementById( 'titlewrap' ).appendChild( document.getElementById( 'yith-ywar-cpt-title__wrapper' ) );
					})();
				</script>
				<?php
			}
		}

		/**
		 * Change messages when a post type is updated.
		 *
		 * @param array $messages Array of messages.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function post_updated_messages( array $messages ): array {
			$settings         = $this->get_post_type_settings();
			$updated_messages = $settings['updated_messages'] ?? array();

			$messages[ $this->post_type ] = $updated_messages;

			return $messages;
		}

		/**
		 * Add plugin admin screen IDs to allow including styles and scripts correctly.
		 *
		 * @param array $screen_ids The screen IDs.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_admin_screen_ids( array $screen_ids ): array {
			$screen_ids[] = $this->post_type;
			$screen_ids[] = 'edit-' . $this->post_type;

			return $screen_ids;
		}

		/**
		 * Is the post type list?
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public function is_post_type_list(): bool {
			$screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$screen_id = ! ! $screen ? $screen->id : false;

			return 'edit-' . $this->post_type === $screen_id;
		}

		/**
		 * Is the post type edit page?
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public function is_post_type_edit(): bool {
			$screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$screen_id = ! ! $screen ? $screen->id : false;

			return $screen_id === $this->post_type;
		}

		/**
		 * Add classes to body.
		 *
		 * @param string $classes The CSS classes.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function add_admin_body_classes( string $classes ): string {
			$custom_classes               = array();
			$settings                     = $this->get_post_type_settings();
			$hide_views                   = $settings['hide_views'] ?? false;
			$hide_new_post_button_in_list = $settings['hide_new_post_button_in_list'] ?? false;

			if ( $this->is_post_type_list() ) {
				$custom_classes[] = 'yith-ywar-post-type';
				$custom_classes[] = 'yith-ywar-post-type-list';

				if ( $hide_views ) {
					$custom_classes[] = 'yith-ywar-post-type-list--hide-views';
				}

				if ( $hide_new_post_button_in_list ) {
					$custom_classes[] = 'yith-ywar-post-type-list--hide-new-post-button';
				}
			}

			if ( $this->is_post_type_edit() ) {
				$custom_classes[] = 'yith-ywar-post-type';
				$custom_classes[] = 'yith-ywar-post-type-edit';
			}

			if ( $custom_classes ) {
				$custom_classes = array_unique( $custom_classes );

				$classes .= ' ' . implode( ' ', $custom_classes ) . ' ';
			}

			return $classes;
		}

		/**
		 * Print save button in edit page.
		 *
		 * @param WP_Post $post The post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function print_save_button_in_edit_page( WP_Post $post ) {
			if ( ! ! $post && isset( $post->post_type ) && $post->post_type === $this->post_type ) {
				global $post_id;
				$is_updating      = ! ! $post_id;
				$save_text        = esc_html_x( 'Save', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' );
				$post_type_object = get_post_type_object( $this->post_type );
				$single           = $post_type_object->labels->singular_name ?? '';

				if ( $single ) {
					// translators: %s is the post type name.
					$save_text = sprintf( esc_html_x( 'Save %s', '[Admin panel] Button label with post type name', 'yith-woocommerce-advanced-reviews' ), strtolower( $single ) );
				}
				?>
				<div class="yith-ywar-post-type__actions yith-plugin-ui">
					<?php if ( $is_updating ) : ?>
						<button id="yith-ywar-post-type__save" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl"><?php echo esc_html( $save_text ); ?></button>
					<?php else : ?>
						<input id="yith-ywar-post-type__save" type="submit" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl" name="publish" value="<?php echo esc_html( $save_text ); ?>">
					<?php endif; ?>

					<a id="yith-ywar-post-type__float-save" class="yith-plugin-fw__button--primary yith-plugin-fw__button--xl yith-plugin-fw-animate__appear-from-bottom"><?php echo esc_html( $save_text ); ?></a>
				</div>
				<?php
			}
		}

		/**
		 * Remove publish box from edit post page.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function remove_publish_box() {
			remove_meta_box( 'submitdiv', $this->post_type, 'side' );
		}

		/**
		 * Redirect to main list if the current view is 'trash' and there are no post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function maybe_redirect_to_main_list() {

			$post_status = wc_clean( wp_unslash( $_REQUEST['post_status'] ?? 'any' ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( 'trash' === $post_status ) {
				$counts = (array) wp_count_posts( $this->post_type );
				unset( $counts['auto-draft'] );
				$count = array_sum( $counts );

				if ( 0 < $count ) {
					return;
				}

				$args = array(
					'post_type' => $this->post_type,
					'deleted'   => isset( $_GET['deleted'] ) ? wc_clean( wp_unslash( $_GET['deleted'] ) ) : null, //phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				$list_url = add_query_arg( $args, admin_url( 'edit.php' ) );

				wp_safe_redirect( $list_url );
				exit();
			}
		}
	}
}
