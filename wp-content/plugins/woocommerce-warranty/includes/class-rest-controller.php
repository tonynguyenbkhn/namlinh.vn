<?php
/**
 * Warranty Custom REST class.
 *
 * @package WooCommerce_Warranty
 */

namespace WooCommerce\Warranty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Warranty Custom REST class.
 */
class REST_Controller {
	/**
	 * WooCommerce REST API namespace.
	 *
	 * @var string
	 */
	protected string $namespace = 'wc/v3';

	/**
	 * This plugin REST base.
	 *
	 * @var string
	 */
	protected string $rest_base = 'default_warranty/(?P<product_id>\d+)';

	/**
	 * Registering the custom routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_default_warranty' ),
				'permission_callback' => array( $this, 'get_warranty_permissions_check' ),
			)
		);
	}

	/**
	 * Retrieve triggers for a specific product.
	 *
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return array
	 */
	public function get_default_warranty( \WP_REST_Request $request ): array {
		$warranty = warranty_get_product_default_warranty( (int) $request['product_id'] );

		if ( isset( $warranty['default'] ) ) {
			unset( $warranty['default'] );
		}

		if ( isset( $warranty['label'] ) ) {
			unset( $warranty['label'] );
		}

		return $warranty;
	}

	/**
	 * Makes sure the current user has access to WRITE the settings APIs.
	 *
	 * @return \WP_Error|bool
	 */
	public function get_warranty_permissions_check() {
		// phpcs:ignore --- WooCommerce custom capability.
		if ( ! current_user_can( 'edit_products' ) ) {
			return new \WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
