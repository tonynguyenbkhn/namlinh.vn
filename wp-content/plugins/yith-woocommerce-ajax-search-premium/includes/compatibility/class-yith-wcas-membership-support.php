<?php
/**
 * YITH_WCAS_Membership_Support class
 *
 * @since      2.1.0
 * @author     YITH
 * @package    YITH/Search
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Membership_Support' ) ) {
	/**
	 * YITH WooCommerce Membership support class
	 *
	 * @since 2.1.0
	 */
	class YITH_WCAS_Membership_Support {

		use YITH_WCAS_Trait_Singleton;

		/**
		 * Avoid multiple check
		 *
		 * @var bool
		 */
		protected $doing_filter = false;

		/**
		 * Constructor
		 *
		 * @since 2.1.0
		 */
		private function __construct() {
			add_filter( 'ywcas_search_result_data', array( $this, 'filter_products' ), 10, 1 );
		}

		/**
		 * Filter product for user.
		 *
		 * @param   array $results  Array results.
		 *
		 * @return array
		 */
		public function filter_products( $results ) {
			$user_id = get_current_user_id();

			if ( ! $this->doing_filter && $results ) {
				$this->doing_filter = true;
				$prod_manager       = YITH_WCMBS_Products_Manager::get_instance();

				foreach ( $results as $key => $result ) {
					$is_allowed = $prod_manager->user_has_access_to_product( $user_id, $result['post_id'] );

					if ( ! $is_allowed ) {
						unset( $results[ $key ] );
					}
				}
			}

			return $results;
		}

	}

}
