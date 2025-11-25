<?php
/**
 * Class YITH_YWAR_Sync
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Sync' ) ) {
	/**
	 * Class YITH_YWAR_Sync
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Sync {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * YITH_YWAR_Sync constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			add_action( 'yith_ywar_review_created', array( $this, 'on_review_update' ) );
			add_action( 'yith_ywar_review_updated', array( $this, 'on_review_update' ) );
			add_action( 'wp_trash_post', array( $this, 'on_review_trash' ) );
			add_action( 'untrash_post', array( $this, 'on_review_untrash' ) );
			add_action( 'before_delete_post', array( $this, 'on_review_delete' ) );
			add_action( 'yith_ywar_review_status_changed', array( $this, 'on_review_status_change' ) );
			add_action( 'edit_comment', array( $this, 'on_comment_update' ), 10, 2 );
			add_action( 'comment_unapproved_to_approved', array( $this, 'on_comment_status_change' ) );
			add_action( 'comment_approved_to_unapproved', array( $this, 'on_comment_status_change' ) );
			add_action( 'comment_approved_to_spam', array( $this, 'on_comment_status_change' ) );
			add_action( 'comment_spam_to_approved', array( $this, 'on_comment_status_change' ) );
			add_action( 'comment_spam_to_unapproved', array( $this, 'on_comment_status_change' ) );
			add_action( 'comment_unapproved_to_spam', array( $this, 'on_comment_status_change' ) );
			add_action( 'trashed_comment', array( $this, 'on_comment_trash' ) );
			add_action( 'untrashed_comment', array( $this, 'on_comment_untrash' ) );
			add_action( 'deleted_comment', array( $this, 'on_comment_delete' ) );
			add_action( 'yith_ywar_review_box_updated', array( $this, 'fetch_products_to_update' ) );
			add_action( 'yith_ywar_update_product_stats', array( $this, 'update_product_stats' ) );
		}

		/**
		 * Actions from YITH Reviews to WC Comments
		 */

		/**
		 * Update the comment on review update
		 *
		 * @param YITH_YWAR_Review $review The current review.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_review_update( YITH_YWAR_Review $review ) {
			$review_updated = get_transient( '_ywar_update_review_' . $review->get_id() );
			if ( $review_updated ) {
				delete_transient( '_ywar_update_review_' . $review->get_id() );

				return;
			}

			$comment_id = $review->get_comment_id();
			if ( $comment_id > 0 ) {

				$comment_data = array(
					'comment_ID'           => $comment_id,
					'comment_author'       => $review->get_review_author(),
					'comment_author_email' => $review->get_review_author_email(),
					'comment_content'      => $review->get_content(),
					'comment_author_url'   => '',
					'comment_type'         => 'review',
					'comment_approved'     => $this->convert_review_status( $review->get_status() ),
					'comment_meta'         => array(
						'rating' => $review->get_rating(),
					),
				);
				set_transient( '_ywar_update_review_' . $review->get_id(), $comment_id );
				wp_update_comment( $comment_data );
			} else {
				$comment_data = array(
					'comment_author'       => $review->get_review_author(),
					'comment_author_email' => $review->get_review_author_email(),
					'comment_author_IP'    => $review->get_review_author_IP(),
					'comment_content'      => $review->get_content(),
					'comment_author_url'   => '',
					'comment_type'         => 'review',
					'comment_parent'       => $review->get_parent_comment_id(),
					'comment_post_ID'      => $review->get_product_id(),
					'user_id'              => $review->get_review_user_id(),
					'comment_approved'     => $this->convert_review_status( $review->get_status() ),
					'comment_meta'         => array(
						'rating' => $review->get_rating(),
					),
				);
				$comment_id   = wp_insert_comment( $comment_data );
				$review->set_comment_id( $comment_id );
				$review->save();
			}
			$product = wc_get_product( $review->get_product_id() );

			if ( $product ) {
				yith_ywar_get_review_stats( $product, true );
			}
		}

		/**
		 * Trash comment if the review is moved to trash
		 *
		 * @param int $review_id The Review ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_review_trash( int $review_id ) {
			$review = yith_ywar_get_review( $review_id );
			if ( $review ) {
				wp_trash_comment( $review->get_comment_id() );
				$product = wc_get_product( $review->get_product_id() );
				if ( $product ) {
					yith_ywar_get_review_stats( $product, true );
				}
			}
		}

		/**
		 * Untrash comment if the review is restored
		 *
		 * @param int $review_id The Review ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_review_untrash( int $review_id ) {
			$review = yith_ywar_get_review( $review_id );
			if ( $review ) {
				wp_untrash_comment( $review->get_comment_id() );
				$product = wc_get_product( $review->get_product_id() );
				yith_ywar_get_review_stats( $product, true );
			}
		}

		/**
		 * Delete comment if the review is deleted
		 *
		 * @param int $review_id The Review ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_review_delete( int $review_id ) {
			$review = yith_ywar_get_review( $review_id );

			if ( $review ) {
				set_transient( '_ywar_deleted_comment_' . $review->get_comment_id(), $review_id );
				wp_delete_comment( $review->get_comment_id(), true );
			}
		}

		/**
		 * Sync review status change
		 *
		 * @param YITH_YWAR_Review $review The current review.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_review_status_change( YITH_YWAR_Review $review ) {
			$comment_id     = $review->get_comment_id();
			$status_updated = get_transient( '_ywar_update_review_status_' . $review->get_id() );

			if ( $status_updated ) {
				delete_transient( '_ywar_update_review_status_' . $review->get_id() );

				return;
			}
			set_transient( '_ywar_update_review_status_' . $review->get_id(), $comment_id );
		}

		/**
		 * Actions from WC Comments to YITH Reviews
		 */

		/**
		 * Update the review on comment update
		 *
		 * @param int   $comment_id The comment ID.
		 * @param array $data       The comment data.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_comment_update( int $comment_id, array $data ) {
			$reviews = yith_ywar_get_reviews(
				array(
					'fields'         => 'ids',
					'posts_per_page' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						array(
							'key'   => '_ywar_comment_id',
							'value' => $comment_id,
						),
					),
				)
			);
			$review  = yith_ywar_get_review( reset( $reviews ) );
			if ( $review && get_post( $review->get_id() ) ) {
				$review_updated = $review ? get_transient( '_ywar_update_review_' . $review->get_id() ) : false;
				if ( $review_updated ) {
					delete_transient( '_ywar_update_review_' . $review->get_id() );

					return;
				}
				
				set_transient( '_ywar_update_review_' . $review->get_id(), $comment_id );
				$rating = get_comment_meta( $comment_id, 'rating', true );
				$status = $this->convert_comment_status( (string) $data['comment_approved'] );
				$review->set_content( $data['comment_content'] );
				$review->set_review_author( $data['comment_author'] );
				$review->set_review_author_email( $data['comment_author_email'] );
				$review->set_rating( (int) $rating );
				$review->set_status( $status );
				$review->save();
			}
		}

		/**
		 * Update the review status on comment status change
		 *
		 * @param WP_Comment $comment The comment.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_comment_status_change( WP_Comment $comment ) {

			$reviews = yith_ywar_get_reviews(
				array(
					'fields'         => 'ids',
					'posts_per_page' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						array(
							'key'   => '_ywar_comment_id',
							'value' => $comment->comment_ID,
						),
					),
				)
			);
			$review  = yith_ywar_get_review( reset( $reviews ) );
			if ( $review && get_post( $review->get_id() ) ) {
				$review_updated = get_transient( '_ywar_update_review_' . $review->get_id() );
				$status_updated = get_transient( '_ywar_update_review_status_' . $review->get_id() );

				if ( $review_updated || $status_updated ) {
					delete_transient( '_ywar_update_review_' . $review->get_id() );
					delete_transient( '_ywar_update_review_status_' . $review->get_id() );

					return;
				}

				set_transient( '_ywar_update_review_' . $review->get_id(), $comment->comment_ID );
				set_transient( '_ywar_update_review_status_' . $review->get_id(), $comment->comment_ID );
				$status = $this->convert_comment_status( $comment->comment_approved );
				$review->update_status( $status );
			}
		}

		/**
		 * Trash review if the comment is moved to trash
		 *
		 * @param int $comment_id The comment ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_comment_trash( int $comment_id ) {
			$reviews = yith_ywar_get_reviews(
				array(
					'fields'         => 'ids',
					'posts_per_page' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						array(
							'key'   => '_ywar_comment_id',
							'value' => $comment_id,
						),
					),
				)
			);
			$review  = yith_ywar_get_review( reset( $reviews ) );
			if ( $review && get_post( $review->get_id() ) ) {
				$review->delete();
			}
		}

		/**
		 * Untrash review if the comment is restored
		 *
		 * @param int $comment_id The comment ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_comment_untrash( int $comment_id ) {
			$reviews = yith_ywar_get_reviews(
				array(
					'fields'         => 'ids',
					'post_status'    => 'trash',
					'posts_per_page' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						array(
							'key'   => '_ywar_comment_id',
							'value' => $comment_id,
						),
					),
				)
			);
			$review  = yith_ywar_get_review( reset( $reviews ) );
			if ( $review && get_post( $review->get_id() ) ) {
				$status = substr( $review->get_meta( '_wp_trash_meta_status' ), 5 );
				$review->update_status( $status );
			}
		}

		/**
		 * Delete review if the comment is deleted
		 *
		 * @param int $comment_id The comment ID.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_comment_delete( int $comment_id ) {
			$reviews        = yith_ywar_get_reviews(
				array(
					'fields'         => 'ids',
					'post_status'    => 'trash',
					'posts_per_page' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						array(
							'key'   => '_ywar_comment_id',
							'value' => $comment_id,
						),
					),
				)
			);
			$review         = yith_ywar_get_review( reset( $reviews ) );
			$review_deleted = get_transient( '_ywar_deleted_comment_' . $comment_id );

			if ( $review && ! $review_deleted ) {
				$review->delete( true );
			} else {
				delete_transient( '_ywar_deleted_comment_' . $comment_id );
			}
		}

		/**
		 * Actions triggered when a Review Box is updated
		 */

		/**
		 * Fetch products to update
		 *
		 * @param YITH_YWAR_Review_Box $review_box The review box.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function fetch_products_to_update( YITH_YWAR_Review_Box $review_box ) {
			if ( 'yes' === $review_box->get_active() && 'yes' === $review_box->get_enable_multi_criteria() ) {
				switch ( $review_box->get_show_on() ) {
					case 'products':
						$products = $review_box->get_product_ids();
						break;
					case 'categories':
						$products = wc_get_products(
							array(
								'limit'               => -1,
								'product_category_id' => $review_box->get_category_ids(),
								'return'              => 'ids',
							)
						);
						break;
					case 'tags':
						$products = wc_get_products(
							array(
								'limit'          => -1,
								'product_tag_id' => $review_box->get_tag_ids(),
								'return'         => 'ids',
							)
						);
						break;
					case 'virtual':
						$products = wc_get_products(
							array(
								'limit'   => -1,
								'virtual' => true,
								'return'  => 'ids',
							)
						);
						break;
					default:
						$products = wc_get_products(
							array(
								'limit'  => -1,
								'return' => 'ids',
							)
						);
				}

				if ( ! empty( $products ) ) {
					$grouped_products = array_chunk( $products, 10, false );
					$time             = 0;
					foreach ( $grouped_products as $group ) {
						wc()->queue()->schedule_single(
							time() + $time,
							'yith_ywar_update_product_stats',
							array( 'products' => $group ),
							'yith-ywar-update-product-stats'
						);
						$time += ( MINUTE_IN_SECONDS * 5 );
					}
				}
			}
		}

		/**
		 * Update product stats
		 *
		 * @param array $products List of Product IDs.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function update_product_stats( array $products ) {
			foreach ( $products as $product_id ) {
				$product = wc_get_product( $product_id );
				yith_ywar_get_review_stats( $product, true );
			}
		}

		/**
		 * Convert comment status
		 *
		 * @param string $status The comment status.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function convert_comment_status( string $status ): string {
			switch ( $status ) {
				case 'spam':
					$review_status = 'spam';
					break;
				case '1':
					$review_status = 'approved';
					break;
				default:
					$review_status = 'pending';
			}

			return $review_status;
		}

		/**
		 * Convert review status
		 *
		 * @param string $status The comment status.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function convert_review_status( string $status ): string {
			switch ( $status ) {
				case 'spam':
					$review_status = 'spam';
					break;
				case 'approved':
					$review_status = '1';
					break;
				default:
					$review_status = '0';
			}

			return $review_status;
		}
	}
}
