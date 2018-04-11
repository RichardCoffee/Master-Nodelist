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
	return $fields;
}
add_filter('wpfep_fields_tech_tab','wmn_wpfep_tech_tab_meta_fields');

function wmn_current() {
	return ( get_current_user_id() == 2 ) ? true : false;
}
