<?php
/**
 * Reports widget "Last Reviews" content.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs\Reports\Lists
 * @var YITH_YWAR_Review[] $values The values.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$url = add_query_arg(
	array(
		'post_type' => YITH_YWAR_Post_Types::REVIEWS,
	),
	admin_url( 'edit.php' )
);
?>
<?php foreach ( $values as $review ) : ?>
	<?php
	$full_text    = $review->get_content();
	$short_text   = strlen( $full_text ) > 50 ? substr( $full_text, 0, 50 ) . ' (...)' : $full_text;
	$product      = wc_get_product( $review->get_product_id() );
	$review_box   = yith_ywar_get_current_review_box( $product );
	$thumbnail    = $product ? $product->get_image( 'thumbnail' ) : wc_placeholder_img( 'thumbnail' );
	$product_name = $product ? $product->get_name() : esc_html_x( 'Deleted product', '[Admin panel] Generic label for products reviewed that were deleted', 'yith-woocommerce-advanced-reviews' );
	?>
	<a class="reviewed-item" href="<?php echo esc_url( get_edit_post_link( $review->get_id() ) ); ?>">
		<span class="picture">
			<?php echo wp_kses_post( $thumbnail ); ?>
		</span>
		<span class="title">
			<span class="content">"<?php echo wp_kses_post( $short_text ); ?>"</span>
			<span class="product-rating">
				<span class="review-rating rating-<?php echo esc_attr( $review->get_rating() ); ?>"></span>
			</span>
			<span class="product-name"><?php echo wp_kses_post( $product_name ); ?></span>
		</span>
	</a>
<?php endforeach; ?>
<a class="see-all" href="<?php echo esc_url( $url ); ?>"> <?php echo esc_html_x( 'View all reviews', '[Admin panel] view all reviews link text', 'yith-woocommerce-advanced-reviews' ); ?></a>
