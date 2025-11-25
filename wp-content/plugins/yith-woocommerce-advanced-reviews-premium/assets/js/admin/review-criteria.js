/**
 * Review boxes scripts
 *
 * @var jQuery
 * @var ywar_admin
 * @package YITH\AdvancedReviews\Assets\JS\Admin
 */

(
	function ( $, ywar ) {

		const term_table = '.wp-list-table';

		function refresh_list() {
			$.ajax(
				{
					url:     window.location.href,
					success: function ( resp ) {
						if ( resp !== '' ) {
							var temp_content = $( "<div></div>" ).html( resp ),
								content      = temp_content.find( term_table );
							$( term_table ).html( content.html() );
						}
					}
				}
			);
		}

		$( document )
			.on(
				'click',
				'.yith-ywar-criteria__delete',
				function ( e ) {
					var term_id = $( this ).data( 'term_id' );
					e.preventDefault();
					yith.ui.confirm(
						{
							title:             ywar_admin.modals.delete_criteria.title,
							message:           ywar_admin.modals.delete_criteria.message,
							confirmButtonType: 'delete',
							confirmButton:     ywar_admin.modals.delete_criteria.button,
							onConfirm:         function () {
								var data = {
									term_id: term_id,
									request: 'delete_criteria'
								};

								ywar
									.adminAjax(
										data,
										{block: $( term_table )}
									)
									.done(
										function () {
											refresh_list();
										}
									);
							}
						}
					);
				}
			)
			.on(
				'click',
				'.yith-ywar-criteria__edit',
				function ( e ) {
					e.preventDefault();
					var button = $( this );
					yith.ui.confirm(
						{
							title:             ywar_admin.modals.edit_criteria.title,
							message:           ywar_admin.modals.edit_criteria.content,
							confirmButton:     ywar_admin.modals.edit_criteria.button,
							closeAfterConfirm: false,
							width:             450,
							classes:           {
								wrap: 'yith-ywar-criteria-popup',
							},
							onCreate:          function () {
								var wrapper = $( '.yith-plugin-fw__panel__section__content' );
								wrapper.find( '#criterion_id' ).val( button.data( 'term_id' ) );
								wrapper.find( '#criterion_name' ).val( button.data( 'name' ) );
								wrapper.find( '#criterion_icon' ).val( button.data( 'icon' ) );
								if ( '' !== button.data( 'icon' ) ) {
									wrapper.find( '.yith-plugin-fw-media__preview__image' ).attr( 'src', button.data( 'icon_src' ) );
									wrapper.find( '.yith-plugin-fw-media__preview' ).attr( 'data-type', 'image' );
								}
							},
							onConfirm:         function () {
								var wrapper = $( '.yith-plugin-fw__panel__section__content' );
								var data    = {
									term_id: wrapper.find( '#criterion_id' ).val(),
									name:    wrapper.find( '#criterion_name' ).val(),
									icon:    wrapper.find( '#criterion_icon' ).val(),
									request: 'edit_criteria'
								};
								ywar
									.adminAjax(
										data,
										{block: $( term_table )}
									)
									.done(
										function ( response ) {
											if ( response.success ) {
												$( '.yith-plugin-fw__confirm__button--cancel' ).trigger( 'click' );
												refresh_list();
											} else {
												wrapper.prepend( response.data )
											}
										}
									);
							}
						}
					);
				}
			)
			.on(
				'click',
				'.yith-ywar-add-criteria',
				function ( e ) {
					e.preventDefault();
					yith.ui.confirm(
						{
							title:             ywar_admin.modals.add_criteria.title,
							message:           ywar_admin.modals.add_criteria.content,
							confirmButton:     ywar_admin.modals.add_criteria.button,
							closeAfterConfirm: false,
							width:             450,
							classes:           {
								wrap: 'yith-ywar-criteria-popup',
							},
							onConfirm:         function () {
								var wrapper = $( '.yith-plugin-fw__panel__section__content' );
								var data    = {
									name:    wrapper.find( '#criterion_name' ).val(),
									icon:    wrapper.find( '#criterion_icon' ).val(),
									request: 'add_criteria'
								};
								ywar
									.adminAjax(
										data,
										{block: $( term_table )}
									)
									.done(
										function ( response ) {
											if ( response.success ) {
												$( '.yith-plugin-fw__confirm__button--cancel' ).trigger( 'click' );
												refresh_list()
											} else {
												wrapper.prepend( response.data )
											}
										}
									);
							}
						}
					);
				}
			);

	}
)( jQuery, window.ywar );
