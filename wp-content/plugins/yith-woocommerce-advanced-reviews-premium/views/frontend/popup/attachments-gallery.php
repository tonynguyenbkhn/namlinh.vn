<?php
/**
 * Attachments gallery template
 *
 * @package YITH\AdvancedReviews\Views\Frontend\Popup
 * @var array  $attachments The attachments.
 * @var string $type        The gallery type.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.
?>

<div class="yith-ywar-swiper swiper swiper-gallery-<?php echo esc_attr( $type ); ?>">
	<div class="swiper-wrapper">
		<?php foreach ( $attachments as $attachment ) : ?>
			<div class="swiper-slide" data-review-id="<?php echo esc_attr( $attachment['review_id'] ); ?>">
				<?php yith_ywar_get_attachment( $attachment ); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="swiper-buttons swiper-button-next"></div>
	<div class="swiper-buttons swiper-button-prev"></div>
</div>
<div class="yith-ywar-swiper swiper thumbs-gallery-<?php echo esc_attr( $type ); ?>">
	<div class="swiper-wrapper">
		<?php foreach ( $attachments as $attachment ) : ?>
			<div class="swiper-slide thumb-slide attachment-<?php echo esc_attr( $attachment['type'] ); ?>">
				<img src="<?php echo esc_url( $attachment['thumb'] ); ?>"/>
			</div>
		<?php endforeach; ?>
	</div>
</div>
