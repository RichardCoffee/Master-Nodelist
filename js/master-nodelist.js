

//jQuery( document ).ready( function( $ ) {
//} );

function load_nodelist() {
	var outdata = {
		action: "wmn_show_nodelist",
		active: jQuery( "#active_node option:selected" ).text()
	};
	var data = contact_server( outdata );
	jQuery( '#master-nodelist' ).html( data );
}

function contact_server( outData, wait ) {
  var aSync = !wait;
  var value = false;
  jQuery.ajax({
    url:   nodelist_ajax.ajaxurl,
    type: 'post',
    data:  outData,
    async: aSync, // wtf chrome?  aSync, // jQuery default is true
    success: function(result,textStatus,jqXHR) {
      value = true;
      if (result) {
        try { // was json returned?
          value = JSON.parse(result);
        } catch (e) { // assume a string was returned
          value = result;
          alert(value);
        }
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



}
