<?php
/**
 * Handle the Funds email.
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Coupon_Funds_Single_Review_Email' ) ) {
	/**
	 * YITH_YWAR_Coupon_Funds_Single_Review_Email class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Emails
	 */
	class YITH_YWAR_Coupon_Funds_Single_Review_Email extends YITH_YWAR_Email {

		/**
		 * Constructor
		 *
		 * Initialize email type and set templates paths
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->id             = 'yith-ywar-coupon-funds-single-review';
			$this->template_base  = yith_ywar_get_module_path( 'review-for-discounts', 'templates/' );
			$this->template_html  = 'emails/coupon-funds-single-review.php';
			$this->template_plain = 'emails/plain/coupon-funds-single-review.php';

			add_action( 'yith_ywar_coupon_funds_single_review_notification', array( $this, 'trigger' ) );

			$this->placeholders = array_merge(
				array(
					'{product_name}' => '',
					'{funds_amount}' => '',
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

			$this->description    = esc_html_x( 'Funds gifted for a single review', '[Admin panel] Email description', 'yith-woocommerce-advanced-reviews' );
			$this->title          = esc_html_x( 'Funds for a single review', '[Admin panel] Email title', 'yith-woocommerce-advanced-reviews' );
			$this->heading        = esc_html_x( 'A gift to thank you for your review!', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->subject        = esc_html_x( 'You have received funds from {site_title}', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->custom_message = esc_html_x( "Hi {customer_name}, \n\nThanks for reviewing the {product_name}!\nTo celebrate, we have credited {funds_amount} on your funds as a small gift.\n\nSee you on our shop,\n{site_title} Staff", '[Admin panel] Email default message', 'yith-woocommerce-advanced-reviews' );
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
				$this->placeholders['{product_name}'] = yith_ywar_render_mailbody_link( $this->object['product_id'], 'product' );
				$this->placeholders['{funds_amount}'] = wc_price( $this->object['funds_amount'] );
			}
		}
	}

}

return new YITH_YWAR_Coupon_Funds_Single_Review_Email();
