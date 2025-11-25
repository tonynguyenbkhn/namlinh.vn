<?php
/**
 * Settings class file.
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin settings class.
 */
class Warranty_Settings {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// Settings.
		add_action( 'woocommerce_update_option', array( $this, 'update_warranty_emails' ) );
		add_action( 'woocommerce_update_option', array( $this, 'update_permissions' ) );
		add_action( 'woocommerce_update_option', array( $this, 'update_multi_status' ) );
		add_action( 'woocommerce_update_option', array( $this, 'update_form_builder' ) );
		add_action( 'woocommerce_update_option', array( $this, 'update_category_warranties' ) );
		add_action( 'woocommerce_update_option', array( $this, 'update_default_addons' ) );

		// WC 2.4 support for storing admin settings.
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'warranty_emails_posted_value' ), 10, 2 );
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'permissions_posted_value' ), 10, 2 );
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'multi_status_posted_value' ), 10, 2 );
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'form_builder_posted_value' ), 10, 2 );
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'default_addons_posted_value' ), 10, 2 );

		add_action( 'woocommerce_admin_field_warranty_emails', array( $this, 'warranty_emails_table' ) );
		add_action( 'woocommerce_admin_field_multi_status', array( $this, 'warranty_multi_status_field' ) );
		add_action( 'woocommerce_admin_field_warranty_form_builder', array( $this, 'form_builder' ) );
		add_action( 'woocommerce_admin_field_warranty_permissions', array( $this, 'permissions_table' ) );
		add_action( 'woocommerce_admin_field_warranty_addons_table', array( $this, 'warranty_addons_table' ) );
		add_action( 'woocommerce_admin_field_warranty_categories_table', array( $this, 'warranty_categories_table' ) );
		add_action( 'woocommerce_admin_field_warranty_logo', array( $this, 'warranty_logo_field' ) );
	}

	/**
	 * Get list of settings fields
	 *
	 * @return array
	 */
	public static function get_settings_fields() {
		$warranty_page_id        = wc_get_page_id( 'warranty' );
		$order_status_options    = array();
		$warranty_statuses       = warranty_get_statuses();
		$warranty_status_options = array();

		$saved_rma = get_option( 'warranty_saved_rma', 0 );
		$last_rma  = get_option( 'warranty_last_rma', 0 );

		$statuses = wc_get_order_statuses();

		foreach ( $statuses as $key => $status ) {
			$key                          = str_replace( 'wc-', '', $key );
			$order_status_options[ $key ] = $key;
		}

		foreach ( $warranty_statuses as $warranty_status ) {
			$warranty_status_options[ $warranty_status->slug ] = $warranty_status->name;
		}

		/**
		 * Filter to let 3rd party adding more settings.
		 *
		 * @since 1.7
		 */
		return apply_filters(
			'woocommerce_warranty_settings',
			array(
				'general'     => array(
					array(
						'name' => 'Setup and Settings',
						'type' => 'title',
						'desc' => 'Below you will set your warranty page, enable features and text, and define the statuses to which to allow warranty requests.',
						'id'   => 'warranty_settings_title',
					),

					array(
						'name'     => __( 'Warranty Request Page', 'woocommerce-warranty' ),
						'type'     => 'single_select_page',
						'id'       => 'woocommerce_warranty_page_id',
						'desc'     => __( 'Must contain the <code>[warranty_request]</code> shortcode', 'woocommerce-warranty' ),
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Initiate RMA from My Account Page', 'woocommerce-warranty' ),
						'desc'     => __( 'Selecting NO will hide the <em>Request Warranty</em> button from the customer\'s My Account page', 'woocommerce-warranty' ),
						'desc_tip' => true,
						'type'     => 'select',
						'id'       => 'warranty_show_rma_button',
						'options'  => array(
							'yes' => 'Yes',
							'no'  => 'No',
						),
						'default'  => 'yes',
						'css'      => 'min-width:150px;',
					),

					array(
						'name'     => __( 'Request Tracking Code', 'woocommerce-warranty' ),
						'desc'     => __( 'Display a text field on the Request page where a customer can provide the shipping tracking code', 'woocommerce-warranty' ),
						'desc_tip' => true,
						'id'       => 'warranty_show_tracking_field',
						'type'     => 'select',
						'options'  => array(
							'yes' => 'Yes',
							'no'  => 'No',
						),
						'default'  => 'yes',
						'css'      => 'min-width:150px;',
					),

					array(
						'name'     => __( 'Add Order Notes on Warranty Updates', 'woocommerce-warranty' ),
						'desc'     => __( 'Create order notes when a warranty is set to the selected statuses', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_request_order_note_statuses',
						'css'      => 'width:400px;',
						'default'  => array_keys( $warranty_status_options ),
						'type'     => 'multiselect',
						'class'    => 'wc-enhanced-select',
						'options'  => $warranty_status_options,
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Returned Status', 'woocommerce-warranty' ),
						'desc'     => __( 'The warranty status that marks an item as "returned" and ends the warranty process', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_returned_status',
						'css'      => 'min-width: 150px;',
						'default'  => 'Processing',
						'type'     => 'select',
						'options'  => $warranty_status_options,
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Warranty Button Text', 'woocommerce-warranty' ),
						'desc'     => __( 'Default: Request Warranty', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_button_text',
						'css'      => 'min-width:150px;',
						'default'  => __( 'Request Warranty', 'woocommerce-warranty' ),
						'type'     => 'text',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'View Warranty Button Text', 'woocommerce-warranty' ),
						'desc'     => __( 'Default: View Warranty Status', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'view_warranty_button_text',
						'css'      => 'min-width:150px;',
						'default'  => __( 'View Warranty Status', 'woocommerce-warranty' ),
						'type'     => 'text',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Reset Warranty Statuses', 'woocommerce-warranty' ),
						'desc'     => __( 'Checking this box will rescan order statuses and returned statuses to update them on save', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_reset_statuses',
						'css'      => 'min-width:150px;',
						'default'  => 'no',
						'value'    => '0',
						'type'     => 'checkbox',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Warranty Return Form URL', 'woocommerce-warranty' ),
						'desc'     => esc_html__( 'URL for a page that has [warranty_return_form] shortcode', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_return_form_url',
						'css'      => 'min-width:150px;',
						'default'  => '',
						'type'     => 'text',
						'desc_tip' => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_settings_title',
					),

					array(
						'name' => __( 'Refunds and Credits', 'woocommerce-warranty' ),
						'type' => 'title',
						'desc' => 'The following settings allow you to define if you wish to allow your customers to request a specific refund type (refund or coupon credit).',
						'id'   => 'warranty_refunds_title',
					),

					array(
						'name'     => __( 'Enable Refund Requests', 'woocommerce-warranty' ),
						'desc'     => __( 'Allow customers to request for refunds', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_enable_refund_requests',
						'css'      => 'min-width: 150px;',
						'default'  => 'no',
						'type'     => 'select',
						'options'  => array(
							'no'  => __( 'No', 'woocommerce-warranty' ),
							'yes' => __( 'Yes', 'woocommerce-warranty' ),
						),
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Enable Coupon Requests', 'woocommerce-warranty' ),
						'desc'     => __( 'Allow customers to request for coupons as store credit', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_enable_coupon_requests',
						'css'      => 'min-width: 150px;',
						'default'  => 'no',
						'type'     => 'select',
						'options'  => array(
							'no'  => __( 'No', 'woocommerce-warranty' ),
							'yes' => __( 'Yes', 'woocommerce-warranty' ),
						),
						'desc_tip' => true,
					),
					array(
						'name'     => __( 'Coupon Prefix', 'woocommerce-warranty' ),
						'desc'     => __( 'Add prefix in generated coupon', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_coupon_prefix',
						'css'      => 'min-width: 150px;',
						'default'  => 'wcwarranty-',
						'type'     => 'text',
						'desc_tip' => true,
						'class'    => 'show-if-coupon-requests-enabled',
					),
					array(
						'name'     => __( 'Coupon Description', 'woocommerce-warranty' ),
						'desc'     => __( 'Add description in generated coupon', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_coupon_desc',
						'css'      => 'min-width: 150px;',
						'default'  => __( 'Generated coupon from WC Warranty', 'woocommerce-warranty' ),
						'type'     => 'textarea',
						'desc_tip' => true,
						'class'    => 'show-if-coupon-requests-enabled',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_refunds_title',
					),

					array(
						'name' => __( 'Setup Print Layout', 'woocommerce-warranty' ),
						'type' => 'title',
						'desc' => 'The following settings will allow you to customize the print views',
						'id'   => 'warranty_print_title',
					),

					array(
						'name'     => __( 'Display Logo', 'woocommerce-warranty' ),
						'desc'     => __( 'The following settings will allow you to customize the print logo', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_print_logo',
						'css'      => 'min-width:150px;',
						'default'  => '',
						'type'     => 'warranty_logo',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Display URL', 'woocommerce-warranty' ),
						'desc'     => __( 'The following settings will allow you to customize the print URL', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_print_url',
						'css'      => 'min-width:150px;',
						'default'  => 'no',
						'value'    => 'yes',
						'type'     => 'checkbox',
						'desc_tip' => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_settings_title',
					),

					array(
						'name' => __( 'Return Code Format', 'woocommerce-warranty' ),
						'type' => 'title',
						'desc' => 'Below defines your custom warranty ID settings for your store and customers',
						'id'   => 'warranty_rma_title',
					),

					array(
						'name'     => __( 'RMA Code Start', 'woocommerce-warranty' ),
						'desc'     => __( 'The starting number for the incrementing portion of the code', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_rma_start',
						'css'      => 'min-width:150px;',
						'default'  => $last_rma,
						'type'     => 'text',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'RMA Code Length', 'woocommerce-warranty' ),
						'desc'     => __( 'The desired minimum length of the incrementing portion of the code', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_rma_length',
						'css'      => 'min-width:150px;',
						'default'  => 3,
						'type'     => 'text',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'RMA Code Prefix', 'woocommerce-warranty' ),
						'desc'     => __( 'You may use {DD}, {MM} and {YYYY} for the current day, month and year respectively', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_rma_prefix',
						'css'      => 'min-width:150px;',
						'default'  => '',
						'type'     => 'text',
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'RMA Code Suffix', 'woocommerce-warranty' ),
						'desc'     => __( 'You may use {DD}, {MM} and {YYYY} for the current day, month and year respectively', 'woocommerce-warranty' ),
						'tip'      => '',
						'id'       => 'warranty_rma_suffix',
						'css'      => 'min-width:150px;',
						'default'  => '',
						'type'     => 'text',
						'desc_tip' => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_rma_title',
					),
				),

				'default'     => array(
					array(
						'name' => 'Default Warranty',
						'type' => 'title',
						'desc' => __( 'The default warranty settings below will to apply to all products in your store', 'woocommerce-warranty' ),
						'id'   => 'warranty_default_title',
					),

					array(
						'name'     => __( 'Override Existing Warranties', 'woocommerce-warranty' ),
						'id'       => 'warranty_override_all',
						'desc'     => __( 'Removes existing warranty settings on all products, and applies the below default warranty', 'woocommerce-warranty' ),
						'tip'      => '',
						'default'  => 'no',
						'type'     => 'select',
						'options'  => array(
							'yes' => 'Yes',
							'no'  => 'No',
						),
						'desc_tip' => true,
					),

					array(
						'name'     => __( 'Label', 'woocommerce-warranty' ),
						'id'       => 'warranty_default_label',
						'css'      => 'min-width:150px;',
						'default'  => __( 'Warranty', 'woocommerce-warranty' ),
						'type'     => 'text',
						'desc_tip' => false,
					),

					array(
						'name'    => __( 'Type', 'woocommerce-warranty' ),
						'id'      => 'warranty_default_type',
						'type'    => 'select',
						'options' => array(
							'no_warranty'       => __( 'No Warranty', 'woocommerce-warranty' ),
							'included_warranty' => __( 'Warranty Included', 'woocommerce-warranty' ),
							'addon_warranty'    => __( 'Warranty as Add-On', 'woocommerce-warranty' ),
						),
						'default' => 'no_warranty',
						'css'     => 'min-width:150px;',
					),

					array(
						'name'    => __( 'Length', 'woocommerce-warranty' ),
						'id'      => 'warranty_default_length',
						'type'    => 'select',
						'options' => array(
							'limited'  => __( 'Limited', 'woocommerce-warranty' ),
							'lifetime' => __( 'Lifetime', 'woocommerce-warranty' ),
						),
						'default' => 'limited',
						'css'     => 'min-width:150px;',
						'class'   => 'show-if-included_warranty',
					),

					array(
						'name'    => __( 'Length Value', 'woocommerce-warranty' ),
						'id'      => 'warranty_default_length_value',
						'type'    => 'text',
						'default' => 0,
						'css'     => 'width: 50px',
						'class'   => 'show-if-included_warranty',
					),

					array(
						'name'    => __( 'Length Duration', 'woocommerce-warranty' ),
						'id'      => 'warranty_default_length_duration',
						'type'    => 'select',
						'options' => array(
							'days'   => __( 'Days', 'woocommerce-warranty' ),
							'weeks'  => __( 'Weeks', 'woocommerce-warranty' ),
							'months' => __( 'Months', 'woocommerce-warranty' ),
							'years'  => __( 'Years', 'woocommerce-warranty' ),
						),
						'default' => 'days',
						'css'     => 'min-width: 150px',
						'class'   => 'show-if-included_warranty',
					),

					array(
						'name'    => __( '&quot;No Warranty&quot; Option', 'woocommerce-warranty' ),
						'id'      => 'warranty_default_addon_no_warranty',
						'type'    => 'select',
						'options' => array(
							'yes' => __( 'Yes', 'woocommerce-warranty' ),
							'no'  => __( 'No', 'woocommerce-warranty' ),
						),
						'default' => 'yes',
						'css'     => 'min-width: 150px',
						'class'   => 'show-if-addon_warranty',
					),

					array(
						'name'  => __( 'Add-Ons', 'woocommerce-warranty' ),
						'id'    => 'warranty_default_addons',
						'type'  => 'warranty_addons_table',
						'class' => 'show-if-addon_warranty',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_default_title',
					),

					array(
						'name' => __( 'Category Warranties', 'woocommerce-warranty' ),
						'type' => 'title',
						'desc' => 'Define category specific default warranty settings. Product specific warranties will still override these if set.',
						'id'   => 'warranty_default_categories_title',
					),

					array(
						'name' => __( 'Categories', 'woocommerce-warranty' ),
						'id'   => 'warranty_categories',
						'type' => 'warranty_categories_table',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_default_categories_title',
					),
				),

				'form'        => array(
					array(
						'name' => 'Custom Form Builder',
						'type' => 'title',
						'desc' => 'Below will define the information you require from your customers on warranty request. Drag/drop the fields below to reorder, and click fields to the right to add to the form.',
						'id'   => 'warranty_form_title',
					),

					array(
						'name' => '',
						'id'   => 'warranty_form_builder',
						'type' => 'warranty_form_builder',
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_form_title',
					),
				),

				'emails'      => array(
					array(
						'name' => '&nbsp;',
						'type' => 'title',
						'desc' => '',
						'id'   => 'warranty_emails_title',
					),

					array(
						'name'     => __( 'Emails', 'woocommerce-warranty' ),
						'desc'     => '',
						'tip'      => '',
						'id'       => 'warranty_emails',
						'default'  => '',
						'type'     => 'warranty_emails',
						'desc_tip' => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_emails_title',
					),
				),

				'permissions' => array(
					array(
						'name' => '&nbsp;',
						'type' => 'title',
						'desc' => '',
						'id'   => 'warranty_permissions_title',
					),

					array(
						'name'     => __( 'Permissions', 'woocommerce-warranty' ),
						'desc'     => '',
						'tip'      => '',
						'id'       => 'warranty_permissions',
						'default'  => '',
						'type'     => 'warranty_permissions',
						'desc_tip' => true,
					),

					array(
						'type' => 'sectionend',
						'id'   => 'warranty_permissions_title',
					),
				),

			)
		);
	}

	/**
	 * Updating warranty emails type field.
	 *
	 * @param array $value Field value.
	 */
	public function update_warranty_emails( $value ) {
		if ( empty( $value['type'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_emails' === $value['type'] ) {
			$emails = self::get_warranty_emails_from_post();

			update_option( 'warranty_emails', $emails );
		}
	}

	/**
	 * Update warranty permission type field.
	 *
	 * @param array $value Field value.
	 */
	public function update_permissions( $value ) {
		if ( empty( $value['type'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_permissions' === $value['type'] ) {
			$permissions = self::get_warranty_permissions_from_post();

			update_option( 'warranty_permissions', $permissions );
		}
	}

	/**
	 * Update warranty multi status type field.
	 *
	 * @param array $value Field value.
	 */
	public function update_multi_status( $value ) {
		if ( empty( $value['type'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'multi_status' === $value['type'] ) {
			$statuses = self::get_multi_status_from_post( $value['id'] );

			update_option( $value['id'], $statuses );
		}
	}

	/**
	 * Update warranty form builder type field.
	 *
	 * @param array $value Field value.
	 */
	public function update_form_builder( $value ) {
		if ( empty( $value['type'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_form_builder' === $value['type'] ) {
			$form = self::get_form_builder_from_post();

			update_option( 'warranty_form', $form );
		}
	}

	/**
	 * Update warranty category type field.
	 *
	 * @param array $value Field value.
	 */
	public function update_category_warranties( $value ) {
		if ( empty( $value['type'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_categories_table' === $value['type'] ) {
			$warranties = self::get_category_warranties_from_post();

			update_option( 'wc_warranty_categories', $warranties );
		}
	}

	/**
	 * Update warranty default addons type field.
	 *
	 * @param array $value Field value.
	 */
	public function update_default_addons( $value ) {
		if ( empty( $value['type'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_addons_table' === $value['type'] ) {
			$addons = self::get_default_addons_from_post();

			update_option( 'warranty_default_addons', $addons );
		}
	}

	/**
	 * Sanitize the value of warranty emails type field.
	 *
	 * @param mixed $value Field value.
	 * @param array $option Field option.
	 *
	 * @return mixed
	 */
	public function warranty_emails_posted_value( $value, $option ) {
		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_emails' === $option['type'] ) {
			$value = self::get_warranty_emails_from_post();
		}

		return $value;
	}

	/**
	 * Sanitize the value of warranty permission type field.
	 *
	 * @param mixed $value Field value.
	 * @param array $option Field option.
	 *
	 * @return mixed
	 */
	public function permissions_posted_value( $value, $option ) {
		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_permissions' === $option['type'] ) {
			$value = self::get_warranty_permissions_from_post();
		}

		return $value;
	}

	/**
	 * Sanitize the value of warranty multi status type field.
	 *
	 * @param mixed $value Field value.
	 * @param array $option Field option.
	 *
	 * @return mixed
	 */
	public function multi_status_posted_value( $value, $option ) {
		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'multi_status' === $option['type'] ) {
			$value = self::get_multi_status_from_post( $option['id'] );
		}

		return $value;
	}

	/**
	 * Sanitize the value of warranty form builder type field.
	 *
	 * @param mixed $value Field value.
	 * @param array $option Field option.
	 *
	 * @return mixed
	 */
	public function form_builder_posted_value( $value, $option ) {
		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_form_builder' === $option['type'] ) {
			$value = self::get_form_builder_from_post();
		}

		return $value;
	}

	/**
	 * Sanitize the value of warranty default addons type field.
	 *
	 * @param mixed $value Field value.
	 * @param array $option Field option.
	 *
	 * @return mixed
	 */
	public function default_addons_posted_value( $value, $option ) {
		// phpcs:ignore WordPress.WP.Capabilities.Unknown --- `manage_woocommerce` is a native capability from WooCommerce
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die( esc_html__( 'Permission denied: Not enough capability', 'woocommerce-warranty' ) );
		}

		if ( 'warranty_addons_table' === $option['type'] ) {
			$value = self::get_default_addons_from_post();
		}

		return $value;
	}

	/**
	 * Create Warranty emails table.
	 *
	 * @param mixed $value Field value.
	 */
	public function warranty_emails_table( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found --- It was being used on the include file below
		$all_statuses  = warranty_get_statuses();
		$default_slugs = get_option( 'wc_warranty_default_slugs' );
		$emails        = get_option( 'warranty_emails', array() );

		$defaults = array(
			'fields' => array(),
			'inputs' => '',
		);
		$form     = get_option( 'warranty_form', $defaults );

		$inputs = array();
		if ( ! empty( $form['inputs'] ) ) {
			$inputs = json_decode( $form['inputs'] );
		}

		$custom_vars = array();
		foreach ( $inputs as $input ) {
			$key = $input->key;

			if ( ! empty( $form['fields'][ $key ]['name'] ) ) {
				$custom_vars[] = $form['fields'][ $key ]['name'];
			}
		}

		wp_enqueue_script( 'wc-warranty-admin-emails-table' );
		include WooCommerce_Warranty::$base_path . '/templates/settings/warranty-emails-table.php';
	}

	/**
	 * Display warranty multi status type field.
	 *
	 * @param array $value Field value.
	 */
	public function warranty_multi_status_field( $value ) {
		global $woocommerce;

		if ( ! isset( $value['id'] ) ) {
			$value['id'] = '';
		}
		if ( ! isset( $value['title'] ) ) {
			$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
		}
		if ( ! isset( $value['class'] ) ) {
			$value['class'] = '';
		}
		if ( ! isset( $value['css'] ) ) {
			$value['css'] = '';
		}
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = '';
		}
		if ( ! isset( $value['desc'] ) ) {
			$value['desc'] = '';
		}
		if ( ! isset( $value['desc_tip'] ) ) {
			$value['desc_tip'] = false;
		}

		// Custom attribute handling.
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		// Description handling.
		if ( true === $value['desc_tip'] ) {
			$description = '';
			$tip         = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description = $value['desc'];
			$tip         = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description = $value['desc'];
			$tip         = '';
		} else {
			$description = '';
			$tip         = '';
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description ) {
			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}

		if ( $tip && 'checkbox' === $value['type'] ) {

			$tip = '<p class="description">' . $tip . '</p>';

		} elseif ( $tip ) {

			$tip = '<span class="woocommerce-help-tip" data-tip="' . esc_attr( $tip ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16"/>';

		}
		$option_value = woocommerce_settings_get_option( $value['id'], $value['default'] );

		include WooCommerce_Warranty::$base_path . '/templates/settings/multi-status-field.php';
	}

	/**
	 * Display warranty form builder type field.
	 *
	 * @param array $value Field value.
	 */
	public function form_builder( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found --- It was being used on the include file below
		global $woocommerce;

		$types = array(
			'paragraph' => array(
				'label'   => __( 'Paragraph', 'woocommerce-warranty' ),
				'options' => 'text',
			),
			'text'      => array(
				'label'   => __( 'Text Field', 'woocommerce-warranty' ),
				'options' => 'name|label|style|default|required',
			),
			'textarea'  => array(
				'label'   => __( 'Multi-line Text Field', 'woocommerce-warranty' ),
				'options' => 'name|label|style|default|rowscols|required',
			),
			'select'    => array(
				'label'   => __( 'Drop Down', 'woocommerce-warranty' ),
				'options' => 'name|label|style|default|options|multiple|required',
			),
			'file'      => array(
				'label'   => __( 'File Upload Field', 'woocommerce-warranty' ),
				'options' => 'name|label|required',
			),
		);

		$defaults = array(
			'fields' => array(),
			'inputs' => '',
		);
		$form     = get_option( 'warranty_form', $defaults );

		$inputs = array();
		if ( ! empty( $form['inputs'] ) ) {
			$inputs = json_decode( $form['inputs'] );
		}

		include WooCommerce_Warranty::$base_path . '/templates/settings/form-builder.php';
	}

	/**
	 * Display warranty permission table field.
	 *
	 * @param array $value Field value.
	 */
	public function permissions_table( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found --- It was being used on the include file below
		$all_statuses        = warranty_get_statuses();
		$managers            = get_users( array( 'role' => 'shop_manager' ) );
		$admins              = get_users( array( 'role' => 'administrator' ) );
		$all_permitted_users = array_merge( $managers, $admins );
		$permissions         = get_option( 'warranty_permissions', array() );

		include WooCommerce_Warranty::$base_path . '/templates/settings/permissions-table.php';
	}

	/**
	 * Display warranty addons table.
	 *
	 * @param array $value Field value.
	 */
	public function warranty_addons_table( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found --- It was being used on the include file below
		$currency = get_woocommerce_currency_symbol();
		$addons   = get_option( 'warranty_default_addons', array() );

		include WooCommerce_Warranty::$base_path . '/templates/settings/addons-table.php';
	}

	/**
	 * Display warranty categories table.
	 *
	 * @param array $value Field value.
	 */
	public function warranty_categories_table( $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found --- It was being used on the include file below
		$currency   = get_woocommerce_currency_symbol();
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);

		$default_warranty = warranty_get_default_warranty();
		$warranties       = get_option( 'wc_warranty_categories', array() );

		include WooCommerce_Warranty::$base_path . '/templates/settings/categories-table.php';

		$js = '
            $(".inline-edit-row").hide();

            $("a.editinline").click(function(e) {
                e.preventDefault();
                var target = "#"+ $(this).data("target");

                if ( $(target).is(":visible") ) {
                    $(target).hide();
                } else {
                    $(target).css("display", "table-row");
                }
            });

            $("a.updateinline").click(function(e) {
                e.preventDefault();
                var target = "#"+ $(this).data("target");
				var nonce  = $( "#categories_list" ).data( "nonce" );
                var fields = $("#categories_list :input").serializeArray();

                $(".categories-warranty-container").block({
                    message: null,
                    overlayCSS: {
                        background: "#fff",
                        opacity: 0.6
                    }
                });

                fields.push({
                    name: "action",
                    value: "warranty_update_category_defaults"
                });

				fields.push({
					name: "security",
					value: nonce
				});

                $.post( ajaxurl, fields, function( resp ) {
					if ( resp.success === false ) {
						alert( resp.message );
					} else if ( resp.success === true && resp.data) {
						for ( id in resp.data ) {
							$("#row_"+ id +" .warranty-string").html( resp.data[id] );
						}
					}

                    $(target).hide();
                    $(".categories-warranty-container").unblock();
                });
            });

            $("table.warranty-category-table").on("change", ".default_toggle", function() {
                var id = $(this).data("id");

                if ( $(this).is(":checked") ) {
                    $(".warranty_"+ id).attr("disabled", true);
                } else {
                    $(".warranty_"+ id)
                        .attr("disabled", false)
                        .change();
                }
            });
            $(".default_toggle").change();

            $("table.warranty-category-table").on("change", ".warranty-type", function() {
                var parent  = $(this).parents("tr.inline-edit-row");
                var id      = $(parent).data("id");

                $(parent).find(".included-form").hide();
                $(parent).find(".addon-form").hide();

                switch ($(this).val()) {

                    case "included_warranty":
                        $(parent).find(".included-form").show();
                        $("#included_warranty_length_"+id).change();
                        break;

                    case "addon_warranty":
                        $(parent).find(".addon-form").show();
                        break;

                    default:
                        break;

                }
            });
            $(".warranty-type").change();

            $("table.warranty-category-table").on("change", ".included-warranty-length", function() {
                var parent  = $(this).parents("tr");
                var id      = $(parent).data("id");

                if ($(this).val() == "lifetime") {
                    $("#limited_warranty_row_"+id).hide();
                } else {
                    $("#limited_warranty_row_"+id).show();
                }
            });

            $(".btn-add-addon").on("click", function(e) {
                e.preventDefault();

                var id = $(this).parents("tr.inline-edit-row").eq(0).data("id");

                var t = $("#addon_tpl").html().replace(new RegExp("{id}", "g"), id);
                $(this).parents("tr.inline-edit-row").find(".addons-tbody").append(t);
            });
            ';

		if ( function_exists( 'wc_enqueue_js' ) ) {
			wc_enqueue_js( $js );
		} else {
			WC()->add_inline_js( $js );
		}
	}

	/**
	 * Display warranty logo field.
	 *
	 * @param array $value Field value.
	 */
	public function warranty_logo_field( $value ) {
		$type         = 'text';
		$option_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] );

		// Custom attribute handling.
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		// Description handling.
		$field_description = WC_Admin_Settings::get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];

		include WooCommerce_Warranty::$base_path . '/templates/settings/logo-field.php';

		$js = "
            var file_frame;

            jQuery('.warranty-logo-upload').on('click', function( event ){
                event.preventDefault();
                var btn = this;

                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: jQuery( this ).data( 'uploader_title' ),
                    button: {
                        text: jQuery( this ).data( 'uploader_button_text' ),
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                });

                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {
                    // We set multiple to false so only get one image from the uploader
                    attachment = file_frame.state().get('selection').first().toJSON();

                    $(btn).parents().find('.warranty-logo-field').val( attachment.url );
                });

                // Finally, open the modal
                file_frame.open();
              });";
		wc_enqueue_js( $js );
	}

	/**
	 * Get warranty emails from POST.
	 */
	public static function get_warranty_emails_from_post() {
		$post_data        = warranty_request_post_data();
		$triggers         = ( isset( $post_data['trigger'] ) ) ? $post_data['trigger'] : array();
		$statuses         = ( isset( $post_data['status'] ) ) ? $post_data['status'] : array();
		$from_statuses    = ( isset( $post_data['from_status'] ) ) ? $post_data['from_status'] : array();
		$recipients       = ( isset( $post_data['send_to'] ) ) ? $post_data['send_to'] : array();
		$admin_recipients = ( isset( $post_data['admin_recipients'] ) ) ? $post_data['admin_recipients'] : array();
		$subjects         = ( isset( $post_data['subject'] ) ) ? $post_data['subject'] : array();
		$messages         = ( isset( $post_data['message'] ) ) ? $post_data['message'] : array();
		$emails           = array();

		if ( ! empty( $statuses ) ) {
			foreach ( $statuses as $idx => $status ) {
				if ( '_id_' === $idx ) {
					continue;
				}

				if (
					! empty( $triggers[ $idx ] ) &&
					! empty( $from_statuses[ $idx ] ) &&
					! empty( $recipients[ $idx ] ) &&
					! empty( $subjects[ $idx ] ) &&
					! empty( $messages[ $idx ] )
				) {
					$key = $status;

					if ( 'status' !== $triggers[ $idx ] ) {
						$key = $triggers[ $idx ];
					}

					if ( isset( $admin_recipients[ $idx ] ) && is_array( $admin_recipients[ $idx ] ) ) {
						$admin_recipients[ $idx ] = implode( ',', $admin_recipients[ $idx ] );
					} else {
						$admin_recipients[ $idx ] = '';
					}

					$emails[ $key ][] = array(
						'trigger'          => $triggers[ $idx ],
						'from_status'      => $from_statuses[ $idx ],
						'recipient'        => $recipients[ $idx ],
						'admin_recipients' => $admin_recipients[ $idx ],
						'subject'          => $subjects[ $idx ],
						'message'          => $messages[ $idx ],
					);
				}
			}
		}

		return $emails;
	}

	/**
	 * Get warranty permission from POST.
	 */
	public static function get_warranty_permissions_from_post() {
		$statuses = warranty_get_statuses();

		// Skipping nonce verification because it's already done when WC calls do_action( 'woocommerce_update_option' ).
    	// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$users       = isset( $_POST['permission'] ) ? wc_clean( wp_unslash( $_POST['permission'] ) ) : array();
		$permissions = array();

		foreach ( $statuses as $status ) {
			$slug = $status->slug;
			if ( isset( $users[ $slug ] ) ) {
				foreach ( $users[ $slug ] as $user_id ) {
					$permissions[ $slug ][] = $user_id;
				}
			} else {
				$permissions[ $slug ] = array();
			}
		}

		return $permissions;
	}

	/**
	 * Get multi status from POST
	 *
	 * @param string $option_id Option ID.
	 */
	public static function get_multi_status_from_post( $option_id ) {
		$post_data = warranty_request_post_data();
		$statuses  = isset( $post_data[ $option_id ] ) ? $post_data[ $option_id ] : false;

		return $statuses;
	}

	/**
	 * Get form builder data from post
	 *
	 * @return array
	 */
	public static function get_form_builder_from_post() {
		$post_data = warranty_request_post_data();
		$fields    = $post_data['fb_field'];
		$inputs    = $post_data['form_fields'];

		return array(
			'fields' => $fields,
			'inputs' => $inputs,
		);
	}

	/**
	 * Get category warranties from post
	 *
	 * @return array
	 */
	public static function get_category_warranties_from_post() {
		$post_data  = warranty_request_post_data();
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
			)
		);
		$warranties = array();

		$default_warranty = isset( $post_data['category_warranty_default'] ) ? $post_data['category_warranty_default'] : array();
		$types            = isset( $post_data['category_warranty_type'] ) ? $post_data['category_warranty_type'] : array();
		$labels           = isset( $post_data['category_warranty_label'] ) ? $post_data['category_warranty_label'] : array();

		$included_length          = isset( $post_data['category_included_warranty_length'] ) ? $post_data['category_included_warranty_length'] : array();
		$included_length_value    = isset( $post_data['category_limited_warranty_length_value'] ) ? $post_data['category_limited_warranty_length_value'] : array();
		$included_length_duration = isset( $post_data['category_limited_warranty_length_duration'] ) ? $post_data['category_limited_warranty_length_duration'] : array();

		$addon_no_warranty              = isset( $post_data['category_addon_no_warranty'] ) ? $post_data['category_addon_no_warranty'] : array();
		$addon_warranty_amount          = isset( $post_data['category_addon_warranty_amount'] ) ? $post_data['category_addon_warranty_amount'] : array();
		$addon_warranty_length_value    = isset( $post_data['category_addon_warranty_length_value'] ) ? $post_data['category_addon_warranty_length_value'] : array();
		$addon_warranty_length_duration = isset( $post_data['category_addon_warranty_length_duration'] ) ? $post_data['category_addon_warranty_length_duration'] : array();

		foreach ( $categories as $category ) {
			$id       = $category->term_id;
			$warranty = array();

			if ( ! empty( $default_warranty[ $id ] ) ) {
				$warranties[ $id ] = $warranty;
				continue;
			}

			if ( 'included_warranty' === $types[ $id ] ) {
				$warranty = array(
					'type'     => 'included_warranty',
					'label'    => $labels[ $id ],
					'length'   => $included_length[ $id ],
					'value'    => $included_length_value[ $id ],
					'duration' => $included_length_duration[ $id ],
				);
			} elseif ( 'addon_warranty' === $types[ $id ] ) {
				$no_warranty = isset( $addon_no_warranty[ $id ] ) ? $addon_no_warranty[ $id ] : 'no';
				$amounts     = $addon_warranty_amount[ $id ];
				$values      = $addon_warranty_length_value[ $id ];
				$durations   = $addon_warranty_length_duration[ $id ];
				$addons      = array();
				$num_amounts = count( $amounts );

				for ( $x = 0; $x < $num_amounts; $x++ ) {
					if ( ! isset( $amounts[ $x ] ) || ! isset( $values[ $x ] ) || ! isset( $durations[ $x ] ) ) {
						continue;
					}

					$addons[] = array(
						'amount'   => $amounts[ $x ],
						'value'    => $values[ $x ],
						'duration' => $durations[ $x ],
					);
				}

				$warranty = array(
					'type'               => 'addon_warranty',
					'label'              => $labels[ $id ],
					'addons'             => $addons,
					'no_warranty_option' => $no_warranty,
				);
			} else {
				$warranty = array(
					'type' => 'no_warranty',
				);
			}

			$warranties[ $id ] = $warranty;
		}

		return $warranties;
	}

	/**
	 * Get Posted data for the default addons and format them into an array of addons
	 *
	 * @return array
	 */
	public static function get_default_addons_from_post() {
		$post_data = warranty_request_post_data();

		$amounts   = isset( $post_data['addon_warranty_amount'] ) ? $post_data['addon_warranty_amount'] : array();
		$lengths   = isset( $post_data['addon_warranty_length_value'] ) ? $post_data['addon_warranty_length_value'] : array();
		$durations = isset( $post_data['addon_warranty_length_duration'] ) ? $post_data['addon_warranty_length_duration'] : array();
		$addons    = array();

		foreach ( $amounts as $key => $amount ) {
			$addons[] = array(
				'amount'   => $amount,
				'value'    => $lengths[ $key ],
				'duration' => $durations[ $key ],
			);
		}

		return $addons;
	}
}

new Warranty_Settings();
