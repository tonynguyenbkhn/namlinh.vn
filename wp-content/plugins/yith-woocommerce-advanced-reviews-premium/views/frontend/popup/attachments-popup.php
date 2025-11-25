<?php
/**
 * Attachments lightbox template
 *
 * @package YITH\AdvancedReviews\Views\Frontend\Popup
 * @var string $popup_id     The popup ID.
 * @var string $product_name The product_name.
 * @var string $popup_title  The popup title.
 * @var string $popup_extra  The extra content.
 * @var string $popup_kses   The extra content kses args.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.
?>

<div id="yith-ywar-gallery-lightbox" class="yith-ywar-gallery-lightbox">
	<div class="lightbox-overlay"></div>
	<div class="lightbox-wrapper">
		<span class="lightbox-close"></span>
		<div class="lightbox-content"></div>
	</div>
</div>
