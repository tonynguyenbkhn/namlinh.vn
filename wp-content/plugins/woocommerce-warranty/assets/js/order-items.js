document.addEventListener( 'DOMContentLoaded', function () {
	const btn_request = document.querySelector( '#wcContent input.warranty-button' );

	if ( btn_request ) {
		btn_request.addEventListener( 'click', function ( evt ) {
			evt.preventDefault();

			const checked_items = document.querySelectorAll( '.chk-order-item:checked' );

			if ( checked_items.length < 1 ) {
				alert( WC_Warranty_Order_Items_var.no_item_selected );
				return;
			}
			const current_form = this.closest( 'form' );

			if ( current_form ) {
				current_form.submit();
			}
		} );
	}
} );