<?php
/**
 * Class YITH_YWAR_Migration_Tools_AJAX
 *
 * @package YITH\AdvancedReviews\Modules\MigrationTools
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Migration_Tools_AJAX' ) ) {
	/**
	 * Class YITH_YWAR_Migration_Tools_AJAX
	 *
	 * @since   2.1.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\MigrationTools
	 */
	class YITH_YWAR_Migration_Tools_AJAX {

		use YITH_YWAR_Trait_Singleton;

		/**
		 * YITH_YWAR_Migration_Tools_AJAX constructor.
		 *
		 * @return void
		 * @since  2.1.0
		 */
		protected function __construct() {
			add_action( 'yith_ywar_admin_ajax_migrate_settings', array( $this, 'ajax_migrate_settings' ) );
			add_action( 'yith_ywar_admin_ajax_deactivate_plugin', array( $this, 'ajax_deactivate_plugin' ) );
		}

		/**
		 * Initialize settings migration
		 *
		 * @return void
		 * @throws Exception An exception.
		 * @since  2.1.0
		 */
		public function ajax_migrate_settings() {

			isset( $_POST['migrate_settings'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			try {
				if ( empty( $_POST['migrate_settings'] ) ) {
					throw new Exception( esc_html_x( 'You must select at least one option!', '[Migration tools] Error message', 'yith-woocommerce-advanced-reviews' ) );
				}
				$schedule_gap = 0;
				$status       = array();
				foreach ( wp_unslash( $_POST['migrate_settings'] ) as $settings ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					wc()->queue()->schedule_single(
						time() + $schedule_gap,
						'yith_ywar_run_migration_callback',
						array(
							'update_callback' => "yith_ywar_migrate_$settings",
						),
						'yith-ywar-migrate'
					);
					$schedule_gap += ( MINUTE_IN_SECONDS * 3 );

					$status[ $settings ] = 'pending';

					yith_ywar_update_migration_status( $settings, 'pending' );
				}

				wp_send_json_success();
			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $e->getMessage(),
						),
						false
					)
				);
			}
		}

		/**
		 * Disable a plugin after the migration
		 *
		 * @return void
		 * @throws Exception An exception.
		 * @since  2.1.0
		 */
		public function ajax_deactivate_plugin() {

			isset( $_POST['plugin_id'] ) && ! empty( $_POST['plugin_id'] ) && check_ajax_referer( YITH_YWAR_AJAX::ADMIN_AJAX_ACTION, 'security' );

			try {
				$plugin_prefix = strtoupper( sanitize_text_field( wp_unslash( $_POST['plugin_id'] ) ) );
				deactivate_plugins( constant( "{$plugin_prefix}_INIT" ), false, is_network_admin() );
				wp_send_json_success();
			} catch ( Exception $e ) {
				yith_ywar_error( $e->getMessage() );
				wp_send_json_error(
					yith_plugin_fw_get_component(
						array(
							'type'        => 'notice',
							'notice_type' => 'error',
							'message'     => $e->getMessage(),
						),
						false
					)
				);
			}
		}
	}
}
