<?php
/**
 * Review Request module init.
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/functions-database.php';
require_once __DIR__ . '/includes/functions-schedule.php';

YITH_YWAR_Request_Review_AJAX::get_instance();
YITH_YWAR_Request_Review_Admin::get_instance();
YITH_YWAR_Request_Review_Frontend::get_instance();
YITH_YWAR_Request_Review_Blocks::get_instance();

return YITH_YWAR_Request_Review::get_instance();
