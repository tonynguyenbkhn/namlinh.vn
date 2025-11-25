/**
 * Admin JS scripts
 *
 * @package YITH\AdvancedReviews\Modules\MigrationTools\Assets\JS\Admin
 */

(
	function ( $, ywar ) {
		const container = $( '.yith-ywar-migration-options' );

		$( document )
			.on(
				'click',
				'.yith-ywar-start-migration',
				function () {
					let migrate_settings = [];
					const block          = $( this ).closest( '.yith-ywar-migration-block' );

					container.find( '.yith-plugin-fw__notice' ).remove();
					block.find( '.yith-ywar-migrate' ).each(
						function () {
							if ( $( this ).is( ':checked' ) && ! $( this ).is( ':disabled' ) ) {
								migrate_settings.push( $( this ).attr( 'id' ) );
							}
						}
					);

					if ( migrate_settings.length > 0 ) {
						yith.ui.confirm(
							{
								title:         ywar_admin.modals['migrate_settings'].title,
								message:       ywar_admin.modals['migrate_settings'].message,
								confirmButton: ywar_admin.modals['migrate_settings'].button,
								onConfirm:     function () {
									ywar
										.adminAjax(
											{
												migrate_settings: migrate_settings,
												request:          'migrate_settings'
											},
											{block: container}
										)
										.done(
											function ( response ) {
												var ajax_message = response.data;
												if ( response.success ) {
													$.ajax(
														{
															url:     window.location.href,
															success: function ( resp ) {
																var content = $( resp ).find( '.yith-ywar-migration-block' ).html();
																$( block ).html( '' ).html( content );
															}
														}
													);
												} else {
													$( container ).prepend( ajax_message )
												}
											}
										);
								}
							}
						);
					} else {
						$( container ).prepend( ywar_admin.messages.no_setting_selected )

					}
				}
			)
			.on(
				'click',
				'.yith-ywar-deactivate-plugin',
				function () {
					container.block( ywar_admin.blockParams );
					ywar
						.adminAjax(
							{
								plugin_id: $( this ).data( 'plugin-id' ),
								request:   'deactivate_plugin'
							},
						)
						.done(
							function ( response ) {
								var ajax_message = response.data;
								if ( response.success ) {
									window.location.reload();
								} else {
									$( container ).prepend( ajax_message )
								}
							}
						);
				}
			);

	}
)( jQuery, window.ywar );
