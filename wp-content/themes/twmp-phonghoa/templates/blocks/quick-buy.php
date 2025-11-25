<?php

global $product;

$condition = $product->is_type('simple') || $product->is_type('variable');

$button_class = 'quick_buy rounded-0';

$button_text = __("Buy Now", "twmp-phonghoa");
$sub_button_text = __("Home delivery (COD) or pick up at store", "twmp-phonghoa");

if ($condition) {
    echo $product->is_type('simple') ? '<input type="hidden" name="product_id" value="'.$product->get_id().'"><a data-block="quick-buy" href="#" class=" w-100 text-white ' . $button_class . '"><span>' . $button_text . '</span><span>'.$sub_button_text.'</span></a>' : '<a data-block="quick-buy" href="#" class="text-white ' . $button_class . '"><span>' . $button_text . '</span><span>'.$sub_button_text.'</span></a>';
}