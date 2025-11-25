/**
 * Email scripts
 *
 * @var jQuery
 * @var ywar_admin
 * @package YITH\AdvancedReviews\Assets\JS\Admin
 */

(
	function ( $, ywar ) {

		var DEFAULT_CONTENT_SHOWN_CLASS = 'yith-ywar-email-field__row--empty-value',
			INITIAL_TEXT_EDITOR_HEIGHT  = 300,
			saveTimeout                 = false;

		function getFormDataObject( formElement ) {

			var data = {};

			for ( let field of formElement ) {
				var re        = new RegExp( '(\\[.*?\\])', 'gmi' ),
					is_array  = field.name.match( re ),
					dataKey   = is_array && is_array[0] ? field.name.replace( re, '' ) : field.name,
					dataValue = field.value;

				if ( is_array ) {
					var subKey         = is_array[0].replace( '[', '' ).replace( ']', '' );
					var currValues     = data[dataKey] || {};
					currValues[subKey] = dataValue;
					data[dataKey]      = currValues;
				} else {
					data[dataKey] = dataValue;
				}
			}

			return data;
		}

		function syncEditors() {
			if ( 'tinyMCE' in window && 'triggerSave' in window.tinyMCE ) {
				window.tinyMCE.triggerSave();
			}
		}

		$( document )
			.on(
				'change',
				'.yith-ywar-emails__email__toggle-active .on_off',
				function () {

					window.onbeforeunload = null;

					var checkbox     = $( this ),
						toggle       = checkbox.closest( '.yith-ywar-emails__email__toggle-active' ),
						emailWrapper = checkbox.closest( '.yith-ywar-emails__email' ),
						emailKey     = emailWrapper.data( 'email' );

					var data = {
						email:   emailKey,
						request: 'switch_email_activation',
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
				'.yith-ywar-emails__email__toggle-editing',
				function ( e ) {
					e.preventDefault();
					var target    = $( this ),
						email     = target.closest( '.yith-ywar-emails__email' ),
						options   = email.find( '.yith-ywar-emails__email__options' ),
						textAreas = email.find( 'textarea.wp-editor-area' );

					if ( textAreas.length ) {
						textAreas.each(
							function () {
								var id = $( this ).attr( 'id' );
								if ( 'tinymce' in window ) {
									var editor = window.tinymce.get( id );
									if ( editor ) {
										editor.theme.resizeTo( undefined, INITIAL_TEXT_EDITOR_HEIGHT );
									}
								}
							}
						);
					}

					if ( email.is( '.yith-ywar-emails__email--open' ) ) {
						email.removeClass( 'yith-ywar-emails__email--open' );
						options.slideUp();
					} else {
						email.addClass( 'yith-ywar-emails__email--open' );
						options.slideDown();
						options.find( '.email_style_selector' ).trigger( 'change' )
					}
				}
			)
			.on(
				'click',
				'.yith-ywar-emails__email__save',
				function () {

					syncEditors();

					var target            = $( this ),
						emailWrapper      = target.closest( '.yith-ywar-emails__email' ),
						emailKey          = emailWrapper.data( 'email' ),
						optionsForm       = emailWrapper.find( 'form' ),
						buttonTextElement = target.find( '.yith-ywar-emails__email__save__text' ),
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
						email:   emailKey,
						request: 'update_email_options',
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
								if ( response.success ) {
									emailWrapper.find( '.email_style_selector' ).trigger( 'change' );
								}

								setSaved( true );

								saveTimeout = setTimeout(
									function () {
										setSaved( false );
									},
									1000
								);
							}
						);
				}
			)
			.on(
				'click',
				'.yith-ywar-email-field__edit',
				function ( e ) {
					e.preventDefault();
					var row            = $( this ).closest( '.yith-ywar-email-field__row' ),
						defaultContent = row.data( 'default-content' ),
						textArea       = row.find( 'textarea.wp-editor-area' ),
						id             = textArea.attr( 'id' );

					if ( 'tinymce' in window ) {
						var editor = window.tinymce.get( id );
						if ( editor ) {
							editor.theme.resizeTo( undefined, INITIAL_TEXT_EDITOR_HEIGHT );
							editor.setContent( defaultContent.replace( /\r?\n/g, '<br />' ) );
						} else {
							textArea.val( defaultContent );
						}
					}

					row.removeClass( DEFAULT_CONTENT_SHOWN_CLASS );
				}
			)
			.on(
				'click',
				'.yith-ywar-email-field__use-default',
				function () {
					var row      = $( this ).closest( '.yith-ywar-email-field__row' ),
						textArea = row.find( 'textarea.wp-editor-area' ),
						id       = textArea.attr( 'id' );

					if ( 'tinymce' in window ) {
						var editor = window.tinymce.get( id );
						if ( editor ) {
							editor.setContent( '' );
						} else {
							textArea.val( '' );
						}
					}

					row.addClass( DEFAULT_CONTENT_SHOWN_CLASS );
				}
			)
			.on(
				'click',
				'.yith-ywar-test-email',
				function () {

					var container = $( this ).closest( '.yith-plugin-fw-text-button-field-wrapper' ),
						email     = container.find( '.yith-plugin-fw-text-input' ).val(),
						type      = $( this ).data( 'type' ),
						re        = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

					container
						.find( '.yith-ywar-send-result' )
						.remove();

					container.append( '<div class="yith-ywar-send-result"></div>' );

					if ( ! re.test( email ) ) {

						container
							.find( '.yith-ywar-send-result' )
							.addClass( 'send-fail' )
							.html( ywar_admin.messages.test_mail_wrong );

					} else {

						var data = {
							request: 'send_test_mail',
							email:   email,
							type:    type
						};

						ywar
							.adminAjax(
								data,
								{block: container}
							)
							.done(
								function ( response ) {
									container
										.find( '.yith-ywar-send-result' )
										.removeClass( 'send-progress' )
										.addClass( response.success === true ? 'send-success' : 'send-fail' )
										.html( response.success === true ? ywar_admin.messages.after_send_test_email : ywar_admin.messages.test_mail_error );
								}
							);

					}

				}
			)
			.on(
				'change',
				'.email_style_selector',
				function () {
					var emailWrapper = $( this ).closest( '.yith-ywar-emails__email' ),
						textarea     = emailWrapper.find( 'textarea.wp-editor-area' ),
						id           = textarea.attr( 'id' ),
						value        = $( this ).is( ':disabled' ) ? 'base' : $( this ).val(),
						body;

					if ( emailWrapper.is( '.yith-ywar-emails__email--open' ) ) {
						if ( 'tinymce' in window ) {
							var editor = window.tinymce.get( id );
							if ( editor ) {
								body = editor.getContent();
							} else {
								body = textarea.val();
							}
						}

						if ( '' === body ) {
							body = textarea.closest( '.yith-ywar-email-field__row' ).data( 'default-content' )
						}
						if ( ! $( this ).is( ':disabled' ) ) {
							if ( 'base' === value ) {
								$( this ).parent().parent().next( '.yith-plugin-fw__panel__option__description' ).show()
							} else {
								$( this ).parent().parent().next( '.yith-plugin-fw__panel__option__description' ).hide()
							}
						}

						update_preview( $( this ), value, body );
					}

				}
			)
			.trigger( 'change' )
			.on(
				'input',
				'.header-colors .wp-color-picker',
				function () {
					$( ywar.get_preview_to_update( $( this ) ) + ' #template_header' ).css( $( this ).data( 'prop' ), $( this ).val() )
				}
			)
			.on(
				'input',
				'.body-colors .wp-color-picker',
				function () {
					switch ( $( this ).data( 'prop' ) ) {
						case 'link_color':
							$( ywar.get_preview_to_update( $( this ) ) + ' a:not(.review-button)' ).css( 'color', $( this ).val() );
							break;
						case 'link_color_hover':
							var hover_color  = $( this ).val(),
								normal_color = $( this ).closest( '.yith-colorpicker-group' ).find( 'input[data-prop="link_color"]' ).val();

							$( ywar.get_preview_to_update( $( this ) ) + ' a:not(.review-button)' ).each(
								function ( index, element ) {

									$( element )
										.off( 'mouseenter mouseleave' )
										.on(
											'mouseenter mouseleave',
											function ( e ) {
												$( this ).attr(
													'style',
													function ( i, s ) {
														var regex = new RegExp( 'color\: [0-9\#a-fgr\,\(\)\;]*', 'gmi' ),
															style = s.replace( ' !important', '' );

														return style.replace( regex, '' ) + 'color: ' + (e.type === 'mouseenter' ? hover_color : normal_color) + ' !important;';
													}
												);
											}
										);
								}
							);

							break;
						case 'background-color':
							$( ywar.get_preview_to_update( $( this ) ) + ' #outer_wrapper' ).css( $( this ).data( 'prop' ), $( this ).val() );
							break;
						default:
							$( ywar.get_preview_to_update( $( this ) ) + ' #body_content_inner' ).css( $( this ).data( 'prop' ), $( this ).val() )
					}
				}
			)
			.on(
				'tinymce-editor-setup',
				function ( event, editor ) {
					editor.on(
						'KeyUp',
						function ( e ) {
							var element      = $( '#' + editor.id ),
								emailWrapper = element.closest( '.yith-ywar-emails__email' ),
								style        = emailWrapper.find( '.email_style_selector' ).val();
							update_preview( element, style, e.target.innerHTML, $( ywar.get_preview_to_update( element ) + ' .mail-heading' ).val() );

						}
					);
				}
			)
			.on(
				'input change',
				'.upload-logo input',
				function () {
					var image   = $( this ).val(),
						style   = $( '.email_style_selector' ).val(),
						content = '',
						align   = $( this ).closest( '.yith-ywar-emails__email' ).find( '.yith-ywar-email-field__row.' + style + ' .logo-position' ).val();

					if ( '' !== image ) {
						content = '<p style="margin-top:0; padding: 0 0 16px 0; text-align: ' + align + '"><img src="' + image + '" alt="" /></p>';
					}
					$( ywar.get_preview_to_update( $( this ) ) + ' #template_header_image' ).html( content );

				}
			)
			.on(
				'change',
				'.logo-position',
				function () {
					var align = $( this ).val(),
						style = $( '.email_style_selector' ).val(),
						image = $( this ).closest( '.yith-ywar-emails__email' ).find( '.yith-ywar-email-field__row.' + style + ' .upload-logo input' ).val();

					if ( '' !== image ) {
						$( ywar.get_preview_to_update( $( this ) ) + ' #template_header_image' ).html( '<p style="margin-top:0; padding: 0 0 16px 0; text-align: ' + align + '"><img src="' + image + '" alt="" /></p>' );
					}
				}
			)
			.on(
				'mousemove',
				'.wp-picker-holder',
				function () {
					$( this ).parent().find( '.wp-color-picker' ).trigger( 'input' );
				}
			)
			.on(
				'keyup input',
				'.mail-heading',
				function () {

					var emailWrapper = $( this ).closest( '.yith-ywar-emails__email' ),
						textarea     = emailWrapper.find( 'textarea.wp-editor-area' ),
						style        = emailWrapper.find( '.email_style_selector' ).val(),
						id           = textarea.attr( 'id' ),
						text         = $( this ).val(),
						def          = $( this ).attr( 'placeholder' ),
						body;

					if ( 'tinymce' in window ) {
						var editor = window.tinymce.get( id );
						if ( editor ) {
							body = editor.getContent();
						} else {
							body = textarea.val();
						}
					}

					if ( '' === body ) {
						body = textarea.closest( '.yith-ywar-email-field__row' ).data( 'default-content' )
					}

					update_preview( $( this ), style, body, ('' !== text ? text : def) );

				}
			)
			.on(
				'input',
				'.button-colors .wp-color-picker',
				function () {
					var container       = $( this ).closest( '.yith-colorpicker-group' ),
						normal_color    = container.find( 'input[data-prop="color"]' ).val(),
						normal_bg_color = container.find( 'input[data-prop="background-color"]' ).val(),
						hover_color     = container.find( 'input[data-prop="hover_text"]' ).val(),
						hover_bg_color  = container.find( 'input[data-prop="hover_bg"]' ).val();

					$( ywar.get_preview_to_update( $( this ) ) + ' .review-button' )
						.css( 'color', normal_color )
						.css( 'background-color', normal_bg_color )
						.each(
							function ( index, element ) {
								$( element )
									.off( 'mouseenter mouseleave' )
									.on(
										'mouseenter mouseleave',
										function ( e ) {
											$( this ).attr(
												'style',
												function ( i, s ) {
													var regexBg = new RegExp( 'background-color\: [0-9\#a-fgr\,\(\)\;]*', 'gmi' ),
														regex   = new RegExp( 'color\: [0-9\#a-fgr\,\(\)\;]*', 'gmi' ),
														style   = s.replace( ' !important', '' );

													return style.replace( regexBg, '' ).replace( regex, '' ) + 'background-color: ' + (e.type === 'mouseenter' ? hover_bg_color : normal_bg_color) + ' !important; color: ' + (e.type === 'mouseenter' ? hover_color : normal_color) + ' !important;';
												}
											);
										}
									);
							}
						);

				}
			)
			.on(
				'keyup input',
				'.button-text',
				function () {
					var text = $( this ).val(),
						def  = $( this ).attr( 'placeholder' );

					$( ywar.get_preview_to_update( $( this ) ) + ' .review-button' ).html( ('' !== text ? text : def) );

				}
			)
			.on(
				'keyup input',
				'.unsubscribe-text',
				function () {
					var text = $( this ).val(),
						def  = $( this ).attr( 'placeholder' );

					$( ywar.get_preview_to_update( $( this ) ) + ' .yith-ywar-unsubscribe-link a' ).html( ('' !== text ? text : def) );

				}
			)
			.on(
				'yith-ywar-email-customizer',
				function () {
					$( '.button-colors .wp-color-picker' ).trigger( 'input' );
					$( '.button-text' ).trigger( 'input' );
					$( '.unsubscribe-text' ).trigger( 'input' );
				}
			);

		function update_preview( element, style, body, heading ) {
			var emailWrapper = element.closest( '.yith-ywar-emails__email' ),
				emailKey     = emailWrapper.data( 'email' ),
				emailId      = ywar.get_preview_to_update( element );

			ywar
				.adminAjax(
					{
						email:   emailKey,
						request: 'reload_email_preview',
						style:   style,
						body:    body,
						heading: heading
					},
					{block: $( emailId )}
				)
				.done(
					function ( response ) {
						$( emailId ).html( response.data );
						if ( style !== 'base' ) {
							emailWrapper.find( '.yith-ywar-email-field__row.' + style + ' .header-colors .wp-color-picker' ).trigger( 'input' );
							emailWrapper.find( '.yith-ywar-email-field__row.' + style + ' .body-colors .wp-color-picker' ).trigger( 'input' );
							emailWrapper.find( '.yith-ywar-email-field__row.' + style + ' .logo-position' ).trigger( 'change' );
						}
						$( document ).trigger( 'yith-ywar-email-customizer' );
					}
				);
		}

		ywar.get_preview_to_update = function ( option ) {
			return '.' + option.closest( '.yith-ywar-emails__email' ).data( 'email-id' )
		}
	}
)( jQuery, window.ywar );
