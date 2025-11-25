<?php
/**
 * Class YITH_YWAR_Review_For_Discounts_Discount
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Review_For_Discounts_Discount' ) ) {
	/**
	 * Class YITH_YWAR_Review_For_Discounts_Discount
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts
	 */
	class YITH_YWAR_Review_For_Discounts_Discount extends YITH_YWAR_Data {

		/**
		 * The ID
		 *
		 * @var int
		 */
		protected $id;

		/**
		 * The data
		 *
		 * @var array
		 */
		protected $data = array(
			'title'                       => '',
			'trigger'                     => 'single',
			'trigger_product_ids'         => array(),
			'trigger_product_categories'  => array(),
			'trigger_threshold'           => 2,
			'trigger_enable_notify'       => 'no',
			'trigger_threshold_notify'    => 1,
			'discount_type'               => 'percent',
			'amount'                      => 0,
			'free_shipping'               => 'no',
			'expiry_days'                 => 0,
			'funds_amount'                => 0,
			'minimum_amount'              => '',
			'maximum_amount'              => '',
			'individual_use'              => 'no',
			'exclude_sale_items'          => 'no',
			'product_ids'                 => array(),
			'excluded_product_ids'        => array(),
			'product_categories'          => array(),
			'excluded_product_categories' => array(),
		);

		/**
		 * The object type
		 *
		 * @var string
		 */
		protected $object_type = 'discount';

		/**
		 * Cache group.
		 *
		 * @var string
		 */
		protected $cache_group = 'ywar_discounts';

		/**
		 * YITH_YWAR_Review_For_Discounts_Discount constructor.
		 *
		 * @param int|YITH_YWAR_Review_For_Discounts_Discount|WP_Post $discount The object.
		 *
		 * @return void
		 * @throws Exception If passed review is invalid.
		 * @since  2.0.0
		 */
		public function __construct( $discount = 0 ) {
			parent::__construct( $discount );

			$this->data_store = WC_Data_Store::load( 'yith-ywar-discount' );

			if ( is_numeric( $discount ) && $discount > 0 ) {
				$this->set_id( $discount );
			} elseif ( $discount instanceof self ) {
				$this->set_id( absint( $discount->get_id() ) );
			} elseif ( ! empty( $discount->ID ) ) {
				$this->set_id( absint( $discount->ID ) );
			} else {
				$this->set_object_read( true );
			}

			if ( $this->get_id() > 0 ) {
				$this->data_store->read( $this );
			}
		}

		/*
		|--------------------------------------------------------------------------
		| CRUD Getters
		|--------------------------------------------------------------------------
		|
		| Functions for getting review data.
		*/

		/**
		 * Return the title
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_title( string $context = 'view' ) {
			return $this->get_prop( 'title', $context );
		}

		/**
		 * Return the trigger
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_trigger( string $context = 'view' ) {
			return $this->get_prop( 'trigger', $context );
		}

		/**
		 * Return the trigger_product_ids
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_trigger_product_ids( string $context = 'view' ) {
			return $this->get_prop( 'trigger_product_ids', $context );
		}

		/**
		 * Return the trigger_product_categories
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_trigger_product_categories( string $context = 'view' ) {
			return $this->get_prop( 'trigger_product_categories', $context );
		}

		/**
		 * Return the trigger_threshold
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_trigger_threshold( string $context = 'view' ) {
			return $this->get_prop( 'trigger_threshold', $context );
		}

		/**
		 * Return the trigger_enable_notify
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_trigger_enable_notify( string $context = 'view' ) {
			return $this->get_prop( 'trigger_enable_notify', $context );
		}

		/**
		 * Return the trigger_threshold_notify
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_trigger_threshold_notify( string $context = 'view' ) {
			return $this->get_prop( 'trigger_threshold_notify', $context );
		}

		/**
		 * Return the discount_type
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_discount_type( string $context = 'view' ) {
			return $this->get_prop( 'discount_type', $context );
		}

		/**
		 * Return the amount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return float
		 * @since  2.0.0
		 */
		public function get_amount( string $context = 'view' ) {
			return $this->get_prop( 'amount', $context );
		}

		/**
		 * Return the free_shipping
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_free_shipping( string $context = 'view' ) {
			return $this->get_prop( 'free_shipping', $context );
		}

		/**
		 * Return the expiry_days
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_expiry_days( string $context = 'view' ) {
			return $this->get_prop( 'expiry_days', $context );
		}

		/**
		 * Return the funds_amount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return float
		 * @since  2.0.0
		 */
		public function get_funds_amount( string $context = 'view' ) {
			return $this->get_prop( 'funds_amount', $context );
		}

		/**
		 * Return the minimum_amount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return float
		 * @since  2.0.0
		 */
		public function get_minimum_amount( string $context = 'view' ) {
			return $this->get_prop( 'minimum_amount', $context );
		}

		/**
		 * Return the maximum_amount
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return float
		 * @since  2.0.0
		 */
		public function get_maximum_amount( string $context = 'view' ) {
			return $this->get_prop( 'maximum_amount', $context );
		}

		/**
		 * Return the individual_use
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_individual_use( string $context = 'view' ) {
			return $this->get_prop( 'individual_use', $context );
		}

		/**
		 * Return the exclude_sale_items
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_exclude_sale_items( string $context = 'view' ) {
			return $this->get_prop( 'exclude_sale_items', $context );
		}

		/**
		 * Return the product_ids
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_product_ids( string $context = 'view' ) {
			return $this->get_prop( 'product_ids', $context );
		}

		/**
		 * Return the excluded_product_ids
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_excluded_product_ids( string $context = 'view' ) {
			return $this->get_prop( 'excluded_product_ids', $context );
		}

		/**
		 * Return the product_categories
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_product_categories( string $context = 'view' ) {
			return $this->get_prop( 'product_categories', $context );
		}

		/**
		 * Return the creation date
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_excluded_product_categories( string $context = 'view' ) {
			return $this->get_prop( 'excluded_product_categories', $context );
		}

		/*
		|--------------------------------------------------------------------------
		| CRUD Setters
		|--------------------------------------------------------------------------
		|
		| Functions for getting review data.
		*/

		/**
		 * Set the title
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_title( string $value ) {
			$this->set_prop( 'title', $value );
		}

		/**
		 * Set the trigger
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_trigger( string $value ) {
			$this->set_prop( 'trigger', $value );
		}

		/**
		 * Set the trigger_product_ids
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_trigger_product_ids( array $value ) {
			$this->set_prop( 'trigger_product_ids', array_filter( wp_parse_id_list( $value ) ) );
		}

		/**
		 * Set the trigger_product_categories
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_trigger_product_categories( array $value ) {
			$this->set_prop( 'trigger_product_categories', array_filter( wp_parse_id_list( $value ) ) );
		}

		/**
		 * Set the trigger_threshold
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_trigger_threshold( int $value ) {
			$this->set_prop( 'trigger_threshold', absint( $value ) );
		}

		/**
		 * Set the trigger_enable_notify
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_trigger_enable_notify( string $value ) {
			$this->set_prop( 'trigger_enable_notify', $value );
		}

		/**
		 * Set the trigger_threshold_notify
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_trigger_threshold_notify( int $value ) {
			$this->set_prop( 'trigger_threshold_notify', absint( $value ) );
		}

		/**
		 * Set the discount_type
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_discount_type( string $value ) {
			$this->set_prop( 'discount_type', $value );
		}

		/**
		 * Set the amount
		 *
		 * @param float $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_amount( $value ) {
			$this->set_prop( 'amount', wc_format_decimal( $value ) );
		}

		/**
		 * Set the free_shipping value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_free_shipping( string $value ) {
			$this->set_prop( 'free_shipping', $value );
		}

		/**
		 * Set the expiry_days value
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_expiry_days( int $value ) {
			$this->set_prop( 'expiry_days', $value );
		}

		/**
		 * Set the funds_amount value
		 *
		 * @param float $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_funds_amount( $value ) {
			$this->set_prop( 'funds_amount', wc_format_decimal( $value ) );
		}

		/**
		 * Set the minimum_amount
		 *
		 * @param float $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_minimum_amount( $value ) {
			$this->set_prop( 'minimum_amount', wc_format_decimal( $value ) );
		}

		/**
		 * Set the maximum_amount value
		 *
		 * @param float $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_maximum_amount( $value ) {
			$this->set_prop( 'maximum_amount', wc_format_decimal( $value ) );
		}

		/**
		 * Set the individual_use value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_individual_use( string $value ) {
			$this->set_prop( 'individual_use', $value );
		}

		/**
		 * Set the exclude_sale_items value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_exclude_sale_items( string $value ) {
			$this->set_prop( 'exclude_sale_items', $value );
		}

		/**
		 * Set the product_ids value
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_product_ids( array $value ) {
			$this->set_prop( 'product_ids', array_filter( wp_parse_id_list( $value ) ) );
		}

		/**
		 * Set the excluded_product_ids value
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_excluded_product_ids( array $value ) {
			$this->set_prop( 'excluded_product_ids', array_filter( wp_parse_id_list( $value ) ) );
		}

		/**
		 * Set the product_categories value
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_product_categories( array $value ) {
			$this->set_prop( 'product_categories', array_filter( wp_parse_id_list( $value ) ) );
		}

		/**
		 * Set the excluded_product_categories
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_excluded_product_categories( array $value ) {
			$this->set_prop( 'excluded_product_categories', array_filter( wp_parse_id_list( $value ) ) );
		}
	}
}
