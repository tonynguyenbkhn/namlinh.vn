<?php

namespace TWMP_THEME\Inc;

use TWMP_THEME\Inc\Traits\Singleton;

class Assets_Theme
{
	use Singleton;

	var $theme_version;
	var $theme_env;

	protected function __construct()
	{

		// load class.
		$this->setup_hooks();

		$this->theme_version = WP_DEBUG ? time() : wp_get_theme()->Get('Version');
		$this->theme_env = !twmp_theme_is_localhost() ? '.min' : '';
	}

	protected function setup_hooks()
	{
		add_action('wp_enqueue_scripts', [$this, 'twmp_critical_frontend_assets']);
		add_action('wp_enqueue_scripts', [$this, 'twmp_frontend_assets']);

		add_filter('style_loader_tag', [$this, 'preload_style_tag'], 10, 4);
		add_filter('script_loader_tag', [$this, 'add_defer_to_script'], 10, 3);
	}

	function preload_style_tag($html, $handle, $href, $media)
	{
		$preload_handles = ['twmp-review', 'twmp-frontend'];
		if (in_array($handle, $preload_handles)) {
			$html = "<link rel='preload' as='style' href='{$href}' onload=\"this.onload=null;this.rel='stylesheet'\">";
			$html .= "<noscript><link rel='stylesheet' href='{$href}'></noscript>";
		}
		return $html;
	}

	function add_defer_to_script($tag, $handle, $src)
	{
		$defer_handles = ['twmp-frontend', 'twmp-review'];

		if (in_array($handle, $defer_handles)) {
			return "<script src='{$src}' defer></script>";
		}

		return $tag;
	}

	public function twmp_critical_frontend_assets()
	{
		$variables_css_context = file_get_contents(get_theme_file_path('variables.css'));
		$bootstrap_css_context = '';
		$critical_css_context = '';

		$bootstrap_css_context = file_get_contents(get_theme_file_path('assets/css/bootstrap.min.css'));
		$critical_css_context = file_get_contents(get_theme_file_path('assets/css/critical_frontend.min.css'));

		// $bootstrap_css_context = file_get_contents(get_theme_file_path('assets/css/bootstrap.css'));
		// $critical_css_context = file_get_contents(get_theme_file_path('assets/css/critical_frontend.css'));

		if (!empty($variables_css_context)) {
			wp_register_style('twmp-variables', false);
			wp_enqueue_style('twmp-variables', false);
			wp_add_inline_style('twmp-variables', twmp_format_css_variables($bootstrap_css_context . $variables_css_context . $critical_css_context));
		}
	}

	public function twmp_frontend_assets()
	{
		// wp_enqueue_style('twmp-frontend', get_stylesheet_directory_uri() . '/assets/css/frontend.min.css', [], $this->theme_version);
		wp_enqueue_style('twmp-frontend', get_stylesheet_directory_uri() . '/assets/css/frontend.min.css', [], $this->theme_version);
		wp_enqueue_script('twmp-frontend', get_stylesheet_directory_uri() . '/assets/js/frontend.min.js', ['jquery'], $this->theme_version);
		wp_enqueue_script('twmp-woocommerce', get_stylesheet_directory_uri() . '/assets/js/woocommerce.min.js', ['jquery'], $this->theme_version);

		// wp_enqueue_style('twmp-frontend', get_stylesheet_directory_uri() . '/assets/css/frontend.css', [], $this->theme_version);
		// wp_enqueue_script('twmp-frontend', get_stylesheet_directory_uri() . '/assets/js/frontend.js', ['jquery'], $this->theme_version);
		// wp_enqueue_script('twmp-woocommerce', get_stylesheet_directory_uri() . '/assets/js/woocommerce.js', ['jquery'], $this->theme_version);

		wp_enqueue_script('twmp-woocommerce-shop', get_stylesheet_directory_uri() . '/custom/shop.js', ['jquery'], $this->theme_version);
		wp_enqueue_script('twmp-woocommerce-checkout', get_stylesheet_directory_uri() . '/custom/checkout.js', ['jquery'], $this->theme_version);
		$locale_settings = array(
			'woocommerce' => array(
				'checkoutUrl'    => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '',
				'addToCartUrl'    => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
			),
			'ajax' => array(
				'restUrl'    => get_rest_url(null, 'twmp/v1'),
				'url'        => admin_url('admin-ajax.php'),
				'ajax_error' => __('Sorry, something went wrong. Please refresh this page and try again!', 'twmp-phonghoa'),
				'nonce'      => wp_create_nonce('twmp-config-nonce'),
			),
			'themePath' => get_template_directory_uri(),
			'message' => array(
				'notfound' => esc_html__('No order found.', 'twmp-phonghoa'),
				'error' => esc_html__('System error, please try again.', 'twmp-phonghoa')
			)
		);

		wp_localize_script('twmp-frontend', 'twmpConfig', $locale_settings);
	}
}
