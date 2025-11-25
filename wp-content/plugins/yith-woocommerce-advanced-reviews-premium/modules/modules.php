<?php
/**
 * Modules list.
 *
 * @package YITH\AdvancedReviews\Modules
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$modules = array(
	'request-review'       => array(
		'name'         => 'Review reminder',
		'description'  => esc_html_x( "Increase the number of quality reviews for your store's products by automatically inviting users to leave reviews. Choose the best communication strategy to encourage them to share their opinions, and you'll see your store grow day by day.", '[Admin panel] Module description', 'yith-woocommerce-advanced-reviews' ),
		'needs_reload' => true,
	),
	'review-for-discounts' => array(
		'name'         => 'Review for discounts',
		'description'  => esc_html_x( 'Increase the number of reviews and sales in your store with a very powerful tool: discounts. Offer them in exchange for a review and your users will buy more and more.', '[Admin panel] Module description', 'yith-woocommerce-advanced-reviews' ),
		'needs_reload' => true,
	),
);

if ( yith_ywar_review_reminder_enabled() || yith_ywar_review_for_discounts_enabled() ) {
	$modules['migration-tools'] = array(
		'name'         => esc_html_x( 'Migration tools', '[Admin panel] Module name', 'yith-woocommerce-advanced-reviews' ),
		'description'  => esc_html_x( 'Migrate your options from the existing plugins.', '[Admin panel] Module description', 'yith-woocommerce-advanced-reviews' ),
		'needs_reload' => true,
	);
}

return $modules;
