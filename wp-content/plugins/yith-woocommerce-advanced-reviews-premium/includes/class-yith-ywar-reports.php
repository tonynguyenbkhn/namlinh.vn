<?php
/**
 * Class YITH_YWAR_Reports
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Reports' ) ) {
	/**
	 * Class YITH_YWAR_Reports
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Reports {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * The stats.
		 */
		const REPORTS = 'yith-ywar-reports';

		/**
		 * The constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			add_action( 'yith_ywar_print_reports_tab', array( $this, 'print_reports_tab' ) );
			add_action( 'wp_loaded', array( $this, 'init_schedule' ) );
			add_action( 'yith_ywar_update_reports', array( $this, 'update_reports' ) );
		}

		/**
		 * Print the Reports tab
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function print_reports_tab() {

			$reports     = $this->get_plugin_reports();
			$stats_boxes = array(
				'total-reviews'  => array(
					'label' => esc_html_x( 'Total reviews', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'value' => $reports['total-reviews'],
					'class' => 'total-reviews',
				),
				'total-requests' => array(
					'label' => esc_html_x( 'Review reminders sent', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'value' => $reports['requests-sent'],
					'class' => 'total-requests',
				),
				'average-rating' => array(
					'label'           => esc_html_x( 'Average rating', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'value'           => $reports['average-rating']['value'],
					'class'           => 'average-rating',
					'additional_html' => '<span class="stars" style="background: linear-gradient(90deg, #dc9202 ' . esc_attr( $reports['average-rating']['perc'] ) . '%, #cdcdcd 0)"></span>',
				),
			);

			$last_reviews = yith_ywar_get_reviews(
				array(
					'posts_per_page' => 4,
					'post_parent'    => 0,
					'order_by'       => array( 'date' => 'DESC' ),
				)
			);

			$widget_boxes = array(
				'last-reviews'      => array(
					'label'         => esc_html_x( 'Last reviews', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'values'        => $last_reviews,
					'class'         => 'last-reviews',
					/* translators: %s BR tag */
					'empty_message' => sprintf( esc_html_x( 'There are no reviews%sfor your products yet.', '[Admin panel] Empty message', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
					'template'      => 'last-reviews',
				),
				'best-rated'        => array(
					'label'         => esc_html_x( 'Best rated products', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'values'        => $reports['best-rated'],
					'class'         => 'best-rated',
					/* translators: %s BR tag */
					'empty_message' => sprintf( esc_html_x( 'There are no reviews%sfor your products yet.', '[Admin panel] Empty message', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
					'template'      => 'rating-count',
				),
				'worst-rated'       => array(
					'label'         => esc_html_x( 'Worst rated products', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'values'        => $reports['worst-rated'],
					'class'         => 'worst-rated',
					/* translators: %s BR tag */
					'empty_message' => sprintf( esc_html_x( 'There are no reviews%sfor your products yet.', '[Admin panel] Empty message', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
					'template'      => 'rating-count',
				),
				'most-reviewed'     => array(
					'label'         => esc_html_x( 'Products with higher number of reviews', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'values'        => $reports['most-reviewed'],
					'class'         => 'most-reviewed',
					/* translators: %s BR tag */
					'empty_message' => sprintf( esc_html_x( 'There are no reviews%sfor your products yet.', '[Admin panel] Empty message', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
					'template'      => 'review-count',
				),
				'less-reviewed'     => array(
					'label'         => esc_html_x( 'Products with lower number of reviews', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'values'        => $reports['less-reviewed'],
					'class'         => 'less-reviewed',
					/* translators: %s BR tag */
					'empty_message' => sprintf( esc_html_x( 'There are no reviews%sfor your products yet.', '[Admin panel] Empty message', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
					'template'      => 'review-count',
				),
				'most-active-users' => array(
					'label'         => esc_html_x( 'Most active users', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'values'        => $reports['most-active-users'],
					'class'         => 'most-active-users',
					/* translators: %s BR tag */
					'empty_message' => esc_html_x( 'No user has left a review yet.', '[Admin panel] Empty message', 'yith-woocommerce-advanced-reviews' ),
					'template'      => 'most-active-users',
				),
				'last-reported'     => array(
					'label'         => esc_html_x( 'Last reported reviews', '[Admin panel] Report widget title', 'yith-woocommerce-advanced-reviews' ),
					'values'        => $reports['last-reported'],
					'class'         => 'last-reported',
					/* translators: %s BR tag */
					'empty_message' => sprintf( esc_html_x( 'There are no reviews%sreported yet.', '[Admin panel] Empty message', 'yith-woocommerce-advanced-reviews' ), '<br />' ),
					'template'      => 'last-reported',
				),
			);

			if ( ! yith_ywar_is_module_active( 'request-review' ) ) {
				unset( $stats_boxes['total-requests'] );
			}

			$args = array(
				'stats_boxes'  => $stats_boxes,
				'widget_boxes' => $widget_boxes,
			);

			yith_ywar_get_view( 'settings-tabs/html-reports.php', $args );
		}

		/**
		 * Get plugin reports
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_plugin_reports(): array {

			$defaults = array(
				'total-reviews'     => 0,
				'requests-sent'     => 0,
				'average-rating'    => array(
					'value' => 0,
					'perc'  => 0,
				),
				'best-rated'        => array(),
				'worst-rated'       => array(),
				'most-reviewed'     => array(),
				'less-reviewed'     => array(),
				'most-active-users' => array(),
				'last-reported'     => array(),
			);

			return get_option( self::REPORTS, $defaults );
		}

		/**
		 * Init schedule report update
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_schedule() {

			if ( empty( get_option( self::REPORTS, array() ) ) ) {
				// If no report exists, then we'll create it for the first time.
				$this->update_reports();
			}

			/**
			 * APPLY_FILTERS: yith_ywar_report_update_recurrence
			 *
			 * Manages recurrence of the report update.
			 *
			 * @param int $value The number of minutes.
			 *
			 * @return int
			 */
			$schedule_minutes = apply_filters( 'yith_ywar_report_update_recurrence', 30 );

			if ( ! wc()->queue()->get_next( 'yith_ywar_update_reports' ) ) {
				wc()->queue()->schedule_single(
					strtotime( 'now +' . $schedule_minutes . ' MINUTES ' ),
					'yith_ywar_update_reports',
					array(),
					'yith-ywar-update-reports'
				);
			}
		}

		/**
		 * Update Reports
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function update_reports() {

			$reviews       = yith_ywar_get_reviews(
				array(
					'posts_per_page' => -1,
					'post_parent'    => 0,
				)
			);
			$total_reviews = 0;
			$total_ratings = 0;

			if ( ! empty( $reviews ) ) {
				$total_reviews = absint( count( $reviews ) );

				foreach ( $reviews as $review ) {
					$total_ratings += $review->get_rating();
				}
			}

			$values = array(
				'total-reviews'     => $total_reviews,
				'requests-sent'     => yith_ywar_is_module_active( 'request-review' ) ? get_option( YITH_YWAR_Request_Review::SENT_COUNTER, 0 ) : 0,
				'average-rating'    => array(
					'value' => $total_reviews > 0 ? round( ( $total_ratings / $total_reviews ), 1 ) : 0,
					'perc'  => $total_reviews > 0 ? ( round( ( $total_ratings / $total_reviews ) / 5, 1 ) * 100 ) : 0,
				),
				'best-rated'        => $this->get_products( 'rating', 'DESC' ),
				'worst-rated'       => $this->get_products( 'rating', 'ASC' ),
				'most-reviewed'     => $this->get_products( 'count', 'DESC' ),
				'less-reviewed'     => $this->get_products( 'count', 'ASC' ),
				'most-active-users' => $this->get_most_active_users(),
				'last-reported'     => $this->get_last_reported(),
			);

			update_option( self::REPORTS, $values );
		}

		/**
		 * Get last reported reviews
		 *
		 * @return array|false|object|YITH_YWAR_Review[]
		 * @since  2.0.0
		 */
		private function get_last_reported() {
			return yith_ywar_get_reviews(
				array(
					'post_status'    => 'ywar-reported',
					'posts_per_page' => 3,
					'orderby'        => 'modified',
					'order'          => 'DESC',
					'fields'         => 'ids',
				)
			);
		}

		/**
		 * Get products according to some criteria.
		 *
		 * @param string $criteria The critera of search.
		 * @param string $order    The order.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		private function get_products( string $criteria = 'rating', string $order = 'ASC' ): array {

			$args     = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					array(
						'key'     => 'count' === $criteria ? '_wc_review_count' : '_wc_average_rating',
						'value'   => 0,
						'compare' => '>',
					),
				),
				'orderby'        => 'meta_value_num',
				'meta_key'       => 'count' === $criteria ? '_wc_review_count' : '_wc_average_rating', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'order'          => $order,
				'fields'         => 'ids',
				'posts_per_page' => 4,
			);
			$products = new WP_Query( $args );

			return $products->posts;
		}

		/**
		 * Get most active users
		 *
		 * @return array
		 * @since  2.0.0
		 */
		private function get_most_active_users(): array {
			global $wpdb;

			return $wpdb->get_results( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				"
                    SELECT 
                        COUNT(*) AS total, 
                        meta_value AS email 
                    FROM $wpdb->postmeta
                    WHERE meta_key = '_ywar_review_author_email'
                    GROUP BY email
                    ORDER BY total DESC
                    LIMIT 4
                    ",
				ARRAY_A
			);
		}
	}
}
