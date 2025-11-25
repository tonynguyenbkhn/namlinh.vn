<?php
/**
 * Migration Tools module init.
 *
 * @package YITH\AdvancedReviews\Modules\MigrationTools
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

require_once __DIR__ . '/includes/functions.php';

YITH_YWAR_Migration_Tools_AJAX::get_instance();

return YITH_YWAR_Migration_Tools::get_instance();
