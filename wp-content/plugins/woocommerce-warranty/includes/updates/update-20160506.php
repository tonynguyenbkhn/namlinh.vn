<?php
/**
 * Update Data to 20160506
 *  - Move all request products into the new wc_warranty_products table.
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$statuses = warranty_get_statuses();
$found    = false;

foreach ( $statuses as $warranty_status ) {
	if ( 'Reviewing' === $warranty_status->name ) {
		$found = true;
		break;
	}
}

if ( ! $found ) {
	wp_insert_term( 'Reviewing', 'shop_warranty_status' );
}

$q                 = new WP_Query(
	array(
		'post_type' => 'warranty_request',
		'nopaging'  => true,
		'fields'    => 'ids',
		'tax_query' => array( // phpcs:ignore --- tax_query is needed to get request that doesnt have status
			array(
				'taxonomy' => 'shop_warranty_status',
				'operator' => 'NOT EXISTS',
			),
		),
	)
);
$warranty_requests = $q->get_posts();

foreach ( $warranty_requests as $request_id ) {
	wp_set_object_terms( $request_id, 'new', 'shop_warranty_status' );
}

delete_option( 'warranty_needs_update' );
update_option( 'warranty_db_version', '20160506' );
wp_safe_redirect( wp_nonce_url( admin_url( 'admin.php?page=warranties&view=updater&act=migrate_products', 'wc_warranty_updater' ) ) );
exit;
