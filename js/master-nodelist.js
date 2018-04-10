

//jQuery( document ).ready( function( $ ) {
//} );

function load_nodelist() {
console.log(jQuery( "#active_nodelist" );
console.log(jQuery( "#active_nodelist option:selected" );
	var text = jQuery( "#active_nodelist option:selected" ).text();
	jQuery( '#master-nodelist' ).html( '<h1>Node selected was '+text+'</h1>' );
//	jQuery( '#master-nodelist' ).html( '<h1>A node was selected</h1>' );
}
