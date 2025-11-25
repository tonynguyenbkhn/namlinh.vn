<?php
/**
 * Item list templates
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Templates\Emails
 * @var $button_text
 * @var $items
 * @var $customer_id
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<table class="yith-ywar-items-table" cellspacing="0" cellpadding="6" border="1">
	<tbody>
	<?php foreach ( $items as $item ) : ?>
		<?php
		$product = wc_get_product( $item );
		/**
		 * APPLY_FILTERS: yith_ywar_product_permalink
		 *
		 * Product permalink.
		 *
		 * @param string $value The product permalink.
		 *
		 * @return string
		 */
		$permalink    = apply_filters( 'yith_ywar_product_permalink', $product->get_permalink() );
		$review_stats = yith_ywar_get_review_stats( $product );

		?>
		<tr>
			<td class="picture-column">
				<?php echo wp_kses_post( $product->get_image( array( 100, 100 ) ) ); ?>
			</td>
			<td class="title-column">
				<a class="product-name" href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $product->get_name() ); ?></a>
				<span class="product-rating">
					<span class="stars"><?php yith_ywar_get_product_rating_email( $review_stats['average']['rating'] ); ?></span>
					<span class="rating">
						<?php
						/* translators: %s number of reviews */
						echo wp_kses_post( sprintf( _nx( '%d review', '%d reviews', absint( $review_stats['total'] ), '[Global] Review counter', 'yith-woocommerce-advanced-reviews' ), absint( $review_stats['total'] ) ) );
						?>
					</span>
				</span>
				<a class="review-button" href="<?php echo esc_url( $permalink ); ?>"><?php echo wp_kses_post( $button_text ); ?></a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
