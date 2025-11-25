<?php
/**
 * Handle a single module.
 *
 * @package YITH\AdvancedReviews
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Module' ) ) {
	/**
	 * YITH_YWAR_Module class.
	 *
	 * @since   2.0.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews
	 */
	abstract class YITH_YWAR_Module {
		use YITH_YWAR_Trait_Multiple_Singleton;

		const KEY = '';

		/**
		 * YITH_YWAR_Module constructor.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function __construct() {
			if ( empty( static::KEY ) ) {
				$error = sprintf( 'Error: The class "%s" must define the constant KEY.', get_called_class() );
				yith_ywar_error( $error );
				wp_die( esc_html( $error ) );
			}

			$this->maybe_add_action( 'yith_ywar_loaded', array( $this, 'on_load' ), 0 );
			$this->maybe_add_action( "yith_ywar_modules_module_{$this->get_key()}_activated", array( $this, 'on_activate' ) );
			$this->maybe_add_action( "yith_ywar_modules_module_{$this->get_key()}_deactivated", array( $this, 'on_deactivate' ) );
			$this->maybe_add_action( 'yith_ywar_register_post_type', array( $this, 'on_register_post_types' ) );
			$this->maybe_add_action( 'yith_ywar_admin_post_type_handlers_loaded', array( $this, 'on_post_type_handlers_loaded' ) );
			$this->maybe_add_filter( 'yith_ywar_styles', array( $this, 'filter_styles' ), 10, 2 );
			$this->maybe_add_filter( 'yith_ywar_scripts', array( $this, 'filter_scripts' ), 10, 2 );
		}

		/**
		 * Maybe add action if the callback exists.
		 *
		 * @param string $action        The action.
		 * @param array  $callback      The callback.
		 * @param int    $priority      Optional. The priority. Default 10.
		 * @param int    $accepted_args Optional. The number of arguments the function accepts. Default 1.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function maybe_add_action( string $action, array $callback, int $priority = 10, int $accepted_args = 1 ) {
			is_callable( $callback ) && add_action( $action, $callback, $priority, $accepted_args );
		}

		/**
		 * Maybe add filter if the callback exists.
		 *
		 * @param string $filter        The filter.
		 * @param array  $callback      The callback.
		 * @param int    $priority      Optional. The priority. Default 10.
		 * @param int    $accepted_args Optional. The number of arguments the function accepts. Default 1.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		private function maybe_add_filter( string $filter, array $callback, int $priority = 10, int $accepted_args = 1 ) {
			is_callable( $callback ) && add_filter( $filter, $callback, $priority, $accepted_args );
		}

		/**
		 * Get the key.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		public function get_key(): string {
			return static::KEY;
		}

		/**
		 * Get the active option.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		protected function get_active_option(): string {
			return YITH_YWAR_Modules::get_module_active_option( $this->get_key() );
		}

		/**
		 * Get a path related to the module.
		 *
		 * @param string $path The path.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		protected function get_path( string $path = '' ): string {
			return YITH_YWAR_Modules::get_module_path( $this->get_key(), $path );
		}

		/**
		 * Get a URL related to the module.
		 *
		 * @param string $url The URL.
		 *
		 * @return string
		 * @since  2.0.0
		 */
		protected function get_url( string $url = '' ): string {
			return YITH_YWAR_Modules::get_module_url( $this->get_key(), $url );
		}
	}
}
