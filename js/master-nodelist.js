

//jQuery( document ).ready( function( $ ) {
//} );

function load_nodelist() {
console.log(jQuery( "#active_node" ) );
console.log(jQuery( "#active_node option:selected" ) );
	var text = jQuery( "#active_node option:selected" ).text();
	jQuery( '#master-nodelist' ).html( '<h1>Node selected was '+text+'</h1>' );
//	jQuery( '#master-nodelist' ).html( '<h1>A node was selected</h1>' );
}
