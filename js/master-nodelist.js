

jQuery( document ).ready( function( $ ) {

	jQuery( '#active_nodelist' ).on( 'change', function( event ) {
		var text = jQuery( "#active_nodelist option:selected" ).val();
		jQuery( '#master-nodelist' ).html( '<h1>Node selected was '+text+'</h1>' );
	}

} );
