<?php
/**
 * Class YITH_YWAR_Review
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Review' ) ) {
	/**
	 * Class YITH_YWAR_Review
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Review extends YITH_YWAR_Data {

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
			'content'                     => '',
			'post_parent'                 => 0,
			'comment_id'                  => 0,
			'parent_comment_id'           => 0,
			'rating'                      => 0,
			'multi_rating'                => array(),
			'product_id'                  => 0,
			'status'                      => 'ywar-pending',
			'votes'                       => array(),
			'upvotes_count'               => 0,
			'downvotes_count'             => 0,
			'inappropriate_list'          => array(),
			'inappropriate_count'         => 0,
			'helpful'                     => 'no',
			'featured'                    => 'no',
			'verified_owner'              => 'no',
			'stop_reply'                  => 'no',
			'in_reply_of'                 => 0,
			'review_user_id'              => 0,
			'review_author'               => '',
			'review_author_email'         => '',
			'review_author_custom_avatar' => '',
			'review_author_IP'            => '',
			'review_author_country'       => '',
			'review_edit_blocked'         => 'no',
			'thumb_ids'                   => array(),
			'guest_cookie'                => '',
			'date_created'                => null,
			'date_modified'               => null,
		);

		/**
		 * The object type
		 *
		 * @var string
		 */
		protected $object_type = 'review';

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
		protected $cache_group = 'ywar_reviews';

		/**
		 * YITH_YWAR_Review constructor.
		 *
		 * @param int|YITH_YWAR_Review|WP_Post $review The object.
		 *
		 * @return void
		 * @throws Exception If passed review is invalid.
		 * @since  2.0.0
		 */
		public function __construct( $review = 0 ) {
			parent::__construct( $review );

			$this->data_store = WC_Data_Store::load( 'yith-review' );

			if ( is_numeric( $review ) && $review > 0 ) {
				$this->set_id( $review );
			} elseif ( $review instanceof self ) {
				$this->set_id( absint( $review->get_id() ) );
			} elseif ( ! empty( $review->ID ) ) {
				$this->set_id( absint( $review->ID ) );
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
		 * Return the content
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_content( string $context = 'view' ) {
			return $this->get_prop( 'content', $context );
		}

		/**
		 * Return the post_parent
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_post_parent( string $context = 'view' ) {
			return $this->get_prop( 'post_parent', $context );
		}

		/**
		 * Return the product ID
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_product_id( string $context = 'view' ) {
			return $this->get_prop( 'product_id', $context );
		}

		/**
		 * Return the comment ID
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_comment_id( string $context = 'view' ) {
			return $this->get_prop( 'comment_id', $context );
		}

		/**
		 * Return the parent comment ID
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_parent_comment_id( string $context = 'view' ) {
			return $this->get_prop( 'parent_comment_id', $context );
		}

		/**
		 * Return the rating
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_rating( string $context = 'view' ) {
			return $this->get_prop( 'rating', $context );
		}

		/**
		 * Return the multi rating
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int[]
		 * @since  2.0.0
		 */
		public function get_multi_rating( string $context = 'view' ) {
			return $this->get_prop( 'multi_rating', $context );
		}

		/**
		 * Return the status
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_status( string $context = 'view' ) {
			return $this->get_prop( 'status', $context );
		}

		/**
		 * Return the votes
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_votes( string $context = 'view' ) {
			return $this->get_prop( 'votes', $context );
		}

		/**
		 * Return the upvotes count
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_upvotes_count( string $context = 'view' ) {
			return $this->get_prop( 'upvotes_count', $context );
		}

		/**
		 * Return the downvotes count
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_downvotes_count( string $context = 'view' ) {
			return $this->get_prop( 'downvotes_count', $context );
		}

		/**
		 * Return the inappropriate list
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_inappropriate_list( string $context = 'view' ) {
			return $this->get_prop( 'inappropriate_list', $context );
		}

		/**
		 * Return the inappropriate count
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_inappropriate_count( string $context = 'view' ) {
			return $this->get_prop( 'inappropriate_count', $context );
		}

		/**
		 * Return the helpful value
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_helpful( string $context = 'view' ) {
			return $this->get_prop( 'helpful', $context );
		}

		/**
		 * Return the featured value
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_featured( string $context = 'view' ) {
			return $this->get_prop( 'featured', $context );
		}

		/**
		 * Return the verified_owner value
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.1.0
		 */
		public function get_verified_owner( string $context = 'view' ) {
			return $this->get_prop( 'verified_owner', $context );
		}

		/**
		 * Return the stop reply value
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_stop_reply( string $context = 'view' ) {
			return $this->get_prop( 'stop_reply', $context );
		}

		/**
		 * Return the in reply of value
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_in_reply_of( string $context = 'view' ) {
			return $this->get_prop( 'in_reply_of', $context );
		}

		/**
		 * Return the review user ID
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function get_review_user_id( string $context = 'view' ) {
			return $this->get_prop( 'review_user_id', $context );
		}

		/**
		 * Return the review author
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_review_author( string $context = 'view' ) {
			return $this->get_prop( 'review_author', $context );
		}

		/**
		 * Return the review author email
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_review_author_email( string $context = 'view' ) {
			return $this->get_prop( 'review_author_email', $context );
		}

		/**
		 * Return the review author custom avatar
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_review_author_custom_avatar( string $context = 'view' ) {
			return $this->get_prop( 'review_author_custom_avatar', $context );
		}

		/**
		 * Return the review author IP
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_review_author_IP( string $context = 'view' ) {
			return $this->get_prop( 'review_author_IP', $context );
		}

		/**
		 * Return the review author country
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.1.0
		 */
		public function get_review_author_country( string $context = 'view' ) {
			return $this->get_prop( 'review_author_country', $context );
		}

		/**
		 * Return the review edit blocked status
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_review_edit_blocked( string $context = 'view' ) {
			return $this->get_prop( 'review_edit_blocked', $context );
		}

		/**
		 * Return the thumb IDs
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_thumb_ids( string $context = 'view' ) {
			return $this->get_prop( 'thumb_ids', $context );
		}

		/**
		 * Return the guest cookie
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_guest_cookie( string $context = 'view' ) {
			return $this->get_prop( 'guest_cookie', $context );
		}

		/**
		 * Return the creation date
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 * @since  2.0.0
		 */
		public function get_date_created( string $context = 'view' ) {
			return $this->get_prop( 'date_created', $context );
		}

		/**
		 * Return the modification date
		 *
		 * @param string $context What the value is for. Valid values are view and edit.
		 *
		 * @return WC_DateTime|NULL object if the date is set or null if there is no date.
		 * @since  2.0.0
		 */
		public function get_date_modified( string $context = 'view' ) {
			return $this->get_prop( 'date_modified', $context );
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
		 * Set the content
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_content( string $value ) {
			$this->set_prop( 'content', $value );
		}

		/**
		 * Set the post_parent
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_post_parent( int $value ) {
			$this->set_prop( 'post_parent', $value );
		}

		/**
		 * Set the product_id
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_product_id( int $value ) {
			$this->set_prop( 'product_id', absint( $value ) );
		}

		/**
		 * Set the comment_id
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_comment_id( int $value ) {
			$this->set_prop( 'comment_id', absint( $value ) );
		}

		/**
		 * Set the parent_comment_id
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_parent_comment_id( int $value ) {
			$this->set_prop( 'parent_comment_id', absint( $value ) );
		}

		/**
		 * Set the rating
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_rating( $value ) {
			$this->set_prop( 'rating', $value );
		}

		/**
		 * Set the multi rating
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_multi_rating( array $value ) {
			$this->set_prop( 'multi_rating', $value );
		}

		/**
		 * Set the status
		 *
		 * @param string $status_to The value to set.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function set_status( string $status_to ): array {
			$statuses    = array_merge( array_keys( yith_ywar_get_review_statuses() ), array( 'trash', 'auto-draft' ) );
			$status_to   = 'ywar-' === substr( $status_to, 0, 5 ) ? substr( $status_to, 5 ) : $status_to;
			$status_to   = in_array( $status_to, $statuses, true ) ? $status_to : 'ywar-pending';
			$status_from = $this->get_status();

			if ( true === $this->object_read && $status_to !== $status_from ) {
				$this->status_transition = array(
					'from' => ! empty( $this->status_transition['from'] ) ? $this->status_transition['from'] : $status_from,
					'to'   => $status_to,
				);
			}

			$this->set_prop( 'status', $status_to );

			return array(
				'from' => $status_from,
				'to'   => $status_to,
			);
		}

		/**
		 * Set the votes
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_votes( array $value ) {
			$this->set_prop( 'votes', $value );
		}

		/**
		 * Set the upvotes_count
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_upvotes_count( int $value ) {
			$this->set_prop( 'upvotes_count', absint( $value ) );
		}

		/**
		 * Set the downvotes_count
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_downvotes_count( int $value ) {
			$this->set_prop( 'downvotes_count', absint( $value ) );
		}

		/**
		 * Set the inappropriate_list
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_inappropriate_list( array $value ) {
			$this->set_prop( 'inappropriate_list', $value );
		}

		/**
		 * Set the inappropriate_count
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_inappropriate_count( int $value ) {
			$this->set_prop( 'inappropriate_count', absint( $value ) );
		}

		/**
		 * Set the helpful value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_helpful( string $value ) {
			$this->set_prop( 'helpful', $value );
		}

		/**
		 * Set the featured value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_featured( string $value ) {
			$this->set_prop( 'featured', $value );
		}

		/**
		 * Set the verified_owner value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.1.0
		 */
		public function set_verified_owner( string $value ) {
			$this->set_prop( 'verified_owner', $value );
		}

		/**
		 * Set the stop_reply value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_stop_reply( string $value ) {
			$this->set_prop( 'stop_reply', $value );
		}

		/**
		 * Set the in_reply_of value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_in_reply_of( string $value ) {
			$this->set_prop( 'in_reply_of', $value );
		}

		/**
		 * Set the review_user_id
		 *
		 * @param int $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_review_user_id( int $value ) {
			$this->set_prop( 'review_user_id', absint( $value ) );
		}

		/**
		 * Set the review_author value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_review_author( string $value ) {
			$this->set_prop( 'review_author', $value );
		}

		/**
		 * Set the review_author_email value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_review_author_email( string $value ) {
			$this->set_prop( 'review_author_email', $value );
		}

		/**
		 * Set the review_author_custom_avatar value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_review_author_custom_avatar( string $value ) {
			$this->set_prop( 'review_author_custom_avatar', $value );
		}

		/**
		 * Set the review_author_IP value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_review_author_IP( string $value ) {
			$this->set_prop( 'review_author_IP', $value );
		}

		/**
		 * Set the review_author_country value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.1.0
		 */
		public function set_review_author_country( string $value ) {
			$this->set_prop( 'review_author_country', $value );
		}

		/**
		 * Set the review_edit_blocked value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_review_edit_blocked( string $value ) {
			$this->set_prop( 'review_edit_blocked', $value );
		}

		/**
		 * Set the thumb_ids list
		 *
		 * @param array $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_thumb_ids( array $value ) {
			$this->set_prop( 'thumb_ids', $value );
		}

		/**
		 * Set the guest_cookie value
		 *
		 * @param string $value The value to set.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_guest_cookie( string $value ) {
			$this->set_prop( 'guest_cookie', $value );
		}

		/**
		 * Set the date_created
		 *
		 * @param string|integer|null $value UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_date_created( $value ) {
			$this->set_date_prop( 'date_created', $value );
		}

		/**
		 * Set the date_modified
		 *
		 * @param string|integer|null $value UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_date_modified( $value ) {
			$this->set_date_prop( 'date_modified', $value );
		}

		/*
		|--------------------------------------------------------------------------
		| Conditionals
		|--------------------------------------------------------------------------
		|
		*/
		/**
		 * Checks the review status against a passed in status.
		 *
		 * @param array|string $status The status.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public function has_status( $status ): bool {
			return apply_filters( 'yith_ywar_review_has_status', ( is_array( $status ) && in_array( $this->get_status(), $status, true ) ) || $this->get_status() === $status, $this, $status );
		}

		/*
		|--------------------------------------------------------------------------
		| Other Useful Methods
		|--------------------------------------------------------------------------
		|
		*/
		/**
		 * Update status of review immediately.
		 *
		 * @param string $new_status The new status.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function update_status( string $new_status ) {

			if ( ! $this->id ) {
				return;
			}

			// Standardise status names.
			$new_status = 'ywar-' === substr( $new_status, 0, 5 ) ? substr( $new_status, 5 ) : $new_status;
			$results    = $this->set_status( $new_status );

			if ( $results ) {
				$this->save();
			}
		}

		/**
		 * Handle the status transition.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function status_transition() {
			$status_transition = $this->status_transition;

			// Reset status transition variable.
			$this->status_transition = false;

			if ( $status_transition ) {
				try {
					$status_to   = $status_transition['to'];
					$status_from = ! empty( $status_transition['from'] ) ? $status_transition['from'] : false;

					/**
					 * DO_ACTION: yith_ywar_review_status_{@status_to}
					 *
					 * Adds an action when the review gets a specific status.
					 *
					 * @param YITH_YWAR_Review $review The current review.
					 */
					do_action( 'yith_ywar_review_status_' . $status_to, $this );

					if ( $status_from ) {
						/**
						 * DO_ACTION: yith_ywar_review_status_{@status_from}_to_{@status_to}
						 *
						 * Adds an action when the review changes from a specific status to another specific status.
						 *
						 * @param YITH_YWAR_Review $review The current review.
						 */
						do_action( 'yith_ywar_review_status_' . $status_from . '_to_' . $status_to, $this );
						/**
						 * DO_ACTION: yith_ywar_review_status_changed
						 *
						 * Adds an action when the review changes status.
						 *
						 * @param YITH_YWAR_Review $review      The current review.
						 * @param string           $status_from The previous status.
						 * @param string           $status_to   The new status.
						 */
						do_action( 'yith_ywar_review_status_changed', $this, $status_from, $status_to );
					}
				} catch ( Exception $e ) {
					yith_ywar_error( sprintf( 'Status transition of review #%d errored!', $this->get_id() ) );
				}
			}
		}

		/**
		 * Save
		 *
		 * @return int
		 * @since  2.0.0
		 */
		public function save(): int {
			parent::save();
			$this->status_transition();

			return $this->get_id();
		}
	}
}
