<?php
/**
 * Main class
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR' ) ) {
	/**
	 * Implements features of YITH WooCommerce Advanced Reviews plugin
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR {

		use YITH_YWAR_Trait_Singleton;

		const TRANSIENT = 'yith-ywar-notifications';

		/**
		 * Constructor
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {
			$this->require_files();
			$this->modules(); // Modules need to be the first thing loaded, to handle Premium version correctly.
			YITH_YWAR_Install::get_instance();

			add_action( 'plugins_loaded', array( $this, 'include_privacy_text' ), 20 );

			// Register plugin to licence/update system.
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );
			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );

			YITH_YWAR_Sync::get_instance();
			YITH_YWAR_Assets::get_instance();
			YITH_YWAR_AJAX::get_instance();
			YITH_YWAR_Emails::get_instance();
			YITH_YWAR_Reports::get_instance();
			YITH_YWAR_Shortcodes::init();

			if ( $this->is_request( 'admin' ) || $this->is_request( 'rest' ) ) {
				YITH_YWAR_Admin::get_instance();
			}

			if ( $this->is_request( 'frontend' ) && wc_reviews_enabled() ) {
				YITH_YWAR_Frontend::get_instance();
				YITH_YWAR_Frontend_My_Account::get_instance();
			}

			update_option( 'woocommerce_enable_review_rating', 'yes' );
			update_option( 'woocommerce_review_rating_required', 'yes' );
		}

		/**
		 * Return the Modules class instance.
		 *
		 * @return YITH_YWAR_Modules
		 * @since  2.0.0
		 */
		public function modules(): YITH_YWAR_Modules {
			return YITH_YWAR_Modules::get_instance();
		}

		/**
		 * Load required files
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function require_files() {
			// Plugin function files.
			require_once YITH_YWAR_INCLUDES_DIR . 'functions-yith-ywar.php';
			require_once YITH_YWAR_INCLUDES_DIR . 'functions-yith-ywar-frontend.php';
			require_once YITH_YWAR_INCLUDES_DIR . 'functions-yith-ywar-reviews.php';
			require_once YITH_YWAR_INCLUDES_DIR . 'functions-yith-ywar-review-boxes.php';
			require_once YITH_YWAR_INCLUDES_DIR . 'functions-yith-ywar-errors.php';
			require_once YITH_YWAR_INCLUDES_DIR . 'functions-yith-ywar-integrations.php';

			// Plugin data stores files.
			require_once YITH_YWAR_INCLUDES_DIR . 'data-stores/class-yith-ywar-review-data-store.php';
			require_once YITH_YWAR_INCLUDES_DIR . 'data-stores/class-yith-ywar-review-box-data-store.php';
		}

		/**
		 * What type of request is this?
		 *
		 * @param string $type admin, ajax, cron or frontend.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public function is_request( string $type ): bool {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'rest':
					return wc()->is_rest_api_request();
				case 'ajax':
					return defined( 'DOING_AJAX' );
				case 'cron':
					return defined( 'DOING_CRON' );
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}

			return false;
		}

		/**
		 * Register privacy text
		 *
		 * @return  void
		 * @since   2.0.0
		 */
		public function include_privacy_text() {
			include_once YITH_YWAR_INCLUDES_DIR . 'class-yith-ywar-privacy.php';
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_YWAR_DIR . 'plugin-fw/lib/yit-licence.php';
			}

			YIT_Plugin_Licence()->register( YITH_YWAR_INIT, YITH_YWAR_SECRET_KEY, YITH_YWAR_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_YWAR_DIR . 'plugin-fw/lib/yit-upgrade.php';
			}

			YIT_Upgrade()->register( YITH_YWAR_SLUG, YITH_YWAR_INIT );
		}

		/**
		 * Declare support for WooCommerce features.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function declare_wc_features_support() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_YWAR_INIT, true );
			}
		}
	}
}
