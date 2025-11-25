/**
 * Unsubscribe JS scripts
 *
 * @package YITH\AdvancedReviews\Modules\RequestReview\Assets\JS
 */

(
	function ( $ ) {

		$( document )
			.on(
				'click',
				'.yith-ywar-unsubscribe',
				function () {
					const form = $( '.yith-ywar-unsubscribe-form' );

					ywar
						.ajax(
							{
								user_id:    $( '#account_id' ).val(),
								email:      $( '#account_email' ).val(),
								email_hash: $( '#email_hash' ).val(),
								request:    'unsubscribe_user',
							},
							{block: form}
						)
						.done(
							function ( response ) {
								// Remove old errors.
								$( '.woocommerce-error, .woocommerce-message' ).remove();
								if ( response.success ) {
									form.find( 'div' ).hide();
									$( '.return-to-shop' ).show();
								}

								form.prepend( response.data );

							}
						);
				}
			);

	}
)( jQuery );
