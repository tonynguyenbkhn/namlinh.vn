jQuery( 'document' ).ready( function( $ ) {
	$( '#warranty_enable_coupon_requests' ).change( function() {
		var show_if_coupon_enabled = $( '.show-if-coupon-requests-enabled' );

		show_if_coupon_enabled.parents( 'tr' ).hide();

		switch ( $( this ).val() ) {

			case 'yes':
				show_if_coupon_enabled.parents( 'tr' ).show();
				break;

		}
	} ).change();
} );