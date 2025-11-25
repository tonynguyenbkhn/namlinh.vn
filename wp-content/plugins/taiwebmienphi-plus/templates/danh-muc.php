<div class="widget-category">
    <h3 class="wp-block-heading"><?php echo esc_html__('Danh mục', 'taiwebmienphi-plus'); ?></h3>
    <ul class="wp-block-categories-list wp-block-danh-muc">
        <?php
        $terms = get_terms([
            'taxonomy' => 'category',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                $term_link = get_term_link($term);
                if (is_wp_error($term_link)) continue;

                $current_class = (is_tax('danh-muc', $term->slug)) ? ' current-cat' : '';

                echo '<li class="cat-item cat-item-' . esc_attr($term->term_id) . esc_attr($current_class) . '">';
                echo '<a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a> ';
                echo '(' . esc_html($term->count) . ')';
                echo '</li>';
            }
        }
        ?>
    </ul>
</div>