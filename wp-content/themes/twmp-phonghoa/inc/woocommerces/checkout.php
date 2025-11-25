<?php

remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
remove_action('woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10);

// Layout
add_action('woocommerce_checkout_before_customer_details',  'wcs_checkout_page_block_open', 10);
add_action('woocommerce_checkout_after_customer_details', 'wcs_checkout_page_block_between', 40);
add_action('woocommerce_checkout_after_order_review', 'wcs_checkout_page_block_close', 90);

// add_filter('woocommerce_default_address_fields', 'wcs_checkout_update_fields_order');
// add_filter('woocommerce_checkout_fields', 'wcs_checkout_update_placeholder_fields');

// Move shipping section
// add_filter('woocommerce_cart_ready_to_calc_shipping', '__return_false');

add_action('woocommerce_before_checkout_form', 'wcs_checkout_page_open', 5);
// add_action('woocommerce_before_checkout_form', 'wcs_checkout_render_shop_steps', 6);

function wcs_checkout_page_open()
{
  $block_attributes = array(
    'endpoint' => [
      'get_tinh_tp',
      'get_quan_huyen',
      'get_xa_phuong',
    ]
  );
  echo '<div class="page-block page-block--checkout" data-settings='.json_encode($block_attributes).' data-block="checkout-custom">';
}

add_action('woocommerce_after_checkout_form', 'wcs_checkout_page_close', 100);

function wcs_checkout_page_close()
{
  echo '</div>';
}

function wcs_checkout_page_block_open()
{
  echo '<div class="grid page-block__grid">';
  echo '<div class="grid__col page-block__col page-block__col--main">';
}

function wcs_checkout_page_block_between()
{
  echo '</div>';
  echo '<div class="grid__col page-block__col page-block__col--sidebar">';
}

function wcs_checkout_page_block_close()
{
  echo '</div>';
  echo '</div>';
}

function wcs_checkout_update_fields_order($fields)
{
  unset($fields['company']);
  unset($fields['address_2']);
  unset($fields['postcode']);

  return $fields;
}

function wcs_checkout_update_placeholder_fields($fields)
{
  $fields['billing']['billing_first_name']['placeholder'] = esc_html__('First name', 'twmp-phonghoa');
  $fields['billing']['billing_last_name']['placeholder'] = esc_html__('Last name', 'twmp-phonghoa');
  $fields['billing']['billing_phone']['placeholder'] = esc_html__('Phone', 'twmp-phonghoa');
  $fields['billing']['billing_city']['placeholder'] = esc_html__('City', 'twmp-phonghoa');
  $fields['billing']['billing_email']['placeholder'] = esc_html__('Email', 'twmp-phonghoa');

  $fields['shipping']['shipping_first_name']['placeholder'] = esc_html__('First name', 'twmp-phonghoa');
  $fields['shipping']['shipping_last_name']['placeholder'] = esc_html__('Last name', 'twmp-phonghoa');
  $fields['shipping']['shipping_phone']['placeholder'] = esc_html__('Phone', 'twmp-phonghoa');
  $fields['shipping']['shipping_city']['placeholder'] = esc_html__('City', 'twmp-phonghoa');

  return $fields;
}

function wcs_checkout_render_shop_steps()
{
  get_template_part('templates/blocks/shop-steps', null, []);
}

add_action('devvn_checkout_fields', function ($fields) {
  unset($fields['billing']['billing_state']);
  unset($fields['billing']['billing_city']);
  unset($fields['billing']['billing_address_1']);
  unset($fields['billing']['billing_address_2']);

  return $fields;
}, 10, 1);


add_filter('woocommerce_checkout_fields', function ($fields) {
  $fields['billing']['billing_city_province_shop'] = array(
    'type'              => 'select',
    'label'             => esc_html__('Province/City', 'twmp-phonghoa'),
    'required'          => true,
    'class'             => array('form-row-wide'),
    'input_class'       => array('regular-select'),
    'options'           => array(
      ''                => esc_html__('Province/City', 'twmp-phonghoa'),
      'Hồ Chí Minh'     => 'Hồ Chí Minh',
    )
  );
  return $fields;
});

// add_filter('woocommerce_checkout_fields', function ($fields) {
//   $fields['billing']['billing_delivery_address'] = array(
//     'type'        => 'select',
//     'label'       => esc_html__('Province/City', 'twmp-phonghoa'),
//     'required'    => true,
//     'class'       => array('form-row-wide'),
//     'input_class' => 'regular-select',
//     'options'     => get_tinh_thanh_pho()
//   );
//   return $fields;
// });

add_filter('woocommerce_checkout_fields', function ($fields) {
  $fields['billing']['billing_district_shop'] = array(
    'type'        => 'select',
    'label'       => esc_html__('District', 'twmp-phonghoa'),
    'required'    => true,
    'class'       => array('form-row-wide'),
    'input_class' => array('regular-select'),
    'options'     => array(
      ''          => esc_html__('District', 'twmp-phonghoa'),
      'Quận 1'    => 'Quận 1',
    )
  );
  return $fields;
});


add_action('wp_enqueue_scripts', function () {
  if (is_checkout()) {
    wp_dequeue_style('select2');
    wp_dequeue_script('select2');

    wp_dequeue_style('selectWoo');
    wp_dequeue_script('selectWoo');

    wp_dequeue_script('wc-enhanced-select');
  }
}, 100);

add_action('woocommerce_after_checkout_form', function () {
?>
  <script>
    jQuery(function($) {
      $('.select2-hidden-accessible').each(function() {
        if ($(this).hasClass('select2-hidden-accessible')) {
          $(this).select2('destroy');
        }
      });
    });
  </script>
<?php
});

add_action('wp_ajax_get_tinh_tp_by_matp', 'load_tinh_tp_ajax');
add_action('wp_ajax_nopriv_get_tinh_tp_by_matp', 'load_tinh_tp_ajax');
function load_tinh_tp_ajax()
{
  $data = get_tinh_thanh_pho();
  wp_send_json_success($data);
}

add_action('wp_ajax_get_quan_huyen_by_matp', 'load_quan_huyen_ajax');
add_action('wp_ajax_nopriv_get_quan_huyen_by_matp', 'load_quan_huyen_ajax');
function load_quan_huyen_ajax()
{
  $matp = $_POST['matp'];
  $data = get_quan_huyen();
  $result = [];

  foreach ($data as $item) {
    if ($item['matp'] === $matp) {
      $result[] = $item;
    }
  }

  wp_send_json_success($result);
}

add_action('wp_ajax_get_xa_phuong_by_maqh', 'load_xa_phuong_ajax');
add_action('wp_ajax_nopriv_get_xa_phuong_by_maqh', 'load_xa_phuong_ajax');
function load_xa_phuong_ajax()
{
  $maqh = $_POST['maqh'];
  $data = get_xa_phuong_thi_tran();
  $result = [];

  foreach ($data as $item) {
    if ($item['maqh'] === $maqh) {
      $result[] = $item;
    }
  }

  wp_send_json_success($result);
}


add_action('rest_api_init', function () {
  register_rest_route('twmp/v1', '/get_tinh_tp', [
    'methods'             => 'GET',
    'callback'            => 'twmp_rest_get_tinh_tp',
    'permission_callback' => '__return_true',
  ]);

  register_rest_route('twmp/v1', '/get_quan_huyen', [
    'methods'             => 'GET',
    'callback'            => 'twmp_rest_get_quan_huyen',
    'permission_callback' => '__return_true',
  ]);

  register_rest_route('twmp/v1', '/get_xa_phuong', [
    'methods'             => 'GET',
    'callback'            => 'twmp_rest_get_xa_phuong',
    'permission_callback' => '__return_true',
  ]);
});

function twmp_rest_get_tinh_tp(WP_REST_Request $request)
{
  $data = get_tinh_thanh_pho();
  return new WP_REST_Response($data, 200);
}

function twmp_rest_get_quan_huyen(WP_REST_Request $request)
{
  $matp = $request->get_param('matp');
  if (empty($matp)) {
    return new WP_REST_Response([], 200);
  }

  $data = get_quan_huyen();
  $result = array_filter($data, function ($item) use ($matp) {
    return isset($item['matp']) && $item['matp'] === $matp;
  });

  return new WP_REST_Response(array_values($result), 200);
}

function twmp_rest_get_xa_phuong(WP_REST_Request $request)
{
  $maqh = $request->get_param('maqh');
  if (empty($maqh)) {
    return new WP_REST_Response([], 200);
  }

  $data = get_xa_phuong_thi_tran();
  $result = array_filter($data, function ($item) use ($maqh) {
    return isset($item['maqh']) && $item['maqh'] === $maqh;
  });

  return new WP_REST_Response(array_values($result), 200);
}

add_filter('woocommerce_add_to_cart_redirect', function($url) {
    // Nếu đang add to cart từ quick-buy (không phải từ page giỏ hàng)
    if (!is_cart()) {
        return wc_get_checkout_url(); // Chuyển luôn đến trang checkout
    }
    return $url;
});
