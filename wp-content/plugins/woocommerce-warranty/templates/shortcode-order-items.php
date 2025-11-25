<?php
/**
 * The template for displaying warranty options.
 *
 * @package WooCommerce_Warranty\Templates
 * @version 2.0.0
 *
 * @var WC_Order $order
 *
 * @see Warranty_Shortcodes::render_warranty_request_shortcode for extracted variable definitions.
 */

defined( 'ABSPATH' ) || exit;

if ( ! $order instanceof WC_Order ) {
	return;
}
?>
<p class="order-info">
	<?php
	$order_date = $order->get_date_created()->date_i18n( WooCommerce_Warranty::get_datetime_format() );
	// translators: %1$s: Order number, %2$s: Order date.
	printf( esc_html__( 'Order %1$s made on %2$s.', 'woocommerce-warranty' ), '<mark class="order-number">' . esc_html( $order->get_order_number() ) . '</mark>', '<mark class="order-date">' . esc_html( $order_date ) . '</mark>' );
	?>
</p>

<form method="get">
	<table width="100%" cellpadding="5" cellspacing="0" border="1px" class="warranty-table">
		<tr>
			<th class="check-column">&nbsp;</th>
			<th><?php esc_html_e( 'Item', 'woocommerce-warranty' ); ?></th>
			<th><?php esc_html_e( 'Price', 'woocommerce-warranty' ); ?></th>
			<th><?php esc_html_e( 'Details', 'woocommerce-warranty' ); ?></th>
		</tr>
		<?php
		$order_has_rma = false;

		/**
		 * WC Order Item.
		 *
		 * @var $items WC_Order_Item[]
		 */
		foreach ( $items as $item_idx => $item ) {
			$item_warranty = new Warranty_Item( $item_idx );
			$warranty      = warranty_get_order_item_warranty( $item );
			$item_has_rma  = false;

			if ( ! empty( $item['item_meta']['_bundled_by'] ) ) {
				continue;
			}

			$warranty_string = esc_html( warranty_get_warranty_duration_string( $warranty, $order ) );
			$item_has_rma    = $item_warranty->has_warranty();

			if ( $item_has_rma ) {
				$order_has_rma = true;
			}

			if ( $item_warranty->is_expired() ) {
				$warranty_string .= '<br/><strong>' . esc_html__( 'Expired Warranty', 'woocommerce-warranty' ) . '</strong>';
			}
			?>
			<tr>
				<td class="check-column">
					<?php if ( $item_has_rma ) : ?>
						<input type="checkbox" name="idx[]" class="chk-order-item" value="<?php echo esc_attr( $item_idx ); ?>" />
					<?php endif; ?>
				</td>
				<td>
					<?php
					// translators: %1$s: Item name, %2$d: Item quantity.
					printf( esc_html__( '%1$s x %2$d', 'woocommerce-warranty' ), esc_html( $item->get_name() ), esc_html( $item->get_quantity() ) );

					wc_display_item_meta( $item );
					?>
				</td>
				<td><?php echo wc_price( $order->get_line_total( $item, true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
				<td><?php echo $warranty_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
			</tr>
			<?php
		}
		?>
	</table>
	<input type="hidden" name="order" value="<?php echo esc_attr( $order->get_id() ); ?>" />

	<?php if ( $order_has_rma ) : ?>
		<input type="submit" class="warranty-button button" value="<?php echo esc_attr( get_option( 'warranty_button_text', __( 'Request Warranty', 'woocommerce-warranty' ) ) ); ?>" />
	<?php endif; ?>
</form>

<h2><?php esc_html_e( 'Existing Requests', 'woocommerce-warranty' ); ?></h2>
<form method="get">
	<table width="100%" cellpadding="5" cellspacing="0" border="1px" class="warranty-table">
		<tr>
			<th><?php esc_html_e( 'RMA', 'woocommerce-warranty' ); ?></th>
			<th><?php esc_html_e( 'Products', 'woocommerce-warranty' ); ?></th>
			<th><?php esc_html_e( 'Details', 'woocommerce-warranty' ); ?></th>
			<th><?php esc_html_e( 'Status', 'woocommerce-warranty' ); ?></th>
		</tr>
		<?php
		// load warranty request for this order, if any.
		$results = warranty_search( $order->get_id() );

		if ( $results ) {
			foreach ( $results as $result ) {
				$result = warranty_load( $result->ID );

				if ( ! is_array( $result ) ) {
					continue;
				}

				$warranty_string  = '';
				$warranty_actions = '';
				$warranty_details = array();
				$status_term      = wp_get_post_terms( $result['ID'], 'shop_warranty_status' );
				$status_name      = ( isset( $status_term[0] ) && $status_term[0] instanceof WP_Term ) ? $status_term[0]->name : get_term_by( 'slug', 'new', 'shop_warranty_status' )->name;

				$warranty_details[] = __( 'Updated ', 'woocommerce-warranty' ) . date_i18n( WooCommerce_Warranty::get_datetime_format(), strtotime( $result['post_modified'] ) );

				if ( 'y' === $result['request_tracking_code'] && empty( $result['tracking_code'] ) ) {
					// Ask for the shipping provider and tracking code.
					$warranty_actions .= warranty_request_shipping_tracking_code_form( $result );
				}

				if ( ! empty( $result['return_tracking_code'] ) || ! empty( $result['tracking_code'] ) ) {
					$warranty_actions .= warranty_return_shipping_tracking_code_form( $result );
				}

				$shipping_label_id = get_post_meta( $result['ID'], '_warranty_shipping_label', true );

				if ( $shipping_label_id ) {
					ob_start();
					?>
					<strong><?php esc_html_e( 'Shipping Label', 'woocommerce-warranty' ); ?></strong><br />
					<?php
					$shipping_label_file_url = wp_get_attachment_url( $shipping_label_id );
					$post_mime_type          = get_post_mime_type( $shipping_label_id );

					if ( 'application/pdf' === $post_mime_type ) {
						echo '<a href="' . esc_url( $shipping_label_file_url ) . '"><img src="' . esc_url( plugins_url( '/assets/images/pdf-icon.png', WOOCOMMERCE_WARRANTY_ABSPATH ) ) . '" />&nbsp; ' . esc_html__( 'Download', 'woocommerce-warranty' ) . '</a>';
					} else {
						echo '<a href="' . esc_url( $shipping_label_file_url ) . '">' . esc_html__( 'Download return label', 'woocommerce-warranty' ) . '</a>';
					}

					$warranty_actions .= ob_get_clean();
				}

				if ( ! empty( $result['refunded'] ) && 'yes' === $result['refunded'] ) {
					$refund_amount = get_post_meta( $result['ID'], '_refund_amount', true );
					$refund_date   = get_post_meta( $result['ID'], '_refund_date', true );

					if ( $refund_amount && $refund_date ) {
						$pretty_date = date_i18n( WooCommerce_Warranty::get_datetime_format(), strtotime( $refund_date ) );
						// translators: %1$s: Refund amount, %2$s: Refund date.
						$warranty_details[] = sprintf( esc_html__( 'Item has been refunded the amount of %1$s on %2$s', 'woocommerce-warranty' ), esc_html( wp_strip_all_tags( wc_price( floatval( $refund_amount ) ) ) ), esc_html( $pretty_date ) );
					} else {
						$warranty_details[] = __( 'Item has been refunded', 'woocommerce-warranty' );
					}
				}
				?>
				<tr>
					<td>
						<strong><?php echo esc_html( $result['code'] ); ?></strong> <br /> <small>
							<?php echo esc_html( __( 'created on ', 'woocommerce-warranty' ) . date_i18n( wc_date_format() . ' ' . wc_time_format(), strtotime( $result['post_date'] ) ) ); ?>
						</small>
					</td>
					<td>
						<?php
						foreach ( $result['products'] as $product_request ) {
							$product      = wc_get_product( $product_request['product_id'] );
							$order_item   = $order->get_item( $product_request['order_item_index'] );
							$product_name = ( $product instanceof WC_Product )
							? sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink( $product->get_id() ) ), esc_html( $product->get_name() ) )
							: ( $order_item instanceof WC_Order_Item_Product ? $order_item->get_name() : '' );

							echo sprintf( '%1$s &times %2$s <br />', $product_name, esc_html( $product_request['quantity'] ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</td>
					<td>
						<?php
						echo '<ul class="warranty-data">';

						foreach ( $warranty_details as $detail ) {
							echo '  <li>' . esc_html( $detail ) . '</li>';
						}

						echo '</ul>';

						echo $warranty_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						?>
					</td>
					<td><?php echo esc_html( $status_name ); ?></td>
				</tr>
				<?php
			}
		}
		?>
	</table>
</form>
