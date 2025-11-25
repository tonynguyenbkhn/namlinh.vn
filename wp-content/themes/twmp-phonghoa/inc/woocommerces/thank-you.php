<?php

add_action('woocommerce_before_thankyou', 'wcs_thank_you_icon');

function wcs_thank_you_icon()
{
    echo '<div class="thank-you__icon">' . twmp_get_svg_icon('thank-you') . '</div>';
}

add_filter('woocommerce_order_get_formatted_billing_address', 'custom_format_billing_address', 10, 3);

function custom_format_billing_address($address, $raw_address, $order)
{
    if (! $raw_address) {
        return '<p class="custom-empty-address">Không có địa chỉ</p>';
    }

    $parts = explode(':', $raw_address['delivery_form'], 2);

    $sexy = strtolower($raw_address['sexy']) === 'male' ? 'Anh' : 'Chị';

    $dia_chi = $raw_address['address_3'] 
    ? $raw_address['address_3'] . ', ' . $raw_address['wards_and_communes'] . ', ' . $raw_address['district_district'] . ', ' . $raw_address['delivery_address']
    : $raw_address['district_shop'] . ', ' . $raw_address['city_province_shop'];

    return '<div class="custom-billing-address">
        <ul>
            <li>
                <span>' . $sexy . ':</span>
                <span>' . $raw_address['last_name'] . '</span>
            </li>
            <li>
                <span>Số điện thoại:</span>
                <span>' . $raw_address['phone'] . '</span>
            </li>
            <li>
                <span>Hình thức nhận hàng:</span>
                <span>' . $parts['1'] . '</span>
            </li>
            <li>
                <span>Địa chỉ:</span>
                <span>' . $dia_chi . '</span>
            </li>
        </ul>
    </div>';
}

add_filter('woocommerce_thankyou_order_received_text', function ($text, $order) {
    if (!$order instanceof WC_Order) {
        return $text;
    }

    // Lấy tên người nhận
    $full_name = $order->get_billing_last_name(); // bạn có thể nối cả họ tên nếu muốn
    $gender = $order->get_meta('_billing_sexy');

    // Xác định cách xưng hô
    $title = (strtolower($gender) === 'male') ? 'Anh' : ((strtolower($gender) === 'female') ? 'Chị' : '');

    // Tổng tiền (đã định dạng)
    $total = $order->get_formatted_order_total();

    // Tạo thông điệp cá nhân hóa
    $message  = sprintf(
        esc_html__('Thank you %1$s for shopping at Phong Hoa!', 'twmp-phonghoa'),
        "<span>{$title} {$full_name}</span>"
    );
    $message .= "\n";
    $message .= sprintf(
        esc_html__('Please pay the amount %s to the delivery staff upon receipt of the goods.', 'twmp-phonghoa'),
        $total
    );

    return nl2br(esc_html($message)); // xuống dòng và escape an toàn
}, 10, 2);


add_action('woocommerce_thankyou', function () { 
    $thank_you_footer = get_field('thank_you_footer', 'option')
    ?>
    <div class="d-flex flex-column justify-content-center align-items-center">
        <?php
        get_template_part('templates/core-blocks/button', null, [
            'class'       => 'back-home',
            'button_text' => esc_html__('Back to home page', 'twmp-phonghoa'),
            'button_url' => home_url(),
        ]);
        ?>
        <div class="copyright-order">
            <?php echo wp_kses_post( $thank_you_footer ); ?>
        </div>
    </div>
<?php
}, 10);
