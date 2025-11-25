<?php
/**
 * Update Data to 20240707
 * Purpose of this update:
 *  - Delete empty coupon metadata.
 *
 * @package WooCommerce_Warranty
 */

namespace WooCommerce\Warranty;

defined( 'ABSPATH' ) || exit;

/**
 * Class Update_20240707
 *
 * Purpose of this update:
 * 1. Add order_id and rma code to post_title and post_parent.
 *
 * @package WooCommerce\Warranty
 */
class Update_20240707 {

	/**
	 * Database version.
	 *
	 * @var string
	 */
	private static $db_version = '20240707';

	/**
	 * Initialize update.
	 */
	public function __construct() {
		$this->run_updates();
		$this->complete_updates();
	}


	/**
	 * Run all the necessary updates.
	 *
	 * @return void
	 */
	private function run_updates() {
		$this->update_meta_data_values();
	}

	/**
	 * Update mata data values, replace empty array with empty string.
	 *
	 * @return void
	 */
	private function update_meta_data_values() {
		global $wpdb;

		if ( version_compare( WC_VERSION, '9.1', '>=' ) ) {
			return;
		}

		$meta_keys = array(
			'product_ids',
			'exclude_product_ids',
		);

		foreach ( $meta_keys as $meta_key ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} pm
                 JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                 SET pm.meta_value = ''
                 WHERE pm.meta_key = %s
                 AND pm.meta_value = 'a:0:{}'
                 AND p.post_type = 'shop_coupon'",
					$meta_key
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

new Update_20240707();
