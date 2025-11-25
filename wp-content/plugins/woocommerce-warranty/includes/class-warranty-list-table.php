<?php
/**
 * File for warranty list table display.
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class for warranty list table display.
 */
class Warranty_List_Table extends WP_List_Table {

	/**
	 * `warranty_form` option value.
	 *
	 * @var array
	 */
	private $form;

	/**
	 * Form inputs.
	 *
	 * @var array
	 */
	private $inputs;

	/**
	 * Row reason injected.
	 *
	 * @var boolean
	 */
	private $row_reason_injected = false;

	/**
	 * Number of columns.
	 *
	 * @var int
	 */
	private $num_columns = 0;

	/**
	 * Registered warranty statuses.
	 *
	 * @var array
	 */
	private $statuses = null;

	/**
	 * Class constructor
	 *
	 * @param array $args Class args.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( $args );

		$this->form   = get_option( 'warranty_form' );
		$this->inputs = json_decode( $this->form['inputs'] );

		add_filter( 'posts_clauses', array( $this, 'status_orderby_clauses' ), 10, 2 );

		$this->get_statuses();
	}

	/**
	 * Method to add SQL query when orderby value is `shop_warranty_status`.
	 *
	 * @param array    $clauses Existing SQL clauses.
	 * @param WP_Query $wp_query WP_Query object.
	 *
	 * @return array
	 */
	public function status_orderby_clauses( $clauses, $wp_query ) {
		global $wpdb;

		if ( isset( $wp_query->query['orderby'] ) && 'shop_warranty_status' === $wp_query->query['orderby'] ) {
			$clauses['join']    .= " LEFT OUTER JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID={$wpdb->term_relationships}.object_id";
			$clauses['join']    .= " LEFT OUTER JOIN {$wpdb->term_taxonomy} USING (term_taxonomy_id)";
			$clauses['join']    .= " LEFT OUTER JOIN {$wpdb->terms} USING (term_id)";
			$clauses['where']   .= " AND (taxonomy = 'shop_warranty_status' OR taxonomy IS NULL)";
			$clauses['groupby']  = 'object_id';
			$clauses['orderby']  = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC) ";
			$clauses['orderby'] .= ( 'ASC' === strtoupper( $wp_query->get( 'order' ) ) ) ? 'ASC' : 'DESC';
		}

		return $clauses;
	}

	/**
	 * Get all statuses.
	 *
	 * @return array
	 */
	private function get_statuses() {
		if ( is_null( $this->statuses ) ) {
			$this->statuses = warranty_get_statuses();
		}

		return $this->statuses;
	}

	/**
	 * Return array with column definition.
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'rma'          => __( 'Return Details', 'woocommerce-warranty' ),
			'products'     => __( 'Products', 'woocommerce-warranty' ),
			'request_type' => __( 'Request Type', 'woocommerce-warranty' ),
			'date'         => __( 'Last Updated', 'woocommerce-warranty' ),
			'status'       => __( 'Status', 'woocommerce-warranty' ),
		);

		$this->num_columns = count( $columns );

		return $columns;
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'order_id' => array( 'order_id', false ),
			'status'   => array( 'shop_warranty_status', false ),
			'date'     => array( 'date', true ),
		);
		return $sortable_columns;
	}

	/**
	 * Add a new field on table navigation form.
	 *
	 * @param string $which Which side of table navigation.
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$get_data = warranty_request_get_data();

			echo '<form action="admin.php" method="get" style="margin-top: 20px;">';
			echo '  <div class="alignleft actions">';
			echo '      <select name="status" id="status" class="postform">';
			echo '          <option value="">' . esc_html__( 'All Statuses', 'woocommerce-warranty' ) . '</option>';

			foreach ( $this->get_statuses() as $status ) {
				$current_status = isset( $get_data['status'] ) ? $get_data['status'] : '';
				echo '<option value="' . esc_attr( $status->slug ) . '" ' . selected( $current_status, $status->slug, false ) . '>' . esc_html( $status->name ) . '</option>';
			}

			echo '      </select>';
			echo '      <input type="hidden" name="page" value="warranties" />';
			submit_button( __( 'Filter', 'woocommerce-warranty' ), 'secondary', false, false, array( 'id' => 'post-query-submit' ) );
			echo '  </div>';
			echo '</form>';
		}
	}

	/**
	 * Preparing column items.
	 */
	public function prepare_items() {
		global $wpdb;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$get_data     = warranty_request_get_data();
		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$query_args   = array(
			'post_type'      => 'warranty_request',
			'orderby'        => 'date',
			'order'          => 'desc',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
		);

		if ( ! empty( $get_data['orderby'] ) ) {
			$query_args['orderby'] = $get_data['orderby'];
			$query_args['order']   = isset( $get_data['order'] ) ? $get_data['order'] : '';
		}

		// Filter by status.
		if ( isset( $get_data['status'] ) && ! empty( $get_data['status'] ) ) {
			// phpcs:ignore --- Tax query is needed to get the warranty request with certain status
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'shop_warranty_status',
					'field'    => 'slug',
					'terms'    => $get_data['status'],
				),
			);
		}

		if ( isset( $get_data['s'] ) && ! empty( $get_data['s'] ) ) {

			$full_name  = explode( ' ', $get_data['s'] );
			$order_args = array(
				'billing_first_name' => $full_name[0],
				'return'             => 'ids',
				'limit'              => -1,
			);

			if ( ! empty( $full_name[1] ) ) {
				$order_args['billing_last_name'] = $full_name[1];
			}

			$existing_orders = wc_get_orders( $order_args );

			if ( ! empty( $existing_orders ) ) {
				// The query for searching the warranty request based on Billing name.
				$query_args['post_parent__in'] = $existing_orders;
			} else {
				// The query for searching the warranty request based on Order ID.
				$query_args['search_columns'] = array( 'post_title' );
				$query_args['s']              = $get_data['s'];
			}
		}

		if ( class_exists( 'WC_Product_Vendors_Utils' ) ) {
			$vendor_id = WC_Product_Vendors_Utils::get_logged_in_vendor( 'id' );

			if ( $vendor_id ) {
				$order_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT order_id
						FROM {$wpdb->prefix}wcpv_commissions c, {$wpdb->postmeta} pm
						WHERE c.vendor_id = %d
						AND c.order_id = pm.meta_value
						AND pm.meta_key = '_order_id'",
						$vendor_id
					)
				);

				$query_args['meta_query'][] = array(
					'key'     => '_order_id',
					'value'   => $order_ids,
					'compare' => 'IN',
				);
			}
		}

		$wp_query = new WP_Query( $query_args );

		$total_items = $wp_query->found_posts;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = array();

		while ( $wp_query->have_posts() ) :
			$wp_query->the_post();
			$id       = get_the_ID();
			$warranty = warranty_load( $id );
			if ( $warranty ) {
				$this->items[] = $warranty;
			}
		endwhile;

		wp_reset_postdata();
	}

	/**
	 * Modifying default column value.
	 *
	 * @param array  $item Column item.
	 * @param string $column_name Column name.
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		$requests_str = array(
			'replacement' => __( 'Replacement item', 'woocommerce-warranty' ),
			'refund'      => __( 'Refund', 'woocommerce-warranty' ),
			'coupon'      => __( 'Refund as store credit', 'woocommerce-warranty' ),
		);

		if ( 'request_type' === $column_name ) {
			if ( empty( $item['request_type'] ) || ! array_key_exists( $item['request_type'], $requests_str ) ) {
				$item['request_type'] = 'replacement';
			}

			return $requests_str[ $item['request_type'] ];
		}

		switch ( $column_name ) {
			case 'order_id':
			case 'customer':
			case 'rma':
			case 'tracking':
			case 'date':
				return $item[ $column_name ];
			default:
				break;
		}
	}

	/**
	 * Value of column status.
	 *
	 * @param array $item Column item.
	 */
	public function column_status( $item ) {
		$statuses = warranty_get_statuses();

		$order_id    = get_post_meta( $item['ID'], '_order_id', true );
		$order       = wc_get_order( $order_id );
		$permissions = get_option( 'warranty_permissions', array() );
		$returned    = get_option( 'warranty_returned_status', 'completed' );
		$term        = wp_get_post_terms( $item['ID'], 'shop_warranty_status' );
		$status      = ( isset( $term[0] ) && $term[0] instanceof WP_Term ) ? $term[0] : get_term_by( 'slug', 'new', 'shop_warranty_status' );
		$me          = wp_get_current_user();
		$readonly    = true;

		$users_has_permissions = isset( $permissions[ $status->slug ] ) && ! empty( $permissions[ $status->slug ] ) ? array_map( 'intval', $permissions[ $status->slug ] ) : array();

		if ( in_array( 'administrator', $me->roles, true ) ) {
			$readonly = false;
		} elseif ( empty( $users_has_permissions ) ) {
			$readonly = false;
		} elseif ( in_array( $me->ID, $permissions[ $status->slug ], true ) ) {
			$readonly = false;
		}

		if ( $readonly ) {
			$content = ucfirst( $status->name );
		} else {
			$content = '<select name="status" id="status_' . esc_attr( $item['ID'] ) . '">';

			foreach ( $statuses as $_status ) :
				$sel      = ( $status->slug === $_status->slug ) ? 'selected' : '';
				$content .= '<option value="' . esc_attr( $_status->slug ) . '" ' . $sel . '>' . ucfirst( $_status->name ) . '</option>';
			endforeach;

			$nonce    = wp_create_nonce( 'wc_warranty_update_status_nonce_' . $item['ID'] );
			$content .= '</select>
			<input type="hidden" id="update_status_nonce_' . esc_attr( $item['ID'] ) . '" name="security" value="' . esc_attr( $nonce ) . '" />
			<button class="button update-status" type="button" title="Update" data-request_id="' . esc_attr( $item['ID'] ) . '"><span>' . __( 'Update', 'woocommerce-warranty' ) . '</span></button>
			';
		}
		return $content;
	}

	/**
	 * Value of column email.
	 *
	 * @param array $item Column item.
	 */
	public function column_email( $item ) {
		$email = get_post_meta( $item['ID'], '_email', true );
		return $email;
	}

	/**
	 * Value of column products.
	 *
	 * @param array $item Column item.
	 */
	public function column_products( $item ) {
		$request_items         = warranty_get_request_items( $item['ID'] );
		$fallback_product_name = ! empty( $item['product_name'] ) ? esc_html( $item['product_name'] ) : '';

		if ( empty( $request_items ) ) {
			return $this->get_formatted_product_line( $fallback_product_name );
		}

		$order  = wc_get_order( $item['order_id'] );
		$output = array();

		foreach ( $request_items as $request_item ) {
			$product_id = intval( $request_item['product_id'] );
			$quantity   = intval( $request_item['quantity'] );

			if ( $product_id > 0 ) {
				$title            = warranty_get_product_title( $product_id );
				$request_item_url = add_query_arg(
					array(
						'post'   => $product_id,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				);

				$output[] = $this->get_formatted_product_line( sprintf( '<a href="%1$s">%2$s</a>', esc_url( $request_item_url ), esc_html( $title ) ), $quantity );
				continue;
			}

			if ( ! $order instanceof WC_Order ) {
				$output[] = $this->get_formatted_product_line( $fallback_product_name );
				continue;
			}

			$order_item = $order->get_item( $request_item['order_item_index'] );
			$item_name  = $order_item instanceof WC_Order_Item_Product ? esc_html( $order_item->get_name() ) : $fallback_product_name;

			$output[] = $this->get_formatted_product_line( $item_name, $quantity );
		}

		return implode( "\n", $output );
	}

	/**
	 * Formats a product display line.
	 *
	 * @param string $name The product name.
	 * @param int    $quantity The quantity of the product.
	 *
	 * @return string
	 */
	private function get_formatted_product_line( string $name, int $quantity = 1 ): string {
		return sprintf( '%1$s &times; %2$d<br/>', $name, $quantity );
	}

	/**
	 * Value of column RMA.
	 *
	 * @param array $item Column item.
	 */
	public function column_rma( $item ) {
		$statuses     = warranty_get_statuses();
		$returned     = get_option( 'warranty_returned_status', 'completed' );
		$term         = wp_get_post_terms( $item['ID'], 'shop_warranty_status' );
		$status       = ( ! empty( $term ) ) ? $term[0]->slug : current( $statuses );
		$request_type = empty( $item['request_type'] ) ? 'replacement' : $item['request_type'];
		$order        = wc_get_order( $item['order_id'] );
		$link         = '';

		if ( $order && $order->get_customer_id() ) {
			$link = get_edit_user_link( $order->get_customer_id() );
		}

		if ( $link ) {
			$customer = '<a href="' . $link . '">' . $item['first_name'] . ' ' . $item['last_name'] . '</a><small class="meta">' . $item['email'] . '</small>';
		} else {
			$customer = '<strong>' . $item['first_name'] . ' ' . $item['last_name'] . '</strong><small class="meta">' . $item['email'] . '</small>';
		}

		$order_number = ( $order ) ? $order->get_order_number() : '-';

		if ( ! $order ) {
			if ( class_exists( 'WC_Seq_Order_Number' ) ) {
				$seq_order_num_obj = ( function_exists( 'wc_sequential_order_numbers' ) ) ? wc_sequential_order_numbers() : $GLOBALS['wc_seq_order_number'];
				$order_id          = is_callable( array( $seq_order_num_obj, 'find_order_by_order_number' ) ) ? $seq_order_num_obj->find_order_by_order_number( $item['order_id'] ) : false;
				$edit_url          = WC_Warranty_Compatibility::get_order_admin_edit_url( $order_id );
				if ( $order_id ) {
					$item_order = '<a href="' . esc_url( $edit_url ) . '">#' . $item['order_id'] . '</a>';
				} else {
					$item_order = '#' . $item['order_id'];
				}
			} else {
				$item_order = '#' . $item['order_id'];
			}
		} else {
			$edit_url   = WC_Warranty_Compatibility::get_order_admin_edit_url( $item['order_id'] );
			$item_order = '<a href="' . esc_url( $edit_url ) . '">#' . $order_number . '</a>';
		}
		// translators: Order number with link.
		$order_str = sprintf( __( 'Order %s', 'woocommerce-warranty' ), $item_order );

		$actions = array(
			'inline-edit' => '<a href="#" class="inline-edit" data-request_id="' . $item['ID'] . '">' . __( 'Manage', 'woocommerce-warranty' ) . '</a>',
		);

		$product_id   = get_post_meta( $item['ID'], '_product_id', true );
		$product      = wc_get_product( $product_id );
		$manage_stock = '';

		if ( $product && $product->is_type( 'variation' ) ) {
			$variation_id = ( version_compare( WC_VERSION, '3.0', '<' ) && isset( $product->variation_id ) ) ? $product->variation_id : $product->get_id();
			$stock        = get_post_meta( $variation_id, '_stock', true );

			if ( $stock > 0 ) {
				$manage_stock = 'yes';
			}
		} else {
			$manage_stock = get_post_meta( $product_id, '_manage_stock', true );
		}

		if ( $status === $returned && 'yes' === $manage_stock ) {
			if ( 'yes' === get_post_meta( $item['ID'], '_returned', true ) ) {
				$actions['inventory-return'] = __( 'Stock Returned', 'woocommerce-warranty' );
			} else {
				$actions['inventory-return'] = '<a href="' . wp_nonce_url( 'admin-post.php?action=warranty_return_inventory&id=' . $item['ID'], 'warranty_return_inventory' ) . '">' . __( 'Return Stock', 'woocommerce-warranty' ) . '</a>';
			}
		}

		if ( 'completed' === $status ) {
			$refunded        = get_post_meta( $item['ID'], '_refunded', true );
			$amount_refunded = get_post_meta( $item['ID'], '_refund_amount', true );

			if ( ! $amount_refunded ) {
				$amount_refunded = 0;
			}

			if ( 'yes' === $refunded ) {
				$request_type = 'refund';
			}

			if ( 'refund' === $request_type ) {
				$actions['item-refund'] = '<a class="thickbox" title="' . __( 'Refund', 'woocommerce-warranty' ) . '" href="#TB_inline?width=400&height=250&inlineId=warranty-refund-modal-' . $item['ID'] . '">' . __( 'Refund Item', 'woocommerce-warranty' ) . '</a>';
			} elseif ( 'coupon' === $request_type ) {
				$actions['item-coupon'] = '<a class="thickbox" title="' . __( 'Send Coupon', 'woocommerce-warranty' ) . '" href="#TB_inline?width=400&height=250&inlineId=warranty-coupon-modal-' . $item['ID'] . '">' . __( 'Send Coupon', 'woocommerce-warranty' ) . '</a>';
			}
		}

		$actions['trash'] = '<a href="' . wp_nonce_url( 'admin-post.php?action=warranty_delete&id=' . $item['ID'], 'warranty_delete' ) . '" class="submitdelete warranty-delete">' . __( 'Delete', 'woocommerce-warranty' ) . '</a>';
		// translators: %1$s: Item code, %2$s: Customer, %3$s: Order.
		$content = sprintf( __( '<strong>%1$s</strong> by %2$s on %3$s', 'woocommerce-warranty' ), $item['code'], $customer, $order_str );

		$content = sprintf( '%1$s %2$s', $content, $this->row_actions( $actions ) );

		return $content;
	}

	/**
	 * Value of column date.
	 *
	 * @param array $item Column item.
	 */
	public function column_date( $item ) {
		return $item['post_modified'];
	}

	/**
	 * Text when no items.
	 */
	public function no_items() {
		esc_html_e( 'No requests found.', 'woocommerce-warranty' );
	}

	/**
	 * Display table.
	 */
	public function display() {
		parent::display();

		$update_nonce = wp_create_nonce( 'warranty_update' );

		echo '<form method="get"><table style="display: none"><tbody id="inlineedit">';
		foreach ( $this->items as $request ) {
			$request_id = $request['ID'];
			include WooCommerce_Warranty::$base_path . 'templates/list-item-details.php';
		}
		echo '</tbody></table></form>';

		foreach ( $this->items as $request ) {
			$item_amount = warranty_get_request_item_amount( $request['ID'] );
			$refunded    = (float) get_post_meta( $request['ID'], '_refund_amount', true );
			$available   = max( 0, $item_amount - $refunded );
			$notes       = array();

			include WooCommerce_Warranty::$base_path . 'templates/list-item-refunds.php';
		}
	}
}
