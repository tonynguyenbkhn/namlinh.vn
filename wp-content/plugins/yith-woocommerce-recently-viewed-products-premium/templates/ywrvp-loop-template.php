<?php
/**
 * YITH WooCommerce Recently Viewed Products
 *
 * @version 1.0.1
 * @package YITH\RecentlyViewedProducts\Templates
 */

if ( ! defined( 'YITH_WRVP' ) ) {
	exit; // Exit if accessed directly.
}

global $woocommerce_loop;

if ( $columns ) {
	$woocommerce_loop['columns'] = $columns;
} else {
	$columns = 4;
}

?>

<div class="woocommerce yith-similar-products cols-<?php echo esc_attr( $columns ); ?><?php echo esc_attr( $class ); ?>" data-slider="<?php echo esc_attr( $slider ); ?>" data-dots="<?php echo esc_attr( $dots ); ?>" data-autoplay="<?php echo esc_attr( $autoplay ); ?>" data-numcolumns="<?php echo intval( $columns ); ?>" data-autoplayspeed="<?php echo intval( $autoplay_speed ); ?>">
	<?php if ( ! empty( $title ) ) : ?>
		<h2><?php echo esc_html( $title ); ?>
			<?php if ( ! empty( $view_all ) ) : ?>
				<a href="<?php echo esc_url( $page_url ); ?>" class="shop-link"><?php echo esc_html( $view_all ); ?></a>
			<?php endif; ?>
		</h2>
	<?php endif; ?>

	<?php woocommerce_product_loop_start(); ?>

	<?php
	while ( $products->have_posts() ) :
		$products->the_post();
		?>
		<?php wc_get_template_part( 'content', 'product' ); ?>

	<?php endwhile; // end of the loop. ?>

	<?php woocommerce_product_loop_end(); ?>
</div>
