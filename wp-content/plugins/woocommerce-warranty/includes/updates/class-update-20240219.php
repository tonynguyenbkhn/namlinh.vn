<?php
/**
 * Update Data to 20240219
 * Purpose of this update:
 *  - Add order_id and rma code to post_title and post_parent.
 *
 * @package WooCommerce_Warranty
 */

namespace WooCommerce\Warranty;

use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Class Update_20240219
 *
 * Purpose of this update:
 * 1. Add order_id and rma code to post_title and post_parent.
 *
 * @package WooCommerce\Warranty
 */
class Update_20240219 {

	/**
	 * Database version.
	 *
	 * @var string
	 */
	private static $db_version = '20240219';

	/**
	 * List of warranty requests.
	 *
	 * @var array
	 */
	private $warranty_request_ids = array();

	/**
	 * Initialize update.
	 */
	public function __construct() {
		$this->set_warranty_request_ids();
		$this->run_updates();
		$this->complete_updates();
	}

	/**
	 * Set warranty request ids.
	 *
	 * @return void
	 */
	private function set_warranty_request_ids() {
		$this->warranty_request_ids = get_posts(
			array(
				'post_type'      => 'warranty_request',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			)
		);
	}

	/**
	 * Run all the necessary updates.
	 *
	 * @return void
	 */
	private function run_updates() {
		if ( empty( $this->warranty_request_ids ) ) {
			return;
		}

		$this->maybe_set_post_parent();
	}

	/**
	 * Add order_id and rma code to post_title and post_parent.
	 *
	 * @return void
	 */
	private function maybe_set_post_parent() {

		foreach ( $this->warranty_request_ids as $warranty_request_id ) {
			$warranty = get_post( $warranty_request_id );
			if ( ! $warranty instanceof WP_Post || ( ! empty( $warranty->post_parent ) && ! empty( $warranty->post_title ) ) ) {
				continue;
			}

			$order_id = get_post_meta( $warranty_request_id, '_order_id', true );
			$code     = get_post_meta( $warranty_request_id, '_code', true );

			if ( empty( $order_id ) || ! is_numeric( $order_id ) ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'          => $warranty_request_id,
					'post_title'  => '#' . $order_id . ' ' . $code,
					'post_parent' => absint( $order_id ),
				)
			);
		}
	}

	/**
	 * Set various flags so the plugin doesn't try to
	 * run updates after this is done. Redirects go here
	 * as well.
	 *
	 * @return void
	 */
	private function complete_updates() {
		delete_option( 'warranty_needs_update' );
		update_option( 'warranty_db_version', self::$db_version );
		wp_safe_redirect( wp_nonce_url( admin_url( 'admin.php?page=warranties&warranty-data-updated=true' ), 'wc_warranty_updater' ) );
		exit;
	}
}

new Update_20240219();
