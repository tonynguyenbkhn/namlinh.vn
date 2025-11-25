<?php
/**
 * Reports widget "Best or Worst rated products" content.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs\Reports\Lists
 * @var array $values The values.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<?php foreach ( $values as $product_id ) : ?>
	<?php
	$product      = wc_get_product( $product_id );
	$review_stats = yith_ywar_get_review_stats( $product );
	$thumbnail    = $product ? $product->get_image( array( 80, 80 ) ) : wc_placeholder_img( array( 80, 80 ) );
	$product_name = $product ? $product->get_name() : esc_html_x( 'Deleted product', '[Admin panel] Generic label for products reviewed that were deleted', 'yith-woocommerce-advanced-reviews' );
	?>
	<a class="reviewed-item" href="<?php echo esc_url( get_edit_post_link( $product->get_id() ) ); ?>" target="_blank">
		<div class="picture">
			<?php echo wp_kses_post( $thumbnail ); ?>
		</div>
		<div class="title">
			<span class="product-name"><?php echo wp_kses_post( $product_name ); ?></span>
			<span class="product-rating">
				<span class="rating"><?php echo esc_html( $review_stats['average']['rating'] ); ?></span>
				<span class="stars" style="background: linear-gradient(90deg, #dc9202 <?php echo esc_attr( $review_stats['average']['perc'] ); ?>%, #cdcdcd 0)"></span>
			</span>
		</div>
	</a>
<?php endforeach; ?>
