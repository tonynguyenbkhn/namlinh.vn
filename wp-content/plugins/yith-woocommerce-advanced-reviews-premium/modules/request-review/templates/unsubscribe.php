<?php
/**
 * Unsubscribe page template file
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Templates
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

wc_print_notices();

$getted        = $_GET; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
$decoded_email = urldecode( base64_decode( $getted['email'] ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
$decoded_id    = urldecode( base64_decode( $getted['id'] ) ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

?>
<div class="form-header">		<?php
	/* translators: %s email */
	printf( esc_html_x( "If you don't want to receive any more review reminders, please re-type your email address: %s", '[Frontend] Unsubscribe page text', 'yith-woocommerce-advanced-reviews' ), '<b>' . esc_attr( $decoded_email ) . '</b>' )
	?></div>
<div class="form-content">
	<div class="form-element">
		<label for="account_email">
			<?php echo esc_html_x( 'Email address', '[Frontend] Unsubscribe page email field', 'yith-woocommerce-advanced-reviews' ); ?>:
			<input type="email" class="input-text" name="account_email" id="account_email"/>
		</label>
	</div>
</div>
<div class="form-footer">
	<input type="hidden" name="account_id" id="account_id" value="<?php echo esc_attr( $decoded_id ); ?>"/>
	<input type="hidden" name="email_hash" id="email_hash" value="<?php echo esc_attr( $getted['email'] ); ?>"/>
	<span class="submit-button yith-ywar-unsubscribe"><?php echo esc_html_x( 'Unsubscribe', '[Frontend] Unsubscribe page', 'yith-woocommerce-advanced-reviews' ); ?></span>
</div>
<p style="display: none;" class="return-to-shop form-row form-row-wide"><a class="button wc-backward" href="<?php echo esc_url( get_home_url() ); ?>"><?php echo esc_html_x( 'Back to Home Page', '[Frontend] Unsubscribe page action button', 'yith-woocommerce-advanced-reviews' ); ?></a></p>
