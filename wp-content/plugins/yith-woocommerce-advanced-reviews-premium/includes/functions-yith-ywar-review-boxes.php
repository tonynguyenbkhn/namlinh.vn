<?php
/**
 * Review Boxes Functions
 *
 * @package YITH\AdvancedReviews
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Get the review box object.
 *
 * @param int|WP_Post|YITH_YWAR_Review_Box|false $review_box The review.
 *
 * @return YITH_YWAR_Review_Box|false false on failure.
 * @since  2.0.0
 */
function yith_ywar_get_review_box( $review_box = false ) {
	global $post;

	if ( false === $review_box && is_a( $post, 'WP_Post' ) && get_post_type( $post ) === YITH_YWAR_Post_Types::BOXES ) {
		$box_id = absint( $post->ID );
	} elseif ( is_numeric( $review_box ) ) {
		$box_id = $review_box;
	} elseif ( $review_box instanceof YITH_YWAR_Review_Box ) {
		$box_id = $review_box->get_id();
	} elseif ( ! empty( $review_box->ID ) ) {
		$box_id = $review_box->ID;
	} else {
		$box_id = false;
	}

	if ( ! $box_id ) {
		return false;
	}

	try {
		$review_box = new YITH_YWAR_Review_Box( $box_id );

		return apply_filters( 'yith_ywar_review_box_object', $review_box );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );

		return false;
	}
}

/**
 * Retrieve review boxes
 *
 * @param array $args The arguments.
 *
 * @return array|false|YITH_YWAR_Review_Box[]
 * @since 2.0.0
 */
function yith_ywar_get_review_boxes( array $args = array() ) {
	try {
		/**
		 * The Review Data Store
		 *
		 * @var YITH_YWAR_Review_Box_Data_Store $data_store
		 */
		$data_store = WC_Data_Store::load( 'yith-review-box' );

		return $data_store->query( $args );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );

		return false;
	}
}

/**
 * Get current review box
 *
 * @param WC_Product|bool $product The current product.
 *
 * @return false|YITH_YWAR_Review_Box
 * @since  2.0.0
 */
function yith_ywar_get_current_review_box( $product ) {

	$default_box_id = get_option( 'yith-ywar-default-box-id' );

	if ( $product ) {

		if ( $product->is_virtual() ) {
			// Search rules on vor virtual products.
			$per_virtual = yith_ywar_get_review_boxes(
				array(
					'posts_per_page' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'   => 'ywar_review_box_show_on',
							'value' => 'virtual',
						),
						array(
							'key'   => 'ywar_review_box_active',
							'value' => 'YES',
						),
					),
					'order_by'       => 'ID',
					'order'          => 'DESC',
				)
			);
		}

		if ( ! empty( $per_virtual ) ) {
			// In case of multiple rules for the same product we will use only the most recent one.
			return reset( $per_virtual );
		}

		// Search rules on product level.
		$per_product = yith_ywar_get_review_boxes(
			array(
				'posts_per_page' => -1,
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'ywar_review_box_show_on',
						'value' => 'products',
					),
					array(
						'key'     => 'ywar_review_box_product_ids',
						'value'   => sprintf( ':"%s";', $product->get_id() ),
						'compare' => 'LIKE',
					),
					array(
						'key'   => 'ywar_review_box_active',
						'value' => 'YES',
					),
				),
				'order_by'       => 'ID',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $per_product ) ) {
			// In case of multiple rules for the same product we will use only the most recent one.
			return reset( $per_product );
		}

		// Search rules on category level.
		$categories = $product->get_category_ids();
		$cat_query  = array(
			'relation' => 'OR',
		);

		foreach ( $categories as $cat_id ) {
			$cat_query[] = array(
				'key'     => 'ywar_review_box_category_ids',
				'value'   => sprintf( ':"%s";', $cat_id ),
				'compare' => 'LIKE',
			);
		}

		$per_category = yith_ywar_get_review_boxes(
			array(
				'posts_per_page' => -1,
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					'relation' => 'AND',
					$cat_query,
					array(
						'key'   => 'ywar_review_box_show_on',
						'value' => 'categories',
					),
					array(
						'key'   => 'ywar_review_box_active',
						'value' => 'YES',
					),
				),
				'order_by'       => 'ID',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $per_category ) ) {
			// In case of multiple rules for the same category we will use only the most recent one.
			return reset( $per_category );
		}

		// Search rules on tag level.
		$tags      = $product->get_tag_ids();
		$tag_query = array(
			'relation' => 'OR',
		);

		foreach ( $tags as $tag_id ) {
			$tag_query[] = array(
				'key'     => 'ywar_review_box_tag_ids',
				'value'   => sprintf( ':"%s";', $tag_id ),
				'compare' => 'LIKE',
			);
		}

		$per_tag = yith_ywar_get_review_boxes(
			array(
				'posts_per_page' => -1,
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					'relation' => 'AND',
					$tag_query,
					array(
						'key'   => 'ywar_review_box_show_on',
						'value' => 'tags',
					),
					array(
						'key'   => 'ywar_review_box_active',
						'value' => 'YES',
					),
				),
				'order_by'       => 'ID',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $per_tag ) ) {
			// In case of multiple rules for the same tag we will use only the most recent one.
			return reset( $per_tag );
		}

		$global = yith_ywar_get_review_boxes(
			array(
				'post__not_in'   => array( $default_box_id ),
				'posts_per_page' => -1,
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'ywar_review_box_show_on',
						'value' => 'all',
					),
					array(
						'key'   => 'ywar_review_box_active',
						'value' => 'YES',
					),
				),
			)
		);

		if ( ! empty( $global ) ) {
			// In case of multiple global rules we will use only the most recent one.
			return reset( $global );
		}
	}

	// If there's no valid rule we will use the default one.
	return yith_ywar_get_review_box( $default_box_id );
}

/**
 * Get existing review criteria
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_retrieve_criteria(): array {
	$criteria = array();
	$query    = get_terms(
		array(
			'taxonomy'   => YITH_YWAR_Post_Types::CRITERIA_TAX,
			'hide_empty' => false,
		)
	);

	if ( ! empty( $query ) ) {
		foreach ( $query as $term ) {
			$criteria[ $term->term_id ] = $term->name;
		}
	}

	return $criteria;
}
