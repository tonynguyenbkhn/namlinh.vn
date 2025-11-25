jQuery( document ).ready( function( $ ) {

	const handle_recipient_select = function( evt ) {
		const cmb_recipient = jQuery( evt.currentTarget );
		const recipient = cmb_recipient.val();

		const parent = cmb_recipient.closest( 'td' );
		const search_container = parent.find( '.search-container-label, .search-container' );
		search_container.removeClass( 'show-if-admin-selected' );

		if ( 'both' === recipient || 'admin' === recipient ) {
			search_container.addClass( 'show-if-admin-selected' );
		}
	}

	const handle_delete_row = function ( e ) {
		e.preventDefault();

		$( e.currentTarget ).parents( 'tr' ).remove();
	}

	$( '.recipient-select' ).on( 'change', handle_recipient_select );

	$( '.add-email' ).click( function( e ) {
		e.preventDefault();

		var idx = 1;

		while ( $( '#email_' + idx ).length > 0 ) {
			idx ++;
		}

		var src = $( '#email-row-template tbody' ).html();
		src = src.replace( /_id_/g, idx );
		// Need to replace noenhance with empty string, otherwise Select2 will be initialized for the template.
		src = src.replace( /_noenhance_/g, '' );

		$( '#emails_tbody' ).append( src );
		$( '#emails_tbody #email_' + idx ).find( '.delete-row' ).on( 'click', handle_delete_row );

		const cmb_recipient = $( '.recipient-select' );
		cmb_recipient.off( 'change', handle_recipient_select );
		cmb_recipient.on( 'change', handle_recipient_select );

		$( 'body' ).trigger( 'wc-enhanced-select-init' );
	} );

	$( '.delete-row' ).on( 'click', handle_delete_row );

	$( '#emails_tbody' ).on( 'change', '.trigger', function() {
		var tr = $( this ).closest( 'tr' );

		if ( 'status' === $( this ).val() ) {
			$( tr ).find( '.trigger_status' ).show();
		} else {
			$( tr ).find( '.trigger_status' ).hide();
		}
	} );
	$( '.trigger' ).change();
} );