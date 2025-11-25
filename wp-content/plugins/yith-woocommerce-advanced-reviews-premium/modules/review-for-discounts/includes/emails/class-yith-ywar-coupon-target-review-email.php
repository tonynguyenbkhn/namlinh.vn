<?php
/**
 * Handle the coupon email.
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Coupon_Target_Review_Email' ) ) {
	/**
	 * YITH_YWAR_Coupon_Target_Review_Email class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Emails
	 */
	class YITH_YWAR_Coupon_Target_Review_Email extends YITH_YWAR_Email {

		/**
		 * Constructor
		 *
		 * Initialize email type and set templates paths
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->id             = 'yith-ywar-coupon-target-review';
			$this->template_base  = yith_ywar_get_module_path( 'review-for-discounts', 'templates/' );
			$this->template_html  = 'emails/coupon-target-review.php';
			$this->template_plain = 'emails/plain/coupon-target-review.php';

			add_action( 'yith_ywar_coupon_target_review_notification', array( $this, 'trigger' ) );

			$this->placeholders = array_merge(
				array(
					'{remaining_reviews}' => '',
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

			$this->description    = esc_html_x( 'Sent when the customer is about to reach the "Multiple review" threshold', '[Admin panel] Email description', 'yith-woocommerce-advanced-reviews' );
			$this->title          = esc_html_x( 'Target to achieve', '[Admin panel] Email title', 'yith-woocommerce-advanced-reviews' );
			$this->heading        = esc_html_x( 'You are about to reach an important goal on {site_title}', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->subject        = esc_html_x( 'You are about to reach an important goal!', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->custom_message = esc_html_x( "Hi {customer_name}, \n\nYou are {remaining_reviews} reviews away from getting a reward from us!\n\nSee you on our shop,\n{site_title} Staff", '[Admin panel] Email default message', 'yith-woocommerce-advanced-reviews' );
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
				$this->placeholders['{remaining_reviews}'] = $this->object['remaining_reviews'];
			}
		}
	}

}

return new YITH_YWAR_Coupon_Target_Review_Email();
