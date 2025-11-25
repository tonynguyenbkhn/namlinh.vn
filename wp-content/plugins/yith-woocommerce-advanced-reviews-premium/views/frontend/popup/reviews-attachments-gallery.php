<?php
/**
 * Gallery attachments template
 *
 * @package YITH\AdvancedReviews\Views\Frontend\Popup
 * @var YITH_YWAR_Review[]   $reviews       The reviews with attachment.
 * @var array                $attachments   The attachments.
 * @var YITH_YWAR_Review_Box $review_box    The current review box.
 * @var int                  $active_review The current review.
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

?>

<div class="yith-ywar-review-attachments avatar-below-review">
	<div class="attachments-gallery">
		<?php
		yith_ywar_get_view(
			'frontend/popup/attachments-gallery.php',
			array(
				'attachments' => $attachments,
				'type'        => 'gallery',
			)
		);
		?>
	</div>
	<?php foreach ( $reviews as $review ) : ?>
		<div id="gallery-review-<?php echo esc_attr( $review->get_id() ); ?>" class="review-data <?php echo( $active_review !== $review->get_id() ? 'inactive-review' : '' ); ?>">
			<?php if ( yith_ywar_check_user_permissions( 'title-reviews' ) && '' !== $review->get_title() ) : ?>
				<div class="review-title">
					<?php echo wp_kses_post( $review->get_title() ); ?>
				</div>
			<?php endif; ?>
			<div class="review-content">
				<?php echo wp_kses_post( nl2br( $review->get_content() ) ); ?>
			</div>
			<div class="review-user-group">
				<div class="review-user-avatar">
					<?php yith_ywar_get_user_avatar( $review ); ?>
				</div>
				<div class="review-info">
					<div class="review-user">
						<?php echo wp_kses_post( yith_ywar_format_author_name( $review->get_review_author(), $review->get_review_user_id() ) ); ?>
					</div>
					<div class="review-date">
						<?php echo wp_kses_post( ucwords( date_i18n( get_option( 'date_format' ), $review->get_date_created()->getOffsetTimestamp() ) ) ); ?>
					</div>
					<?php
					if ( 'yes' !== $review_box->get_enable_multi_criteria() || ( 'yes' === $review_box->get_enable_multi_criteria() && empty( $review_box->get_multi_criteria() ) ) ) {
						yith_ywar_render_rating( $review, $review_box );
					}
					?>
				</div>
				<?php
				if ( 'yes' === $review_box->get_enable_multi_criteria() && ! empty( $review_box->get_multi_criteria() ) ) {
					yith_ywar_render_rating( $review, $review_box );
				}
				?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
