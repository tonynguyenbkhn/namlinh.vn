<?php
/**
 * User reviews
 *
 * @package YITH\AdvancedReviews\Views\Frontend\MyAccount
 * @var object $reviews      The reviews to display array.
 * @var bool   $in_shortcode Check if this template is inside a shortcode.
 * @var int    $current_page The current page.
 * @var int    $next         The next page.
 * @var int    $prev         The previous page.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="yith-ywar-user-reviews-wrapper<?php echo $in_shortcode ? ' woocommerce in-shortcode' : ''; ?>">
	<?php if ( $reviews->total > 0 ) : ?>
		<table class="yith-ywar-user-reviews shop_table shop_table_responsive ">
			<thead>
			<tr>
				<th class="review-product-column"><?php echo esc_html_x( 'Product', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ); ?></th>
				<th class="review-rating-column"><?php echo esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ); ?></th>
				<th class="review-content-column"><?php echo esc_html_x( 'Review', '[Global] Generic text. It refers to the single review', 'yith-woocommerce-advanced-reviews' ); ?></th>
				<th class="review-action-column"></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $reviews->reviews as $review ) : ?>
				<?php
				$product           = wc_get_product( $review->get_product_id() );
				$review_box        = yith_ywar_get_current_review_box( $product );
				$multi_criteria_on = $review_box->get_enable_multi_criteria();
				$criteria          = $review_box->get_multi_criteria();
				$content           = $review->get_content();
				?>
				<tr>
					<td class="review-product-column" data-title="<?php echo esc_html_x( 'Product', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ); ?>">
						<?php
						if ( $product ) {
							?>
							<a class="reviewed-product" href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank">
								<span class="img-wrapper"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
								<span><?php echo wp_kses_post( $product->get_name() ); ?></span>
							</a>
							<?php
						} else {
							?>
							<span class="reviewed-product">
								<span class="img-wrapper"><?php echo wp_kses_post( wc_placeholder_img( 'thumbnail' ) ); ?></span>
								<span><?php echo esc_html_x( 'Deleted product', '[Admin panel] Generic label for products reviewed that were deleted', 'yith-woocommerce-advanced-reviews' ); ?></span>
							</span>
							<?php
						}
						?>
					</td>
					<td class="review-rating-column" data-title="<?php echo esc_html_x( 'Rating', '[Global] Generic text', 'yith-woocommerce-advanced-reviews' ); ?>">
						<div class="rating-wrapper">
							<div class="overall">
								<span class="single-rating"><?php echo esc_html( $review->get_rating() ); ?></span><br/>
								<?php
								if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) {
									printf( '(%s)', esc_html_x( 'avg.', '[Admin panel] Abbrevation for "average"', 'yith-woocommerce-advanced-reviews' ) );
								}
								?>
							</div>
							<?php if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) : ?>
								<?php $multi_rating = $review->get_multi_rating(); ?>
								<div class="multi-criteria">
									<?php foreach ( $criteria as $criterion_id ) : ?>
										<?php $criterion = get_term_by( 'term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX ); ?>
										<div class="single-criterion">
											<span class="criterion-label"><?php echo esc_html( $criterion->name ); ?>:</span>
											<span class="single-rating criterion-rating"><?php echo isset( $multi_rating[ $criterion_id ] ) ? esc_html( $multi_rating[ $criterion_id ] ) : 1; ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					</td>
					<td class="review-content-column" data-title="<?php echo esc_html_x( 'Review', '[Global] Generic text. It refers to the single review', 'yith-woocommerce-advanced-reviews' ); ?>">
						<?php echo wp_kses_post( strlen( $content ) > 100 ? substr( $content, 0, 100 ) . '...' : $content ); ?>
					</td>
					<td class="review-action-column">
						<a href="<?php echo esc_attr( $product->get_permalink() ); ?>#review-<?php echo esc_attr( $review->get_id() ); ?>"><?php echo esc_html_x( 'Read', '[Frontend] my account user review read link', 'yith-woocommerce-advanced-reviews' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<?php if ( 1 < $reviews->max_num_pages ) : ?>
				<tfoot>
				<tr>
					<td class="review-prev-column" colspan="2">
						<?php if ( 1 !== $current_page ) : ?>
							<a class="review-pagination" data-page="<?php echo esc_attr( $current_page - 1 ); ?>" href="<?php echo $in_shortcode ? '#' : esc_url( $prev ); ?>">&lt; <?php echo esc_html_x( 'Previous', '[Frontend] my account user review previous link', 'yith-woocommerce-advanced-reviews' ); ?></a>
						<?php endif; ?>
					</td>
					<td class="review-next-column" colspan="2">
						<?php if ( intval( $reviews->max_num_pages ) !== $current_page ) : ?>
							<a class="review-pagination" data-page="<?php echo esc_attr( $current_page + 1 ); ?>" href="<?php echo $in_shortcode ? '#' : esc_url( $next ); ?>"><?php echo esc_html_x( 'Next', '[Frontend] my account user review next link', 'yith-woocommerce-advanced-reviews' ); ?> &gt;</a>
						<?php endif; ?>
					</td>
				</tr>
				</tfoot>
			<?php endif; ?>

		</table>
	<?php else : ?>
		<div class="yith-ywar-no-reviews">
			<img class="icon" src="<?php echo esc_url( YITH_YWAR_ASSETS_URL ); ?>/images/review-empty.svg"/>
			<span class="message"><?php echo esc_html_x( 'You do not have any reviews yet, leave a review on the products you have purchased and they will appear in this section.', '[Frontend] my account user review empty table', 'yith-woocommerce-advanced-reviews' ); ?></span>
		</div>
	<?php endif; ?>
</div>
