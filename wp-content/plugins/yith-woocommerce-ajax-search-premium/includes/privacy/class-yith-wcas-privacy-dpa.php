<?php
/**
 * Implements Privacy DPA of YITH WooCommerce Ajax Search
 *
 * @class   YITH_WCAS_Privacy_DPA
 * @package YITH WooCommerce Ajax Search
 * @since   2.0.0
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_WCAS_Privacy_DPA' ) && class_exists( 'YITH_Privacy_Plugin_Abstract' ) ) {
	/**
	 * Class YITH_WCAS_Privacy_DPA
	 * Privacy Class.
	 */
	class YITH_WCAS_Privacy_DPA extends YITH_Privacy_Plugin_Abstract {
		use YITH_WCAS_Trait_Singleton;

		/**
		 * YITH_WCAS_Privacy_DPA constructor.
		 */
		private function __construct() {
			parent::__construct( 'YITH WooCommerce Ajax Search Premium' );
		}

		/**
		 * Return the message
		 *
		 * @param string $section Section.
		 *
		 * @return string
		 */
		public function get_privacy_message( $section ) {
			$message = '';

			switch ( $section ) {
				case 'collect_and_store':
					$message = '<p>' . esc_html__( 'When you type a keyword in the search form the following information will be stored:', 'yith-woocommerce-ajax-search' ) . '</p>' .
					'<ul>' .
					'<li>' . esc_html__( 'Your customer ID and the query strings you type in the search form.', 'yith-woocommerce-ajax-search' ) . '</li>' .
					'</ul>' .
					'<p>' . esc_html__( 'We\'ll use this information for purposes, such as:', 'yith-woocommerce-ajax-search' ) . '</p>' .
					'<ul>' .
					'<li>' . esc_html__( 'Tracking the query string to make search analytics and to show you the chronology of your searches.', 'yith-woocommerce-ajax-search' ) . '</li>' .
					'</ul>' .
					'<p>' . esc_html__( 'We generally store information about you for as long as we need the information for the purposes for which we collect and use it, and as long as we are not legally required to continue to keep it.', 'yith-woocommerce-ajax-search' ) . '</p>';
					break;
				default:
					break;
			}

			return apply_filters( 'ywcas_privacy_policy_content', $message, $section );

		}
	}
}
