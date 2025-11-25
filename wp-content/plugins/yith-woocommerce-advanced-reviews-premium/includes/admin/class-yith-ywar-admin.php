<?php
/**
 * Admin class
 *
 * @package YITH\AdvancedReviews\Admin
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Admin
	 */
	class YITH_YWAR_Admin {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * Plugin options
		 *
		 * @var array
		 */
		public $options = array();

		/**
		 * The plugin panel object
		 *
		 * @var YIT_Plugin_Panel_WooCommerce
		 */
		protected $panel;

		const PANEL_PAGE = 'yith_ywar_panel';

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			YITH_YWAR_Review_Box_Post_Type_Admin::get_instance();
			YITH_YWAR_Criteria_Tax_Admin::get_instance();

			add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWAR_DIR . '/' . basename( YITH_YWAR_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
			add_filter( 'yith_plugin_fw_get_field_template_path', array( $this, 'get_yith_panel_custom_template' ), 10, 2 );
			add_filter( 'yith_plugin_fw_inline_fields_allowed_types', array( $this, 'add_inline_field_panel' ), 10, 2 );
			add_action( 'admin_bar_menu', array( $this, 'add_menu_in_admin_bar' ), 999 );
			add_action( 'admin_enqueue_scripts', array( $this, 'woocommerce_custom_script' ), 999 );
			add_action( 'add_meta_boxes', array( $this, 'remove_comment_meta_box' ), 99 );
			add_action( 'admin_notices', array( $this, 'set_plugin_notices' ) );
			add_action( 'init', array( $this, 'check_migration_page' ) );
			add_filter( 'woocommerce_duplicate_product_exclude_meta', array( $this, 'prevent_stats_duplication' ) );
		}

		/**
		 * Check if should enter in the Migration tools page
		 *
		 * @return void
		 * @since  2.1.0
		 */
		public function check_migration_page() {
			if ( isset( $_GET['tab'] ) && 'migration-tools' === $_GET['tab'] && ! yith_ywar_review_reminder_enabled() && ! yith_ywar_review_for_discounts_enabled() ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$args = array(
					'page' => self::PANEL_PAGE,
				);

				wp_safe_redirect( add_query_arg( $args, 'admin.php' ) );
				exit;
			}
		}

		/**
		 * Prevent stats duplication
		 *
		 * @param array $meta_data Product metadata to not duplicate.
		 *
		 * @return array
		 * @since  2.1.0
		 */
		public function prevent_stats_duplication( array $meta_data ): array {
			$meta_data[] = '_ywar_stats';

			return $meta_data;
		}

		/**
		 * Manage plugin notices
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function set_plugin_notices() {
			if ( isset( $_GET['page'] ) && 'product-reviews' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<div class="notice notice-warning">
					<p>
						<?php
						printf(
						/* translators: %1$s plugin name, %2$s opening link tag %3$s closing link tag */
							esc_html_x( 'You are using %1$s. Manage all the options for your reviews from %2$sthis page%3$s.', '[Admin panel] Notice displayed in the Product/Reviews page of WooCommerce', 'yith-woocommerce-advanced-reviews' ),
							'<b>' . esc_html( YITH_YWAR_PLUGIN_NAME ) . '</b>',
							'<a href="' . esc_url( add_query_arg( array( 'post_type' => YITH_YWAR_Post_Types::REVIEWS ), admin_url( 'edit.php' ) ) ) . '">',
							'</a>'
						);
						?>
					</p>
				</div>
				<?php
			}

			if ( yith_ywar_is_admin_page( 'all-plugin-pages' ) && ! empty( wc()->queue()->get_next( 'yith_ywar_update_product_stats' ) ) ) {

				$url = esc_url(
					add_query_arg(
						array(
							'page'   => 'action-scheduler',
							'status' => 'pending',
							's'      => 'yith_ywar_update_product_stats',
						),
						admin_url( 'tools.php' )
					)
				);

				yith_plugin_fw_get_component(
					array(
						'type'        => 'notice',
						'notice_type' => 'warning',
						'message'     => sprintf(
						/* translators: %1$s plugin name, %2$s opening link tag %3$s closing link tag */
							esc_html_x( '%1$s is updating the statistics of product reviews. The data may not be consistent until the process is completed. You can check the update process on %2$sthis page%3$s.', '[Admin panel] Notice displayed in the Product/Reviews page of WooCommerce', 'yith-woocommerce-advanced-reviews' ),
							'<b>' . esc_html( YITH_YWAR_PLUGIN_NAME ) . '</b>',
							'<a href="' . $url . '" target="_blank">',
							'</a>'
						),
					),
					true
				);
			}

			if ( yith_ywar_is_admin_page( 'all-plugin-pages' ) && ! empty( wc()->queue()->get_next( 'yith_ywar_run_update_callback' ) ) ) {

				$url = esc_url(
					add_query_arg(
						array(
							'page'   => 'action-scheduler',
							'status' => 'pending',
							's'      => 'yith_ywar_run_update_callback',
						),
						admin_url( 'tools.php' )
					)
				);

				yith_plugin_fw_get_component(
					array(
						'type'        => 'notice',
						'notice_type' => 'warning',
						'message'     => sprintf(
						/* translators: %1$s plugin name, %2$s opening link tag %3$s closing link tag */
							esc_html_x( '%1$s is converting the reviews. The data may not be consistent until the process is completed. You can check the update process on %2$sthis page%3$s.', '[Admin panel] Notice displayed in the plugin pages', 'yith-woocommerce-advanced-reviews' ),
							'<b>' . esc_html( YITH_YWAR_PLUGIN_NAME ) . '</b>',
							'<a href="' . $url . '" target="_blank">',
							'</a>'
						),
					),
					true
				);
			}
		}

		/**
		 * Remove comment meta box from product page
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function remove_comment_meta_box() {
			global $post;

			if ( isset( $post ) && ( 'publish' === $post->post_status || 'private' === $post->post_status ) && post_type_supports( 'product', 'comments' ) ) {
				remove_meta_box( 'commentsdiv', 'product', 'normal' );
			}
		}

		/**
		 * Add custom script for disabling rating option in WooCommerce panel
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function woocommerce_custom_script() {
			/* translators: %s plugin name */
			$message = sprintf( esc_html_x( 'The following options are disabled because the %s plugin is enabled.', '[Admin] message displayed in WooCommerce/Settings/Products', 'yith-woocommerce-advanced-reviews' ), '<b>' . esc_html( YITH_YWAR_PLUGIN_NAME ) . '</b>' );
			$js      = 'jQuery( function ( $ ) { let wrapper = $( "#woocommerce_enable_review_rating" ).closest( "td" ); wrapper.prepend( "<div class=\"yith-ywar-notice\">%s</div>" ); wrapper.find( "input" ).attr( "disabled", true ); } );';
			$css     = '.yith-ywar-notice {	padding: 10px; font-style: italic; background: #fcf3e9; width: max-content; font-size: small; border-radius: 5px; } .product_page_product-reviews span.reply { display: none; }';
			wp_add_inline_script( 'woocommerce_admin', sprintf( $js, $message ) );
			wp_add_inline_style( 'woocommerce_admin_styles', $css );
		}

		/**
		 * Add Reviews node in admin bar.
		 *
		 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function add_menu_in_admin_bar( WP_Admin_Bar $wp_admin_bar ) {

			$count         = get_transient( YITH_YWAR::TRANSIENT );
			$url           = add_query_arg( array( 'post_type' => YITH_YWAR_Post_Types::REVIEWS ), admin_url( 'edit.php' ) );
			$icon          = '<span class="icon"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"></path></svg></span>';
			$count_total   = isset( $count['total'] ) && 0 < $count['total'] ? '<span class="count">' . $count['total'] . '</span>' : '';
			$count_reviews = isset( $count['reviews'] ) && 0 < count( $count['reviews'] ) ? esc_html_x( 'New reviews', '[Admin panel] Admin widget label', 'yith-woocommerce-advanced-reviews' ) . '<span class="count">' . count( $count['reviews'] ) . '</span>' : esc_html_x( 'No new reviews', '[Admin panel] Admin widget label', 'yith-woocommerce-advanced-reviews' );
			$count_replies = isset( $count['replies'] ) && 0 < count( $count['replies'] ) ? esc_html_x( 'New replies', '[Admin panel] Admin widget label', 'yith-woocommerce-advanced-reviews' ) . '<span class="count">' . count( $count['replies'] ) . '</span>' : esc_html_x( 'No new replies', '[Admin panel] Admin widget label', 'yith-woocommerce-advanced-reviews' );
			$nodes         = array(
				'main'    => array(
					'id'    => 'yith-ywar-admin-bar',
					'title' => $icon . esc_html_x( 'Reviews', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ) . $count_total,
					'href'  => $url,
					'meta'  => array(
						'class' => 'yith-ywar-admin-bar',
					),
				),
				'reviews' => array(
					'id'     => 'yith-ywar-admin-bar-new-reviews',
					'parent' => 'yith-ywar-admin-bar',
					'title'  => $count_reviews,
					'href'   => $url,
					'meta'   => array(
						'class' => 'yith-ywar-admin-bar-new-reviews',
					),
				),
				'replies' => array(
					'id'     => 'yith-ywar-admin-bar-new-replies',
					'parent' => 'yith-ywar-admin-bar',
					'title'  => $count_replies,
					'href'   => $url,
					'meta'   => array(
						'class' => 'yith-ywar-admin-bar-new-replies',
					),
				),
			);

			foreach ( $nodes as $node ) {
				$wp_admin_bar->add_node( $node );
			}
		}

		/**
		 * Add custom panel fields.
		 *
		 * @param string $template Template ID.
		 * @param array  $field    Field options.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_yith_panel_custom_template( string $template, array $field ): string {
			$custom_option_types = array(
				'yith-ywar-analytics-terms',
				'yith-ywar-rating',
				'yith-ywar-multi-rating',
				'yith-ywar-attachments',
			);

			$field_type = $field['type'];

			if ( isset( $field['type'] ) && in_array( $field['type'], $custom_option_types, true ) ) {
				$template = YITH_YWAR_VIEWS_PATH . "/panel/fields/$field_type.php";
			}

			return $template;
		}

		/**
		 * Add colorpicket to inline field allowed types
		 *
		 * @param array  $types The allowed field types.
		 * @param string $name  The field name.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_inline_field_panel( array $types, string $name ): array {
			if ( in_array( $name, array( 'ywar_featured_badge', 'ywar_staff_badge' ), true ) ) {
				$types[] = 'colorpicker';
			}

			return $types;
		}

		/**
		 * Action Links. Add the action links to plugin admin page
		 *
		 * @param array $links An array of action links.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function action_links( array $links ): array {
			$links = yith_add_action_links( $links, self::PANEL_PAGE, true, YITH_YWAR_SLUG );

			return $links;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return void
		 * @since  2.0.0
		 * @use    /Yit_Plugin_Panel class
		 * @see    plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs = array(
				'dashboard'    => array(
					'title' => esc_html_x( 'Main overview', '[Admin panel] Plugin options section name', 'yith-woocommerce-advanced-reviews' ),
					'icon'  => 'dashboard',
				),
				'general'      => array(
					'title' => esc_html_x( 'Settings', '[Admin panel] Plugin options section name', 'yith-woocommerce-advanced-reviews' ),
					'icon'  => 'settings',
				),
				'review-boxes' => array(
					'title' => esc_html_x( 'Review boxes', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'icon'  => '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"></path></svg>',
				),
				'modules'      => array(
					'title'       => esc_html_x( 'Modules', '[Admin panel] Plugin options section name', 'yith-woocommerce-advanced-reviews' ),
					'icon'        => 'add-ons',
					'description' => esc_html_x( 'Enable the following modules to unlock additional features for your reviews.', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' ),
				),
			);

			$admin_tabs           = apply_filters( 'yith_ywar_modules_admin_tabs', $admin_tabs );
			$admin_tabs['emails'] = array(
				'title'       => esc_html_x( 'Emails', '[Admin panel] Plugin options section name', 'yith-woocommerce-advanced-reviews' ),
				'icon'        => '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path></svg>',
				'description' => esc_html_x( 'Manage all the emails sent by the plugin.', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' ),
			);

			$args = array(
				'ui_version'       => 2,
				'create_menu_page' => true,
				'plugin_slug'      => YITH_YWAR_SLUG,
				'is_premium'       => true,
				'parent_slug'      => '',
				'page_title'       => YITH_YWAR_PLUGIN_NAME,
				'plugin_version'   => YITH_YWAR_VERSION,
				'menu_title'       => 'Advanced Reviews',
				'capability'       => 'manage_woocommerce',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => self::PANEL_PAGE,
				'admin-tabs'       => apply_filters( 'yith_ywar_admin_tabs', $admin_tabs ),
				'options-path'     => YITH_YWAR_DIR . '/plugin-options',
				'class'            => yith_set_wrapper_class(),
				'help_tab'         => $this->get_help_tab_options(),
				'your_store_tools' => $this->get_store_tools_tab_options(),
				'welcome_modals'   => $this->get_welcome_modals_options(),
			);

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Get panel help tab options
		 *
		 * @return array
		 * @since 2.0.0
		 */
		protected function get_help_tab_options(): array {
			return array(
				'main_video' => array(
					'desc' => _x( 'Check this video to learn how to <b>configure the plugin:</b>', '[HELP TAB] Video title', 'yith-woocommerce-advanced-reviews' ),
					'url'  => array(
						'en' => '//www.youtube.com/embed/3qKsV7tAksI',
						'it' => '//www.youtube.com/embed/QXGZjQ1dM8I',
						'es' => '//www.youtube.com/embed/ERXsjo-kzQs',
					),
				),
				'playlists'  => array(
					'en' => '',
					'it' => '',
					'es' => '',
				),
			);
		}

		/**
		 * Get welcome modals options
		 *
		 * @return array
		 * @since 2.0.0
		 */
		protected function get_welcome_modals_options(): array {
			return array(
				'on_close' => function () {
					update_option( 'yith-ywar-welcome-modal', 'no' );
				},
				'show_in'  => 'first_page',
				'modals'   => array(
					'welcome' => array(
						'type'        => 'welcome',
						'description' => esc_html_x( 'The ultimate all-in-one solution to manage product reviews in your shop.', '[Welcome modal] Plugin description', 'yith-woocommerce-advanced-reviews' ),
						'show'        => 'welcome' === get_option( 'yith-ywar-welcome-modal', 'welcome' ),
						'items'       => array(
							'documentation'  => array(),
							'how-to-video'   => array(
								'url' => array(
									'en' => '',
									'it' => '',
									'es' => '',
								),
							),
							'extra-modules'  => array(
								/* translators: %1$s Opening MARK tag - %2$s closing MARK tag */
								'title'       => sprintf( esc_html_x( '%1$sEnable the extra modules%2$s available in the plugin', '[Welcome modal] Features description', 'yith-woocommerce-advanced-reviews' ), '<mark>', '</mark>' ),
								'description' => esc_html_x( '...to encourage customers to leave a review right away.', '[Welcome modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'url'         => add_query_arg(
									array(
										'page' => self::PANEL_PAGE,
										'tab'  => 'modules',
									),
									admin_url( 'admin.php' )
								),
							),
							'create-product' => array(
								/* translators: %1$s Opening MARK tag - %2$s closing MARK tag */
								'title'       => sprintf( esc_html_x( 'Configure the plugin %1$sstep-by-step%2$s', '[Welcome modal] Features description', 'yith-woocommerce-advanced-reviews' ), '<mark>', '</mark>' ),
								'description' => esc_html_x( '...to make it work exactly the way you want!', '[Welcome modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'url'         => add_query_arg(
									array(
										'page'    => self::PANEL_PAGE,
										'tab'     => 'general',
										'sub_tab' => 'general-general',
									),
									admin_url( 'admin.php' )
								),
							),
						),
					),
					'update'  => array(
						'type'  => 'update',
						'since' => YITH_YWAR_VERSION,
						'show'  => 'update' === get_option( 'yith-ywar-welcome-modal', 'welcome' ),
						'items' => array(
							'extra-modules'  => array(
								'title'       => esc_html_x( 'The "Review reminder" and "Review for discount" modules make your customers leave a review right away.', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'description' => esc_html_x( 'Now you can send automated notifications to your clients, ask them to leave a review, and offer them discount coupons to keep them coming back for more. We decided to include these features for free in version 2.0 of this plugin, even though they were available in plugins that can be purchased separately for a total of €120!', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'url'         => add_query_arg(
									array(
										'page' => self::PANEL_PAGE,
										'tab'  => 'modules',
									),
									admin_url( 'admin.php' )
								),
							),
							'multi-criteria' => array(
								'title'       => esc_html_x( 'The possibility to define multiple evaluation criteria.', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'description' => esc_html_x( 'In 2.0, give your customers the ability to rate different aspects of your sales, including size, color, delivery, and quality, for more transparent feedback. You choose which policies to configure and which products to display.', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'url'         => add_query_arg(
									array(
										'page'    => self::PANEL_PAGE,
										'tab'     => 'review-boxes',
										'sub_tab' => 'review-boxes-boxes',
									),
									admin_url( 'admin.php' )
								),
							),
							'dashboard'      => array(
								'title'       => esc_html_x( 'A new dashboard to monitor feedback.', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'description' => esc_html_x( 'For an immediate overview of the reviews posted on your products.', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'url'         => add_query_arg(
									array(
										'page'    => self::PANEL_PAGE,
										'tab'     => 'dashboard',
										'sub_tab' => 'dashboard-dashboard',
									),
									admin_url( 'admin.php' )
								),
							),
							'style'          => array(
								'title'       => esc_html_x( 'A modern design with attention to every detail.', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'description' => esc_html_x( 'A configuration panel that is easier to use and a review section with a modern style that is suitable for any theme. After all, aesthetics are important too.', '[Update modal] Features description', 'yith-woocommerce-advanced-reviews' ),
								'url'         => add_query_arg(
									array(
										'page'    => self::PANEL_PAGE,
										'tab'     => 'general',
										'sub_tab' => 'general-style',
									),
									admin_url( 'admin.php' )
								),
							),
						),
					),
				),
			);
		}

		/**
		 * Get panel store tools tab options
		 *
		 * @return array
		 * @since 2.0.0
		 */
		protected function get_store_tools_tab_options(): array {
			return array(
				'items' => array(
					'ajax-search'          => array(
						'name'        => 'AJAX Search',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/ajax-search.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-search/',
						// translators: Description of "YITH AJAX Search" plugin in the "Your Store Tools" tab.
						'description' => esc_html__( 'Add an instant search form to your e-commerce shop and help your customers quickly find the products they want to buy.', 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YITH_WCAS_PREMIUM' ),
					),
					'ajax-product-filter'  => array(
						'name'        => 'AJAX Product Filter',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/ajax-product-filter.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/',
						// translators: Description of "YITH AJAX Product Filter" plugin in the "Your Store Tools" tab.
						'description' => esc_html__( 'Help your customers to easily find the products they are looking for and improve the user experience of your shop.', 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YITH_WCAN_PREMIUM' ),
					),
					'gift-card'            => array(
						'name'        => 'Gift Cards',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/gift-cards.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-gift-cards/',
						// translators: Description of "YITH Gift Cards" plugin in the "Your Store Tools" tab.
						'description' => esc_html__( "Sell gift cards to increase your store's revenue and win new customers.", 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YITH_YWGC_PREMIUM' ),
					),
					'wishlist'             => array(
						'name'        => 'Wishlist',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/wishlist.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-wishlist/',
						// translators: Description of "YITH Wishlist" plugin in the "Your Store Tools" tab.
						'description' => esc_html__( 'Allow your customers to create lists of products they want and share them with family and friends.', 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YITH_WCWL_PREMIUM' ),
					),
					'dynamic-pricing'      => array(
						'name'        => 'Dynamic Pricing and Discounts',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/dynamic-pricing-and-discounts.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-dynamic-pricing-and-discounts/',
						// translators: Description of "YITH Dynamic Pricing and Discounts" plugin in the "Your Store Tools" tab.
						'description' => esc_html__( 'Increase conversions through dynamic discounts and price rules, and build powerful and targeted offers.', 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YITH_YWDPD_PREMIUM' ),
					),
					'request-a-quote'      => array(
						'name'        => 'Request a Quote',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/request-a-quote.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
						// translators: Description of "YITH Request a Quote" plugin in the "Your Store Tools" tab.
						'description' => esc_html_x( 'Hide prices and/or the "Add to cart" button and let your customers request a custom quote for every product.', 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YITH_YWRAQ_PREMIUM' ),
					),
					'catalog-mode'         => array(
						'name'        => 'Catalog Mode',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/catalog-mode.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-catalog-mode/',
						// translators: Description of "YITH Catalog Mode" plugin in the "Your Store Tools" tab.
						'description' => esc_html__( 'Use your shop as a catalog by hiding prices and/or the "Add to cart" button on product pages.', 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YWCTM_PREMIUM' ),
					),
					'customize-my-account' => array(
						'name'        => 'Customize My Account Page',
						'icon_url'    => YITH_YWAR_ASSETS_URL . '/images/plugins/customize-myaccount-page.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-customize-my-account-page/',
						// translators: Description of "YITH Customize My Account Page" plugin in the "Your Store Tools" tab.
						'description' => esc_html__( 'Customize the My Account page of your customers by creating custom sections with promotions and ad-hoc content based on your needs.', 'yith-woocommerce-advanced-reviews' ),
						'is_active'   => defined( 'YITH_WCMAP_PREMIUM' ),
					),
				),
			);
		}

		/**
		 * Add plugin row metas
		 *
		 * @param array    $new_row_meta_args An array of plugin row meta.
		 * @param string[] $plugin_meta       An array of the plugin's metadata,
		 *                                    including the version, author,
		 *                                    author URI, and plugin URI.
		 * @param string   $plugin_file       Path to the plugin file relative to the plugins directory.
		 * @param array    $plugin_data       An array of plugin data.
		 * @param string   $status            Status of the plugin. Defaults are 'All', 'Active',
		 *                                    'Inactive', 'Recently Activated', 'Upgrade', 'Must-Use',
		 *                                    'Drop-ins', 'Search', 'Paused'.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function plugin_row_meta( array $new_row_meta_args, array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

			if ( defined( 'YITH_YWAR_INIT' ) && YITH_YWAR_INIT === $plugin_file ) {
				$new_row_meta_args['slug']       = YITH_YWAR_SLUG;
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}
	}
}
