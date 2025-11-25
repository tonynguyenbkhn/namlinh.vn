<?php
/**
 * Admin Boost to manage the admin panel.
 *
 * @author  YITH
 * @package YITH/Search
 * @version 2.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'YITH_WCAS_Admin_Boost' ) ) {
	/**
	 * Admin Boost to manage the admin panel
	 *
	 * @since 2.1.0
	 */
	class YITH_WCAS_Admin_Boost {

		/**
		 * Constructor
		 *
		 * @access public
		 * @since  2.1.0
		 */
		public function __construct() {
			add_action( 'ywcas_product_boost_tab', array( $this, 'show_product_boost' ) );

			add_action( 'admin_footer', array( $this, 'add_boost_rule_condition_template' ), 25 );
			add_action( 'ywcas_show_custom_field', array( $this, 'show_custom_field' ) );

			add_action( 'wp_ajax_ywcas_boost_product', array( $this, 'ajax_add_boost_product' ) );
			add_action( 'wp_ajax_ywcas_search_product', array( $this, 'ajax_search_products' ) );
			add_action( 'wp_ajax_ywcas_delete_boosted_product', array( $this, 'ajax_delete_boosted_product' ) );
			add_action( 'wp_ajax_ywcas_update_boost', array( $this, 'ajax_ywcas_update_boost' ) );
			add_action( 'wp_ajax_yith_wcas_load_boost_rule', array( $this, 'load_boost_rule' ) );
			add_action( 'wp_ajax_yith_wcas_save_boost_rule', array( $this, 'save_boost_rule' ) );
			add_action( 'wp_ajax_yith_wcas_edit_in_line_boost_rule', array( $this, 'edit_in_line_boost_rule' ) );

		}

		/**
		 * Show the product boost tab
		 *
		 * @return void
		 * @since 2.1.0
		 */
		public function show_product_boost() {
			if ( isset( $_GET['page'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$page    = sanitize_text_field( wp_unslash( $_GET['page'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$tab     = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '';  //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$sub_tab = isset( $_GET['sub_tab'] ) ? sanitize_text_field( wp_unslash( $_GET['sub_tab'] ) ) : '';  //phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( 'yith_wcas_panel' === $page && 'boost' === $tab && 'boost-boost-product' === $sub_tab ) {
					$this->show_boost_page();
					include_once YITH_WCAS_INC . 'admin/views/panel/boost-product-modal.php';
				}
			}
		}

		/**
		 * Show the boost page
		 *
		 * @return void
		 */
		public function show_boost_page() {
			$boosted = YITH_WCAS_Data_Index_Lookup::get_instance()->get_boosted_products( false, 'desc', 1, 0 );
			$class = '';

			if ( isset( $_REQUEST['action'], $_REQUEST['boost'] ) && 'delete' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ){
				$class = 'show yith-plugin-fw-animate__appear-from-top';
			}

			echo '<div class="ywcas-boost-table">';

			if ( empty( $boosted ) ) {
				echo ' <div class="ywcas-boost-detail yith-plugin-ui--boxed-wp-list-style ywcas-boost-product-wrapper">';

				echo '<div class="yith-plugin-fw-wp-page-wrapper"><div class="wrap"> <div id="message" class="notice is-dismissible updated inline yith-plugin-fw-animate__appear-from-topn ' . $class . '">
					<p>' . esc_html__( 'The rule has permanently deleted.', 'yith-woocommerce-ajax-search' ) . '</p>
					<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'yith-woocommerce-ajax-search' ) . '</span></button></div></div></div>';

				yith_plugin_fw_get_component(
					array(
						'type'     => 'list-table-blank-state',
						'icon_url' => YITH_WCAS_ASSETS_URL . '/images/boost-product.svg',
						'message'  => sprintf( /* translators: %1$s and %2$s are html tag */
							_x(
								'No products boosted yet.%1$sAdd now a product to set a custom boost value!%2$s',
								'Text showed when the list of email is empty.',
								'yith-woocommerce-ajax-search'
							),
							'<br><p>',
							'</p>'
						),
						'cta'      => array(
							'title' => __( 'Choose product', 'yith-woocommerce-ajax-search' ),
							'class' => 'ywcas-boost-product-button',
						),
					)
				);
				echo '</div>';
			} else {
				include_once YITH_WCAS_INC . 'admin/views/panel/boost-product.php';
			}
			echo '</div>';
			echo '<div id="header-add-product" class="button-primary ywcas-boost-product-button">' . esc_html__( 'Add product', 'yith-woocommerce-ajax-search' ) . '</div>';

		}


		/**
		 * Product Search
		 *
		 * @return void
		 * @since 2.1.0
		 */
		public function ajax_add_boost_product() {

			check_ajax_referer( 'ywcas-search-product', 'security' );

			if ( ! isset( $_POST['ywcas-boost-product-checked'] ) ) {
				wp_send_json_error( __( 'Please select a product', 'yith-woocommerce-ajax-search' ) );
			}

			$product_list = array_keys( $_POST['ywcas-boost-product-checked'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			$boost   = isset( $_POST['ywcas-boost-value'] ) ? sanitize_text_field( wp_unslash( $_POST['ywcas-boost-value'] ) ) : 0.1;
			$updated = YITH_WCAS_Data_Index_Lookup::get_instance()->set_boost_to_products( $product_list, $boost );

			if ( $updated ) {
				foreach ( $product_list as $product_id ) {
					$product = wc_get_product( $product_id );
					if ( $product instanceof WC_Product ) {
						$product->update_meta_data( 'ywcas_product_boost', $boost );
						$product->save_meta_data();
					}
				}
			}

			wp_send_json_success( array( 'content' => $this->get_ajax_table_content() ) );
		}

		/**
		 * Return the page content for ajax requests.
		 *
		 * @return void
		 */
		public function ajax_delete_boosted_product() {
			check_ajax_referer( 'ywcas-search-product', 'security' );
			$content = '';
			if ( isset( $_POST['post_id'] ) ) {
				$post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );
				YITH_WCAS_Data_Index_Lookup::get_instance()->set_boost_to_products( array( $post_id ), 0 );
			}

			if ( isset( $_POST['rows'] ) ) {
				$rows = (int) sanitize_text_field( wp_unslash( $_POST['rows'] ) );
				if ( 1 === $rows ) {
					$content = $this->get_ajax_table_content();
				}
			}

			wp_send_json_success( array( 'content' => $content ) );
		}

		/**
		 * Return the page content for ajax requests.
		 *
		 * @return false|string
		 */
		public function get_ajax_table_content () {
			ob_start();
			$this->show_boost_page();

			return ob_get_clean();
		}

		/**
		 * Product Search
		 *
		 * @param string $term Term to search.
		 * @param int    $limit Num of results.
		 *
		 * @return array
		 * @since 2.1.0
		 */
		public function search_products( $term, $limit = 20 ) {
			if ( empty( $term ) ) {
				return array();
			}

			return wc_get_products(
				array(
					's'           => $term,
					'parent'      => 0,
					'post_status' => 'publish',
					'limit'       => $limit,
					'type'        => array(
						'simple',
						'variable',
					),
				)
			);
		}

		/**
		 * Search the products to show in list.
		 *
		 * @return void
		 */
		public function ajax_search_products() {
			if ( ! isset( $_POST['term'] ) ) {
				return;
			}
			check_ajax_referer( 'ywcas-search-product', 'security' );

			$term     = sanitize_text_field( wp_unslash( $_POST['term'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$products = $this->search_products( $term, - 1 );
			ob_start();
			include_once YITH_WCAS_INC . 'admin/views/panel/boost-product-modal.php';
			$modal_content = ob_get_clean();
			wp_send_json_success( array( 'content' => $modal_content ) );
		}

		/**
		 * Add in footer the boost rule condition template
		 *
		 * @return void
		 * @since  3.0.0
		 * @author YITH
		 */
		public function add_boost_rule_condition_template() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['post_type'] ) ) {
				$post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
				if ( 'ywcas_boost' === $post_type ) {
					include_once YITH_WCAS_INC . 'admin/views/boost-rule/boost-rule-condition-tmpl.php';
				}
			}
		}

		/**
		 * Update the boost of a product
		 *
		 * @return void
		 * @since  3.0.0
		 * @author YITH
		 */
		public function ajax_ywcas_update_boost() {
			if ( ! isset( $_POST['newBoost'] ) || ! isset( $_POST['postID'] ) ) {
				return;
			}
			check_ajax_referer( 'ywcas-search-product', 'security' );

			$new_boost = sanitize_text_field( wp_unslash( $_POST['newBoost'] ) );// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id   = sanitize_text_field( wp_unslash( $_POST['postID'] ) );// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			YITH_WCAS_Data_Index_Lookup::get_instance()->set_boost_to_products( array( $post_id ), floatval( $new_boost ) );

			wp_send_json_success();
		}

		/**
		 * Show the custom fields
		 *
		 * @param array $field The field.
		 *
		 * @return void
		 * @since 3.0.0
		 */
		public function show_custom_field( $field ) {
			if ( isset( $field['ywcas_type'] ) ) {
				$type = $field['ywcas_type'];
				switch ( $type ) {
					case 'boost-conditions':
						$file_path = YITH_WCAS_INC . 'admin/views/custom-fields/types/boost-conditions/ywcas-' . $type . '.php';
						break;
					default:
						$file_path = YITH_WCAS_INC . 'admin/views/custom-fields/types/' . $type . '.php';
						break;
				}

				if ( file_exists( $file_path ) ) {

					include $file_path;
				}
			}
		}

		/**
		 * Create or update a Boost rule
		 *
		 * @return void
		 */
		public function save_boost_rule() {
			check_ajax_referer( 'ywcas-save-boost-rule', 'security' );
			// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			if ( isset( $_POST['boost_rule'] ) ) {
				$boost_config = $_POST['boost_rule'];
				$id           = ! empty( $boost_config['id'] ) ? intval( $boost_config['id'] ) : 0;
				unset( $boost_config['id'] );
				if ( ! isset( $boost_config['enable_for_terms'] ) ) {
					$boost_config['enable_for_terms'] = 'no';
				}
				$boost_rule = new YITH_WCAS_Boost( $id );

				$boost_rule->set_props( $boost_config );
				$boost_rule->save();
				if ( $boost_rule->get_id() > 0 ) {
					$this->remove_boost_lock( $boost_rule->get_id() );
				}
				wp_send_json_success();
			}
		}


		/**
		 * Remove the lock in the boost post
		 *
		 * @param int $boost_id The boost rule id.
		 *
		 * @return void
		 */
		public function remove_boost_lock( $boost_id ) {
			if ( false !== wp_check_post_lock( $boost_id ) ) {

				$active_lock = array_map( 'absint', get_post_meta( $boost_id, '_edit_lock', true ) );

				if ( get_current_user_id() != $active_lock[1] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					wp_die( 0 );
				}

				/**
				 * Filters the post lock window duration.
				 *
				 * @param int $interval The interval in seconds the post lock duration
				 *                      should last, plus 5 seconds. Default 150.
				 *
				 * @since 3.3.0
				 */
				$new_lock = ( time() - apply_filters( 'wp_check_post_lock_window', 150 ) + 5 ) . ':' . $active_lock[1];
				update_post_meta( $boost_id, '_edit_lock', $new_lock, implode( ':', $active_lock ) );
				wp_die( 1 );
			}
		}

		/**
		 * Return the template for edit the boost rule and lock it
		 *
		 * @return void
		 */
		public function load_boost_rule() {
			check_ajax_referer( 'ywcas-load-boost-rule-template', 'security' );

			if ( isset( $_POST['boostRuleID'] ) ) {
				$boost_id = sanitize_text_field( wp_unslash( $_POST['boostRuleID'] ) );
				if ( $boost_id > 0 && ( 'ywcas_boost' !== get_post_type( $boost_id ) || ! current_user_can( 'edit_post', $boost_id ) ) ) {
					wp_send_json_error( __( 'Can\'t edit this boost rule', 'yith-woocommerce-ajax-search' ) );
				}
				if ( $boost_id > 0 ) {
					wp_set_post_lock( $boost_id );
				}
				$boost = new YITH_WCAS_Boost( $boost_id );
				ob_start();
				$data = $boost->get_data();
				include YITH_WCAS_INC . 'admin/views/boost-rule/boost-rule-panel.php';
				$template = ob_get_clean();

				wp_send_json_success( array( 'popup' => $template ) );

			}
		}

		/**
		 * Edit active status and boost value from table
		 *
		 * @return void
		 */
		public function edit_in_line_boost_rule() {
			check_ajax_referer( 'ywcas-edit-in-line-boost-rule', 'security' );

			if ( isset( $_POST['id'], $_POST['boost'], $_POST['active'] ) ) {
				$boost_id    = sanitize_text_field( wp_unslash( $_POST['id'] ) );
				$boost_value = sanitize_text_field( wp_unslash( $_POST['boost'] ) );
				$active      = sanitize_text_field( wp_unslash( $_POST['active'] ) );
				$boost       = new YITH_WCAS_Boost( $boost_id );

				$boost->set_active( $active );
				$boost->set_boost( $boost_value );
				$boost->save();
				wp_send_json_success();
			}
		}


	}
}
