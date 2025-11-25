/**
 * Review boxes scripts
 *
 * @var jQuery
 * @var ywar_admin
 * @package YITH\AdvancedReviews\Assets\JS\Admin
 */

(
	function ( $, ywar ) {

		var saveTimeout = false,
			criteria    = [];

		function getFormDataObject( formElement ) {

			var data = {};

			for ( let field of formElement ) {
				var re        = new RegExp( '(\\[.*?\\])', 'gmi' ),
					is_array  = field.name.match( re ),
					dataKey   = is_array ? field.name.replace( re, '' ) : field.name,
					dataValue = field.value;

				if ( is_array ) {
					var currValues = data[dataKey] || [];
					currValues.push( dataValue );
					data[dataKey] = currValues;
				} else {
					data[dataKey] = dataValue;
				}
			}

			return data;
		}

		function refresh_list( init_js = true ) {
			$.ajax(
				{
					url:     window.location.href,
					success: function ( resp ) {
						if ( resp !== '' ) {
							var temp_content = $( "<div></div>" ).html( resp ),
								content      = temp_content.find( '.yith-ywar-review-boxes__list' );
							$( '.yith-ywar-review-boxes__list' ).html( content.html() );
							if ( init_js ) {
								$( document.body ).trigger( 'wc-enhanced-select-init' );
								$( document.body ).trigger( 'yith-framework-enhanced-select-init' );
								$( document.body ).trigger( 'yith_fields_init' );
								$( document.body ).trigger( 'yith-add-box-button-toggle' );
							}
						}
					}
				}
			);
		}

		$( document )
			.on(
				'change',
				'.yith-ywar-review-boxes__box__toggle-active .on_off',
				function () {

					window.onbeforeunload = null;

					var checkbox   = $( this ),
						toggle     = checkbox.closest( '.yith-ywar-review-boxes__box__toggle-active' ),
						boxWrapper = checkbox.closest( '.yith-ywar-review-boxes__box' ),
						boxKey     = boxWrapper.data( 'box-id' );

					var data = {
						box_id:  boxKey,
						request: 'switch_box_activation',
						enabled: checkbox.is( ':checked' ) ? 'yes' : 'no'
					};

					ywar.adminAjax(
						data,
						{block: toggle}
					);
				}
			)
			.on(
				'click',
				'.yith-ywar-review-boxes__box__delete-box',
				function ( e ) {
					e.preventDefault();

					var target     = $( '.yith-ywar-review-boxes__list' ),
						boxWrapper = $( this ).closest( '.yith-ywar-review-boxes__box' ),
						boxKey     = boxWrapper.data( 'box-id' );

					yith.ui.confirm(
						{
							title:             ywar_admin.modals.delete_review_box.title,
							message:           ywar_admin.modals.delete_review_box.message,
							confirmButtonType: 'delete',
							confirmButton:     ywar_admin.modals.delete_review_box.button,
							onConfirm:         function () {
								var data = {
									box_id:  boxKey,
									request: 'delete_box'
								};

								ywar
									.adminAjax(
										data,
										{block: target}
									)
									.done(
										function ( response ) {
											if ( response.success ) {
												refresh_list( false );
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
				'.yith-ywar-review-boxes__box__toggle-editing',
				function ( e ) {
					e.preventDefault();
					var target  = $( this ),
						box     = target.closest( '.yith-ywar-review-boxes__box' ),
						options = box.find( '.yith-ywar-review-boxes__box__options' );

					if ( box.is( '.yith-ywar-review-boxes__box--open' ) ) {
						box.removeClass( 'yith-ywar-review-boxes__box--open' );
						options.slideUp();
					} else {
						box.addClass( 'yith-ywar-review-boxes__box--open' );
						options.slideDown();
					}

					criteria = [];
				}
			)
			.on(
				'click',
				'.yith-ywar-review-boxes__box__save',
				function () {

					var target            = $( this ),
						boxWrapper        = target.closest( '.yith-ywar-review-boxes__box' ),
						boxKey            = boxWrapper.data( 'box-id' ),
						optionsForm       = boxWrapper.find( 'form' ),
						buttonTextElement = target.find( '.yith-ywar-review-boxes__box__save__text' ),
						saveMessage       = target.data( 'save-message' ),
						savedMessage      = target.data( 'saved-message' ),
						setSaved          = function ( saved ) {
							if ( saved ) {
								target.addClass( 'is-saved' );
								buttonTextElement.html( savedMessage );
							} else {
								target.removeClass( 'is-saved' );
								buttonTextElement.html( saveMessage );
							}
						};

					var data = {
						box_id:  boxKey,
						request: 'update_box_options',
						data:    getFormDataObject( optionsForm.serializeArray() )
					};

					setSaved( false );
					if ( saveTimeout ) {
						clearTimeout( saveTimeout );
					}

					ywar
						.adminAjax(
							data,
							{block: target}
						)
						.done(
							function ( response ) {

								setSaved( true );

								saveTimeout = setTimeout(
									function () {
										setSaved( false );
									},
									1000
								);

								if ( response.success ) {
									refresh_list();
								}
							}
						);
				}
			)
			.on(
				'click',
				'.yith-ywar-add-box',
				function () {

					var target = $( '.yith-ywar-review-boxes__list' );

					var data = {
						request: 'new_box_options'
					};

					ywar
						.adminAjax(
							data,
							{block: target}
						)
						.done(
							function ( response ) {
								if ( response.success ) {
									refresh_list();
								}
							}
						);
				}
			)
			.on(
				'click',
				'.yith-ywar-new-criteria',
				function ( e ) {
					e.preventDefault();
					var container    = $( this ).closest( '.yith-ywar-review-boxes__box' ),
						container_id = container.attr( 'id' );
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
										{block: container.find( '.multi-criteria' )}
									)
									.done(
										function ( response ) {
											if ( response.success ) {
												$( '.yith-plugin-fw__confirm__button--cancel' ).trigger( 'click' );
												criteria.push( response.data.id );

												$.ajax(
													{
														url:     window.location.href,
														success: function ( resp ) {
															if ( resp !== '' ) {
																var temp_content = $( "<div></div>" ).html( resp ),
																	content      = temp_content.find( '#' + container_id ).find( '.multi-criteria' );
																container.find( '.multi-criteria' ).html( content.html() );
																var select = container.find( '.multi-criteria' ).find( 'select' ),
																	values = select.data( 'value' );

																values = ('' === values) ? criteria.join( ',' ) : values + ',' + criteria.join( ',' );
																select.attr( 'data-value', values );

																$.each(
																	criteria,
																	function ( j, criterion ) {
																		select.find( 'option[value="' + criterion + '"]' ).attr( 'selected', 'selected' );
																	}
																);

																$( document.body ).trigger( 'wc-enhanced-select-init' );
																$( document.body ).trigger( 'yith-framework-enhanced-select-init' );
															}
														}
													}
												);
											} else {
												wrapper.append( response.data )
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
