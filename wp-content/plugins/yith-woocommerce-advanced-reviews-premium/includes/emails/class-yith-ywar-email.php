<?php
/**
 * Class YITH_YWAR_Email
 *
 * @package YITH\AdvancedReviews\Emails
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Email' ) ) {
	/**
	 * Class YITH_YWAR_Email
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Emails
	 */
	abstract class YITH_YWAR_Email extends WC_Email {

		/**
		 * Default custom message.
		 *
		 * @var string
		 */
		public $custom_message = '';

		/**
		 * Default email language
		 *
		 * @var string
		 */
		public $previous_language = '';

		/**
		 * YITH_YWAR_Email constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			$this->customer_email = true;

			$this->placeholders = array_merge(
				array(
					'{customer_name}'      => '',
					'{customer_last_name}' => '',
					'{customer_email}'     => '',
				),
				$this->placeholders
			);

			add_filter( 'woocommerce_mail_callback', array( $this, 'set_mail_callback' ), 10, 2 );

			$this->set_default_params();

			parent::__construct();
		}

		/**
		 * Chdck if Mandrill should be used
		 *
		 * @param mixed    $callback The mail callback.
		 * @param WC_Email $email    The current email.
		 *
		 * @return mixed
		 * @since  2.0.0
		 */
		public function set_mail_callback( $callback, WC_Email $email ) {

			if ( 'yes' === yith_ywar_get_option( 'ywar_mandrill_enable' ) && 0 === strpos( $email->id, 'yith-ywar' ) ) {
				$callback = 'yith_ywar_mandrill_send';
			}

			return $callback;
		}

		/**
		 * Override it to set default params.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_default_params() {
		}

		/**
		 * Trigger.
		 *
		 * @param array $args_obj The email arguments.
		 * @param bool  $output   Check if the function should return a value.
		 *
		 * @return void|bool
		 * @since  2.0.0
		 */
		public function trigger( array $args_obj, bool $output = false ) {

			$this->object = $args_obj;

			$this->maybe_set_recipient();

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return false;
			}

			/**
			 * DO_ACTION: yith_ywar_email_before_sending
			 *
			 * Adds an action before email is sent.
			 *
			 * @param WC_Email $this   The current email.
			 * @param string   $review The customer email.
			 */
			do_action( 'yith_ywar_email_before_sending', $this, $this->customer_email );

			$this->init_placeholders_before_sending();
			$status = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			/**
			 * DO_ACTION: yith_ywar_email_after_sending
			 *
			 * Adds an action after email is sent.
			 *
			 * @param WC_Email $this   The current email.
			 * @param string   $review The customer email.
			 */
			do_action( 'yith_ywar_email_after_sending', $this, $this->customer_email );

			if ( $output ) {
				return $status;
			}
			if ( ! $status ) {
				yith_ywar_debug_errors_trigger( 'error' );
			}
		}

		/**
		 * Initialize placeholders before sending.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_placeholders_before_sending() {
			if ( $this->object ) {
				$this->placeholders['{customer_name}']      = $this->object['user']['customer_name'];
				$this->placeholders['{customer_last_name}'] = $this->object['user']['customer_last_name'];
				$this->placeholders['{customer_email}']     = $this->object['user']['customer_email'];

				$this->placeholders = apply_filters( 'yith_ywar_email_placeholders', $this->placeholders, $this );
			}
		}

		/**
		 * Maybe set customer recipient email.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function maybe_set_recipient() {
			if ( $this->object ) {
				$this->recipient = $this->customer_email ? $this->object['user']['customer_email'] : get_option( 'admin_email' );
			}
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_content_plain(): string {
			$params = array_merge(
				array(
					'email_heading'  => $this->get_heading(),
					'sent_to_admin'  => ! $this->is_customer_email(),
					'plain_text'     => true,
					'email'          => $this,
					'custom_message' => $this->get_custom_message(),
				),
				$this->get_extra_content_params()
			);

			return wc_get_template_html( $this->template_plain, $params, '', $this->template_base );
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_content_html(): string {
			$params = array_merge(
				array(
					'email_heading'  => $this->get_heading(),
					'sent_to_admin'  => ! $this->is_customer_email(),
					'plain_text'     => false,
					'email'          => $this,
					'custom_message' => $this->get_custom_message(),
					'mail_style'     => yith_ywar_email_templates_enabled() ? array() : ( isset( $this->object['style'] ) ? $this->object['style'] : $this->get_custom_option( 'email_style' ) ),
				),
				$this->get_extra_content_params()
			);

			return wc_get_template_html( $this->template_html, $params, '', $this->template_base );
		}

		/**
		 * Do you need extra content params? If so, override me!
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_extra_content_params(): array {
			return array( 'footer_link' => false );
		}

		/**
		 * Get email subject.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_default_subject(): string {
			return $this->subject;
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_default_heading(): string {
			return $this->heading;
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_heading() {
			$heading = isset( $this->object['heading'] ) && ! empty( $this->object['heading'] ) ? $this->object['heading'] : $this->get_option( 'heading', $this->get_default_heading() );

			return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $heading ), $this->object, $this );
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_default_custom_message(): string {
			return $this->custom_message;
		}

		/**
		 * Return content from the custom_message field.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_custom_message(): string {

			$custom_message = isset( $this->object['body'] ) && ! empty( $this->object['body'] ) ? $this->object['body'] : $this->get_option( 'custom_message', $this->get_default_custom_message() );

			return $this->format_string( $custom_message );
		}

		/**
		 * Initialise settings form fields.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'        => array(
					'title'   => esc_html_x( 'Enable/Disable', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'    => 'checkbox',
					'label'   => esc_html_x( 'Enable this email notification', '[Admin panel] Setting description', 'yith-woocommerce-advanced-reviews' ),
					'default' => 'yes',
				),
				'subject'        => array(
					'title'       => esc_html_x( 'Subject', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'        => 'text',
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'        => array(
					'title'       => esc_html_x( 'Email heading', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'        => 'text',
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'custom_message' => array(
					'title'                   => esc_html_x( 'Message', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'                    => 'yith_ywar_field',
					'yith_ywar_field_type'    => 'textarea-editor',
					'default'                 => '',
					'teeny'                   => true,
					'media_buttons'           => false,
					'default_content_options' => array(
						'content'          => $this->get_default_custom_message(),
						'edit_text'        => esc_html_x( 'Edit message', '[Admin panel] Edit button text', 'yith-woocommerce-advanced-reviews' ),
						'use_default_text' => esc_html_x( 'Restore default message', '[Admin panel] Restore button text', 'yith-woocommerce-advanced-reviews' ),
					),
					'description'             =>
						'<span class="yith-ywar-emails__email__placeholders">' .
						'<span class="yith-ywar-emails__email__placeholders__label">' . esc_html_x( 'Available placeholders', '[Admin panel] Email placeholder text', 'yith-woocommerce-advanced-reviews' ) . ':</span>' .
						'<code>' . implode( '</code> <code>', array_keys( $this->placeholders ) ) . '</code>' .
						'</span>',
					'desc_tip'                => false,
				),
				'email_type'     => array(
					'title'   => esc_html_x( 'Email type', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'    => 'select',
					'default' => 'html',
					'class'   => 'email_type wc-enhanced-select',
					'options' => $this->get_email_type_options(),
				),
			);
		}

		/**
		 * Get email custom options.
		 *
		 * @param string     $key         The option key.
		 * @param mixed|null $empty_value The empty value (optional).
		 *
		 * @return mixed
		 */
		public function get_custom_option( string $key, $empty_value = null ) {

			$settings = get_option( $this->get_option_key(), null );

			// If there are no settings defined, use defaults.
			if ( ! is_array( $settings ) ) {
				$form_fields = $this->get_email_options( 'layout' ) + $this->get_email_options( 'content' ) + $this->get_email_options( 'configuration' );

				foreach ( $form_fields as $key_field => $field ) {
					if ( isset( $field['colorpickers'] ) ) {
						foreach ( $field['colorpickers'] as $colorpicker ) {
							$settings[ $key_field ][ $colorpicker['id'] ] = isset( $colorpicker['default'] ) ? $colorpicker['default'] : '';
						}
					} else {
						$settings[ $key_field ] = isset( $field['default'] ) ? $field['default'] : '';
					}
				}
			}

			// Get option default if unset.
			if ( ! isset( $settings[ $key ] ) ) {
				$form_fields      = $this->get_email_options( 'layout' ) + $this->get_email_options( 'content' ) + $this->get_email_options( 'configuration' );
				$settings[ $key ] = isset( $form_fields[ $key ] ) ? $this->get_field_default( $form_fields[ $key ] ) : '';
			}

			// Override email settings if YITH Emsail Templates is enabled.
			if ( yith_ywar_email_templates_enabled() && 'email_style' === $key ) {
				$settings[ $key ] = 'base';
			}

			if ( ! is_null( $empty_value ) && '' === $settings[ $key ] ) {
				$settings[ $key ] = $empty_value;
			}

			return maybe_unserialize( $settings[ $key ] );
		}

		/**
		 * Get the email options
		 *
		 * @param string $tab The tab to output.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_email_options( string $tab ) {

			$custom_atts = yith_ywar_email_templates_enabled() ? array( 'disabled' => true ) : array();
			if ( yith_ywar_email_templates_enabled() ) {
				/* translators: %1$s plugin name, %2$s BR tag, %3$s opening link, %4$s closing link */
				$desc = sprintf( esc_html_x( 'This email will use the style set in %1$s.%2$sYou can customize the style on %3$sthis page%4$s.', '[Admin panel] email style description', 'yith-woocommerce-advanced-reviews' ), 'YITH WooCommerce Email Templates', '<br />', '<a href="admin.php?page=yith_wcet_panel#yith-wcet-email-template-' . $this->id . '" target="_blank">', '</a>' );
			} else {
				/* translators: %1$s BR tag, %2$s opening link, %3$s closing link */
				$desc = sprintf( esc_html_x( 'This email will use the default style of WooCommerce emails.%1$sYou can customize the colors in %2$sWooCommerce > Settings > Emails%3$s.', '[Admin panel] email style description', 'yith-woocommerce-advanced-reviews' ), '<br />', '<a href="admin.php?page=wc-settings&tab=email" target="_blank">', '</a>' );
			}

			$layout = apply_filters(
				'yith_ywar_email_settings_layout_tab',
				array(
					'email_style'     => array(
						'title'             => esc_html_x( 'Email style', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'select',
						'default'           => 'base',
						'class'             => 'email_style_selector wc-enhanced-select',
						'options'           => array(
							'base'    => esc_html_x( 'WooCommerce template', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'style-1' => esc_html_x( 'Style #1', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'style-2' => esc_html_x( 'Style #2', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'style-3' => esc_html_x( 'Style #3', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
						),
						'custom_attributes' => $custom_atts,
						'desc'              => $desc,
					),
					'header_colors_1' => array(
						'title'             => esc_html_x( 'Header colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'multi-colorpicker',
						'colorpickers'      => array(
							array(
								'id'      => 'background',
								'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#759FD1',
								'data'    => array( 'prop' => 'background-color' ),
							),
							array(
								'id'      => 'text',
								'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#FFFFFF',
								'data'    => array( 'prop' => 'color' ),
							),
						),
						'class'             => 'header-colors',
						'deps'              => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-1',
						),
						'group'             => 'style-1',
						'sanitize_callback' => array( $this, 'validate_yith_field' ),
					),
					'body_colors_1'   => array(
						'title'             => esc_html_x( 'Body colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'multi-colorpicker',
						'colorpickers'      => array(
							array(
								'id'      => 'background',
								'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#D9E2ED',
								'data'    => array( 'prop' => 'background-color' ),
							),
							array(
								'id'      => 'text',
								'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#6C6C6C',
								'data'    => array( 'prop' => 'color' ),
							),
							array(
								'id'      => 'link',
								'name'    => esc_html_x( 'Link', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#759FD1',
								'data'    => array( 'prop' => 'link_color' ),
							),
							array(
								'id'      => 'link_hover',
								'name'    => esc_html_x( 'Link hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => wc_hex_darker( '#759FD1', 20 ),
								'data'    => array( 'prop' => 'link_color_hover' ),
							),
						),
						'deps'              => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-1',
						),
						'class'             => 'body-colors',
						'group'             => 'style-1',
						'sanitize_callback' => array( $this, 'validate_yith_field' ),
					),
					'upload_logo_1'   => array(
						'title'            => esc_html_x( 'Upload logo', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'             => 'media',
						'allow_custom_url' => false,
						'deps'             => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-1',
						),
						'class'            => 'upload-logo',
						'group'            => 'style-1',
					),
					'logo_position_1' => array(
						'title'   => esc_html_x( 'Logo position', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'    => 'select',
						'default' => 'left',
						'class'   => 'wc-enhanced-select logo-position',
						'options' => array(
							'left'   => esc_html_x( 'Left', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'center' => esc_html_x( 'Center', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'right'  => esc_html_x( 'Right', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
						),
						'deps'    => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-1',
						),
						'group'   => 'style-1',
					),
					'header_colors_2' => array(
						'title'             => esc_html_x( 'Header colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'multi-colorpicker',
						'colorpickers'      => array(
							array(
								'id'      => 'background',
								'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#6DCBBB',
								'data'    => array( 'prop' => 'background-color' ),
							),
							array(
								'id'      => 'text',
								'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#FFFFFF',
								'data'    => array( 'prop' => 'color' ),
							),
						),
						'class'             => 'header-colors',
						'deps'              => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-2',
						),
						'group'             => 'style-2',
						'sanitize_callback' => array( $this, 'validate_yith_field' ),
					),
					'body_colors_2'   => array(
						'title'             => esc_html_x( 'Body colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'multi-colorpicker',
						'colorpickers'      => array(
							array(
								'id'      => 'background',
								'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#65707A',
								'data'    => array( 'prop' => 'background-color' ),
							),
							array(
								'id'      => 'text',
								'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#6C6C6C',
								'data'    => array( 'prop' => 'color' ),
							),
							array(
								'id'      => 'link',
								'name'    => esc_html_x( 'Link', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#6DCBBB',
								'data'    => array( 'prop' => 'link_color' ),
							),
							array(
								'id'      => 'link_hover',
								'name'    => esc_html_x( 'Link hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => wc_hex_darker( '#6DCBBB', 20 ),
								'data'    => array( 'prop' => 'link_color_hover' ),
							),
						),
						'deps'              => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-2',
						),
						'class'             => 'body-colors',
						'group'             => 'style-2',
						'sanitize_callback' => array( $this, 'validate_yith_field' ),
					),
					'upload_logo_2'   => array(
						'title'            => esc_html_x( 'Upload logo', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'             => 'media',
						'allow_custom_url' => false,
						'deps'             => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-2',
						),
						'class'            => 'upload-logo',
						'group'            => 'style-2',
					),
					'logo_position_2' => array(
						'title'   => esc_html_x( 'Logo position', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'    => 'select',
						'default' => 'left',
						'class'   => 'wc-enhanced-select logo-position',
						'options' => array(
							'left'   => esc_html_x( 'Left', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'center' => esc_html_x( 'Center', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'right'  => esc_html_x( 'Right', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
						),
						'deps'    => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-2',
						),
						'group'   => 'style-2',
					),
					'header_colors_3' => array(
						'title'             => esc_html_x( 'Header colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'multi-colorpicker',
						'colorpickers'      => array(
							array(
								'id'      => 'background',
								'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#D75957',
								'data'    => array( 'prop' => 'background-color' ),
							),
							array(
								'id'      => 'text',
								'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#FFFFFF',
								'data'    => array( 'prop' => 'color' ),
							),
						),
						'class'             => 'header-colors',
						'deps'              => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-3',
						),
						'group'             => 'style-3',
						'sanitize_callback' => array( $this, 'validate_yith_field' ),
					),
					'body_colors_3'   => array(
						'title'             => esc_html_x( 'Body colors', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'multi-colorpicker',
						'colorpickers'      => array(
							array(
								'id'      => 'background',
								'name'    => esc_html_x( 'Background', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#f5f5f5',
								'data'    => array( 'prop' => 'background-color' ),
							),
							array(
								'id'      => 'text',
								'name'    => esc_html_x( 'Text', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#6C6C6C',
								'data'    => array( 'prop' => 'color' ),
							),
							array(
								'id'      => 'link',
								'name'    => esc_html_x( 'Link', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => '#D75957',
								'data'    => array( 'prop' => 'link_color' ),
							),
							array(
								'id'      => 'link_hover',
								'name'    => esc_html_x( 'Link hover', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
								'default' => wc_hex_darker( '#D75957', 20 ),
								'data'    => array( 'prop' => 'link_color_hover' ),
							),
						),
						'deps'              => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-3',
						),
						'class'             => 'body-colors',
						'group'             => 'style-3',
						'sanitize_callback' => array( $this, 'validate_yith_field' ),
					),
					'upload_logo_3'   => array(
						'title'            => esc_html_x( 'Upload logo', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'             => 'media',
						'allow_custom_url' => false,
						'deps'             => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-3',
						),
						'class'            => 'upload-logo',
						'group'            => 'style-3',
					),
					'logo_position_3' => array(
						'title'   => esc_html_x( 'Logo position', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'    => 'select',
						'default' => 'left',
						'class'   => 'wc-enhanced-select logo-position',
						'options' => array(
							'left'   => esc_html_x( 'Left', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'center' => esc_html_x( 'Center', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
							'right'  => esc_html_x( 'Right', '[Admin panel] Option name', 'yith-woocommerce-advanced-reviews' ),
						),
						'deps'    => array(
							'id'    => $this->get_field_key( 'email_style' ),
							'value' => 'style-3',
						),
						'group'   => 'style-3',
					),
				),
				$this
			);

			$content = apply_filters(
				'yith_ywar_email_settings_content_tab',
				array(
					'heading'        => array(
						'title'             => esc_html_x( 'Email heading', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'text',
						'custom_attributes' => array(
							'placeholder' => $this->get_default_heading(),
						),
						'default'           => '',
						'class'             => 'mail-heading',
					),
					'custom_message' => array(
						'title'                   => esc_html_x( 'Message', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'                    => 'textarea-editor',
						'default'                 => '',
						'teeny'                   => true,
						'media_buttons'           => false,
						'default_content_options' => array(
							'content'          => $this->get_default_custom_message(),
							'edit_text'        => esc_html_x( 'Edit message', '[Admin panel] Edit button text', 'yith-woocommerce-advanced-reviews' ),
							'use_default_text' => esc_html_x( 'Restore default message', '[Admin panel] Restore button text', 'yith-woocommerce-advanced-reviews' ),
						),
						'description'             =>
							'<span class="yith-ywar-emails__email__placeholders">' .
							'<span class="yith-ywar-emails__email__placeholders__label">' . esc_html_x( 'Available placeholders', '[Admin panel] Email placeholder text', 'yith-woocommerce-advanced-reviews' ) . ':</span>' .
							'<code>' . implode( '</code> <code>', array_keys( $this->placeholders ) ) . '</code>' .
							'</span>',
						'sanitize_callback'       => array( $this, 'validate_yith_field' ),
					),
				),
				$this
			);

			$configuration = apply_filters(
				'yith_ywar_email_settings_configuration_tab',
				array(
					'subject'    => array(
						'title'             => esc_html_x( 'Subject', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'              => 'text',
						'custom_attributes' => array(
							'placeholder' => $this->get_default_subject(),
						),
						'default'           => '',
					),
					'email_type' => array(
						'title'   => esc_html_x( 'Email type', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
						'type'    => 'select',
						'default' => 'html',
						'class'   => 'wc-enhanced-select',
						'options' => $this->get_email_type_options(),
					),
				),
				$this
			);

			$test_email = array(
				'test_email' => array(
					'title'   => esc_html_x( 'Send test email', '[Admin panel] Setting name', 'yith-woocommerce-advanced-reviews' ),
					'type'    => 'text-button',
					'buttons' => array(
						array(
							'name'  => esc_html_x( 'Send', '[Admin panel] Button label', 'yith-woocommerce-advanced-reviews' ),
							'class' => 'yith-plugin-fw__button--primary yith-plugin-fw__button--xl yith-ywar-test-email',
							'data'  => array(
								'type' => get_class( $this ),
							),
						),
					),
					'value'   => get_option( 'admin_email' ),
					'default' => '',
				),
			);

			$tabs = array(
				'layout'        => $layout,
				'content'       => $content,
				'configuration' => $configuration + $test_email,
			);

			return $tabs[ $tab ];
		}

		/**
		 * Processes and saves options.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function process_admin_options() {

			$post_data = $this->get_post_data();
			$settings  = array();

			if ( isset( $post_data['ywar_panel'] ) && 'yes' === $post_data['ywar_panel'] ) {
				$fields = $this->get_email_options( 'layout' ) + $this->get_email_options( 'content' ) + $this->get_email_options( 'configuration' );

				foreach ( $fields as $key => $field ) {
					if ( 'title' !== $this->get_field_type( $field ) ) {
						try {
							$settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
						} catch ( Exception $e ) {
							yith_ywar_error( $e->getMessage() );
							$this->add_error( $e->getMessage() );
						}
					}
				}

				$option_key = $this->get_option_key();
				do_action( 'woocommerce_update_option', array( 'id' => $option_key ) );
				update_option( $option_key, apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $settings ), 'yes' );
			} else {
				parent::process_admin_options();
			}
			// Save templates.
			if ( isset( $post_data['template_html_code'] ) ) {
				$this->save_template( $post_data['template_html_code'], $this->template_html );
			}
			if ( isset( $post_data['template_plain_code'] ) ) {
				$this->save_template( $post_data['template_plain_code'], $this->template_plain );
			}
		}

		/**
		 * Serialize array if needed
		 *
		 * @param string $key   The option key.
		 * @param mixed  $value The option value.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function validate_yith_ywar_field_field( $key, $value ): string {
			return maybe_serialize( $value );
		}

		/**
		 * Serialize value if needed
		 *
		 * @param mixed $value The option value.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function validate_yith_field( $value ): string {
			return maybe_serialize( $value );
		}

		/**
		 * Email type options.
		 * Allow only HTML and Multipart
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_email_type_options(): array {
			$types = parent::get_email_type_options();

			if ( isset( $types['plain'] ) ) {
				unset( $types['plain'] );
			}

			return $types;
		}

		/**
		 * Return email type.
		 * Allow only HTML and Multipart
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_email_type(): string {
			$type = parent::get_email_type();

			return 'plain' !== $type ? $type : 'html';
		}

		/**
		 * Return the description to be shown in settings list.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_description_to_show_in_settings_list(): string {
			return $this->description;
		}

		/**
		 * Generate custom fields by using YITH framework fields.
		 *
		 * @param string $key  The key of the field.
		 * @param array  $data The attributes of the field as an associative array.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function generate_yith_ywar_field_html( string $key, array $data ): string {
			$field_key = $this->get_field_key( $key );
			$value     = isset( $data['value'] ) ? $data['value'] : $this->get_custom_option( $key );
			$defaults  = array(
				'title'                   => '',
				'label'                   => '',
				'yith_ywar_field_type'    => 'text',
				'description'             => '',
				'desc_tip'                => false,
				'default_content_options' => array(),
				'deps'                    => '',
			);

			wp_enqueue_script( 'yith-plugin-fw-fields' );
			wp_enqueue_style( 'yith-plugin-fw-fields' );

			$data = wp_parse_args( $data, $defaults );

			$default_content_options                     = $data['default_content_options'];
			$default_content_options['content']          = $default_content_options['content'] ?? '';
			$default_content_options['edit_text']        = $default_content_options['edit_text'] ?? esc_html_x( 'Edit', '[Global] Generic edit button text', 'yith-woocommerce-advanced-reviews' );
			$default_content_options['use_default_text'] = $default_content_options['use_default_text'] ?? esc_html_x( 'Restore default message', '[Admin panel] Restore button text', 'yith-woocommerce-advanced-reviews' );
			$default_content                             = $default_content_options['content'];

			$field          = $data;
			$field['type']  = $data['yith_ywar_field_type'];
			$field['name']  = $field_key;
			$field['value'] = $value;
			$private_keys   = array( 'label', 'title', 'description', 'yith_ywar_field_type', 'default_content_options' );

			foreach ( $private_keys as $private_key ) {
				unset( $field[ $private_key ] );
			}

			$row_classes = implode(
				' ',
				array_filter(
					array(
						'yith-ywar-email-field__row',
						! ! $value ? '' : 'yith-ywar-email-field__row--empty-value',
					)
				)
			);

			$row_classes .= ! empty( $data['deps'] ) ? ' ' . $data['deps'] : '';

			ob_start();
			?>
			<tr valign="top" class="<?php echo esc_attr( $row_classes ); ?>" data-default-content="<?php echo esc_attr( $default_content ); ?>">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				</th>
				<td class="forminp yith-plugin-ui">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
						<?php if ( $default_content && 'textarea-editor' === $field['type'] ) : ?>
							<div class="yith-ywar-email-field__default-content">
								<?php
								echo wp_kses_post( nl2br( $default_content ) );
								yith_plugin_fw_get_component(
									array(
										'class'  => 'yith-ywar-email-field__edit',
										'type'   => 'action-button',
										'action' => 'edit',
										'icon'   => 'edit',
										'title'  => $default_content_options['edit_text'],
										'url'    => '#',
									),
									true
								);
								?>
							</div>
							<div class="yith-ywar-email-field__field">
								<?php yith_plugin_fw_get_field( $field, true, true ); ?>
							</div>
						<?php else : ?>
							<?php yith_plugin_fw_get_field( $field, true, true ); ?>
						<?php endif; ?>
						<?php echo wp_kses_post( $this->get_description_html( $data ) ); ?>
						<?php if ( $default_content && 'textarea-editor' === $field['type'] ) : ?>
							<span class="yith-ywar-email-field__use-default">
								<?php echo esc_html( $default_content_options['use_default_text'] ); ?>
							</span>
						<?php endif; ?>
					</fieldset>
				</td>
			</tr>
			<?php

			return ob_get_clean();
		}

		/**
		 * Generate custom fields in WooCommerce email settings.
		 *
		 * @param string $key  The key of the field.
		 * @param array  $data The attributes of the field as an associative array.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function generate_fields_html( string $key, array $data ): string {
			$field_key = $this->get_field_key( $key );
			$value     = isset( $data['value'] ) ? $data['value'] : $this->get_custom_option( $key );
			$defaults  = array(
				'description'             => '',
				'default_content_options' => array(),
			);

			wp_enqueue_script( 'yith-plugin-fw-fields' );
			wp_enqueue_style( 'yith-plugin-fw-fields' );

			$data = wp_parse_args( $data, $defaults );

			$default_content_options                     = $data['default_content_options'];
			$default_content_options['content']          = $default_content_options['content'] ?? '';
			$default_content_options['edit_text']        = $default_content_options['edit_text'] ?? esc_html_x( 'Edit', '[Global] Generic edit button text', 'yith-woocommerce-advanced-reviews' );
			$default_content_options['use_default_text'] = $default_content_options['use_default_text'] ?? esc_html_x( 'Restore default message', '[Admin panel] Restore button text', 'yith-woocommerce-advanced-reviews' );
			$default_content                             = $default_content_options['content'];

			$field          = $data;
			$field['id']    = $field_key;
			$field['name']  = $field_key;
			$field['value'] = $value;
			$private_keys   = array( 'description', 'default_content_options' );

			foreach ( $private_keys as $private_key ) {
				unset( $field[ $private_key ] );
			}

			ob_start();

			$row_classes = array(
				'yith-plugin-fw__panel__option',
				'yith-plugin-fw__panel__option--' . $field['type'],
				'yith-ywar-email-field__row',
				! ! $value ? '' : 'yith-ywar-email-field__row--empty-value',
				isset( $field['group'] ) ? $field['group'] : '',
			);
			$row_classes = implode( ' ', array_filter( $row_classes ) );

			?>
			<div class="<?php echo esc_attr( $row_classes ); ?>" <?php echo wp_kses_post( yith_field_deps_data( $field ) ); ?> data-default-content="<?php echo esc_attr( $default_content ); ?>">
				<?php if ( isset( $field['title'] ) && '' !== $field['title'] ) : ?>
					<div class="yith-plugin-fw__panel__option__label">
						<label for="<?php echo esc_attr( ( $field_key ) ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
					</div>
				<?php endif; ?>
				<div class="yith-plugin-fw__panel__option__content">
					<?php if ( 'textarea-editor' === $field['type'] ) : ?>
						<fieldset>
							<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
							<?php if ( $default_content ) : ?>
								<div class="yith-ywar-email-field__default-content">
									<?php
									echo wp_kses_post( nl2br( $default_content ) );
									yith_plugin_fw_get_component(
										array(
											'class'  => 'yith-ywar-email-field__edit',
											'type'   => 'action-button',
											'action' => 'edit',
											'icon'   => 'edit',
											'title'  => $default_content_options['edit_text'],
											'url'    => '#',
										),
										true
									);
									?>
								</div>
								<div class="yith-ywar-email-field__field">
									<?php yith_plugin_fw_get_field( $field, true, true ); ?>
								</div>
							<?php else : ?>
								<?php yith_plugin_fw_get_field( $field, true, true ); ?>
							<?php endif; ?>
							<?php echo wp_kses_post( $data['description'] ); ?>
							<?php if ( $default_content ) : ?>
								<span class="yith-ywar-email-field__use-default">
								<?php echo esc_html( $default_content_options['use_default_text'] ); ?>
							</span>
							<?php endif; ?>
						</fieldset>
					<?php else : ?>
						<?php yith_plugin_fw_get_field( $field, true, true ); ?>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $field['desc'] ) ) : ?>
					<div class="yith-plugin-fw__panel__option__description">
						<?php echo wp_kses_post( $field['desc'] ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php

			return ob_get_clean();
		}
	}
}
