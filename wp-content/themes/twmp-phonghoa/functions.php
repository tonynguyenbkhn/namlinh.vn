<?php
if (! defined('TWMP_DIR_PATH')) {
    define('TWMP_DIR_PATH', untrailingslashit(get_theme_file_path()));
}

if (! defined('TWMP_DIR_URI')) {
    define('TWMP_DIR_URI', untrailingslashit(get_theme_file_uri()));
}

if (! defined('TWMP_DIST_URI')) {
    define('TWMP_DIST_URI', untrailingslashit(get_theme_file_uri()) . '/assets');
}

if (! defined('TWMP_DIST_PATH')) {
    define('TWMP_DIST_PATH', untrailingslashit(get_theme_file_path()) . '/assets');
}

if (! defined('TWMP_DIST_JS_URI')) {
    define('TWMP_DIST_JS_URI', untrailingslashit(get_theme_file_uri()) . '/assets/js');
}

if (! defined('TWMP_DIST_JS_DIR_PATH')) {
    define('TWMP_DIST_JS_DIR_PATH', untrailingslashit(get_theme_file_path()) . '/assets/js');
}

if (! defined('TWMP_IMG_URI')) {
    define('TWMP_IMG_URI', untrailingslashit(get_theme_file_uri()) . '/assets/images');
}

if (! defined('TWMP_IMAGES_URI')) {
    define('TWMP_IMAGES_URI', untrailingslashit(get_theme_file_uri()) . '/images');
}

if (! defined('TWMP_DIST_CSS_URI')) {
    define('TWMP_DIST_CSS_URI', untrailingslashit(get_theme_file_uri()) . '/assets/css');
}

if (! defined('TWMP_DIST_CSS_DIR_PATH')) {
    define('TWMP_DIST_CSS_DIR_PATH', untrailingslashit(get_theme_file_path()) . '/assets/css');
}

require_once TWMP_DIR_PATH . '/inc/helpers/utility.php';
require_once TWMP_DIR_PATH . '/inc/helpers/autoloader.php';
require_once TWMP_DIR_PATH . '/inc/helpers/template-functions.php';

function twmp_get_theme_instance()
{
    \TWMP_THEME\Inc\TWMP_THEME::get_instance();
}

twmp_get_theme_instance();

add_filter('rest_pre_serve_request', function ($served, $result, $request, $server) {
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header('Access-Control-Allow-Headers: Content-Type, Authorization, nonce, X-WP-Nonce');
    return $served;
}, 10, 4);

if (is_singular() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
}

add_filter('get_the_archive_title', function ($title) {
    if (is_category() || is_tag() || is_tax()) {
        $title = single_term_title('', false);
    }
    return $title;
});

add_action('widgets_init', function () {
    // Đảm bảo class gốc đã load
    if (class_exists('WC_Widget_Brand_Nav')) {

        // Ghi đè class tại đây
        class TWMP_WC_Widget_Brand_Nav extends WC_Widget_Brand_Nav
        {
            protected function layered_nav_list($terms, $taxonomy, $depth = 0)
            {
                // List display.
                echo '<ul class="' . (0 === $depth ? '' : 'children ') . 'wc-brand-list-layered-nav-' . esc_attr($taxonomy) . '">';

                $term_counts        = $this->get_filtered_term_product_counts(wp_list_pluck($terms, 'term_id'), $taxonomy, 'or');
                $_chosen_attributes = $this->get_chosen_attributes();
                $current_values     = ! empty($_chosen_attributes) ? $_chosen_attributes : array();
                $found              = false;

                $filter_name = 'filter_' . $taxonomy;

                foreach ($terms as $term) {
                    $option_is_set = in_array($term->term_id, $current_values, true);
                    $count         = isset($term_counts[$term->term_id]) ? $term_counts[$term->term_id] : 0;

                    // skip the term for the current archive.
                    if ($this->get_current_term_id() === $term->term_id) {
                        continue;
                    }

                    // Only show options with count > 0.
                    if (0 < $count) {
                        $found = true;
                    } elseif (0 === $count && ! $option_is_set) {
                        continue;
                    }

                    $current_filter = isset($_GET[$filter_name]) ? explode(',', wc_clean(wp_unslash($_GET[$filter_name]))) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    $current_filter = array_map('intval', $current_filter);

                    if (! in_array($term->term_id, $current_filter, true)) {
                        $current_filter[] = $term->term_id;
                    }

                    $link = $this->get_page_base_url($taxonomy);

                    // Add current filters to URL.
                    foreach ($current_filter as $key => $value) {
                        // Exclude query arg for current term archive term.
                        if ($value === $this->get_current_term_id()) {
                            unset($current_filter[$key]);
                        }

                        // Exclude self so filter can be unset on click.
                        if ($option_is_set && $value === $term->term_id) {
                            unset($current_filter[$key]);
                        }
                    }

                    if (! empty($current_filter)) {
                        $link = add_query_arg(
                            array(
                                'filtering'  => '1',
                                $filter_name => implode(',', $current_filter),
                            ),
                            $link
                        );
                    }

                    echo '<li class="wc-layered-nav-term ' . ($option_is_set ? 'chosen' : '') . '">';

                    echo ($count > 0 || $option_is_set) ? '<a href="' . esc_url(apply_filters('woocommerce_layered_nav_link', $link)) . '">' : '<span>'; // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment

                    $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');

                    if ($image_url) {
                        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($term->name) . '">';
                    }

                    echo ($count > 0 || $option_is_set) ? '</a> ' : '</span> ';

                    // echo wp_kses_post(apply_filters('woocommerce_layered_nav_count', '<span class="count">(' . absint($count) . ')</span>', $count, $term)); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment

                    $child_terms = get_terms(
                        array(
                            'taxonomy'   => $taxonomy,
                            'hide_empty' => true,
                            'parent'     => $term->term_id,
                        )
                    );

                    if (! empty($child_terms)) {
                        $found |= $this->layered_nav_list($child_terms, $taxonomy, $depth + 1);
                    }

                    echo '</li>';
                }

                echo '</ul>';

                return $found;
            }
        }

        // Hủy và đăng ký lại widget
        unregister_widget('WC_Widget_Brand_Nav');
        register_widget('TWMP_WC_Widget_Brand_Nav');
    }
});

class TWMP_Layered_Nav_Widget extends WC_Widget_Layered_Nav
{
    public function public_layered_nav_list($terms, $taxonomy, $query_type)
    {
        return $this->layered_nav_list($terms, $taxonomy, $query_type);
    }
}

add_action('widgets_init', function () {
    // Đảm bảo class gốc đã load
    if (class_exists('WP_Widget_Recent_Posts')) {

        // Ghi đè class tại đây
        class TWMP_Widget_Recent_Posts extends WP_Widget_Recent_Posts
        {

            public function widget($args, $instance)
            {
                if (! isset($args['widget_id'])) {
                    $args['widget_id'] = $this->id;
                }

                $default_title  = __('Recent Posts', 'twmp-phonghoa');
                $title          = (! empty($instance['title'])) ? $instance['title'] : $default_title;
                $cat_id         = isset($instance['cat_id']) ? (int) $instance['cat_id'] : 0;

                /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
                $title = apply_filters('widget_title', $title, $instance, $this->id_base);

                $number = (! empty($instance['number'])) ? absint($instance['number']) : 5;
                if (! $number) {
                    $number = 5;
                }
                $show_date = isset($instance['show_date']) ? $instance['show_date'] : false;

                $r = new WP_Query(
                    /**
                     * Filters the arguments for the Recent Posts widget.
                     *
                     * @since 3.4.0
                     * @since 4.9.0 Added the `$instance` parameter.
                     *
                     * @see WP_Query::get_posts()
                     *
                     * @param array $args     An array of arguments used to retrieve the recent posts.
                     * @param array $instance Array of settings for the current widget.
                     */
                    apply_filters(
                        'widget_posts_args',
                        array(
                            'posts_per_page'      => $number,
                            'no_found_rows'       => true,
                            'post_status'         => 'publish',
                            'ignore_sticky_posts' => true,
                            'cat'                 => $cat_id !== 0 ? $cat_id : '',
                        ),
                        $instance
                    )
                );

                if (! $r->have_posts()) {
                    return;
                }
?>

                <?php echo $args['before_widget']; ?>

                <?php
                if ($title) {
                    echo $args['before_title'] . $title . $args['after_title'];
                }

                $format = current_theme_supports('html5', 'navigation-widgets') ? 'html5' : 'xhtml';

                /** This filter is documented in wp-includes/widgets/class-wp-nav-menu-widget.php */
                $format = apply_filters('navigation_widgets_format', $format);

                if ('html5' === $format) {
                    // The title may be filtered: Strip out HTML and make sure the aria-label is never empty.
                    $title      = trim(strip_tags($title));
                    $aria_label = $title ? $title : $default_title;
                    echo '<nav aria-label="' . esc_attr($aria_label) . '">';
                }
                ?>

                <ul class="recent-posts-widget">
                    <?php $index = 0; ?>
                    <?php foreach ($r->posts as $recent_post) : ?>
                        <?php
                        $post_title   = get_the_title($recent_post->ID);
                        $title        = (! empty($post_title)) ? $post_title : __('(no title)', 'twmp-phonghoa');
                        $aria_current = get_queried_object_id() === $recent_post->ID ? ' aria-current="page"' : '';
                        $thumbnail    = get_the_post_thumbnail($recent_post->ID, 'thumbnail', ['class' => 'recent-post-thumb-img']);
                        $is_first     = ($index === 0) ? ' first-post' : ' other-post';
                        ?>
                        <li class="recent-post-item<?php echo $is_first; ?>">
                            <?php if ($thumbnail) : ?>
                                <div class="recent-post-thumb"><?php echo $thumbnail; ?></div>
                            <?php endif; ?>

                            <div class="recent-post-content">
                                <a href="<?php the_permalink($recent_post->ID); ?>" <?php echo $aria_current; ?> class="recent-post-title">
                                    <?php echo $title; ?>
                                </a>
                                <?php if ($show_date) : ?>
                                    <span class="post-date"><?php echo get_the_date('', $recent_post->ID); ?></span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php $index++; ?>
                    <?php endforeach; ?>
                </ul>

                <?php
                if ('html5' === $format) {
                    echo '</nav>';
                }

                echo $args['after_widget'];
            }

            public function update($new_instance, $old_instance)
            {
                $instance              = $old_instance;
                $instance['title']     = sanitize_text_field($new_instance['title']);
                $instance['number']    = (int) $new_instance['number'];
                $instance['show_date'] = isset($new_instance['show_date']) ? (bool) $new_instance['show_date'] : false;
                $instance['cat_id']    = isset($new_instance['cat_id']) ? (int) $new_instance['cat_id'] : 0;
                return $instance;
            }

            public function form($instance)
            {
                $title      = isset($instance['title']) ? esc_attr($instance['title']) : '';
                $number     = isset($instance['number']) ? absint($instance['number']) : 5;
                $show_date  = isset($instance['show_date']) ? (bool) $instance['show_date'] : false;
                $cat_id     = isset($instance['cat_id']) ? (int) $instance['cat_id'] : 0;
                ?>
                <p>
                    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'twmp-phonghoa'); ?></label>
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                </p>

                <p>
                    <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', 'twmp-phonghoa'); ?></label>
                    <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" />
                </p>

                <p>
                    <input class="checkbox" type="checkbox" <?php checked($show_date); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
                    <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Display post date?', 'twmp-phonghoa'); ?></label>
                </p>

                <p>
                    <label for="<?php echo $this->get_field_id('cat_id'); ?>"><?php _e('Select Category:', 'twmp-phonghoa'); ?></label>
                    <select id="<?php echo $this->get_field_id('cat_id'); ?>" name="<?php echo $this->get_field_name('cat_id'); ?>" class="widefat">
                        <option value="0"><?php _e('All Categories', 'twmp-phonghoa'); ?></option>
                        <?php
                        $categories = get_categories(array(
                            'hide_empty' => false,
                        ));

                        foreach ($categories as $category) {
                            echo '<option value="' . esc_attr($category->term_id) . '"' . selected($cat_id, $category->term_id, false) . '>' . esc_html($category->name) . '</option>';
                        }
                        ?>
                    </select>
                </p>
<?php
            }
        }

        // Hủy và đăng ký lại widget
        unregister_widget('WP_Widget_Recent_Posts');
        register_widget('TWMP_Widget_Recent_Posts');
    }
});

add_action('save_post', function ($post_id) {
    // Kiểm tra quyền & autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['iframe_custom'])) {
        update_post_meta($post_id, 'iframe_custom', sanitize_text_field($_POST['iframe_custom']));
    }
});

add_action('twmp_before_content_page', function () {
    if (is_page()) {
        global $post;
        $page_id = $post->ID;

        if ($page_id && $hero_sliders = get_field('hero_slider', $page_id)) {
            $items = [];
            foreach ($hero_sliders as $image_id) {
                $items[]['image'] = $image_id;
            }
            get_template_part('templates/blocks/hero-slider', null, [
                'id' => '',
                'class' => 'section hero-slider mb-2',
                'lazyload' => false,
                'items' => $items,
                'enable_container' => false
            ]);
        }
    }
}, 10);

add_filter('pre_site_transient_update_plugins', '__return_null');

add_filter( 'term_link', 'devvn_product_cat_permalink', 10, 3 );
function devvn_product_cat_permalink( $url, $term, $taxonomy ){
    switch ($taxonomy):
        case 'product_cat':
            $taxonomy_slug = 'product-category'; //Thay bằng slug hiện tại của bạn. Mặc định là product-category
            if(strpos($url, $taxonomy_slug) === FALSE) break;
            $url = str_replace('/' . $taxonomy_slug, '', $url);
            break;
    endswitch;
    return $url;
}
// Add our custom product cat rewrite rules
function devvn_product_category_rewrite_rules($flash = false) {
    $terms = get_terms( array(
        'taxonomy' => 'product_cat',
        'post_type' => 'product',
        'hide_empty' => false,
    ));
    if($terms && !is_wp_error($terms)){
        $siteurl = esc_url(home_url('/'));
        foreach ($terms as $term){
            $term_slug = $term->slug;
            $baseterm = str_replace($siteurl,'',get_term_link($term->term_id,'product_cat'));
            add_rewrite_rule($baseterm.'?$','index.php?product_cat='.$term_slug,'top');
            add_rewrite_rule($baseterm.'page/([0-9]{1,})/?$', 'index.php?product_cat='.$term_slug.'&paged=$matches[1]','top');
            add_rewrite_rule($baseterm.'(?:feed/)?(feed|rdf|rss|rss2|atom)/?$', 'index.php?product_cat='.$term_slug.'&feed=$matches[1]','top');
        }
    }
    if ($flash == true)
        flush_rewrite_rules(false);
}
add_action('init', 'devvn_product_category_rewrite_rules');

/*Sửa lỗi khi tạo mới taxomony bị 404*/
add_action( 'create_term', 'devvn_new_product_cat_edit_success', 10, 2 );
function devvn_new_product_cat_edit_success( $term_id, $taxonomy ) {
    devvn_product_category_rewrite_rules(true);
}

add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );

// add_action( 'after_setup_theme', function() {
//     remove_theme_support( 'duotone' );
// });

// add_action('wp_enqueue_scripts', function() {
//     wp_dequeue_style('core-block-supports-duotone');
// }, 100);

add_action('template_redirect', function() {
    ob_start('remove_core_inline_css');
});

function remove_core_inline_css($html) {
    return preg_replace('#<style[^>]*id=[\'"]core-block-supports-inline-css[\'"][^>]*>.*?</style>#si', '', $html);
}

add_action( 'template_redirect', 'redirect_old_product_category_url' );

function redirect_old_product_category_url() {
    // Lấy URL hiện tại
    $requested_url = $_SERVER['REQUEST_URI'];

    // Kiểm tra nếu URL có chứa /product-category/
    if ( strpos( $requested_url, '/product-category/' ) !== false ) {
        // Tách slug (vd: /product-category/shoes/ → shoes)
        $slug = str_replace( '/product-category/', '', $requested_url );

        // Đảm bảo kết thúc bằng dấu /
        if ( substr($slug, -1) !== '/' ) {
            $slug .= '/';
        }

        // Tạo URL mới
        $new_url = home_url( '/' . $slug );

        // Redirect 301
        wp_redirect( $new_url, 301 );
        exit;
    }
}
