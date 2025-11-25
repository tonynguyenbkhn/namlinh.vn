<?php
/**
 * Handle the review equest email.
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Request_Review_Email' ) ) {
	/**
	 * YITH_YWAR_Request_Review_Email class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview\Emails
	 */
	class YITH_YWAR_Request_Review_Email extends YITH_YWAR_Email {

		/**
		 * The text for the unsubscribe link.
		 *
		 * @var string
		 */
		public $unsubscribe_text;

		/**
		 * The text for the product button.
		 *
		 * @var string
		 */
		public $button_text;

		/**
		 * Constructor
		 *
		 * Initialize email type and set templates paths
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			$this->id             = 'yith-ywar-request-review';
			$this->template_base  = yith_ywar_get_module_path( 'request-review', 'templates/' );
			$this->template_html  = 'emails/request-review.php';
			$this->template_plain = 'emails/plain/request-review.php';

			add_action( 'yith_ywar_request_review_notification', array( $this, 'trigger' ) );

			$this->placeholders = array_merge(
				array(
					'{completed_date}' => '',
					'{items}'          => '',
					'{days_ago},'      => '',
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

			/* translators: %s number of days */
			$this->description      = sprintf( esc_html_x( 'Sent %s day(s) after the order has been set as "completed".', '[Admin panel] Email description', 'yith-woocommerce-advanced-reviews' ), yith_ywar_get_option( 'ywar_mail_schedule_day' ) );
			$this->title            = esc_html_x( 'Review reminder', '[Admin panel] Order/booking page column name and option name', 'yith-woocommerce-advanced-reviews' );
			$this->heading          = esc_html_x( "We'd like to hear your feedback!", '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->subject          = esc_html_x( 'Review recently purchased products on {site_title}', '[Admin panel] Email heading/subject', 'yith-woocommerce-advanced-reviews' );
			$this->custom_message   = esc_html_x( "Hi {customer_name},\n\nThank you so much for your recent order on {site_title} shop!\n\nIf you have a few minutes to leave us a review on the following items, we'd really appreciate it:\n\n{items}\n\nSee you on our shop,\n{site_title} Staff", '[Admin panel] Email default message', 'yith-woocommerce-advanced-reviews' );
			$this->unsubscribe_text = esc_html_x( 'Unsubscribe from review reminders.', '[Admin panel] Email unsubscibe link text', 'yith-woocommerce-advanced-reviews' );
			$this->button_text      = esc_html_x( 'Submit a review', '[Admin panel] Email review link text', 'yith-woocommerce-advanced-reviews' );
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
				$this->placeholders['{completed_date}'] = $this->object['completed_date'];
				$this->placeholders['{items}']          = yith_ywar_items_to_review( $this->get_button_text(), $this->object['items'], $this->object['user']['customer_id'] );
				$this->placeholders['{days_ago}']       = $this->object['days_ago'];
			}
		}

		/**
		 * Default unsubscribe message.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_default_unsubscribe_text(): string {
			return $this->unsubscribe_text;
		}

		/**
		 * Default button text.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_default_button_text(): string {
			return $this->button_text;
		}

		/**
		 * Return content from the custom_message field.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_custom_message(): string {
			$custom_message = parent::get_custom_message();

			if ( 'base' === ( isset( $this->object['style'] ) ? $this->object['style'] : $this->get_custom_option( 'email_style' ) ) ) {
				$custom_message .= '<br /><br />' . yith_ywar_set_unsubscribe_link( $this->get_unsubscribe_text(), $this );
			}

			return $this->format_string( $custom_message );
		}

		/**
		 * Return content from the unsubscribe field.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_unsubscribe_text(): string {
			$unsubscribe_text = $this->get_option( 'unsubscribe', $this->get_default_unsubscribe_text() );

			return $this->format_string( $unsubscribe_text );
		}

		/**
		 * Return content from the button_text field.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_button_text(): string {
			$button_text = $this->get_option( 'button_label', $this->get_default_button_text() );

			return $this->format_string( $button_text );
		}

		/**
		 * Get extra content params
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_extra_content_params(): array {
			$extra = array();
			if ( 'base' !== ( isset( $this->object['style'] ) ? $this->object['style'] : $this->get_custom_option( 'email_style' ) ) && ! yith_ywar_email_templates_enabled() ) {
				$extra = array(
					'footer_link' => yith_ywar_set_unsubscribe_link( $this->get_unsubscribe_text(), $this ),
				);
			}

			return $extra;
		}
	}

}

return new YITH_YWAR_Request_Review_Email();
