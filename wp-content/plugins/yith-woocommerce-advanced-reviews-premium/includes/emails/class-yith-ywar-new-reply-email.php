<?php
/**
 * Handle the new review email.
 *
 * @package YITH\AdvancedReviews\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_New_Reply_Email' ) ) {
	/**
	 * YITH_YWAR_New_Reply_Email class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Emails
	 */
	class YITH_YWAR_New_Reply_Email extends YITH_YWAR_Email {

		/**
		 * Constructor
		 *
		 * Initialize email type and set templates paths
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->id             = 'yith-ywar-new-reply';
			$this->template_base  = YITH_YWAR_TEMPLATES_DIR;
			$this->template_html  = 'emails/new-reply.php';
			$this->template_plain = 'emails/plain/new-reply.php';

			add_action( 'yith_ywar_new_reply_email_notification', array( $this, 'trigger' ) );

			$this->placeholders = array_merge(
				array(
					'{user_info}'        => '',
					'{reviewed_product}' => '',
					'{reply}'            => '',
					'{reply_link}'       => '',
				),
				$this->placeholders
			);

			parent::__construct();
		}

		/**
		 * Set default params.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_default_params() {
			parent::set_default_params();

			$this->description    = esc_html_x( 'Sent when a new reply is placed', '[Admin panel] Email description', 'yith-woocommerce-advanced-reviews' );
			$this->title          = esc_html_x( 'New reply', '[Admin panel] Email title', 'yith-woocommerce-advanced-reviews' );
			$this->heading        = esc_html_x( 'Someone replied to your review!', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->subject        = esc_html_x( 'Someone replied to your review!', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->custom_message = esc_html_x( "Hi {customer_name}, \n\nUser {user_info} has just replied to your review on {reviewed_product}\n\n{reply}\n\nYou can {reply_link} in the product page.", '[Admin panel] Email default message', 'yith-woocommerce-advanced-reviews' );
		}

		/**
		 * Initialize placeholders before sending.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_placeholders_before_sending() {
			parent::init_placeholders_before_sending();
			if ( $this->object ) {
				$review        = yith_ywar_get_review( $this->object['review']['id'] );
				$dummy_content = $review ? array() : $this->object['review']['dummy_content'];
				$product_id    = $review ? $review->get_product_id() : $dummy_content['product_id'];
				if ( ! $review ) {
					$dummy_content['is_reply'] = 'yes';
				}
				$this->placeholders['{user_info}']        = sprintf( '<b>%1$s</b> (<a href="mailto:%2$s">%2$s</a>)', $this->object['reviewer_info']['name'], $this->object['reviewer_info']['email'] );
				$this->placeholders['{reviewed_product}'] = yith_ywar_render_mailbody_link( $product_id, 'product' );
				$this->placeholders['{reply}']            = yith_ywar_get_review_content_email( $review, $dummy_content, '' );
				$product                                  = wc_get_product( $product_id );
				$reply_link_text                          = esc_html_x( 'read this reply', '[Admin panel] Email email reply link (part of the sentence "You can read this reply")', 'yith-woocommerce-advanced-reviews' );
				$this->placeholders['{reply_link}']       = sprintf( '%1$s%3$s%2$s', '<a href="' . $product->get_permalink() . '#review-' . esc_attr( $this->object['review']['id'] ) . '" target="_blank">', '</a>', $reply_link_text );
			}
		}
	}

}

return new YITH_YWAR_New_Reply_Email();
