<?php
/**
 * The template for displaying warranty options.
 *
 * @package WooCommerce_Warranty\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( 'refund' === $request['request_type'] ) : ?>
	<div id="warranty-refund-modal-<?php echo esc_attr( $request['ID'] ); ?>" style="display:none;">
		<table class="form-table">
			<tr>
				<th><span class="label"><?php esc_html_e( 'Amount refunded:', 'woocommerce-warranty' ); ?></span></th>
				<td><span class="value"><?php echo wc_price( $refunded ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></td>
			</tr>
			<tr>
				<th><span class="label"><?php esc_html_e( 'Item cost:', 'woocommerce-warranty' ); ?></span></th>
				<td><span class="value"><?php echo wc_price( $item_amount ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></td>
			</tr>
			<tr>
				<th><span class="label"><?php esc_html_e( 'Refund amount:', 'woocommerce-warranty' ); ?></span></th>
				<td>
					<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
					<input type="text" class="input-short amount" value="<?php echo esc_attr( $available ); ?>" size="5" />
				</td>
			</tr>
		</table>

		<p class="submit alignright">
			<input
				type="button"
				class="warranty-process-refund button-primary"
				value="<?php esc_attr_e( 'Process Refund', 'woocommerce-warranty' ); ?>"
				data-item-cost="<?php echo esc_attr( $item_amount ); ?>"
				data-amount-refunded="<?php echo esc_attr( $refunded ); ?>"
				data-id="<?php echo esc_attr( $request['ID'] ); ?>"
				data-security="<?php echo esc_attr( $update_nonce ); ?>"
				/>
		</p>
	</div>
	<?php elseif ( 'coupon' === $request['request_type'] ) : ?>
		<?php
		$coupon_sent = get_post_meta( $request['ID'], '_coupon_sent', true );
		$coupon_date = get_post_meta( $request['ID'], '_coupon_date', true );
		?>
	<div id="warranty-coupon-modal-<?php echo esc_attr( $request['ID'] ); ?>" style="display:none;">
		<?php if ( 'yes' === $coupon_sent ) { ?>
		<div class="warranty-coupon-notice">
			<?php
			// translators: %1$s is a date string.
			printf( esc_html__( 'Coupon has been sent on: %1$s', 'woocommerce-warranty' ), esc_html( wp_date( 'j F Y, H:i', strtotime( $coupon_date ) ) ) );
			?>
		</div>
		<?php } ?>
		<table class="form-table">
			<tr>
				<th><span class="label"><?php esc_html_e( 'Amount refunded:', 'woocommerce-warranty' ); ?></span></th>
				<td><span class="value"><?php echo wc_price( $refunded ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></td>
			</tr>
			<tr>
				<th><span class="label"><?php esc_html_e( 'Item cost:', 'woocommerce-warranty' ); ?></span></th>
				<td><span class="value"><?php echo wc_price( $item_amount ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></td>
			</tr>
			<tr>
				<th><span class="label"><?php esc_html_e( 'Coupon amount:', 'woocommerce-warranty' ); ?></span></th>
				<td>
					<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
					<input type="text" class="input-short amount" value="<?php echo esc_attr( $available ); ?>" size="5" />
				</td>
			</tr>
		</table>

		<p class="submit alignright">
			<?php
			$button_text = ( 'yes' === $coupon_sent ) ? __( 'Send Coupon Again', 'woocommerce-warranty' ) : __( 'Send Coupon', 'woocommerce-warranty' );
			?>
			<input
				type="button"
				class="warranty-process-coupon button-primary"
				value="<?php echo esc_attr( $button_text ); ?>"
				data-item-cost="<?php echo esc_attr( $item_amount ); ?>"
				data-amount-refunded="<?php echo esc_attr( $refunded ); ?>"
				data-id="<?php echo esc_attr( $request['ID'] ); ?>"
				data-security="<?php echo esc_attr( wp_create_nonce( 'warranty_send_coupon' ) ); ?>"
				/>
		</p>
	</div>
<?php endif; ?>
<?php
