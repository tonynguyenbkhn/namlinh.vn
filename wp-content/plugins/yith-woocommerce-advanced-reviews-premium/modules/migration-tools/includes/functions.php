<?php
/**
 * Module functions
 *
 * @package YITH\AdvancedReviews\Modules\MigrationTools
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Migrate the Review Reminder settings
 *
 * @return void
 * @since  2.1.0
 */
function yith_ywar_migrate_ywrr_settings() {
	try {
		$refuse_request    = get_option( 'ywrr_refuse_requests' );
		$refuse_label      = get_option( 'ywrr_refuse_requests_label' );
		$request_type      = get_option( 'ywrr_request_type' );
		$request_criteria  = get_option( 'ywrr_request_criteria' );
		$request_number    = get_option( 'ywrr_request_number' );
		$schedule_day      = get_option( 'ywrr_mail_schedule_day' );
		$analytics_enabled = get_option( 'ywrr_enable_analytics' );
		$analytics_source  = get_option( 'ywrr_campaign_source' );
		$analytics_medium  = get_option( 'ywrr_campaign_medium' );
		$analytics_term    = get_option( 'ywrr_campaign_term' );
		$analytics_content = get_option( 'ywrr_campaign_content' );
		$analytics_name    = get_option( 'ywrr_campaign_name' );

		update_option( 'ywar_refuse_requests_label', $refuse_label );
		update_option( 'ywar_refuse_requests', $refuse_request );
		update_option( 'ywar_request_type', $request_type );
		update_option( 'ywar_request_criteria', $request_criteria );
		update_option( 'ywar_request_number', $request_number );
		update_option( 'ywar_mail_schedule_day', $schedule_day );
		update_option( 'ywar_enable_analytics', $analytics_enabled );
		update_option( 'ywar_campaign_source', $analytics_source );
		update_option( 'ywar_campaign_medium', $analytics_medium );
		update_option( 'ywar_campaign_term', $analytics_term );
		update_option( 'ywar_campaign_content', $analytics_content );
		update_option( 'ywar_campaign_name', $analytics_name );

		if ( '' === yith_ywar_get_option( 'ywar_mandrill_apikey' ) ) {
			$mandrill_enable = get_option( 'ywrr_mandrill_enable' );
			$mandrill_apikey = get_option( 'ywrr_mandrill_apikey' );
			update_option( 'ywar_mandrill_enable', $mandrill_enable );
			update_option( 'ywar_mandrill_apikey', $mandrill_apikey );
		}

		yith_ywar_update_migration_status( 'ywrr_settings', time() );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );
		yith_ywar_update_migration_status( 'ywrr_settings', 'failed' );
	}
}

/**
 * Fetch the Scheduled Emails table from Review Reminder
 *
 * @return void
 * @throws Exception An exception.
 * @since  2.1.0
 */
function yith_ywar_migrate_ywrr_scheduled_emails() {
	try {
		global $wpdb;

		$results = $wpdb->get_results( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"
			SELECT *
			FROM   {$wpdb->prefix}ywrr_email_schedule
			"
		);

		if ( false === $results ) {
			throw new Exception( 'There was an error while trying to migrate the scheduled email list.' );
		}

		if ( ! empty( $results ) ) {
			$grouped_emails = array_chunk( $results, 10, false );
			$time           = 0;
			$group_count    = count( $grouped_emails );
			$group_index    = 1;
			foreach ( $grouped_emails as $group ) {
				wc()->queue()->schedule_single(
					time() + $time,
					'yith_ywar_migrate_ywrr_emails',
					array(
						'emails'    => $group,
						'last_item' => ( $group_index === $group_count ),
					),
					'yith-ywar-migrate-ywrr-emails'
				);
				$time += ( MINUTE_IN_SECONDS * 3 );
				++$group_index;
			}
		}
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );
		yith_ywar_update_migration_status( 'ywrr_scheduled_emails', 'failed' );
	}
}

/**
 * Migrate the emails from Review Reminder
 *
 * @param array $emails     List of emails.
 * @param bool  $last_group Check if it is the last group of rules.
 *
 * @return void
 * @since  2.1.0
 */
function yith_ywar_migrate_ywrr_emails_group( array $emails, bool $last_group ) {
	try {
		YITH_YWAR_Request_Review_DB::define_tables();

		foreach ( $emails as $email ) {

			$old_items = maybe_unserialize( $email['request_items'] );
			$items     = ! empty( $old_items ) ? array_keys( $old_items ) : array();

			YITH_YWAR_Request_Review_DB::add_schedule(
				$email['order_id'],
				$email['order_date'],
				$email['scheduled_date'],
				maybe_serialize( $items ),
				$email['mail_type']
			);

		}
		if ( $last_group ) {
			yith_ywar_update_migration_status( 'ywrr_scheduled_emails', time() );
		}
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );
		yith_ywar_update_migration_status( 'ywrr_scheduled_emails', 'failed' );
	}
}

/**
 * Migrate the Blocklist table from Review Reminder
 *
 * @return void
 * @throws Exception An exception.
 * @since  2.1.0
 */
function yith_ywar_migrate_ywrr_blocklist() {
	try {
		global $wpdb;

		YITH_YWAR_Request_Review_DB::define_tables();

		$results = $wpdb->query( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"
			INSERT INTO $wpdb->yith_ywar_email_blocklist
			SELECT *
			FROM   {$wpdb->prefix}ywrr_email_blocklist
			"
		);

		if ( false === $results ) {
			throw new Exception( 'There was an error while trying to migrate the email blocklist.' );
		}

		yith_ywar_update_migration_status( 'ywrr_blocklist', time() );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );
		yith_ywar_update_migration_status( 'ywrr_blocklist', 'failed' );
	}
}

/**
 * Migrate the Review for Discounts settings
 *
 * @return void
 * @since  2.1.0
 */
function yith_ywar_migrate_ywrfd_settings() {
	try {

		$coupon_sending = get_option( 'ywrfd_coupon_sending' );
		update_option( 'ywar_coupon_sending', $coupon_sending );

		if ( '' === yith_ywar_get_option( 'ywar_mandrill_apikey' ) ) {
			$mandrill_enable = get_option( 'ywrfd_mandrill_enable' );
			$mandrill_apikey = get_option( 'ywrfd_mandrill_apikey' );
			update_option( 'ywar_mandrill_enable', $mandrill_enable );
			update_option( 'ywar_mandrill_apikey', $mandrill_apikey );
		}

		yith_ywar_update_migration_status( 'ywrfd_settings', time() );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );
		yith_ywar_update_migration_status( 'ywrfd_settings', 'failed' );
	}
}

/**
 * Fetch the Discounts from Review for Discounts
 *
 * @return void
 * @since  2.1.0
 */
function yith_ywar_migrate_ywrfd_discounts() {
	try {

		$discounts = get_posts(
			array(
				'post_type'      => 'ywrfd-discount',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $discounts ) ) {
			$grouped_discounts = array_chunk( $discounts, 10, false );
			$time              = 0;
			$group_count       = count( $grouped_discounts );
			$group_index       = 1;
			foreach ( $grouped_discounts as $group ) {
				wc()->queue()->schedule_single(
					time() + $time,
					'yith_ywar_migrate_ywrfd_discounts',
					array(
						'discounts' => $group,
						'last_item' => ( $group_index === $group_count ),
					),
					'yith-ywar-migrate-ywrfd-discounts'
				);
				$time += ( MINUTE_IN_SECONDS * 3 );
				++$group_index;
			}
		}
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );
		yith_ywar_update_migration_status( 'ywrfd_discounts', 'failed' );
	}
}

/**
 * Migrate the Discounts from Review for Discounts
 *
 * @param array $discounts  List of Discounts IDs.
 * @param bool  $last_group Check if it is the last group of rules.
 *
 * @return void
 * @since  2.1.0
 */
function yith_ywar_migrate_ywrfd_discounts_group( array $discounts, bool $last_group ) {
	try {
		foreach ( $discounts as $discount_id ) {
			$old_discount = new YWRFD_Discounts( $discount_id );
			$new_discount = new YITH_YWAR_Review_For_Discounts_Discount();
			$new_discount->set_title( get_the_title( $discount_id ) );
			$new_discount->set_trigger( $old_discount->trigger );
			$new_discount->set_trigger_product_ids( $old_discount->trigger_product_ids );
			$new_discount->set_trigger_product_categories( $old_discount->trigger_product_categories );
			$new_discount->set_trigger_threshold( (int) $old_discount->trigger_threshold );
			$new_discount->set_trigger_enable_notify( $old_discount->trigger_enable_notify );
			$new_discount->set_trigger_threshold_notify( (int) $old_discount->trigger_threshold_notify );
			$new_discount->set_discount_type( $old_discount->discount_type );
			if ( 'funds' === $old_discount->discount_type ) {
				$new_discount->set_funds_amount( $old_discount->coupon_amount );
			} else {
				$new_discount->set_amount( $old_discount->coupon_amount );
			}
			$new_discount->set_expiry_days( $old_discount->expiry_days );
			$new_discount->set_free_shipping( $old_discount->free_shipping );
			$new_discount->set_individual_use( $old_discount->individual_use );
			$new_discount->set_exclude_sale_items( $old_discount->exclude_sale_items );
			$new_discount->set_minimum_amount( $old_discount->minimum_amount );
			$new_discount->set_maximum_amount( $old_discount->maximum_amount );
			$new_discount->set_product_ids( $old_discount->product_ids );
			$new_discount->set_product_categories( $old_discount->product_categories );
			$new_discount->set_excluded_product_ids( $old_discount->excluded_product_ids );
			$new_discount->set_excluded_product_categories( $old_discount->excluded_product_categories );
			$new_discount->save();
		}
		if ( $last_group ) {
			yith_ywar_update_migration_status( 'ywrfd_discounts', time() );
		}
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );
		yith_ywar_update_migration_status( 'ywrfd_discounts', 'failed' );
	}
}

/**
 * Update migration status
 *
 * @param string $setting    Setting for which update the status.
 * @param string $new_status The new status.
 *
 * @return void
 * @since  2.1.0
 */
function yith_ywar_update_migration_status( string $setting, string $new_status ) {
	$statuses             = get_option( 'yith-ywar-migrate-status', array() );
	$statuses[ $setting ] = $new_status;

	update_option( 'yith-ywar-migrate-status', $statuses );
}

/**
 * Print the migration status
 *
 * @param array  $field  The current field.
 * @param string $status The current status.
 *
 * @return array
 * @since  2.1.0
 */
function yith_ywar_print_migration_status( array $field, string $status ): array {

	if ( 'failed' === $status ) {
		$message = esc_html_x( 'migration failed. Please try again.', '[Migration tools] Migration status description', 'yith-woocommerce-advanced-reviews' );
	} else {
		$field['type'] = 'html';
		$field['html'] = sprintf( '<span class="status-icon %s-status"></span>', $status );

		/* Translators: %s date of the migration */
		$message = 'pending' === $status ? esc_html_x( 'migration pending', '[Migration tools] Migration status description', 'yith-woocommerce-advanced-reviews' ) : sprintf( esc_html_x( 'migration completed on %s', '[Migration tools] Migration status description', 'yith-woocommerce-advanced-reviews' ), esc_html( date_i18n( wc_date_format(), $status ) ) );
	}

	$field['title'] .= " - <span>$message</span>";

	return $field;
}

/**
 * Check if the migration is complete
 *
 * @param array $available_settings The settings available for the migration.
 *
 * @return bool
 * @since  2.1.0
 */
function yith_ywar_check_migration_complete( array $available_settings ): bool {
	$statuses       = get_option( 'yith-ywar-migrate-status', array() );
	$total_settings = count( $available_settings );
	$migrated       = 0;
	if ( ! empty( $available_settings ) ) {
		foreach ( $available_settings as $setting ) {
			if ( isset( $statuses[ $setting ] ) && 'failed' !== $statuses[ $setting ] ) {
				++$migrated;
			}
		}
	}

	return $total_settings <= $migrated;
}

add_action( 'yith_ywar_migrate_ywrfd_discounts', 'yith_ywar_migrate_ywrfd_discounts_group', 10, 2 );
add_action( 'yith_ywar_migrate_ywrr_emails', 'yith_ywar_migrate_ywrr_emails_group', 10, 2 );
