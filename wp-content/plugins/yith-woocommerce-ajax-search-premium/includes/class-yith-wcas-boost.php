<?php
/**
 * This class manage the Boost Rule object
 *
 * @author  YITH
 * @package YITH/Search
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class that create the Boost objects
 */
class YITH_WCAS_Boost extends WC_Data {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'ywcas_boost';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'ywcas_boost';

	/**
	 * Cache group.
	 *
	 * @var string
	 */
	protected $cache_group = 'ywcas_boosts';

	/**
	 * Stores boost data.
	 *
	 * @var array
	 */
	protected $data = array(
		'active'            => 'no',
		'name'              => '',
		'boost'             => 1.0,
		'enable_for_terms'  => 'no',
		'check_term_type'   => 'exact',
		'terms'             => '',
		'conditions'        => array(),
		'validation_method' => 'and',
	);

	/**
	 * Get the boost if ID is passed, otherwise the boost is new and empty.
	 * This class should NOT be instantiated, but the wc_get_product() function
	 * should be used. It is possible, but the wc_get_product() is preferred.
	 *
	 * @param int|YITH_WCAS_Boost|object $boost Boost Rule to init.
	 */
	public function __construct( $boost = 0 ) {
		parent::__construct( $boost );
		if ( is_numeric( $boost ) && $boost > 0 ) {
			$this->set_id( $boost );
		} elseif ( $boost instanceof self ) {
			$this->set_id( absint( $boost->get_id() ) );
		} elseif ( ! empty( $boost->ID ) ) {
			$this->set_id( absint( $boost->ID ) );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( 'ywcas-boost-rule' );
		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/** ==== SETTER METHODS === */

	/**
	 * Set if the boost rule is active or not
	 *
	 * @param string $active Active ( yes|no ).
	 *
	 * @return void
	 */
	public function set_active( $active ) {
		$this->set_prop( 'active', $active );
	}

	/**
	 * Set the name of the boost
	 *
	 * @param string $name The name of boost.
	 *
	 * @return void
	 */
	public function set_name( $name ) {
		$this->set_prop( 'name', $name );
	}

	/**
	 * Set the boost value
	 *
	 * @param float $boost The value.
	 *
	 * @return void
	 */
	public function set_boost( $boost ) {
		$this->set_prop( 'boost', $boost );
	}

	/**
	 * Set the check for search terms
	 *
	 * @param string $enable Yes or no.
	 *
	 * @return void
	 */
	public function set_enable_for_terms( $enable ) {
		$this->set_prop( 'enable_for_terms', $enable );
	}

	/**
	 * Set the check terms type
	 *
	 * @param string $check_type The check type ( exact|partial ).
	 *
	 * @return void
	 */
	public function set_check_term_type( $check_type ) {
		$this->set_prop( 'check_term_type', $check_type );
	}

	/**
	 * Set the search terms list
	 *
	 * @param string $search_terms A comma separated list of terms.
	 *
	 * @return void
	 */
	public function set_terms( $search_terms ) {
		$this->set_prop( 'terms', $search_terms );
	}

	/**
	 * Set the conditions
	 *
	 * @param array $conditions The conditions.
	 *
	 * @return void
	 */
	public function set_conditions( $conditions ) {
		$this->set_prop( 'conditions', $conditions );
	}

	/**
	 * Set how the conditions will be checked
	 *
	 * @param string $validation_method The validation method (all|any).
	 *
	 * @return void
	 */
	public function set_validation_method( $validation_method ) {
		$this->set_prop( 'validation_method', $validation_method );
	}

	/** ==== GETTER METHODS === */

	/**
	 * Get if the boost rule is active or not
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_active( $context = 'view' ) {
		return $this->get_prop( 'active', $context );
	}

	/**
	 * Get boost name.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_name( $context = 'view' ) {
		return $this->get_prop( 'name', $context );
	}

	/**
	 * Get the boost value
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return float
	 */
	public function get_boost( $context = 'view' ) {
		return $this->get_prop( 'boost', $context );
	}

	/**
	 * Get the check for search terms
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_enable_for_terms( $context = 'view' ) {
		return $this->get_prop( 'enable_for_terms', $context );
	}

	/**
	 * Get the check terms type
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_check_term_type( $context = 'view' ) {
		return $this->get_prop( 'check_term_type', $context );
	}

	/**
	 * Get the search terms list
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_terms( $context = 'view' ) {
		return $this->get_prop( 'terms', $context );
	}

	/**
	 * Get the conditions
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 */
	public function get_conditions( $context = 'view' ) {
		return $this->get_prop( 'conditions', $context );
	}

	/**
	 * Get how the conditions will be checked
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_validation_method( $context = 'view' ) {
		return $this->get_prop( 'validation_method', $context );
	}

	/**
	 * Prefix for action and filter hooks on data.
	 *
	 * @return string
	 */
	protected function get_hook_prefix() {
		return $this->object_type . '_get_';
	}

	/**
	 * Return a list of search terms
	 *
	 * @return array
	 */
	public function get_terms_list() {
		$terms = $this->get_terms();

		return array_map( 'trim', explode( ',', $terms ) );
	}

	/**
	 * Check if the searched terms match with the terms configurated in the rule
	 *
	 * @param string $searched_term The customer search.
	 *
	 * @return bool
	 */
	public function match_with_searched_term( $searched_term ) {
		$match = true;
		if ( 'yes' === $this->get_enable_for_terms() ) {
			$match         = false;
			$check_mode    = $this->get_check_term_type();
			$terms         = $this->get_terms_list();
			$searched_term = strtolower( trim( $searched_term ) );
			foreach ( $terms as $term ) {
				$term = strtolower( $term );

				if ( 'exact' === $check_mode ) {
					if ( $term === $searched_term ) {
						$match = true;
						break;
					}
				} else {
					if ( function_exists( 'str_contains' ) && str_contains( $term, $searched_term ) ) {
						$match = true;
						break;
					} elseif ( strpos( $term,$searched_term ) !== false ) {
						$match = true;
						break;
					}
				}
			}
		}

		return $match;
	}

	/**
	 * Check if a result set can be boosted
	 *
	 * @param array $result The result set.
	 *
	 * @return bool
	 */
	public function check_conditions( $result ) {
		$is_valid   = true;
		$conditions = $this->get_conditions();
		$check      = $this->get_validation_method();
		if ( count( $conditions ) > 0 ) {
			$is_valid = false;
			foreach ( $conditions as $condition ) {
				$condition_for    = $condition['condition_config']['condition_for'];
				$function_to_call = 'is_valid_' . $condition_for . '_condition';
				if ( is_callable( array( $this, $function_to_call ) ) ) {
					$is_valid = $this->$function_to_call( $condition, $result );
				}

				$is_valid = apply_filters( 'ywcas_boost_rule_condition_is_valid', $is_valid, $condition, $result, $this );

				if ( 'and' === $check && ! $is_valid ) {
					break;
				} elseif ( 'or' === $check && $is_valid ) {
					break;
				}
			}
		}

		return $is_valid;
	}

	/**
	 * Check if the condition on categories is valid
	 *
	 * @param array $condition The single condition to check.
	 * @param array $result The search result set.
	 *
	 * @return bool
	 */
	public function is_valid_product_cat_condition( $condition, $result ) {
		$condition_type = $condition['condition_config']['condition_type'];
		if ( ! is_array( $result['parent_category'] ) ) {
			return 'is' === $condition_type;
		}
		$categories_set = array_map( 'intval', $condition['product_cat'] );
		$product_cat    = array_map( 'intval', $result['parent_category'] );
		$check          = count( array_intersect( $categories_set, $product_cat ) ) > 0;
		return 'is' === $condition_type ? $check : ! $check;
	}

	/**
	 * Check if the condition on tag is valid
	 *
	 * @param array $condition The single condition to check.
	 * @param array $result The search result set.
	 *
	 * @return bool
	 */
	public function is_valid_product_tag_condition( $condition, $result ) {
		$condition_type = $condition['condition_config']['condition_type'];
		if ( ! is_array( $result['tags'] ) ) {
			return 'is' !== $condition_type;
		}
		$tag_set = array_map( 'intval', $condition['product_tag'] );

		$product_tag = array_map( 'intval', $result['tags'] );
		$check       = count( array_intersect( $tag_set, $product_tag ) ) > 0;

		return 'is' === $condition_type ? $check : ! $check;
	}

	/**
	 * Check if the condition on product stock status is valid
	 *
	 * @param array $condition The single condition to check.
	 * @param array $result The search result set.
	 *
	 * @return bool
	 */
	public function is_valid_product_stock_status_condition( $condition, $result ) {
		$status_to_check = $condition['stock_status'];
		$condition_type  = $condition['condition_config']['condition_type'];
		$product_status  = $result['instock'] ? 'instock' : 'outofstock';
		$check           = $status_to_check === $product_status;
		return 'is' === $condition_type ? $check : ! $check;
	}

	/**
	 * Check if the condition on product price is valid
	 *
	 * @param array $condition The single condition to check.
	 * @param array $result The search result set.
	 *
	 * @return bool
	 */
	public function is_valid_product_price_condition( $condition, $result ) {

		$condition_type = $condition['condition_config']['condition_type'];
		$min_price      = wc_format_decimal( $result['min_price'], wc_get_price_decimals() );
		$max_price      = wc_format_decimal( $result['max_price'], wc_get_price_decimals() );
		$min_price_set  = wc_format_decimal( $condition['product_price']['min_price'], wc_get_price_decimals() );
		$max_price_set  = isset( $condition['product_price']['max_price'] ) ?? wc_format_decimal( $condition['product_price']['max_price'], wc_get_price_decimals() );

		switch ( $condition_type ) {
			case 'in-range':
			case 'not-in-range':
				$check = $min_price >= $min_price_set && $min_price <= $max_price_set;
				$check = 'not-in-range' === $condition_type ? ! $check : $check;
				break;
			case 'lower':
				$check = $min_price <= $min_price_set;
				break;
			default:
				$check = $min_price >= $min_price_set;
				break;
		}

		return $check;
	}

}
