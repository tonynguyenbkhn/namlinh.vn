/**
 * AJAX scripts
 *
 * @var jQuery
 * @var ywar_admin
 * @package YITH\AdvancedReviews\Assets\JS\Admin
 */

// Make sure the ywar object exists.
window.ywar = window.ywar || {};

(
	function ( $, ywar ) {

		ywar.adminAjax = function ( data, options ) {
			data          = typeof data !== 'undefined' ? data : {};
			options       = typeof options !== 'undefined' ? options : {};
			data.action   = ywar_admin.adminAjaxAction;
			data.security = ywar_admin.nonces.adminAjax;

			if ( 'block' in options ) {
				options.block.block( ywar_admin.blockParams );
			}

			return $.ajax(
				{
					type    : 'POST',
					data    : data,
					url     : ywar_admin.ajaxurl,
					complete: function () {
						if ( 'block' in options ) {
							options.block.unblock();
						}
					}
				}
			);
		};

		ywar.Error = function () {
			Error.apply( this, arguments );
			this.name = "This is not a real error, it just stops the execution if an input is wrong.";
		};

		Error.prototype = Object.create( Error.prototype );

	}
)( jQuery, window.ywar );
