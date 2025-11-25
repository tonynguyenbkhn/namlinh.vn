<?php
/**
 * Rating stars template
 *
 * @package YITH\AdvancedReviews\Views\Frontend\EditForm
 * @var string $field_name The field name.
 * @var string $label      The field label.
 * @var int    $rating     The current rating.
 * @var string $index      The rating index.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<span class="rating-label">
	<?php echo esc_html( $label ); ?>
</span>
<span class="rating-wrapper">
	<span class="stars<?php echo( 0 !== $rating ? ' selected' : '' ); ?>">
		<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
			<span data-value="<?php echo esc_attr( $i ); ?>" class="star-<?php echo esc_attr( $i ); ?><?php echo( $rating === $i ? ' active' : '' ); ?>"></span>
		<?php endfor; ?>
	</span>
	<input name="<?php echo esc_attr( $field_name ); ?>" data-index="<?php echo esc_attr( $index ); ?>" class="rating-value" type="hidden" value="<?php echo esc_attr( $rating ); ?>"/>
</span>