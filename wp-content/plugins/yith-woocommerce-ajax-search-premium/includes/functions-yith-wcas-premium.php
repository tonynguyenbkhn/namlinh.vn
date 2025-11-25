<?php
/**
 * Functions
 *
 * @author  YITH
 * @package YITH/Search
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ywcas_premium_install_woocommerce_admin_notice' ) ) {
	/**
	 * Check if WooCommerce is installed.
	 */
	function ywcas_premium_install_woocommerce_admin_notice() {
		?>
		<div class="error">
			<p><?php esc_html_e( 'YITH WooCommerce Ajax Search Premium is enabled but not effective. It requires WooCommerce in order to work.', 'yith-woocommerce-ajax-search' ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'ywcas_get_boost_rule' ) ) {
	/**
	 * Get the boost rule object by id|object or post
	 *
	 * @param int|YITH_WCAS_Boost|WP_Post $the_boost_rule The boost rule ID or object.
	 *
	 * @return bool|YITH_WCAS_Boost
	 */
	function ywcas_get_boost_rule( $the_boost_rule ) {

		if ( $the_boost_rule instanceof YITH_WCAS_Boost ) {
			$id = $the_boost_rule->get_id();
		} elseif ( is_numeric( $the_boost_rule ) ) {
			$id = $the_boost_rule;
		} elseif ( ! empty( $the_boost_rule->ID ) ) {
			$id = $the_boost_rule->ID;
		} else {
			$id = false;
		}

		if ( ! $id ) {
			return false;
		}

		try {
			return new YITH_WCAS_Boost( $id );
		} catch ( Exception $e ) {
			return false;
		}
	}
}

if ( ! function_exists( 'ywcas_get_boost_rules' ) ) {

	/**
	 * Get boost rules
	 *
	 * @param array $args The query args.
	 *
	 * @return array
	 * @throws Exception The exception.
	 */
	function ywcas_get_boost_rules( $args = array() ) {
		$data_store = WC_Data_Store::load( 'ywcas-boost-rule' );

		return $data_store ? $data_store->query( $args ) : array();
	}
}
