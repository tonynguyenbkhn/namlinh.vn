<?php
/**
 * The template for displaying warranty options.
 *
 * @package WooCommerce_Warranty\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<?php global $wc_warranty; ?>
<div class="wrap woocommerce">

	<h2><?php esc_html_e( 'New Warranty Request', 'woocommerce-warranty' ); ?></h2>

	<div id="search_form"
		<?php
		if ( $searched || $form_view ) {
			echo 'style="display:none;"';
		}
		?>
	>
		<form action="admin.php" id="search_form" method="get">
			<h4><?php esc_html_e( 'Search for an Order', 'woocommerce-warranty' ); ?></h4>

			<input type="hidden" name="page" value="warranties-new" />

			<p>
				<select name="search_key" id="search_key">
					<option value="order_id"><?php esc_html_e( 'Order Number', 'woocommerce-warranty' ); ?></option>
					<option value="customer"><?php esc_html_e( 'Customer Name or Email', 'woocommerce-warranty' ); ?></option>
				</select>

				<input type="text" name="search_term" id="search_term" value="" class="short" />
				<?php wp_nonce_field( 'wc_warranty_new_search' ); ?>
				<select id="search_users"
						class="wc-user-search"
						name="search_term"
						multiple="multiple"
						placeholder="<?php esc_attr_e( 'Search for a customer&hellip;', 'woocommerce-warranty' ); ?>"
						style="width: 400px;"> </select>

				<input type="submit"
						id="order_search_button"
						class="button-primary"
						value="<?php esc_attr_e( 'Search', 'woocommerce-warranty' ); ?>" />
			</p>
		</form>
	</div>
	<?php if ( $searched || $form_view ) : ?>
		<p><input type="button" class="toggle_search_form button" value="Show Search Form" /></p>
	<?php endif; ?>

	<?php if ( $searched && empty( $orders ) ) : ?>
		<div class="error"><p><?php esc_html_e( 'No orders found', 'woocommerce-warranty' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! empty( $orders ) ) : ?>
		<table class="wp-list-table widefat fixed warranty" cellspacing="0">
			<thead>
				<tr>
					<th scope="col"
						id="order_id"
						class="manage-column column-order_id"><?php esc_html_e( 'Order ID', 'woocommerce-warranty' ); ?></th>
					<th scope="col"
						id="order_customer"
						class="manage-column column-order_customer"><?php esc_html_e( 'Customer', 'woocommerce-warranty' ); ?></th>
					<th scope="col"
						id="order_status"
						class="manage-column column-status"><?php esc_html_e( 'Order Status', 'woocommerce-warranty' ); ?></th>
					<th scope="col"
						id="order_items"
						class="manage-column column-order_items"><?php esc_html_e( 'Order Items', 'woocommerce-warranty' ); ?></th>
					<th scope="col"
						id="order_date"
						class="manage-column column-order_items"><?php esc_html_e( 'Date', 'woocommerce-warranty' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $orders as $order_id ) :
					$this_order = wc_get_order( $order_id );

					if ( ! $this_order ) {
						continue;
					}

					$has_warranty = Warranty_Order::order_has_warranty( $this_order );

					?>
					<tr class="alternate">
						<td class="order_id column-order_id">
							<a href="<?php echo esc_url( 'post.php?post=' . esc_attr( $this_order->get_id() ) . '&action=edit' ); ?>"><?php echo esc_html( $this_order->get_order_number() ); ?></a>
						</td>
						<td class="order_id column-order_customer"><?php echo esc_html( $this_order->get_billing_first_name() . ' ' . $this_order->get_billing_last_name() ); ?></td>
						<td class="order_status column-status"><?php echo esc_html( $this_order->get_status() ); ?></td>
						<td class="order_items column-order_items">
							<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
								<ul class="order-items">
									<?php
									$can_create_request = false;

									foreach ( $this_order->get_items() as $item_idx => $item ) :
										$item_id = ( isset( $item['product_id'] ) ) ? $item['product_id'] : $item['id'];

										// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
										if ( ! current_user_can( 'manage_woocommerce' ) && class_exists( 'WC_Product_Vendors_Utils' ) && WC_Product_Vendors_Utils::is_vendor() && ! WC_Product_Vendors_Utils::can_logged_in_user_manage_order_item( $item_idx ) ) {
											continue;
										}

										// variation support.
										if ( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) {
											$item_id = $item['variation_id'];
										}

										if ( $has_warranty && $item['qty'] > 1 ) {
											$max = warranty_get_quantity_remaining( $this_order->get_id(), $item_id, $item_idx );
										} else {
											$max = $item['qty'] - warranty_count_quantity_used( $this_order->get_id(), $item_id, $item_idx );
										}

										if ( $max < 1 ) {
											continue;
										}

										$can_create_request = true;
										?>
										<li>
											<input type="checkbox"
													name="idx[]"
													value="<?php echo esc_attr( $item_idx ); ?>" />
											<?php echo esc_html( $item['name'] ); ?>
											<?php if ( isset( $item['Warranty'] ) ) : ?>
												<span class="description">(<?php esc_html_e( 'Warranty', 'wc-warranty' ); ?>: <?php echo esc_html( $item['Warranty'] ); ?>)</span>
											<?php endif; ?>
											&times;
											<?php echo esc_html( $item['qty'] ); ?>
										</li>
									<?php endforeach; ?>
								</ul>
								<input type="hidden" name="page" value="warranties-new" />
								<?php wp_nonce_field( 'warranty_create_request' ); ?>
								<input type="hidden"
										name="order_id"
										value="<?php echo esc_attr( $this_order->get_id() ); ?>" />
								<input type="submit" <?php echo ( ! $can_create_request ) ? 'disabled' : ''; ?>
										class="button"
										value="<?php esc_attr_e( 'Create Request', 'woocommerce-warranty' ); ?>" />
							</form>
						</td>
						<td class="order_id column-order_date"><?php echo esc_html( $this_order->get_date_created()->date( 'Y-m-d H:i:s' ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php
	if ( isset( $_GET['order_id'], $_GET['idx'] ) ) :

		if ( ! check_admin_referer( 'warranty_create_request' ) ) {
			wp_die( esc_html__( 'You have taken too long. Please go back and retry.', 'woocommerce-warranty' ), esc_html__( 'Error', 'woocommerce-warranty' ), array( 'response' => 403 ) );
		}
		if ( isset( $_GET['error'] ) ) {
			echo '<div class="error"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['error'] ) ) ) . '</p></div>';
		}

		$this_order   = wc_get_order( sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) );
		$has_warranty = Warranty_Order::order_has_warranty( $this_order );
		$items        = $this_order->get_items();

		include WooCommerce_Warranty::$base_path . '/templates/admin/new-warranty-form.php';
	endif;
	?>
</div>
