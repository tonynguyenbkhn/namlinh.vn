/**
 * Admin scripts
 *
 * @var jQuery
 * @var ywar_admin
 * @package YITH\AdvancedReviews\Assets\JS\Admin
 */

(
	function ( $ ) {

		if ( typeof adminpage !== 'undefined' && [ 'post-php', 'post-new-php' ].indexOf( adminpage ) >= 0 ) {
			var postTypeSaving = {
				dom:                       {
					actions:   $( '#yith-ywar-post-type__actions' ),
					save:      $( '#yith-ywar-post-type__save' ),
					floatSave: $( '#yith-ywar-post-type__float-save' )
				},
				init:                      function () {
					var self = postTypeSaving;
					if ( self.dom.save.length ) {
						self.dom.save.on( 'click', self.onSaveClick );
						self.dom.floatSave.on( 'click', self.onFloatSaveClick );
						document.addEventListener( 'scroll', self.handleFloatSaveVisibility, {passive: true} );
						$( window ).on( 'resize', self.handleFloatSaveVisibility );
						self.handleFloatSaveVisibility();
					}
				},
				isInViewport:              function ( el ) {
					var rect     = el.get( 0 ).getBoundingClientRect();
					var viewport = {
						width:  window.innerWidth || document.documentElement.clientWidth,
						height: window.innerHeight || document.documentElement.clientHeight
					};
					return (
						rect.top >= 0 &&
						rect.left >= 0 &&
						rect.top <= viewport.height &&
						rect.left <= viewport.width
					);
				},
				handleFloatSaveVisibility: function () {
					if ( postTypeSaving.isInViewport( postTypeSaving.dom.save ) ) {
						postTypeSaving.dom.floatSave.removeClass( 'visible' );
					} else {
						postTypeSaving.dom.floatSave.addClass( 'visible' );
					}
				},
				onSaveClick:               function ( e ) {
					var validation = {
						is_valid: true
					};

					$( document ).trigger( 'yith_ywar_post_validation', [ validation ] );

					$( '.yith-plugin-fw-field-wrapper' )
						.removeClass( 'is_required' )
						.find( 'span.warning' )
						.remove();

					if ( ! validation.is_valid ) {
						var items = validation.failed.length;
						for ( var i = 0; i < items; i++ ) {
							validation.failed[i]
								.closest( '.yith-plugin-fw-field-wrapper' )
								.addClass( 'is_required' )
								.append( '<span class="warning">' + ywar_admin.messages.required_field + '</span>' );
						}
						e.preventDefault();
						return;
					}

					$( window ).off( 'beforeunload.edit-post' );

					$( this ).block(
						{
							message:    null,
							overlayCSS: {
								background: 'transparent',
								opacity:    0.6
							}
						}
					);
				},
				onFloatSaveClick:          function () {
					postTypeSaving.dom.save.trigger( 'click' );
				}
			};

			postTypeSaving.init();
		}

		var array_unique_noempty = function ( array ) {
			var out = [];

			$.each(
				array,
				function ( key, val ) {
					val = val.trim();

					if ( val && $.inArray( val, out ) === -1 ) {
						out.push( val );
					}
				}
			);

			return out;
		};
		var element_box          = {
			clean:       function ( tags ) {
				tags = tags.replace( /\s*,\s*/g, ',' ).replace( /,+/g, ',' ).replace( /[,\s]+$/, '' ).replace( /^[,\s]+/, '' );
				return tags;
			},
			parseTags:   function ( el ) {
				var id             = el.id,
					num            = id.split( '-check-num-' )[1],
					element_box    = $( el ).closest( '.yith-ywar-analytics-terms-div' ),
					values         = element_box.find( '.yith-ywar-analytics-terms-values' ),
					current_values = values.val().split( ',' ),
					new_elements   = [];

				delete current_values[num];

				$.each(
					current_values,
					function ( key, val ) {
						if ( val ) {
							val = val.trim();
							new_elements.push( val );
						}
					}
				);

				values.val( this.clean( new_elements.join( ',' ) ) );

				this.quickClicks( element_box );
				return false;
			},
			quickClicks: function ( el ) {

				var values      = $( '.yith-ywar-analytics-terms-values', el ),
					values_list = $( '.yith-ywar-analytics-terms-value-list ul', el ),
					id          = $( el ).attr( 'id' ),
					current_values;

				if ( ! values.length ) {
					return;
				}

				current_values = values.val().split( ',' );
				values_list.empty();

				$.each(
					current_values,
					function ( key, val ) {

						var item,
							xbutton;

						if ( ! val ) {
							return;
						}
						val = val.trim();

						item    = $( '<li class="select2-selection__choice" />' );
						xbutton = $( '<span id="' + id + '-check-num-' + key + '" class="select2-selection__choice__remove" tabindex="0"></span>' );

						xbutton.on(
							'click keypress',
							function ( e ) {

								if ( e.type === 'click' || e.keyCode === 13 ) {

									if ( e.keyCode === 13 ) {
										$( this ).closest( '.yith-ywar-analytics-terms-div' ).find( 'input.yith-ywar-analytics-terms-insert' ).focus();
									}

									element_box.parseTags( this );
								}

							}
						);

						item.prepend( val ).prepend( xbutton );

						values_list.append( item );

					}
				);
			},
			flushTags:   function ( el, a, f ) {
				var current_values,
					new_values,
					text,
					values  = $( '.yith-ywar-analytics-terms-values', el ),
					add_new = $( 'input.yith-ywar-analytics-terms-insert', el );

				a = a || false;

				text = a ? $( a ).text() : add_new.val();

				if ( 'undefined' === typeof (text) ) {
					return false;
				}

				current_values = values.val();
				new_values     = current_values ? current_values + ',' + text : text;
				new_values     = this.clean( new_values );
				new_values     = array_unique_noempty( new_values.split( ',' ) ).join( ',' );
				values.val( new_values );

				this.quickClicks( el );

				if ( ! a ) {
					add_new.val( '' );
				}
				if ( 'undefined' === typeof (f) ) {
					add_new.focus();
				}

				return false;

			},
			init:        function () {
				var ajax_div = $( '.yith-ywar-analytics-terms-ajax' );

				$( '.yith-ywar-analytics-terms-div' ).each(
					function () {
						element_box.quickClicks( this );
					}
				);

				$( 'input.yith-ywar-analytics-terms-insert', ajax_div )
					.on(
						'keyup',
						function ( e ) {
							if ( 13 === e.which ) {
								element_box.flushTags( $( this ).closest( '.yith-ywar-analytics-terms-div' ) );
								return false;
							}
						}
					)
					.on(
						'keypress',
						function ( e ) {
							if ( 13 === e.which ) {
								e.preventDefault();
								return false;
							}
						}
					);
			}
		};

		element_box.init();

		$( '#bulk-action-selector-top, #bulk-action-selector-bottom' ).select2(
			{
				minimumResultsForSearch: Infinity
			}
		);
	}
)( jQuery );
