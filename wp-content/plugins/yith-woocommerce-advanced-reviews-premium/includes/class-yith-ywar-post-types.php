<?php
/**
 * Post Types handler.
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Post_Types' ) ) {
	/**
	 * YITH_YWAR_Post_Types class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Post_Types {

		const REVIEWS = 'ywar_reviews';

		const DISCOUNTS = 'ywar_discount';

		const BOXES = 'ywar_review_boxes';

		const CRITERIA_TAX = 'ywar_review_criteria';

		/**
		 * Let's init the post types, post statuses, taxonomies and data stores.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
			add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
			add_action( 'init', array( __CLASS__, 'add_criteria_taxonomy' ), 10 );
			add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_data_stores' ), 10, 1 );
			add_action( 'plugins_loaded', array( __CLASS__, 'include_admin_handlers' ), 20 );
			add_filter( 'wp_untrash_post_status', array( __CLASS__, 'untrash_post_status' ), 10, 3 );
			add_action( 'save_post', array( __CLASS__, 'save_meta_boxes' ), 1, 2 );
			add_action( 'dbx_post_sidebar', array( __CLASS__, 'add_nonce_in_edit_page' ), 10, 1 );
		}

		/**
		 * Include Admin Post Type and Taxonomy handlers.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function include_admin_handlers() {
			require_once YITH_YWAR_INCLUDES_DIR . 'abstracts/class-yith-ywar-post-type-admin.php';
			require_once YITH_YWAR_INCLUDES_DIR . 'admin/class-yith-ywar-review-post-type-admin.php';

			do_action( 'yith_ywar_admin_post_type_handlers_loaded' );
		}

		/**
		 * Register core post types.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function register_post_types() {
			if ( post_type_exists( self::REVIEWS ) && post_type_exists( self::BOXES ) ) {
				return;
			}

			do_action( 'yith_ywar_register_post_type' );

			$review_args = array(
				'labels'              => array(
					'name'               => esc_html_x( 'Reviews', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'singular_name'      => esc_html_x( 'Review', '[Global] Generic text. It refers to the single review', 'yith-woocommerce-advanced-reviews' ),
					'add_new_item'       => esc_html_x( 'Add new review', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'add_new'            => esc_html_x( 'Add review', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'new_item'           => esc_html_x( 'New review', '[Admin panel] Post type label and email title', 'yith-woocommerce-advanced-reviews' ),
					'edit_item'          => esc_html_x( 'Edit review', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'view_item'          => esc_html_x( 'View review', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'search_items'       => esc_html_x( 'Search review', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'not_found'          => esc_html_x( 'Not found', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'not_found_in_trash' => esc_html_x( 'Not found in Trash', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
				),
				'supports'            => false,
				'hierarchical'        => true,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'menu_position'       => 10,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'capability_type'     => self::REVIEWS,
				'menu_icon'           => '',
				'query_var'           => false,
				'map_meta_cap'        => true,
				'rewrite'             => false,
			);

			register_post_type( self::REVIEWS, $review_args );

			$boxes_args = array(
				'labels'              => array(
					'name'               => esc_html_x( 'Review boxes', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'singular_name'      => esc_html_x( 'Review box', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'add_new_item'       => esc_html_x( 'Add new review boxes', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'add_new'            => esc_html_x( 'Add review box', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'new_item'           => esc_html_x( 'New review box', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'edit_item'          => esc_html_x( 'Edit review box', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'view_item'          => esc_html_x( 'View review box', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'search_items'       => esc_html_x( 'Search review box', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'not_found'          => esc_html_x( 'Not found', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
					'not_found_in_trash' => esc_html_x( 'Not found in Trash', '[Admin panel] Post type label', 'yith-woocommerce-advanced-reviews' ),
				),
				'supports'            => false,
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'menu_position'       => 10,
				'show_in_nav_menus'   => false,
				'has_archive'         => true,
				'exclude_from_search' => true,
				'menu_icon'           => '',
				'capability_type'     => self::BOXES,
				'map_meta_cap'        => true,
				'rewrite'             => false,
				'publicly_queryable'  => false,
				'query_var'           => false,
			);

			register_post_type( self::BOXES, $boxes_args );
		}

		/**
		 * Register our custom post statuses, used for order status.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function register_post_status() {
			$singulars = array(
				'pending'  => _nx( 'Pending', 'Pending', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
				'reported' => _nx( 'Reported', 'Reported', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
				'approved' => _nx( 'Approved', 'Approved', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
				'spam'     => _nx( 'Spam', 'Spam', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
			);
			$plurals   = array(
				'pending'  => _nx( 'Pending', 'Pending', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
				'reported' => _nx( 'Reported', 'Reported', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
				'approved' => _nx( 'Approved', 'Approved', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
				'spam'     => _nx( 'Spam', 'Spam', 1, '[Admin panel] Review status label with plurals', 'yith-woocommerce-advanced-reviews' ),
			);

			foreach ( yith_ywar_get_review_statuses() as $status_slug => $status_label ) {
				$count    = ' <span class="count">(%s)</span>';
				$singular = $singulars[ $status_slug ] ?? $status_label;
				$plural   = $plurals[ $status_slug ] ?? $status_label;

				$singular .= $count;
				$plural   .= $count;

				$label_count = array(
					0          => $singular,
					1          => $plural,
					'singular' => $singular,
					'plural'   => $plural,
					'context'  => 'No translate',
					'domain'   => 'yith-woocommerce-advanced-reviews',
				);

				$status_slug = 'ywar-' . $status_slug;
				$options     = array(
					'label'                     => $status_label,
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => $label_count,
				);

				register_post_status( $status_slug, $options );
			}
		}

		/**
		 * Register data stores
		 *
		 * @param array $data_stores WooCommerce Data Stores.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public static function register_data_stores( array $data_stores ): array {
			$data_stores['yith-review']        = 'YITH_YWAR_Review_Data_Store';
			$data_stores['yith-review-box']    = 'YITH_YWAR_Review_Box_Data_Store';
			$data_stores['yith-ywar-discount'] = 'YITH_YWAR_Review_For_Discounts_Data_Store';

			return $data_stores;
		}

		/**
		 * Add capabilities to Admin and Shop Manager
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function add_capabilities() {
			$capability_types = array(
				self::REVIEWS         => 'post',
				self::BOXES           => 'post',
				'yith_create_review'  => 'single',
				'yith_manage_reviews' => 'single',
			);

			foreach ( $capability_types as $object_type => $type ) {
				$caps = yith_ywar_get_capabilities( $type, $object_type );
				if ( self::REVIEWS === $object_type || self::BOXES === $object_type ) {
					unset( $caps['create_posts'] );
				}

				yith_ywar_add_capabilities( $caps );
			}
		}

		/**
		 * Ensure statuses are correctly reassigned when restoring CPT.
		 *
		 * @param string $new_status      The new status of the post being restored.
		 * @param int    $post_id         The ID of the post being restored.
		 * @param string $previous_status The status of the post at the point where it was trashed.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public static function untrash_post_status( string $new_status, int $post_id, string $previous_status ): string {
			$post_types = array( self::REVIEWS, self::DISCOUNTS );

			if ( in_array( get_post_type( $post_id ), $post_types, true ) ) {
				$new_status = $previous_status;
			}

			return $new_status;
		}

		/**
		 * Check if we're saving, the trigger an action based on the post type.
		 *
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function save_meta_boxes( int $post_id, WP_Post $post ) {
			static $saved = false;

			$post_id = absint( $post_id );

			// $post_id and $post are required
			if ( empty( $post_id ) || empty( $post ) || $saved ) {
				return;
			}

			// Dont' save meta boxes for revisions or auto-saves.
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
				return;
			}

			// Check the nonce.
			if ( empty( $_POST['yith_ywar_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yith_ywar_meta_nonce'] ) ), 'yith_ywar_save_data' ) ) {
				return;
			}

			// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
			if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
				return;
			}

			// Check user has permission to edit.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// We need this save event to run once to avoid potential endless loops.
			$saved = true;

			// Check the post type.
			if ( in_array( $post->post_type, self::get_post_types(), true ) ) {
				$key = array_flip( self::get_post_types() )[ $post->post_type ];
				do_action( 'yith_ywar_post_process_' . $key . '_meta', $post_id, $post );
			}
		}

		/**
		 * Print save button in edit page.
		 *
		 * @param WP_Post $post The post.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function add_nonce_in_edit_page( WP_Post $post ) {
			if ( ! ! $post && isset( $post->post_type ) && in_array( $post->post_type, self::get_post_types(), true ) ) {
				self::meta_box_nonce_field();
			}
		}

		/**
		 * Print the meta-box nonce field for saving meta.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function meta_box_nonce_field() {
			wp_nonce_field( 'yith_ywar_save_data', 'yith_ywar_meta_nonce' );
		}

		/**
		 * Retrieve post types handled by the plugin.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public static function get_post_types(): array {

			$post_types = array(
				'reviews'      => self::REVIEWS,
				'review_boxes' => self::BOXES,
			);

			/**
			 * APPLY_FILTERS: yith_ywar_post_types
			 *
			 * Manages plugin's post types.
			 *
			 * @param array $post_types The array of post types.
			 *
			 * @return array
			 */
			return apply_filters( 'yith_ywar_post_types', $post_types );
		}

		/**
		 * Add criteria taxonomy
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function add_criteria_taxonomy() {

			$labels = array(
				'name'                       => esc_html_x( 'Criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'singular_name'              => esc_html_x( 'Criterion', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'menu_name'                  => esc_html_x( 'Criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'all_items'                  => esc_html_x( 'All criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'parent_item'                => esc_html_x( 'Parent criterion', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'parent_item_colon'          => esc_html_x( 'Parent criterion:', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'new_item_name'              => esc_html_x( 'New criterion', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'add_new_item'               => esc_html_x( 'Add new criterion', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'edit_item'                  => esc_html_x( 'Edit criterion', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'update_item'                => esc_html_x( 'Update criterion', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'view_item'                  => esc_html_x( 'View criterion', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'separate_items_with_commas' => esc_html_x( 'Separate items with commas', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'add_or_remove_items'        => esc_html_x( 'Add or remove criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'choose_from_most_used'      => esc_html_x( 'Choose from the most used criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'popular_items'              => esc_html_x( 'Popular criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'search_items'               => esc_html_x( 'Search criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'not_found'                  => esc_html_x( 'Not found', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'no_terms'                   => esc_html_x( 'No criteria', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'items_list'                 => esc_html_x( 'Criteria list', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
				'items_list_navigation'      => esc_html_x( 'Criteria list navigation', '[Admin panel] Taxonomy label', 'yith-woocommerce-advanced-reviews' ),
			);
			$args   = array(
				'labels'             => $labels,
				'hierarchical'       => false,
				'public'             => false,
				'show_ui'            => true,
				'show_admin_column'  => false,
				'show_in_nav_menus'  => false,
				'show_tagcloud'      => false,
				'show_in_rest'       => false,
				'publicly_queryable' => false,
				'capabilities'       => array(
					'edit_terms'   => 'do_not_allow',
					'delete_terms' => 'do_not_allow',
				),
			);

			register_taxonomy( self::CRITERIA_TAX, self::BOXES, $args );
		}
	}
}
