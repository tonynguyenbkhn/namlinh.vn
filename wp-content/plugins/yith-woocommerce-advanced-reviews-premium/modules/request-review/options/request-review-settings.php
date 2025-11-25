<?php
/**
 * Review request options tab array
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Options
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'request-review-settings' => array(
		array(
			'name' => esc_html_x( 'GDPR option', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Show checkbox consent in Checkout page', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'Enable to display a checkbox to ask users for permission to receive review reminders emails. Users who do not accept will be added to the Blocklist and will not receive review requests.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_refuse_requests',
			'default'   => yith_ywar_get_default( 'ywar_refuse_requests' ),
		),
		array(
			'name'              => esc_html_x( 'Checkbox text', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'desc'              => '',
			'default'           => yith_ywar_get_default( 'ywar_refuse_requests_label' ),
			'id'                => 'ywar_refuse_requests_label',
			'custom_attributes' => 'required',
			'deps'              => array(
				'id'    => 'ywar_refuse_requests',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Review reminder', '[Admin panel] Order/booking page column name and option name', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Send a review reminder for', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => '',
			'options'   => array(
				'all'       => esc_html_x( 'All products in order', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'selection' => esc_html_x( 'Specific products', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'default'   => yith_ywar_get_default( 'ywar_request_type' ),
			'id'        => 'ywar_request_type',
		),
		array(
			'name'      => '',
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'desc'      => '',
			'options'   => array(
				'first'               => esc_html_x( 'First product(s) bought', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'last'                => esc_html_x( 'Last product(s) bought', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'highest_quantity'    => esc_html_x( 'Products with highest number of items bought', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'lowest_quantity'     => esc_html_x( 'Products with lowest number of items bought', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'most_reviewed'       => esc_html_x( 'Products with the highest number of reviews', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'least_reviewed'      => esc_html_x( 'Products with the lowest number of reviews', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'highest_priced'      => esc_html_x( 'Products with the highest price', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'lowest_priced'       => esc_html_x( 'Products with the lowest price', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'highest_total_value' => esc_html_x( 'Products with the highest total value', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'lowest_total_value'  => esc_html_x( 'Products with the lowest total value', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'random'              => esc_html_x( 'Random', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'class'     => 'wc-enhanced-select',
			'default'   => yith_ywar_get_default( 'ywar_request_criteria' ),
			'id'        => 'ywar_request_criteria',
			'deps'      => array(
				'id'    => 'ywar_request_type',
				'value' => 'selection',
			),
		),
		array(
			'name'              => esc_html_x( 'Number of products to show for the request', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ) . ':',
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'desc'              => '',
			'default'           => yith_ywar_get_default( 'ywar_request_number' ),
			'id'                => 'ywar_request_number',
			'min'               => 1,
			'custom_attributes' => 'required',
			'deps'              => array(
				'id'    => 'ywar_request_type',
				'value' => 'selection',
			),
		),
		array(
			'name'              => esc_html_x( 'Send the email', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'desc'              => esc_html_x( 'day(s) after the order has been set as "Completed".', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'default'           => yith_ywar_get_default( 'ywar_mail_schedule_day' ),
			'id'                => 'ywar_mail_schedule_day',
			'min'               => 1,
			'custom_attributes' => 'required',
			'extra_row_class'   => 'yith-ywar-schedule-days',
		),
		array(
			'name'            => esc_html_x( 'Reschedule emails', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'            => 'yith-field',
			'yith-type'       => 'checkbox-array',
			'default'         => yith_ywar_get_default( 'ywar_mail_reschedule' ),
			'options'         => array(
				'reschedule'       => esc_html_x( 'Yes, reschedule emails', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'send-immediately' => esc_html_x( 'Send emails when the rescheduled date has passed', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'id'              => 'ywar_mail_reschedule',
			'desc'            => esc_html_x( "You're changing the time interval for email delivery. Do you want to reschedule the email on the new date?", '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'extra_row_class' => 'yith-ywar-reschedule-emails',
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Google Analytics integration', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Add Google Analytics to email links', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => yith_ywar_get_default( 'ywar_enable_analytics' ),
			'id'        => 'ywar_enable_analytics',
		),
		array(
			'name'              => esc_html_x( 'Campaign source', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'desc'              => esc_html_x( 'Referrer: google, citysearch, newsletter4.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'                => 'ywar_campaign_source',
			'default'           => yith_ywar_get_default( 'ywar_campaign_source' ),
			'custom_attributes' => 'required',
			'deps'              => array(
				'id'    => 'ywar_enable_analytics',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'name'              => esc_html_x( 'Campaign medium', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'desc'              => esc_html_x( 'Marketing medium: cpc, banner, email.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'                => 'ywar_campaign_medium',
			'default'           => yith_ywar_get_default( 'ywar_campaign_medium' ),
			'custom_attributes' => 'required',
			'deps'              => array(
				'id'    => 'ywar_enable_analytics',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'name'        => esc_html_x( 'Campaign term', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'        => 'yith-field',
			'yith-type'   => 'yith-ywar-analytics-terms',
			'desc'        => esc_html_x( 'Identify the paid keywords. Enter values separated by commas (for example, term1, term2).', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'          => 'ywar_campaign_term',
			'default'     => yith_ywar_get_default( 'ywar_campaign_term' ),
			'placeholder' => esc_html_x( 'Type a term and press Enter', '[Global] Ajax field placeholder', 'yith-woocommerce-advanced-reviews' ) . '&hellip;',
			'deps'        => array(
				'id'    => 'ywar_enable_analytics',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'name'      => esc_html_x( 'Campaign content', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'text',
			'desc'      => esc_html_x( 'Use to differentiate ads.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_campaign_content',
			'default'   => yith_ywar_get_default( 'ywar_campaign_content' ),
			'deps'      => array(
				'id'    => 'ywar_enable_analytics',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'name'              => esc_html_x( 'Campaign name', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'text',
			'desc'              => esc_html_x( 'Product, promo code, or slogan.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'                => 'ywar_campaign_name',
			'custom_attributes' => 'required',
			'default'           => yith_ywar_get_default( 'ywar_campaign_name' ),
			'deps'              => array(
				'id'    => 'ywar_enable_analytics',
				'value' => 'yes',
				'type'  => 'hide-disable',
			),
		),
		array(
			'type' => 'sectionend',
		),
	),
);
