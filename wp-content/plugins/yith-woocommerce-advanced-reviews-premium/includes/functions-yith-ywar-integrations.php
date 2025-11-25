<?php
/**
 * Integration management functions
 *
 * @package YITH\AdvancedReviews
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

/**
 * Is YITH Customize My Account Page enabled?
 *
 * @return bool
 * @since  2.0.2
 */
function yith_ywar_customize_my_account_enabled(): bool {
	return defined( 'YITH_WCMAP_PREMIUM' ) && YITH_WCMAP_PREMIUM;
}

/**
 * Is YITH Email Tenmplates enabled?
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_email_templates_enabled(): bool {
	return defined( 'YITH_WCET_PREMIUM' ) && YITH_WCET_PREMIUM;
}

/**
 * Is YITH Account Funds enabled?
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_account_funds_enabled(): bool {
	return defined( 'YITH_FUNDS_PREMIUM' ) && YITH_FUNDS_PREMIUM;
}

/**
 * Is YITH Booking enabled?
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_booking_enabled(): bool {
	return defined( 'YITH_WCBK_PREMIUM' ) && YITH_WCBK_PREMIUM;
}

/**
 * Is YITH Multi Vendor enabled?
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_multivendor_enabled(): bool {
	return defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM;
}

/**
 * Is YITH Points and Rewards enabled?
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_point_and_rewards_enabled(): bool {
	return defined( 'YITH_YWPAR_PREMIUM' ) && YITH_YWPAR_PREMIUM;
}

/**
 * Assign points when review approved (If enabled).
 *
 * @param YITH_YWAR_Review $review The review.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_assign_extra_points( YITH_YWAR_Review $review ) {
	if ( yith_ywar_point_and_rewards_enabled() && in_array( 'enable_review_exp', ywpar_get_active_extra_points_rules(), true ) && is_user_logged_in() ) {
		$review_user = $review->get_review_user_id();
		$customer    = ywpar_get_customer( $review_user );

		if ( $customer ) {
			yith_points()->extra_points->handle_actions( array( 'reviews' ), $customer );
		}
	}
}

add_action( 'yith_ywar_review_status_approved', 'yith_ywar_assign_extra_points', 10 );


/**
 * Is YITH Review Reminder enabled?
 *
 * @return bool
 * @since  2.1.0
 */
function yith_ywar_review_reminder_enabled(): bool {
	return defined( 'YWRR_PREMIUM' ) && YWRR_PREMIUM;
}

/**
 * Is YITH Review for Discounts enabled?
 *
 * @return bool
 * @since  2.1.0
 */
function yith_ywar_review_for_discounts_enabled(): bool {
	return defined( 'YWRFD_PREMIUM' ) && YWRFD_PREMIUM;
}
