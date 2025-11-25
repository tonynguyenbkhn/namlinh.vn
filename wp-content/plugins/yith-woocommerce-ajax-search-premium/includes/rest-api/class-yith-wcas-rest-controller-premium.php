<?php
/**
 * REST API Search Controller
 *
 * Handles requests to /ywcas/{option}
 *
 * @package YITH/Search/RestAPI
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_REST_Controller_Premium' ) && class_exists( 'YITH_WCAS_REST_Controller' ) ) {

	/**
	 * Ajax Search controller.
	 *
	 * @internal
	 * @extends WC_REST_Controller
	 */
	class  YITH_WCAS_REST_Controller_Premium extends YITH_WCAS_REST_Controller {

		/**
		 * Registers the routes for posts.
		 *
		 * @since 1.0.0
		 *
		 * @see   register_rest_route()
		 */
		public function register_routes() {
			parent::register_routes();

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/reset-user-history',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'reset_user_history' ),
						'permission_callback' => '__return_true',
						'args'                => $this->get_collection_params(),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/categories',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_categories' ),
						'permission_callback' => '__return_true',
						'args'                => $this->get_collection_params(),
					),
					'schema' => array( $this, 'get_public_item_schema' ),
				)
			);
		}




		/**
		 * Get a list of category.
		 *
		 * @param   WP_REST_Request $request  Full details about the request.
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function get_categories( $request ) {
			$category_type = $request['type'] ?? 'hierarchical';
			$lang          = isset( $request['lang'] ) ? sanitize_text_field( wp_unslash( $request['lang'] ) ) : ywcas_get_current_language();
			$categories    = ywcas_get_category_list( $category_type, $lang );
			return rest_ensure_response( $categories );
		}


		/**
		 * Reset the customer history search
		 *
		 * @param   WP_REST_Request $request  Full details about the request.
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function reset_user_history( $request ) {
			YITH_WCAS_Search_History_Premium::get_instance()->reset_history_searches();

			return rest_ensure_response( array( 'reset' => true ) );
		}

		/**
		 * Register query string
		 *
		 * @param   WP_REST_Request $request  Full details about the request.
		 *
		 * @return WP_Error|WP_REST_Response
		 */
		public function register_query_string( $request ) {
			$query         = $request['queryString'];
			$total_results = $request['totalResults'];
			$item_id       = isset( $request['itemID'] ) ? $request['itemID'] : 0;
			$lang          = isset( $request['lang'] ) ? sanitize_text_field( wp_unslash( $request['lang'] ) ) : ywcas_get_current_language();

			$logger_id = YITH_WCAS_Search_History_Premium::get_instance()->register_query( $query, $total_results, $lang, $item_id );
			$results   = array(
				'loggerID' => $logger_id,
			);

			return rest_ensure_response( $results );
		}

	}
}
