<?php
/**
 * Review
 *
 * @package YITH\AdvancedReviews\Views\Frontend
 * @var YITH_YWAR_Review     $review           The review.
 * @var YITH_YWAR_Review_Box $review_box       The review box.
 * @var bool                 $is_reply         Check if this is a reply to a review.
 * @var bool                 $in_shortcode     Check if this is inside a shortcode.
 * @var bool                 $hide_buttons     Check if buttons should be hidden.
 * @var bool                 $hide_attachments Check if attachments should be hidden.
 * @var string               $css_classes      Additional CSS class.
 * @var string               $badge_label      Additional badge label.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$verified = 'yes' === $review->get_verified_owner() || ( wc_review_is_from_verified_owner( $review->get_comment_id() ) && 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) );
$product  = wc_get_product( $review->get_product_id() );
?>
<div id="review-<?php echo esc_attr( $review->get_id() ); ?>" class="yith-ywar-single-review review-<?php echo esc_attr( $review->get_id() ); ?> <?php echo esc_attr( $css_classes ); ?>" data-badge="<?php echo isset( $badge_label ) ? esc_attr( $badge_label ) : ''; ?>">
	<?php if ( isset( $in_shortcode ) && $in_shortcode ) : ?>
		<div class="review-product-info">
			<?php
			if ( $product ) {
				?>
				<a class="reviewed-product" href="<?php echo esc_url( $product->get_permalink() ); ?>" target="_blank">
					<span class="img-wrapper"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
					<span><?php echo wp_kses_post( $product->get_name() ); ?></span>
				</a>
				<?php
			} else {
				?>
				<span class="reviewed-product">
					<span class="img-wrapper"><?php echo wp_kses_post( wc_placeholder_img( 'thumbnail' ) ); ?></span>
					<span><?php echo esc_html_x( 'Deleted product', '[Admin panel] Generic label for products reviewed that were deleted', 'yith-woocommerce-advanced-reviews' ); ?></span>
				</span>
				<?php
			}
			?>
		</div>
	<?php endif; ?>
	<div class="review-user-group">
		<div class="review-user-avatar">
			<?php yith_ywar_get_user_avatar( $review ); ?>
		</div>
		<div class="review-info">
			<div class="review-user">
				<?php echo wp_kses_post( yith_ywar_format_author_name( $review->get_review_author(), $review->get_review_user_id(), $verified ) ); ?>
			</div>
			<div class="review-date">
				<?php yith_ywar_show_user_country( $review ); ?>
				<?php echo wp_kses_post( ucwords( date_i18n( get_option( 'date_format' ), $review->get_date_created()->getOffsetTimestamp() ) ) ); ?>
			</div>
			<?php yith_ywar_render_rating( $review, $review_box ); ?>
		</div>
	</div>
	<?php if ( $review->get_post_parent() > 0 ) : ?>
		<?php
		$parent_review_id = $review->get_in_reply_of() > 0 ? $review->get_in_reply_of() : $review->get_post_parent();
		$parent_review    = yith_ywar_get_review( $parent_review_id );
		?>
		<div class="reply-to">
			-
			<?php
			/* translators: %s link to parent review */
			printf( esc_html_x( 'In reply to: %s', '[Global] Indicates the user to whom the review is addressed', 'yith-woocommerce-advanced-reviews' ), '<b>' . wp_kses_post( yith_ywar_format_author_name( $parent_review->get_review_author(), $parent_review->get_review_user_id(), false ) ) . '</b>' );
			?>
		</div>
	<?php endif; ?>
	<?php if ( yith_ywar_check_user_permissions( 'title-reviews' ) && '' !== $review->get_title() ) : ?>
		<div class="review-title<?php echo( $review->get_post_parent() > 0 ? ' reply-title' : '' ); ?>">
			<?php echo wp_kses_post( $review->get_title() ); ?>
		</div>
	<?php endif; ?>
	<div class="review-content">
		<?php echo wp_kses_post( nl2br( $review->get_content() ) ); ?>
		<?php if ( ! isset( $hide_buttons ) || ( isset( $hide_buttons ) && false === $hide_buttons ) ) : ?>
			<?php yith_ywar_frontend_edit_button( $is_reply, $review ); ?>
		<?php endif; ?>
	</div>
	<?php if ( ! isset( $hide_attachments ) || ( isset( $hide_attachments ) && false === $hide_attachments ) ) : ?>
		<div class="review-attachments">
			<?php $index = 0; ?>
			<?php foreach ( array_filter( $review->get_thumb_ids() ) as $attachment_id ) : ?>
				<div class="single-attachment attachment-<?php echo esc_attr( $attachment_id ); ?> attachment-<?php echo( wp_attachment_is( 'video', $attachment_id ) ? 'video' : 'image' ); ?>" data-review-id="<?php echo esc_attr( $review->get_id() ); ?>" data-slide-index="<?php echo esc_attr( $index ); ?>">
					<img src="<?php echo esc_url( yith_ywar_get_attachment_image( $product, $attachment_id ) ); ?>"/>
				</div>
				<?php ++$index; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( ! isset( $hide_buttons ) || ( isset( $hide_buttons ) && false === $hide_buttons ) ) : ?>
		<div class="review-actions">
			<div class="helpful-count">
				<?php yith_ywar_helpful_count_label( $review, $review_box ); ?>
			</div>
			<div class="buttons-wrapper">
				<?php yith_ywar_print_action_buttons( $review, $review_box ); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
