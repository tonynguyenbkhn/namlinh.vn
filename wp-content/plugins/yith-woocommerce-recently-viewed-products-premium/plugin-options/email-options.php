<?php
/**
 * Email settings options
 *
 * @package YITH\RecentlyViewedProducts\PluginOptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * APPLY_FILTERS: yith_wrvp_panel_email_options
 *
 * Filters the options available in the 'Email Settings' tab.
 *
 * @param array $options Array with options.
 *
 * @return array
 */
return apply_filters(
	'yith_wrvp_panel_email_options',
	array(
		'email' => array(
			array(
				'title' => __( 'E-mail settings', 'yith-woocommerce-recently-viewed-products' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith-wrvp-email-options',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[enabled]',
				'title'     => __( 'Send email notifications', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Select whether to send notification emails to your users about their recently viewed products.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'default'   => 'yes',
			),
			array(
				'id'        => 'yith-wrvp-email-period',
				'title'     => __( 'Schedule email', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Set how many days after their last login the system should send the email to users.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'number',
				'default'   => '7',
				'min'       => '1',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[number_products]',
				'title'     => __( 'Number of products', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Choose how many of the recently viewed products to show in the email.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'number',
				'default'   => '5',
				'min'       => '0',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[products_type]',
				'title'     => __( 'Select which products to show', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Choose whether to show only viewed products or also similar products.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'options'   => array(
					'viewed'  => __( 'Only viewed products', 'yith-woocommerce-recently-viewed-products' ),
					'similar' => __( 'Viewed products and similar items', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default'   => 'viewed',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[products_order]',
				'title'     => __( 'Order products by', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Choose in which order the products should be shown.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'radio',
				'options'   => array(
					'rand'     => __( 'Random', 'yith-woocommerce-recently-viewed-products' ),
					'viewed'   => __( 'Latest viewed', 'yith-woocommerce-recently-viewed-products' ),
					'sales'    => __( 'Sales', 'yith-woocommerce-recently-viewed-products' ),
					'newest'   => __( 'Newest', 'yith-woocommerce-recently-viewed-products' ),
					'high-low' => __( 'Price: High to Low', 'yith-woocommerce-recently-viewed-products' ),
					'low-high' => __( 'Price: Low to High', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default'   => 'rand',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[cat_most_viewed]',
				'title'     => __( 'Show only products from the most viewed category', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Enable to only show products from the most-viewed category by the user.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'default'   => 'no',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[custom_products_enabled]',
				'title'     => __( 'Add also custom products', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Enable to select additional products to promote in the email.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'default'   => 'yes',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[custom_products]',
				'title'     => __( 'Select custom products', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Choose the products to promote in the email.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'ajax-products',
				'multiple'  => true,
				'default'   => '',
				'deps'      => array(
					'target-id' => 'woocommerce_yith_wrvp_mail_settings\\[custom_products\\]',
					'id'        => 'woocommerce_yith_wrvp_mail_settings\\[custom_products_enabled\\]',
					'value'     => 'yes',
					'type'      => 'hide',
				),
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[coupon_enable]',
				'title'     => __( 'Add a coupon', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Enable to add a coupon in the email.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'default'   => 'no',
			),
			array(
				'id'                => 'woocommerce_yith_wrvp_mail_settings[coupon_type]',
				'title'             => __( 'Coupon type', 'yith-woocommerce-recently-viewed-products' ),
				'desc'              => __( 'Choose to use an existing coupon or to create automatically a coupon for the products added in the email.', 'yith-woocommerce-recently-viewed-products' ),
				'type'              => 'yith-field',
				'yith-type'         => 'radio',
				'options'           => array(
					'exs' => __( 'Use an existing coupon', 'yith-woocommerce-recently-viewed-products' ),
					'new' => __( 'Create a coupon', 'yith-woocommerce-recently-viewed-products' ),
				),
				'default'           => 'new',
				'custom_attributes' => array(
					'data-deps'       => 'woocommerce_yith_wrvp_mail_settings[coupon_enable]',
					'data-deps_value' => 'yes',
				),
			),
			array(
				'id'                => 'woocommerce_yith_wrvp_mail_settings[coupon_code]',
				'title'             => __( 'Coupon code', 'yith-woocommerce-recently-viewed-products' ),
				'desc'              => __( 'Type the coupon code to use in the email.', 'yith-woocommerce-recently-viewed-products' ),
				'type'              => 'yith-field',
				'yith-type'         => 'text',
				'default'           => '',
				'class'             => 'yith_wrvp_coupon_validate',
				'custom_attributes' => array(
					'data-deps'       => 'woocommerce_yith_wrvp_mail_settings[coupon_enable],woocommerce_yith_wrvp_mail_settings[coupon_type]',
					'data-deps_value' => 'yes,exs',
				),
			),
			array(
				'id'                => 'woocommerce_yith_wrvp_mail_settings[coupon_amount]',
				'title'             => __( 'Coupon amount', 'yith-woocommerce-recently-viewed-products' ),
				'desc'              => __( 'The coupon amount (Product % Discount).', 'yith-woocommerce-recently-viewed-products' ),
				'type'              => 'yith-field',
				'yith-type'         => 'number',
				'default'           => '',
				'min'               => 0,
				'max'               => 100,
				'custom_attributes' => array(
					'placeholder'     => '%',
					'data-deps'       => 'woocommerce_yith_wrvp_mail_settings[coupon_enable],woocommerce_yith_wrvp_mail_settings[coupon_type]',
					'data-deps_value' => 'yes,new',
				),
			),
			array(
				'id'                => 'woocommerce_yith_wrvp_mail_settings[coupon_expiry]',
				'title'             => __( 'Coupon expiration date', 'yith-woocommerce-recently-viewed-products' ),
				'desc'              => __( 'Set the number of days before the coupon expires, that is sent with the email.', 'yith-woocommerce-recently-viewed-products' ),
				'type'              => 'yith-field',
				'yith-type'         => 'number',
				'default'           => 7,
				'min'               => 1,
				'custom_attributes' => array(
					'data-deps'       => 'woocommerce_yith_wrvp_mail_settings[coupon_enable],woocommerce_yith_wrvp_mail_settings[coupon_type]',
					'data-deps_value' => 'yes,new',
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'yith-wrvp-end-email-options',
			),
			array(
				'title' => __( 'Email customization', 'yith-woocommerce-recently-viewed-products' ),
				'type'  => 'title',
				'desc'  => '',
				'id'    => 'yith-wrvp-email-customization',
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[upload_logo]',
				'title'     => __( 'Logo image', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Upload logo image for email header. Use <code>{logo_image}</code> placeholder in the header to show it.', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'upload',
				'default'   => '',
			),
			array(
				'id'                => 'woocommerce_yith_wrvp_mail_settings[heading]',
				'title'             => __( 'Email heading', 'yith-woocommerce-recently-viewed-products' ),
				'desc'              => __( 'Enter a text for the email heading or use one of the available placeholder <code>{site_title}, {site_address}, {site_url}</code>', 'yith-woocommerce-recently-viewed-products' ),
				'type'              => 'yith-field',
				'yith-type'         => 'text',
				'default'           => '{blogname}',
				'custom_attributes' => array(
					'placeholder' => '{blogname}',
				),
			),
			array(
				'id'                => 'woocommerce_yith_wrvp_mail_settings[subject]',
				'title'             => __( 'Email subject', 'yith-woocommerce-recently-viewed-products' ),
				'desc'              => __( 'Enter a text for the email subject or use one of the available placeholder <code>{site_title}, {site_address}, {site_url}</code>. <br>Use the placeholder <code>{first_product_title}</code> to show the name of the first product of the list as subject.', 'yith-woocommerce-recently-viewed-products' ),
				'type'              => 'yith-field',
				'yith-type'         => 'text',
				'default'           => __( 'Are you still looking for these products?', 'yith-woocommerce-recently-viewed-products' ),
				'custom_attributes' => array(
					'placeholder' => __( 'Are you still looking for these products?', 'yith-woocommerce-recently-viewed-products' ),
				),
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[email_type]',
				'title'     => __( 'Email format', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Choose which type of email to send.', 'yith-woocommerce-recently-viewed-products' ),
				'default'   => 'html',
				'type'      => 'yith-field',
				'yith-type' => 'select',
				'class'     => 'wc-enhanced-select',
				'options'   => array(
					'plain'     => __( 'Plain', 'yith-woocommerce-recently-viewed-products' ),
					'html'      => __( 'HTML', 'yith-woocommerce-recently-viewed-products' ),
					'multipart' => __( 'Multipart', 'yith-woocommerce-recently-viewed-products' ),
				),
			),
			array(
				'id'        => 'woocommerce_yith_wrvp_mail_settings[mail_content]',
				'title'     => __( 'Email content', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => '',
				'type'      => 'yith-field',
				'yith-type' => 'textarea-editor',
				'default'   => __( 'According to your research, you may be interested in the following products. Moreover, purchasing one of these products will entitle you to receive a discount with the following coupon {coupon_code}{products_list}', 'yith-woocommerce-recently-viewed-products' ),
			),
			array(
				'id'      => 'yith-wrvp-image-size',
				'title'   => __( 'Thumbnail Size', 'yith-woocommerce-recently-viewed-products' ),
				// translators: %s is the string "regenerate your thumbnails", with the link to the free plugin to regenerate thumbnails.
				'desc'    => sprintf( __( 'Set product image size (in px). After changing this option, you may need to %s.', 'yith-woocommerce-recently-viewed-products' ), '<a href="http://wordpress.org/extend/plugins/regenerate-thumbnails/">' . __( 'regenerate your thumbnails', 'yith-woocommerce-recently-viewed-products' ) . '</a>' ),
				'type'    => 'ywrvp_image_size',
				'default' => array(
					'width'  => '80',
					'height' => '80',
					'crop'   => 1,
				),
			),
			array(
				'id'        => 'yith-wrvp-use-mandrill',
				'title'     => __( 'Enable Mandrill', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'default'   => 'no',
			),
			array(
				'id'        => 'yith-wrvp-mandrill-api-key',
				'title'     => __( 'Mandrill API KEY', 'yith-woocommerce-recently-viewed-products' ),
				'desc'      => __( 'Insert your Mandrill API KEY', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'default'   => '',
				'deps'      => array(
					'id'    => 'yith-wrvp-use-mandrill',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			array(
				'id'        => 'yith-wrvp-enable-analytics',
				'title'     => __( 'Add Google Analytics to email links', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'onoff',
				'desc'      => '',
				'default'   => 'no',
			),
			array(
				'id'        => 'yith-wrvp-campaign-source',
				'title'     => __( 'Campaign Source', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'desc'      => __( 'Referrer: google, citysearch, newsletter4', 'yith-woocommerce-recently-viewed-products' ),
				'css'       => 'width: 400px;',
				'deps'      => array(
					'id'    => 'yith-wrvp-enable-analytics',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			array(
				'id'        => 'yith-wrvp-campaign-medium',
				'title'     => __( 'Campaign Medium', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'desc'      => __( 'Marketing medium: cpc, banner, email', 'yith-woocommerce-recently-viewed-products' ),
				'css'       => 'width: 400px;',
				'deps'      => array(
					'id'    => 'yith-wrvp-enable-analytics',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			array(
				'id'          => 'yith-wrvp-campaign-term',
				'title'       => __( 'Campaign Term', 'yith-woocommerce-recently-viewed-products' ),
				'type'        => 'ywrvp_custom_checklist',
				'desc'        => __( 'Identify the paid keywords. Enter values separated by commas, for example: term1, term2', 'yith-woocommerce-recently-viewed-products' ),
				'css'         => 'width: 400px;',
				'placeholder' => __( 'Insert a term&hellip;', 'yith-woocommerce-recently-viewed-products' ),
				'deps'        => array(
					'id'    => 'yith-wrvp-enable-analytics',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			array(
				'id'        => 'yith-wrvp-campaign-content',
				'title'     => __( 'Campaign Content', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'desc'      => __( 'Use to differentiate ads', 'yith-woocommerce-recently-viewed-products' ),
				'css'       => 'width: 400px;',
				'deps'      => array(
					'id'    => 'yith-wrvp-enable-analytics',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			array(
				'id'        => 'yith-wrvp-campaign-name',
				'title'     => __( 'Campaign Name', 'yith-woocommerce-recently-viewed-products' ),
				'type'      => 'yith-field',
				'yith-type' => 'text',
				'desc'      => __( 'Product, promo code, or slogan', 'yith-woocommerce-recently-viewed-products' ),
				'css'       => 'width: 400px;',
				'deps'      => array(
					'id'    => 'yith-wrvp-enable-analytics',
					'value' => 'yes',
					'type'  => 'hide',
				),
			),
			array(
				'id'      => 'yith-wrvp-test-mail',
				'title'   => __( 'Send a test email to:', 'yith-woocommerce-recently-viewed-products' ),
				'type'    => 'ywrvp_test_email',
				'desc'    => __( 'Use this option to send a test of this email and check if everything is okay.', 'yith-woocommerce-recently-viewed-products' ),
				'default' => '',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'yith-wrvp-end-email-customization',
			),
		),
	)
);
