/**
 * Admin JS scripts
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Assets\JS\Admin
 */

(
	function ( $ ) {
		$( document )
			.on(
				'change',
				'#ywar_mail_reschedule-send-immediately',
				function () {
					if ( $( this ).is( ':checked' ) ) {
						$( '#ywar_mail_reschedule-reschedule' ).prop( 'checked', true );
					}
				}
			)
			.on(
				'change',
				'#ywar_mail_reschedule-reschedule',
				function () {
					if ( ! $( this ).is( ':checked' ) ) {
						$( '#ywar_mail_reschedule-send-immediately' ).prop( 'checked', false );
					}
				}
			)
			.on(
				'change',
				'#ywar_mail_schedule_day, input[name^=ywar_request_type], #ywar_request_criteria, #ywar_request_number',
				function () {

					var box = $( '.yith-ywar-reschedule-emails' );

					if ( ! box.hasClass( 'visible' ) ) {
						box.css( 'display', 'flex' ).hide().addClass( 'visible' ).fadeIn();
					}

				}
			)
			.on(
				'click',
				'.yith-ywar-bulk-button',
				function () {

					var container = $( this ).closest( '.yith-plugin-fw-list-table-container' ),
						action    = $( this ).data( 'action' );
					container.find( '.yith-plugin-fw__notice' ).remove();

					yith.ui.confirm(
						{
							title:             ywar_admin.modals[action].title,
							message:           ywar_admin.modals[action].message,
							confirmButtonType: 'delete',
							confirmButton:     ywar_admin.modals[action].button,
							onConfirm:         function () {
								ywar
									.adminAjax(
										{
											request: action
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
															var content = $( resp ).find( '.yith-plugin-fw-list-table-container' ).html();
															$( container ).html( '' ).html( content ).prepend( ajax_message );
															refresh_select_boxes();
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
				}
			)
			.on(
				'click',
				'.action__set_cancelled',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var id        = $( this ).data( 'id' ),
						container = $( this ).closest( '.yith-plugin-fw-list-table-container' );

					yith.ui.confirm(
						{
							title:             ywar_admin.modals.set_cancelled.title,
							message:           ywar_admin.modals.set_cancelled.message,
							confirmButtonType: 'delete',
							confirmButton:     ywar_admin.modals.set_cancelled.button,
							onConfirm:         function () {
								ywar
									.adminAjax(
										{
											request: 'set_email_cancelled',
											id:      id
										},
										{block: container}
									)
									.done(
										function () {
											window.location.reload();
										}
									);
							}
						}
					);
				}
			)
			.on(
				'click',
				'.yith-ywar-add-emails',
				function () {
					yith.ui.confirm(
						{
							title:             ywar_admin.modals.schedule_new_emails.title,
							message:           ywar_admin.modals.schedule_new_emails.content,
							confirmButton:     ywar_admin.modals.schedule_new_emails.button,
							width:             600,
							closeAfterConfirm: false,
							classes:           {
								wrap:    'yith-ywar-list-table-popup',
								confirm: 'yith-plugin-fw__button--xxl'
							},
							onCreate:          function () {
								$( 'input[name^=schedule_request]' ).on(
									'change',
									function () {
										if ( $( this ).is( ':checked' ) && 'all' === $( this ).val() ) {
											$( '.yith-plugin-fw-radio__row' ).find( '.yith-plugin-fw-field-wrapper' ).hide()
										} else {
											$( '.yith-plugin-fw-radio__row' ).find( '.yith-plugin-fw-field-wrapper' ).show()
										}
									}
								);

								// Date picker fields.
								function date_picker_select( datepicker ) {
									var this_field_id  = datepicker.attr( 'id' ),
										option         = this_field_id === 'date_range_start_date' ? 'minDate' : 'maxDate',
										other_field_id = '#' + ('maxDate' === option ? 'date_range_start_date' : 'date_range_end_date'),
										date           = datepicker.datepicker( 'getDate' );

									$( other_field_id ).datepicker( 'option', option, date );
									$( datepicker ).trigger( 'change' );
								}

								$.datepicker.setDefaults(
									{
										onSelect: function () {
											date_picker_select( $( this ) );
										}
									}
								);

								$( '.yith-plugin-fw-datepicker' ).each(
									function () {
										date_picker_select( $( this ) );
									}
								);

								$( document ).trigger( 'yith_fields_init' );

							},
							onConfirm:         function () {
								var container     = $( '.yith-plugin-fw-list-table-container' ),
									schedule_type = $( 'input[name^=schedule_request]:checked' ).val(),
									start_date    = false,
									end_date      = false;

								if ( 'all' !== schedule_type ) {
									start_date = $( '#date_range_start_date' ).val() || false;
									end_date   = $( '#date_range_end_date' ).val() || false;
								}
								container.find( '.yith-plugin-fw__notice' ).remove();

								ywar
									.adminAjax(
										{
											request:       'mass_schedule',
											schedule_type: schedule_type,
											start_date:    start_date,
											end_date:      end_date
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
															var content = $( resp ).find( '.yith-plugin-fw-list-table-container' ).html();
															$( container ).html( '' ).html( content ).prepend( ajax_message );
															$( '.yith-plugin-fw-panel-custom-tab-title .yith-plugin-fw__button--primary' ).addClass( 'visible' );
															$( '.yith-plugin-fw__confirm__button--cancel' ).trigger( 'click' );
															refresh_select_boxes();
														}
													}
												);
											} else {
												var popup = $( '.yith-plugin-fw__confirm__content' );
												popup.find( '.yith-plugin-fw__notice' ).remove();
												popup.prepend( response.data );
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
				'.action__delete',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var id        = $( this ).data( 'id' ),
						container = $( this ).closest( '.yith-plugin-fw-list-table-container' );

					yith.ui.confirm(
						{
							title:             ywar_admin.modals.delete_from_blocklist.title,
							message:           ywar_admin.modals.delete_from_blocklist.message,
							confirmButtonType: 'delete',
							confirmButton:     ywar_admin.modals.delete_from_blocklist.button,
							onConfirm:         function () {
								ywar
									.adminAjax(
										{
											request: 'delete_from_blocklist',
											id:      id
										},
										{block: container}
									)
									.done(
										function () {
											window.location.reload();
										}
									);
							}
						}
					);
				}
			)
			.on(
				'click',
				'.yith-ywar-add-to-blocklist',
				function () {
					yith.ui.confirm(
						{
							title:             ywar_admin.modals.add_to_blocklist.title,
							message:           ywar_admin.modals.add_to_blocklist.content,
							confirmButton:     ywar_admin.modals.add_to_blocklist.button,
							closeAfterConfirm: false,
							width:             450,
							classes:           {
								wrap:    'yith-ywar-list-table-popup',
								confirm: 'yith-plugin-fw__button--xxl'
							},
							onConfirm:         function () {
								var container = $( '.yith-plugin-fw-list-table-container' ),
									wrapper   = $( '.yith-plugin-fw-text-field-wrapper' ),
									email     = $( '#add_to_blocklist' ).val(),
									re        = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

								container.find( '.yith-plugin-fw__notice' ).remove();
								wrapper
									.find( '.yith-ywar-mail-error' )
									.remove();

								if ( ! re.test( email ) ) {
									wrapper.append( '<div class="yith-ywar-mail-error">' + ywar_admin.messages.test_mail_wrong + '</div>' );
									throw new ywar.Error( ywar_admin.messages.test_mail_wrong );
								} else {
									wrapper
										.find( '.yith-ywar-mail-error' )
										.remove();

									ywar
										.adminAjax(
											{
												request: 'add_to_blocklist',
												email:   email
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
																var content = $( resp ).find( '.yith-plugin-fw-list-table-container' ).html();
																$( container ).html( '' ).html( content ).prepend( ajax_message );
																$( '.yith-plugin-fw-panel-custom-tab-title .yith-plugin-fw__button--primary' ).addClass( 'visible' );
																$( '.yith-plugin-fw__confirm__button--cancel' ).trigger( 'click' );
																refresh_select_boxes();
															}
														}
													);
												} else {
													var popup = $( '.yith-plugin-fw__confirm__content' );
													popup.find( '.yith-plugin-fw__notice' ).remove();
													popup.prepend( response.data );
												}
											}
										);
								}
							}
						}
					);
				}
			)
			.trigger( 'yith-plugin-fw-tips-init' );

		function refresh_select_boxes() {
			$( '#bulk-action-selector-top, #bulk-action-selector-bottom' ).select2(
				{
					minimumResultsForSearch: Infinity
				}
			);
			$( document.body ).trigger( 'wc-enhanced-select-init' );
			$( document.body ).trigger( 'yith-framework-enhanced-select-init' );
		}
	}
)( jQuery );
