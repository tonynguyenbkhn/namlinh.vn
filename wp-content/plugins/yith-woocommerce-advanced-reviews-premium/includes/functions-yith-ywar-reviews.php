<?php
/**
 * General Function
 *
 * @package YITH\AdvancedReviews
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Return the list of review statuses
 *
 * @return array
 * @since  2.0.0
 */
function yith_ywar_get_review_statuses(): array {
	$statuses = array(
		'pending'  => _nx( 'Pending', 'Pending', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
		'reported' => _nx( 'Reported', 'Reported', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
		'approved' => _nx( 'Approved', 'Approved', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
		'spam'     => _nx( 'Spam', 'Spam', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
	);

	return $statuses;
}

/**
 * Check if review status is valid.
 *
 * @param string $status The status.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_is_a_review_status( string $status ): bool {
	$review_statuses = yith_ywar_get_review_statuses();

	return isset( $review_statuses[ $status ] );
}

/**
 * Get the review object.
 *
 * @param int|WP_Post|YITH_YWAR_Review|false $review The review.
 *
 * @return YITH_YWAR_Review|false false on failure.
 * @since  2.0.0
 */
function yith_ywar_get_review( $review = false ) {
	global $post;

	if ( false === $review && is_a( $post, 'WP_Post' ) && get_post_type( $post ) === YITH_YWAR_Post_Types::REVIEWS ) {
		$review_id = absint( $post->ID );
	} elseif ( is_numeric( $review ) ) {
		$review_id = $review;
	} elseif ( $review instanceof YITH_YWAR_Review ) {
		$review_id = $review->get_id();
	} elseif ( ! empty( $review->ID ) ) {
		$review_id = $review->ID;
	} else {
		$review_id = false;
	}

	if ( ! $review_id ) {
		return false;
	}

	try {
		$review = new YITH_YWAR_Review( $review_id );

		return apply_filters( 'yith_ywar_review_object', $review );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );

		return false;
	}
}

/**
 * Retrieve reviews
 *
 * @param array $args The arguments.
 *
 * @return array|false|object|YITH_YWAR_Review[]
 * @since 2.0.0
 */
function yith_ywar_get_reviews( array $args = array() ) {
	try {
		/**
		 * The Review Data Store
		 *
		 * @var YITH_YWAR_Review_Data_Store $data_store
		 */
		$data_store = WC_Data_Store::load( 'yith-review' );

		return $data_store->query( $args );
	} catch ( Exception $e ) {
		yith_ywar_error( $e->getMessage() );

		return false;
	}
}

/**
 * Check if has reviewed the product
 *
 * @param int    $product_id The product ID.
 * @param string $user_email The user email.
 * @param bool   $get_all    Should get all review statuses.
 * @param int    $min_value  Minimum review amount.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_user_has_commented( int $product_id, string $user_email, bool $get_all = false, int $min_value = 0 ): bool {

	if ( '' === $user_email ) {
		return false;
	}

	$reviews = yith_ywar_get_reviews(
		array(
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'post_parent'    => 0,
			'post_status'    => $get_all ? array( 'any', 'trash' ) : 'ywar-approved',
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_ywar_product_id',
					'value'   => $product_id,
					'compare' => '=',
				),
				array(
					'key'     => '_ywar_review_author_email',
					'value'   => $user_email,
					'compare' => '=',
				),
			),
		)
	);

	return count( $reviews ) > $min_value;
}

/**
 * Check user permissions
 *
 * @param string $permission The permission to check. Allowed values: multiple-reviews, title-reviews, edit-reviews, reply-reviews, vote-helpful, report-reviews.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_check_user_permissions( string $permission ): bool {
	return false !== array_search( $permission, yith_ywar_get_option( 'ywar_user_permission' ), true );
}

/**
 * Get review stats
 *
 * @param WC_Product $product      The product.
 * @param bool       $force        Should force the regeneration.
 *
 * @return array{
 * @type array       $ratings      {
 * @type array $1{
 * @type int         $count        The total reviews with this rating.
 * @type float       $perc         The percentage of reviews with this rating.
 *                                 }
 * @type array $2{
 * @type int         $count        The total reviews with this rating.
 * @type float       $perc         The percentage of reviews with this rating.
 *                                 }
 * @type array $3{
 * @type int         $count        The total reviews with this rating.
 * @type float       $perc         The percentage of reviews with this rating.
 *                                 }
 * @type array $4{
 * @type int         $count        The total reviews with this rating.
 * @type float       $perc         The percentage of reviews with this rating.
 *                                 }
 * @type array $5{
 * @type int         $count        The total reviews with this rating.
 * @type float       $perc         The percentage of reviews with this rating.
 *                                 }
 *                                 }
 * @type array       $multiratings The multi criteria available for this product
 * @type int         $total        The total reviews of the product.
 * @type array       $average      {
 * @type float       $rating       The average rating.
 * @type float       $perc         The the average rating in percentage.
 *                                 }
 * @type array       $attachments  The attachment IDs list.
 * @type array       $helpful      The IDs of the reviews marked as helpful.
 *                                 }
 */
function yith_ywar_get_review_stats( WC_Product $product, bool $force = false ): array {
	$review_stats = $product->get_meta( '_ywar_stats' );
	if ( ! $review_stats || $force ) {
		$review_stats = array(
			'ratings'      => array(
				'1' => array(
					'count' => 0,
					'perc'  => 0,
				),
				'2' => array(
					'count' => 0,
					'perc'  => 0,
				),
				'3' => array(
					'count' => 0,
					'perc'  => 0,
				),
				'4' => array(
					'count' => 0,
					'perc'  => 0,
				),
				'5' => array(
					'count' => 0,
					'perc'  => 0,
				),
			),
			'multiratings' => array(),
			'total'        => 0,
			'average'      => array(
				'rating' => 0,
				'perc'   => 0,
			),
			'attachments'  => array(),
			'helpful'      => array(),
		);
		$reviews      = yith_ywar_get_reviews(
			array(
				'post_status'    => 'ywar-approved',
				'posts_per_page' => -1,
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					array(
						'key'   => '_ywar_product_id',
						'value' => $product->get_id(),
					),
				),
			)
		);

		if ( ! empty( $reviews ) ) {
			$review_box    = yith_ywar_get_current_review_box( $product );
			$multi_enabled = 'yes' === $review_box->get_enable_multi_criteria() && ! empty( $review_box->get_multi_criteria() );
			$total_reviews = 0;
			$total_ratings = 0;
			$attachments   = array();
			$helpful       = 0;
			$multirating   = array();
			$count         = array(
				'1' => 0,
				'2' => 0,
				'3' => 0,
				'4' => 0,
				'5' => 0,
			);

			if ( $multi_enabled ) {
				$multirating = array_fill_keys( $review_box->get_multi_criteria(), 0 );
			}

			foreach ( $reviews as $review ) {
				if ( ! empty( $review->get_rating() ) ) {
					$rating = $review->get_rating();
					++$count[ $rating ];
					++$total_reviews;
					$total_ratings += $rating;
				}

				if ( ! empty( $review->get_thumb_ids() ) ) {
					$attachments[ $review->get_id() ] = array(
						'type'  => $review->get_post_parent() > 0 ? 'reply' : 'review',
						'media' => array_filter( $review->get_thumb_ids() ),
					);
				}

				if ( 'yes' === $review->get_helpful() ) {
					++$helpful;
				}

				if ( $multi_enabled ) {
					foreach ( $multirating as $key => $value ) {
						$multirating[ $key ] += isset( $review->get_multi_rating()[ $key ] ) ? $review->get_multi_rating()[ $key ] : $review->get_rating();
					}
				}
			}

			if ( $multi_enabled ) {
				foreach ( $multirating as $key => $value ) {
					$average             = $value > 0 ? round( ( $value / $total_reviews ), 1 ) : 0;
					$multirating[ $key ] = $average;
				}
			}

			$review_stats = array(
				'ratings'      => array(
					'1' => array(
						'count' => $count['1'],
						'perc'  => $total_reviews > 0 ? round( ( $count['1'] / $total_reviews ) * 100 ) : 0,
					),
					'2' => array(
						'count' => $count['2'],
						'perc'  => $total_reviews > 0 ? round( ( $count['2'] / $total_reviews ) * 100 ) : 0,
					),
					'3' => array(
						'count' => $count['3'],
						'perc'  => $total_reviews > 0 ? round( ( $count['3'] / $total_reviews ) * 100 ) : 0,
					),
					'4' => array(
						'count' => $count['4'],
						'perc'  => $total_reviews > 0 ? round( ( $count['4'] / $total_reviews ) * 100 ) : 0,
					),
					'5' => array(
						'count' => $count['5'],
						'perc'  => $total_reviews > 0 ? round( ( $count['5'] / $total_reviews ) * 100 ) : 0,
					),
				),
				'total'        => $total_reviews,
				'average'      => array(
					'rating' => $total_reviews > 0 ? round( ( $total_ratings / $total_reviews ), 1 ) : 0,
					'perc'   => $total_reviews > 0 ? ( round( ( $total_ratings / $total_reviews ) / 5, 1 ) * 100 ) : 0,
				),
				'multiratings' => $multirating,
				'attachments'  => $attachments,
				'helpful'      => $helpful,
			);
		}

		$product->update_meta_data( '_ywar_stats', $review_stats );
		$product->set_average_rating( $review_stats['average']['rating'] );
		$product->set_review_count( $review_stats['total'] );
		$product->save();
	}

	return $review_stats;
}

/**
 * Get the review content to display in the email
 *
 * @param YITH_YWAR_Review|bool $review        The review.
 * @param array                 $dummy_content The dummyc ontent.
 * @param string                $edit_url      The review edit url.
 * @param bool                  $is_report     Check if it is a reply.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_review_content_email( $review, array $dummy_content, string $edit_url, bool $is_report = false ): string {

	$product_id  = $review ? $review->get_product_id() : $dummy_content['product_id'];
	$review_text = $review ? $review->get_content() : $dummy_content['text'];
	$is_reply    = $review ? $review->get_post_parent() > 0 : isset( $dummy_content['is_reply'] );
	$to_approve  = $review ? 'pending' === $review->get_status() : 'no' === yith_ywar_get_option( 'ywar_review_autoapprove' );

	ob_start();
	?>
	<div class="yith-ywar-review-box<?php echo( $is_report ? ' report-box' : '' ); ?>">
		<?php if ( ! $is_reply && ! $is_report ) : ?>
			<?php
			$product      = wc_get_product( $product_id );
			$review_stats = yith_ywar_get_review_stats( $product );
			?>
			<table class="reviewed-product" cellspacing="0" cellpadding="6" border="1">
				<tbody>
				<tr>
					<td class="picture-column">
						<?php echo wp_kses_post( $product->get_image( array( 100, 100 ) ) ); ?>
					</td>
					<td class="title-column">
						<a class="product-name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo wp_kses_post( $product->get_name() ); ?></a>
						<span class="product-rating">
							<span class="stars"><?php yith_ywar_get_product_rating_email( $review_stats['average']['rating'] ); ?><span class="average"><?php echo esc_html( $review_stats['average']['rating'] ); ?></span></span>

							<span class="rating">
								<?php
								/* translators: %s number of reviews */
								echo wp_kses_post( sprintf( _nx( '%d review', '%d reviews', absint( $review_stats['total'] ), '[Global] Review counter', 'yith-woocommerce-advanced-reviews' ), absint( $review_stats['total'] ) ) );
								?>
							</span>
						</span>
					</td>
				</tr>
				</tbody>
			</table>
		<?php endif; ?>
		<div class="review-text">
			"<?php echo wp_kses_post( $review_text ); ?>"
		</div>
		<?php if ( $to_approve && ! $is_report ) : ?>
			<div class="approve-text">
				<?php
				$opening_tag = '<a href="' . $edit_url . '" target="_blank">';
				$closing_tag = '</a>';
				if ( $is_reply ) {
					/* translators: %1$s opening link tag %2$s closing link tag */
					printf( esc_html_x( '%1$sNote:%2$s The reply is not yet visible on your site. To approve it click %3$shere%4$s', '[Admin panel] Message displayed in the email when the reply needs to be approved', 'yith-woocommerce-advanced-reviews' ), '<span>', '</span>', wp_kses_post( $opening_tag ), wp_kses_post( $closing_tag ) );
				} else {
					/* translators: %1$s opening link tag %2$s closing link tag */
					printf( esc_html_x( '%1$sNote:%2$s The review is not yet visible on your site. To approve it click %3$shere%4$s', '[Admin panel] Message displayed in the email when the review needs to be approved', 'yith-woocommerce-advanced-reviews' ), '<span>', '</span>', wp_kses_post( $opening_tag ), wp_kses_post( $closing_tag ) );
				}
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Set the product rating in the email
 *
 * @param string $average The average rating.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_get_product_rating_email( string $average ) {
	$indexes     = explode( '.', $average );
	$half_star   = isset( $indexes[1] ) && intval( $indexes[1] ) < 5 ? 1 : 0;
	$full_stars  = intval( $indexes[0] ) + ( isset( $indexes[1] ) && intval( $indexes[1] ) >= 5 ? 1 : 0 );
	$empty_stars = 5 - $full_stars - $half_star;

	$stars = str_repeat( '<img src="' . YITH_YWAR_ASSETS_URL . '/images/rating-star-full.png" />', $full_stars );
	if ( 1 === $half_star ) {
		$stars .= '<img src="' . YITH_YWAR_ASSETS_URL . '/images/rating-star-half.png" />';
	}
	$stars .= str_repeat( '<img src="' . YITH_YWAR_ASSETS_URL . '/images/rating-star-empty.png" />', $empty_stars );

	echo wp_kses_post( $stars );
}

/**
 * Calculate average rating from multi-criteria
 *
 * @param array $multi_criteria Array of multiple ratings.
 *
 * @return int
 * @since  2.0.0
 */
function yith_ywar_calculate_avg_rating( array $multi_criteria ): int {
	return round( ( array_sum( $multi_criteria ) / count( $multi_criteria ) ), 0, PHP_ROUND_HALF_UP );
}

/**
 * Get user avatar
 *
 * @param YITH_YWAR_Review $review The current review.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_get_user_avatar( YITH_YWAR_Review $review ) {

	if ( 'initials' === yith_ywar_get_option( 'ywar_avatar_type' ) ) {

		$parts = explode( ' ', $review->get_review_author() );
		if ( count( $parts ) > 1 ) {
			$letters = substr( $parts[0], 0, 1 ) . substr( $parts[ array_key_last( $parts ) ], 0, 1 );
		} else {
			$letters = substr( $parts[0], 0, 1 );
		}

		$html = "<span>$letters</span>";
	} else {
		if ( '' !== $review->get_review_author_custom_avatar() ) {
			$url = $review->get_review_author_custom_avatar();
		} elseif ( yith_ywar_customize_my_account_enabled() ) {
			preg_match( "/src='([^']+)'/mi", get_avatar( $review->get_review_author_email() ), $match );
			$url = html_entity_decode( array_pop( $match ) );
		} else {
			$url = get_avatar_data( $review->get_review_author_email() )['url'];
		}

		$html = sprintf( '<img src="%s" />', esc_url( $url ) );
	}

	/**
	 * APPLY_FILTERS: yith_ywar_user_avatar_html
	 *
	 * User avatar HTML.
	 *
	 * @param string           $html   The avatar HTML.
	 * @param YITH_YWAR_Review $review The current Review.
	 *
	 * @return string
	 */
	echo wp_kses_post( apply_filters( 'yith_ywar_user_avatar_html', $html, $review ) );
}

/**
 * Show user country
 *
 * @param YITH_YWAR_Review $review The current review.
 *
 * @return void
 * @since  2.1.0
 */
function yith_ywar_show_user_country( YITH_YWAR_Review $review ) {
	if ( 'yes' === yith_ywar_get_option( 'ywar_show_user_country' ) ) {
		if ( empty( $review->get_review_author_country() ) ) {
			$country_code = substr( get_option( 'woocommerce_default_country' ), 0, 2 );
		} else {
			$country_code = $review->get_review_author_country();
		}

		/* translators: %s country name */
		printf( esc_html_x( 'From %s', '[Admin panel] Message displayed in the email when the review needs to be approved', 'yith-woocommerce-advanced-reviews' ) . ' - ', esc_html( WC()->countries->get_countries()[ $country_code ] ) );
	}
}