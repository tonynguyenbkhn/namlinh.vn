<?php
/**
 * Class YITH_YWAR_Install
 * Installation related functions and actions.
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Install' ) ) {
	/**
	 * YITH_YWAR_Install class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Install {
		use YITH_YWAR_Trait_Singleton;

		/**
		 * The updates to fire.
		 *
		 * @var callable[][]
		 */
		private $db_updates = array(
			'2.0.0' => array(
				'yith_ywar_update_200_options',
				'yith_ywar_update_200_reviews',
				'yith_ywar_update_200_convert_reviews',
				'yith_ywar_update_200_db_version',
			),
			'2.0.3' => array(
				'yith_ywar_update_203_clear_scheduled_hooks',
			),
		);

		/**
		 * The version option.
		 */
		const VERSION_OPTION = 'yith-ywar-version';

		/**
		 * The DB version option.
		 */
		const DB_VERSION_OPTION = 'yith-ywar-db-version';

		/**
		 * The update scheduled option.
		 */
		const DB_UPDATE_SCHEDULED_OPTION = 'yith-ywar-db-update-scheduled-for';

		/**
		 * YITH_YWAR_Install constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function __construct() {
			add_action( 'init', array( $this, 'check_version' ), 5 );
			add_action( 'init', array( $this, 'create_default_review_box' ), 30 );
			add_action( 'yith_ywar_run_update_callback', array( $this, 'run_update_callback' ) );
			add_action( 'yith_ywar_convert_reviews', array( $this, 'convert_wc_reviews' ) );
			add_action( 'yith_ywar_update_reviews', array( $this, 'update_reviews' ) );
		}

		/**
		 * Check the plugin version and run the updater is required.
		 * This check is done on all requests and runs if the versions do not match.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function check_version() {
			if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( self::VERSION_OPTION, '1.0.0' ), YITH_YWAR_VERSION, '<' ) ) {
				$this->install();
				do_action( 'yith_ywar_updated' );
			}
		}

		/**
		 * Install plugin.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function install() {
			// Check if we are not already running this routine.
			if ( 'yes' === get_transient( 'yith_ywar_installing' ) ) {
				return;
			}

			set_transient( 'yith_ywar_installing', 'yes', MINUTE_IN_SECONDS * 10 );
			if ( ! defined( 'YITH_YWAR_INSTALLING' ) ) {
				define( 'YITH_YWAR_INSTALLING', true );
			}

			$this->handle_caps();
			$this->update_version();
			$this->maybe_update_db_version();

			delete_transient( 'yith_ywar_installing' );

			do_action( 'yith_ywar_installed' );
		}

		/**
		 * Handle capabilities
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function handle_caps() {
			YITH_YWAR_Post_Types::add_capabilities();
		}

		/**
		 * Update version to current.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function update_version() {
			delete_option( self::VERSION_OPTION );
			add_option( self::VERSION_OPTION, YITH_YWAR_VERSION );
		}

		/**
		 * Maybe update db
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function maybe_update_db_version() {
			if ( $this->needs_db_update() ) {
				$this->update();
			} else {
				$this->update_db_version();
			}
		}

		/**
		 * The DB needs to be updated?
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public function needs_db_update(): bool {
			$current_db_version = get_option( self::DB_VERSION_OPTION, '1.0.0' );

			return ! is_null( $current_db_version ) && version_compare( $current_db_version, $this->get_greatest_db_version_in_updates(), '<' );
		}

		/**
		 * Retrieve the major version in update callbacks.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function get_greatest_db_version_in_updates(): string {
			$update_callbacks = $this->get_db_update_callbacks();
			$update_versions  = array_keys( $update_callbacks );
			usort( $update_versions, 'version_compare' );

			return end( $update_versions );
		}

		/**
		 * Get list of DB update callbacks.
		 *
		 * @return array
		 * @since  2.0.0
		 */
		public function get_db_update_callbacks(): array {
			return $this->db_updates;
		}

		/**
		 * Update DB version to current.
		 *
		 * @param string|null $version New DB version or null.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public static function update_db_version( $version = null ) {
			delete_option( self::DB_VERSION_OPTION );
			add_option( self::DB_VERSION_OPTION, is_null( $version ) ? YITH_YWAR_VERSION : $version );

			// Delete "update scheduled for" option, to allow future update scheduling.
			delete_option( self::DB_UPDATE_SCHEDULED_OPTION );
		}

		/**
		 * Push all needed DB updates to the queue for processing.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function update() {
			$current_db_version   = get_option( self::DB_VERSION_OPTION );
			$loop                 = 0;
			$greatest_version     = $this->get_greatest_db_version_in_updates();
			$is_already_scheduled = get_option( self::DB_UPDATE_SCHEDULED_OPTION, '' ) === $greatest_version;

			if ( ! $is_already_scheduled ) {
				foreach ( $this->get_db_update_callbacks() as $version => $update_callbacks ) {
					if ( version_compare( $current_db_version, $version, '<' ) ) {
						foreach ( $update_callbacks as $update_callback ) {
							wc()->queue()->schedule_single(
								time() + $loop,
								'yith_ywar_run_update_callback',
								array(
									'update_callback' => $update_callback,
								),
								'yith-ywar-install'
							);
							++$loop;
						}
					}
				}
				update_option( self::DB_UPDATE_SCHEDULED_OPTION, $greatest_version );
			}
		}

		/**
		 * Run an update callback when triggered by ActionScheduler.
		 *
		 * @param string $callback Callback name.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function run_update_callback( string $callback ) {
			include_once YITH_YWAR_INCLUDES_DIR . 'functions-yith-ywar-update.php';

			if ( is_callable( $callback ) ) {
				self::run_update_callback_start( $callback );
				$result = (bool) call_user_func( $callback );
				self::run_update_callback_end( $callback, $result );
			}
		}

		/**
		 * Triggered when a callback will run.
		 *
		 * @param string $callback Callback name.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function run_update_callback_start( string $callback ) { //phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			if ( ! defined( 'YITH_YWAR_UPDATING' ) ) {
				define( 'YITH_YWAR_UPDATING', true );
			}
		}

		/**
		 * Triggered when a callback has ran.
		 *
		 * @param string $callback Callback name.
		 * @param bool   $result   Return value from callback. Non-false need to run again.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		protected function run_update_callback_end( string $callback, bool $result ) {
			if ( $result ) {
				wc()->queue()->add(
					'yith_ywar_run_update_callback',
					array(
						'update_callback' => $callback,
					),
					'yith-ywar-install'
				);
			}
		}

		/**
		 * Create default review box
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function create_default_review_box() {
			$default_box = get_option( 'yith-ywar-default-box-id', 0 );

			if ( 0 === $default_box || ! get_post( $default_box ) || YITH_YWAR_Post_Types::BOXES !== get_post_type( $default_box ) ) {
				$title      = esc_html_x( 'General', '[Admin panel] Default Review box name', 'yith-woocommerce-advanced-reviews' );
				$review_box = new YITH_YWAR_Review_Box();
				$review_box->set_title( $title );
				$box_id = $review_box->save();
				update_option( 'yith-ywar-default-box-id', $box_id );
			}
		}

		/**
		 * Convert WooCommerce Reviews
		 *
		 * @param array $comments List of WC Comments IDs.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function convert_wc_reviews( array $comments ) {

			$import_id = get_option( 'yith-ywar-import-id' );

			foreach ( $comments as $comment_id ) {
				$comment = get_comment( $comment_id );
				$rating  = get_comment_meta( $comment_id, 'rating', true );
				$status  = 1 === (int) $comment->comment_approved ? 'approved' : 'pending';
				$data    = array(
					'content'             => $comment->comment_content,
					'comment_id'          => $comment_id,
					'rating'              => (int) $rating,
					'product_id'          => $comment->comment_post_ID,
					'status'              => $status,
					'review_user_id'      => $comment->user_id,
					'review_author'       => $comment->comment_author,
					'review_author_email' => $comment->comment_author_email,
					'review_author_IP'    => $comment->comment_author_IP,
					'date_created'        => $comment->comment_date,
					'date_modified'       => $comment->comment_date,
				);

				$review = new YITH_YWAR_Review();

				foreach ( $data as $field => $value ) {
					$review->{"set_$field"}( $value );
				}
				$review->save();
				update_comment_meta( $comment_id, '_ywar_imported', $import_id );

				$product = wc_get_product( $comment->comment_post_ID );
				if ( $product ) {
					yith_ywar_get_review_stats( $product, true );
				}
			}
		}

		/**
		 * Update Reviews
		 *
		 * @param array $reviews List of Review IDs.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function update_reviews( array $reviews ) {
			foreach ( $reviews as $review_id ) {

				$is_approved = '1' === get_post_meta( $review_id, '_ywar_approved', true );

				wp_update_post(
					array(
						'ID'          => $review_id,
						'post_status' => $is_approved ? 'ywar-approved' : 'ywar-pending',
					)
				);

				$product = wc_get_product( get_post_meta( $review_id, '_ywar_product_id', true ) );
				if ( $product ) {
					yith_ywar_get_review_stats( $product, true );
				}
				delete_post_meta( $review_id, '_ywar_approved' );

			}
		}
	}
}
