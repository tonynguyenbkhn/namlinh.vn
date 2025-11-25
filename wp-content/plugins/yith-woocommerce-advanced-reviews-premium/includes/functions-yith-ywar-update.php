<?php
/**
 * Update functions
 *
 * @package YITH\AdvancedReviews
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Update options
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_update_200_options() {
	$show_load_more  = '1' === get_option( 'ywar_show_load_more' ) ? 'no' : 'yes';
	$import_id       = get_option( 'YITH_WC_ywar_meta_value_import_id', '' );
	$attachment_type = explode( ',', get_option( 'ywar_attachment_type' ) );
	$max_attachments = (int) get_option( 'ywar_max_attachments' );

	if ( '' === $import_id ) {
		$import_id = uniqid();
	} else {
		update_option( 'yith-ywar-welcome-modal', 'update' );
	}

	update_option( 'yith-ywar-import-id', $import_id );
	update_option( 'ywar_show_load_more', $show_load_more );
	update_option( 'ywar_attachment_type', $attachment_type );
	update_option( 'ywar_max_attachments', ( 0 === $max_attachments ? 20 : $max_attachments ) );
}

/**
 * Update existing Reviews
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_update_200_reviews() {
	$reviews = get_posts(
		array(
			'post_type'      => YITH_YWAR_Post_Types::REVIEWS,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post_status'    => array( 'publish' ),
		)
	);

	if ( ! empty( $reviews ) ) {
		$grouped_reviews = array_chunk( $reviews, 25, false );
		$time            = 0;
		foreach ( $grouped_reviews as $group ) {
			wc()->queue()->schedule_single(
				time() + $time,
				'yith_ywar_update_reviews',
				array( 'reviews' => $group ),
				'yith-ywar-update-reviews'
			);
			$time += ( MINUTE_IN_SECONDS * 10 );
		}
	}
}

/**
 * Import existing reviews from WooCommerce.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_update_200_convert_reviews() {

	add_filter( 'wpml_is_comment_query_filtered', '__return_false' );

	$wc_comments = get_comments(
		array(
			'type'       => 'review',
			'fields'     => 'ids',
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				array(
					'key'     => '_ywar_imported',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	if ( ! empty( $wc_comments ) ) {
		$grouped_comments = array_chunk( $wc_comments, 25, false );
		$time             = 0;
		foreach ( $grouped_comments as $group ) {
			wc()->queue()->schedule_single(
				time() + $time,
				'yith_ywar_convert_reviews',
				array( 'comments' => $group ),
				'yith-ywar-convert-reviews'
			);
			$time += ( MINUTE_IN_SECONDS * 5 );
		}
	}
}

/**
 * Update DB Version.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_update_200_db_version() {
	YITH_YWAR_Install::update_db_version( '2.0.0' );
}

/**
 * Clear scheduled hooks
 *
 * @return void
 * @since  2.0.3
 */
function yith_ywar_update_203_clear_scheduled_hooks() {
	global $wpdb;
	$to_delete = array(
		'yith_ywar_update_reports',
		'yith_ywar_request_review_daily_check',
	);
	foreach ( $to_delete as $hook ) {
		WC()->queue()->cancel_all( $hook );
		// Delete manually, since cancel_all doesn't cancel completed-recurring actions.
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->actionscheduler_actions WHERE hook = %s", $hook ) ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
}
