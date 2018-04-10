

//jQuery( document ).ready( function( $ ) {
//} );

function load_nodelist() {
	var outdata = {
		action: "wmn_show_nodelist",
		active: jQuery( "#active_node option:selected" ).val()
	};
	contact_server( '#master-nodelist', outdata );
}

function contact_server( contentDiv, outData, wait ) {
	var aSync = !wait;
	var value = false;
	jQuery.ajax({
		url:   nodelist_ajax.ajaxurl,
		type: 'post',
		data:  outData,
		async: aSync, // wtf chrome?  aSync, // jQuery default is true
		success: function(result,textStatus,jqXHR) {
			if (result) {
				jQuery(contentDiv).html(result);
				value = true;
			}
		},
		error: function(jqXHR,textStatus,errorThrown) {
			alert('Server Error: '+errorThrown+' '+textStatus);
			console.log('server error: '+errorThrown);
			console.log(nodelist_ajax.ajaxurl);
			console.log(outData);
		}
	});
	return value;
}
