/**
 * Admin top bar scripts.
 *
 * @var jQuery
 * @var ywar_admin
 * @package YITH\AdvancedReviews\Assets\JS\Admin
 */

(
	function ( $ ) {
		check_messages();
		setInterval(
			check_messages,
			900000 // Refresh every 15 minutes.
		);

		function check_messages() {

			var container = $( '.yith-ywar-admin-bar' );

			container.block( ywar_admin.blockParams );
			$.ajax(
				{
					url:     window.location.href,
					success: function ( resp ) {
						if ( resp !== '' ) {
							var temp_content = $( "<div></div>" ).html( resp ),
								widget       = temp_content.find( '.yith-ywar-admin-bar' );
							$( '.yith-ywar-admin-bar' ).html( widget.html() );
							container.unblock()
						}
					}
				}
			);

		}
	}
)( jQuery );
