<?php
/**
 * Warranty Product Editor class.
 *
 * @package WooCommerce_Warranty
 */

namespace WooCommerce\Warranty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\BlockTemplates\BlockInterface;
use Automattic\WooCommerce\Admin\Features\ProductBlockEditor\BlockRegistry;
use Automattic\WooCommerce\Admin\Features\ProductBlockEditor\ProductTemplates\GroupInterface;
use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * Product_Editor class.
 */
class Product_Editor {

	/**
	 * __construct function.
	 */
	public function __construct() {
		// Declaring compatibility with the product editor.
		add_action( 'before_woocommerce_init', array( $this, 'declare_product_editor_compatibility' ) );
		add_filter( 'woocommerce_rest_api_get_rest_namespaces', array( $this, 'add_rest_controller' ) );
		add_action( 'init', array( $this, 'warranty_extension_block_init' ) );

		add_action( 'woocommerce_block_template_area_product-form_after_add_block_shipping', array( $this, 'add_warranty_tab' ) );
		add_action( 'woocommerce_block_template_area_product-form_after_add_block_warranty-group', array( $this, 'add_warranty_fields' ) );
	}

	/**
	 * Declare Product Editor compatibility.
	 */
	public function declare_product_editor_compatibility() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'product_block_editor', 'woocommerce-warranty/woocommerce-warranty.php' );
		}
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 *
	 * @return void
	 */
	public function warranty_extension_block_init() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended --- No need to use nonce as no DB operation required.
		if ( isset( $_GET['page'] ) && 'wc-admin' === $_GET['page'] ) {
			BlockRegistry::get_instance()->register_block_type_from_metadata( WOOCOMMERCE_WARRANTY_ABSPATH . '/dist/product-editor' );
		}
	}

	/**
	 * Add warranty group tab to the product editor.
	 *
	 * @param BlockInterface $group The targeted group.
	 */
	public function add_warranty_tab( BlockInterface $group ) {
		$parent = $group->get_parent();

		// Warranty tab.
		$parent->add_group(
			array(
				'id'         => 'warranty-group',
				'order'      => 80,
				'attributes' => array(
					'title' => __( 'Warranty', 'woocommerce-warranty' ),
				),
			)
		);
	}

	/**
	 * Add warranty fields to the warranty group tab in the product editor.
	 *
	 * @param GroupInterface $warranty_group Warranty group.
	 */
	public function add_warranty_fields( GroupInterface $warranty_group ) {
		// Add warranty section.
		$warranty_section = $warranty_group->add_section(
			array(
				'id'         => 'warranty-section',
				'order'      => 10,
				'attributes' => array(
					'title'       => __( 'Warranty Settings', 'woocommerce-warranty' ),
					'description' => __( 'Configure warranty options and details for this product.', 'woocommerce-warranty' ),
				),
			)
		);

		// Add warranty block to section.
		$warranty_section->add_block(
			array(
				'id'         => 'product-warranty',
				'blockName'  => 'extension/woocommerce-warranty',
				'order'      => 10,
			)
		);
	}

	/**
	 * Add plugin custom WC REST API endpoint.
	 *
	 * @param array $controllers List of WooCommerce controllers.
	 *
	 * @return array Modified list of WooCommerce controllers.
	 */
	public function add_rest_controller( array $controllers ): array {
		$controllers['wc/v3']['product_warranty'] = 'WooCommerce\Warranty\REST_Controller';

		return $controllers;
	}
}
