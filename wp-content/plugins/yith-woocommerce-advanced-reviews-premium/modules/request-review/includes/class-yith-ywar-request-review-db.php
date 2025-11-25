<?php
/**
 * Class YITH_YWAR_Request_Review_DB
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Request_Review_DB' ) ) {
	/**
	 * Class YITH_YWAR_Request_Review_DB
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\RequestReview
	 */
	abstract class YITH_YWAR_Request_Review_DB {

		//phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		const BLOCKLIST_TABLE = 'yith_ywar_email_blocklist';

		const SCHEDULE_TABLE = 'yith_ywar_email_schedule';

		const DB_VERSION = '1.0.0';

		/**
		 * TABLE CREATION AND MANAGEMENT
		 */
		/**
		 * Register custom tables within $wpdb object.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function define_tables() {
			global $wpdb;

			// List of tables without prefixes.
			$tables = array(
				self::BLOCKLIST_TABLE => self::BLOCKLIST_TABLE,
				self::SCHEDULE_TABLE  => self::SCHEDULE_TABLE,
			);

			foreach ( $tables as $name => $table ) {
				$wpdb->$name    = $wpdb->prefix . $table;
				$wpdb->tables[] = $table;
			}
		}

		/**
		 * Create tables
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function create_db_tables() {
			global $wpdb;

			$wpdb->hide_errors();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$blocklist_table_name = $wpdb->prefix . self::BLOCKLIST_TABLE;
			$schedule_table_name  = $wpdb->prefix . self::SCHEDULE_TABLE;
			$collate              = '';

			if ( $wpdb->has_cap( 'collation' ) ) {
				$collate = $wpdb->get_charset_collate();
			}

			$sql = "
            CREATE TABLE $blocklist_table_name (
              id				int 		NOT NULL AUTO_INCREMENT,
              customer_email	longtext 	NOT NULL,
              customer_id		bigint(20)	NOT NULL DEFAULT 0,
              PRIMARY KEY (id)
            ) $collate;

            CREATE TABLE $schedule_table_name (
              id 				int 			NOT NULL AUTO_INCREMENT,
              object_id 		bigint(20)		NOT NULL,
              order_date 		date			NOT NULL DEFAULT '0000-00-00',
              scheduled_date	date			NOT NULL DEFAULT '0000-00-00',
              request_items 	longtext		NOT NULL DEFAULT '',
              mail_status 		varchar(15)		NOT NULL DEFAULT 'pending',
              mail_type 		varchar(100)	NOT NULL DEFAULT 'order',
              PRIMARY KEY (id)
            ) $collate;
			";

			dbDelta( $sql );

			update_option( 'yith-ywar-request-review-db-version', self::DB_VERSION );
		}

		/**
		 * SCHEDULE TABLE
		 */
		/**
		 * Set a schedule.
		 *
		 * @param int    $object_id     The object ID.
		 * @param string $order_date    The order date.
		 * @param string $schedule_date The scheduled date.
		 * @param string $items_list    The items to review.
		 * @param string $type          The object type.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function add_schedule( int $object_id, string $order_date, string $schedule_date, string $items_list, string $type ): bool {
			global $wpdb;

			return $wpdb->insert(
				$wpdb->yith_ywar_email_schedule,
				array(
					'object_id'      => $object_id,
					'mail_status'    => 'pending',
					'order_date'     => $order_date,
					'scheduled_date' => $schedule_date,
					'request_items'  => $items_list,
					'mail_type'      => $type,
				),
				array( '%d', '%s', '%s', '%s', '%s', '%s' )
			);
		}

		/**
		 * Update a row in scheduled emails
		 *
		 * @param array $data         Data to update (in column => value pairs).
		 * @param array $where        A named array of WHERE clauses (in column => value pairs).
		 * @param array $format       Optional. An array of formats to be mapped to each of the values in $data.
		 * @param array $where_format Optional. An array of formats to be mapped to each of the values in $where.
		 *
		 * @return bool
		 * @since  2.0.0
		 * @see    wpdb::update()
		 */
		public static function update_schedule( array $data, array $where, array $format, array $where_format ): bool {
			global $wpdb;

			return $wpdb->update(
				$wpdb->yith_ywar_email_schedule,
				$data,
				$where,
				$format,
				$where_format
			);
		}

		/**
		 * Delete all scheduled emails with a specific status
		 *
		 * @param string $status The email status to delete.
		 *
		 * @return bool|int
		 * @since  2.0.0
		 */
		public static function delete_schedules( string $status ) {
			global $wpdb;

			return $wpdb->delete(
				$wpdb->yith_ywar_email_schedule,
				array( 'mail_status' => $status ),
				array( '%s' )
			);
		}

		/**
		 * Get IDs of scheduled object.
		 *
		 * @param string $type The object type.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public static function get_schedules_for_type( string $type ): array {
			global $wpdb;

			return $wpdb->get_col(
				$wpdb->prepare(
					"
                    SELECT object_id
                    FROM   $wpdb->yith_ywar_email_schedule
                    WHERE  mail_type = %s
                    ",
					$type
				)
			);
		}

		/**
		 * Check if an object is scheduled.
		 *
		 * @param int    $object_id   The object ID.
		 * @param string $object_type The object type.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function check_object_scheduled( int $object_id, string $object_type ): bool {
			global $wpdb;

			$count = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT COUNT(*)
                    FROM   $wpdb->yith_ywar_email_schedule
                    WHERE  object_id = %d
                    AND    mail_type = %s
                    ",
					$object_id,
					$object_type
				)
			);

			return $count > 0;
		}

		/**
		 * List all the emails scheduled.
		 *
		 * @param string $mail_status  The mail status.
		 * @param string $search_param The search param.
		 * @param int    $limit        The items limit.
		 * @param int    $paged        The current page.
		 *
		 * @return array|object|stdClass[]|null
		 * @since  2.0.0
		 */
		public static function list_schedules( string $mail_status = '', string $search_param = '', int $limit = 0, int $paged = 0 ) {
			global $wpdb;

			$where = '';

			if ( '' !== $mail_status ) {
				$where .= "AND mail_status='$mail_status'";
			}

			if ( '' !== $search_param ) {
				$where .= "AND object_id LIKE '%{$wpdb->esc_like( $search_param )}%' ";
			}

			$sql = "
					SELECT
						id,
						object_id,
						order_date,
						scheduled_date,
						request_items,
						mail_status,
						mail_type
					FROM $wpdb->yith_ywar_email_schedule
					WHERE 1 = 1
					$where
					ORDER BY scheduled_date DESC";

			if ( $limit > 0 ) {
				$sql .= "
				LIMIT $limit
				OFFSET $paged
				";
			}

			return $wpdb->get_results( $sql );
		}

		/**
		 * Get schedule of a specific object
		 *
		 * @param int    $object_id   The object ID.
		 * @param string $object_type The object type.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public static function get_schedule_by_object( int $object_id, string $object_type ): array {

			global $wpdb;

			$schedule = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT id,
					       object_id,
					       request_items,
					       order_date,
					       mail_type,
					       scheduled_date,
					       mail_status
					FROM   $wpdb->yith_ywar_email_schedule
					WHERE  object_id = %d
					                    and mail_type = %s
					  ",
					$object_id,
					$object_type
				),
				ARRAY_A
			);

			return $schedule ?? array(
				'id'             => 0,
				'object_id'      => '',
				'request_items'  => '',
				'order_date'     => '',
				'mail_type'      => '',
				'scheduled_date' => '',
				'mail_status'    => 'unscheduled',
			);
		}

		/**
		 * Get specific schedule
		 *
		 * @param int $schedule_id The schedule ID.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public static function get_schedule_by_id( int $schedule_id ): array {

			global $wpdb;

			return $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT id,
					       object_id,
					       request_items,
					       order_date,
					       mail_type
					FROM   $wpdb->yith_ywar_email_schedule
					WHERE  id = %d
					  ",
					$schedule_id
				),
				ARRAY_A
			);
		}

		/**
		 * Count all the emails scheduled.
		 *
		 * @param string $mail_status  The mail status.
		 * @param string $search_param The search param.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public static function count_total_schedules( string $mail_status, string $search_param ): int {
			global $wpdb;

			$where = '';

			if ( '' !== $mail_status ) {
				$where .= " and mail_status = '$mail_status'";
			}

			if ( '' !== $search_param ) {
				$where .= " and object_id LIKE '%{$wpdb->esc_like( $search_param )}%' ";
			}

			$sql = "
					SELECT COUNT(*)
					FROM   $wpdb->yith_ywar_email_schedule
					WHERE  1 = 1
					$where
					";

			return (int) $wpdb->get_var( $sql );
		}

		/**
		 * Count the email that should be sent today
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public static function get_daily_schedules(): array {

			global $wpdb;

			return $wpdb->get_results(
				$wpdb->prepare(
					"
							SELECT id,
							       object_id,
							       request_items,
							       order_date,
							       mail_type
							FROM   $wpdb->yith_ywar_email_schedule
							WHERE  mail_status = 'pending'
							       and scheduled_date <= %s
							",
					current_time( 'mysql' )
				),
				ARRAY_A
			);
		}

		/**
		 * BLOCKLIST TABLE
		 */
		/**
		 * Check if the customer is in blocklist table
		 *
		 * @param int    $customer_id    The customer ID, 0 if is a guest.
		 * @param string $customer_email The customer email.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function check_blocklist( int $customer_id, string $customer_email ): bool {
			global $wpdb;

			if ( $customer_id ) {
				$query = $wpdb->prepare(
					"
                    SELECT COUNT(*)
                    FROM   $wpdb->yith_ywar_email_blocklist
                    WHERE ( customer_id = %d )
                    ",
					$customer_id
				);
			} else {
				$query = $wpdb->prepare(
					"
                    SELECT COUNT(*)
                    FROM   $wpdb->yith_ywar_email_blocklist
                    WHERE( customer_email = %s AND customer_id = 0 ) 
                    ",
					$customer_email,
				);
			}

			$count = $wpdb->get_var( $query );

			return $count > 0;
		}

		/**
		 * List all the users in the blocklist.
		 *
		 * @param string $search_param Additional search parameter.
		 * @param int    $limit        The items limit.
		 * @param int    $paged        The current page.
		 *
		 * @return array|object|stdClass[]|null
		 * @since  2.0.0
		 */
		public static function list_blocklist( string $search_param, int $limit, int $paged ) {
			global $wpdb;

			$where = '' !== $search_param ? "WHERE customer_email LIKE '%{$wpdb->esc_like( $search_param )}%'" : '';
			$sql   = "
					SELECT
					id,
					customer_id,
					customer_email
					FROM $wpdb->yith_ywar_email_blocklist
					$where
					GROUP BY customer_email
					ORDER BY customer_id ASC
					LIMIT $limit
					OFFSET $paged
					";

			return $wpdb->get_results( $sql );
		}

		/**
		 * Count all the users in the blocklist.
		 *
		 * @param string $search_param Additional search parameter.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public static function count_total_blocklist( string $search_param ): int {
			global $wpdb;

			$where = '' !== $search_param ? "WHERE customer_email LIKE '%{$wpdb->esc_like( $search_param )}%'" : '';
			$sql   = "
					SELECT COUNT(*)
					FROM   $wpdb->yith_ywar_email_blocklist
					$where
					";

			return (int) $wpdb->get_var( $sql );
		}

		/**
		 * Deletes an entry from the blocklist table
		 *
		 * @param int $id The item ID.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function delete_blocklist( int $id ): bool {
			global $wpdb;

			return $wpdb->delete(
				$wpdb->yith_ywar_email_blocklist,
				array( 'id' => $id ),
				array( '%d' )
			);
		}

		/**
		 * Deletes an entry from the blocklist table by Customer ID
		 *
		 * @param int $customer_id The item ID.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function delete_blocklist_by_customer( int $customer_id ): bool {
			global $wpdb;

			return $wpdb->delete(
				$wpdb->yith_ywar_email_blocklist,
				array( 'customer_id' => $customer_id ),
				array( '%d' )
			);
		}

		/**
		 * Add a customer to the blocklist.
		 *
		 * @param int    $customer_id    The customer ID, 0 if is a guest.
		 * @param string $customer_email The customer email.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function add_to_blocklist( int $customer_id, string $customer_email ): bool {
			global $wpdb;

			return $wpdb->insert(
				$wpdb->yith_ywar_email_blocklist,
				array(
					'customer_email' => $customer_email,
					'customer_id'    => $customer_id,
				),
				array( '%s', '%d' )
			);
		}
	}
}
