<?php
/**
 * Class YITH_YWAR_Review_Box
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Review_Box' ) ) {
	/**
	 * Class YITH_YWAR_Review_Box
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Review_Box extends YITH_YWAR_Data {

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
			'title'                 => '',
			'show_on'               => 'all',
			'tag_ids'               => array(),
			'category_ids'          => array(),
			'product_ids'           => array(),
			'active'                => 'yes',
			'enable_multi_criteria' => 'no',
			'multi_criteria'        => array(),
			'show_elements'         => array(),
		);

		/**
		 * The object type
		 *
		 * @var string
		 */
		protected $object_type = 'review_box';

		/**
		 * Stores data about status changes so relevant hooks can be fired.
		 *
		 * @var bool|array
		 */
		protected $status_transition = false;

		/**
		 * Cache group.
		 *
		 * @var string
		 */
		protected $cache_group = 'ywar_review_boxes';

		/**
		 * YITH_YWAR_Review_Box constructor.
		 *
		 * @param int|YITH_YWAR_Review_Box|WP_Post $review_box The object.
		 *
		 * @return void
		 * @throws Exception If passed review is invalid.
		 * @since  2.0.0
		 */
		public function __construct( $review_box = 0 ) {
			parent::__construct( $review_box );

			$this->data_store = WC_Data_Store::load( 'yith-review-box' );

			if ( is_numeric( $review_box ) && $review_box > 0 ) {
				$this->set_id( $review_box );
			} elseif ( $review_box instanceof self ) {
				$this->set_id( absint( $review_box->get_id() ) );
			} elseif ( ! empty( $review_box->ID ) ) {
				$this->set_id( absint( $review_box->ID ) );
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
		 * Return the show_on
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_show_on( string $context = 'view' ) {
			return $this->get_prop( 'show_on', $context );
		}

		/**
		 * Return the tag_ids
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_tag_ids( string $context = 'view' ) {
			return $this->get_prop( 'tag_ids', $context );
		}

		/**
		 * Return the category_ids
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_category_ids( string $context = 'view' ) {
			return $this->get_prop( 'category_ids', $context );
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
		 * Return the active
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_active( string $context = 'view' ) {
			return $this->get_prop( 'active', $context );
		}

		/**
		 * Return the enable_multi_criteria
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_enable_multi_criteria( string $context = 'view' ) {
			return $this->get_prop( 'enable_multi_criteria', $context );
		}

		/**
		 * Return the multi_criteria
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_multi_criteria( string $context = 'view' ) {
			return $this->get_prop( 'multi_criteria', $context );
		}

		/**
		 * Return the show_elements
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_show_elements( string $context = 'view' ) {
			return $this->get_prop( 'show_elements', $context );
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
		 * Set the show_on
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_show_on( string $value ) {
			$this->set_prop( 'show_on', $value );
		}

		/**
		 * Set the tag_ids
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_tag_ids( array $value ) {
			$this->set_prop( 'tag_ids', $value );
		}

		/**
		 * Set the category_ids
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_category_ids( array $value ) {
			$this->set_prop( 'category_ids', $value );
		}

		/**
		 * Set the product_ids
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_product_ids( array $value ) {
			$this->set_prop( 'product_ids', $value );
		}

		/**
		 * Set the active
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_active( string $value ) {
			$this->set_prop( 'active', $value );
		}

		/**
		 * Set the enable_multi_criteria
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_enable_multi_criteria( string $value ) {
			$this->set_prop( 'enable_multi_criteria', $value );
		}

		/**
		 * Set the multi_criteria
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_multi_criteria( array $value ) {
			$this->set_prop( 'multi_criteria', $value );
		}

		/**
		 * Set the show_elements
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_show_elements( array $value ) {
			$this->set_prop( 'show_elements', $value );
		}

		/**
		 * Save
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function save(): int {
			parent::save();

			return $this->get_id();
		}
	}
}
