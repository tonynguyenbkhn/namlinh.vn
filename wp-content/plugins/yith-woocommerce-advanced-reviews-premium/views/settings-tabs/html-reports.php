<?php
/**
 * Reports tab content.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs
 * @var array $stats_boxes  The stats boxes to display.
 * @var array $widget_boxes The widget boxes to display.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="yith-ywar-reports">
	<div class="global-stats">
		<?php
		foreach ( $stats_boxes as $stats_box ) {
			yith_ywar_get_view( 'settings-tabs/reports/stats-box.php', $stats_box );
		}
		?>
	</div>
	<div class="widgets">
		<?php
		foreach ( $widget_boxes as $widget_box ) {
			yith_ywar_get_view( 'settings-tabs/reports/widget-box.php', $widget_box );
		}
		?>
	</div>
</div>
