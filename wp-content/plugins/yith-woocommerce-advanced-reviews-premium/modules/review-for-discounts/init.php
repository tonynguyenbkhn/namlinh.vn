<?php
/**
 * Review for Discounts module init.
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/data-stores/class-yith-ywar-review-for-discounts-data-store.php';

return YITH_YWAR_Review_For_Discounts::get_instance();
