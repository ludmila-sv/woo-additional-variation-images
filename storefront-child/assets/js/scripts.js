( function( $ ) {
	if ( $( '.variation-color__btn' ).length > 0 ) {
		$( '.variation-color__btn input' ).each( function(){
			if ( $( this ).val() == $('#pa_color').val() ) {
				$( this ).prop('checked', true);
			} else {
				$( this ).prop('checked', false);
			}
		} );
		$( '.variation-color__btn input' ).on( 'change', function() {
			if ( $( this ).is(':checked') ) {
				const color = $( this ).val();
				$('#pa_color option').prop('selected', false);
				$('#pa_color option[value='+color+']').prop('selected', true);
				$('#pa_color').trigger('change');
			}
		} );
	}
} )( jQuery );