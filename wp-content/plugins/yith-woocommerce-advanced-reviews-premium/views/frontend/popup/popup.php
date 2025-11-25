<?php
/**
 * Popup bone template
 *
 * @package YITH\AdvancedReviews\Views\Frontend\Popup
 * @var string $popup_id      The popup ID.
 * @var string $product_name  The product_name.
 * @var string $popup_title   The popup title.
 * @var string $popup_extra   The extra content.
 * @var string $popup_kses    The extra content kses args.
 * @var string $review_box_id The current Review Box ID.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.
?>

<div id="yith-ywar-<?php echo esc_attr( $popup_id ); ?>-popup" class="yith-ywar-popup woocommerce" data-review-box="<?php echo esc_attr( $review_box_id ); ?>">
	<span class="popup-close"></span>
	<div id="yith-ywar-<?php echo esc_attr( $popup_id ); ?>-popup-wrapper" class="popup-wrapper">
		<a href="#" class="popup-close-link">&lt; <?php echo esc_html_x( 'Back to all reviews', '[Frontend] Popup action button', 'yith-woocommerce-advanced-reviews' ); ?></a>
		<div class="product-name"><?php echo wp_kses_post( $product_name ); ?></div>
		<div class="popup-title"><?php echo wp_kses_post( $popup_title ); ?></div>
		<div class="popup-extra"><?php echo( '' !== $popup_extra ? wp_kses( $popup_extra, $popup_kses ) : '' ); ?></div>
		<div class="popup-content"></div>
	</div>
</div>
