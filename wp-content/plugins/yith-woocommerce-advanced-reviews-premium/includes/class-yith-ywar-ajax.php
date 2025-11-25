<?php
/**
 * Class YITH_YWAR_AJAX
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_AJAX' ) ) {
	/**
	 * Class YITH_YWAR_AJAX
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_AJAX {

		use YITH_YWAR_Trait_Singleton;

		const ADMIN_AJAX_ACTION = 'yith_ywar_admin_ajax_action';

		const FRONTEND_AJAX_ACTION = 'yith_ywar_frontend_ajax_action';

		/**
		 * YITH_YWAR_AJAX constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			add_action( 'wp_ajax_' . self::ADMIN_AJAX_ACTION, array( $this, 'handle_admin_ajax_action' ) );
			add_action( 'wp_ajax_' . self::FRONTEND_AJAX_ACTION, array( $this, 'handle_frontend_ajax_action' ) );
			add_action( 'wp_ajax_nopriv_' . self::FRONTEND_AJAX_ACTION, array( $this, 'handle_frontend_ajax_action' ) );
		}

		/**
		 * Handle generic admin Ajax action.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function handle_admin_ajax_action() {
			check_ajax_referer( self::ADMIN_AJAX_ACTION, 'security' );

			$request = sanitize_title( wp_unslash( $_REQUEST['request'] ?? '' ) );
			if ( ! ! $request ) {
				$method = 'admin_ajax_' . $request;

				if ( is_callable( array( $this, $method ) ) ) {
					$result = $this->$method();
					wp_send_json_success( $result );
				}

				do_action( 'yith_ywar_admin_ajax_' . $request );
			}

			wp_send_json_error();
		}

		/**
		 * Handle generic frontend Ajax action.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function handle_frontend_ajax_action() {
			// Frontend actions don't require nonce check.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$request = sanitize_title( wp_unslash( $_REQUEST['request'] ?? '' ) );
			if ( ! ! $request ) {
				$method = 'frontend_ajax_' . $request;

				if ( is_callable( array( $this, $method ) ) ) {
					$result = $this->$method();
					wp_send_json_success( $result );
				}

				do_action( 'yith_ywar_frontend_ajax_' . $request );
			}

			wp_send_json_error();
		}
	}
}
