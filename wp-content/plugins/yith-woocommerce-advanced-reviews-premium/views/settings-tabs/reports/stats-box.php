<?php
/**
 * Reports stats box content.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs\Reports
 * @var string $class           The CSS class.
 * @var string $value           The value.
 * @var string $label           The label.
 * @var string $additional_html Additional HTML (Optional).
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="stats-box <?php echo esc_attr( $class ); ?>">
	<span class="amount"><?php echo esc_attr( $value ); ?></span>
	<?php
	if ( isset( $additional_html ) ) {
		echo wp_kses_post( $additional_html );
	}
	?>
	<span class="label"><?php echo esc_html( $label ); ?></span>
</div>
