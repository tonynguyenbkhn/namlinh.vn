<?php
/**
 * Class YITH_YWAR_Request_Review_Blocks
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\WCBlocks
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;

if ( ! class_exists( 'YITH_YWAR_Request_Review_Blocks' ) ) {
	/**
	 * Class YITH_YWAR_Request_Review_Blocks
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview\WCBlocks
	 */
	class YITH_YWAR_Request_Review_Blocks {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * The constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			if ( did_action( 'woocommerce_blocks_loaded' ) ) {
				$this->add_plugin_blocks();
			} else {
				add_action( 'woocommerce_blocks_loaded', array( $this, 'add_plugin_blocks' ) );
			}
		}

		/**
		 * Add the block in WooCommerce blocks
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_plugin_blocks() {
			if ( 'yes' !== yith_ywar_get_option( 'ywar_refuse_requests' ) ) {
				return;
			}

			require_once yith_ywar_get_module_path( 'request-review', 'includes/wc-blocks/class-yith-ywar-request-review-blocks-integration.php' );

			add_action( 'woocommerce_blocks_checkout_block_registration', array( $this, 'register_checkout_blocks' ) );
			add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'manage_customer_blocklist' ), 10, 2 );

			woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => CheckoutSchema::IDENTIFIER,
					'namespace'       => 'yith-ywar',
					'data_callback'   => array( $this, 'data_callback' ),
					'schema_callback' => array( $this, 'schema_callback' ),
					'schema_type'     => ARRAY_A,
				)
			);
		}

		/**
		 * Register the block
		 *
		 * @param IntegrationRegistry $integration_registry The integration registry.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function register_checkout_blocks( IntegrationRegistry $integration_registry ) {
			$integration_registry->register( new YITH_YWAR_Request_Review_Blocks_Integration() );
		}

		/**
		 * Callback function to register endpoint data for blocks.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function data_callback(): array {
			return array(
				'ywarAcceptRequests' => '',
			);
		}

		/**
		 * Callback function to register schema for data.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function schema_callback(): array {
			return array(
				'ywarAcceptRequests' => array(
					'type'     => 'boolean',
					'readonly' => true,
				),
			);
		}

		/**
		 * Checks if the customer accepts to receive review requests
		 *
		 * @param WC_Order        $order   Order object.
		 * @param WP_REST_Request $request Full details about the request.
		 *
		 * @return  void
		 * @since   2.0.0
		 */
		public function manage_customer_blocklist( WC_Order $order, WP_REST_Request $request ) {
			if ( $order->has_status( array( 'failed', 'cancelled' ) ) ) {
				return;
			}

			$params = $request->get_params();
			if ( isset( $params['extensions'], $params['extensions']['yith-ywar'], $params['extensions']['yith-ywar']['ywarAcceptRequests'] ) && ! $params['extensions']['yith-ywar']['ywarAcceptRequests'] && ! yith_ywar_check_blocklist( wp_get_current_user()->ID, $order->get_billing_email() ) ) {
				yith_ywar_add_to_blocklist( wp_get_current_user()->ID, $order->get_billing_email() );
			}
		}
	}
}
