<?php
/**
 * Handle the coupon email.
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Coupon_Single_Review_Email' ) ) {
	/**
	 * YITH_YWAR_Coupon_Single_Review_Email class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Emails
	 */
	class YITH_YWAR_Coupon_Single_Review_Email extends YITH_YWAR_Email {

		/**
		 * Constructor
		 *
		 * Initialize email type and set templates paths
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->id             = 'yith-ywar-coupon-single-review';
			$this->template_base  = yith_ywar_get_module_path( 'review-for-discounts', 'templates/' );
			$this->template_html  = 'emails/coupon-single-review.php';
			$this->template_plain = 'emails/plain/coupon-single-review.php';

			add_action( 'yith_ywar_coupon_single_review_notification', array( $this, 'trigger' ) );

			$this->placeholders = array_merge(
				array(
					'{product_name}'       => '',
					'{coupon_description}' => '',
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

			$this->description    = esc_html_x( 'Coupon sent for a single review', '[Admin panel] Email description', 'yith-woocommerce-advanced-reviews' );
			$this->title          = esc_html_x( 'Reward for a single review', '[Admin panel] Email title', 'yith-woocommerce-advanced-reviews' );
			$this->heading        = esc_html_x( 'A coupon to thank you for your review!', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->subject        = esc_html_x( 'You have received a coupon from {site_title}', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->custom_message = esc_html_x( "Hi {customer_name}, \n\nThanks for reviewing the {product_name}!\nTo celebrate, we would like to offer you this coupon as a small gift:\n\n{coupon_description}\n\nSee you on our shop,\n{site_title} Staff", '[Admin panel] Email default message', 'yith-woocommerce-advanced-reviews' );
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
				$this->placeholders['{product_name}']       = yith_ywar_render_mailbody_link( $this->object['product_id'], 'product' );
				$this->placeholders['{coupon_description}'] = yith_ywar_get_coupon_description( $this->object['coupon_code'] );
			}
		}
	}

}

return new YITH_YWAR_Coupon_Single_Review_Email();
