<?php
/**
 * Handle the new review email.
 *
 * @package YITH\AdvancedReviews\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Reported_Review_Email' ) ) {
	/**
	 * YITH_YWAR_Reported_Review_Email class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Emails
	 */
	class YITH_YWAR_Reported_Review_Email extends YITH_YWAR_Email {

		/**
		 * Constructor
		 *
		 * Initialize email type and set templates paths
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->id             = 'yith-ywar-reported-review';
			$this->template_base  = YITH_YWAR_TEMPLATES_DIR;
			$this->template_html  = 'emails/reported-review.php';
			$this->template_plain = 'emails/plain/reported-review.php';

			add_action( 'yith_ywar_reported_review_email_notification', array( $this, 'trigger' ) );

			$this->placeholders = array(
				'{admin_name}' => '',
				'{review}'     => '',
				'{edit_link}'  => '',
			);

			parent::__construct();

			$this->customer_email = false;
		}

		/**
		 * Set default params.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_default_params() {
			parent::set_default_params();

			$this->description    = esc_html_x( 'Sent when a review is reported', '[Admin panel] Email description', 'yith-woocommerce-advanced-reviews' );
			$this->title          = esc_html_x( 'Reported review', '[Admin panel] Post type label and email title', 'yith-woocommerce-advanced-reviews' );
			$this->heading        = esc_html_x( 'A customer has just reported a review', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->subject        = esc_html_x( 'A customer has just reported a review', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->custom_message = esc_html_x( "Hi {admin_name}, \n\nthe following review was reported by a user for inappropriate content:\n\n{review}\n\nYou can {edit_link} this review through your admin panel.", '[Admin panel] Email default message', 'yith-woocommerce-advanced-reviews' );
		}

		/**
		 * Initialize placeholders before sending.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_placeholders_before_sending() {
			if ( $this->object ) {
				$review                             = yith_ywar_get_review( $this->object['review']['id'] );
				$dummy_content                      = $review ? array() : $this->object['review']['dummy_content'];
				$edit_url                           = $review ? admin_url( "post.php?post={$review->get_id()}&action=edit" ) : '#';
				$admin                              = get_user_by( 'email', get_option( 'admin_email' ) );
				$this->placeholders['{admin_name}'] = trim( sprintf( '%1$s %2$s', $admin->first_name, $admin->last_name ) );
				$this->placeholders['{review}']     = yith_ywar_get_review_content_email( $review, $dummy_content, $edit_url, true );
				$edit_link_text                     = esc_html_x( 'edit/delete', '[Admin panel] Email email reply link (part of the sentence "or edit/delete thir review")', 'yith-woocommerce-advanced-reviews' );
				$this->placeholders['{edit_link}']  = sprintf( '%1$s%3$s%2$s', '<a href="' . $edit_url . '" target="_blank">', '</a>', $edit_link_text );
			}
		}
	}

}

return new YITH_YWAR_Reported_Review_Email();
