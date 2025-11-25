/**
 * Admin review scripts
 *
 * @var jQuery
 * @var ywar_admin
 * @package YITH\AdvancedReviews\Assets\JS\Admin
 */

(
	function ( $, ywar ) {

		setTimeout(
			function () {
				$( 'tr' ).removeClass( 'yith-ywar-new-element' )
			},
			10000
		);

		$( '.postbox-header h2' ).removeClass( 'hndle' ).removeClass( 'ui-sortable-handle' );
		$( '.postbox-header .handle-actions' ).html( '' );

		var upload_attachments = {
			instance: null,
			wrapper:  null,
			init:     function ( wrapper ) {
				upload_attachments.wrapper = wrapper;

				if ( ! upload_attachments.instance ) {
					var mediaUploaderStates = [
						new wp.media.controller.Library(
							{
								library:    wp.media.query( {type: [ 'image', 'video' ]} ),
								multiple:   false,
								priority:   20,
								filterable: 'uploaded'
							}
						)
					];

					upload_attachments.instance = wp.media.frames.downloadable_file = wp.media(
						{
							library:  {type: ''},
							multiple: false,
							states:   mediaUploaderStates
						}
					);

					// When a file is selected, grab the URL and set it as the text field's value.
					upload_attachments.instance.on(
						'select',
						function () {
							var attachment = upload_attachments.instance.state().get( 'selection' ).first().toJSON();

							ywar
								.adminAjax(
									{
										attachment_id: attachment.id,
										request:       'get_attachment_image'
									},
									{block: wrapper}
								)
								.done(
									function ( response ) {
										if ( response.success ) {

											var attachments = wrapper.find( '.attachment-values' ),
												values      = attachments.val().split( ',' ),
												new_item    = response.data.id;

											if ( new_item && $.inArray( new_item, values ) === -1 ) {
												wrapper.find( '.attachments-list' ).removeClass( 'empty' ).append( response.data.html );
												values.push( new_item );
												$( document ).trigger( 'yith-plugin-fw-tips-init' )
											}

											attachments.val( values.join( ',' ) );

										}
									}
								);
						}
					);
				}
			},
			open:     function ( wrapper ) {
				upload_attachments.init( wrapper );
				upload_attachments.instance.open();
			},
			destroy:  function () {
				upload_attachments.instance = null;
			}
		};

		$( document )
			.on(
				'click',
				'.yith-ywar-show-more .show-more, .yith-ywar-show-more .hide-more',
				function ( e ) {
					e.stopPropagation();
					var content    = $( this ).closest( '.yith-ywar-show-more' ),
						short_text = content.find( '.short-text' ),
						long_text  = content.find( '.long-text' );
					content.toggleClass( 'yith-ywar-show-more--open' );
					if ( long_text.length ) {
						if ( content.is( '.yith-ywar-show-more--open' ) ) {
							long_text.show();
							short_text.hide();
						} else {
							long_text.hide();
							short_text.show()
						}
					}
				}
			)
			.on(
				'click',
				'.yith-ywar-change-review-status',
				function () {
					var container = $( this ).closest( 'tr' ).find( '.column-status' ),
						review_id = $( this ).data( 'id' );

					ywar
						.adminAjax(
							{
								id:         review_id,
								set_status: $( this ).data( 'action' ),
								request:    'change_review_status'
							},
							{block: container}
						)
						.done(
							function ( response ) {
								if ( response.success ) {
									location.reload();
								}
							}
						);
				}
			)
			.on(
				'click',
				'.yith-ywar-rating-wrapper .stars span',
				function () {
					var star      = $( this ),
						rating    = $( this ).closest( '.yith-ywar-rating-wrapper' ).find( '.rating-value' ),
						container = $( this ).closest( '.stars' );

					rating.val( star.data( 'value' ) );
					star.siblings( 'span' ).removeClass( 'active' );
					star.addClass( 'active' );
					container.addClass( 'selected' );
				}
			)
			.on(
				'yith_ywar_post_validation',
				function ( event, validation ) {

					var fields = JSON.parse( $( '#validate_fields' ).val() ),
						items  = fields.length,
						failed = [];

					for ( var i = 0; i < items; i++ ) {
						var field = $( fields[i] );

						if ( field.val() === '' || field.val() === null ) {
							failed.push( field );
							validation.is_valid = false
						}
						validation.failed = failed;
					}
				}
			)
			.on(
				'click',
				'.yith-ywar-attachments .new-attachment',
				function () {
					var wrapper = $( this ).closest( '.yith-ywar-attachments' );

					upload_attachments.open( wrapper );
				}
			)
			.on(
				'click',
				'.yith-ywar-attachments .delete-button',
				function () {
					var item_id      = $( this ).data( 'item_id' ),
						wrapper      = $( this ).closest( '.yith-ywar-attachments' ),
						attachments  = wrapper.find( '.attachment-values' ),
						values       = attachments.val().split( ',' ),
						new_elements = [];

					if ( item_id && $.inArray( item_id, values ) ) {
						wrapper.find( '.attachment-' + item_id ).remove();
						new_elements = values.filter(
							function ( val ) {
								return parseInt( val ) !== parseInt( item_id );
							}
						);
					}

					attachments.val( new_elements.join( ',' ) );
					if ( '' === attachments.val() ) {
						wrapper.find( '.attachments-list' ).addClass( 'empty' );
					}
				}
			)
			.on(
				'click',
				'.unlock-edit-fields',
				function () {
					var wrapper = $( this ).closest( '.editable-fields-wrapper' );
					if ( wrapper.hasClass( 'locked' ) ) {
						wrapper.removeClass( 'locked' );
						wrapper.find( 'input' ).prop( 'readonly', false );
						wrapper.find( 'select' ).prop( 'disabled', false );
						wrapper.find( '.yith-plugin-fw__panel__option--html' ).hide();
						wrapper.find( '.yith-plugin-fw__panel__option--ajax-customers' ).show();
					} else {
						wrapper.addClass( 'locked' );
						wrapper.find( 'input' ).prop( 'readonly', true );
						wrapper.find( 'select' ).prop( 'disabled', false );
						wrapper.find( '.yith-plugin-fw__panel__option--html' ).show();
						wrapper.find( '.yith-plugin-fw__panel__option--ajax-customers' ).hide();

					}
				}
			)
			.on(
				'input change',
				'.yith-plugin-fw-media input',
				function () {
					if ( '' !== $( this ).val() ) {
						$( this ).closest( '.yith-plugin-fw-media' ).removeClass( 'empty' );
					} else {
						$( this ).closest( '.yith-plugin-fw-media' ).addClass( 'empty' );
					}
				}
			)
			.on(
				'change',
				'.reviewed-product',
				function () {

					var wrapper = $( '#yith-ywar-review-options' );

					ywar
						.adminAjax(
							{
								product_id: $( this ).val(),
								request:    'get_product_rating'
							},
							{block: wrapper}
						)
						.done(
							function ( response ) {
								if ( response.success ) {
									var element = $( '.yith-plugin-fw__panel__option--' + response.data.search );
									element.removeClass( 'yith-plugin-fw__panel__option--' + response.data.search ).addClass( 'yith-plugin-fw__panel__option--' + response.data.replace );
									element.find( '.yith-plugin-fw__panel__option__content' ).html( response.data.html );
									$( '#validate_fields' ).val( response.data.validate_fields )
								}
							}
						);
				}
			)
			.on(
				'change',
				'select#rating',
				function () {
					$( '#post-query-submit' ).trigger( 'click' );
				}
			)
			.on(
				'change',
				'select#review_user_id',
				function () {
					const user_id = $( this ).val();

					ywar
						.adminAjax(
							{
								user_id: user_id,
								request: 'get_user_data'
							},
						)
						.done(
							function ( response ) {
								if ( response.success ) {
									if ( response.data.guest ) {
										$( '.user-info' ).html( response.data.guest );
										$( '#review_author' ).val( '' );
										$( '#review_author_email' ).val( '' );
									} else {
										$( '#review_author' ).val( response.data.user_name );
										$( '#review_author_email' ).val( response.data.user_email );
										$( '.user-info' ).html( response.data.user_info );
									}
								}
							}
						);
				}
			);
	}
)( jQuery, window.ywar );
