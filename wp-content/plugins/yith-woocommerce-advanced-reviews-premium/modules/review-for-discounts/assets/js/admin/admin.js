/**
 * Admin JS scripts
 *
 * @package YITH\AdvancedReviews\Modules\ReviewForDiscounts\Assets\JS\Admin
 */

(
	function ( $ ) {

		$( document )
			.on(
				'change',
				'#trigger',
				function () {
					if ( $( this ).val() === 'multiple' ) {
						$( '#trigger_enable_notify' ).trigger( 'change' );
						$( '#trigger_threshold' ).trigger( 'change' );
					} else {
						$( '.trigger_threshold_notify' ).hide();
					}
				}
			)
			.on(
				'change',
				'#trigger_threshold',
				function () {
					if ( $( '#trigger_enable_notify' ).is( ':checked' ) ) {
						var value     = ($( this ).val() !== '') ? parseInt( $( this ).val() ) : 1,
							threshold = $( '#trigger_threshold_notify' );

						threshold.prop( 'max', ((value > 1) ? value - 1 : 1) );

						if ( threshold.val() > threshold.prop( 'max' ) ) {
							threshold.val( threshold.prop( 'max' ) );
						}
					}
				}
			);

		$( '#trigger' ).trigger( 'change' );
		$( '#trigger_threshold' ).trigger( 'change' );

	}
)( jQuery );
