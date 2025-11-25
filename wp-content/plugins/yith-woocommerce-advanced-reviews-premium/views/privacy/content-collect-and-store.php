<?php
/**
 * Privacy Policy - Collect and Store
 *
 * @package YITH\AdvancedReviews\Views\Privacy
 */

?>
<p>
	<?php echo esc_html_x( 'Information about you is collected during the process of creating a product comment or review. We will track:', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	&bull; <?php echo esc_html_x( 'your name', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	&bull; <?php echo esc_html_x( 'your email', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	&bull; <?php echo esc_html_x( 'your IP', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	<?php echo esc_html_x( "we'll use them to create reviews of products", '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?>
</p>
<?php if ( yith_ywar_is_module_active( 'request-review' ) && 'yes' === yith_ywar_get_option( 'ywar_refuse_requests' ) ) : ?>
	<p>
		<?php echo esc_html_x( "During checkout, you can choose to receive a reminder to review the product(s) you've purchased.", '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
		<?php echo esc_html_x( 'If you agree, an email will be scheduled and sent in the following days after the order is completed.', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
		<?php echo esc_html_x( 'If you decline, the email address provided during checkout, and your ID (if you are registered on the site), will be added to a special table in the database to prevent you from receiving any reminders.', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?>
	</p>
<?php endif; ?>
