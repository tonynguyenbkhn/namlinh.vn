<?php
/**
 * Review reply - HTML.
 *
 * @var string   $email_heading  The heading.
 * @var WC_Email $email          The email.
 * @var bool     $sent_to_admin  Is this sent to admin?
 * @var bool     $plain_text     Is this plain?
 * @var string   $custom_message The email message.
 * @var string   $mail_style     The email style.
 *
 * @package YITH\AdvancedReviews\Templates\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email, $mail_style ); ?>

<?php echo wp_kses_post( wpautop( wptexturize( $custom_message ) ) ); ?>

<?php
do_action( 'woocommerce_email_footer', $email, $mail_style );
