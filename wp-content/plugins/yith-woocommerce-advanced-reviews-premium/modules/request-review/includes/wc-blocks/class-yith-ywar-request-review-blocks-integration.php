<?php
/**
 * Class YITH_YWAR_Request_Review_Blocks_Integration
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\WCBlocks
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if ( ! class_exists( 'YITH_YWAR_Request_Review_Blocks_Integration' ) ) {
	/**
	 * Class YITH_YWAR_Request_Review_Blocks_Integration
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview\WCBlocks
	 */
	class YITH_YWAR_Request_Review_Blocks_Integration implements IntegrationInterface {

		/**
		 * The name of the integration.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_name(): string {
			return 'request-review-block';
		}

		/**
		 * When called invokes any initialization/setup for the integration.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function initialize() {
			$this->register_block_frontend_scripts();
			$this->register_block_editor_scripts();
		}

		/**
		 * Returns an array of script handles to enqueue in the frontend context.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_script_handles(): array {
			return array( 'yith-ywar-request-review-frontend' );
		}

		/**
		 * Returns an array of script handles to enqueue in the editor context.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_editor_script_handles(): array {
			return array( 'yith-ywar-request-review-editor' );
		}

		/**
		 * An array of key, value pairs of data made available to the block on the client side.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_script_data(): array {
			return array();
		}

		/**
		 * Register scripts for delivery date block editor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function register_block_editor_scripts() {
			$script_url   = YITH_YWAR_ASSETS_URL . '/js/wc-blocks/request-review/index.js';
			$script_asset = require_once YITH_YWAR_DIR . 'assets/js/wc-blocks/request-review/index.asset.php';

			wp_register_script(
				'yith-ywar-request-review-editor',
				$script_url,
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);

			wp_localize_script(
				'yith-ywar-request-review-editor',
				'ywar_blocks',
				array(
					'user_in_blocklist'       => yith_ywar_check_blocklist( get_current_user_id(), '' ),
					/**
					 * APPLY_FILTERS: yith_ywar_checkout_option_label
					 *
					 * Checkout label text.
					 *
					 * @param string $value The checkout label.
					 *
					 * @return string
					 */
					'request_review_checkout' => array( apply_filters( 'yith_ywar_checkout_option_label', yith_ywar_get_option( 'ywar_refuse_requests_label' ) ) ),
				)
			);
		}

		/**
		 * Register scripts for frontend block.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function register_block_frontend_scripts() {
			$script_url   = YITH_YWAR_ASSETS_URL . '/js/wc-blocks/request-review/request-review-frontend.js';
			$script_asset = require_once YITH_YWAR_DIR . 'assets/js/wc-blocks/request-review/request-review-frontend.asset.php';

			wp_register_script(
				'yith-ywar-request-review-frontend',
				$script_url,
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);

			wp_localize_script(
				'yith-ywar-request-review-frontend',
				'ywar_blocks',
				array(
					'user_in_blocklist'       => yith_ywar_check_blocklist( get_current_user_id(), '' ),
					/**
					 * APPLY_FILTERS: yith_ywar_checkout_option_label
					 *
					 * Checkout label text.
					 *
					 * @param string $value The checkout label.
					 *
					 * @return string
					 */
					'request_review_checkout' => apply_filters( 'yith_ywar_checkout_option_label', yith_ywar_get_option( 'ywar_refuse_requests_label' ) ),
				)
			);
		}
	}
}
