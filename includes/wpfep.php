<?php


function wmn_wpfep_tech_tab( $tabs = array() ) {
	$tabs[] = array(
		'id'            => 'tech_tab',
		'label'         => __('Tech Info','wmn-workbook'),
		'tab_class'     => 'tech-tab',
		'content_class' => 'tech-content',
	);
	return $tabs;
}
add_filter('wpfep_tabs','wmn_wpfep_tech_tab',15);

function wmn_wpfep_tech_tab_meta_fields( $fields = array() ) {
	$fields[] = array(
		'id'      => 'tech_id',
		'label'   => __('Tech ID','wmn-workbook'),
		'desc'    => __('Your tech id number for spreadsheet.','wmn-workbook'),
		'type'    => 'text',
		'classes' => 'tech-id-field'
	);
	$fields[] = array(
		'id'      => 'tech_location',
		'label'   => __('Tech Location','wmn-workbook'),
		'desc'    => __('Your location as it will appear in the exported spreadsheet filename.  example:  ROOM203','wmn-workbook'),
		'type'    => 'text',
		'classes' => 'tech-location-field'
	);
	return $fields;
}
add_filter('wpfep_fields_tech_tab','wmn_wpfep_tech_tab_meta_fields');

function wmn_current() {
	return ( in_array( get_current_user_id(), [ 2, 3 ] ) );
}
