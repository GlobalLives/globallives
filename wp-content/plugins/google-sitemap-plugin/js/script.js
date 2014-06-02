(function($) {
	$(document).ready( function() {
		$( '#gglstmp_auth input' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) == 'checkbox' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#gglstmp_settings_notice' ).css( 'display', 'block' );
			};
		});		
	});
})(jQuery);