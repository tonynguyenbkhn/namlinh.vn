<?php
/**
 * Class YITH_YWAR_Blocklist_Admin_Table
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Admin\AdminTables
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Blocklist_Admin_Table' ) ) {
	/**
	 * Class YITH_YWAR_Blocklist_Admin_Table
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview\Admin\AdminTables
	 */
	class YITH_YWAR_Blocklist_Admin_Table extends WP_List_Table {

		/**
		 * Is the table filtered?
		 *
		 * @var bool
		 */
		private $filtered = false;

		/**
		 * YITH_YWAR_Blocklist_Admin_Table constructor.
		 *
		 * @return void
		 * @since   2.0.0
		 */
		public function __construct() {
			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'customer',  // singular name of the listed records.
					'plural'   => 'customers', // plural name of the listed records.
					'ajax'     => false,        // does this table support ajax?.
				)
			);

			$this->handle_bulk_action();
		}

		/**
		 * Returns columns available in table
		 *
		 * @return array Array of columns of the table.
		 * @since  2.0.0
		 */
		public function get_columns(): array {
			return array(
				'cb'             => '<input type="checkbox" />',
				'customer_name'  => esc_html_x( 'Customer', '[Admin panel] column name', 'yith-woocommerce-advanced-reviews' ),
				'customer_email' => esc_html_x( 'Email address', '[Admin panel] column name', 'yith-woocommerce-advanced-reviews' ),
				'actions'        => '',
			);
		}

		/**
		 * Print default column content
		 *
		 * @param object $item        Item of the row.
		 * @param string $column_name Column name.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function column_default( $item, $column_name ): string {
			$column = '';
			switch ( $column_name ) {
				case 'customer_name':
					$user = get_userdata( $item->customer_id );
					if ( $user ) {
						$customer_name = sprintf( '%1$s %2$s', $user->get( 'billing_first_name' ), $user->get( 'billing_last_name' ) );
						$customer_name = trim( $customer_name ) === '' ? $user->nickname : $customer_name;
						$edit_url      = esc_url( add_query_arg( array( 'user_id' => $item->customer_id ), admin_url( 'user-edit.php' ) ) );
						$column        = sprintf( '<a target="_blank" href="%1$s">%2$s</a>', $edit_url, $customer_name );
					} else {
						$column = esc_html_x( 'Unregistered user', '[Admin panel] label to display in blocklist if the user is not registered', 'yith-woocommerce-advanced-reviews' );
					}
					break;
				case 'customer_email':
					$column = $item->$column_name;
					break;
				case 'actions':
					$actions['delete'] = array(
						'type'   => 'action-button',
						'title'  => esc_html_x( 'Remove from blocklist', '[Admin panel] action button label', 'yith-woocommerce-advanced-reviews' ),
						'action' => 'delete',
						'icon'   => 'trash',
						'url'    => '',
						'class'  => 'action__delete',
						'data'   => array(
							'id' => $item->id,
						),
					);

					$column = yith_plugin_fw_get_action_buttons( $actions );
					break;
			}

			return $column;
		}

		/**
		 * Column cb.
		 *
		 * @param object $item .
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function column_cb( $item ): string {
			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item->id );
		}

		/**
		 * Get bulk actions
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_bulk_actions(): array {
			return array( 'delete' => esc_html_x( 'Remove from blocklist', '[Admin panel] action button label', 'yith-woocommerce-advanced-reviews' ) );
		}

		/**
		 * Process Bulk Actions
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function handle_bulk_action() {
			$action = $this->current_action();

			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-' . $this->_args['plural'] ) ) {
				return;
			}

			$items = isset( $_REQUEST[ $this->_args['singular'] ] ) ? wp_unslash( $_REQUEST[ $this->_args['singular'] ] ) : array(); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( $action && ! empty( $items ) ) {
				if ( 'delete' === $action ) {
					foreach ( $items as $item ) {
						yith_ywar_delete_from_blocklist( $item );
					}
					isset( $_REQUEST['_wp_http_referer'] ) && wp_safe_redirect( sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ) );
				}
			}
		}

		/**
		 * Get sortable columns
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_sortable_columns(): array {
			return array(
				'customer_name'  => array( 'customer_name', true ),
				'customer_email' => array( 'customer_email', false ),
			);
		}

		/**
		 * Prepare items for table
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function prepare_items() {

			if ( ! isset( $_REQUEST['s'] ) && isset( $_REQUEST['_wp_http_referer'] ) && ! empty( $_REQUEST['_wp_http_referer'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// _wp_http_referer is used only on bulk actions, we remove it to keep the $_GET shorter.
				wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'action', 'action2' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
				exit;
			}

			$search_param     = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$limit            = $this->get_items_per_page( 'user_per_page', 10 );
			$this->filtered   = '' !== $search_param ?? false;
			$view_columns     = $this->get_columns();
			$hidden_columns   = array();
			$sortable_columns = $this->get_sortable_columns();

			// Here we configure table headers, defined in our methods.
			$this->_column_headers = array( $view_columns, $hidden_columns, $sortable_columns );

			// Will be used in pagination settings.
			$total_items = YITH_YWAR_Request_Review_DB::count_total_blocklist( $search_param );

			// Prepare query params, as usual current page, order by and order direction.
			$paged = isset( $_GET['paged'] ) ? $limit * ( intval( $_GET['paged'] ) - 1 ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$this->items = YITH_YWAR_Request_Review_DB::list_blocklist( $search_param, $limit, $paged );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $limit,
					'total_pages' => ceil( $total_items / $limit ),
				)
			);
		}

		/**
		 * Display the table
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function display() {
			if ( ! $this->has_items() && ! $this->filtered ) {
				yith_plugin_fw_get_component(
					array(
						'type'    => 'list-table-blank-state',
						'icon'    => 'user-off',
						'message' => esc_html_x( 'No user in the blocklist.', '[Admin panel] Empty state message', 'yith-woocommerce-advanced-reviews' ),
						'cta'     => array(
							'title' => esc_html_x( 'Add email', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ),
							'class' => 'yith-ywar-add-to-blocklist',
							'url'   => '#',
							'icon'  => 'plus',
						),
					)
				);
			} else {
				$singular = $this->_args['singular'];

				$this->display_tablenav( 'top' );
				$this->screen->render_screen_reader_content( 'heading_list' );
				?>
				<div class="yith-plugin-ui__wp-list-auto-h-scroll">
					<table class="wp-list-table <?php echo esc_html( implode( ' ', $this->get_table_classes() ) ); ?>">
						<?php $this->print_table_description(); ?>
						<thead>
						<tr>
							<?php $this->print_column_headers(); ?>
						</tr>
						</thead>
						<tbody id="the-list" <?php echo ( $singular ) ? wp_kses_post( " data-wp-lists='list:$singular'" ) : ''; ?>>
						<?php $this->display_rows_or_placeholder(); ?>
						</tbody>
						<tfoot>
						<tr>
							<?php $this->print_column_headers( false ); ?>
						</tr>
						</tfoot>
					</table>
				</div>
				<?php
				$this->display_tablenav( 'bottom' );
			}
		}

		/**
		 * Display the search box.
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );
			?>
			<input type="hidden" name="page" value="<?php echo esc_attr( YITH_YWAR_Admin::PANEL_PAGE ); ?>"/>
			<input type="hidden" name="tab" value="<?php echo esc_attr( YITH_YWAR_Request_Review::KEY ); ?>"/>
			<input type="hidden" name="sub_tab" value="<?php echo esc_attr( YITH_YWAR_Request_Review::KEY ); ?>-blocklist"/>
			<?php
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @param string $which The placement, one of 'top' or 'bottom'.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which && $this->filtered ) {
				?>
				<a id="yith-plugin-fw__wp-list__reset-filters" class="yith-plugin-fw__button--tertiary" href="<?php echo esc_url( add_query_arg( array() ) ); ?>"><?php echo esc_html_x( 'Reset filters', '[Admin panel] Reset filter button label', 'yith-woocommerce-advanced-reviews' ); ?></a>
				<?php
			}
		}
	}
}
