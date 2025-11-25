<?php
/**
 * Class YITH_YWAR_Review_Box_Post_Type_Admin
 *
 * @package YITH\AdvancedReviews\Admin
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Review_Box_Post_Type_Admin' ) ) {
	/**
	 * YITH_YWAR_Review_Box_Post_Type_Admin class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Admin
	 */
	class YITH_YWAR_Review_Box_Post_Type_Admin {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * The post type.
		 *
		 * @var string
		 */
		protected $post_type = YITH_YWAR_Post_Types::BOXES;

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			add_action( 'yith_ywar_print_review_box_tab', array( $this, 'print_tab' ) );
			add_action( 'yith_ywar_admin_ajax_switch_box_activation', array( $this, 'ajax_switch_box_activation' ) );
			add_action( 'yith_ywar_admin_ajax_update_box_options', array( $this, 'ajax_update_box_options' ) );
			add_action( 'yith_ywar_admin_ajax_new_box_options', array( $this, 'ajax_new_box_options' ) );
			add_action( 'yith_ywar_admin_ajax_delete_box', array( $this, 'ajax_delete_box' ) );
		}

		/**
		 * Print the review boxes tab
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function print_tab() {
			$default_box_id = get_option( 'yith-ywar-default-box-id' );
			$default_box    = yith_ywar_get_review_box( $default_box_id );
			$boxes          = yith_ywar_get_review_boxes(
				array(
					'post__not_in' => array( $default_box_id ),
				)
			);
			?>
			<div class="yith-ywar-review-boxes">
				<div class="yith-ywar-review-boxes__headings">
					<?php foreach ( $this->get_column_names() as $key => $column ) : ?>
						<div class="yith-ywar-review-boxes__heading yith-ywar-review-boxes__heading-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></div>
					<?php endforeach; ?>
				</div>
				<div class="yith-ywar-review-boxes__list">
					<?php
					$this->print_setting_row( $default_box, true );

					foreach ( $boxes as $box ) {
						$this->print_setting_row( $box );
					}
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Get the Box settings in Review Boxes admin panel.
		 *
		 * @param YITH_YWAR_Review_Box $review_box   the review box to display.
		 * @param bool                 $default_item Flag to identify the default box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function print_setting_row( YITH_YWAR_Review_Box $review_box, bool $default_item = false ) {
			?>
			<div id="review-box-<?php echo esc_attr( $review_box->get_id() ); ?>" class="yith-ywar-review-boxes__box box-<?php echo esc_attr( $review_box->get_id() ); ?>" data-box-id="<?php echo esc_attr( $review_box->get_id() ); ?>">
				<div class="yith-ywar-review-boxes__box__head">
					<?php foreach ( $this->get_column_names() as $key => $column ) : ?>
						<div class="yith-ywar-review-boxes__box__column yith-ywar-review-boxes__box__column-<?php echo esc_attr( $key ); ?>">
							<?php $this->get_column_content( $key, $review_box, $default_item ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="yith-ywar-review-boxes__box__options">
					<form class="yith-ywar-review-boxes__box__options__form">
						<div class="yith-plugin-ui yith-plugin-fw yith-ywar-review-boxes__box__options__container">
							<div class="yith-plugin-fw__panel__section__content">
								<?php $this->print_review_box_settings( $review_box, $default_item ); ?>
							</div>
						</div>
						<div class="yith-ywar-review-boxes__box__actions">
							<span class="yith-ywar-review-boxes__box__save yith-plugin-fw__button yith-plugin-fw__button--primary yith-plugin-fw__button--xl" data-save-message="<?php echo esc_html_x( 'Save', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ); ?>">
								<svg class="yith-ywar-review-boxes__box__save__saved-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" role="img">
									<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
								</svg>
								<span class="yith-ywar-review-boxes__box__save__text">
									<?php echo esc_html_x( 'Save', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ); ?>
								</span>
							</span>
						</div>
					</form>
				</div>
			</div>
			<?php
		}

		/**
		 * Get the column names in Review Boxes admin panel.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		private function get_column_names(): array {
			return array(
				'name'    => esc_html_x( 'Name', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' ),
				'show_on' => esc_html_x( 'Show on', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ),
				'active'  => esc_html_x( 'Active', '[Admin panel] Column name - Status', 'yith-woocommerce-advanced-reviews' ),
				'actions' => '',
			);
		}

		/**
		 * Get the column content in Review Boxes admin panel.
		 *
		 * @param string               $column       The column key.
		 * @param YITH_YWAR_Review_Box $review_box   the review box to display.
		 * @param bool                 $default_item Flag to identify the default box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function get_column_content( string $column, YITH_YWAR_Review_Box $review_box, bool $default_item = false ) {
			switch ( $column ) {
				case 'name':
					$title = '' !== $review_box->get_title() ? $review_box->get_title() : esc_html_x( '(No name)', '[Admin panel] Default box name if no data is provided', 'yith-woocommerce-advanced-reviews' );
					printf( '<strong>%s</strong>', esc_html( $title ) );
					break;
				case 'show_on':
					switch ( $review_box->get_show_on() ) {
						case 'products':
							$products = yith_ywar_get_product_names( $review_box->get_product_ids(), true );
							if ( count( $products ) === 1 ) {
								$text = sprintf( '%1$s: %2$s', esc_html_x( 'Product', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ), implode( '', $products ) );
							} else {
								/* translators: %s number of products */
								$text = '<span class="underline">' . sprintf( esc_html_x( '%s specific products', '[Admin panel] Review box and discount description', 'yith-woocommerce-advanced-reviews' ), count( $products ) ) . '</span>';
							}
							$tip = esc_html_x( 'Products', '[Admin panel] Generic label description', 'yith-woocommerce-advanced-reviews' ) . ':<br />' . implode( '<br/>', $products );

							break;
						case 'categories':
							$categories = yith_ywar_get_category_names( $review_box->get_category_ids(), true );
							if ( count( $categories ) === 1 ) {
								$text = sprintf( '%1$s: %2$s', esc_html_x( 'Category', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' ), implode( '', $categories ) );
							} else {
								/* translators: %s number of products */
								$text = '<span class="underline">' . sprintf( esc_html_x( '%s specific categories', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' ), count( $categories ) ) . '</span>';
							}
							$tip = esc_html_x( 'Categories', '[Admin panel] Generic label description', 'yith-woocommerce-advanced-reviews' ) . ':<br />' . implode( '<br/>', $categories );
							break;
						case 'tags':
							$tags = yith_ywar_get_tag_names( $review_box->get_tag_ids(), true );
							if ( count( $tags ) === 1 ) {
								$text = sprintf( '%1$s: %2$s', esc_html_x( 'Tag', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' ), implode( '', $tags ) );
							} else {
								/* translators: %s number of products */
								$text = '<span class="underline">' . sprintf( esc_html_x( '%s specific tags', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' ), count( $tags ) ) . '</span>';
							}
							$tip = esc_html_x( 'Tags', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' ) . ':<br />' . implode( '<br/>', $tags );
							break;
						case 'virtual':
							$tip  = '';
							$text = esc_html_x( 'All virtual products', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' );
							break;
						default:
							$tip  = '';
							$text = esc_html_x( 'All products', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' );
					}
					?>
					<span class="yith-ywar-review-boxes__box__show-on-wrapper yith-plugin-fw__tips" data-tip="<?php echo wp_kses_post( wc_sanitize_tooltip( $tip ) ); ?>"><?php echo wp_kses_post( $text ); ?></span>
					<?php
					break;
				case 'active':
					if ( $default_item ) {
						$tip  = esc_html_x( 'Default box cannot be deactivated', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' );
						$atts = array( 'disabled' => 'disabled' );
					} else {
						$tip  = esc_html_x( 'Enable/Disable box', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' );
						$atts = array();
					}
					?>
					<div class="yith-ywar-review-boxes__box__toggle-active-wrapper yith-plugin-fw__tips <?php echo( $default_item ? 'default' : '' ); ?>" data-tip="<?php echo esc_html( $tip ); ?>">
						<?php
						yith_plugin_fw_get_field(
							array(
								'type'              => 'onoff',
								'value'             => $review_box->get_active(),
								'class'             => 'yith-ywar-review-boxes__box__toggle-active',
								'custom_attributes' => $atts,
							),
							true
						);
						?>
					</div>
					<?php
					break;
				case 'actions':
					yith_plugin_fw_get_component(
						array(
							'class'  => 'yith-ywar-review-boxes__box__toggle-editing',
							'type'   => 'action-button',
							'action' => 'edit',
							'icon'   => 'edit',
							'title'  => esc_html_x( 'Edit', '[Global] Generic edit button text', 'yith-woocommerce-advanced-reviews' ),
							'url'    => '#',
						),
						true
					);
					if ( ! $default_item ) {
						yith_plugin_fw_get_component(
							array(
								'class'  => 'yith-ywar-review-boxes__box__delete-box',
								'type'   => 'action-button',
								'action' => 'delete',
								'icon'   => 'trash',
								'title'  => esc_html_x( 'Delete', '[Admin panel] Generic delete action label', 'yith-woocommerce-advanced-reviews' ),
								'url'    => '#',
							),
							true
						);
					}
					break;
			}
		}

		/**
		 * Print review box settings
		 *
		 * @param YITH_YWAR_Review_Box $review_box   The review box to display.
		 * @param bool                 $default_item Flag to identify the default box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function print_review_box_settings( YITH_YWAR_Review_Box $review_box, bool $default_item = false ) {
			$atts   = $default_item ? array( 'readonly' => true ) : array();
			$fields = array(
				array(
					'id'                => 'title',
					'type'              => 'text',
					'title'             => esc_html_x( 'Name', '[Admin panel] Column and setting name', 'yith-woocommerce-advanced-reviews' ),
					'custom_attributes' => $atts,
				),
				( $default_item ? array() : array(
					'id'      => 'show_on',
					'title'   => esc_html_x( 'Show on', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ) . ':',
					'options' => array(
						'all'        => esc_html_x( 'All products', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'products'   => esc_html_x( 'Specific products', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'categories' => esc_html_x( 'Specific categories', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'tags'       => esc_html_x( 'Specific tags', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'virtual'    => esc_html_x( 'All virtual products', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
					),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
				) ),
				( $default_item ? array() : array(
					'id'       => 'product_ids',
					'type'     => 'ajax-products',
					'title'    => esc_html_x( 'Select products', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ),
					'multiple' => true,
					'deps'     => array(
						'id'    => "show_on_{$review_box->get_id()}",
						'value' => 'products',
					),
				) ),
				( $default_item ? array() : array(
					'id'       => 'category_ids',
					'title'    => esc_html_x( 'Select categories', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'     => 'ajax-terms',
					'data'     => array(
						'placeholder' => esc_html_x( 'Search for a category', '[Global] Ajax field placeholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
						'taxonomy'    => 'product_cat',
					),
					'multiple' => true,
					'deps'     => array(
						'id'    => "show_on_{$review_box->get_id()}",
						'value' => 'categories',
					),
				) ),
				( $default_item ? array() : array(
					'id'       => 'tag_ids',
					'title'    => esc_html_x( 'Select tags', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'     => 'ajax-terms',
					'data'     => array(
						'placeholder' => esc_html_x( 'Search for a tag', '[Global] Ajax field placeholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
						'taxonomy'    => 'product_tag',
					),
					'multiple' => true,
					'deps'     => array(
						'id'    => "show_on_{$review_box->get_id()}",
						'value' => 'tags',
					),
				) ),
				array(
					'id'    => 'enable_multi_criteria',
					'title' => esc_html_x( 'Enable multi-criteria', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ),
					'desc'  => esc_html_x( 'Enable to create multi-criteria for reviews or disable to use the default WooCommerce rating system.', '[Admin panel] Review box setting description', 'yith-woocommerce-advanced-reviews' ),
					'type'  => 'onoff',
				),
				array(
					'id'        => 'multi_criteria',
					'title'     => esc_html_x( 'Criteria', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'      => 'select',
					'multiple'  => true,
					'class'     => 'wc-enhanced-select',
					'row-class' => 'multi-criteria',
					'options'   => yith_ywar_retrieve_criteria(),
					'buttons'   => array(
						array(
							'name'  => '+ ' . esc_html_x( 'Add new criteria', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ),
							'class' => 'yith-ywar-new-criteria',
						),
					),
					'deps'      => array(
						'id'    => "enable_multi_criteria_{$review_box->get_id()}",
						'value' => 'yes',
					),
				),
				array(
					'id'      => 'show_elements',
					'title'   => esc_html_x( 'Show', '[Admin panel] Review box setting name', 'yith-woocommerce-advanced-reviews' ) . ':',
					'type'    => 'checkbox-array',
					'options' => array(
						'average-rating-box' => esc_html_x( 'Average rating box', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'graph-bars'         => esc_html_x( 'Graph bars', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'sorting-options'    => esc_html_x( 'Sorting options', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'vote-helpful'       => esc_html_x( 'Vote reviews as helpful', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
						'most-helpful_tab'   => esc_html_x( 'Most helpful reviews tab', '[Admin panel] Review box setting option', 'yith-woocommerce-advanced-reviews' ),
					),
				),
			);

			foreach ( $fields as $field ) {
				if ( empty( $field ) ) {
					continue;
				}
				$field['name'] = $field['id'];
				$field['id']   = "{$field['id']}_{$review_box->get_id()}";
				$row_classes   = array(
					'yith-plugin-fw__panel__option',
					'yith-plugin-fw__panel__option--' . $field['type'],
					$field['id'],
					isset( $field['row-class'] ) ? $field['row-class'] : '',
				);
				$row_classes   = implode( ' ', array_filter( $row_classes ) );

				$field['value'] = $review_box->{"get_{$field['name']}"}();
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
				<?php
			}
		}

		/**
		 * Switch box activation.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_switch_box_activation() {
			check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$box_id  = sanitize_text_field( wp_unslash( $_REQUEST['box_id'] ?? '' ) );
			$enabled = sanitize_title( wp_unslash( $_REQUEST['enabled'] ?? '' ) );

			if ( ! yith_ywar_current_user_can_manage() || ! in_array( $enabled, array( 'yes', 'no' ), true ) ) {
				wp_send_json_error();
			}

			$review_box = yith_ywar_get_review_box( $box_id );

			$review_box->set_active( $enabled );
			$review_box->save();

			wp_send_json_success();
		}

		/**
		 * Update box options.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_update_box_options() {

			check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$box_id = sanitize_text_field( wp_unslash( $_REQUEST['box_id'] ?? '' ) );
			$data   = wp_unslash( $_REQUEST['data'] ?? array() ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ! yith_ywar_current_user_can_manage() ) {
				wp_send_json_error();
			}

			$fields = array(
				'title'                 => $data['title'],
				'show_on'               => isset( $data['show_on'] ) ? $data['show_on'] : 'all',
				'tag_ids'               => ! empty( $data['tag_ids'] ) ? $data['tag_ids'] : array(),
				'category_ids'          => ! empty( $data['category_ids'] ) ? $data['category_ids'] : array(),
				'product_ids'           => ! empty( $data['product_ids'] ) ? $data['product_ids'] : array(),
				'enable_multi_criteria' => isset( $data['enable_multi_criteria'] ) ? 'yes' : 'no',
				'multi_criteria'        => ! empty( $data['multi_criteria'] ) ? $data['multi_criteria'] : array(),
				'show_elements'         => ! empty( $data['show_elements'] ) ? $data['show_elements'] : array(),
			);

			$review_box = yith_ywar_get_review_box( $box_id );

			foreach ( $fields as $key => $value ) {
				$review_box->{"set_$key"}( $value );
			}
			$review_box->save();

			wp_send_json_success();
		}

		/**
		 * New box options.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_new_box_options() {

			check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			if ( ! yith_ywar_current_user_can_manage() ) {
				wp_send_json_error();
			}

			$review_box = new YITH_YWAR_Review_Box();

			$review_box->set_active( 'no' );
			$review_box->set_show_on( 'all' );
			$review_box->save();

			wp_send_json_success();
		}

		/**
		 * Delete box
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_delete_box() {

			check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			$box_id = sanitize_text_field( wp_unslash( $_REQUEST['box_id'] ?? '' ) );

			if ( ! yith_ywar_current_user_can_manage() ) {
				wp_send_json_error();
			}

			$review_box = yith_ywar_get_review_box( $box_id );

			if ( $review_box && get_post( $review_box->get_id() ) ) {
				$review_box->delete();
			}

			wp_send_json_success();
		}
	}
}
