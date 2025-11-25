<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Implements YITH_WCAS_Privacy
 *
 * @class   YITH_WCAS_Privacy
 * @package YITH/Search
 * @since   2.0.0
 * @author  YITH
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'YITH_WCAS_Privacy' ) ) {
	/**
	 * Class YITH_WCAS_Privacy
	 */
	class YITH_WCAS_Privacy {
		use YITH_WCAS_Trait_Singleton;

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since 2.0.0
		 */
		private function __construct() {
			add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporters' ), 5 );
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_erasers' ), 4 );
		}

		/**
		 * Register the exporter for YITH Subscription.
		 *
		 * @param   array $exporters  Exporters.
		 *
		 * @return array
		 */
		public function register_exporters( $exporters = array() ) {
			$exporters['ywcas-customer-searches'] = array(
				'exporter_friendly_name' => esc_html__( 'Customer Searches', 'yith-woocommerce-ajax-search' ),
				'callback'               => array( 'YITH_WCAS_Privacy', 'search_data_exporter' ),
			);

			return $exporters;
		}

		/**
		 * Register the eraser for YITH Subscription.
		 *
		 * @param   array $erasers  Erasers.
		 *
		 * @return array
		 */
		public function register_erasers( $erasers = array() ) {
			$erasers['ywcas-customer-searches'] = array(
				'eraser_friendly_name' => esc_html__( 'Customer Searches', 'yith-woocommerce-ajax-search' ),
				'callback'             => array( 'YITH_WCAS_Privacy', 'search_data_eraser' ),
			);

			return $erasers;
		}

		/**
		 * Subscription data exporter.
		 *
		 * @param   string $email_address  Email address.
		 * @param   string $page           Page.
		 *
		 * @return array
		 * @throws Exception Return error.
		 */
		public static function search_data_exporter( $email_address, $page ) {
			$data_to_export = array();
			$user           = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
			if ( ! $user ) {
				return array(
					'data' => $data_to_export,
					'done' => false,
				);
			}
			$queries = YITH_WCAS_Data_Search_Query_Log::all_user_searches( $user->ID );
			if ( $user instanceof WP_User ) {
				if ( $queries ) {
					foreach ( $queries as $query ) {

						$data_to_export[] = array(
							'group_id'          => 'ywcas_searches',
							'group_label'       => esc_html__( 'Searches', 'yith-woocommerce-ajax-search' ),
							'group_description' => __( 'User\'s search.', 'yith-woocommerce-ajax-search' ),
							'item_id'           => $query['id'],
							'data'              => array(
								array(
									'name'  => 'query',
									'value' => $query['query'],
								),
								array(
									'name'  => 'search-date',
									'value' => $query['search_date'],
								),
								array(
									'name'  => 'clicked-product',
									'value' => $query['clicked_product'],
								),
								array(
									'name'  => 'lang',
									'value' => $query['lang'],
								),
							),
						);
					}
				}
			}

			return array(
				'data' => $data_to_export,
				'done' => true,
			);
		}

		/**
		 * Ajax Search data eraser.
		 *
		 * @param   string $email_address  Email address.
		 * @param   string $page           Page.
		 *
		 * @return array
		 */
		public static function search_data_eraser( $email_address, $page ) {

			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			$user = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.
			if ( ! $user ) {
				$response['messages'] = array( __( 'User not found', 'yith-woocommerce-ajax-search' ) );

				return $response;
			}

			$queries = YITH_WCAS_Data_Search_Query_Log::all_user_searches( $user->ID );
			YITH_WCAS_Data_Search_Query_Log::delete_all_user_searches( $user->ID );

			return array(
				'items_removed'  => count( $queries ),
				'items_retained' => 0,
				'messages'       => array(),
				'done'           => true,
			);

		}


	}
}

/**
 * Unique access to instance of YITH_WCAS_Privacy class
 *
 * @return YITH_WCAS_Privacy
 */
function YITH_WCAS_Privacy() { // phpcs:ignore
	return YITH_WCAS_Privacy::get_instance();
}

YITH_WCAS_Privacy();
