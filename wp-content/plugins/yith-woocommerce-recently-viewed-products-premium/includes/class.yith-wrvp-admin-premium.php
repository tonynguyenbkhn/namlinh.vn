<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Admin Premium class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\RecentlyViewedProducts\Classes
 * @version 1.0.0
 */

defined( 'YITH_WRVP' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WRVP_Admin_Premium' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class YITH_WRVP_Admin_Premium extends YITH_WRVP_Admin {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var YITH_WRVP_Admin_Premium
		 */
		protected static $instance;

		/**
		 * An array of shortcodes data
		 *
		 * @since 1.5.0
		 * @var array
		 */
		private $shortcodes_data;

		/**
		 * Plugin version
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = YITH_WRVP_VERSION;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 * @return YITH_WRVP_Admin_Premium
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

			parent::__construct();

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// add tabs to plugin panel.
			add_filter( 'yith_wrvp_admin_tabs', array( $this, 'add_tabs' ), 10, 1 );

			add_action( 'yith_wrvp_shortcode_tab', array( $this, 'shortcode_tab' ) );

			// Custom tinymce button.
			add_action( 'admin_head', array( $this, 'tc_button' ) );

			// custom panel types.
			add_action( 'woocommerce_admin_field_ywrvp_image_size', array( $this, 'custom_image_size' ), 10, 1 );
			add_action( 'woocommerce_admin_field_ywrvp_custom_checklist', array( $this, 'custom_checklist_output' ), 10, 1 );
			add_action( 'woocommerce_admin_field_ywrvp_test_email', array( $this, 'test_email_output' ), 10, 1 );

			// register Gutenberg block.
			add_action( 'init', array( $this, 'register_gutenberg_block' ), 10 );

			// delete plugin transient on term add/update.
			add_action( 'created_term', array( $this, 'delete_transient' ), 10 );
			add_action( 'edit_term', array( $this, 'delete_transient' ), 10 );

			// import old option.
			add_action( 'init', array( $this, 'import_old_option' ), 0 );

			// filter field template editor textarea to let it works with brackets in IDs.
			add_filter( 'yith_plugin_fw_wc_panel_field_data', array( $this, 'filter_textarea_editor_field' ), 10, 1 );

			// redirect default email settings to the plugin tab.
			add_action( 'admin_init', array( $this, 'redirect_email_settings' ), 0 );
		}

		/**
		 * Redirect WC email settings page to plugin settings tab
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function redirect_email_settings() {
			if ( isset( $_GET['page'] ) && isset( $_GET['section'] ) && 'wc-settings' === $_GET['page'] && 'yith_wrvp_mail' === $_GET['section'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				wp_safe_redirect( admin_url( "admin.php?page={$this->panel_page}&tab=email" ) );
				exit;
			}
		}

		/**
		 * Enqueue scripts for admin panel
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_register_script( 'yith-wrvp-admin', YITH_WRVP_ASSETS_URL . '/js/yith-wrvp-admin' . $min . '.js', array( 'jquery' ), $this->version, true );
			wp_register_style( 'yith-wrvp-admin', YITH_WRVP_ASSETS_URL . '/css/yith-wrvp-admin.css', array(), $this->version );

			if ( isset( $_GET['page'] ) && 'yith_wrvp_panel' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				wp_enqueue_script( 'yith-wrvp-admin' );
				wp_enqueue_style( 'yith-wrvp-admin' );
			}
		}

		/**
		 * Add tabs to plugin setting panel
		 *
		 * @access public
		 * @since 1.0.0
		 * @param array $tabs Admin tabs.
		 * @return array
		 */
		public function add_tabs( $tabs ) {

			$tabs['shortcode'] = __( 'Create Shortcode', 'yith-woocommerce-recently-viewed-products' );
			$tabs['email']     = __( 'Email Settings', 'yith-woocommerce-recently-viewed-products' );

			return $tabs;
		}

		/**
		 * Load the shortcode tab template on admin page
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function shortcode_tab() {

			$shortcode_key = $this->get_shortcode_from_sub_tab();
			$shortcodes    = $this->get_shortcodes_data();

			if ( ! $shortcode_key || ! array_key_exists( $shortcode_key, $shortcodes ) ) {
				$shortcode = array_slice( $shortcodes, 0, 1 ); // get the first.
			} else {
				$shortcode = array_intersect_key( $shortcodes, array( $shortcode_key => '' ) );
			}

			$shortcode_tab_template = YITH_WRVP_TEMPLATE_PATH . '/admin/shortcode-tab.php';
			if ( file_exists( $shortcode_tab_template ) ) {
				include_once $shortcode_tab_template;
			}
		}

		/**
		 * Add custom image size to standard WC types
		 *
		 * @since 1.0.0
		 * @access public
		 * @param array $value Option data.
		 */
		public function custom_image_size( $value ) {

			$option_values = get_option( $value['id'] );
			$width         = isset( $option_values['width'] ) ? $option_values['width'] : $value['default']['width'];
			$height        = isset( $option_values['height'] ) ? $option_values['height'] : $value['default']['height'];
			$crop          = isset( $option_values['crop'] ) ? $option_values['crop'] : $value['default']['crop'];

			?>
			<tr valign="top">
			<th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
			<td class="forminp yith_image_size_settings"
				<?php
				if ( isset( $value['custom_attributes'] ) ) {
					foreach ( $value['custom_attributes'] as $key => $data ) {
						echo ' ' . esc_html( $key ) . '="' . esc_html( $data ) . '"';
					}
				}
				?>
			>

				<div class="yith_image_size_wrap">
					<input name="<?php echo esc_attr( $value['id'] ); ?>[width]"
							id="<?php echo esc_attr( $value['id'] ); ?>-width" type="text" size="3"
							value="<?php echo esc_attr( $width ); ?>"/><span>&times;</span>
					<input name="<?php echo esc_attr( $value['id'] ); ?>[height]"
							id="<?php echo esc_attr( $value['id'] ); ?>-height" type="text" size="3"
							value="<?php echo esc_attr( $height ); ?>"/><span>px</span>

					<label><input name="<?php echo esc_attr( $value['id'] ); ?>[crop]"
								id="<?php echo esc_attr( $value['id'] ); ?>-crop" type="checkbox"
								value="1" <?php checked( 1, $crop ); ?> /> <?php esc_html_e( 'Hard Crop?', 'yith-woocommerce-recently-viewed-products' ); ?>
					</label>
				</div>
				<span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
			</td>
			</tr>
			<?php

		}

		/**
		 * Add a new button to tinymce
		 *
		 * @since    1.0
		 * @return   void
		 */
		public function tc_button() {
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
				return;
			}

			if ( ! isset( $_GET['page'] ) || $this->panel_page !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}

			if ( 'true' === get_user_option( 'rich_editing' ) ) {
				add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
				add_filter( 'mce_buttons', array( $this, 'register_tc_button' ) );
				add_filter( 'mce_external_languages', array( $this, 'add_tc_button_lang' ) );
			}
		}

		/**
		 * Add plugin button to tinymce from filter mce_external_plugins
		 *
		 * @since    1.0
		 * @param    array $plugin_array Plugins array.
		 * @return   array
		 */
		public function add_tinymce_plugin( $plugin_array ) {
			$min                       = ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '.min' : '';
			$plugin_array['tc_button'] = YITH_WRVP_ASSETS_URL . '/js/tinymce/text-editor' . $min . '.js';

			return $plugin_array;
		}

		/**
		 * Register the custom button to tinymce from filter mce_buttons
		 *
		 * @since    1.0
		 * @param    array $buttons Buttons.
		 * @return   array
		 */
		public function register_tc_button( $buttons ) {
			array_push( $buttons, 'tc_button' );

			return $buttons;
		}

		/**
		 * Add multilingual to mce button from filter mce_external_languages
		 *
		 * @since    1.0
		 * @param    array $locales Locales.
		 * @return   array
		 */
		public function add_tc_button_lang( $locales ) {
			$locales ['tc_button'] = YITH_WRVP_DIR . 'includes/tinymce/tinymce-plugin-langs.php';

			return $locales;
		}

		/**
		 * Print the custom checklist output for admin settings panel
		 *
		 * @access public
		 * @since 1.0.4
		 * @param array $value Option data.
		 */
		public function custom_checklist_output( $value ) {
			$option_value = get_option( $value['id'] );

			$template = YITH_WRVP_TEMPLATE_PATH . '/admin/custom-checklist.php';
			if ( file_exists( $template ) ) {
				include_once $template;
			}
		}

		/**
		 * Print the custom test email field in email settings
		 *
		 * @access public
		 * @since 1.0.4
		 * @param array $value Option data.
		 */
		public function test_email_output( $value ) {
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="yith-wrvp-test-mail"><?php echo esc_html( $value['title'] ); ?></label>
				</th>
				<td class="yith-wrvp-test-email-wrap">
					<input type="text" id="yith-wrvp-test-mail" name="yith-wrvp-test-mail"
							placeholder="<?php esc_html_e( 'Type an email address to send a test email', 'yith-woocommerce-recently-viewed-products' ); ?>"/>
					<button type="submit" class="button-secondary ywrvp-send-test-email"><?php esc_html_e( 'Send email', 'yith-woocommerce-recently-viewed-products' ); ?></button>
					<span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
				</td>
			</tr>
			<?php
		}

		/**
		 * Register a block for Gutenberg editor
		 *
		 * @since 1.4.5
		 * @return void
		 */
		public function register_gutenberg_block() {

			$shortcodes = $this->get_shortcodes_data();
			if ( empty( $shortcodes ) ) {
				return;
			}

			foreach ( $shortcodes as $shortcode_name => $data ) {
				if ( empty( $data['block_id'] ) ) {
					continue;
				}

				// make sure data attributes are compliant with Gutenberg block.
				foreach ( $data['attributes'] as $data_key => &$data_field ) {
					if ( 'onoff' === $data_field['type'] ) {
						$data_field['type'] = 'radio';
					}

					$data_field['label'] = $data_field['title'];
				}

				$the_block = array(
					$data['block_id'] => array(
						'title'          => $data['title'],
						'description'    => $data['description'],
						'shortcode_name' => $shortcode_name,
						'do_shortcode'   => isset( $data['do_shortcode'] ) ? $data['do_shortcode'] : false,
						'attributes'     => $data['attributes'],
					),
				);

				yith_plugin_fw_gutenberg_add_blocks( $the_block );
			}
		}

		/**
		 * Get shortcodes data
		 *
		 * @since 1.5.0
		 * @return array
		 */
		public function get_shortcodes_data() {
			if ( empty( $this->shortcodes_data ) ) {
				$this->shortcodes_data = include YITH_WRVP_DIR . '/plugin-options/shortcodes-data.php';
			}

			return $this->shortcodes_data;
		}

		/**
		 * Get shortcode name from sub tab value
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function get_shortcode_from_sub_tab() {

			$tab = ! empty( $_GET['sub_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			if ( empty( $tab ) ) {
				return '';
			}

			$tab = str_replace( 'shortcode', 'yith', $tab );
			$tab = str_replace( '-', '_', $tab );

			return $tab;
		}

		/**
		 * Delete transient
		 *
		 * @since 1.5.0
		 * @return void
		 */
		public function delete_transient() {
			delete_transient( 'yith_wrvp_categories_list' );
		}

		/**
		 * Import old option to the new one
		 *
		 * @since 2.0.0
		 * @return void
		 */
		public function import_old_option() {

			$options = array(
				'yith-wrvp-num-products' => array(
					'total'   => 'yith-wrvp-num-tot-products',
					'per-row' => 'yith-wrvp-num-visible-products',
				),
			);

			foreach ( $options as $new_option => $old_option ) {

				if ( get_option( $new_option, false ) ) { // if option is already set, exit;.
					continue;
				}

				if ( is_array( $old_option ) ) {
					$value = array();
					foreach ( $old_option as $new_option_key => $old_option_key ) {
						$value[ $new_option_key ] = get_option( $old_option_key, '' );
					}
				} else {
					$value = get_option( $old_option, '' );
				}

				$value = array_filter( $value );
				if ( empty( $value ) ) {
					continue;
				}

				// update and delete old option.
				if ( update_option( $new_option, $value ) ) {
					if ( is_array( $old_option ) ) {
						foreach ( $old_option as $new_option_key => $old_option_key ) {
							delete_option( $old_option_key );
						}
					} else {
						delete_option( $old_option );
					}
				};
			}
		}

		/**
		 * Remove brackets from textarea editor ID
		 *
		 * @since 2.0.0
		 * @param array $field Field.
		 * @return array
		 */
		public function filter_textarea_editor_field( $field ) {
			if ( isset( $field['id'] ) && 'woocommerce_yith_wrvp_mail_settings[mail_content]' === $field['id'] ) {
				$field['id'] = 'woocommerce_yith_wrvp_mail_settings_mail_content';
			}

			return $field;
		}
	}
}
/**
 * Unique access to instance of YITH_WRVP_Admin_Premium class
 *
 * @since 1.0.0
 * @return YITH_WRVP_Admin_Premium
 */
function YITH_WRVP_Admin_Premium() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return YITH_WRVP_Admin_Premium::get_instance();
}
