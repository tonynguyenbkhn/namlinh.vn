<?php
/**
 * YITH WooCommerce Recently Viewed Products
 *
 * @package YITH\RecentlyViewedProducts\Templates
 */

if ( ! defined( 'YITH_WRVP' ) ) {
	exit; // Exit if accessed directly.
}

?>
<div class="clear"></div>
<div class="yith-wrvp-filters-cat">
	<?php
	foreach ( $categories as $id => $name ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( isset( $_GET['ywrvp_cat_id'] ) && intval( $_GET['ywrvp_cat_id'] ) === $id ) { // phpcs:disable WordPress.Security.NonceVerification
			$class = 'active';
			$url   = remove_query_arg( 'ywrvp_cat_id' );
		} else {
			$class = '';
			$url   = add_query_arg( 'ywrvp_cat_id', $id );
		}

		?>
		<div class="filter-cat <?php echo esc_html( $class ); ?>">
			<a href="<?php echo esc_url( $url ); ?>" class="cat-link" data-cat_id="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></a>
		</div>
	<?php endforeach; ?>
</div>
