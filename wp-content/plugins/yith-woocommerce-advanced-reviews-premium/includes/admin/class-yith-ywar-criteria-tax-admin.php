<?php
/**
 * Class YITH_YWAR_Criteria_Tax_Admin
 *
 * @package YITH\AdvancedReviews\Admin
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Criteria_Tax_Admin' ) ) {
	/**
	 * YITH_YWAR_Criteria_Tax_Admin class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Admin
	 */
	class YITH_YWAR_Criteria_Tax_Admin {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * The taxonomy.
		 *
		 * @var string
		 */
		protected $taxonomy = YITH_YWAR_Post_Types::CRITERIA_TAX;

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			add_action( "after-$this->taxonomy-table", array( $this, 'maybe_render_blank_state' ) );
			add_filter( "manage_edit-{$this->taxonomy}_columns", array( $this, 'get_columns' ) );
			add_filter( "manage_{$this->taxonomy}_custom_column", array( $this, 'custom_columns' ), 12, 3 );
			add_filter( 'tag_row_actions', array( $this, 'remove_row_actions' ), 10, 2 );
			add_filter( 'yith_ywar_admin_screen_ids', array( $this, 'add_admin_screen_ids' ), 10, 1 );
			add_filter( 'woocommerce_screen_ids', array( $this, 'add_admin_screen_ids' ), 100, 1 );
			add_action( 'yith_ywar_admin_ajax_delete_criteria', array( $this, 'ajax_delete_criteria' ) );
			add_action( 'yith_ywar_admin_ajax_add_criteria', array( $this, 'ajax_add_criteria' ) );
			add_action( 'yith_ywar_admin_ajax_edit_criteria', array( $this, 'ajax_edit_criteria' ) );
		}

		/**
		 * Maybe render blank state
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function maybe_render_blank_state() {
			$count = absint( wp_count_terms( $this->taxonomy ) );
			if ( 0 < $count ) {
				?>
				<a href="#" class="yith-ywar-add-criteria page-title-action">
					<?php echo esc_html_x( 'Add new', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ); ?>
				</a>
				<script type="text/javascript">
					jQuery( '.yith-ywar-add-criteria' ).insertAfter( 'h1.wp-heading-inline' );
				</script>
				<?php
			} else {
				$this->render_blank_state();
				?>
				<style type="text/css">
					#posts-filter {
						display : none;
					}

					form.search-form {
						visibility : hidden;
					}
				</style>
				<?php
			}
		}

		/**
		 * Render blank state.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function render_blank_state() {
			$component = array(
				'type'     => 'list-table-blank-state',
				'icon_url' => YITH_YWAR_ASSETS_URL . '/images/review-empty.svg',
				'message'  => esc_html_x( 'You have no review criteria yet!', '[Admin panel] Empty state message', 'yith-woocommerce-advanced-reviews' ),
				'cta'      => array(
					'title' => esc_html_x( 'Add criteria', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ),
					'class' => 'yith-ywar-add-criteria',
					'url'   => '#',
					'icon'  => 'plus',
				),
			);

			yith_plugin_fw_get_component( $component, true );
		}

		/**
		 * Filter columns
		 *
		 * @param array $columns The columns.
		 *
		 * @return array The columns list
		 * @since  2.0.0
		 */
		public function get_columns( array $columns ): array {

			$to_remove = array( 'cb', 'posts', 'slug', 'description' );

			foreach ( $to_remove as $column ) {
				if ( isset( $columns[ $column ] ) ) {
					unset( $columns[ $column ] );
				}
			}

			$columns['icon']    = esc_html_x( 'Icon', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' );
			$columns['actions'] = esc_html_x( 'Actions', '[Admin panel] Column name', 'yith-woocommerce-advanced-reviews' );

			return $columns;
		}

		/**
		 * Display custom columns
		 *
		 * @param string $custom_column Filter value.
		 * @param string $column_name   Column name.
		 * @param int    $term_id       The term id.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function custom_columns( string $custom_column, string $column_name, int $term_id ): string {
			switch ( $column_name ) {
				case 'icon':
					$icon_id       = get_term_meta( $term_id, 'icon', true );
					$custom_column = $icon_id ? wp_get_attachment_image( $icon_id, array( 60, 60 ), true ) : '';
					break;
				case 'actions':
					$icon_id  = get_term_meta( $term_id, 'icon', true );
					$icon_src = $icon_id ? wp_get_attachment_image_src( $icon_id, array( 180, 180 ) )[0] : '';

					$custom_column .= yith_plugin_fw_get_component(
						array(
							'class'  => 'yith-ywar-criteria__edit',
							'type'   => 'action-button',
							'action' => 'edit',
							'icon'   => 'edit',
							'title'  => esc_html_x( 'Edit', '[Global] Generic edit button text', 'yith-woocommerce-advanced-reviews' ),
							'data'   => array(
								'term_id'  => $term_id,
								'icon'     => $icon_id,
								'icon_src' => $icon_src,
								'name'     => get_term_by( 'term_id', $term_id, $this->taxonomy )->name,
							),
						),
						false
					);
					$custom_column .= yith_plugin_fw_get_component(
						array(
							'class'  => 'yith-ywar-criteria__delete',
							'type'   => 'action-button',
							'action' => 'delete',
							'icon'   => 'trash',
							'title'  => esc_html_x( 'Delete', '[Admin panel] Generic delete action label', 'yith-woocommerce-advanced-reviews' ),
							'data'   => array(
								'term_id' => $term_id,
							),
						),
						false
					);
					break;
			}

			return $custom_column;
		}

		/**
		 * Remove Row Actions
		 *
		 * @param array   $actions An array of row action links. Defaults are
		 *                         'Edit', 'Quick Edit', 'Restore, 'Trash',
		 *                         'Delete Permanently', 'Preview', and 'View'.
		 * @param WP_Term $tag     The post object.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function remove_row_actions( array $actions, WP_Term $tag ): array {
			if ( $this->taxonomy === $tag->taxonomy ) {
				$actions = array();
			}

			return $actions;
		}

		/**
		 * Add admin screen IDs to allow including styles and scripts correctly.
		 *
		 * @param array $screen_ids The screen IDs.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_admin_screen_ids( array $screen_ids ): array {
			$screen_ids[] = 'edit-' . $this->taxonomy;

			return $screen_ids;
		}

		/**
		 * Delete criteria
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_delete_criteria() {

			check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$term_id = sanitize_text_field( wp_unslash( $_REQUEST['term_id'] ?? '' ) );
			wp_delete_term( $term_id, $this->taxonomy );
			wp_send_json_success();
		}

		/**
		 * Add criteria
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_add_criteria() {

			check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$term_name = sanitize_text_field( wp_unslash( $_REQUEST['name'] ?? '' ) );
			$term_icon = sanitize_text_field( wp_unslash( $_REQUEST['icon'] ?? '' ) );
			$term      = wp_insert_term( $term_name, $this->taxonomy );

			if ( is_wp_error( $term ) ) {
				if ( isset( $term->errors['term_exists'] ) ) {
					$message = esc_html_x( 'A criterion with the specified name is already in existence.', '[Admin panel] Error message', 'yith-woocommerce-advanced-reviews' );
				} else {
					$message = esc_html_x( 'An error occurred.', '[Admin panel] generic error message', 'yith-woocommerce-advanced-reviews' );
				}

				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $message,
						),
						false
					)
				);
			}

			add_term_meta( $term['term_id'], 'icon', $term_icon );
			wp_send_json_success( array( 'id' => $term['term_id'] ) );
		}

		/**
		 * Edit criteria
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_edit_criteria() {

			check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$term_id   = sanitize_text_field( wp_unslash( $_REQUEST['term_id'] ?? '' ) );
			$term_name = sanitize_text_field( wp_unslash( $_REQUEST['name'] ?? '' ) );
			$term_icon = sanitize_text_field( wp_unslash( $_REQUEST['icon'] ?? '' ) );
			$term      = wp_update_term( $term_id, $this->taxonomy, array( 'name' => $term_name ) );
			if ( is_wp_error( $term ) ) {
				if ( isset( $term->errors['term_exists'] ) ) {
					$message = esc_html_x( 'A criterion with the specified name is already in existence.', '[Admin panel] Error message', 'yith-woocommerce-advanced-reviews' );
				} else {
					$message = esc_html_x( 'An error occurred.', '[Admin panel] generic error message', 'yith-woocommerce-advanced-reviews' );
				}

				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $message,
						),
						false
					)
				);
			}

			update_term_meta( $term['term_id'], 'icon', $term_icon );
			wp_send_json_success();
		}
	}
}
