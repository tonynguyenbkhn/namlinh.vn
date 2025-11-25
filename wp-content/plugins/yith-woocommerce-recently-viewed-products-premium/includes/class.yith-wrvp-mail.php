<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Email class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WRVP_Mail' ) ) {
	/**
	 * Email Class
	 * Extend WC_Email to send mail to customer
	 *
	 * @class    YITH_WRVP_Mail
	 * @extends  WC_Email
	 */
	class YITH_WRVP_Mail extends WC_Email {

		/**
		 * Temp options array
		 *
		 * @since 2.0.0
		 * @var array
		 */
		protected $temp_options = array();

		/**
		 * Constructor
		 *
		 */
		public function __construct() {

			$this->id             = 'yith_wrvp_mail';
			$this->title          = __( 'YITH Recently Viewed Products Email', 'yith-woocommerce-recently-viewed-products' );
			$this->customer_email = true;
			$this->description    = '';

			$this->heading      = __( '{blogname}', 'yith-woocommerce-recently-viewed-products' );
			$this->subject      = __( 'You may be interested in these products.', 'yith-woocommerce-recently-viewed-products' );
			$this->mail_content = __( 'According to your research, you may be interested in the following products. Moreover, purchasing one of these products will entitle you to receive a discount with the following coupon {coupon_code}{products_list}', 'yith-woocommerce-recently-viewed-products' );

			$this->template_base  = YITH_WRVP_TEMPLATE_PATH . '/email/';
			$this->template_html  = 'ywrvp-mail-template.php';
			$this->template_plain = 'plain/ywrvp-mail-template.php';

			// Triggers for this email.
			add_action( 'send_yith_wrvp_mail_notification', array( $this, 'trigger' ), 10, 1 );

			// Filter style for email.
			add_filter( 'woocommerce_email_styles', array( $this, 'my_email_style' ), 10, 1 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Set an array of temp options to use in test email
		 *
		 * @since  2.0.0
		 * @param array $options Array of options.
		 * @return void
		 */
		public function set_temp_options( $options ) {
			$this->temp_options = $options;
		}

		/**
		 * Get email options
		 *
		 * @since  2.0.0
		 * @param string $key Option key.
		 * @param mixed  $empty_value Option empty value.
		 * @return mixed
		 */
		public function get_option( $key, $empty_value = null ) {

			if ( isset( $this->temp_options[ $key ] ) ) {
				return $this->temp_options[ $key ];
			}

			return parent::get_option( $key, $empty_value );
		}

		/**
		 * Trigger Function
		 *
		 * @since  1.0.0
		 * @access public
		 * @param array $data Data to process in the email.
		 * @return boolean
		 */
		public function trigger( $data ) {

			if ( ! $this->is_enabled() ) {
				return false;
			}

			$return = false;
			// get option mail.
			$content         = $this->get_option( 'mail_content' );
			$coupon_enabled  = ( 'yes' === $this->get_option( 'coupon_enable' ) && 'yes' === get_option( 'woocommerce_enable_coupons' ) );
			$custom_products = ( 'yes' === $this->get_option( 'custom_products_enabled', 'yes' ) ) ? $this->get_option( 'custom_products', array() ) : array();
			$most_viewed_cat = 'yes' === $this->get_option( 'cat_most_viewed' );
			$products_type   = $this->get_option( 'products_type' );

			// find logo image.
			$find['blogname']      = '{blogname}';
			$replace['blogname']   = $this->get_blogname();
			$find['logo-image']    = '{logo_image}';
			$replace['logo-image'] = $this->print_logo_image();

			foreach ( $data as $customer => $products ) {

				// if products is empty that is test.
				$is_test              = empty( $products );
				$products_list        = '';
				$custom_products_list = '';
				$coupon_code          = '';
				$coupon_expire        = '';
				$cat_id               = false;
				$product_title        = 'Product-Name';

				if ( ! $is_test ) {
					// most viewed cat.
					if ( $most_viewed_cat ) {
						$cat_id = YITH_WRVP_Helper::most_viewed_cat( $products );
					}
					// get similar.
					if ( 'similar' === $products_type ) {
						$products = YITH_WRVP_Helper::get_similar_products( array( $cat_id ), '', $products );
					}

					// remove from products list the custom products to avoid duplication.
					if ( ( strpos( $content, '{custom_products_list}' ) >= 0 ) && ! empty( $custom_products ) ) {
						// get products list html.
						if ( ! is_array( $custom_products ) ) {
							$custom_products = explode( ',', $custom_products );
						}

						$products = array_diff( $products, $custom_products );
					}

					// set subject based on first product title.
					if ( strpos( $this->subject, '{first_product_title}' ) >= 0 ) {
						$first_product = array_slice( $products, 0, 1 );
						if ( ! is_null( $first_product ) ) {
							$product_title = get_the_title( array_shift( $first_product ) );

						}
					}
				}

				// products list.
				if ( strpos( $content, '{products_list}' ) >= 0 ) {
					// get products list html.
					$products_list = YITH_WRVP_Mail_Handler()->get_products_list_html( $products, false, $cat_id, $this );
				}

				$find['products-list']    = '{products_list}';
				$replace['products-list'] = $products_list;
				$find['first-product']    = '{first_product_title}';
				$replace['first-product'] = $product_title;

				// user fields.
				preg_match( '/\{customer_(.*?)\}/', $content, $customer_data );
				if ( $customer_data ) {
					$customer_obj = get_user_by( 'email', $customer );
					if ( $customer ) {
						foreach ( $customer_data as $data ) {
							if ( isset( $customer_obj->$data ) ) {
								$find[ "customer-$data" ]    = "{customer_$data}";
								$replace[ "customer-$data" ] = $customer_obj->$data;
							}
						}
					}
				}

				// coupon code..
				if ( $coupon_enabled && ( strpos( $content, '{coupon_code}' ) >= 0 ) ) {
					$type = $this->get_option( 'coupon_type' );
					if ( 'exs' === $type ) {
						$coupon_code = $this->get_option( 'coupon_code' );
						if ( $coupon_code ) {
							$id = wc_get_coupon_id_by_code( $coupon_code );
							// make sure coupon exists.
							if ( ! $id ) {
								$coupon_code = '';
							} else {
								$coupon = new WC_Coupon( $coupon_code );
								$expire = $coupon->get_date_expires();
								if ( ! is_null( $expire ) && $expire->getTimestamp() > time() ) {
									$coupon_expire = $expire->getTimestamp();
								}
							}
						}
					} else {
						$coupon_expire = time() + ( intval( $this->get_option( 'coupon_expiry' ) ) * DAY_IN_SECONDS );
						$coupon_value  = $this->get_option( 'coupon_amount' );
						// create coupon.
						$coupon_code = $is_test ? 'aaabbbccc' : YITH_WRVP_Mail_Handler()->add_coupon_to_mail( $customer, $products, $coupon_expire, $coupon_value );
					}
				}

				// coupon expire.
				$find['coupon-expire']    = '{coupon_expire}';
				$replace['coupon-expire'] = gmdate( 'Y-m-d', intval( $coupon_expire ) );
				$find['coupon-code']      = '{coupon_code}';
				$replace['coupon-code']   = yith_wrvp_get_mail_copuon_code_html( $coupon_code );

				// custom products list.
				if ( ( strpos( $content, '{custom_products_list}' ) >= 0 ) && ! empty( $custom_products ) ) {
					// get products list html.

					if ( ! is_array( $custom_products ) ) {
						$custom_products = explode( ',', $custom_products );
					}

					$custom_products_list = YITH_WRVP_Mail_Handler()->get_products_list_html( $custom_products, true, false, $this );
				}

				$find['custom-products-list']    = '{custom_products_list}';
				$replace['custom-products-list'] = $custom_products_list;

				// search for unsubscribe link.
				preg_match( '/\{{(.*?)\}}/', $content, $unsub_link );
				if ( $unsub_link && isset( $unsub_link[1] ) ) {

					$find['unsubscribe-from-list']    = '{{' . $unsub_link[1] . '}}';
					$replace['unsubscribe-from-list'] = YITH_WRVP_Mail_Handler()->get_unsubscribe_link( $customer, $unsub_link[1], $is_test );
				}

				// change placeholder values.
				$this->placeholders = array_merge(
					$this->placeholders,
					array_combine( array_values( $find ), array_values( $replace ) )
				);

				// send!
				$return = $this->send( $customer, $this->get_subject(), $this->get_content(), $this->get_headers(), array() );
				if ( $return ) {
					/**
					 * DO_ACTION: yith_wrvp_mail_sent_correctly
					 *
					 * Allows to trigger some action when the email has been sent correctly.
					 *
					 * @param int $customer Customer email address.
					 */
					do_action( 'yith_wrvp_mail_sent_correctly', $customer );
				} else {
					/**
					 * DO_ACTION: yith_wrvp_mail_sent_error
					 *
					 * Allows to trigger some action when the email has not been sent correctly.
					 *
					 * @param int $customer Customer email address.
					 */
					do_action( 'yith_wrvp_mail_sent_error', $customer );
				}
			}

			return $return;
		}

		/**
		 * Send mail using standard WP Mail or Mandrill Service
		 *
		 * @access public
		 * @since  1.0.0
		 * @param string $to Email receiver address.
		 * @param string $subject Email subject.
		 * @param string $message Email message.
		 * @param string $headers Email headers.
		 * @param array  $attachments Email attachments.
		 * @return bool | void
		 */
		public function send( $to, $subject, $message, $headers, $attachments ) {

			// Retrieve Mandrill API KEY.
			$api_key = get_option( 'yith-wrvp-mandrill-api-key' );

			if ( 'yes' !== get_option( 'yith-wrvp-use-mandrill' ) || empty( $api_key ) ) {
				return parent::send( $to, $subject, $message, $headers, $attachments );
			} else {

				/**
				 * Filter the wp_mail() arguments.
				 *
				 * @since 2.2.0
				 * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
				 *                    subject, message, headers, and attachments values.
				 */
				$atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

				if ( isset( $atts['to'] ) ) {
					$to = $atts['to'];
				}

				if ( isset( $atts['subject'] ) ) {
					$subject = $atts['subject'];
				}

				if ( isset( $atts['message'] ) ) {
					$message = $atts['message'];
				}

				if ( isset( $atts['headers'] ) ) {
					$headers = $atts['headers'];
				}

				if ( isset( $atts['attachments'] ) ) {
					$attachments = $atts['attachments'];
				}

				if ( ! is_array( $attachments ) ) {
					$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
				}

				// include lib.
				include_once YITH_WRVP_DIR . 'includes/third-party/Mandrill/Mandrill.php';

				// Headers.
				if ( empty( $headers ) ) {
					$headers = array();
				} else {
					if ( ! is_array( $headers ) ) {
						// Explode the headers out, so this function can take both
						// string headers and an array of headers.
						$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
					} else {
						$tempheaders = $headers;
					}

					$headers = array();
					$cc      = array();
					$bcc     = array();

					// If it's actually got contents.
					if ( ! empty( $tempheaders ) ) {
						// Iterate through the raw headers.
						foreach ( (array) $tempheaders as $header ) {
							if ( strpos( $header, ':' ) === false ) {
								if ( false !== stripos( $header, 'boundary=' ) ) {
									$parts    = preg_split( '/boundary=/i', trim( $header ) );
									$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
								}
								continue;
							}
							// Explode them out.
							list( $name, $content ) = explode( ':', trim( $header ), 2 );

							// Cleanup crew.
							$name    = trim( $name );
							$content = trim( $content );

							switch ( strtolower( $name ) ) {
								// Mainly for legacy -- process a From: header if it's there.
								case 'from':
									if ( strpos( $content, '<' ) !== false ) {
										// So... making my life hard again?
										$from_name = substr( $content, 0, strpos( $content, '<' ) - 1 );
										$from_name = str_replace( '"', '', $from_name );
										$from_name = trim( $from_name );

										$from_email = substr( $content, strpos( $content, '<' ) + 1 );
										$from_email = str_replace( '>', '', $from_email );
										$from_email = trim( $from_email );
									} else {
										$from_email = trim( $content );
									}
									break;
								default:
									// Add it to our grand headers array.
									$headers[ trim( $name ) ] = trim( $content );
									break;
							}
						}
					}
				}

				// From email and name
				// If we don't have a name from the input headers.
				if ( ! isset( $from_name ) ) {
					$from_name = $this->get_from_name();
				}

				// If we don't have an email from the input headers.
				if ( ! isset( $from_email ) ) {
					$from_email = $this->get_from_address();
				}

				// Set destination addresses.
				if ( ! is_array( $to ) ) {
					$to = explode( ',', $to );
				}

				$recipients = array();

				foreach ( (array) $to as $recipient ) {
					try {
						// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>".
						$recipient_name = '';
						if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
							if ( 3 === count( $matches ) ) {
								$recipient_name = $matches[1];
								$recipient      = $matches[2];
							}
						}
						$recipients[] = array(
							'email' => $recipient,
							'name'  => $recipient_name,
							'type'  => 'to',
						);
					} catch ( phpmailerException $e ) {
						continue;
					}
				}

				$files_to_attach = array();

				if ( ! empty( $attachments ) ) {
					foreach ( $attachments as $attachment ) {
						try {
							$new_attachment = $this->get_attachment_struct( $attachment );

							if ( false === $new_attachment ) {
								continue;
							}

							$files_to_attach[] = $new_attachment;
						} catch ( Exception $e ) {
							continue;
						}
					}
				}

				try {
					$mandrill = new Mandrill( $api_key );

					/**
					 * APPLY_FILTERS: ywrvp_mandrill_send_mail_message
					 *
					 * Filters the attributes to send the email through Mandrill.
					 *
					 * @param array $atts Array of attributes.
					 *
					 * @return array
					 */
					$message = apply_filters(
						'ywrvp_mandrill_send_mail_message',
						array(
							'html'        => apply_filters( 'woocommerce_mail_content', $this->style_inline( $message ) ),
							'subject'     => $subject,
							'from_email'  => apply_filters( 'wp_mail_from', $from_email ),
							'from_name'   => apply_filters( 'wp_mail_from_name', $from_name ),
							'to'          => $recipients,
							'headers'     => $headers,
							'attachments' => $files_to_attach,
						)
					);

					/**
					 * APPLY_FILTERS: ywrvp_mandrill_send_mail_async
					 *
					 * Filters whether to enable a background sending mode that is optimized for bulk sending.
					 *
					 * @param bool $send_mail_async Whether to enable a background sending mode or not.
					 *
					 * @return bool
					 */
					$async = apply_filters( 'ywrvp_mandrill_send_mail_async', false );

					/**
					 * APPLY_FILTERS: ywrvp_mandrill_send_mail_ip_pool
					 *
					 * Filters the name of the dedicated IP pool that should be used to send the message.
					 *
					 * @param string $ip_pool IP pool to send the message.
					 *
					 * @return string
					 */
					$ip_pool = apply_filters( 'ywrvp_mandrill_send_mail_ip_pool', null );

					/**
					 * APPLY_FILTERS: ywrvp_mandrill_send_mail_send_at
					 *
					 * Filters the date when the email should be sent.
					 *
					 * @param DateTime $send_at Date to send the email.
					 *
					 * @return DateTime
					 */
					$send_at = apply_filters( 'ywrvp_mandrill_send_mail_send_at', null );

					$results = $mandrill->messages->send( $message, $async, $ip_pool, $send_at );
					$return  = true;

					if ( ! empty( $results ) ) {
						foreach ( $results as $result ) {
							if ( ! isset( $result['status'] ) || in_array( $result['status'], array( 'rejected', 'invalid' ), true ) ) {
								$return = false;
							}
						}
					}

					return $return;
				} catch ( Mandrill_Error $e ) {
					return false;
				}
			}
		}

		/**
		 * Using file path, build an attachment struct, to use in Mandrill send request
		 *
		 * @since 1.0.0
		 * @param string $path File absolute path.
		 * @static
		 * @return bool|array
		 * [
		 *     type => mime type of the file
		 *     name => file name with extension
		 *     content => file complete content, divided in chunks
		 * ]
		 * @throws Exception When some error occurs with file handling.
		 */
		public static function get_attachment_struct( $path ) {

			$struct = array();

			try {
				if ( ! @is_file( $path ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
					throw new Exception( $path . ' is not a valid file.' );
				}

				$filename = basename( $path );

				$file_buffer = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$file_buffer = chunk_split( base64_encode( $file_buffer ), 76, "\n" ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

				$mime_type = '';
				if ( function_exists( 'finfo_open' ) && function_exists( 'finfo_file' ) ) {
					$finfo     = finfo_open( FILEINFO_MIME_TYPE );
					$mime_type = finfo_file( $finfo, $path );
				} elseif ( function_exists( 'mime_content_type' ) ) {
					$mime_type = mime_content_type( $path );
				}

				if ( ! empty( $mime_type ) ) {
					$struct['type'] = $mime_type;
				}

				$struct['name']    = $filename;
				$struct['content'] = $file_buffer;

			} catch ( Exception $e ) {
				return false;
			}

			return $struct;
		}

		/**
		 * Get custom email content from options
		 *
		 * @access public
		 * @since  1.0.0
		 * @return string
		 */
		public function get_custom_option_content() {
			$content = $this->get_option( 'mail_content' );

			return $this->format_string( $content );
		}

		/**
		 * Function get_content_html
		 *
		 * @access public
		 * @since  1.0.0
		 * @return string
		 */
		public function get_content_html() {
			ob_start();

			wc_get_template(
				$this->template_html,
				array(
					'email_heading' => $this->get_heading(),
					'email_content' => $this->get_custom_option_content(),
					'email'         => $this,
				),
				false,
				$this->template_base
			);

			return ob_get_clean();
		}

		/**
		 * Function get_content_plain
		 *
		 * @access public
		 * @since  1.0.0
		 * @return string
		 */
		public function get_content_plain() {
			ob_start();

			wc_get_template(
				$this->template_plain,
				array(
					'email_heading' => $this->get_heading(),
					'email_content' => $this->get_custom_option_content(),
					'email'         => $this,
				),
				false,
				$this->template_base
			);

			return ob_get_clean();
		}

		/**
		 * Filter email style and add custom style
		 *
		 * @access public
		 * @since  1.0.0
		 * @param string $css Email styles.
		 * @return string
		 */
		public function my_email_style( $css ) {

			if ( 'yith_wrvp_mail' !== $this->id ) {
				return $css;
			}

			ob_start();
			wc_get_template( 'ywrvp-mail-style.php', array(), '', YITH_WRVP_TEMPLATE_PATH . '/email/' );
			$css .= ob_get_clean();

			return $css;
		}

		/**
		 * Print html for logo image
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function print_logo_image() {

			$logo = $this->get_option( 'upload_logo' );

			if ( empty( $logo ) ) {
				return '';
			}

			ob_start();
			?>
			<img src="<?php echo esc_url( $logo ); ?>" alt="<?php esc_html_e( 'Logo Image', 'yith-woocommerce-recently-viewed-products' ); ?>">
			<?php

			return ob_get_clean();
		}

		/**
		 * Validate field select product
		 *
		 * @since  1.1.0
		 * @param string $key Option key.
		 * @param mixed  $value Option value.
		 * @return mixed
		 */
		public function validate_yith_wrvp_select_products_field( $key, $value = false ) {
			if ( false === $value ) {
				$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			}

			return is_array( $value ) ? array_filter( $value ) : (string) $value;
		}
	}
}

return new YITH_WRVP_Mail();
