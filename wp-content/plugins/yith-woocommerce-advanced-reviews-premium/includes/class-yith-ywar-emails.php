<?php
/**
 * Class YITH_YWAR_Emails
 *
 * @package YITH\AdvancedReviews
 */

use Pelago\Emogrifier\CssInliner;
use Pelago\Emogrifier\HtmlProcessor\CssToAttributeConverter;
use Pelago\Emogrifier\HtmlProcessor\HtmlPruner;

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Emails' ) ) {
	/**
	 * Class YITH_YWAR_Emails
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Emails {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * The constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function __construct() {
			add_action( 'woocommerce_email', array( $this, 'woocommerce_email' ), 20 );
			add_filter( 'woocommerce_email_classes', array( $this, 'add_email_classes' ) );
			add_filter( 'woocommerce_email_styles', array( $this, 'email_style' ), 1000, 3 ); // use 1000 as priority to allow support for YITH  Email Templates.
			add_filter( 'woocommerce_email_actions', array( $this, 'add_email_actions' ) );
			add_action( 'yith_ywar_email_before_sending', array( $this, 'switch_language_to_translate_email' ), 10, 2 );
			add_action( 'yith_ywar_email_after_sending', array( $this, 'switch_language_after_translating_email' ), 10 );
			add_action( 'yith_ywar_print_emails_tab', array( $this, 'print_emails_tab' ) );
			add_action( 'yith_ywar_admin_ajax_switch_email_activation', array( $this, 'ajax_switch_email_activation' ) );
			add_action( 'yith_ywar_admin_ajax_update_email_options', array( $this, 'ajax_update_email_options' ) );
			add_action( 'yith_ywar_admin_ajax_send_test_mail', array( $this, 'ajax_send_test_mail' ) );
			add_action( 'yith_ywar_admin_ajax_reload_email_preview', array( $this, 'ajax_reload_email_preview' ) );
			add_action( 'woocommerce_email_get_option', array( $this, 'filter_emails_strings' ), 10, 4 );
		}

		/**
		 * Add email actions to WooCommerce email actions
		 *
		 * @param array $actions Actions.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_email_actions( array $actions ): array {

			$actions[] = 'yith_ywar_new_review_email';
			$actions[] = 'yith_ywar_new_reply_email';
			$actions[] = 'yith_ywar_reported_review_email';

			return $actions;
		}

		/**
		 * Replace WooCommerce email actions with YITH ones.
		 *
		 * @param WC_Emails $mailer Mailer instance.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function woocommerce_email( WC_Emails $mailer ) {
			if ( yith_ywar_email_templates_enabled() ) {
				return;
			}

			$priority = has_action( 'woocommerce_email_header', array( $mailer, 'email_header' ) );
			if ( $priority ) {
				remove_action( 'woocommerce_email_header', array( $mailer, 'email_header' ), $priority );
			}

			$priority = has_action( 'woocommerce_email_footer', array( $mailer, 'email_footer' ) );
			if ( $priority ) {
				remove_action( 'woocommerce_email_footer', array( $mailer, 'email_footer' ), $priority );
			}

			add_action( 'woocommerce_email_header', array( $this, 'email_header' ), 10, 3 );
			add_action( 'woocommerce_email_footer', array( $this, 'email_footer' ), 10, 2 );
		}


		/**
		 * Custom email styles.
		 *
		 * @param string        $style      WooCommerce style.
		 * @param WC_Email|null $email      The current email.
		 * @param string        $mail_style The style of the email.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function email_style( string $style, WC_Email $email = null, string $mail_style = 'base' ): string {
			ob_start();

			if ( 'base' !== $mail_style ) {

				$mail_style = str_replace( 'style-', '', $mail_style );

				$header_colors = maybe_unserialize( $email->get_custom_option( "header_colors_$mail_style" ) );
				$body_colors   = maybe_unserialize( $email->get_custom_option( "body_colors_$mail_style" ) );

				include YITH_YWAR_DIR . 'assets/css/emails.css';

				?>
				#outer_wrapper {
				background-color: <?php echo esc_attr( $body_colors['background'] ); ?>;
				}

				#template_header {
				background-color: <?php echo esc_attr( $header_colors['background'] ); ?>;
				color: <?php echo esc_attr( $header_colors['text'] ); ?>;
				}

				#body_content_inner {
				color: <?php echo esc_attr( $body_colors['text'] ); ?>;
				}

				a {
				color: <?php echo esc_attr( $body_colors['link'] ); ?>;
				}

				a:hover {
				color: <?php echo esc_attr( $body_colors['link_hover'] ); ?>!important;
				}
				<?php
			}

			include YITH_YWAR_DIR . 'assets/css/emails-global.css';

			$style .= ob_get_clean();

			return $style;
		}

		/**
		 * Get the email header.
		 *
		 * @param string        $email_heading Heading for the email.
		 * @param WC_Email|null $email         The current email.
		 * @param string        $mail_style    The style of the email.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function email_header( string $email_heading, WC_Email $email = null, string $mail_style = 'base' ) {

			if ( ( class_exists( 'Kadence_Woomail_Designer' ) && $email instanceof WC_Email && $this->is_plugin_email( get_class( $email ) ) ) ) {
				$kadence = Kadence_Woomail_Designer::get_instance();
				remove_action( 'woocommerce_email_header', array( $kadence, 'add_email_header' ), 20 );
				remove_filter( 'woocommerce_locate_template', array( $kadence, 'filter_locate_template' ), 10 );
			}

			if ( ! $email instanceof WC_Email || ( $email instanceof WC_Email && ! $this->is_plugin_email( get_class( $email ) ) ) || yith_ywar_email_templates_enabled() ) {

				if ( ! class_exists( 'Kadence_Woomail_Designer' ) ) {
					wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
				}

				return;
			}

			if ( 'base' !== $mail_style ) {
				$mail_style = str_replace( 'style-', '', $mail_style );

				$args = array(
					'email_heading'    => $email_heading,
					'email_logo'       => $email->get_option( "upload_logo_$mail_style" ),
					'email_logo_align' => $email->get_option( "logo_position_$mail_style" ),
				);

				wc_get_template( 'emails/yith-ywar-email-header.php', $args, '', YITH_YWAR_TEMPLATES_DIR );
			} else {
				wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
			}
		}

		/**
		 * Get the email footer.
		 *
		 * @param WC_Email|string|null $email      The current email.
		 * @param string               $mail_style The style of the email.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function email_footer( $email = null, string $mail_style = 'base' ) {

			if ( ( class_exists( 'Kadence_Woomail_Designer' ) && $email instanceof WC_Email && $this->is_plugin_email( get_class( $email ) ) ) ) {
				remove_filter( 'woocommerce_locate_template', array( Kadence_Woomail_Designer::get_instance(), 'filter_locate_template' ), 10 );
			}

			if ( ! $email instanceof WC_Email || ( $email instanceof WC_Email && ! $this->is_plugin_email( get_class( $email ) ) ) || yith_ywar_email_templates_enabled() ) {
				if ( ! class_exists( 'Kadence_Woomail_Designer' ) ) {
					wc_get_template( 'emails/email-footer.php' );
				}

				return;
			}

			if ( 'base' !== $mail_style ) {
				wc_get_template( 'emails/yith-ywar-email-footer.php', $email->get_extra_content_params(), '', YITH_YWAR_TEMPLATES_DIR );
			} else {
				wc_get_template( 'emails/email-footer.php' );
			}
		}

		/**
		 * Manage test email sending.
		 *
		 * @return void
		 * @throws Exception An Exception.
		 * @since  2.0.0
		 */
		public function ajax_send_test_mail() {

			try {
				check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

				$action = sanitize_text_field( wp_unslash( $_REQUEST['type'] ?? '' ) );

				if ( ! $action ) {
					throw new Exception( 'Error' );
				}

				$email       = wc()->mailer()->emails[ $action ];
				$args_obj    = yith_ywar_get_test_values( $email->id, sanitize_text_field( wp_unslash( $_REQUEST['email'] ?? '' ) ) );
				$mail_status = $email->trigger( $args_obj, true );

				if ( ! $mail_status ) {
					throw new Exception( 'Error' );
				}

				wp_send_json_success();
			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error( $e );
			}
		}

		/**
		 * Add email classes to WooCommerce
		 *
		 * @param array $emails Emails.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_email_classes( array $emails ): array {

			$plugin_emails = array(
				'YITH_YWAR_New_Review_Email'      => YITH_YWAR_INCLUDES_DIR . '/emails/class-yith-ywar-new-review-email.php',
				'YITH_YWAR_New_Reply_Email'       => YITH_YWAR_INCLUDES_DIR . '/emails/class-yith-ywar-new-reply-email.php',
				'YITH_YWAR_Reported_Review_Email' => YITH_YWAR_INCLUDES_DIR . '/emails/class-yith-ywar-reported-review-email.php',
			);

			/**
			 * APPLY_FILTERS: yith_ywar_emails
			 *
			 * Manages plugin's emails.
			 *
			 * @param array $plugin_emails The array of emails.
			 *
			 * @return array
			 */
			$plugin_emails = apply_filters( 'yith_ywar_emails', $plugin_emails );

			if ( ! empty( $plugin_emails ) ) {
				require_once YITH_YWAR_INCLUDES_DIR . '/emails/class-yith-ywar-email.php';

				foreach ( $plugin_emails as $classname => $path ) {
					$emails[ $classname ] = include $path;
				}
			}

			return $emails + $plugin_emails;
		}

		/**
		 * Return true if is a plugin email.
		 *
		 * @param string $email_class The email class name.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		private function is_plugin_email( string $email_class ): bool {
			return 0 === strpos( $email_class, 'YITH_YWAR_' );
		}

		/**
		 * Print the Emails tab
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function print_emails_tab() {

			$mailer             = wc()->mailer();
			$woocommerce_emails = $mailer->get_emails();
			$emails             = array_filter(
				$woocommerce_emails,
				function ( $value, $key ) {
					return $this->is_plugin_email( $key );
				},
				ARRAY_FILTER_USE_BOTH
			);

			yith_ywar_get_view( 'settings-tabs/html-emails.php', compact( 'emails' ) );
		}

		/**
		 * Switch email activation.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_switch_email_activation() {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$email_class_name   = sanitize_text_field( wp_unslash( $_REQUEST['email'] ?? '' ) );
			$enabled            = sanitize_title( wp_unslash( $_REQUEST['enabled'] ?? '' ) );
			$mailer             = wc()->mailer();
			$woocommerce_emails = $mailer->get_emails();

			if ( ! yith_ywar_current_user_can_manage() || ! $this->is_plugin_email( $email_class_name ) || ! in_array( $email_class_name, array_keys( $woocommerce_emails ), true ) || ! in_array( $enabled, array( 'yes', 'no' ), true ) ) {
				wp_send_json_error();
			}

			$email = $woocommerce_emails[ $email_class_name ];
			$data  = array();

			foreach ( $email->get_form_fields() as $key => $field ) {
				if ( 'title' !== $email->get_field_type( $field ) ) {
					$field_key  = $email->get_field_key( $key );
					$field_type = $email->get_field_type( $field );
					$value      = $email->settings[ $key ] ?? $field['default'] ?? '';

					if ( 'checkbox' === $field_type ) {
						if ( 'yes' === $value ) {
							$data[ $field_key ] = 1;
						}
					} else {
						$data[ $field_key ] = $value;
					}
				}
			}

			$field_key = $email->get_field_key( 'enabled' );
			if ( 'yes' === $enabled ) {
				$data[ $field_key ] = 1;
			} else {
				unset( $data[ $field_key ] );
			}

			$email->set_post_data( $data );
			$email->process_admin_options();

			wp_send_json_success();

			// phpcs:enable
		}

		/**
		 * Update email options.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_update_email_options() {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$email_class_name   = sanitize_text_field( wp_unslash( $_REQUEST['email'] ?? '' ) );
			$data               = wp_unslash( $_REQUEST['data'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$mailer             = wc()->mailer();
			$woocommerce_emails = $mailer->get_emails();

			if ( ! yith_ywar_current_user_can_manage() || ! $this->is_plugin_email( $email_class_name ) || ! in_array( $email_class_name, array_keys( $woocommerce_emails ), true ) ) {
				wp_send_json_error();
			}

			$email       = $woocommerce_emails[ $email_class_name ];
			$enabled_key = $email->get_field_key( 'enabled' );
			if ( $email->is_enabled() ) {
				$data[ $enabled_key ] = 1;
			} else {
				unset( $data[ $enabled_key ] );
			}
			$data['ywar_panel'] = 'yes';

			$email->set_post_data( $data );
			$email->process_admin_options();

			wp_send_json_success();

			// phpcs:enable
		}

		/**
		 * Switch language to translate the email.
		 *
		 * @param YITH_YWAR_Email $email             The email.
		 * @param bool            $is_customer_email Customer email flag.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function switch_language_to_translate_email( YITH_YWAR_Email $email, bool $is_customer_email ) {
			global $sitepress;

			if ( $is_customer_email ) {
				$language = $email->object['language'] ?? false;
				if ( $language && $sitepress ) {
					$current_language = $sitepress->get_current_language();

					if ( $language !== $current_language ) {
						$email->previous_language = $current_language;
						$sitepress->switch_lang( $language );
						$email->set_default_params();
					}
				}
			}
		}

		/**
		 * Switch language to the previous language.
		 *
		 * @param YITH_YWAR_Email $email The email.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function switch_language_after_translating_email( YITH_YWAR_Email $email ) {
			global $sitepress;

			if ( $email->previous_language ) {
				$sitepress->switch_lang( $email->previous_language );
				$email->previous_language = '';
			}
		}

		/**
		 * Filter email strings to translate them.
		 *
		 * @param string   $value     The value.
		 * @param WC_Email $email     The email.
		 * @param string   $old_value The old value.
		 * @param string   $key       The key.
		 *
		 * @return mixed
		 * @since 2.0.4
		 */
		public function filter_emails_strings( $value, $email, $old_value, $key ) {
			if ( $email instanceof YITH_YWAR_Email && $email->is_customer_email() && $email->object ) {
				$email_id             = $email->id;
				$options_to_translate = array( 'custom_message' );
				if ( in_array( $key, $options_to_translate, true ) ) {
					$domain           = "admin_texts_woocommerce_{$email_id}_settings";
					$name             = '[woocommerce_' . $email_id . '_settings]' . $key;
					$language         = $email->object['language'] ?? false;
					$translated_value = apply_filters( 'wpml_translate_single_string', false, $domain, $name, $language );

					if ( $translated_value ) {
						$value = $translated_value;
					}
				}
			}

			return $value;
		}

		/**
		 * Reload email preview
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function ajax_reload_email_preview() {

			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$email_class_name   = sanitize_text_field( wp_unslash( $_REQUEST['email'] ?? '' ) );
			$mail_style         = sanitize_text_field( wp_unslash( $_REQUEST['style'] ?? 'base' ) );
			$mail_body          = wp_kses_post( wp_unslash( $_REQUEST['body'] ?? '' ) );
			$mail_heading       = wp_kses_post( wp_unslash( $_REQUEST['heading'] ?? '' ) );
			$custom_data        = array(
				'style'   => $mail_style,
				'body'    => $mail_body,
				'heading' => $mail_heading,
			);
			$mailer             = wc()->mailer();
			$woocommerce_emails = $mailer->get_emails();

			if ( ! yith_ywar_current_user_can_manage() || ! $this->is_plugin_email( $email_class_name ) || ! in_array( $email_class_name, array_keys( $woocommerce_emails ), true ) ) {
				wp_send_json_error();
			}

			$email         = $woocommerce_emails[ $email_class_name ];
			$email->object = array_merge( yith_ywar_get_test_values( $email->id ), $custom_data );
			$email->init_placeholders_before_sending();
			$content = $email->get_content();

			ob_start();
			wc_get_template( 'emails/email-styles.php' );
			$css = apply_filters( 'woocommerce_email_styles', ob_get_clean(), $email, $mail_style );

			try {
				$css_inliner = CssInliner::fromHtml( $content )->inlineCss( $css );

				do_action( 'woocommerce_emogrifier', $css_inliner, $email );

				$dom_document = $css_inliner->getDomDocument();

				HtmlPruner::fromDomDocument( $dom_document )->removeElementsWithDisplayNone();
				$content = CssToAttributeConverter::fromDomDocument( $dom_document )->convertCssToVisualAttributes()->render();
			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
			}

			$content = yith_ywar_prune_preview_email_content( $content );

			wp_send_json_success( $content );
			// phpcs:enable
		}
	}
}
