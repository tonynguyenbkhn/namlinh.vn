<?php
/**
 * General options sub tab array
 *
 * @package YITH\AdvancedReviews\PluginOptions\General
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

return array(
	'general-user' => array(
		array(
			'name' => esc_html_x( 'General permissions', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'id'        => 'ywar_user_permission',
			'type'      => 'yith-field',
			'name'      => esc_html_x( 'Users can', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ) . ':',
			'yith-type' => 'checkbox-array',
			'options'   => array(
				'multiple-reviews' => esc_html_x( 'Publish multiple reviews for the same product', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'title-reviews'    => esc_html_x( 'Write a title for their reviews', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'edit-reviews'     => esc_html_x( 'Edit their reviews', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'delete-reviews'   => esc_html_x( 'Delete their reviews', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'reply-reviews'    => esc_html_x( 'Reply to reviews', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'vote-helpful'     => esc_html_x( 'Vote reviews as helpful', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'report-reviews'   => esc_html_x( 'Report reviews as inappropriate', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'default'   => yith_ywar_get_default( 'ywar_user_permission' ),
		),
		array(
			'name'              => esc_html_x( 'Highlight a review as helpful after', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'desc'              => esc_html_x( 'vote(s)', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'                => 'ywar_highlight_helpful_review',
			'custom_attributes' => 'required',
			'min'               => 0,
			'default'           => yith_ywar_get_default( 'ywar_highlight_helpful_review' ),
			'extra_row_class'   => 'yith-ywar-reviews-threshold',
			'deps'              => array(
				'id'    => 'ywar_user_permission-vote-helpful',
				'value' => 'yes',
			),
		),
		array(
			'name'      => esc_html_x( 'Users that can report reviews', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'desc'      => esc_html_x( 'Choose who can flag a review as inappropriate.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_user_can_report_inappropriate',
			'options'   => array(
				'all'    => esc_html_x( 'All users', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
				'logged' => esc_html_x( 'Only logged users', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
			),
			'default'   => yith_ywar_get_default( 'ywar_user_can_report_inappropriate' ),
			'deps'      => array(
				'id'    => 'ywar_user_permission-report-reviews',
				'value' => 'yes',
			),
		),
		array(
			'name'              => esc_html_x( 'Hide a review automatically after', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'number',
			'desc'              => esc_html_x( 'report(s)', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'                => 'ywar_hide_inappropriate_review',
			'custom_attributes' => 'required',
			'min'               => 0,
			'default'           => yith_ywar_get_default( 'ywar_hide_inappropriate_review' ),
			'extra_row_class'   => 'yith-ywar-reviews-threshold',
			'deps'              => array(
				'id'    => 'ywar_user_permission-report-reviews',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Image attachments', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Allow image uploads', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'If enabled, users will be able to attach images to their reviews.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_enable_attachments',
			'default'   => yith_ywar_get_default( 'ywar_enable_attachments' ),
		),
		array(
			'name'      => esc_html_x( 'Max. number of images', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'id'        => 'ywar_max_attachments',
			'min'       => 1,
			'max'       => 20,
			'default'   => yith_ywar_get_default( 'ywar_max_attachments' ),
			'deps'      => array(
				'id'    => 'ywar_enable_attachments',
				'value' => 'yes',
			),
		),
		array(
			'name'              => esc_html_x( 'Supported file formats', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'desc'              => esc_html_x( 'Set the supported file formats.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'select',
			'class'             => 'wc-enhanced-select',
			'id'                => 'ywar_attachment_type',
			'custom_attributes' => 'required',
			'multiple'          => true,
			'options'           => array(
				'jpg'  => 'jpg',
				'jpeg' => 'jpeg',
				'gif'  => 'gif',
				'png'  => 'png',
				'webp' => 'webp',
			),
			'default'           => yith_ywar_get_default( 'ywar_attachment_type' ),
			'deps'              => array(
				'id'    => 'ywar_enable_attachments',
				'value' => 'yes',
			),
		),
		array(
			'name'      => esc_html_x( 'Upload size limit (MB)', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'id'        => 'ywar_attachment_max_size',
			'min'       => 0,
			'max'       => 10,
			'step'      => 1,
			'default'   => yith_ywar_get_default( 'ywar_attachment_max_size' ),
			'deps'      => array(
				'id'    => 'ywar_enable_attachments',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
		array(
			'name' => esc_html_x( 'Video attachments', '[Admin panel] Options section title', 'yith-woocommerce-advanced-reviews' ),
			'type' => 'title',
		),
		array(
			'name'      => esc_html_x( 'Allow video uploads', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'desc'      => esc_html_x( 'If enabled, users will be able to attach videos to their reviews.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'id'        => 'ywar_enable_attachments_video',
			'default'   => yith_ywar_get_default( 'ywar_enable_attachments_video' ),
		),
		array(
			'name'      => esc_html_x( 'Max. number of videos', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'id'        => 'ywar_max_attachments_video',
			'min'       => 1,
			'max'       => 5,
			'default'   => yith_ywar_get_default( 'ywar_max_attachments_video' ),
			'deps'      => array(
				'id'    => 'ywar_enable_attachments_video',
				'value' => 'yes',
			),
		),
		array(
			'name'              => esc_html_x( 'Supported file formats', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'desc'              => esc_html_x( 'Set the supported file formats.', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
			'type'              => 'yith-field',
			'yith-type'         => 'select',
			'class'             => 'wc-enhanced-select',
			'id'                => 'ywar_attachment_type_video',
			'custom_attributes' => 'required',
			'multiple'          => true,
			'options'           => array(
				'flv'  => 'flv',
				'm4v'  => 'm4v',
				'mp4'  => 'mp4',
				'ogv'  => 'ogv',
				'webm' => 'webm',
				'wmv'  => 'wmv',
			),
			'default'           => yith_ywar_get_default( 'ywar_attachment_type_video' ),
			'deps'              => array(
				'id'    => 'ywar_enable_attachments_video',
				'value' => 'yes',
			),
		),
		array(
			'name'      => esc_html_x( 'Upload size limit (MB)', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
			'type'      => 'yith-field',
			'yith-type' => 'number',
			'id'        => 'ywar_attachment_max_size_video',
			'min'       => 0,
			'max'       => 20,
			'step'      => 1,
			'default'   => yith_ywar_get_default( 'ywar_attachment_max_size_video' ),
			'deps'      => array(
				'id'    => 'ywar_enable_attachments_video',
				'value' => 'yes',
			),
		),
		array(
			'type' => 'sectionend',
		),
	),
);
