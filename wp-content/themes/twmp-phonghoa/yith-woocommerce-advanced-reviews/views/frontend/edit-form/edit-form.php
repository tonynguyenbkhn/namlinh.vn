<?php

/**
 * Edit review form
 *
 * @package YITH\AdvancedReviews\Views\Frontend\EditForm
 * @var string           $form_id                The id of the form.
 * @var string           $type                   Flag to identify if it is a review or a reply.
 * @var string           $action                 Flag to identify if it is editing or a new review/reply.
 * @var bool             $multi_criteria_enabled Flag to identify if multi-criteria is enabled.
 * @var array            $multi_criteria         Review Multi-criteria.
 * @var YITH_YWAR_Review $review                 The review.
 * @var string           $form_title             The the title of the form.
 * @var string           $button_text            The text of the submit button.
 * @var bool             $logged_user            Flag to check if it's a logged user.
 * @var int              $in_reply_of            ID of the reply (if available).
 */

defined('YITH_YWAR') || exit; // Exit if accessed directly.
global $post;
$review_id      = $review ? $review->get_id() : ('review' === $type ? 'new' : str_replace('new-reply-', '', $form_id));
$has_attachment = $review && ! empty($review->get_thumb_ids());
$upload_images  = 'yes' === yith_ywar_get_option('ywar_enable_attachments');
$upload_video   = 'yes' === yith_ywar_get_option('ywar_enable_attachments_video');

?>
<div class="modal modal--yith-comment-form" role="dialog" data-block="modal-yith-comment-form">
    <div class="modal__wrapper position-relative">
        <div class="modal__header">
            <span class="modal__title"><?php echo esc_html($post->post_title); ?></span>
            <button class="modal__close-button" data-close-modal="modal-yith-comment-form" aria-label="<?php echo esc_attr__('Close a search form modal', 'twmp-phonghoa'); ?>">
                <?php echo twmp_get_svg_icon('close'); ?>
            </button>
        </div>
        <div class="modal__content js-content">
            <div id="yith-ywar-<?php echo esc_attr($form_id); ?>" class="yith-ywar-edit-forms <?php echo ! $review ? esc_attr("new-$type") : ''; ?>">
                <div class="form-header">
                    <?php echo esc_html($form_title); ?>
                </div>
                <div class="form-content">
                    <?php if (! $logged_user && 'create' === $action) : ?>
                        <div class="form-element form-review-user-data">
                            <label>
                                <?php echo esc_html_x('Your Name', '[Frontend] User name form field', 'twmp-phonghoa'); ?>:
                                <input type="text" name="yith-ywar-user-name" value="<?php echo esc_html($review ? $review->get_review_author() : ''); ?>" />
                            </label>
                            <label>
                                <?php echo esc_html_x('Your Email', '[Frontend] User email form field', 'twmp-phonghoa'); ?>:
                                <input type="email" name="yith-ywar-user-email" value="<?php echo esc_html($review ? $review->get_review_author_email() : ''); ?>" />
                            </label>
                        </div>
                    <?php endif; ?>
                    <?php if (yith_ywar_check_user_permissions('title-reviews')) : ?>
                        <div class="form-element form-review-title">
                            <label>
                                <?php echo 'review' === $type ? esc_html_x('Review title', '[Frontend] Review title form field', 'twmp-phonghoa') : esc_html_x('Reply title', '[Frontend] Reply title form field', 'twmp-phonghoa'); ?>:
                                <input type="text" name="yith-ywar-title" value="<?php echo esc_html($review ? $review->get_title() : ''); ?>" />
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="form-element form-review-content">
                        <label>
                            <?php echo 'review' === $type ? esc_html_x('Your review', '[Frontend] Review content form field', 'twmp-phonghoa') : esc_html_x('Your reply', '[Frontend] Reply content form field', 'twmp-phonghoa'); ?>:
                            <textarea name="yith-ywar-content" class="review-content"><?php echo esc_html($review ? $review->get_content() : ''); ?></textarea>
                        </label>
                    </div>
                    <?php if ($upload_images || $upload_video) : ?>
                        <div class="form-element form-review-attachment">
                            <label>
                                <?php echo esc_html_x('Upload files', '[Frontend] Review upload field label', 'twmp-phonghoa'); ?>:
                                <span>
                                    <?php
                                    switch (true) {
                                        case $upload_images && ! $upload_video:
                                            /* translators: %s number of items */
                                            $file_amount = sprintf(esc_html_x('You can upload a maximum of %s images.', '[Frontend] Review upload field file label', 'twmp-phonghoa'), yith_ywar_get_option('ywar_max_attachments'));
                                            break;
                                        case ! $upload_images && $upload_video:
                                            /* translators: %s number of items */
                                            $file_amount = sprintf(esc_html_x('You can upload a maximum of %s videos.', '[Frontend] Review upload field file label', 'twmp-phonghoa'), yith_ywar_get_option('ywar_max_attachments_video'));
                                            break;
                                        default:
                                            /* translators: %1$s number of images, %2$s number of videos */
                                            $file_amount = sprintf(esc_html_x('You can upload a maximum of %1$s images and %2$s videos.', '[Frontend] Review upload field file label', 'twmp-phonghoa'), yith_ywar_get_option('ywar_max_attachments'), yith_ywar_get_option('ywar_max_attachments_video'));
                                    }

                                    /* translators: %s list of allowed file formats */
                                    $allowed_types = sprintf(esc_html_x('Allowed file types: %s.', '[Frontend] Review upload field file types label', 'twmp-phonghoa'), esc_html(yith_ywar_get_allowed_filetypes()));

                                    switch (true) {
                                        case $upload_images && ! $upload_video:
                                            /* translators: %s file size */
                                            $file_size = sprintf(esc_html_x('Maximum file size: %sMB.', '[Frontend] Review upload field file size label', 'twmp-phonghoa'), yith_ywar_get_option('ywar_attachment_max_size'));
                                            break;
                                        case ! $upload_images && $upload_video:
                                            /* translators: %s file size */
                                            $file_size = sprintf(esc_html_x('Maximum file size: %sMB.', '[Frontend] Review upload field file size label', 'twmp-phonghoa'), yith_ywar_get_option('ywar_attachment_max_size_video'));
                                            break;
                                        default:
                                            /* translators: %1$s image size, %2$s video size */
                                            $file_size = sprintf(esc_html_x('Maximum file size: %1$sMB for images and %2$sMB for videos.', '[Frontend] Review upload field file size label', 'twmp-phonghoa'), yith_ywar_get_option('ywar_max_attachments'), yith_ywar_get_option('ywar_max_attachments_video'));
                                    }

                                    echo wp_kses_post("$file_amount<br />$allowed_types<br/>$file_size");
                                    ?>
                                </span>
                            </label>
                            <div class="yith-ywar-attachments">
                                <div class="attachments-wrapper">
                                    <div class="attachments-list <?php echo ($has_attachment ? '' : 'empty'); ?>">
                                        <?php if ($has_attachment) : ?>
                                            <?php
                                            $product        = wc_get_product($review->get_product_id());
                                            $attachment_ids = ! empty($review->get_thumb_ids()) ? array_filter($review->get_thumb_ids()) : array();
                                            ?>
                                            <?php foreach ($attachment_ids as $attachment_id) : ?>
                                                <div class="attachment attachment-<?php echo esc_attr($attachment_id); ?> attachment-<?php echo (wp_attachment_is('video', $attachment_id) ? 'video' : 'image'); ?>" data-item-id="<?php echo esc_attr($attachment_id); ?>">
                                                    <img src="<?php echo esc_url(yith_ywar_get_attachment_image($product, $attachment_id)); ?>" />
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="new-attachment">
                                        <?php echo twmp_get_svg_icon('camera'); ?>
                                        <span><?php echo esc_html__('Send real photos', 'twmp-phonghoa'); ?></span>
                                        <input type="file" name="yith-ywar-add-attachment" class="attachment-field" multiple accept="<?php echo esc_html(yith_ywar_get_allowed_filetypes()); ?>" />
                                    </div>
                                    <?php if ($has_attachment) : ?>
                                        <input type="hidden" name="yith-ywar-attachments" value="<?php echo esc_attr(implode(',', $review->get_thumb_ids())); ?>" />
                                    <?php endif; ?>
                                </div>
                                <div class="messages"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ('review' === $type) : ?>
                        <div class="form-element form-review-rating">
                            <?php
                            if ($multi_criteria_enabled) {
                                $multi_rating   = $review ? $review->get_multi_rating() : array();
                                $default_rating = $review ? $review->get_rating() : 0;

                                foreach ($multi_criteria as $criterion_id) {
                                    $criterion = get_term_by('term_id', $criterion_id, YITH_YWAR_Post_Types::CRITERIA_TAX);
                                    $args      = array(
                                        'label'      => $criterion->name,
                                        'rating'     => isset($multi_rating[$criterion_id]) ? intval($multi_rating[$criterion_id]) : $default_rating,
                                        'field_name' => "yith-ywar-rating[$criterion_id]",
                                        'index'      => $criterion_id,
                                    );
                                    yith_ywar_get_view('frontend/edit-form/rating-input.php', $args);
                                }
                            } else {
                                $args = array(
                                    'label'      => esc_html__('How do you feel about this product? (choose a star):', 'twmp-phonghoa'),
                                    'rating'     => $review ? intval($review->get_rating()) : 0,
                                    'field_name' => 'yith-ywar-rating',
                                    'index'      => 'rating',

                                );
                                yith_ywar_get_view('frontend/edit-form/rating-input.php', $args);
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    <?php if (yith_ywar_is_recaptcha_enabled() && 'v2' === yith_ywar_get_option('ywar_recaptcha_version')) : ?>
                        <div class="form-element form-review-recaptcha">
                            <div class="g-recaptcha" id="yith-ywar-recaptcha-<?php echo esc_attr($form_id); ?>" data-sitekey="<?php echo esc_attr(yith_ywar_get_option('ywar_recaptcha_site_key')); ?>"></div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-footer">
                    <?php if ($logged_user) : ?>
                        <span class="login-info">
                            <?php
                            /* translators: %s the current username */
                            printf(esc_html_x('Logged in as %s.', '[Frontend] Logged user info', 'twmp-phonghoa'), esc_html(wp_get_current_user()->display_name));
                            ?>
                            <a href="<?php echo esc_url(wc_logout_url()); ?>"><?php echo esc_html_x('Logout', '[Frontend] Logout form link', 'twmp-phonghoa'); ?> &gt;</a>
                        </span>
                    <?php else : ?>
                        <span class="spacer"></span>
                    <?php endif; ?>
                    <span class="submit-button <?php echo esc_attr($action); ?>-action" data-review-id="<?php echo esc_attr($review_id); ?>" data-reply-to="<?php echo esc_attr($in_reply_of); ?>" data-type="<?php echo esc_attr($action); ?>-<?php echo esc_attr($type); ?>"><?php echo esc_html($button_text); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>