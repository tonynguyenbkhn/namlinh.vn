<?php
/**
 * Modules tab content.
 *
 * @var array $available_modules     The available modules data.
 * @var array $non_available_modules The non-available modules data.
 *
 * @package YITH\AdvancedReviews\Views\SettingsTabs
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>
<div class="yith-ywar-modules">
	<div class="modules">
		<?php foreach ( $available_modules as $module_data ) : ?>
			<?php yith_ywar_get_view( 'settings-tabs/html-module.php', compact( 'module_data' ) ); ?>
		<?php endforeach; ?>
	</div>
</div>
