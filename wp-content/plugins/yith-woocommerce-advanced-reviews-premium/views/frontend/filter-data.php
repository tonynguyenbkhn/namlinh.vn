<?php
/**
 * Filter data
 *
 * @package YITH\AdvancedReviews\Views\Frontend
 * @var array $rating The  filtered rating array.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="yith-ywar-filter-data">
	<p class="filter-title"><?php echo esc_html_x( 'Reviews filtered by rating', '[Frontend] Filtered reviews title', 'yith-woocommerce-advanced-reviews' ); ?></p>
	<div class="filter-buttons">
		<span class="filter-button rating-label">
			<span class="review-rating rating-<?php echo esc_attr( $rating ); ?>"></span>
			<?php
			/* translators: %s number of reviews */
			echo wp_kses_post( sprintf( _nx( '%d star', '%d stars', $rating, '[Global] Rating descrption with plural forms', 'yith-woocommerce-advanced-reviews' ), $rating ) );
			?>
		</span>
		<span class="filter-button show-all-reviews"><?php echo esc_html_x( 'Clear filter', '[Frontend] Filtered reviews clear action', 'yith-woocommerce-advanced-reviews' ); ?></span>
	</div>
</div>
