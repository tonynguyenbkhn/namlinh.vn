<?php
$data = wp_parse_args(
    $args,
    array(
        'class'              => '',
        'post_id '           => '',
        'post_data'          => '',
        'post_title_limit'   => 25,
        'post_excerpt_limit' => 30,
        'view_more_button'   => __('View more', 'taiwebmienphi-plus'),
        'options' => [
            'show_excerpt' => true,
            'show_date' => true,
            'show_author' => true,
            'show_categories' => true,
            'show_view_more' => true
        ]
    )
);

$data['options']['show_excerpt'] = !!$data['showDescription'];
$data['options']['show_date'] = !!$data['showDate'];
$data['options']['show_author'] = !!$data['showAuthor'];
$data['options']['show_categories'] = !!$data['showCategory'];
$data['options']['show_view_more'] = !!$data['showViewMore'];

$_class  = 'post-row';
$_class .= ! empty($data['class']) ? esc_attr(' ' . $data['class']) : '';

$options = $data['options'];

$args_related_post = array(
    'post_status' => 'publish',
    'post_type' => 'post',
    'posts_per_page' => 5,
);
if (is_singular('tin-tuc')) {
    global $post;
    $terms = wp_get_post_terms($post->ID, 'danh-muc', array('fields' => 'ids'));
    // if ( ! empty( $terms ) && count( (array) $terms ) <= 5 ) {
    //     $args_related_post['tax_query'] = array(
    //         array(
    //             'taxonomy' => 'danh-muc',
    //             'field'    => 'term_id',
    //             'terms'    => array_map('intval', (array) $terms),
    //             'include_children' => false,
    //             'operator' => 'IN',
    //         ),
    //     );
    // }
    // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
    $args_related_post['post__not_in'] = array($post->ID);
}
$query = new WP_Query($args_related_post);
if ($query->have_posts()) :
    while ($query->have_posts()) : $query->the_post();
        $post_data = get_post(get_the_ID());

        $post_title       = ! empty($data['post_title_limit']) ? wp_trim_words($post_data->post_title, $data['post_title_limit'], '...') : $post_data->post_title;
        $post_description = $post_data->post_excerpt ? wp_trim_words($post_data->post_excerpt, $data['post_excerpt_limit'], '...') : wp_trim_words($post_data->post_content, $data['post_excerpt_limit'], '...');
?>
        <article class="<?php echo esc_attr($_class); ?>">
            <div class="post-row__wrapper row">
                <?php /* <div class="col-lg-4 col-md-5 col-sm-5 col-5">
                    <a class="image__overlay-link post-row__overlay-link" href="<?php echo esc_url_raw(get_permalink($post_data)); ?>" title="">
                        <?php
                        get_template_part('templates/core-blocks/image', null, [
                            'image_id' => get_post_thumbnail_id($post_data),
                            'image_size' => 'full',
                            'lazyload' => false,
                            'class' => 'pe-none image--cover post-row__image',
                        ]);
                        ?>
                    </a>
                </div> */ ?>
                <!-- <div class="col-lg-8 col-lg-7 col-sm-7 col-7"> -->
                <div class="col-12">
                    <div class="post-row__content">
                        <a class="post-row__title-link" href="<?php echo esc_url_raw(get_permalink($post_data)); ?>" title="">
                            <h3 class="post-row__title h6"><?php echo esc_html($post_title); ?></h3>
                        </a>
                        <?php if ($options['show_excerpt']): ?>
                            <p class="post-row__description"><?php echo esc_html($post_description); ?> </p>
                        <?php endif; ?>
                        <?php
                        get_template_part('templates/blocks/post-meta', null, [
                            'date' => $options['show_date'],
                            'author' => $options['show_author'],
                            'categories' => $options['show_categories'],
                            'class' => 'post-row__post-meta'
                        ]);
                        ?>
                        <?php if ($options['show_view_more'] && $data['view_more_button'] !== '') : ?>
                            <div class="post-row__footer">
                                <?php
                                get_template_part('templates/core-blocks/button', null, [
                                    'class'       => 'post-row__button rounded-0 text-white',
                                    'button_text' => $data['view_more_button'],
                                    'button_url' => esc_url_raw(get_permalink($post_data)),
                                    'type' => 'dark'
                                ]);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
<?php
    endwhile;
    wp_reset_postdata();
endif;
