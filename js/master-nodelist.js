

//jQuery( document ).ready( function( $ ) {
//} );

function load_nodelist( page, scroll ) {
	var outdata = {
		action:  "wmn_show_nodelist",
		active:   jQuery( "#active_node option:selected" ).val(),
		nodepage: page,
		security: nodelist_ajax.security
	};
	contact_server( '#master-nodelist', outdata );
	if ( scroll ) {
		scrollToElement( '#master-nodelist' );
	}
}

function contact_server( contentDiv, outData, wait ) {
	var aSync = !wait;
	var value = false;
	jQuery.ajax({
		url:   nodelist_ajax.ajaxurl,
		type: 'post',
		data:  outData,
		async: aSync, // wtf chrome?  aSync, // jQuery default is true
		success: function( result, textStatus, jqXHR ) {
			if ( result ) {
				jQuery( contentDiv ).html( result );
				value = true;
			}
		},
		error: function( jqXHR, textStatus, errorThrown ) {
			alert( 'Server Error: ' + errorThrown + ' ' + textStatus );
			console.log( 'server error: ' + errorThrown );
			console.log( nodelist_ajax.ajaxurl );
			console.log( outData );
		}
	});
	return value;
}

function pick_entry( el, id ) {
	var outData = {
		action:  'wmn_pick_entry',
		active:   jQuery( "#active_node option:selected" ).val(),
		entry:    id,
		security: nodelist_ajax.security
	}
	contact_server( '#tech-editlist', outData );
}
