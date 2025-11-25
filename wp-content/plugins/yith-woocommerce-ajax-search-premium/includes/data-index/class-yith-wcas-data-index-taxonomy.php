<?php
/**
 * Class to manage the Data Lookup table
 *
 * @author  YITH
 * @package YITH/Search/DataIndex
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Recover the data from database
 *
 * @since 2.0.0
 */
class YITH_WCAS_Data_Index_Taxonomy {

	use YITH_WCAS_Trait_Singleton;

	/**
	 * Construction
	 *
	 * @return void
	 */
	private function __construct() {
	}

	/**
	 * Insert the taxonomy on database
	 *
	 * @param array $data Array of value.
	 *
	 * @since 2.0.0
	 * @return mixed
	 */
	public function insert( $data ) {
		global $wpdb;
		$result = $wpdb->insert( $wpdb->yith_wcas_index_taxonomy, $data, $this->get_format() );
		return $result ? $wpdb->insert_id : 0;
	}

	/**
	 * Remove the taxonomy from database
	 *
	 * @param   int $term_id  Term id to remove.
	 *
	 * @return void
	 */
	public function remove_data( $term_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->yith_wcas_index_taxonomy, array( 'term_id' => $term_id ), array( '%d' ) );
	}

	/**
	 * Get the format of columns
	 *
	 * @return array
	 */
	protected function get_format() {
		return array(
			'%d', // term_id.
			'%s', // term_name.
			'%s', // taxonomy.
			'%s', // url.
			'%d', // parent.
			'%d', // count.
			'%s', // lang.
		);
	}

	/**
	 * Clear the table
	 *
	 * @return void
	 */
	public function clear_table() {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE $wpdb->yith_wcas_index_taxonomy" );
		$wpdb->query( "ALTER TABLE $wpdb->yith_wcas_index_taxonomy DROP INDEX index_term_id" ); //phpcs:ignore
	}


	/**
	 * Reindex the table
	 *
	 * @return void
	 */
	public function index_table() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE $wpdb->yith_wcas_index_taxonomy ADD INDEX index_term_id (term_id)" ); //phpcs:ignore
	}


	/**
	 * Return the data index.
	 *
	 * @param array $ids List of ids.
	 *
	 * @return array
	 */
	public function get_taxnomies( $ids ) {
		global $wpdb;
		// ORDER BY FIELD returns results following the order of ids.
		return $wpdb->get_results( "SELECT * FROM $wpdb->yith_wcas_index_taxonomy WHERE term_id IN(" . implode( ',', $ids ) . ')', ARRAY_A ); //phpcs:ignore
	}

}
