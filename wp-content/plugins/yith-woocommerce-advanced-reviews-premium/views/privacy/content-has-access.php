<?php
/**
 * Privacy Policy - Access
 *
 * @package YITH\AdvancedReviews\Views\Privacy
 */

?>
<p>
	<?php echo esc_html_x( 'Members of our team have access to the information you provide to us. For example, both Administrators and Shop Managers can access to:', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	&bull; <?php echo esc_html_x( 'your name', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	&bull; <?php echo esc_html_x( 'your email', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	&bull; <?php echo esc_html_x( 'your IP', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
	<?php if ( yith_ywar_is_module_active( 'request-review' ) ) : ?>
		&bull; <?php echo esc_html_x( 'the list of scheduled emails', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?><br/>
		&bull; <?php echo esc_html_x( 'the list of all email addresses that do not wish to receive review reminders', '[Admin panel] text for privacy policy', 'yith-woocommerce-advanced-reviews' ); ?>
	<?php endif; ?>
</p>
