<?php
/**
 * Reports widget box content.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs\Reports
 * @var string $class         The CSS class.
 * @var array  $values        The values.
 * @var string $label         The label.
 * @var string $empty_message The empty message.
 * @var string $template      The eempalte file.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="widget-box <?php echo esc_attr( $class ); ?>">
	<span class="label"><?php echo esc_html( $label ); ?></span>
	<?php if ( empty( $values ) ) : ?>
		<div class="empty-message">
			<?php echo wp_kses_post( $empty_message ); ?>
		</div>
	<?php else : ?>
		<div class="items-list">
			<?php yith_ywar_get_view( "settings-tabs/reports/lists/$template.php", array( 'values' => $values ) ); ?>
		</div>
	<?php endif; ?>
</div>
