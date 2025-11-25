<?php
/**
 * Admin Boost Product List Table
 *
 * @author  YITH
 * @package YITH/Search
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Admin_Boost_Product_List_Table' ) ) {
	/**
	 * Admin class for show the list table
	 *
	 * @since 2.1.0
	 */
	class YITH_WCAS_Admin_Boost_Product_List_Table extends WP_List_Table {

		/**
		 * The construct
		 *
		 * @param array $args The args.
		 */
		public function __construct( $args = array() ) {
			parent::__construct( array() );

		}

		/**
		 * Get sorttable columns.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'boost' => array( 'boost', false ),
			);

			return $sortable_columns;
		}

		/**
		 * Get the columns.
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'cb'     => '<input type="checkbox" />',
				'name'   => esc_html__( 'Product', 'yith-woocommerce-ajax-search' ),
				'boost'  => esc_html__( 'Boost value', 'yith-woocommerce-ajax-search' ),
				'action' => '',
			);

			return $columns;
		}

		/**
		 * Prepare items to show
		 */
		public function prepare_items() {
			//phpcs:disable WordPress.Security.NonceVerification.Recommended
			global $_wp_column_headers;
			$screen     = get_current_screen();
			$s          = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : false;
			$totalitems = YITH_WCAS_Data_Index_Lookup::get_instance()->get_boosted_products( $s );

			if ( empty( $totalitems ) ) {
				$this->items = $totalitems;
			} else {
				$order           = ! empty( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC';
				$num_total_items = count( $totalitems );
				$offset          = 0;
				$perpage         = 20;
				$paged           = ! empty( $_GET['paged'] ) ? sanitize_text_field( wp_unslash( $_GET['paged'] ) ) : '';

				if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
					$paged = 1;
				}

				$totalpages = ceil( $num_total_items / $perpage );
				if ( ! empty( $paged ) && ! empty( $perpage ) ) {
					$offset = ( $paged - 1 ) * $perpage;
				}

				$this->set_pagination_args(
					array(
						'total_items' => $num_total_items,
						'total_pages' => $totalpages,
						'per_page'    => $perpage,
					)
				);

				$this->items = YITH_WCAS_Data_Index_Lookup::get_instance()->get_boosted_products( $s, $order, $perpage, $offset );

				$columns               = $this->get_columns();
				$hidden                = array();
				$sortable              = $this->get_sortable_columns();
				$this->_column_headers = array( $columns, $hidden, $sortable );
				if ( isset( $screen->id ) ) {
					$_wp_column_headers[ $screen->id ] = $columns;
				}
			}

		}

		/**
		 * Show the cb column
		 *
		 * @param array $item The item.
		 *
		 * @return void
		 */
		public function column_cb( $item ) {
			?>
			<input id="cb-select-<?php echo esc_attr( $item['post_id'] ); ?>" type="checkbox" name="boost[]"
				   value="<?php echo esc_attr( $item['post_id'] ); ?>"/>
			<label for="cb-select-<?php echo esc_attr( $item['post_id'] ); ?>">
				<span class="screen-reader-text">
				<?php
				/* translators: %s: Post title. */
				echo wp_kses_post( sprintf( __( 'Select %s' ), $item['name'] ) );
				?>
				</span>
			</label>
			<?php
		}

		/**
		 * Fill the columns.
		 *
		 * @param object $item Current Object.
		 * @param string $column_name Current Column.
		 *
		 * @return string
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'name':
					$product           = wc_get_product( $item['post_id'] );
					$post_thumbnail_id = $product->get_image_id();

					if ( $post_thumbnail_id ) {
						$html = wp_get_attachment_image( $post_thumbnail_id, array( 50, 50 ) );
					} else {
						$html = sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
					}
					$content_column = '<div class="col-name-image">' . $html . $item['name'] . '</div>';
					break;
				case 'boost':
					$content_column = '<input type="number" name="ywcas_boost" step="0.1" min="0.1" max ="50" data-post_id=' . $item['post_id'] . ' value="' . floatval( $item['boost'] ) . '">';
					break;
				case 'action':
					$content_column  = '<div class="action-wrapper" data-post_id="' . $item['post_id'] . '">';
					$content_column .= yith_plugin_fw_get_component(
						array(
							'type'   => 'action-button',
							'action' => 'delete',
							'icon'   => 'trash',
							'url'    => '#',
						),
						false
					);
					$content_column .= '</div>';
					break;
			}

			return $content_column;

		}

		/**
		 * Define bulk actions.
		 *
		 * @return array
		 */
		public function get_bulk_actions() {

			return array( 'delete' => __( 'Delete', 'yith-woocommerce-ajax-search' ) );
		}

		/**
		 * Displays the pagination.
		 *
		 * @param string $which The position.
		 *
		 * @since 2.1.0
		 */
		protected function pagination( $which ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing,WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( empty( $this->_pagination_args ) ) {
				return;
			}

			$total_items     = $this->_pagination_args['total_items'];
			$total_pages     = $this->_pagination_args['total_pages'];
			$infinite_scroll = false;
			if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
				$infinite_scroll = $this->_pagination_args['infinite_scroll'];
			}

			if ( 'top' === $which && $total_pages > 1 ) {
				$this->screen->render_screen_reader_content( 'heading_pagination' );
			}

			$output = '<span class="displaying-num">';

			$output .= sprintf(
			/* translators: %s: Number of items. */
				_n( '%s item', '%s items', $total_items ),
				number_format_i18n( $total_items )
			);
			$output .= '</span>';

			$current              = $this->get_pagenum();
			$removable_query_args = wp_removable_query_args();

			if ( isset( $_POST['href'] ) ) {
				$current_url = sanitize_text_field( wp_unslash( $_POST['href'] ) );
			} else {
				$order       = ! empty( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC';
				$current_url = add_query_arg(
					array(
						'page'    => 'yith_wcas_panel',
						'tab'     => 'boost',
						'sub_tab' => 'boost-boost-product',
						'order'   => $order,
					),
					admin_url( 'admin.php' )
				);
			}

			$current_url = remove_query_arg( $removable_query_args, $current_url );

			$page_links = array();

			$total_pages_before = '<span class="paging-input">';
			$total_pages_after  = '</span></span>';

			$disable_first = false;
			$disable_last  = false;
			$disable_prev  = false;
			$disable_next  = false;

			if ( 1 === $current ) {
				$disable_first = true;
				$disable_prev  = true;
			}
			if ( $total_pages === $current ) {
				$disable_last = true;
				$disable_next = true;
			}

			if ( $disable_first ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
			} else {
				$page_links[] = sprintf(
					"<a class='first-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
					'</a>',
					esc_url( remove_query_arg( 'paged', $current_url ) ),
					/* translators: Hidden accessibility text. */
					__( 'First page' ),
					'&laquo;'
				);
			}

			if ( $disable_prev ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
			} else {
				$page_links[] = sprintf(
					"<a class='prev-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
					'</a>',
					esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
					/* translators: Hidden accessibility text. */
					__( 'Previous page' ),
					'&lsaquo;'
				);
			}

			if ( 'bottom' === $which ) {
				$html_current_page  = $current;
				$total_pages_before = sprintf(
					'<span class="screen-reader-text">%s</span>' .
					'<span id="table-paging" class="paging-input">' .
					'<span class="tablenav-paging-text">',
					/* translators: Hidden accessibility text. */
					__( 'Current Page' )
				);
			} else {
				$html_current_page = sprintf(
					'<label for="current-page-selector" class="screen-reader-text">%s</label>' .
					"<input class='current-page' id='current-page-selector' type='text'
					name='paged' value='%s' size='%d' aria-describedby='table-paging' />" .
					"<span class='tablenav-paging-text'>",
					/* translators: Hidden accessibility text. */
					__( 'Current Page' ),
					$current,
					strlen( $total_pages )
				);
			}

			$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );

			$page_links[] = $total_pages_before . sprintf(
				/* translators: 1: Current page, 2: Total pages. */
				_x( '%1$s of %2$s', 'paging' ),
				$html_current_page,
				$html_total_pages
			) . $total_pages_after;

			if ( $disable_next ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
			} else {
				$page_links[] = sprintf(
					"<a class='next-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
					'</a>',
					esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ),
					/* translators: Hidden accessibility text. */
					__( 'Next page' ),
					'&rsaquo;'
				);
			}

			if ( $disable_last ) {
				$page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
			} else {
				$page_links[] = sprintf(
					"<a class='last-page button' href='%s'>" .
					"<span class='screen-reader-text'>%s</span>" .
					"<span aria-hidden='true'>%s</span>" .
					'</a>',
					esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
					/* translators: Hidden accessibility text. */
					__( 'Last page' ),
					'&raquo;'
				);
			}

			$pagination_links_class = 'pagination-links';
			if ( ! empty( $infinite_scroll ) ) {
				$pagination_links_class .= ' hide-if-js';
			}
			$output .= "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';

			if ( $total_pages ) {
				$page_class = $total_pages < 2 ? ' one-page' : '';
			} else {
				$page_class = ' no-pages';
			}
			$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

			echo $this->_pagination;
		}

		/**
		 * Prints column headers, accounting for hidden and sortable columns.
		 *
		 * @param bool $with_id Whether to set the ID attribute or not.
		 *
		 * @since 2.1.0
		 */
		public function print_column_headers( $with_id = true ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended,WordPress.Security.NonceVerification.Missing
			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
			if ( isset( $_POST['href'] ) ) {
				$current_url = sanitize_text_field( wp_unslash( $_POST['href'] ) );
			} else {
				$current_url = add_query_arg(
					array(
						'page'    => 'yith_wcas_panel',
						'tab'     => 'boost',
						'sub_tab' => 'boost-boost-product',
					),
					admin_url( 'admin.php' )
				);
			}

			$current_url = remove_query_arg( 'paged', $current_url );

			// When users click on a column header to sort by other columns.
			if ( isset( $_GET['orderby'] ) ) {
				$current_orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
				// In the initial view there's no orderby parameter.
			} else {
				$current_orderby = '';
			}

			// Not in the initial view and descending order.
			if ( isset( $_GET['order'] ) && 'desc' === sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) {
				$current_order = 'desc';
			} else {
				// The initial view is not always 'asc', we'll take care of this below.
				$current_order = 'asc';
			}

			if ( ! empty( $columns['cb'] ) ) {
				static $cb_counter = 1;
				$columns['cb']     = '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />
			<label for="cb-select-all-' . $cb_counter . '">' .
								 '<span class="screen-reader-text">' .
								 /* translators: Hidden accessibility text. */
								 __( 'Select All' ) .
								 '</span>' .
								 '</label>';
				++ $cb_counter;
			}

			foreach ( $columns as $column_key => $column_display_name ) {
				$class          = array( 'manage-column', "column-$column_key" );
				$aria_sort_attr = '';
				$abbr_attr      = '';
				$order_text     = '';

				if ( in_array( $column_key, $hidden, true ) ) {
					$class[] = 'hidden';
				}

				if ( 'cb' === $column_key ) {
					$class[] = 'check-column';
				} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
					$class[] = 'num';
				}

				if ( $column_key === $primary ) {
					$class[] = 'column-primary';
				}

				if ( isset( $sortable[ $column_key ] ) ) {
					$orderby       = isset( $sortable[ $column_key ][0] ) ? $sortable[ $column_key ][0] : '';
					$desc_first    = isset( $sortable[ $column_key ][1] ) ? $sortable[ $column_key ][1] : false;
					$abbr          = isset( $sortable[ $column_key ][2] ) ? $sortable[ $column_key ][2] : '';
					$orderby_text  = isset( $sortable[ $column_key ][3] ) ? $sortable[ $column_key ][3] : '';
					$initial_order = isset( $sortable[ $column_key ][4] ) ? $sortable[ $column_key ][4] : '';

					/*
					 * We're in the initial view and there's no $_GET['orderby'] then check if the
					 * initial sorting information is set in the sortable columns and use that.
					 */
					if ( '' === $current_orderby && $initial_order ) {
						// Use the initially sorted column $orderby as current orderby.
						$current_orderby = $orderby;
						// Use the initially sorted column asc/desc order as initial order.
						$current_order = $initial_order;
					}

					/*
					 * True in the initial view when an initial orderby is set via get_sortable_columns()
					 * and true in the sorted views when the actual $_GET['orderby'] is equal to $orderby.
					 */
					if ( $current_orderby === $orderby ) {
						// The sorted column. The `aria-sort` attribute must be set only on the sorted column.
						if ( 'asc' === $current_order ) {
							$order          = 'desc';
							$aria_sort_attr = ' aria-sort="ascending"';
						} else {
							$order          = 'asc';
							$aria_sort_attr = ' aria-sort="descending"';
						}

						$class[] = 'sorted';
						$class[] = $current_order;
					} else {
						// The other sortable columns.
						$order = strtolower( $desc_first );

						if ( ! in_array( $order, array( 'desc', 'asc' ), true ) ) {
							$order = $desc_first ? 'desc' : 'asc';
						}

						$class[] = 'sortable';
						$class[] = 'desc' === $order ? 'asc' : 'desc';

						/* translators: Hidden accessibility text. */
						$asc_text = __( 'Sort ascending.' );
						/* translators: Hidden accessibility text. */
						$desc_text  = __( 'Sort descending.' );
						$order_text = 'asc' === $order ? $asc_text : $desc_text;
					}

					if ( '' !== $order_text ) {
						$order_text = ' <span class="screen-reader-text">' . $order_text . '</span>';
					}

					// Print an 'abbr' attribute if a value is provided via get_sortable_columns().
					$abbr_attr = $abbr ? ' abbr="' . esc_attr( $abbr ) . '"' : '';

					$column_display_name = sprintf(
						'<a href="%1$s">' .
						'<span>%2$s</span>' .
						'<span class="sorting-indicators">' .
						'<span class="sorting-indicator asc" aria-hidden="true"></span>' .
						'<span class="sorting-indicator desc" aria-hidden="true"></span>' .
						'</span>' .
						'%3$s' .
						'</a>',
						esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ),
						$column_display_name,
						$order_text
					);
				}

				$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
				$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
				$id    = $with_id ? "id='$column_key'" : '';

				if ( ! empty( $class ) ) {
					$class = "class='" . implode( ' ', $class ) . "'";
				}

				echo "<$tag $scope $id $class $aria_sort_attr $abbr_attr>$column_display_name</$tag>";
			}
		}

		/**
		 * Manage the bulk action requested.
		 */
		public function current_action() {
			if ( isset( $_REQUEST['action'], $_REQUEST['boost'] ) && 'delete' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) {
				$boost = wp_unslash( $_REQUEST['boost'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				YITH_WCAS_Data_Index_Lookup::get_instance()->set_boost_to_products( $boost, 0 );
			}
		}


		/**
		 * Displays the search box.
		 *
		 * @param string $text The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 *
		 * @since 2.1.0
		 */
		public function search_box( $text, $input_id ) {
			if ( ! $this->has_items() ) {
				return;
			}

			$input_id = $input_id . '-search-input';

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) ) . '" />';
			}
			if ( ! empty( $_REQUEST['order'] ) ) {
				echo '<input type="hidden" name="order" value="' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) . '" />';
			}

			?>
			<p class="search-box">
				<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>
					:</label>
				<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s"
					   value="<?php _admin_search_query(); ?>"/>
				<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
			</p>
			<?php
		}


		/**
		 * Show now items view
		 *
		 * @return void
		 */
		public function no_items() {
			echo ' <div class="ywcas-boost-detail yith-plugin-ui--boxed-wp-list-style ywcas-boost-product-wrapper">';
			echo '<div class="yith-plugin-fw-wp-page-wrapper"><div class="wrap"> <div id="message" class="notice is-dismissible updated inline yith-plugin-fw-animate__appear-from-topn hide">
					<p>' . esc_html__( 'The rule has permanently deleted.', 'yith-woocommerce-ajax-search' ) . '</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'yith-woocommerce-ajax-search' ) . '</span></button></div></div></div>';
			yith_plugin_fw_get_component(
				array(
					'type'     => 'list-table-blank-state',
					'icon_url' => YITH_WCAS_ASSETS_URL . '/images/boost-product.svg',
					/* translators: %1$s is html tag, %2$s another html tag */
					'message'  => sprintf( _x( 'No products boosted yet.%1$sAdd now a product to set a custom boost value!%2$s', 'Text showed when the list of email is empty.', 'yith-woocommerce-ajax-search' ), '<br><p>', '</p>' ),
					'cta'      => array(
						'title' => __( 'Choose product', 'yith-woocommerce-ajax-search' ),
						'class' => 'ywcas-boost-product-button',
					),
				)
			);
			echo '</div>';
		}
	}
}
