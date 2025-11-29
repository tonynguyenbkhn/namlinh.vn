<?php

/**
 * Plugin Name: TWMP WooCommerce Search
 * Description: AJAX product search for WooCommerce. Adds a shortcode [twmp_woocommerce_search] which shows a search input and a popup with 5 results after typing 3 or more characters.
 * Version: 1.0.0
 * Author: Generated
 * Text Domain: twmp-woocommerce-search
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class TWMP_WooCommerce_Search
{

    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_shortcode('twmp_woocommerce_search', array($this, 'render_search_form'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_twmp_woo_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_twmp_woo_search', array($this, 'ajax_search'));
    }

    public function init()
    {
        load_plugin_textdomain('twmp-woocommerce-search', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function enqueue_assets()
    {
        $dir = plugin_dir_url(__FILE__);
        wp_register_script('twmp-woo-search-js', $dir . 'assets/twmp-woo-search.js', array('jquery'), '1.0.0', true);
        wp_register_style('twmp-woo-search-css', $dir . 'assets/twmp-woo-search.css', array(), '1.0.0');
    }

    public function render_search_form($atts = array())
    {
        $atts = shortcode_atts(array(
            'placeholder' => esc_attr__('Tìm sản phẩm...', 'twmp-woocommerce-search'),
            'min_chars' => 3,
            'max_results' => 5,
        ), $atts, 'twmp_woocommerce_search');

        // Do not attempt to load assets or ajax if WooCommerce isn't active

        ob_start();
        if (!class_exists('WooCommerce')) {
?>
            <div class="twmp-woo-search-notice"><?php esc_html_e('WooCommerce chưa được kích hoạt.', 'twmp-woocommerce-search'); ?></div>
        <?php
            return ob_get_clean();
        }

        // Enqueue assets only when shortcode is used
        wp_enqueue_script('twmp-woo-search-js');
        wp_enqueue_style('twmp-woo-search-css');
        wp_localize_script('twmp-woo-search-js', 'TWMPWooSearch', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('twmp_woo_search_nonce'),
            'min_chars' => intval($atts['min_chars']),
            'max_results' => intval($atts['max_results']),
        ));
        ?>
        <div class="twmp-woo-search">
            <form class="twmp-woo-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" class="twmp-woo-search-input" name="s" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" autocomplete="off" />
                <button type="submit" class="twmp-woo-search-button"><?php esc_html_e('Tìm kiếm', 'twmp-woocommerce-search'); ?></button>
            </form>
            <div class="twmp-woo-search-results" aria-hidden="true"></div>
        </div>
<?php
        return ob_get_clean();
    }

    public function ajax_search()
    {
        check_ajax_referer('twmp_woo_search_nonce', 'nonce');

        if (!class_exists('WooCommerce')) {
            wp_send_json_error(array('message' => __('WooCommerce is not activated.', 'twmp-woocommerce-search')));
        }

        $term = isset($_REQUEST['term']) ? sanitize_text_field(wp_unslash($_REQUEST['term'])) : '';
        $max_results = isset($_REQUEST['max_results']) ? intval($_REQUEST['max_results']) : 5;

        if (strlen($term) < 3) {
            wp_send_json_success(array('products' => array()));
        }

        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            's' => $term,
            'post_status' => 'publish',
        );

        $query = new WP_Query($args);
        $products = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $product = wc_get_product($product_id);
                if (!$product) continue;
                // Only include if title contains the full search term (case-insensitive)
                $search_lc = mb_strtolower(trim($term)); // "a pro"
                $title_lc = mb_strtolower(get_the_title($product_id));
                $search_lc = mb_strtolower(trim($term));
                if (!preg_match('/\b' . preg_quote($search_lc, '/') . '\b/u', $title_lc)) {
                    continue; // bỏ qua nếu không match nguyên cụm từ
                }
                $products[] = array(
                    'id' => $product_id,
                    'title' => get_the_title(),
                    'permalink' => get_permalink($product_id),
                    'price' => $product->get_price_html(),
                    'thumb' => get_the_post_thumbnail_url($product_id, 'thumbnail') ?: wc_placeholder_img_src(),
                );
                if (count($products) >= $max_results) break;
            }
        }
        wp_reset_postdata();

        wp_send_json_success(array('products' => $products));
    }
}

new TWMP_WooCommerce_Search();

/**
 * Developers: Short helper function to output the shortcode directly
 */
function twmp_woocommerce_search_show()
{
    echo do_shortcode('[twmp_woocommerce_search]');
}
