<?php
/**
 * Handle the Review For Discounts module.
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Review_For_Discounts' ) ) {
	/**
	 * YITH_YWAR_Review_For_Discounts class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts
	 */
	class YITH_YWAR_Review_For_Discounts extends YITH_YWAR_Module {

		const KEY = 'review-for-discounts';

		/**
		 * On load.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_load() {
			add_filter( 'yith_ywar_post_types', array( $this, 'add_post_type' ), 10, 1 );
			add_filter( 'yith_ywar_modules_admin_tabs', array( $this, 'add_settings_tab' ), 20 );
			add_filter( 'yith_ywar_emails', array( $this, 'add_module_emails' ), 20 );
			add_filter( 'woocommerce_email_actions', array( $this, 'add_email_actions' ) );
			add_filter( 'woocommerce_email_styles', array( $this, 'email_style' ), 1001, 2 ); // use 1000 as priority to allow support for YITH  Email Templates.
			add_action( 'yith_ywar_review_created', array( $this, 'on_review_created' ), 10 );
			add_action( 'yith_ywar_review_status_approved', array( $this, 'on_review_approved' ), 10 );
		}

		/**
		 * Custom email styles.
		 *
		 * @param string        $style WooCommerce style.
		 * @param WC_Email|null $email The current email.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function email_style( string $style, WC_Email $email = null ): string {

			if ( isset( $email ) && in_array( $email->id, array( 'yith-ywar-coupon-single-review', 'yith-ywar-coupon-multiple-review' ), true ) ) {
				ob_start();

				include yith_ywar_get_module_path( 'review-for-discounts', 'assets/css/emails.css' );
				$style .= ob_get_clean();
			}

			return $style;
		}

		/**
		 * Add the module emails.
		 *
		 * @param array $emails Other plugin emails.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_module_emails( array $emails ): array {
			$emails['YITH_YWAR_Coupon_Single_Review_Email']   = yith_ywar_get_module_path( 'review-for-discounts', 'includes/emails/class-yith-ywar-coupon-single-review-email.php' );
			$emails['YITH_YWAR_Coupon_Multiple_Review_Email'] = yith_ywar_get_module_path( 'review-for-discounts', 'includes/emails/class-yith-ywar-coupon-multiple-review-email.php' );
			$emails['YITH_YWAR_Coupon_Target_Review_Email']   = yith_ywar_get_module_path( 'review-for-discounts', 'includes/emails/class-yith-ywar-coupon-target-review-email.php' );

			if ( yith_ywar_account_funds_enabled() ) {
				$emails['YITH_YWAR_Coupon_Funds_Single_Review_Email']   = yith_ywar_get_module_path( 'review-for-discounts', 'includes/emails/class-yith-ywar-coupon-funds-single-review-email.php' );
				$emails['YITH_YWAR_Coupon_Funds_Multiple_Review_Email'] = yith_ywar_get_module_path( 'review-for-discounts', 'includes/emails/class-yith-ywar-coupon-funds-multiple-review-email.php' );
			}

			return $emails;
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

			$actions[] = 'yith_ywar_coupon_single_review';
			$actions[] = 'yith_ywar_coupon_multiple_review';
			$actions[] = 'yith_ywar_coupon_target_review';

			if ( yith_ywar_account_funds_enabled() ) {
				$actions[] = 'yith_ywar_coupon_funds_single_review';
				$actions[] = 'yith_ywar_coupon_funds_multiple_review';
			}

			return $actions;
		}

		/**
		 * Add admin scripts.
		 *
		 * @param array  $scripts The scripts.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function filter_scripts( array $scripts, string $context ): array {
			$scripts['yith-ywar-discounts'] = array(
				'src'     => $this->get_url( 'assets/js/admin/admin.js' ),
				'context' => 'admin',
				'deps'    => array( 'jquery', 'woocommerce_admin' ),
				'enqueue' => YITH_YWAR_Post_Types::DISCOUNTS,
			);

			return $scripts;
		}

		/**
		 * Add admin styles.
		 *
		 * @param array  $styles  The styles.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function filter_styles( array $styles, string $context ): array {

			$styles['yith-ywar-discounts'] = array(
				'src'     => $this->get_url( 'assets/css/admin/admin.css' ),
				'context' => 'admin',
				'enqueue' => array( 'edit-' . YITH_YWAR_Post_Types::DISCOUNTS, YITH_YWAR_Post_Types::DISCOUNTS ),
			);

			return $styles;
		}

		/**
		 * Add admin panel tabs
		 *
		 * @param array $tabs The panel tab.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_settings_tab( array $tabs ): array {
			$tabs['review-for-discounts'] = array(
				'title'       => 'Review for discounts',
				'icon'        => '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>',
				'description' => esc_html_x( 'Enable the following modules to unlock additional features for your reviews.', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' ),
			);

			return $tabs;
		}

		/**
		 * On activate.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_activate() {
			$caps = yith_ywar_get_capabilities( 'post', YITH_YWAR_Post_Types::DISCOUNTS );
			yith_ywar_add_capabilities( $caps );
		}

		/**
		 * On deactivate.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_deactivate() {
			$caps = yith_ywar_get_capabilities( 'post', YITH_YWAR_Post_Types::DISCOUNTS );
			yith_ywar_remove_capabilities( $caps );
		}

		/**
		 * On register post types.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_register_post_types() {

			$args = array(
				'labels'              => array(
					'name'               => esc_html_x( 'Coupons', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'singular_name'      => esc_html_x( 'Coupon', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'add_new_item'       => esc_html_x( 'Add new coupon', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'add_new'            => esc_html_x( 'Add coupon', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'new_item'           => esc_html_x( 'New coupon', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'edit_item'          => esc_html_x( 'Edit coupon', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'view_item'          => esc_html_x( 'View coupon', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'search_items'       => esc_html_x( 'Search coupon', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'not_found'          => esc_html_x( 'Not found', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'not_found_in_trash' => esc_html_x( 'Not found in Trash', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
				),
				'supports'            => array( 'title' ),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'menu_position'       => 10,
				'show_in_nav_menus'   => false,
				'has_archive'         => true,
				'exclude_from_search' => true,
				'menu_icon'           => '',
				'capability_type'     => YITH_YWAR_Post_Types::DISCOUNTS,
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'publicly_queryable'  => false,
				'query_var'           => false,
			);

			register_post_type( YITH_YWAR_Post_Types::DISCOUNTS, $args );
		}

		/**
		 * On post type handlers loaded.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_post_type_handlers_loaded() {
			require_once $this->get_path( 'includes/admin/class-yith-ywar-review-for-discounts-post-type-admin.php' );
		}

		/**
		 * Add post type to allow handling meta-box saving through data stores.
		 *
		 * @param array $post_types Post types.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function add_post_type( array $post_types ): array {
			$post_types['discounts'] = YITH_YWAR_Post_Types::DISCOUNTS;

			return $post_types;
		}

		/**
		 * On review created.
		 *
		 * @param YITH_YWAR_Review $review The review.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_review_created( YITH_YWAR_Review $review ) {
			if ( ! yith_ywar_is_moderation_required() ) {

				$product_id       = $review->get_product_id();
				$user_email       = $review->get_review_author_email();
				$already_approved = 'yes' === $review->get_meta( '_ywar_got_discount' );

				if ( ! yith_ywar_user_has_commented( $product_id, $user_email, true, 1 ) && ! $already_approved ) {
					$this->get_coupons( $review );
				}
			}
		}

		/**
		 * On review approved.
		 *
		 * @param YITH_YWAR_Review $review The review.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function on_review_approved( YITH_YWAR_Review $review ) {
			if ( yith_ywar_is_moderation_required() ) {

				$product_id       = $review->get_product_id();
				$user_email       = $review->get_review_author_email();
				$already_approved = 'yes' === $review->get_meta( '_ywar_got_discount' );

				if ( ! yith_ywar_user_has_commented( $product_id, $user_email, true, 1 ) && ! $already_approved ) {
					$this->get_coupons( $review );
				}
			}
		}

		/**
		 * Check if a specific email (or type of emails) is enabled.
		 *
		 * @param string $type    The main type of the email (single/multiple review).
		 * @param string $subtype The additonal type of email (coupon/funds).
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		private function enabled_email( string $type, string $subtype = '' ): bool {

			$emails = array(
				'single'   => array( 'coupon' => 'YITH_YWAR_Coupon_Single_Review_Email' ),
				'multiple' => array( 'coupon' => 'YITH_YWAR_Coupon_Multiple_Review_Email' ),
				'target'   => array( 'coupon' => 'YITH_YWAR_Coupon_Target_Review_Email' ),
			);

			if ( yith_ywar_account_funds_enabled() ) {
				$emails['single']['funds']   = 'YITH_YWAR_Coupon_Funds_Single_Review_Email';
				$emails['multiple']['funds'] = 'YITH_YWAR_Coupon_Funds_Multiple_Review_Email';
			}

			if ( '' === $subtype ) {
				foreach ( $emails[ $type ] as $key => $classname ) {
					if ( ! wc()->mailer()->emails[ $classname ]->is_enabled() ) {
						return false;
					}
				}

				return true;
			} else {
				return wc()->mailer()->emails[ $emails[ $type ][ $subtype ] ]->is_enabled();
			}
		}

		/**
		 * Get coupons to be sent
		 *
		 * @param YITH_YWAR_Review $review The comment placed by the customer.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function get_coupons( YITH_YWAR_Review $review ): void {

			$product_id = $review->get_product_id();
			$user_id    = $review->get_review_user_id();
			$user_mail  = $review->get_review_author_email();
			$nickname   = str_replace( ' ', '-', ( ( $user_id ) ? get_user_meta( $user_id, 'nickname', true ) : $review->get_review_author() ) );

			if ( $this->enabled_email( 'single' ) ) {
				$discounts = $this->get_discounts_single( $product_id );

				if ( ! empty( $discounts ) ) {
					foreach ( $discounts as $discount ) {
						switch ( $discount->get_discount_type() ) {
							case 'funds':
								if ( is_user_logged_in() && yith_ywar_account_funds_enabled() && $this->enabled_email( 'single', 'funds' ) ) {
									yith_ywar_give_funds( $discount, $user_id, 'single', $product_id, $user_mail );
								}
								break;
							default:
								if ( $this->enabled_email( 'single', 'coupon' ) ) {
									$coupon_code = yith_ywar_create_coupon( $discount, $nickname, $user_mail );
									$args        = array(
										'user'        => array(
											'customer_name'      => $user_id ? wp_get_current_user()->get( 'billing_first_name' ) : $nickname,
											'customer_last_name' => $user_id ? wp_get_current_user()->get( 'billing_last_name' ) : $nickname,
											'customer_email'     => $user_mail,
										),
										'product_id'  => $product_id,
										'coupon_code' => $coupon_code,
									);

									do_action( 'yith_ywar_coupon_single_review', $args );
								}
						}
					}
				}
			}

			if ( $this->enabled_email( 'multiple' ) ) {
				$count     = $this->count_reviews( $user_mail );
				$discounts = $this->get_discounts_multiple( $count );

				if ( ! empty( $discounts ) ) {
					foreach ( $discounts as $discount ) {
						switch ( $discount->get_discount_type() ) {
							case 'funds':
								if ( is_user_logged_in() && yith_ywar_account_funds_enabled() && $this->enabled_email( 'multiple', 'funds' ) ) {
									yith_ywar_give_funds( $discount, $user_id, 'multiple', $count, $user_mail );
								}
								break;
							default:
								if ( $this->enabled_email( 'multiple', 'coupon' ) ) {
									$coupon_code = yith_ywar_create_coupon( $discount, $nickname, $user_mail );
									$args        = array(
										'user'          => array(
											'customer_name'      => $user_id ? wp_get_current_user()->get( 'billing_first_name' ) : $nickname,
											'customer_last_name' => $user_id ? wp_get_current_user()->get( 'billing_last_name' ) : $nickname,
											'customer_email'     => $user_mail,
										),
										'total_reviews' => $count,
										'coupon_code'   => $coupon_code,
									);

									do_action( 'yith_ywar_coupon_multiple_review', $args );
								}
						}
					}
				}

				$notifications = $this->get_discounts_notifications( $count );

				if ( ! empty( $notifications ) ) {
					foreach ( $notifications as $target_reviews ) {
						$args = array(
							'user'              => array(
								'customer_name'      => $user_id ? wp_get_current_user()->get( 'billing_first_name' ) : $nickname,
								'customer_last_name' => $user_id ? wp_get_current_user()->get( 'billing_last_name' ) : $nickname,
								'customer_email'     => $user_mail,
							),
							'remaining_reviews' => $target_reviews - $count,
						);

						do_action( 'yith_ywar_coupon_target_review', $args );
					}
				}
			}

			$review->add_meta_data( '_ywar_got_discount', 'yes' );
			$review->save();
		}

		/**
		 * Count approved Reviews
		 *
		 * @param string $author_email The email of the comment author.
		 *
		 * @return int
		 * @since  2.0.0
		 */
		private function count_reviews( string $author_email ): int {

			global $wpdb;

			$post_type = YITH_YWAR_Post_Types::REVIEWS;
			$approved  = yith_ywar_is_moderation_required() ? "'ywar-approved'" : implode( ',', array( "'ywar-pending'", "'ywar-reported'", "'ywar-approved'", "'ywar-spam'" ) );
			$sql       = "
							SELECT
								meta_value
							FROM  $wpdb->postmeta
							WHERE post_id IN ( 
								SELECT
								    $wpdb->posts.ID
								FROM $wpdb->posts  INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
								WHERE $wpdb->posts.post_parent = 0
								  AND $wpdb->postmeta.meta_key = '_ywar_review_author_email'
								  AND $wpdb->postmeta.meta_value = '$author_email'
								  AND $wpdb->posts.post_type = '$post_type'
								  AND $wpdb->posts.post_status IN ( $approved )
								GROUP BY $wpdb->posts.ID
								)
							  AND $wpdb->postmeta.meta_key = '_ywar_product_id'   
							GROUP BY $wpdb->postmeta.meta_value
                    ";
			$reviews   = $wpdb->get_col( $sql ); //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$count     = count( $reviews );

			return $count;
		}

		/**
		 * Get valid discounts for single review.
		 *
		 * @param int $product_id The reviewed product ID.
		 *
		 * @return YITH_YWAR_Review_For_Discounts_Discount[]
		 * @since  2.0.0
		 */
		private function get_discounts_single( int $product_id ): array {

			$valid_discounts = array();
			$discounts       = get_posts(
				array(
					'post_type'   => YITH_YWAR_Post_Types::DISCOUNTS,
					'post_status' => 'publish',
					'numberposts' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_key'    => YITH_YWAR_Post_Types::DISCOUNTS . '_trigger',
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'meta_value'  => 'single',
					'fields'      => 'ids',
				)
			);

			if ( ! empty( $discounts ) ) {
				foreach ( $discounts as $discount_id ) {
					$valid    = false;
					$discount = yith_ywar_get_discount( $discount_id );

					if ( empty( $discount->get_trigger_product_ids() ) && empty( $discount->get_trigger_product_categories() ) ) {
						$valid = true;
					} elseif ( ! empty( $discount->get_trigger_product_ids() ) && in_array( $product_id, $discount->get_trigger_product_ids(), true ) ) {
						$valid = true;
					} elseif ( ! empty( $discount->get_trigger_product_categories() ) ) {
						$categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
						if ( $categories ) {
							foreach ( $categories as $category_id ) {
								if ( in_array( (int) $category_id, $discount->get_trigger_product_categories(), true ) ) {
									$valid = true;
								}
							}
						}
					}

					if ( $valid ) {
						$valid_discounts[] = $discount;
					}
				}
			}

			return $valid_discounts;
		}

		/**
		 * Get valid discounts for multiple review.
		 *
		 * @param int $count The reviews count.
		 *
		 * @return YITH_YWAR_Review_For_Discounts_Discount[]
		 * @since  2.0.0
		 */
		private function get_discounts_multiple( int $count ): array {

			$valid_discounts = array();
			$discounts       = get_posts(
				array(
					'post_type'   => YITH_YWAR_Post_Types::DISCOUNTS,
					'post_status' => 'publish',
					'numberposts' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'  => array(
						'relation' => 'AND',
						array(
							'key'   => YITH_YWAR_Post_Types::DISCOUNTS . '_trigger',
							'value' => 'multiple',
						),
						array(
							'key'   => YITH_YWAR_Post_Types::DISCOUNTS . '_trigger_threshold',
							'value' => $count,
						),
					),
					'fields'      => 'ids',
				)
			);

			if ( ! empty( $discounts ) ) {
				foreach ( $discounts as $discount_id ) {
					$valid_discounts[] = yith_ywar_get_discount( $discount_id );
				}
			}

			return $valid_discounts;
		}

		/**
		 * Get valid discounts threshold approaching notification.
		 *
		 * @param int $count The reviews count.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		private function get_discounts_notifications( int $count ): array {

			$valid_discounts = array();
			$discounts       = get_posts(
				array(
					'post_type'   => YITH_YWAR_Post_Types::DISCOUNTS,
					'post_status' => 'publish',
					'numberposts' => -1,
					//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'meta_query'  => array(
						'relation' => 'AND',
						array(
							'key'   => YITH_YWAR_Post_Types::DISCOUNTS . '_trigger',
							'value' => 'multiple',
						),
						array(
							'key'   => YITH_YWAR_Post_Types::DISCOUNTS . '_trigger_enable_notify',
							'value' => 'yes',
						),
						array(
							'key'     => YITH_YWAR_Post_Types::DISCOUNTS . '_trigger_threshold_notify',
							'value'   => $count,
							'compare' => '<=',
							'type'    => 'NUMERIC',
						),
						array(
							'key'     => YITH_YWAR_Post_Types::DISCOUNTS . '_trigger_threshold',
							'value'   => $count,
							'compare' => '>',
							'type'    => 'NUMERIC',
						),
					),
					'fields'      => 'ids',
				)
			);

			if ( ! empty( $discounts ) ) {
				foreach ( $discounts as $discount_id ) {
					$valid_discounts[] = yith_ywar_get_discount( $discount_id )->get_trigger_threshold();
				}
			}

			return array_unique( $valid_discounts );
		}
	}
}
