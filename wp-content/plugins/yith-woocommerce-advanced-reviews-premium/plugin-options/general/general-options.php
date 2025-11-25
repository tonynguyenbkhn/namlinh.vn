<?php
/**
 * General options sub tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions\General
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

$recaptcha_link = sprintf( ' <a target="_blank" href="https://developers.google.com/recaptcha/intro">%s</a>', esc_html_x( 'How to obtain this key.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ), '<a target="_blank" href="https://developers.google.com/recaptcha/intro">', '</a>' );

return array(
	'general-general' => array(
		array(
			'name' => esc_html_x( 'Review options', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Auto-approve reviews', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'If enabled, reviews are automatically approved with no moderation.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_review_autoapprove',
			'default'   => yith_ywar_get_default( 'ywar_review_autoapprove' ),
		),
		array(
			'name'      => esc_html_x( 'Allow anonymous reviews', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'If enabled, guest users can also leave reviews.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_enable_visitors_vote',
			'default'   => yith_ywar_get_default( 'ywar_enable_visitors_vote' ),
		),
		array(
			'name'      => esc_html_x( "Show user's country", '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( "If enabled, it will show the user's country", '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_show_user_country',
			'default'   => yith_ywar_get_default( 'ywar_show_user_country' ),
		),
		array(
			'name'      => esc_html_x( 'Show "Verified buyer" label on customer reviews', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'If enabled, it will show the "Verified buyer" label next to the user name', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'woocommerce_review_rating_verification_label',
		),
		array(
			'name'      => esc_html_x( 'Reviews can only be left by "Verified buyers"', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'If enabled, only logged in customers who have purchased the product can leave a review', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'woocommerce_review_rating_verification_required',
		),
		array(
			'name'      => esc_html_x( 'In review show', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ) . ':',
			'desc'      => esc_html_x( 'Choose whether to show or hide the full user name.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'full'        => esc_html_x( 'Full username', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ) . ' ( John Doe )',
				'masked'      => esc_html_x( 'Only first and last letter', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ) . ' ( J****e )',
				'masked-full' => esc_html_x( 'Initials of first and last name only', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ) . ' ( J*** D*** )',
				'name-only'   => esc_html_x( 'First name and the initial of the last name', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ) . ' ( John D. )',
				'nickname'    => esc_html_x( 'Username', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ) . ' ( John45 )',
			),
			'default'   => yith_ywar_get_default( 'ywar_username_format' ),
			'id'        => 'ywar_username_format',
		),
		array(
			'name'      => esc_html_x( 'Show tooltip with graph bars', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'Enable this option to display a tooltip with graph bars when hovering over the product name in reviews.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_enable_graph_tooltip',
			'default'   => yith_ywar_get_default( 'ywar_enable_graph_tooltip' ),
		),
		array(
			'name'      => esc_html_x( 'Notice to show in products without reviews', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'textarea',
			'desc'      => esc_html_x( 'Enter a custom message to be displayed when reviews are empty.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_no_reviews_text',
			'default'   => yith_ywar_get_default( 'ywar_no_reviews_text' ),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'reCAPTCHA options', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Enable Google reCAPTCHA', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'Enable Google reCAPTCHA to avoid spam reviews.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_enable_recaptcha',
			'default'   => yith_ywar_get_default( 'ywar_enable_recaptcha' ),
		),
		array(
			'id'        => 'ywar_recaptcha_version',
			'type'      => 'yith-field',
			'name'      => esc_html_x( 'reCAPTCHA version', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'desc'      => esc_html_x( 'Set the reCAPTCHA version you want to use and make sure your keys below match the version set.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'yith-type' => 'select-images',
			'options'   => array(
				'v2' => array(
					'label' => 'v2',
					'image' => YITH_YWAR_ASSETS_URL . '/images/bg.png',
				),
				'v3' => array(
					'label' => 'v3',
					'image' => YITH_YWAR_ASSETS_URL . '/images/bg.png',
				),
			),
			'default'   => yith_ywar_get_default( 'ywar_recaptcha_version' ),
			'deps'      => array(
				'id'    => 'ywar_enable_recaptcha',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'name'              => esc_html_x( 'reCAPTCHA site key', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'desc'              => esc_html_x( 'Enter your reCAPTCHA site key.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ) . $recaptcha_link,
			'id'                => 'ywar_recaptcha_site_key',
			'custom_attributes' => 'required',
			'default'           => yith_ywar_get_default( 'ywar_recaptcha_site_key' ),
			'deps'              => array(
				'id'    => 'ywar_enable_recaptcha',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'name'              => esc_html_x( 'reCAPTCHA secret key', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'desc'              => esc_html_x( 'Enter your reCAPTCHA secret key.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ) . $recaptcha_link,
			'id'                => 'ywar_recaptcha_secret_key',
			'custom_attributes' => 'required',
			'default'           => yith_ywar_get_default( 'ywar_recaptcha_secret_key' ),
			'deps'              => array(
				'id'    => 'ywar_enable_recaptcha',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Review attachments gallery', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Display a gallery to highlight all review attachments', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'Enable this option to display a gallery of all review attachments above the review list.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_show_attachments_gallery',
			'default'   => yith_ywar_get_default( 'ywar_show_attachments_gallery' ),
		),
		array(
			'name'      => esc_html_x( 'Hide attachments of review replies from the gallery', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'Enable to exclude images or videos uploaded in review replies from the attachment gallery.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_show_replies_attachments',
			'default'   => yith_ywar_get_default( 'ywar_show_replies_attachments' ),
			'deps'      => array(
				'id'    => 'ywar_show_attachments_gallery',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Reviews pagination', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Show pagination of reviews', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => sprintf( '%1$s<br ><b>%2$s:</b> %3$s', esc_html_x( 'Disable to show all the reviews without pagination.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ), esc_html_x( 'Note', '[Admin panel] additional note description field', 'yith-woocommerce-advanced-reviews' ), esc_html_x( 'a large number of reviews can affect the loading time of the product page.', '[Admin panel] additional note description field', 'yith-woocommerce-advanced-reviews' ) ),
			'id'        => 'ywar_show_load_more',
			'default'   => yith_ywar_get_default( 'ywar_show_load_more' ),
		),
		array(
			'name'              => esc_html_x( 'Reviews pagination', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'desc'              => sprintf( '%1$s</div><div class="yith-plugin-fw__panel__option__description">%2$s', esc_html_x( 'for page', '[Admin panel] additional descrption of the reviews pagination field', 'yith-woocommerce-advanced-reviews' ), esc_html_x( 'Set how many reviews to show.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ) ),
			'default'           => yith_ywar_get_default( 'ywar_review_per_page' ),
			'id'                => 'ywar_review_per_page',
			'custom_attributes' => 'required',
			'min'               => 5,
			'extra_row_class'   => 'yith-ywar-reviews-pagination',
			'deps'              => array(
				'id'    => 'ywar_show_load_more',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Mandrill integration', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Use Mandrill for email delivery', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywar_mandrill_enable',
			'default'   => yith_ywar_get_default( 'ywar_mandrill_enable' ),
		),
		array(
			'name'              => esc_html_x( 'Mandrill API Key', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'id'                => 'ywar_mandrill_apikey',
			'default'           => yith_ywar_get_default( 'ywar_mandrill_apikey' ),
			'custom_attributes' => 'required',
			'deps'              => array(
				'id'    => 'ywar_mandrill_enable',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'type' => 'sectionend',
		),
	),
);
