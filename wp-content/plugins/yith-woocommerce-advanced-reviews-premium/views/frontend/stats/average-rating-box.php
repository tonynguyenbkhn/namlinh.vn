<?php
/**
 * Average rating box
 *
 * @package YITH\AdvancedReviews\Views\Frontend
 * @var float $average The reviews average.
 * @var int   $perc    The reviews average percentage.
 * @var int   $total   The reviews total.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="yith-ywar-average-rating-box">
	<span class="average-rating"><?php echo esc_html( $average ); ?></span>
	<span class="stars" style="background: linear-gradient(90deg, var(--ywar-stars-accent) <?php echo esc_attr( $perc ); ?>%, var(--ywar-stars-default) 0)"></span>
	<span class="total-reviews">
		<?php
		/* translators: %s number of reviews */
		echo wp_kses_post( sprintf( _nx( '%d review', '%d reviews', $total, '[Global] Review counter', 'yith-woocommerce-advanced-reviews' ), $total ) );
		?>
	</span>
</div>
