<?php
/**
 * Handle the new review email.
 *
 * @package YITH\AdvancedReviews\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_New_Review_Email' ) ) {
	/**
	 * YITH_YWAR_New_Review_Email class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Emails
	 */
	class YITH_YWAR_New_Review_Email extends YITH_YWAR_Email {

		/**
		 * Constructor
		 *
		 * Initialize email type and set templates paths
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->id             = 'yith-ywar-new-review';
			$this->template_base  = YITH_YWAR_TEMPLATES_DIR;
			$this->template_html  = 'emails/new-review.php';
			$this->template_plain = 'emails/plain/new-review.php';

			add_action( 'yith_ywar_new_review_email_notification', array( $this, 'trigger' ) );

			$this->placeholders = array(
				'{admin_name}' => '',
				'{user_info}'  => '',
				'{review}'     => '',
				'{reply_link}' => '',
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

			$this->description    = esc_html_x( 'Sent when a new review is placed', '[Admin panel] Email description', 'yith-woocommerce-advanced-reviews' );
			$this->title          = esc_html_x( 'New review', '[Admin panel] Post type label and email title', 'yith-woocommerce-advanced-reviews' );
			$this->heading        = esc_html_x( 'A customer has just posted a review', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->subject        = esc_html_x( 'A customer has just posted a review', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->custom_message = esc_html_x( "Hi {admin_name}, \n\nUser {user_info} has written a review on the following product:\n\n{review}\n\nYou can {reply_link} in the product page or {edit_link} this review through your admin panel.", '[Admin panel] Email default message', 'yith-woocommerce-advanced-reviews' );
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
				$product_id                         = $review ? $review->get_product_id() : $dummy_content['product_id'];
				$edit_url                           = $review ? admin_url( "post.php?post={$review->get_id()}&action=edit" ) : '#';
				$admin                              = get_user_by( 'email', get_option( 'admin_email' ) );
				$this->placeholders['{admin_name}'] = trim( sprintf( '%1$s %2$s', $admin->first_name, $admin->last_name ) );
				$this->placeholders['{user_info}']  = sprintf( '<b>%1$s</b> (<a href="mailto:%2$s">%2$s</a>)', $this->object['reviewer_info']['name'], $this->object['reviewer_info']['email'] );
				$this->placeholders['{review}']     = yith_ywar_get_review_content_email( $review, $dummy_content, $edit_url );
				$product                            = wc_get_product( $product_id );
				$reply_link_text                    = esc_html_x( 'reply', '[Admin panel] Email email reply link (part of the sentence "You can reply in the product page")', 'yith-woocommerce-advanced-reviews' );
				$edit_link_text                     = esc_html_x( 'edit/delete', '[Admin panel] Email email reply link (part of the sentence "or edit/delete thir review")', 'yith-woocommerce-advanced-reviews' );
				$this->placeholders['{reply_link}'] = sprintf( '%1$s%3$s%2$s', '<a href="' . $product->get_permalink() . '#review-' . esc_attr( $this->object['review']['id'] ) . '" target="_blank">', '</a>', $reply_link_text );
				$this->placeholders['{edit_link}']  = sprintf( '%1$s%3$s%2$s', '<a href="' . $edit_url . '" target="_blank">', '</a>', $edit_link_text );
			}
		}
	}

}

return new YITH_YWAR_New_Review_Email();
