<?php
/**
 * General Function
 *
 * @package YITH\AdvancedReviews
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.


/**
 * Print review action buttons
 *
 * @param YITH_YWAR_Review     $review     The current review.
 * @param YITH_YWAR_Review_Box $review_box The review box.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_print_action_buttons( YITH_YWAR_Review $review, YITH_YWAR_Review_Box $review_box ) {

	$user_can = array(
		'reply'        => yith_ywar_user_can_reply( $review ),
		'vote-helpful' => yith_ywar_user_can_vote_as_helpful( $review_box ),
		'report'       => yith_ywar_user_can_report(),
		'delete'       => yith_ywar_user_can_delete( $review ),
	);

	?>
	<?php if ( $user_can['reply'] ) : ?>
		<span class="action-buttons reply-button" data-action="reply" data-review-id="<?php echo $review->get_post_parent() > 0 ? esc_attr( $review->get_post_parent() ) : esc_attr( $review->get_id() ); ?>" data-reply-to="<?php echo $review->get_post_parent() > 0 ? esc_attr( $review->get_id() ) : 0; ?>">
			<?php echo esc_html_x( 'Reply', '[Global] Review reply button', 'yith-woocommerce-advanced-reviews' ); ?>
		</span>
	<?php endif; ?>
	<span class="spacer"></span>
	<?php if ( $user_can['vote-helpful'] ) : ?>
		<span class="action-buttons helpful-button<?php yith_ywar_user_did_action( $review ); ?>" data-action="like" data-review-id="<?php echo esc_attr( $review->get_id() ); ?>" data-user-id="<?php echo esc_attr( yith_ywar_get_user_id( 'liked' ) ); ?>">
			<?php echo esc_html_x( 'Helpful', '[Frontend] Review vote helpful button', 'yith-woocommerce-advanced-reviews' ); ?>
		</span>
	<?php endif; ?>
	<?php if ( $user_can['report'] ) : ?>
		<span class="action-buttons report-button<?php yith_ywar_user_did_action( $review, 'reported' ); ?>" data-action="report" data-review-id="<?php echo esc_attr( $review->get_id() ); ?>" data-user-id="<?php echo esc_attr( yith_ywar_get_user_id( 'reported' ) ); ?>">
			<?php echo esc_html_x( 'Report', '[Frontend] Review report button', 'yith-woocommerce-advanced-reviews' ); ?>
		</span>
	<?php endif; ?>
	<?php if ( $user_can['delete'] ) : ?>
		<span class="action-buttons delete-button" data-action="delete" data-review-id="<?php echo esc_attr( $review->get_id() ); ?>">
			<?php echo esc_html_x( 'Delete', '[Frontend] Review delete button', 'yith-woocommerce-advanced-reviews' ); ?>
		</span>
	<?php endif; ?>
	<?php
}

/**
 * Manage frontend delete button
 *
 * @param YITH_YWAR_Review $review The current review.
 *
 * @return bool
 *
 * @since  2.0.0
 */
function yith_ywar_user_can_delete( YITH_YWAR_Review $review ): bool {

	$user_can_delete  = yith_ywar_check_user_permissions( 'delete-reviews' );
	$is_review_author = is_user_logged_in() && get_current_user_id() === $review->get_review_user_id();
	$is_staff_member  = yith_ywar_user_is_staff_member( get_current_user_id() );

	return ( ( $user_can_delete && $is_review_author ) || $is_staff_member );
}

/**
 * Check if current user can reply
 *
 * @param YITH_YWAR_Review $review The current review.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_user_can_reply( YITH_YWAR_Review $review ): bool {
	$reply_opened           = 'yes' !== $review->get_stop_reply();
	$user_can_reply         = yith_ywar_check_user_permissions( 'reply-reviews' );
	$user_can_write_reviews = ! is_user_logged_in() ? 'yes' === yith_ywar_get_option( 'ywar_enable_visitors_vote' ) : true;
	$verified_customer      = 'yes' === get_option( 'woocommerce_review_rating_verification_required' ) ? wc_customer_bought_product( '', get_current_user_id(), $review->get_product_id() ) : true;

	return ( $reply_opened && $user_can_reply && $user_can_write_reviews && $verified_customer ) || ( yith_ywar_user_is_staff_member( get_current_user_id() ) && ( ! $reply_opened || ! $user_can_reply ) );
}

/**
 * Check if current user can vote as helpful
 *
 * @param YITH_YWAR_Review_Box $review_box The current review box.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_user_can_vote_as_helpful( YITH_YWAR_Review_Box $review_box ): bool {

	$user_can_vote  = yith_ywar_check_user_permissions( 'vote-helpful' );
	$show_user_vote = false !== array_search( 'vote-helpful', $review_box->get_show_elements(), true );

	return $user_can_vote && $show_user_vote;
}

/**
 * Check if current user can report the review
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_user_can_report(): bool {

	$user_can_report = yith_ywar_check_user_permissions( 'report-reviews' );
	$who_can_report  = yith_ywar_get_option( 'ywar_user_can_report_inappropriate' );

	return $user_can_report && ( ( 'logged' === $who_can_report && get_current_user_id() > 0 ) || 'all' === $who_can_report );
}

/**
 * Check if the current user can review
 *
 * @param WC_Product $product The current product.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_user_can_review( WC_Product $product ): string {
	if ( 'yes' === get_option( 'woocommerce_review_rating_verification_required' ) && ! wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) && ! yith_ywar_user_is_staff_member( get_current_user_id() ) ) {
		return 'not-verified';
	} elseif ( 'yes' !== yith_ywar_get_option( 'ywar_enable_visitors_vote' ) && ! is_user_logged_in() ) {
		return 'not-logged';
	} elseif ( ! yith_ywar_check_user_permissions( 'multiple-reviews' ) && yith_ywar_user_has_commented( $product->get_id(), wp_get_current_user()->user_email, true ) && ! yith_ywar_user_is_staff_member( get_current_user_id() ) ) {
		return 'already-reviewed';
	} else {
		return 'yes';
	}
}

/**
 * Get the user id
 *
 * @param string $action The button action.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_user_id( string $action = 'liked' ): string {

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		$user_id = yith_ywar_set_guest_cookie( $action );
	}

	return $user_id;
}

/**
 * Set the action cookie and gets the cookie ID
 *
 * @param string $action The cookie action.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_set_guest_cookie( string $action = 'liked' ): string {
	$cookie_name = "yith-ywar-$action";
	$cookie_id   = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) : false;

	if ( ! $cookie_id ) {
		// If there's no cookie we create a new one.
		$cookie_id = uniqid( "yith-ywar-$action-" );
		wc_setcookie( $cookie_name, $cookie_id );
	}

	return $cookie_id;
}

/**
 * Check if the user did a specific action
 *
 * @param YITH_YWAR_Review $review     The current review.
 * @param string           $action     The action to check.
 * @param bool             $echo_value Should echo a value or return it.
 *
 * @return void|bool
 * @since  2.0.0
 */
function yith_ywar_user_did_action( YITH_YWAR_Review $review, string $action = 'liked', bool $echo_value = true ) {

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
	} else {
		$cookie_name = "yith-ywar-$action";
		$user_id     = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) ) : 0;
	}

	if ( 'reported' === $action ) {
		$blames     = $review->get_inappropriate_list();
		$did_action = isset( $blames[ $user_id ] ) && 1 === $blames[ $user_id ];
	} else {
		$likes      = $review->get_votes();
		$did_action = isset( $likes[ $user_id ] ) && 1 === $likes[ $user_id ];
	}
	if ( $echo_value ) {
		echo $did_action ? ' selected' : '';
	} else {
		return $did_action;
	}
}

/**
 * Get the helpful count label
 *
 * @param YITH_YWAR_Review     $review     The current review.
 * @param YITH_YWAR_Review_Box $review_box The review box.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_helpful_count_label( YITH_YWAR_Review $review, YITH_YWAR_Review_Box $review_box ) {
	if ( yith_ywar_user_can_vote_as_helpful( $review_box ) ) {

		$count = $review->get_upvotes_count();

		if ( yith_ywar_user_did_action( $review, 'liked', false ) && $count > 1 ) {
			/* translators: %s upvotes count */
			printf( wp_kses_post( _nx( 'You and %s person found this helpful', 'You and %s people found this helpful', esc_html( $count - 1 ), '[Frontend] Review helpful text', 'yith-woocommerce-advanced-reviews' ) ), esc_html( $count - 1 ) );
		} elseif ( yith_ywar_user_did_action( $review, 'liked', false ) && 1 === $count ) {
			echo esc_html_x( 'You found this helpful', '[Frontend] Review helpful text', 'yith-woocommerce-advanced-reviews' );
		} else {
			/* translators: %s upvotes count */
			printf( wp_kses_post( _nx( '%s person found this helpful', '%s people found this helpful', esc_html( $count ), '[Frontend] Review helpful text', 'yith-woocommerce-advanced-reviews' ) ), esc_html( $count ) );

		}
	}
}

/**
 * Check if user is staff member
 *
 * @param int $user_id The user ID.
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_user_is_staff_member( int $user_id ): bool {
	return wc_user_has_role( $user_id, 'administrator' ) || wc_user_has_role( $user_id, 'shop_manager' );
}

/**
 * Format review author name
 *
 * @param string $name     The author name.
 * @param int    $user_id  The user ID.
 * @param bool   $verified Check if the user is a verified owner.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_format_author_name( string $name, int $user_id, bool $verified = false ): string {
	$format         = yith_ywar_get_option( 'ywar_username_format' );
	$verified_label = '';

	switch ( $format ) {
		case 'masked':
			$len    = strlen( $name );
			$filler = $len - 2 > 0 ? str_repeat( '*', $len - 2 ) : '*';
			$name   = substr( $name, 0, 1 ) . $filler . substr( $name, $len - 1, 1 );
			break;
		case 'masked-full':
			$parts  = explode( ' ', $name );
			$filler = str_repeat( '*', 6 );

			if ( count( $parts ) > 1 ) {
				$name = substr( $parts[0], 0, 1 ) . $filler . ' ' . substr( $parts[ array_key_last( $parts ) ], 0, 1 ) . $filler;

			} else {
				$name = substr( $parts[0], 0, 1 ) . $filler;
			}
			break;
		case 'name-only':
			$parts = explode( ' ', $name );
			if ( count( $parts ) > 1 ) {
                $name = $parts[0] . ' ' . mb_substr( $parts[ array_key_last( $parts ) ], 0, 1, 'UTF-8' ) . '.';
			} else {
				$name = $parts[0];
			}
			break;
		case 'nickname':
			if ( $user_id > 0 ) {
				$user = get_user_by( 'id', $user_id );
				$name = $user->nickname;
			}
			break;
	}

	if ( $verified ) {
		$verified_label = sprintf( ' <span class="verified"><img src="%2$s" />%1$s</span>', esc_html_x( 'Verified buyer', '[Frontend] label for users that purchased the product', 'yith-woocommerce-advanced-reviews' ), YITH_YWAR_ASSETS_URL . '/images/verified.svg' );

		$name .= $verified_label;
	}

	/**
	 * APPLY_FILTERS: yith_ywar_rating_additional_label
	 *
	 * Manages additional label for the Rating widget.
	 *
	 * @param string $name           The author name.
	 * @param int    $user_id        The user ID.
	 * @param string $verified_label The "verified owner" label.
	 *
	 * @return string
	 */
	return apply_filters( 'yith_ywar_format_author_name', $name, $user_id, $verified_label );
}

/**
 * Get product rating for single and shop pages
 *
 * @param WC_Product $product The current product.
 * @param string     $context The context.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_get_rating_html( WC_Product $product, string $context = 'single' ) {

	$review_stats = yith_ywar_get_review_stats( $product );

	if ( 'single' === $context ) {
		?>
		<div class="yith-ywar-product-rating-wrapper">
		<?php
	}
	/**
	 * APPLY_FILTERS: yith_ywar_show_rating
	 *
	 * Manages additional label for the Rating widget.
	 *
	 * @param bool   $show_rating  Wheter to show the rating.
	 * @param string $context      The context.
	 * @param array  $review_stats The product review stats.
	 *
	 * @return bool
	 */
	if ( apply_filters( 'yith_ywar_show_rating', true, $context, $review_stats ) ) {

		$average = 'single' !== $context ? "<b>{$review_stats['average']['rating']}</b> - " : '';

		/* translators: %s number of reviews */
		$label = sprintf( _nx( '%d review', '%d reviews', $review_stats['total'], '[Global] Review counter', 'yith-woocommerce-advanced-reviews' ), $review_stats['total'] );
		/**
		 * APPLY_FILTERS: yith_ywar_rating_additional_label
		 *
		 * Manages additional label for the Rating widget.
		 *
		 * @param string     $value        Empty string by default.
		 * @param WC_Product $product      The current product.
		 * @param string     $context      The context.
		 * @param array      $review_stats The product review stats.
		 *
		 * @return string
		 */
		$additional_label = apply_filters( 'yith_ywar_rating_additional_label', '', $product, $context, $review_stats );
		$elem             = ( 'single' === $context ? 'a' : 'span' );
		/**
		 * APPLY_FILTERS: yith_ywar_rating_link
		 *
		 * Manages rating href link.
		 *
		 * @param string     $value        Depending on context, href="#reviews" or empty by default.
		 * @param WC_Product $product      The current product.
		 * @param string     $context      The context.
		 * @param array      $review_stats The product review stats.
		 *
		 * @return string
		 */
		$link             = apply_filters( 'yith_ywar_rating_link', ( 'single' === $context ? ' href="#reviews" ' : '' ), $product, $context, $review_stats );
		?>

		<div class="yith-ywar-product-rating page-<?php echo esc_attr( $context ); ?>">
			<span class="stars" style="background: linear-gradient(90deg, var(--ywar-stars-accent) <?php echo esc_attr( $review_stats['average']['perc'] ); ?>%, var(--ywar-stars-default) 0)"></span>
			<?php
			/**
			 * APPLY_FILTERS: yith_ywar_rating_widget
			 *
			 * Manages the Rating widget aspect.
			 *
			 * @param string $html             The current rating element.
			 * @param string $context          The context.
			 * @param array  $review_stats     The product review stats.
			 * @param string $elem             The HTML container element.
			 * @param string $link             The optional link.
			 * @param string $label            The label.
			 * @param string $additional_label The additional label (optional).
			 *
			 * @return string
			 */
			echo wp_kses_post( apply_filters( 'yith_ywar_rating_widget', "<$elem class=\"total-reviews\"$link>$average$label</$elem>$additional_label", $context, $review_stats, $elem, $link, $label, $additional_label ) );

			if ( 'single' === $context ) {
				$review_box = yith_ywar_get_current_review_box( $product );
				$graph_box  = false !== array_search( 'graph-bars', $review_box->get_show_elements(), true );
				if ( 'yes' === yith_ywar_get_option( 'ywar_enable_graph_tooltip' ) && $graph_box ) {
					$graph_args = array(
						'ratings'   => $review_stats['ratings'],
						'show_perc' => 'yes' === yith_ywar_get_option( 'ywar_summary_percentage_value' ),
					);
					yith_ywar_get_view( 'frontend/stats/graph-box.php', $graph_args );
				}
			} else {
				/**
				 * APPLY_FILTERS: yith_ywar_rating_additional_label_loop
				 *
				 * Manages additional label for the Rating widget in the shop/category/tag pages.
				 *
				 * @param string     $value        Empty string by default.
				 * @param WC_Product $product      The current product.
				 * @param array      $review_stats The product review stats.
				 *
				 * @return string
				 */
				echo wp_kses_post( apply_filters( 'yith_ywar_rating_additional_label_loop', '', $product, $review_stats ) );
			}
			?>
		</div>
		<?php
	}
	if ( 'single' === $context ) {
		?>
		</div>
		<?php
	}
}

/**
 * Render the rating on frontend
 *
 * @param YITH_YWAR_Review     $review     The current review.
 * @param YITH_YWAR_Review_Box $review_box The review box.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_render_rating( YITH_YWAR_Review $review, YITH_YWAR_Review_Box $review_box ) {
	if ( $review->get_post_parent() > 0 ) {
		return;
	}

	$multi_criteria_on = $review_box->get_enable_multi_criteria();
	$criteria          = $review_box->get_multi_criteria();

	if ( 'yes' === $multi_criteria_on && ! empty( $criteria ) ) {
		$multi_rating = $review->get_multi_rating();
		?>
		<div class="multi-rating">
			<?php foreach ( $criteria as $criterion_id ) : ?>
				<?php $criterion = get_term_by( 'term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX ); ?>
				<div class="single-criterion">
					<?php echo esc_html( $criterion->name ); ?>
					<div class="review-rating rating-<?php echo isset( $multi_rating[ $criterion_id ] ) ? esc_html( $multi_rating[ $criterion_id ] ) : esc_html( $review->get_rating() ); ?>"></div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	} else {
		?>
		<div class="review-rating rating-<?php echo esc_attr( $review->get_rating() ); ?>"></div>
		<?php
	}
}

/**
 * Get attachment
 *
 * @param array $attachment The attachment.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_get_attachment( array $attachment ) {
	if ( 'video' === $attachment['type'] ) {
		echo do_shortcode( '[video src="' . $attachment['full'] . '"]' );
	} else {
		?>
		<img src="<?php echo esc_html( $attachment['full'] ); ?>"/>
		<?php
	}
}

/**
 * Get attachment image
 *
 * @param WC_Product $product       The Product.
 * @param int        $attachment_id The attachment ID.
 * @param array      $size          The image size to retrieve.
 * @param bool       $thumb         Check if the image should be treated as an icon.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_attachment_image( WC_Product $product, int $attachment_id, array $size = array( 80, 80 ), bool $thumb = true ): string {
	if ( wp_attachment_is( 'video', $attachment_id ) ) {
		$image = wp_get_attachment_image_url( $product->get_image_id(), $size, $thumb );
	} else {
		$image = wp_get_attachment_image_url( $attachment_id, $size, $thumb );
	}

	return $image;
}

/**
 * Manage frontend edit button
 *
 * @param bool             $is_reply Check if it is a reply.
 * @param YITH_YWAR_Review $review   The review.
 *
 * @return void
 * @since  2.0.0
 */
function yith_ywar_frontend_edit_button( bool $is_reply, YITH_YWAR_Review $review ) {
	$is_review_author = is_user_logged_in() ? ( get_current_user_id() === $review->get_review_user_id() ) : ( $review->get_guest_cookie() === yith_ywar_set_guest_cookie( 'reviewed' ) );
	$user_can_edit    = yith_ywar_check_user_permissions( 'edit-reviews' );
	$is_staff_member  = yith_ywar_user_is_staff_member( get_current_user_id() );

	if ( ( $user_can_edit && $is_review_author ) || $is_staff_member ) {
		?>
		<span class="edit-button" data-type="<?php echo( $is_reply ? 'reply' : 'review' ); ?>" data-review-id="<?php echo esc_attr( $review->get_id() ); ?>"><?php echo esc_html_x( 'Edit', '[Global] Generic edit button text', 'yith-woocommerce-advanced-reviews' ); ?></span>
		<?php
	}
}

/**
 * Get the message to display if a customer cannot review
 *
 * @param string $type The type of message to display.
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_edit_review_form_message( string $type ): string {

	$messages = array(
		'not-logged'       => esc_html_x( 'Only logged in customers can leave a review.', '[Frontend] Message if user cannot leave a review', 'yith-woocommerce-advanced-reviews' ),
		'not-verified'     => esc_html_x( 'Only logged in customers who have purchased this product can leave a review.', '[Frontend] Message if user cannot leave a review', 'yith-woocommerce-advanced-reviews' ),
		'already-reviewed' => esc_html_x( 'You cannot leave more than one review for the same product.', '[Frontend] Message if user cannot leave multiple review', 'yith-woocommerce-advanced-reviews' ),
		'pending-approval' => esc_html_x( 'Thanks for your review, we will check its content and publish it very soon.', '[Frontend] Message to show after a pending review is published', 'yith-woocommerce-advanced-reviews' ),
		'duplicated'       => esc_html_x( "Duplicate comment detected; it looks like you've already said that!", '[Frontend] Message to show after a duplicated review is published', 'yith-woocommerce-advanced-reviews' ),
		'deleted'          => esc_html_x( 'The review was deleted successfully!', '[Frontend] Message to show after a review wasdeleted from the frontend', 'yith-woocommerce-advanced-reviews' ) . ' <span class="undo-delete-review">' . esc_html_x( 'Undo?', '[Frontend] Undo delete review link text', 'yith-woocommerce-advanced-reviews' ) . '</span>',
	);
	$message  = isset( $messages[ $type ] ) ? $messages[ $type ] : '';

	/**
	 * APPLY_FILTERS: yith_ywar_frontend_edit_review_message
	 *
	 * Allows changes on the message in the edit form.
	 *
	 * @param string $message The current message.
	 * @param string $type    The message type.
	 *
	 * @return string
	 */
	return apply_filters( 'yith_ywar_frontend_edit_review_message', $message, $type );
}

/**
 * Get allowed filetypes
 *
 * @return string
 * @since  2.0.0
 */
function yith_ywar_get_allowed_filetypes(): string {
	$images = 'yes' === yith_ywar_get_option( 'ywar_enable_attachments' ) ? yith_ywar_get_option( 'ywar_attachment_type' ) : array();
	$video  = 'yes' === yith_ywar_get_option( 'ywar_enable_attachments_video' ) ? yith_ywar_get_option( 'ywar_attachment_type_video' ) : array();
	$images = ! is_array( $images ) ? array() : $images;
	$video  = ! is_array( $video ) ? array() : $video;

	return '.' . implode( ', .', array_merge( $images, $video ) );
}

/**
 * Check if reCaptcha is enabled
 *
 * @return bool
 * @since  2.0.0
 */
function yith_ywar_is_recaptcha_enabled() {
	$recaptcha = yith_ywar_get_option( 'ywar_enable_recaptcha' );
	$sitekey   = yith_ywar_get_option( 'ywar_recaptcha_site_key' );
	$secretkey = yith_ywar_get_option( 'ywar_recaptcha_secret_key' );

	return 'yes' === $recaptcha && ! empty( $sitekey ) && ! empty( $secretkey );
}