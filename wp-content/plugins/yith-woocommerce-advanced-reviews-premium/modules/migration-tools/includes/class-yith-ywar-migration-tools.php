<?php
/**
 * Handle the Migration Tools module.
 *
 * @package YITH\AdvancedReviews\Modules\MigrationTools
 */

defined( 'YITH_YWAR' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_YWAR_Migration_Tools' ) ) {
	/**
	 * YITH_YWAR_Migration_Tools class.
	 *
	 * @since   2.1.0
	 * @author  YITH <plugins@yithemes.com>
	 * @package YITH\AdvancedReviews\Modules\MigrationTools
	 */
	class YITH_YWAR_Migration_Tools extends YITH_YWAR_Module {

		const KEY = 'migration-tools';

		/**
		 * On load.
		 *
		 * @return void
		 * @since  2.1.0
		 */
		public function on_load() {
			add_filter( 'yith_ywar_modules_admin_tabs', array( $this, 'add_settings_tab' ), 99 );
			add_filter( 'yith_ywar_print_migration_tab', array( $this, 'print_migration_tab' ), 10 );
			add_filter( 'yith_ywar_assets_globals_admin', array( $this, 'assets_globals_admin' ) );
			add_action( 'yith_ywar_run_migration_callback', array( $this, 'run_migration_callback' ) );
		}

		/**
		 * Print the migration tab
		 *
		 * @return void
		 * @since  2.1.0
		 */
		public function print_migration_tab() {

			$sections = array();
			$statuses = get_option( 'yith-ywar-migrate-status', array() );

			if ( yith_ywar_review_reminder_enabled() ) {
				$sections[] = array(
					'title'    => 'Review Reminder',
					'id'       => 'ywrr',
					'fields'   => array(
						array(
							'id'    => 'ywrr_settings',
							'title' => esc_html_x( 'Plugin settings', '[Migration tools] Option label', 'yith-woocommerce-advanced-reviews' ),
						),
						array(
							'id'    => 'ywrr_scheduled_emails',
							'title' => esc_html_x( 'Scheduled emails list table', '[Migration tools] Option label', 'yith-woocommerce-advanced-reviews' ),
						),
						array(
							'id'    => 'ywrr_blocklist',
							'title' => esc_html_x( 'Blocklist table', '[Migration tools] Option label', 'yith-woocommerce-advanced-reviews' ),
						),
					),
					'settings' => array(
						'ywrr_settings',
						'ywrr_scheduled_emails',
						'ywrr_blocklist',
					),
				);
			}

			if ( yith_ywar_review_for_discounts_enabled() ) {
				$sections[] = array(
					'title'    => 'Review for Discounts',
					'id'       => 'ywrfd',
					'fields'   => array(
						array(
							'id'    => 'ywrfd_settings',
							'title' => esc_html_x( 'Plugin settings', '[Migration tools] Option label', 'yith-woocommerce-advanced-reviews' ),
						),
						array(
							'id'    => 'ywrfd_discounts',
							'title' => esc_html_x( 'Review discounts', '[Migration tools] Option label', 'yith-woocommerce-advanced-reviews' ),
						),
					),
					'settings' => array(
						'ywrfd_settings',
						'ywrfd_discounts',
					),
				);
			}

			?>
			<div id="plugin-fw-wc" class="yith-ywar-migration-options">
				<?php
				if ( ! empty( wc()->queue()->get_next( 'yith_ywar_run_migration_callback' ) ) ) {
					$url = esc_url(
						add_query_arg(
							array(
								'page'   => 'action-scheduler',
								'status' => 'pending',
								's'      => 'yith_ywar_run_migration_callback',
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
								esc_html_x( '%1$s is migrating the options. The data may not be consistent until the process is completed. You can check the update process on %2$sthis page%3$s.', '[Migration tools] Notice displayed when the migration is scheduled', 'yith-woocommerce-advanced-reviews' ),
								'<b>' . esc_html( YITH_YWAR_PLUGIN_NAME ) . '</b>',
								'<a href="' . $url . '" target="_blank">',
								'</a>'
							),
						),
						true
					);
				}
				?>
				<?php foreach ( $sections as $section ) : ?>
					<div class="yith-plugin-fw__panel__section">
						<div class="yith-plugin-fw__panel__section__title">
							<h2><?php echo esc_html( $section['title'] ); ?></h2>
						</div>
						<div class="yith-plugin-fw__panel__section__content">
							<div class="yith-ywar-migration-block">
								<div class="title-row">
									<?php echo esc_html_x( 'Select options to migrate', '[Migration tools] Mirgation box title', 'yith-woocommerce-advanced-reviews' ); ?>:
								</div>
								<?php
								foreach ( $section['fields'] as $field ) {
									$field['name']  = $field['id'];
									$field['type']  = 'checkbox';
									$field['class'] = 'yith-ywar-migrate';
									$row_classes    = array(
										'yith-plugin-fw__panel__option',
										'yith-plugin-fw__panel__option--' . $field['type'],
									);

									if ( isset( $statuses[ $field['id'] ] ) ) {

										$status        = 'failed' !== $statuses[ $field['id'] ] && 'pending' !== $statuses[ $field['id'] ] ? 'completed' : $statuses[ $field['id'] ];
										$field         = yith_ywar_print_migration_status( $field, $status );
										$row_classes[] = "yith-ywar-migration-status $status-status";
									}

									$row_classes = implode( ' ', array_filter( $row_classes ) );

									?>
									<div class="<?php echo esc_attr( $row_classes ); ?>" <?php echo wp_kses_post( yith_field_deps_data( $field ) ); ?>>
										<?php if ( isset( $field['title'] ) && '' !== $field['title'] ) : ?>
											<div class="yith-plugin-fw__panel__option__label">
												<label for="<?php echo esc_attr( ( $field['id'] ) ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
											</div>
										<?php endif; ?>
										<div class="yith-plugin-fw__panel__option__content">
											<?php yith_plugin_fw_get_field( $field, true, true ); ?>
										</div>
										<?php if ( ! empty( $field['desc'] ) ) : ?>
											<div class="yith-plugin-fw__panel__option__description">
												<?php echo wp_kses_post( $field['desc'] ); ?>
											</div>
										<?php endif; ?>
									</div>
									<?php
								}

								$migrated = yith_ywar_check_migration_complete( $section['settings'] )

								?>
								<div class="submit-row<?php echo( $migrated ? ' migration-done' : '' ); ?>">
									<?php
									if ( $migrated ) {
										echo esc_html_x( 'All settings were migrated, you can now deactivate the plugin.', '[Migration tools] Migration status completed message', 'yith-woocommerce-advanced-reviews' )
										?>
										<input type="button" class="yith-plugin-fw__button--xl yith-plugin-fw__button--primary yith-ywar-deactivate-plugin button button-secondary" data-plugin-id="<?php echo esc_attr( $section['id'] ); ?>" value="<?php echo esc_html_x( 'Deactivate plugin', '[Migration tools] Button label', 'yith-woocommerce-advanced-reviews' ); ?>">
										<?php
									} else {
										?>
										<input type="button" class="yith-plugin-fw__button--xl yith-plugin-fw__button--primary yith-ywar-start-migration button button-secondary" value="<?php echo esc_html_x( 'Start migration', '[Migration tools] Button label', 'yith-woocommerce-advanced-reviews' ); ?>">
										<?php
									}
									?>

								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Add admin scripts.
		 *
		 * @param array  $scripts The scripts.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.1.0
		 */
		public function filter_scripts( array $scripts, string $context ): array {
			if ( 'admin' === $context ) {
				$scripts['yith-ywar-migration'] = array(
					'src'     => $this->get_url( 'assets/js/admin/admin.js' ),
					'context' => 'admin',
					'deps'    => array( 'jquery', 'yith-ywar-admin-ajax' ),
					'enqueue' => array( 'panel/migration-tools' ),
				);
			}

			return $scripts;
		}

		/**
		 * Add admin styles.
		 *
		 * @param array  $styles  The styles.
		 * @param string $context The context [admin or frontend].
		 *
		 * @return array
		 * @since  2.1.0
		 */
		public function filter_styles( array $styles, string $context ): array {
			if ( 'admin' === $context ) {
				$styles['yith-ywar-migration'] = array(
					'src'     => $this->get_url( 'assets/css/admin/admin.css' ),
					'context' => 'admin',
					'enqueue' => array( 'panel/migration-tools' ),
				);
			}

			return $styles;
		}

		/**
		 * Set globals JS vars.
		 *
		 * @param array $globals The globals JS vars.
		 *
		 * @return array
		 * @since  2.1.0
		 */
		public function assets_globals_admin( array $globals ): array {
			$globals['modals']['migrate_settings']      = array(
				'title'   => esc_html_x( 'Migrate settings', '[Admin panel] Modal title', 'yith-woocommerce-advanced-reviews' ),
				'message' => esc_html_x( 'Do you want to continue with the migration of the settings? This operation cannot be undone.', '[Admin panel] Modal message', 'yith-woocommerce-advanced-reviews' ),
				'button'  => esc_html_x( 'Yes, proceed.', '[Admin panel] Modal confirm button text', 'yith-woocommerce-advanced-reviews' ),
			);
			$globals['messages']['no_setting_selected'] = yith_plugin_fw_get_component(
				array(
					'type'        => 'notice',
					'notice_type' => 'error',
					'message'     => esc_html_x( 'You must select at least one option!', '[Migration tools] Error message', 'yith-woocommerce-advanced-reviews' ),
				),
				false
			);

			return $globals;
		}

		/**
		 * Add admin panel tabs
		 *
		 * @param array $tabs The panel tab.
		 *
		 * @return array
		 * @since  2.1.0
		 */
		public function add_settings_tab( array $tabs ): array {
			$tabs['migration-tools'] = array(
				'title'       => esc_html_x( 'Migration tools', '[Admin panel] Module name', 'yith-woocommerce-advanced-reviews' ),
				'icon'        => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 0 1-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 1 1-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 0 1 6.336-4.486l-3.276 3.276a3.004 3.004 0 0 0 2.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852Z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008Z"></path></svg>',
				'description' => esc_html_x( 'Choose which settings to migrate from old plugins.', '[Admin panel] Plugin options section description', 'yith-woocommerce-advanced-reviews' ),
			);

			return $tabs;
		}

		/**
		 * Run an update callback when triggered by ActionScheduler.
		 *
		 * @param string $callback Callback name.
		 *
		 * @return void
		 * @since  2.0.0
		 */
		public function run_migration_callback( string $callback ) {
			if ( is_callable( $callback ) ) {
				call_user_func( $callback );
			}
		}
	}
}
