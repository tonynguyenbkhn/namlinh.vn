<?php
/**
 * The data store for Boost rules
 *
 * @package YITH/Search/DataStores
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class manage the Data store for the boost rule object
 */
class YITH_WCAS_Boost_Data_Store extends WC_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Map meta key and props
	 *
	 * @var string[]
	 */
	protected $meta_key_to_props = array(
		'_active'            => 'active',
		'_boost'             => 'boost',
		'_enable_for_terms'  => 'enable_for_terms',
		'_check_term_type'   => 'check_term_type',
		'_terms'             => 'terms',
		'_conditions'        => 'conditions',
		'_validation_method' => 'validation_method',
	);

	/**
	 * Data stored in meta keys, but not considered "meta".
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_active',
		'_boost',
		'_enable_for_terms',
		'_check_term_type',
		'_terms',
		'_conditions',
		'_validation_method',
	);

	/**
	 * Meta data which should exist in the DB, even if empty.
	 *
	 * @var array
	 */
	protected $must_exist_meta_keys = array(
		'_conditions',
	);

	/**
	 * Create a new boost rule
	 *
	 * @param YITH_WCAS_Boost $boost The boost to create.
	 *
	 * @return void
	 */
	public function create( &$boost ) {
		$boost_id = wp_insert_post(
			array(
				'post_type'   => 'ywcas_boost',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'post_title'  => $boost->get_name() ? $boost->get_name() : __( 'Boost Rule', 'yith-woocommerce-ajax-search' ),
			),
			true
		);

		if ( $boost_id && ! is_wp_error( $boost_id ) ) {
			$boost->set_id( $boost_id );
			$this->update_post_meta( $boost, true );
			$boost->save_meta_data();
			$boost->apply_changes();
			do_action( 'ywcas_new_boost_rule', $boost_id, $boost );
		}

	}

	/**
	 * Read a boost rule
	 *
	 * @param YITH_WCAS_Boost $boost The boost rule.
	 *
	 * @return void
	 * @throws Exception The exception.
	 */
	public function read( &$boost ) {
		$boost->set_defaults();
		$post_object = get_post( $boost->get_id() );
		if ( ! $boost->get_id() || ! $post_object || 'ywcas_boost' !== $post_object->post_type ) {
			throw new Exception( __( 'Invalid boost rule.', 'yith-woocommerce-ajax-search' ) );
		}

		$boost->set_name( $post_object->post_title );
		$post_meta_values = get_post_meta( $boost->get_id() );
		$set_props        = array();

		foreach ( $this->meta_key_to_props as $meta_key => $prop ) {
			$meta_value         = $post_meta_values[ $meta_key ][0] ?? null;
			$set_props[ $prop ] = maybe_unserialize( $meta_value ); // get_post_meta only unserializes single values.
		}

		$boost->set_props( $set_props );

		$boost->set_object_read( true );

		do_action( 'ywcas_boost_rule_read', $boost->get_id() );

	}

	/**
	 * Update the boost rule
	 *
	 * @param YITH_WCAS_Boost $boost The boost rule.
	 *
	 * @return void
	 */
	public function update( &$boost ) {
		$boost->save_meta_data();
		$changes = $boost->get_changes();

		if ( in_array( 'name', array_keys( $changes ), true ) ) {
			$post_data = array(
				'post_title' => $boost->get_name( 'edit' ),
			);
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $boost->get_id() ) );
				clean_post_cache( $boost->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $boost->get_id() ), $post_data ) );
			}

			$boost->read_meta_data( true );
		}
		$this->update_post_meta( $boost );
		$boost->apply_changes();

		do_action( 'ywcas_update_boost_rule', $boost->get_id(), $boost );
	}

	/**
	 * Delete a boost rule
	 *
	 * @param YITH_WCAS_Boost $boost The boost rule.
	 * @param array           $args Array of args to pass to the delete method.
	 *
	 * @return void
	 */
	public function delete( &$boost, $args = array() ) {
		$id = $boost->get_id();

		if ( ! $id ) {
			return;
		}
		do_action( 'ywcas_before_delete_ywcas_boost', $id );
		wp_delete_post( $id );
		$boost->set_id( 0 );
		do_action( 'ywcas_delete_ywcas_boost', $id );
	}

	/**
	 * Update the boost meta
	 *
	 * @param YITH_WCAS_Boost $boost The boost rule.
	 * @param bool            $force Force update. Used during create.
	 *
	 * @return void
	 */
	public function update_post_meta( $boost, $force = false ) {

		$props_to_update = $force ? $this->meta_key_to_props : $this->get_props_to_update( $boost, $this->meta_key_to_props );
		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $boost->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'active':
				case 'enable_for_terms':
					$value = wc_bool_to_string( $value );
					break;
			}

			$this->update_or_delete_post_meta( $boost, $meta_key, $value );
		}
	}

	/**
	 * Manage the query
	 *
	 * @param array $args The query args.
	 *
	 * @return array|int[]|object|WP_Post[]
	 */
	public function query( $args = array() ) {
		$default = array(
			'name'             => '',
			'active'           => '',
			'enable_for_terms' => '',
			'include'          => array(),
			'exclude'          => array(),
			'return'           => 'object',
			'limit'            => get_option( 'posts_per_page' ),
			'page'             => 1,
			'offset'           => '',
			'paginate'         => false,
			'order'            => 'DESC',
			'orderby'          => 'ID',
		);

		$args = wp_parse_args( $args, $default );

		$query_args = $this->get_wp_query_args( $args );

		if ( ! empty( $query_args['errors'] ) ) {
			$query = (object) array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			);
		} else {
			$query = new WP_Query( $query_args );
		}

		if ( isset( $args['return'] ) && 'objects' === $args['return'] && ! empty( $query->posts ) ) {
			// Prime caches before grabbing objects.
			update_post_caches( $query->posts, 'ywcas_boost' );
		}

		$boost_rules = ( isset( $args['return'] ) && 'ids' === $args['return'] ) ? $query->posts : array_filter( array_map( 'ywcas_get_boost_rule', $query->posts ) );

		if ( isset( $args['paginate'] ) && $args['paginate'] ) {
			return (object) array(
				'boost_rules'   => $boost_rules,
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			);
		}

		return $boost_rules;
	}

	/**
	 * Get valid WP_Query args from a WC_Product_Query's query variables.
	 *
	 * @param array $query_vars Query vars.
	 *
	 * @return array
	 */
	protected function get_wp_query_args( $query_vars ) {

		// Map query vars to ones that get_wp_query_args or WP_Query recognize.
		$key_mapping = array(
			'page'    => 'paged',
			'include' => 'post__in',
		);
		foreach ( $key_mapping as $query_key => $db_key ) {
			if ( isset( $query_vars[ $query_key ] ) ) {
				$query_vars[ $db_key ] = $query_vars[ $query_key ];
				unset( $query_vars[ $query_key ] );
			}
		}

		// Map boolean queries that are stored as 'yes'/'no' in the DB to 'yes' or 'no'.
		$boolean_queries = array(
			'active',
			'enable_for_terms',
		);
		foreach ( $boolean_queries as $boolean_query ) {
			if ( isset( $query_vars[ $boolean_query ] ) && '' !== $query_vars[ $boolean_query ] ) {
				$query_vars[ $boolean_query ] = $query_vars[ $boolean_query ] ? 'yes' : 'no';
			}
		}

		$wp_query_args = parent::get_wp_query_args( $query_vars );

		if ( ! isset( $wp_query_args['date_query'] ) ) {
			$wp_query_args['date_query'] = array();
		}
		if ( ! isset( $wp_query_args['meta_query'] ) ) {
			$wp_query_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		// Handle paginate.
		if ( ! isset( $query_vars['paginate'] ) || ! $query_vars['paginate'] ) {
			$wp_query_args['no_found_rows'] = true;
		}

		// Handle orderby.
		if ( isset( $query_vars['orderby'] ) && 'include' === $query_vars['orderby'] ) {
			$wp_query_args['orderby'] = 'post__in';
		}

		$wp_query_args['post_type']   = 'ywcas_boost';
		$wp_query_args['post_status'] = 'publish';

		return apply_filters( 'ywcas_boost_rule_data_store_cpt_get_boost_query', $wp_query_args, $query_vars, $this );
	}
}
