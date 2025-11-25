<?php
/**
 * Customer review request - plain.
 *
 * @var string   $email_heading  The heading.
 * @var WC_Email $email          The email.
 * @var bool     $sent_to_admin  Is this sent to admin?
 * @var bool     $plain_text     Is this plain?
 * @var string   $custom_message The email message.
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Templates\Emails\Plain
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( wp_strip_all_tags( wptexturize( $custom_message ) ) ) . "\n\n";

echo esc_html( wp_strip_all_tags( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) );
