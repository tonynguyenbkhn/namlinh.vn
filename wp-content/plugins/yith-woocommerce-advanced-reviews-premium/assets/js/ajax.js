/**
 * AJAX scripts
 *
 * @var jQuery
 * @var ywar_frontend
 * @package YITH\AdvancedReviews\Assets\JS
 */

// Make sure the ywar object exists.
window.ywar = window.ywar || {};

(
	function ( $, ywar ) {

		ywar.ajax = function ( data, options ) {
			data    = typeof data !== 'undefined' ? data : {};
			options = typeof options !== 'undefined' ? options : {
				processData: true,
				contentType: 'application/x-www-form-urlencoded'
			};
			if ( data instanceof FormData ) {
				data.append( 'action', ywar_frontend.frontendAjaxAction );
				data.append( 'context', 'frontend' );
			} else {
				data.action  = ywar_frontend.frontendAjaxAction;
				data.context = 'frontend';
			}

			if ( 'block' in options ) {
				options.block.block( ywar_frontend.blockParams );
			}

			return $.ajax(
				{
					type:        'POST',
					data:        data,
					processData: options.processData,
					contentType: options.contentType,
					url:         ywar_frontend.ajaxurl,
					complete:    function () {
						if ( 'block' in options ) {
							options.block.unblock();
						}
					}
				}
			);
		};

	}
)( jQuery, window.ywar );
