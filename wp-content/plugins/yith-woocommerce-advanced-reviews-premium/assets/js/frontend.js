/**
 * Frontend review scripts
 *
 * @var jQuery
 * @var ywar_frontend
 * @package YITH\AdvancedReviews\Assets\JS
 */

(
	function ( $, ywar ) {

		const image_mime_types = [
			'image/jpeg',
			'image/gif',
			'image/png',
			'image/webp',
		];

		const video_mime_types = [
			'video/mp4',
			'video/ogg',
			'video/webm',
			'video/x-m4v',
			'video/x-flv',
		];

		var ywar_init                        = false,
			sb_instance,
			file_uploads                     = {form: '', files: [], type_count: {image: 0, video: 0}},
			load_reviews                     = function ( self, callback = null ) {
				var review_id = 0;
				if ( window.location.hash.indexOf( '#review-' ) !== -1 ) {
					$( 'body' )
						.find( 'li.reviews_tab a' )
						.trigger( 'click' );

					review_id = window.location.hash.replace( '#review-', '' );
				}
				ywar
					.ajax(
						{
							review_id:  review_id,
							product_id: self.element.data( 'product-id' ),
							box_id:     self.element.data( 'review-box' ),
							request:    'load_reviews',
							page:       self.args.page,
							rating:     self.args.rating,
							sorting:    self.args.sorting,
							helpful:    self.args.helpful,
							popup:      self.popup ? 'yes' : 'no'
						},
						{block: self.element}
					)
					.done(
						function ( response ) {
							if ( callback ) {
								callback( response, self );
							}
						}
					);
			},
			validate_form_fields             = function ( self, fields, action ) {
				var form_has_error = false;
				$( '*' ).removeClass( 'has-error' );
				if ( typeof fields.rating_field !== "undefined" ) {
					if ( fields.rating_field.length > 1 ) {
						fields.rating_field.each(
							function () {
								// Check if rating is zero.
								if ( 0 === parseInt( $( this ).val() ) ) {
									$( this ).parent().parent().addClass( 'has-error' ).attr( 'data-message', ywar_frontend.messages['required_rating'] );
									$( this ).parent().prev().addClass( 'has-error' );
									$( 'html, body' )
										.animate(
											{
												scrollTop: $( this ).parent().parent().offset().top - ywar_frontend.scroll_offset
											},
											200
										);

									form_has_error = true;
								}
							}
						);
					} else {
						// Check if rating is zero.
						if ( 0 === parseInt( fields.rating_field.val() ) ) {
							form_validation_error( fields.rating_field, 'required_rating', form_has_error );
							form_has_error = true;
						}
					}
				}

				if ( 0 === fields.user_id && 'insert' === action ) {
					// Check if username is empty.
					if ( '' === fields.user_name.val() ) {
						form_validation_error( fields.user_name, 'required_field', form_has_error );
						form_has_error = true;
					}

					// Check if email is empty.
					if ( '' === fields.user_email.val() ) {
						form_validation_error( fields.user_email, 'required_field', form_has_error );
						form_has_error = true;
					}

					/* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
					var pattern = new RegExp( /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i );

					if ( ! pattern.test( fields.user_email.val() ) ) {
						form_validation_error( fields.user_email, 'mail_wrong', form_has_error );
						form_has_error = true;
					}
				}

				// Check if content is empty.
				if ( '' === fields.content.val() ) {
					form_validation_error( fields.content, 'required_field', form_has_error );
					form_has_error = true;
				}

				if ( ywar_frontend.use_recaptcha && 'v2' === ywar_frontend.recaptcha_version && ('off' === fields.recaptcha_response || '' === fields.recaptcha_response) ) {
					form_validation_error( fields.recaptcha_wrapper, 'required_captcha', form_has_error );
					form_has_error = true;
				}

				return form_has_error;

			},
			form_validation_error            = function ( element, message, form_has_error ) {
				element.parent().addClass( 'has-error' ).attr( 'data-message', ywar_frontend.messages[message] );

				if ( 'required_rating' === message ) {
					element.parent().prev().addClass( 'has-error' );
				}

				if ( ! form_has_error ) {
					$( 'html, body' )
						.animate(
							{
								scrollTop: element.parent().offset().top - ywar_frontend.scroll_offset
							},
							200
						);
				}
			},
			like_review                      = function ( button ) {
				var review_wrapper = $( '.review-' + button.data( 'review-id' ) );
				// Manage review like.
				ywar
					.ajax(
						{
							review_id: button.data( 'review-id' ),
							request:   'like_review',
							user_id:   button.data( 'user-id' ),
						},
						{block: button}
					)
					.done(
						function ( response ) {
							if ( response.data.selected ) {
								review_wrapper.find( '.helpful-button' ).addClass( 'selected' );
							} else {
								review_wrapper.find( '.helpful-button' ).removeClass( 'selected' );
							}
							review_wrapper.find( '.helpful-count' ).html( response.data.message )
						}
					);
			},
			report_review                    = function ( button ) {
				var review_wrapper = $( '.review-' + button.data( 'review-id' ) );

				// Manage review report.
				ywar
					.ajax(
						{
							request:   'report_review',
							review_id: button.data( 'review-id' ),
							user_id:   button.data( 'user-id' ),
						},
						{block: button}
					)
					.done(
						function ( response ) {
							var wrapper = button.closest( '.review-actions' );
							wrapper.find( '.reported-message' ).remove();
							if ( response.data.selected ) {
								review_wrapper.find( '.report-button' ).addClass( 'selected' );
							} else {
								review_wrapper.find( '.report-button' ).removeClass( 'selected' );
							}
							if ( response.data.message ) {
								wrapper.append( '<span class="reported-message">' + response.data.message + '</span>' )
							}
						}
					);
			},
			delete_review                    = function ( button ) {
				var review_wrapper = $( '.yith-ywar-single-review.review-' + button.data( 'review-id' ) ),
					context        = 'default';

				switch ( true ) {
					case review_wrapper.hasClass( 'in-shortcode' ):
						context = 'shortcode';
						break;
					case review_wrapper.hasClass( 'in-popup' ):
						context = 'popup';
						break;
				}

				// Manage review delete.
				ywar
					.ajax(
						{
							review_id:      button.data( 'review-id' ),
							request:        'delete_review',
							button_context: context,
						},
						{block: button}
					)
					.done(
						function ( response ) {
							if ( response.data.message ) {
								review_wrapper
									.after( response.data.message )
									.remove();
							}
						}
					);
			},
			open_reply_review                = function ( self, button ) {
				// Manage review reply.
				var review_id   = button.data( 'review-id' ),
					in_reply_of = button.data( 'reply-to' ),
					container   = self.element.find( '#review-' + review_id );
				ywar
					.ajax(
						{
							review_id:   review_id,
							in_reply_of: in_reply_of,
							box_id:      self.element.data( 'review-box' ),
							request:     'reply_review',
						},
						{block: container}
					)
					.done(
						function ( response ) {
							if ( response.data ) {
								self.element.find( '.yith-ywar-edit-forms:not(.new-review)' ).remove();

								if ( self.element.find( '.replies-review-' + review_id ).length === 0 ) {
									container.after( '<div class="yith-ywar-replies-wrapper replies-review-' + review_id + '"></div>' )
								}
								self.element.find( '.replies-review-' + review_id ).append( response.data );

								$( 'html, body' )
									.animate(
										{
											scrollTop: self.element.find( '#yith-ywar-new-reply-' + review_id ).offset().top - ywar_frontend.scroll_offset
										},
										200
									);
								if ( ywar_frontend.use_recaptcha ) {
									var recaptcha = $( '#yith-ywar-new-reply-' + review_id ).find( '.g-recaptcha' );
									if ( typeof grecaptcha !== "undefined" && recaptcha.length > 0 ) {
										ywar.captcha_edit = grecaptcha.render( 'yith-ywar-recaptcha-new-reply-' + review_id, {'sitekey': recaptcha.data( 'sitekey' )} );
									}
								}
							}
						}
					);
			},
			open_edit_review                 = function ( self, review_id, type ) {
				// Manage review editing.
				var container = self.element.find( '#review-' + review_id );
				ywar
					.ajax(
						{
							review_id: review_id,
							box_id:    self.element.data( 'review-box' ),
							request:   'edit_review',
						},
						{block: container}
					)
					.done(
						function ( response ) {
							if ( response.data ) {
								self.element.find( '.yith-ywar-edit-forms:not(.new-review)' ).remove();
								container.append( response.data );
								if ( ywar_frontend.use_recaptcha ) {
									var recaptcha = $( '#yith-ywar-edit-' + type + '-' + review_id ).find( '.g-recaptcha' );
									if ( typeof grecaptcha !== "undefined" && recaptcha.length > 0 ) {
										ywar.captcha_edit = grecaptcha.render( 'yith-ywar-recaptcha-edit-' + type + '-' + review_id, {'sitekey': recaptcha.data( 'sitekey' )} );
									}
								}
							}
						}
					);
			},
			init_swiper                      = function ( slide, type ) {
				var loop           = true,
					disabled_class = 'swiper-button-disabled',
					lock_class     = 'swiper-button-lock';

				var thumb_swiper = new ywar.Swiper(
					'.thumbs-gallery-' + type,
					{
						spaceBetween:        10,
						slidesPerView:       'auto',
						freeMode:            true,
						watchSlidesProgress: true,
					}
				);
				var swiper       = new ywar.Swiper(
					'.swiper-gallery-' + type,
					{
						on:           {
							init:              function ( swiper ) {
								var current_slide    = $( swiper.slides[swiper.activeIndex] ),
									review_id        = current_slide.data( 'review-id' ),
									review_container = $( '#gallery-review-' + review_id );

								if ( slide === swiper.activeIndex && current_slide.find( '.wp-video-shortcode' ).length > 0 ) {
									current_slide.find( '.wp-video-shortcode' ).get( 0 ).play();
								}

								$( '.review-data' ).addClass( 'inactive-review' );
								review_container.removeClass( 'inactive-review' );
							},
							activeIndexChange: function ( swiper ) {
								var current_slide    = $( swiper.slides[swiper.activeIndex] ),
									review_id        = current_slide.data( 'review-id' ),
									review_container = $( '#gallery-review-' + review_id );

								if ( current_slide.find( '.wp-video-shortcode' ).length > 0 ) {
									current_slide.find( '.wp-video-shortcode' ).get( 0 ).play();
								} else {
									$( '.wp-video-shortcode' ).each(
										function () {
											var video = $( this ).get( 0 );
											video.pause();
											video.currentTime = 0;
										}
									);
								}

								$( '.review-data' ).addClass( 'inactive-review' );
								review_container.removeClass( 'inactive-review' );
							}
						},
						spaceBetween: 0,
						autoHeight:   false,
						loop:         loop,
						navigation:   {
							nextEl:        '.swiper-button-next',
							prevEl:        '.swiper-button-prev',
							disabledClass: disabled_class,
							lockClass:     lock_class,
						},
						thumbs:       {
							swiper: thumb_swiper,
						}
					}
				);

				swiper.slideTo( slide )

			},
			init_buttons                     = function ( self ) {
				var wrapper_id = `#${self.element.attr( 'id' )}`;
				$( document )
					.on(
						'click',
						wrapper_id + ' .action-buttons',
						function () {
							var button = $( this );
							switch ( button.data( 'action' ) ) {
								case 'like':
									like_review( button );
									break;
								case 'report':
									report_review( button );
									break;
								case 'delete':
									delete_review( button );
									break;
								default:
									open_reply_review( self, button );
							}
						}
					)
					.on(
						'click',
						wrapper_id + ' .edit-button',
						function () {
							open_edit_review( self, $( this ).data( 'review-id' ), $( this ).data( 'type' ) );
						}
					)
					.on(
						'click',
						wrapper_id + ' .form-review-rating .stars span',
						function () {
							var star      = $( this ),
								rating    = $( this ).closest( '.rating-wrapper' ).find( '.rating-value' ),
								container = $( this ).closest( '.stars' );

							rating.val( star.data( 'value' ) );
							star.siblings( 'span' ).removeClass( 'active' );
							star.addClass( 'active' );
							container.addClass( 'selected' );
						}
					)
					.on(
						'click',
						wrapper_id + ' .submit-button',
						function () {

							var element     = self,
								review_id   = $( this ).data( 'review-id' ),
								in_reply_of = $( this ).data( 'reply-to' );

							switch ( $( this ).data( 'type' ) ) {
								case 'edit-reply' :
									if ( ywar_frontend.use_recaptcha && 'v3' === ywar_frontend.recaptcha_version ) {
										grecaptcha.ready(
											function () {
												grecaptcha
													.execute(
														ywar_frontend.recaptcha_sitekey,
														{action: 'submit'}
													)
													.then(
														function ( token ) {
															submit_edit_reply( element, review_id, token );
														}
													);
											}
										);
									} else {
										submit_edit_reply( element, review_id, 'off' );
									}
									break;
								case 'edit-review':
									if ( ywar_frontend.use_recaptcha && 'v3' === ywar_frontend.recaptcha_version ) {
										grecaptcha.ready(
											function () {
												grecaptcha
													.execute(
														ywar_frontend.recaptcha_sitekey,
														{action: 'submit'}
													)
													.then(
														function ( token ) {
															submit_edit_review( element, review_id, token );
														}
													);
											}
										);
									} else {
										submit_edit_review( element, review_id, 'off' );
									}
									break;
								case 'create-reply':
									if ( ywar_frontend.use_recaptcha && 'v3' === ywar_frontend.recaptcha_version ) {
										grecaptcha.ready(
											function () {
												grecaptcha
													.execute(
														ywar_frontend.recaptcha_sitekey,
														{action: 'submit'}
													)
													.then(
														function ( token ) {
															submit_new_reply( element, review_id, in_reply_of, token );
														}
													);
											}
										);
									} else {
										submit_new_reply( element, review_id, in_reply_of, 'off' );
									}
									break;
								default:
									if ( ywar_frontend.use_recaptcha && 'v3' === ywar_frontend.recaptcha_version ) {
										grecaptcha.ready(
											function () {
												grecaptcha
													.execute(
														ywar_frontend.recaptcha_sitekey,
														{action: 'submit'}
													)
													.then(
														function ( token ) {
															submit_new_review( element, token );
														}
													);
											}
										);
									} else {
										submit_new_review( element, 'off' );
									}
							}
						}
					)
					.on(
						'dragover',
						wrapper_id + ' .yith-ywar-attachments',
						function () {
							$( this )
								.closest( '.yith-ywar-attachments' )
								.addClass( 'yith-ywar-is-dragging' );
						}
					)
					.on(
						'dragleave',
						wrapper_id + ' .yith-ywar-attachments',
						function () {
							$( this )
								.closest( '.yith-ywar-attachments' )
								.removeClass( 'yith-ywar-is-dragging' );
						}
					)
					.on(
						'change',
						wrapper_id + ' .yith-ywar-attachments .attachment-field',
						function () {
							var wrapper = $( this ).closest( '.yith-ywar-attachments' ),
								list    = wrapper.find( '.attachments-list' ),
								files   = this.files.length ? this.files : false,
								form_id = $( this ).closest( '.yith-ywar-edit-forms' ).attr( 'id' );

							wrapper.removeClass( 'yith-ywar-is-dragging' );
							wrapper.find( '.messages' ).html( '' );
							if ( files ) {
								var files_count     = files.length,
									exceeding_files = {'image': [], 'video': []},
									exceeding_size  = {'image': [], 'video': []},
									messages        = [];

								if ( file_uploads.form !== form_id ) {
									// Reset the upload data if the active form is changed.
									reset_file_upload();
								}

								file_uploads.type_count.image = list.find( '.attachment-image' ).length;
								file_uploads.type_count.video = list.find( '.attachment-video' ).length;
								file_uploads.form             = form_id;

								for ( let index = 0; index < files_count; index++ ) {
									let file_type   = check_file_type( files[index].type ),
										can_upload  = true,
										count_check = file_uploads.type_count[file_type] < ywar_frontend.file_upload.allowed_quantity[file_type],
										size_check  = files[index].size < ((ywar_frontend.file_upload.allowed_size[file_type] * 1024) * 1024);

									if ( ! count_check ) {
										can_upload = false;
										exceeding_files[file_type].push( files[index].name );
									} else if ( ! size_check ) {
										can_upload = false;
										exceeding_size[file_type].push( files[index].name );
									}

									if ( can_upload ) {
										let reader    = new FileReader(),
											new_index = file_uploads.files.push( files[index] ) - 1;

										file_uploads.type_count[file_type] += 1;

										reader.onload = function ( e ) {
											let att_wrapper = $( '<div class="attachment temp-attachment attachment-' + file_type + '" data-item-id="' + new_index + '"></div>' ),
												image       = file_type === 'video' ? ywar_frontend.file_upload.video_placeholder : e.target.result,
												att_image   = $( '<img width="80" height="80" src="' + image + '" class="attachment-80x80 size-80x80" decoding="async" loading="lazy" />' );

											att_wrapper.append( att_image );
											list.append( att_wrapper )
										};

										reader.readAsDataURL( file_uploads.files[new_index] );
										list.removeClass( 'empty' );
									}
								}

								if ( exceeding_files.image.length > 0 ) {
									messages.push( ywar_frontend.messages.too_many_images + exceeding_files.image.join( ', ' ) )
								}
								if ( exceeding_files.video.length > 0 ) {
									messages.push( ywar_frontend.messages.too_many_videos + exceeding_files.video.join( ', ' ) );
								}
								if ( exceeding_size.image.length > 0 ) {
									messages.push( ywar_frontend.messages.image_too_big + exceeding_size.image.join( ', ' ) );
								}
								if ( exceeding_size.video.length > 0 ) {
									messages.push( ywar_frontend.messages.video_too_big + exceeding_size.video.join( ', ' ) );
								}
								if ( messages.length > 0 ) {
									wrapper.find( '.messages' ).html( messages.join( '<br />' ) );
								}
							}
						}
					)
					.on(
						'click',
						wrapper_id + ' .yith-ywar-attachments .attachment',
						function () {
							var item_id = $( this ).data( 'item-id' ),
								wrapper = $( this ).closest( '.yith-ywar-attachments' );

							if ( $( this ).hasClass( 'temp-attachment' ) ) {
								file_uploads.files.splice( item_id, 1 );
								$( this ).remove();
							} else {
								var attachments  = wrapper.find( 'input[name="yith-ywar-attachments"]' ),
									values       = attachments.val().split( ',' ),
									new_elements = [];

								if ( item_id && $.inArray( item_id, values ) ) {
									$( this ).remove();
									new_elements = values.filter(
										function ( val ) {
											return parseInt( val ) !== parseInt( item_id );
										}
									);
								}
								attachments.val( new_elements.join( ',' ) );
							}

							if ( wrapper.find( '.attachments-list' ).children().length === 0 ) {
								wrapper.find( '.attachments-list' ).addClass( 'empty' );
							}
						}
					)
					.on(
						'click',
						wrapper_id + ' .undo-delete-review',
						function () {
							var wrapper   = $( this ).parent().parent(),
								review_id = wrapper.data( 'review-id' ),
								context   = wrapper.data( 'context' );
							// Manage review restore.
							ywar
								.ajax(
									{
										review_id:      review_id,
										button_context: context,
										request:        'restore_review',
									},
									{block: wrapper}
								)
								.done(
									function ( response ) {
										if ( response.data.message ) {
											$( '.yith-ywar-review-form-message.review-' + review_id )
												.after( response.data.message )
												.remove();

											if ( 'popup' === context ) {
												$( '.yith-ywar-reviews-list .review-' + review_id ).removeClass( 'in-popup' );
											}
										}
									}
								);
						}
					);
			},
			submit_new_review                = function ( self, recaptcha_response ) {
				var form_wrapper = self.element.find( '.yith-ywar-edit-forms.new-review' ),
					form_has_error;

				var fields = {
					rating_field:       form_wrapper.find( 'input[name^="yith-ywar-rating"]' ),
					multi_rating:       {},
					rating:             0,
					user_name:          form_wrapper.find( 'input[name="yith-ywar-user-name"]' ),
					user_email:         form_wrapper.find( 'input[name="yith-ywar-user-email"]' ),
					title:              form_wrapper.find( 'input[name="yith-ywar-title"]' ),
					content:            form_wrapper.find( 'textarea[name="yith-ywar-content"]' ),
					recaptcha_wrapper:  form_wrapper.find( '.g-recaptcha' ),
					recaptcha_response: recaptcha_response,
					user_id:            parseInt( ywar_frontend.user_id ),
				};

				if ( ywar_frontend.use_recaptcha && 'v2' === ywar_frontend.recaptcha_version ) {
					fields.recaptcha_response = grecaptcha.getResponse();
				}

				if ( fields.rating_field.length > 1 ) {
					fields.rating_field.each(
						function () {
							fields.multi_rating[$( this ).data( 'index' )] = parseInt( $( this ).val() );
						}
					);
					fields.rating = 0;
				} else {
					fields.rating = fields.rating_field.val();
				}

				form_has_error = validate_form_fields( self, fields, 'insert' );

				if ( ! form_has_error ) {

					var formdata = new FormData();

					formdata.append( 'request', 'submit_new_review' );
					formdata.append( 'rating', fields.rating );
					formdata.append( 'multi_rating', Object.keys( fields.multi_rating ).length === 0 ? '' : JSON.stringify( fields.multi_rating ) );
					formdata.append( 'user_name', typeof fields.user_name.val() === 'undefined' ? '' : fields.user_name.val() );
					formdata.append( 'user_email', typeof fields.user_email.val() === 'undefined' ? '' : fields.user_email.val() );
					formdata.append( 'user_id', fields.user_id );
					formdata.append( 'title', typeof fields.title.val() === 'undefined' ? '' : fields.title.val() );
					formdata.append( 'content', fields.content.val() );
					formdata.append( 'recaptcha_response', fields.recaptcha_response );
					formdata.append( 'product_id', self.element.data( 'product-id' ) );

					$.each(
						file_uploads.files,
						function ( j, file ) {
							formdata.append( 'file-' + j, file );
						}
					);

					// Manage review creation.
					ywar
						.ajax(
							formdata,
							{
								block:       form_wrapper,
								processData: false,
								contentType: false
							}
						)
						.done(
							function ( response ) {
								if ( response.success === true ) {
									refresh_review_stats_attachments();
									reset_file_upload();
									if ( response.data.message ) {
										form_wrapper.html( response.data.message );
										$( 'html, body' )
											.animate(
												{
													scrollTop: form_wrapper.offset().top - ywar_frontend.scroll_offset
												},
												200
											);
									} else {
										window.location = '#review-' + response.data.review_id;
										self.element.find( self.wrapper ).html( '' );
										self.args = {
											page:    1,
											rating:  'all',
											sorting: 'default',
											helpful: 'no'
										};
										load_reviews( self, self.reviews_loaded );

										if ( fields.rating_field.length > 1 ) {
											fields.rating_field.each(
												function () {
													$( this ).val( '' );
												}
											);
										} else {
											fields.rating_field.val( '' );
										}

										form_wrapper.find( '.stars' ).removeClass( 'selected' );
										form_wrapper.find( '.stars' ).find( 'span' ).removeClass( 'active' );
										form_wrapper.find( '.temp-attachment' ).each(
											function () {
												$( this ).remove()
											}
										);
										form_wrapper.find( '.attachments-list' ).addClass( 'empty' );

										fields.user_name.val( '' );
										fields.user_email.val( '' );
										fields.content.val( '' );
										fields.title.val( '' );

										if ( ywar_frontend.use_recaptcha && 'v2' === ywar_frontend.recaptcha_version ) {
											grecaptcha.reset();
										}
									}
								} else {
									validate_form_fields( self, fields, 'insert' )
								}
							}
						);
				}
			},
			submit_edit_review               = function ( self, review_id, recaptcha_response ) {
				var form_wrapper = self.element.find( '#yith-ywar-edit-review-' + review_id ),
					in_popup     = form_wrapper.closest( '.yith-ywar-single-review' ).hasClass( 'in-popup' ),
					form_has_error;

				var fields = {
					rating_field:       form_wrapper.find( 'input[name^="yith-ywar-rating"]' ),
					multi_rating:       {},
					rating:             0,
					title:              form_wrapper.find( 'input[name="yith-ywar-title"]' ),
					content:            form_wrapper.find( 'textarea[name="yith-ywar-content"]' ),
					attachments:        form_wrapper.find( 'input[name="yith-ywar-attachments"]' ),
					recaptcha_wrapper:  form_wrapper.find( '.g-recaptcha' ),
					recaptcha_response: recaptcha_response,
					user_id:            parseInt( ywar_frontend.user_id ),
				};

				if ( ywar_frontend.use_recaptcha && 'v2' === ywar_frontend.recaptcha_version ) {
					fields.recaptcha_response = grecaptcha.getResponse( ywar.captcha_edit );
				}

				if ( fields.rating_field.length > 1 ) {
					fields.rating_field.each(
						function () {
							fields.multi_rating[$( this ).data( 'index' )] = parseInt( $( this ).val() );
						}
					);
					fields.rating = 0;
				} else {
					fields.rating = fields.rating_field.val();
				}

				form_has_error = validate_form_fields( self, fields, 'edit' );

				if ( ! form_has_error ) {

					var formdata = new FormData();
					formdata.append( 'request', 'submit_edit_review' );
					formdata.append( 'rating', fields.rating );
					formdata.append( 'multi_rating', Object.keys( fields.multi_rating ).length === 0 ? '' : JSON.stringify( fields.multi_rating ) );
					formdata.append( 'title', typeof fields.title.val() === 'undefined' ? '' : fields.title.val() );
					formdata.append( 'content', fields.content.val() );
					formdata.append( 'attachments', (fields.attachments.length > 0 ? fields.attachments.val() : '') );
					formdata.append( 'recaptcha_response', fields.recaptcha_response );
					formdata.append( 'review_id', review_id );
					formdata.append( 'box_id', self.element.data( 'review-box' ) );
					formdata.append( 'popup', in_popup ? 'yes' : 'no' );

					$.each(
						file_uploads.files,
						function ( j, file ) {
							formdata.append( 'file-' + j, file );
						}
					);

					// Manage review creation.
					ywar
						.ajax(
							formdata,
							{
								block:       form_wrapper,
								processData: false,
								contentType: false
							}
						)
						.done(
							function ( response ) {
								if ( response.success === true ) {
									reset_file_upload();
									var temp_content   = $( "<div></div>" ).html( response.data.html ),
										updated_review = temp_content.find( response.data.review_id ).html();
									self.element.find( response.data.review_id ).html( updated_review );
									refresh_review_stats_attachments();
									$( 'html, body' )
										.animate(
											{
												scrollTop: self.element.find( response.data.review_id ).offset().top - ywar_frontend.scroll_offset
											},
											200
										);
								} else {
									validate_form_fields( self, fields, 'edit' );
								}
							}
						);
				}
			},
			submit_edit_reply                = function ( self, review_id, recaptcha_response ) {
				var form_wrapper = self.element.find( '#yith-ywar-edit-reply-' + review_id ),
					in_popup     = form_wrapper.closest( '.yith-ywar-single-review' ).hasClass( 'in-popup' ),
					form_has_error;

				var fields = {
					title:              form_wrapper.find( 'input[name="yith-ywar-title"]' ),
					content:            form_wrapper.find( 'textarea[name="yith-ywar-content"]' ),
					attachments:        form_wrapper.find( 'input[name="yith-ywar-attachments"]' ),
					recaptcha_wrapper:  form_wrapper.find( '.g-recaptcha' ),
					recaptcha_response: recaptcha_response,
					user_id:            parseInt( ywar_frontend.user_id ),
				};

				if ( ywar_frontend.use_recaptcha && 'v2' === ywar_frontend.recaptcha_version ) {
					fields.recaptcha_response = grecaptcha.getResponse( ywar.captcha_edit );
				}

				form_has_error = validate_form_fields( self, fields, 'edit' );

				if ( ! form_has_error ) {

					var formdata = new FormData();

					formdata.append( 'request', 'submit_edit_reply' );
					formdata.append( 'title', fields.title.val() );
					formdata.append( 'content', fields.content.val() );
					formdata.append( 'attachments', (fields.attachments.length > 0 ? fields.attachments.val() : '') );
					formdata.append( 'recaptcha_response', fields.recaptcha_response );
					formdata.append( 'review_id', review_id );
					formdata.append( 'box_id', self.element.data( 'review-box' ) );
					formdata.append( 'popup', in_popup ? 'yes' : 'no' );

					$.each(
						file_uploads.files,
						function ( j, file ) {
							formdata.append( 'file-' + j, file );
						}
					);
					// Manage review creation.
					ywar
						.ajax(
							formdata,
							{
								block:       form_wrapper,
								processData: false,
								contentType: false
							}
						)
						.done(
							function ( response ) {
								if ( response.success === true ) {
									reset_file_upload();
									var temp_content   = $( "<div></div>" ).html( response.data.html ),
										updated_review = temp_content.find( response.data.review_id ).html();
									self.element.find( response.data.review_id ).html( updated_review );
									refresh_review_stats_attachments();
									$( 'html, body' )
										.animate(
											{
												scrollTop: self.element.find( response.data.review_id ).offset().top - ywar_frontend.scroll_offset
											},
											200
										);
								} else {
									validate_form_fields( self, fields, 'edit' );
								}
							}
						);
				}
			},
			submit_new_reply                 = function ( self, review_id, in_reply_of, recaptcha_response ) {
				var form_wrapper = self.element.find( '#yith-ywar-new-reply-' + review_id ),
					in_popup     = form_wrapper.closest( '.yith-ywar-single-review' ).hasClass( 'in-popup' ),
					form_has_error;

				var fields = {
					user_name:          form_wrapper.find( 'input[name="yith-ywar-user-name"]' ),
					user_email:         form_wrapper.find( 'input[name="yith-ywar-user-email"]' ),
					title:              form_wrapper.find( 'input[name="yith-ywar-title"]' ),
					content:            form_wrapper.find( 'textarea[name="yith-ywar-content"]' ),
					recaptcha_wrapper:  form_wrapper.find( '.g-recaptcha' ),
					recaptcha_response: recaptcha_response,
					user_id:            parseInt( ywar_frontend.user_id ),
				};

				if ( ywar_frontend.use_recaptcha && 'v2' === ywar_frontend.recaptcha_version ) {
					fields.recaptcha_response = grecaptcha.getResponse( ywar.captcha_edit );
				}

				form_has_error = validate_form_fields( self, fields, 'insert' );

				if ( ! form_has_error ) {

					var formdata = new FormData();

					formdata.append( 'request', 'submit_new_reply' );
					formdata.append( 'user_name', fields.user_name.val() );
					formdata.append( 'user_email', fields.user_email.val() );
					formdata.append( 'user_id', fields.user_id );
					formdata.append( 'title', fields.title.val() );
					formdata.append( 'content', fields.content.val() );
					formdata.append( 'recaptcha_response', fields.recaptcha_response );
					formdata.append( 'review_id', review_id );
					formdata.append( 'in_reply_of', in_reply_of );
					formdata.append( 'popup', in_popup ? 'yes' : 'no' );

					$.each(
						file_uploads.files,
						function ( j, file ) {
							formdata.append( 'file-' + j, file );
						}
					);

					// Manage review creation.
					ywar
						.ajax(
							formdata,
							{
								block:       form_wrapper,
								processData: false,
								contentType: false
							}
						)
						.done(
							function ( response ) {
								if ( response.success === true ) {
									reset_file_upload();
									form_wrapper.find( '.temp-attachment' ).each(
										function () {
											$( this ).remove()
										}
									);
									form_wrapper.find( '.attachments-list' ).addClass( 'empty' );

									refresh_review_stats_attachments();
									if ( response.data.message ) {
										form_wrapper.html( response.data.message );
										$( 'html, body' )
											.animate(
												{
													scrollTop: form_wrapper.offset().top - ywar_frontend.scroll_offset
												},
												200
											);
									} else {
										self.element.find( '.yith-ywar-edit-forms:not(.new-review)' ).remove();
										self.element.find( '.replies-review-' + review_id ).append( response.data.html );
										$( 'html, body' )
											.animate(
												{
													scrollTop: self.element.find( response.data.review_id ).offset().top + self.element.find( response.data.review_id ).innerHeight() - 400
												},
												200
											);
									}
								} else {
									validate_form_fields( self, fields, 'insert' );
								}
							}
						);
				}
			},
			check_file_type                  = function ( file_type ) {
				if ( image_mime_types.includes( file_type ) ) {
					return 'image'
				} else if ( video_mime_types.includes( file_type ) ) {
					return 'video'
				}
				return false;
			},
			reset_file_upload                = function () {
				file_uploads = {form: '', files: [], type_count: {image: 0, video: 0}};
				$( '.messages' ).html( '' );
			},
			scroll_to_review_tab             = function () {

				const wc_tabs = $( '.woocommerce-tabs' );

				$( 'body' )
					.find( 'li.reviews_tab a' )
					.trigger( 'click' );

				// Fix for Porto theme.
				$( 'body.theme-porto' )
					.find( 'li.reviews_tab' )
					.trigger( 'click' );

				if ( wc_tabs.length > 0 ) {
					$( 'html, body' )
						.animate(
							{
								scrollTop: wc_tabs.offset().top - ywar_frontend.scroll_offset
							},
							500
						);
				}

				$( document ).trigger( 'yith_ywar_review_tab' );

			},
			refresh_review_stats_attachments = function () {
				if ( ywar_frontend.attachments_gallery || ywar_frontend.graph_bars ) {
					const stats_wrapper       = '.yith-ywar-stats-wrapper';
					const attachments_wrapper = '.yith-ywar-reviews-with-attachments';
					const tab_title           = '.yith-ywar-tab-title';
					const rating_wrapper      = '.yith-ywar-product-rating-wrapper';

					$.ajax(
						{
							url:     window.location.href,
							success: function ( resp ) {
								if ( resp !== '' ) {
									const temp_content = $( '<div></div>' ).html( resp );

									if ( $( stats_wrapper ).length > 0 ) {
										const stats_content = temp_content.find( stats_wrapper );
										$( stats_wrapper ).html( stats_content.html() );
									}

									if ( $( tab_title ).length > 0 ) {
										const tab_title_content = temp_content.find( tab_title );
										$( tab_title ).html( tab_title_content.html() );
									}

									if ( $( rating_wrapper ).length > 0 ) {
										const rating_wrapper_content = temp_content.find( rating_wrapper );
										$( rating_wrapper ).html( rating_wrapper_content.html() );
									}

									if ( $( attachments_wrapper ).length > 0 ) {
										const attachments_content = temp_content.find( attachments_wrapper );
										if ( attachments_content.html().trim().length > 0 ) {
											$( attachments_wrapper ).html( attachments_content.html() ).removeClass( 'empty-gallery' );
										} else {
											$( attachments_wrapper ).addClass( 'empty-gallery' );
										}
										init_attachment_swiper();
									}
								}
							}
						}
					);
				}
			},
			init_attachment_swiper           = function () {
				var thumb_swiper = new ywar.Swiper(
					'.preview-gallery',
					{
						spaceBetween:        10,
						slidesPerView:       'auto',
						watchSlidesProgress: true,
						loop:                true,
						navigation:          {
							nextEl: '.swiper-button-next',
							prevEl: '.swiper-button-prev',
						},
					}
				);
			},
			yith_ywar_reviews                = function ( element ) {
				this.element = $( element );
				this.args    = {
					page:    1,
					rating:  'all',
					sorting: 'default',
					helpful: 'no'
				};
				this.popup   = false;
				this.wrapper = '.yith-ywar-reviews-list';
				this.init();
			},
			yith_ywar_reviews_shortcode      = function ( element ) {
				this.element = $( element );
				this.page    = 1;
				this.init();
			},
			yith_ywar_filtered_reviews       = function ( element, args ) {
				this.element = $( element );
				this.scroll  = $( '#yith-ywar-filter-popup-wrapper' );
				this.args    = {
					page:    1,
					rating:  args.rating,
					sorting: 'default',
					helpful: 'no'
				};
				this.popup   = true;
				this.wrapper = '.yith-ywar-reviews-list-popup';
				this.element.data( 'product-id', args.product_id );
				this.element.data( 'box-id', args.box_id );
				this.init();
			},
			yith_ywar_attachment_review      = function ( element, args ) {
				this.element   = $( element );
				this.scroll    = $( '#yith-ywar-attachments-popup-wrapper' );
				this.slide     = args.slide_index;
				this.review_id = args.review_id;
				this.parent    = args.parent;
				this.init();
			},
			yith_ywar_attachment_gallery     = function ( element, args ) {
				this.element   = $( element );
				this.scroll    = $( '#yith-ywar-gallery-popup-wrapper' );
				this.slide     = args.slide_index;
				this.review_id = args.review_id;
				this.parent    = args.parent;
				this.init();
			},
			yith_ywar_attachment_lightbox    = function ( element, args ) {
				this.element   = $( element );
				this.slide     = args.slide_index;
				this.review_id = args.review_id;
				this.parent    = args.parent;
				this.init();
			};

		/**
		 * PROTOTYPES
		 */
		yith_ywar_reviews.prototype = {
			init:             function () {
				load_reviews( this, this.reviews_loaded );
				this.after_init();
			},
			after_init:       function () {
				const self = this;

				self.element
					.on(
						'click',
						'.load-more-button',
						function () {
							self.args.page = $( this ).data( 'page' );
							load_reviews( self, self.reviews_loaded );
						}
					)
					.on(
						'click',
						'.yith-ywar-single-review:not(.in-popup) .single-attachment',
						function () {
							var args = {
								review_id:   $( this ).data( 'review-id' ),
								slide_index: $( this ).data( 'slide-index' ),
								parent:      self.element
							};

							$( '#yith-ywar-attachments-popup' ).yith_ywar_attachment_review( args );
						}
					)
					.on(
						'click',
						'.yith-ywar-swiper.preview-gallery .swiper-slide',
						function () {
							var args = {
								review_id:   $( this ).data( 'review-id' ),
								slide_index: $( this ).data( 'slide-index' ),
								parent:      self.element
							};

							$( '#yith-ywar-gallery-popup' ).yith_ywar_attachment_gallery( args );
						}
					)
					.on(
						'click',
						'.rating-group',
						function () {
							if ( 0 !== parseInt( $( this ).data( 'count' ) ) ) {
								if ( ywar_frontend.filter_dialog ) {
									var args = {
										rating:     $( this ).data( 'rating' ),
										product_id: self.element.data( 'product-id' ),
										box_id:     self.element.data( 'review-box' ),
									};

									$( '#yith-ywar-filter-popup' ).yith_ywar_filtered_reviews( args );

								} else {
									self.element.find( self.wrapper ).html( '' );
									self.args.page   = 1;
									self.args.rating = $( this ).data( 'rating' );
									load_reviews( self, self.reviews_loaded );
								}
							}
						}
					)
					.on(
						'click',
						'.filter-buttons .show-all-reviews, .filter-buttons .rating-label',
						function () {
							self.element.find( self.wrapper ).html( '' );
							self.args.page   = 1;
							self.args.rating = 'all';
							load_reviews( self, self.reviews_loaded );
						}
					)
					.on(
						'change',
						'.sorting-options',
						function () {
							self.element.find( self.wrapper ).html( '' );
							self.args.page    = 1;
							self.args.sorting = $( this ).val();
							load_reviews( self, self.reviews_loaded );
						}
					)
					.on(
						'click',
						'.tab-item',
						function () {
							self.element.find( '.tab-item' ).removeClass( 'selected' );
							$( this ).addClass( 'selected' );
							self.args             = {
								page:    1,
								rating:  'all',
								sorting: 'default',
								helpful: $( this ).data( 'filter' ) === 'helpful' ? 'yes' : 'no',
							};
							const sorting_options = self.element.find( '.sorting-options' );
							if ( sorting_options.length > 0 ) {
								sorting_options.val( 'default' ).trigger( 'change' );
							} else {
								self.element.find( self.wrapper ).html( '' );
								load_reviews( self, self.reviews_loaded );
							}
						}
					)
					.on(
						'click',
						'.yith-ywar-pending-reviews-list .wrapper-title',
						function () {
							$( this ).parent().find( '.wrapper-content' ).slideToggle();
						}
					);

				init_buttons( self );
				init_attachment_swiper();
			},
			reviews_loaded:   function ( response, self ) {
				self.element.find( '.yith-ywar-filter-data' ).remove();
				self.element.find( '.load-more-reviews' ).remove();
				self.element.find( self.wrapper ).append( response.data.reviews );
				if ( response.data.message ) {
					$( response.data.message ).insertAfter( '.yith-ywar-stats-wrapper' );
				}

				if ( window.location.hash.indexOf( '#review-' ) !== -1 || window.location.hash.indexOf( '#comment-' ) !== -1 ) {
					self.scroll_to_review( window.location.hash.replace( 'comment', 'review' ) );
				}
			},
			scroll_to_review: function ( review_id ) {
				if ( $( review_id ).length > 0 ) {
					$( 'html, body' )
						.animate(
							{
								scrollTop: $( review_id ).offset().top - ywar_frontend.scroll_offset
							},
							200
						);
					history.replaceState( "", document.title, window.location.pathname );
				}
			},
		};

		yith_ywar_reviews_shortcode.prototype = {
			init:         function () {
				if ( ywar_frontend.is_block_editor ) {
					this.element.css( 'min-height', '400px' )
				}
				this.load_reviews();
				this.after_init();
			},
			load_reviews: function () {
				const self = this;
				ywar
					.ajax(
						{
							settings: self.element.data( 'settings' ),
							request:  'load_reviews_shortcode',
							page:     self.page,
						},
						{block: self.element}
					)
					.done(
						function ( response ) {
							self.element.find( '.load-more-reviews-shortcode' ).remove();
							self.element.append( response.data );
						}
					);
			},
			after_init:   function () {
				const self = this;

				self.element
					.on(
						'click',
						'.load-more-button-shortcode',
						function () {
							self.page = $( this ).data( 'page' );
							self.load_reviews();
						}
					)
					.on(
						'click',
						'.yith-ywar-single-review:not(.in-popup) .single-attachment',
						function () {
							var args = {
								review_id:   $( this ).data( 'review-id' ),
								slide_index: $( this ).data( 'slide-index' ),
								parent:      self.element
							};

							ywar
								.ajax(
									{
										request:   'load_attachment_popup',
										review_id: $( this ).data( 'review-id' ),
									},
									{block: self.element}
								)
								.done(
									function ( response ) {
										var popup = '#yith-ywar-attachments-popup';
										$( popup ).remove();
										$( 'body' ).append( response.data );
										$( popup ).yith_ywar_attachment_review( args );
									}
								);
						}
					);

				init_buttons( self );
			}
		};

		yith_ywar_filtered_reviews.prototype = {
			init:            function () {
				this.element.find( '.filter-options' ).val( this.args.rating );
				load_reviews( this, this.open_popup );
				this.after_load();
			},
			open_popup:      function ( content, self ) {
				self.init_scrollbar( self.scroll );
				// Add class to html for prevent page scroll on mobile device.
				$( 'html' ).addClass( 'yith-ywar-open-popup' );
				self.element.removeClass( 'closed' ).addClass( 'visible' );
				self.element.find( '.popup-content' ).html( '<div class="yith-ywar-reviews-list-popup">' + content.data.reviews + '</div>' );
			},
			init_scrollbar:  function ( scroller ) {
				sb_instance = new ywar.SimpleBar(
					scroller[0],
					{
						forceVisible: true,
						autoHide:     false
					}
				);
			},
			after_load_more: function ( response, self ) {
				wrapper = self.element.find( self.wrapper );
				wrapper.find( '.load-more-reviews-popup' ).remove();
				wrapper.append( response.data.reviews );
			},
			after_load:      function () {
				const self = this;

				self.element
					.on(
						'click',
						'.load-more-button-popup',
						function () {
							self.args.page = $( this ).data( 'page' );
							load_reviews( self, self.after_load_more );
						}
					)
					.on(
						'change',
						'.filter-options',
						function () {
							self.args.page   = 1;
							self.args.rating = $( this ).val();
							load_reviews(
								self,
								function ( response ) {
									self.scroll.scrollTop( 0 );
									self.element.find( self.wrapper ).html( response.data.reviews );
								}
							);
						}
					)
					.on(
						'click',
						'.popup-close, .popup-close-link',
						function ( e ) {
							e.preventDefault();
							self.close_popup();
						}
					)
					.on(
						'click',
						'.yith-ywar-single-review.in-popup .single-attachment',
						function () {
							var args = {
								review_id:   $( this ).data( 'review-id' ),
								slide_index: $( this ).data( 'slide-index' ),
								parent:      self.element
							};
							$( '#yith-ywar-gallery-lightbox' ).yith_ywar_attachment_lightbox( args );
						}
					);
				init_buttons( self );

				// Init selectWoo on sorting dropdown.
				$( '.filter-options' )
					.selectWoo(
						{
							dropdownCssClass:        'yith-ywar-select2-stars',
							minimumResultsForSearch: Infinity,
							templateResult:          function ( state ) {
								if ( ! state.id ) {
									return state.text;
								}

								return $( '<span class="select2-star-rating" title="' + state.id + '">' + state.text + '</span>' );
							},
						}
					);
			},
			close_popup:     function () {

				// Popup close actions.
				this.element.addClass( 'closed' ).removeClass( 'visible' );
				// Remove class to html.
				$( 'html' ).removeClass( 'yith-ywar-open-popup' );
				$( document )
					.off(
						'click',
						'.load-more-button-popup'
					);
				this.scroll.scrollTop( 0 );
				sb_instance = null;
			},
		};

		yith_ywar_attachment_review.prototype = {
			init:           function () {
				const self = this;
				ywar
					.ajax(
						{
							review_id: self.review_id,
							request:   'load_single_review',
						},
						{block: self.parent}
					)
					.done(
						function ( response ) {
							self.open_popup( response, self );
						}
					);

			},
			open_popup:     function ( content, self ) {
				self.init_scrollbar( self.scroll );
				// Add class to html for prevent page scroll on mobile device.
				$( 'html' ).addClass( 'yith-ywar-open-popup' );
				self.element.removeClass( 'closed' ).addClass( 'visible' );
				self.element.find( '.popup-content' ).html( content.data );
				self.after_load( self );
			},
			init_scrollbar: function ( scroller ) {
				sb_instance = new ywar.SimpleBar(
					scroller[0],
					{
						forceVisible: true,
						autoHide:     false
					}
				);
			},
			after_load:     function ( self ) {

				$( document )
					.on(
						'click',
						'.popup-close, .popup-close-link',
						function ( e ) {
							e.preventDefault();
							self.close_popup();
						}
					);

				init_swiper( self.slide, 'gallery' );
			},
			close_popup:    function () {
				// Popup close actions.
				this.element.addClass( 'closed' ).removeClass( 'visible' );
				// Remove class to html.
				$( 'html' ).removeClass( 'yith-ywar-open-popup' );
				this.scroll.scrollTop( 0 );
				this.element.find( '.popup-content' ).html( '' );
				sb_instance = null;
			}
		};

		yith_ywar_attachment_gallery.prototype = {
			init:           function () {
				const self = this;
				ywar
					.ajax(
						{
							active_review_id: self.review_id,
							request:          'load_reviews_with_attachments',
						},
						{block: self.parent}
					)
					.done(
						function ( response ) {
							self.open_popup( response, self );
						}
					);
			},
			open_popup:     function ( content, self ) {
				self.init_scrollbar( self.scroll );
				// Add class to html for prevent page scroll on mobile device.
				$( 'html' ).addClass( 'yith-ywar-open-popup' );
				self.element.removeClass( 'closed' ).addClass( 'visible' );
				self.element.find( '.popup-content' ).html( content.data );
				self.after_load( self );
			},
			init_scrollbar: function ( scroller ) {
				sb_instance = new ywar.SimpleBar(
					scroller[0],
					{
						forceVisible: true,
						autoHide:     false
					}
				);
			},
			after_load:     function ( self ) {
				$( document )
					.on(
						'click',
						'.popup-close, .popup-close-link',
						function ( e ) {
							e.preventDefault();
							self.close_popup();
						}
					);

				init_swiper( self.slide, 'gallery' );
			},
			close_popup:    function () {
				// Popup close actions.
				this.element.addClass( 'closed' ).removeClass( 'visible' );
				// Remove class to html.
				$( 'html' ).removeClass( 'yith-ywar-open-popup' );
				this.scroll.scrollTop( 0 );
				this.element.find( '.popup-content' ).html( '' );
				sb_instance = null;
			}
		};

		yith_ywar_attachment_lightbox.prototype = {
			init:        function () {
				const self = this;
				ywar
					.ajax(
						{
							review_id: self.review_id,
							request:   'load_review_attachments',
						},
						{block: self.parent}
					)
					.done(
						function ( response ) {
							self.open_popup( response, self );
						}
					);

			},
			open_popup:  function ( content, self ) {
				self.element.removeClass( 'closed' ).addClass( 'visible' );
				self.element.find( '.lightbox-content' ).html( content.data );
				self.after_load( self );
			},
			after_load:  function ( self ) {
				$( document )
					.on(
						'click',
						'.lightbox-close, .lightbox-overlay',
						function ( e ) {
							e.preventDefault();
							self.close_popup();
						}
					);

				init_swiper( self.slide, 'lightbox' );
			},
			close_popup: function () {
				// Popup close actions.
				this.element.removeClass( 'visible' ).addClass( 'closed' );
			}
		};

		$.fn.yith_ywar_reviews             = function () {
			return this.each(
				function () {
					if ( ! $.data( this, 'plugin_yith_ywar_reviews' ) ) {
						$.data( this, 'plugin_yith_ywar_reviews', new yith_ywar_reviews( this ) );
					}
				}
			);
		};
		$.fn.yith_ywar_reviews_shortcode   = function () {
			return this.each(
				function () {
					if ( ! $.data( this, 'plugin_yith_ywar_reviews_shortcode' ) ) {
						$.data( this, 'plugin_yith_ywar_reviews_shortcode', new yith_ywar_reviews_shortcode( this ) );
					}
				}
			);
		};
		$.fn.yith_ywar_filtered_reviews    = function ( args ) {
			$.data( this, 'plugin_yith_ywar_filtered_reviews', new yith_ywar_filtered_reviews( this, args ) );
		};
		$.fn.yith_ywar_attachment_review   = function ( args ) {
			$.data( this, 'plugin_yith_ywar_attachment_review', new yith_ywar_attachment_review( this, args ) );
		};
		$.fn.yith_ywar_attachment_gallery  = function ( args ) {
			$.data( this, 'plugin_yith_ywar_attachment_gallery', new yith_ywar_attachment_gallery( this, args ) );
		};
		$.fn.yith_ywar_attachment_lightbox = function ( args ) {
			$.data( this, 'plugin_yith_ywar_attachment_lightbox', new yith_ywar_attachment_lightbox( this, args ) );
		};

		$( 'body' )
			.on(
				'init',
				function () {

					if ( ywar_init ) {
						console.log( 'YWAR already loaded' );

						return;
					}
					ywar_init = true;
					console.log( 'YWAR loaded' );

					if ( window.location.hash === '#reviews' ) {
						scroll_to_review_tab();
					}

					// Load reviews on page load.
					$( '.yith-ywar-main-wrapper' ).each(
						function () {
							$( this ).yith_ywar_reviews()
						}
					);
				}
			)
			.trigger( 'init' );

		$( 'body:not(.woocommerce)' ).trigger( 'init' );

		// Load reviews in shortcode on page load.
		$( '.yith-ywar-reviews-list-shortcode' ).each(
			function () {
				$( this ).yith_ywar_reviews_shortcode()
			}
		);

		$( document )
			.on(
				'click',
				'.yith-ywar-user-reviews-wrapper.in-shortcode .review-pagination',
				function ( e ) {
					e.preventDefault();
					var wrapper = $( '.yith-ywar-user-reviews-wrapper' );
					ywar
						.ajax(
							{
								page:    $( this ).data( 'page' ),
								request: 'load_user_reviews',
							},
							{block: wrapper}
						)
						.done(
							function ( response ) {

								if ( response.success === true ) {
									wrapper.html( $( response.data ).html() );
									$( 'html, body' )
										.animate(
											{
												scrollTop: wrapper.offset().top - ywar_frontend.scroll_offset
											},
											200
										);
								}
							}
						);

				}
			)
			.on(
				'click',
				'a.total-reviews',
				function ( e ) {
					e.stopPropagation();
					e.preventDefault();
					scroll_to_review_tab();
				}
			);

		// Init selectWoo on sorting dropdown.
		$( '.sorting-options' )
			.selectWoo(
				{
					dropdownCssClass:        'yith-ywar-select2',
					minimumResultsForSearch: Infinity
				}
			);
	}
)( jQuery, window.ywar );
