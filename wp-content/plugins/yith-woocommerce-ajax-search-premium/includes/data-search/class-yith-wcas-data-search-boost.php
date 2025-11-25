<?php
/**
 * Data Boost class
 *
 * @author  YITH
 * @package YITH/Search/DataSearch
 * @version 2.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Data_Search_Boost' ) ) {
	/**
	 * Recover the data from database
	 *
	 * @since 2.1.0
	 */
	class YITH_WCAS_Data_Search_Boost {

		use YITH_WCAS_Trait_Singleton;

		/**
		 * The active boost rules
		 *
		 * @var YITH_WCAS_Boost[]
		 */
		private $rules = array();

		/**
		 * The construct
		 *
		 * @throws Exception The exception.
		 */
		private function __construct() {
			$this->get_active_rules();
			add_filter( 'ywcas_search_result_data', array( $this, 'boost_results' ), 10, 4 );
		}

		/**
		 * Add boost from boost rules
		 *
		 * @param array  $results The results.
		 * @param string $query_string The term searched.
		 * @param array  $post_type The post types.
		 * @param string $lang The language.
		 *
		 * @return array
		 */
		public function boost_results( $results, $query_string, $post_type, $lang ) {

			if ( ! in_array( 'product', $post_type, true ) ) {
				return $results;
			}

			foreach ( $this->rules as $rule ) {
				if ( $rule->match_with_searched_term( $query_string ) ) {

					foreach ( $results as $index => $result ) {
						if ( $rule->check_conditions( $result ) ) {
							$results[ $index ]['score'] *= $rule->get_boost();
						}
					}
				}
			}
			return $results;
		}

		/**
		 * Set the active rules.
		 *
		 * @return void
		 * @throws Exception The exception.
		 */
		private function get_active_rules() {
			if ( ! $this->rules ) {
				$this->rules = ywcas_get_boost_rules( array( 'active' => 'yes' ) );

			}
		}

	}

}
