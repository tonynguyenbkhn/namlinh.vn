<?php
/**
 * The class manage the post table for our boost rule
 *
 * @package YITH\Search
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'YITH_Post_Type_Admin' ) ) {
	require_once YITH_WCAS_DIR . '/plugin-fw/includes/class-yith-post-type-admin.php';
}

/**
 * The class that show all boost rules in a table
 */
class YITH_WCAS_Admin_Boost_Rules_List_Table extends YITH_Post_Type_Admin {

	/**
	 * The post type.
	 *
	 * @var string
	 */
	protected $post_type = 'ywcas_boost';

	/**
	 * The object to be shown for each row.
	 *
	 * @var YITH_WCAS_Boost|null
	 */
	protected $object = null;

	/**
	 * The condition list to show.
	 *
	 * @var array
	 */
	protected $condition_for_options = array();

	/**
	 * YITH_Admin_Post_List_Table constructor.
	 */
	protected function __construct() {
		parent::__construct();
		add_filter( 'views_edit-ywcas_boost', array( $this, 'hide_views' ) );
		$this->prepare_condition_for_options();
	}

	/**
	 * Map the condition for key with the label
	 *
	 * @return void
	 */
	protected function prepare_condition_for_options() {
		$options                     = ywcas()->settings->get_boost_condition_fields();
		$this->condition_for_options = $options['condition_config']['fields']['condition_for']['options'];
	}

	/**
	 * Pre-fetch any data for the row each column has access to it, by loading $this->object.
	 *
	 * @param int $post_id Post ID being shown.
	 */
	protected function prepare_row_data( $post_id ) {
		if ( empty( $this->object ) || $this->object->get_id() !== $post_id ) {
			$this->object = ywcas_get_boost_rule( $post_id );
		}
	}

	/**
	 * Add the right column for this post type
	 *
	 * @param array $columns The default columns.
	 *
	 * @return array
	 * @author YITH <plugins@yithemes.com>
	 * @since 2.1.0
	 */
	public function define_columns( $columns ) {
		if ( isset( $columns['date'] ) ) {
			unset( $columns['date'] );
		}
		if ( isset( $columns['title'] ) ) {
			unset( $columns['title'] );
		}
		$custom_columns = array(
			'custom_title' => _x( 'Name', '[ADMIN] Column label for Boost rule list', 'yith-woocommerce-ajax-search' ),
			'conditions'   => _x( 'Conditions', '[ADMIN] Column label for Boost rule list', 'yith-woocommerce-ajax-search' ),
			'boost'        => _x( 'Boost value', '[ADMIN] Column label for Boost rule list', 'yith-woocommerce-ajax-search' ),
			'active'       => _x( 'Active', '[ADMIN] Column label for Boost rule list', 'yith-woocommerce-ajax-search' ),
			'actions'      => _x( 'Actions', '[ADMIN] Column label for Boost rule list', 'yith-woocommerce-ajax-search' ),
		);

		return array_merge( $columns, $custom_columns );
	}

	/**
	 * Define bulk actions.
	 *
	 * @param array $actions Existing actions.
	 *
	 * @return array
	 */
	public function define_bulk_actions( $actions ) {
		unset( $actions['trash'] );
		unset( $actions['edit'] );
		$actions['activate']   = __( 'Active', 'yith-woocommerce-ajax-search' );
		$actions['deactivate'] = __( 'Deactivate', 'yith-woocommerce-ajax-search' );
		$actions['delete']     = __( 'Delete', 'yith-woocommerce-ajax-search' );

		return $actions;
	}


	/**
	 * Show the Title column
	 */
	public function render_custom_title_column() {
		$post          = get_post( $this->object->get_id() );
		$can_edit_post = current_user_can( 'edit_post', $post->ID );

		if ( $can_edit_post && 'trash' !== $post->post_status ) {
			$lock_holder = wp_check_post_lock( $post->ID );

			if ( $lock_holder ) {
				$lock_holder   = get_userdata( $lock_holder );
				$locked_avatar = get_avatar( $lock_holder->ID, 18 );
				/* translators: %s: User's display name. */
				$locked_text = esc_html( sprintf( __( '%s is currently editing' ), $lock_holder->display_name ) );
			} else {
				$locked_avatar = '';
				$locked_text   = '';
			}

			echo '<div class="locked-info"><span class="locked-avatar">' . $locked_avatar . '</span> <span class="locked-text">' . $locked_text . "</span></div>\n";  //phpcs:ignore WordPress.Security.EscapeOutput
		}

		echo '<strong>';

		$title = _draft_or_post_title();

		printf(
			'<span>%s</span>',
			$title  //phpcs:ignore WordPress.Security.EscapeOutput
		);

		_post_states( $post );

		if ( isset( $parent_name ) ) {
			$post_type_object = get_post_type_object( $post->post_type );
			echo ' | ' . $post_type_object->labels->parent_item_colon . ' ' . esc_html( $parent_name );  //phpcs:ignore WordPress.Security.EscapeOutput
		}

		echo "</strong>\n";

		get_inline_data( $post );
	}

	/**
	 * Render the conditions column
	 *
	 * @return void
	 */
	public function render_conditions_column() {
		$conditions = $this->object->get_conditions();
		echo wp_kses_post( $this->get_condition_list_html( $conditions ) );
	}

	/**
	 * Render the boost value column
	 *
	 * @return void
	 */
	public function render_boost_column() {
		$boost          = $this->object->get_boost();
		$lock           = wp_check_post_lock( $this->object->get_id() );
		$class_disabled = false !== $lock ? 'ywcas-field-disabled' : '';
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywcas-boost-value-' . $this->object->get_id(),
				'class' => 'ywcas-boost-value ' . $class_disabled,
				'type'  => 'number',
				'value' => $boost,
				'min'   => 0.1,
				'step'  => 0.1,
				'max'   => 50,
			),
			true,
			false
		);
	}

	/**
	 * Render the boost rule active column
	 *
	 * @return void
	 */
	public function render_active_column() {
		$active         = $this->object->get_active();
		$lock           = wp_check_post_lock( $this->object->get_id() );
		$class_disabled = false !== $lock ? 'ywcas-field-disabled' : '';
		yith_plugin_fw_get_field(
			array(
				'id'    => 'ywcas-boost-active-' . $this->object->get_id(),
				'class' => 'ywcas-boost-active ' . $class_disabled,
				'type'  => 'onoff',
				'value' => $active,
			),
			true,
			false
		);
	}

	/**
	 * Render Actions column
	 */
	protected function render_actions_column() {

		$actions = yith_plugin_fw_get_default_post_actions( $this->post_id );
		if ( isset( $actions['trash'] ) ) {
			unset( $actions['trash'] );
		}
		$actions['delete']                 = array(
			'type'   => 'action-button',
			'title'  => _x( 'Delete Permanently', 'Post action', 'yith-plugin-fw' ),
			'action' => 'delete',
			'icon'   => 'trash',
			'url'    => get_delete_post_link( $this->post_id, '', true ),
		);
		$actions['delete']['confirm_data'] = array(
			'title'               => __( 'Confirm delete', 'yith-plugin-fw' ),
			// phpcs:ignore  WordPress.WP.I18n.MissingTranslatorsComment
			'message'             => sprintf( __( 'Are you sure you want to delete "%s"?', 'yith-plugin-fw' ), '<strong>' . _draft_or_post_title( $this->post_id ) . '</strong>' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-plugin-fw' ),
			'cancel-button'       => __( 'No', 'yith-plugin-fw' ),
			'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-plugin-fw' ),
			'confirm-button-type' => 'delete',
		);

		$lock = wp_check_post_lock( $this->object->get_id() );
		if ( false === $lock ) :
			?>
			<div class="ywcas-actions-column">
				<?php
				yith_plugin_fw_get_action_buttons( $actions, true );
				?>
			</div>
			<?php
		endif;

	}

	/**
	 * Return a formatted list of conditions
	 *
	 * @param array $conditions The conditions to check.
	 *
	 * @return false|string
	 */
	protected function get_condition_list_html( $conditions ) {
		ob_start();
		?>
		<ul class="ywcas-conditions-column-list">
			<?php
			foreach ( $conditions as $condition ) :
				$condition_for  = $condition['condition_config']['condition_for'];
				$condition_type = $condition['condition_config']['condition_type'];

				if ( isset( $this->condition_for_options[ $condition_for ] ) ) {
					switch ( $condition_for ) {
						case 'product_cat':
						case 'product_tag':
							$terms = get_terms(
								array(
									'taxonomy'   => $condition_for,
									'include'    => $condition[ $condition_for ],
									'fields'     => 'names',
									'hide_empty' => false,
								)
							);
							$li    = array(
								'label'   => __( $this->condition_for_options[ $condition_for ] , 'yith-woocommerce-ajax-search' ),
								'content' => implode( ',', $terms ),
							);
							break;
						case 'product_stock_status':
							$li = array(
								'label'   => __( $this->condition_for_options[ $condition_for ], 'yith-woocommerce-ajax-search' ),
								'content' => 'instock' === $condition['stock_status'] ? __( 'In Stock', 'yith-woocommerce-ajax-search' ) : __( 'Out of Stock', 'yith-woocommerce-ajax-search' ),
							);
							break;
						case 'product_price':
							switch ( $condition_type ) {
								case 'in-range':
								case 'not-in-range':
									$content = sprintf(
											/* translators: %1$s is the min price %2$s is the max price.*/
										__(
											'Price <= %1$s and >= %2$s',
											'yith-woocommerce-ajax-search'
										),
										wc_price( $condition['product_price']['min_price'] ),
										wc_price( $condition['product_price']['max_price'] )
									);
									break;
								case 'lower':
									$content = sprintf(
									/* translators: %s is the min price.*/
										__(
											'Price <= %s',
											'yith-woocommerce-ajax-search'
										),
										wc_price( $condition['product_price']['min_price'] )
									);
									break;
								default:
									$content = sprintf(
									/* translators: %s is the max price.*/
										__(
											'Price >= %s',
											'yith-woocommerce-ajax-search'
										),
										wc_price( $condition['product_price']['min_price'] )
									);
									break;
							}
							$li = array(
								'label'   => __( $this->condition_for_options[ $condition_for ], 'yith-woocommerce-ajax-search' ),
								'content' => $content,
							);
							break;
					}
				} else {
					$li = apply_filters( 'ywcas_get_condition_list_html', array(), $condition_for, $condition );
				}
				if ( isset( $li['label'], $li['content'] ) ) :
					?>
					<li>
						<strong><?php echo esc_html( $li['label'] ); ?>:</strong>
						<span><?php echo wp_kses_post( $li['content'] ); ?></span>
					</li>
					<?php
				endif;
			endforeach;
			?>
		</ul>
		<?php
		return ob_get_clean();
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function define_sortable_columns( $columns ) {
		return array(
			'boost'  => array( 'boost', true ),
			'active' => array( 'active', false ),
		);
	}

	/**
	 * Retrieve an array of parameters for blank state.
	 *
	 * @return array{
	 * @type string $icon The YITH icon. You can use this one (to use an YITH icon) or icon_class or icon_url.
	 * @type string $icon_class The icon class. You can use this one (to use a custom class for your icon) or icon or icon_url.
	 * @type string $icon_url The icon URL. You can use this one (to specify an icon URL) or icon_icon or icon_class.
	 * @type string $message The message to be shown.
	 * @type array  $cta {
	 *                            The call-to-action button params.
	 * @type string $title The call-to-action button title.
	 * @type string $icon The call-to-action button icon.
	 * @type string $url The call-to-action button URL.
	 * @type string $class The call-to-action button class.
	 *                            }
	 *                            }
	 */
	protected function get_blank_state_params() {
		$submessage = '<p>' . esc_html__( 'Create a rule now to boost your products!', 'yith-woocommerce-ajax-search' ) . '</p>';

		return array(
			'icon_url' => YITH_WCAS_ASSETS_URL . '/images/boost-rule.svg',
			'message'  => __( 'No rules created yet.', 'yith-woocommerce-ajax-search' ) . $submessage,
			'cta'      => array(
				'title' => __( 'Create rule', 'yith-woocommerce-ajax-search' ),
				'class' => 'ywcas_add_new_boost yith-plugin-fw__button--add',
			),
		);
	}

	/**
	 * Render blank state. Extend to add content.
	 */
	protected function render_blank_state() {
		parent::render_blank_state();
		echo '<style>.page-title-action{display:none!important;}</style>';
	}

	/**
	 * Hide the views
	 *
	 * @return array
	 */
	public function hide_views() {
		return array();
	}

	/**
	 * Handle the custom bulk action.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $do_action Selected bulk action.
	 * @param array  $post_ids Post ids.
	 *
	 * @return string
	 */
	public function handle_bulk_actions( $redirect_to, $do_action, $post_ids ) {

		if ( 'activate' !== $do_action && 'deactivate' !== $do_action ) {
			return parent::handle_bulk_actions( $redirect_to, $do_action, $post_ids );
		}

		foreach ( $post_ids as $boost_id ) {

			$post_type_object = get_post_type_object( $this->post_type );

			if ( current_user_can( $post_type_object->cap->edit_post, $boost_id ) ) {

				switch ( $do_action ) {
					case 'activate':
						update_post_meta( $boost_id, '_active', 'yes' );
						break;
					case 'deactivate':
						update_post_meta( $boost_id, '_active', 'no' );
						break;
					default:
				}
			}
		}

		return $redirect_to;
	}

}

YITH_WCAS_Admin_Boost_Rules_List_Table::instance();
