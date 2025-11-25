<?php
/**
 * Graph box
 *
 * @package YITH\AdvancedReviews\Views\Frontend
 * @var array $ratings   The ratings array.
 * @var bool  $show_perc Show percentages.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="yith-ywar-graph-box">
	<?php foreach ( $ratings as $rating => $values ) : ?>
		<div class="rating-group rating-group-<?php echo esc_attr( $rating ); ?>" data-rating="<?php echo esc_attr( $rating ); ?>" data-count="<?php echo esc_attr( $values['count'] ); ?>">
			<div class="rating-label"><?php echo esc_attr( $rating ); ?></div>
			<div class="rating-bar">
				<div class="rating-bar-accent" style="width: <?php echo esc_attr( $values['perc'] ); ?>%"></div>
			</div>
			<div class="rating-count"><?php echo $show_perc ? esc_html( $values['perc'] ) . '%' : esc_html( $values['count'] ); ?></div>
		</div>
	<?php endforeach; ?>
</div>
