jQuery( document ).ready( function ( $ ) {
	$( '.toggle_search_form' ).click( function () {
		var search_form = $( '#search_form' );

		if ( search_form.is( ':visible' ) ) {
			$( this ).val( 'Show Search Form' );
			search_form.hide();
		} else {
			$( this ).val( 'Hide Search Form' );
			search_form.show();
		}
	} );

	$( '#search_key' ).change( function () {
		console.log( $( this ).val() );
		if ( 'order_id' === $( this ).val() ) {
			$( '#search_term' ).show();
			$( '.select2-container' ).hide();
			$( '#select2_search_term' )
				.removeClass( 'wc-user-search' )
				.removeClass( 'enhanced' )
				.select2( 'destroy' );
		} else {
			var select2_container = $( '.select2-container' );
			$( '#search_term' ).hide();
			select2_container.show();
			$( '#select2_search_term' ).addClass( 'wc-user-search' );
			$( 'body' ).trigger( 'wc-enhanced-select-init' );
			select2_container.attr( 'style', 'width: 400px; display: inline-block !important;' );
		}
	} ).change();

	$( '.help_tip' ).tipTip();

	$( '#search_form' ).submit( function () {
		if ( 'customer' === $( '#search_key' ).val() ) {
			$( '#search_term' ).val( $( '#select2_search_term' ).val() );
		}
	} );
} );