/**
 * Orders page scripts
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Assets\JS\Admin
 */

(
	function ( $ ) {
		$( document )
			.on(
				'click',
				'.yith-ywar-send-box',
				function () {
					$( this ).find( 'a.yith-ywar-schedule-actions' ).trigger( 'click' );
					return false;
				}
			)
			.on(
				'click',
				'.yith-ywar-schedule-delete',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var schedule_id  = $( this ).data( 'schedule-id' ),
						container    = $( this ).parent(),
						container_id = container.prop( 'id' );

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
											id:      schedule_id
										},
										{block: container}
									)
									.done(
										function ( response ) {
											if ( response.success ) {
												$.ajax(
													{
														url:     window.location.href,
														success: function ( resp ) {
															if ( resp !== '' ) {
																var temp_content = $( "<div></div>" ).html( resp ),
																	content      = temp_content.find( '#' + container_id );
																$( '#' + container_id ).html( content.html() );
															}
														}
													}
												);
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
				'.yith-ywar-schedule-actions, .yith-ywar-button-schedule',
				function ( e ) {
					e.preventDefault();
					e.stopPropagation();

					var schedule_id    = $( this ).data( 'schedule-id' ),
						scheduled_date = $( this ).data( 'schedule-date' ),
						object_id      = $( this ).data( 'object-id' ),
						object_type    = $( this ).data( 'object-type' ),
						label          = $( this ).data( 'additional-label' ),
						container      = $( this ).parent(),
						container_id   = container.prop( 'id' );

					yith.ui.confirm(
						{
							title:         ywar_admin.modals.schedule_new_email.title,
							message:       ywar_admin.modals.schedule_new_email.content,
							confirmButton: ywar_admin.modals.schedule_new_email.button,
							width:         600,
							classes:       {
								wrap: 'yith-ywar-actions-popup',
							},
							onCreate:      function () {
								$( 'input[name^=send_single_request]' ).on(
									'change',
									function () {
										if ( $( this ).is( ':checked' ) && 'now' === $( this ).val() ) {
											$( '.yith-plugin-fw-radio__row' ).find( '.datepicker-wrapper' ).hide()
										} else {
											$( '.yith-plugin-fw-radio__row' ).find( '.datepicker-wrapper' ).show()
										}
									}
								);
								$( '#schedule_date' ).val( scheduled_date );
								$( '.yith-plugin-fw-radio__row' ).find( 'small' ).html( label );
								$( document ).trigger( 'yith_fields_init' );
							},
							onConfirm:     function () {
								var data               = {},
									action_type        = $( 'input[name^=send_single_request]:checked' ).val(),
									date_field         = $( '#schedule_date' ),
									new_scheduled_date = date_field.val();

								date_field
									.parent()
									.removeClass( 'is_required' )
									.find( 'span' )
									.remove();

								if ( action_type === 'now' ) {
									data = {
										request:     'send_request_mail',
										schedule_id: schedule_id,
										object_id:   object_id,
										object_type: object_type,
									};
								} else {
									if ( new_scheduled_date === '' ) {
										date_field
											.parent()
											.addClass( 'is_required' )
											.append( '<span>' + ywar_admin.messages.missing_date_error + '</span>' );
										throw new ywar.Error( ywar_admin.messages.missing_date_error );
									} else {
										if ( schedule_id !== 0 ) {
											data = {
												request:       'reschedule_single_email',
												schedule_id:   schedule_id,
												schedule_date: new_scheduled_date
											};
										} else {
											data = {
												request:       'schedule_single_email',
												object_id:     object_id,
												object_type:   object_type,
												schedule_date: new_scheduled_date
											};
										}
									}
								}

								ywar
									.adminAjax(
										data,
										{block: container}
									)
									.done(
										function ( response ) {
											if ( response.success ) {
												$.ajax(
													{
														url:     window.location.href,
														success: function ( resp ) {
															if ( resp !== '' ) {
																var temp_content = $( "<div></div>" ).html( resp ),
																	content      = temp_content.find( '#' + container_id );
																$( '#' + container_id ).html( content.html() );
															}
														}
													}
												);
											}
										}
									);
							}
						}
					);
				}
			)
			.trigger( 'yith_fields_init' );

		var bulk_selectors = $( '#bulk-action-selector-top, #bulk-action-selector-bottom' );
		bulk_selectors.append( '<option value="yith_ywar_send">' + ywar_admin.bulk_actions.send_label + '</option>' );
		bulk_selectors.append( '<option value="yith_ywar_reschedule">' + ywar_admin.bulk_actions.reschedule_label + '</option>' );
		bulk_selectors.append( '<option value="yith_ywar_cancel">' + ywar_admin.bulk_actions.cancel_label + '</option>' );

	}
)( jQuery );
