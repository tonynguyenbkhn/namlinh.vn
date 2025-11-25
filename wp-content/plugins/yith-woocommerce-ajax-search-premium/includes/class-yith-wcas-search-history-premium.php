<?php
/**
 * Search History Premium class
 *
 * @author  YITH
 * @package YITH/Search
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Search_History_Premium' ) ) {
	/**
	 * WooCommerce Ajax Search
	 *
	 * @since 2.0.0
	 */
	class YITH_WCAS_Search_History_Premium extends YITH_WCAS_Search_History {


		const COOKIE_NAME = 'ywcas_history';

		/**
		 * Register query string
		 *
		 * @param string $query Query to register.
		 * @param int    $total_results Number of results.
		 * @param string $lang Current language.
		 * @param int    $item_id Product id clicked.
		 *
		 * @return int
		 */
		public function register_query( $query, $total_results, $lang, $item_id ) {
			$this->register_query_cookie( $query );
			return parent::register_query( $query, $total_results, $lang, $item_id );
		}


		/**
		 * Register the cookie inside the browser
		 *
		 * @param   string $query  Query to save.
		 *
		 * @return void
		 */
		public function register_query_cookie( $query ) {
			if ( empty( $query ) || apply_filters( 'ywcas_disable_cookies', false ) ) {
				return;
			}

			$value = array();

			if ( ! empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
				$cookie_name = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );
				$value       = json_decode( $cookie_name, true );
			}

			if ( ! in_array( $query, $value, true ) ) {
				$value[] = $query;
			}
			$value                        = wp_json_encode( stripslashes_deep( $value ) );
			$_COOKIE[ self::COOKIE_NAME ] = $value;
			$cookie_expiration_time       = apply_filters( 'ywcas_cookie_expiration_time', 30 * DAY_IN_SECONDS );
			wc_setcookie( self::COOKIE_NAME, $value, time() + $cookie_expiration_time, false );
		}


		/**
		 * Enable search history
		 *
		 * @param   string $lang   The language.
		 * @param   int    $limit  Limit.
		 *
		 * @return array
		 */
		public function get_history( $lang, $limit = 10 ) {

			if ( is_user_logged_in() ) {
				$history = $this->get_history_from_db( get_current_user_id(), $lang, $limit );
				$history = $history ? array_column( $history, 'query' ) : array();
			} else {
				$history = $this->get_history_from_cookie();
				$history = array_slice( $history, 0, $limit );
			}

			return apply_filters( 'ywcas_search_history_results', array_filter( $history ), $limit );
		}


		/**
		 * Return the history saved on cookies
		 *
		 * @param   int    $user_id   Current user.
		 * @param   string $lang   Current languages.
		 * @param   int    $limit  Limit results.
		 *
		 * @return array|mixed
		 */
		public function get_history_from_db( $user_id, $lang, $limit ) {
			return YITH_WCAS_Data_Search_Query_Log::user_history_searches( $user_id, $lang, $limit );
		}

		/**
		 * Return the history saved on cookies
		 *
		 * @param   string $lang  Current languages.
		 * @param   int    $limit  Limit results.
		 *
		 * @return array|mixed
		 */
		public function get_popular_from_db( $lang, $limit = 10 ) {
			return YITH_WCAS_Data_Search_Query_Log::popular( $lang, $limit );
		}

		/**
		 * Return the history saved on cookies ordered by newest to old
		 *
		 * @return array|mixed
		 */
		public function get_history_from_cookie() {
			$value = array();

			if ( ! empty( $_COOKIE[ self::COOKIE_NAME ] ) ) {
				$history = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE_NAME ] ) );

				$value = json_decode( $history, true );

			}

			return $value;
		}


		/**
		 * Get the popular searches
		 *
		 * @param   string $lang   Language.
		 * @param   int    $limit  Limit the results.
		 *
		 * @return array
		 */
		public function get_popular_searches( $lang, $limit = 10 ) {
			if ( 'custom' === ywcas()->settings->get_trending_searches_source() ) {
				$popular = ywcas()->settings->get_trending_searches_keywords();
				$popular = count( $popular ) > $limit ? array_slice( $popular, 0, $limit ) : $popular;
			} else {
				$popular = $this->get_popular_from_db( $lang, $limit );
				$popular = array_column( $popular, 'query' );
			}

			return array_filter( $popular );
		}

		/**
		 * Reset the searches history
		 *
		 * @return void
		 */
		public function reset_history_searches() {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				YITH_WCAS_Data_Search_Query_Log::delete_all_user_searches( $user_id );
			} else {
				wc_setcookie( self::COOKIE_NAME, '', -time(), false );
			}
		}

	}
}
