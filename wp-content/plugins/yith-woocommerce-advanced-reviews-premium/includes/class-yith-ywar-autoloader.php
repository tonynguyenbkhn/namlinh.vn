<?php
/**
 * Autoloader.
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Autoloader' ) ) {
	/**
	 * Autoloader class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	class YITH_YWAR_Autoloader {

		/**
		 * The Constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function __construct() {

			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Take a class name and turn it into a file name.
		 *
		 * @param string $class_name Class name.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		private function get_file_name_from_class( string $class_name ): string {
			$filename = '';
			$base     = str_replace( '_', '-', $class_name );

			if ( false !== strpos( $class_name, 'interface' ) ) {
				$filename = 'interface-' . $base . '.php';
			} elseif ( false !== strpos( $class_name, 'trait' ) ) {
				$base     = str_replace( '-trait', '', $base );
				$filename = 'trait-' . $base . '.php';
			}

			if ( empty( $filename ) ) {
				$filename = 'class-' . $base . '.php';
			}

			return $filename;
		}

		/**
		 * Include a class file.
		 *
		 * @param string $path File path.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		private function load_file( string $path ): bool {
			if ( $path && is_readable( $path ) ) {
				include_once $path;

				return true;
			}

			return false;
		}

		/**
		 * Auto-load plugins' classes on demand to reduce memory consumption.
		 *
		 * @param string $class_name Class name.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function autoload( string $class_name ) {
			$class_name = strtolower( $class_name );

			if ( 0 !== strpos( $class_name, 'yith_ywar' ) ) {
				return;
			}

			$file         = $this->get_file_name_from_class( $class_name );
			$include_path = YITH_YWAR_INCLUDES_DIR;

			if ( false !== strpos( $class_name, 'request_review' ) ) {
				$include_path = YITH_YWAR_MODULES_PATH . 'request-review/includes/';
			} elseif ( false !== strpos( $class_name, 'review_for_discounts' ) ) {
				$include_path = YITH_YWAR_MODULES_PATH . 'review-for-discounts/includes/';
			} elseif ( false !== strpos( $class_name, 'migration_tools' ) ) {
				$include_path = YITH_YWAR_MODULES_PATH . 'migration-tools/includes/';
			}

			$path = '';
			if ( false !== strpos( $class_name, 'trait' ) ) {
				$path = $include_path . 'traits/';
			} elseif ( false !== strpos( $class_name, 'frontend' ) ) {
				$path = $include_path . 'frontend/';
			} elseif ( false !== strpos( $class_name, 'integration' ) ) {
				$path = $include_path . 'integrations/';
			} elseif ( false !== strpos( $class_name, 'admin_table' ) ) {
				$path = $include_path . 'admin/admin-tables/';
			} elseif ( false !== strpos( $class_name, 'admin' ) ) {
				$path = $include_path . 'admin/';
			} elseif ( false !== strpos( $class_name, 'data' ) ) {
				$path = $include_path . 'data/';
			} elseif ( false !== strpos( $class_name, 'data_store' ) ) {
				$path = $include_path . 'data-stores/';
			} elseif ( false !== strpos( $class_name, '_blocks' ) ) {
				$path = $include_path . 'wc-blocks/';
			}

			if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
				$this->load_file( $include_path . $file );
			}
		}
	}
}

new YITH_YWAR_Autoloader();
