<?php
/**
 * Multi rating box
 *
 * @package YITH\AdvancedReviews\Views\Frontend
 * @var array $criteria     The criteria array.
 * @var array $multiratings Multirating averages.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="yith-ywar-multi-criteria-box">
	<?php foreach ( $criteria as $criterion_id ) : ?>
		<?php
		$criterion = get_term_by( 'term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX );
		$icon_id   = get_term_meta( $criterion_id, 'icon', true );
		$icon      = $icon_id ? wp_get_attachment_image_src( $icon_id, array( 30, 30 ), true )[0] : '';
		$value     = isset( $multiratings[ $criterion_id ] ) ? $multiratings[ $criterion_id ] : 0
		?>
		<div class="single-criterion">
			<span class="criterion-label<?php echo( $icon_id ? ' has-icon' : '' ); ?>"<?php echo( $icon_id ? ' style="background-image: url(' . esc_attr( $icon ) . ')"' : '' ); ?>><?php echo esc_html( $criterion->name ); ?></span>
			<span class="criterion-rating"><?php printf( '%.1f', esc_html( $value ) ); ?></span>
		</div>
	<?php endforeach; ?>
</div>
