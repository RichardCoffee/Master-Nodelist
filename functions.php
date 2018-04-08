<?php

defined( 'ABSPATH' ) || exit;

function wmn_workbook_class_loader( $class ) {
	if ( substr( $class, 0, 4 ) === 'WMN_' ) {
		$load = str_replace( '_', '/', substr( $class, ( strpos( $class, '_' ) + 1 ) ) );
		$file = WMN_WORKBOOK_DIR . '/classes/' . $load . '.php';
		if ( is_readable( $file ) ) {
			include $file;
		}
	}
}
spl_autoload_register( 'wmn_workbook_class_loader' ); //*/

function wmn() {
	static $library;
	if ( empty( $library ) ) {
		$library = new WMN_Plugin_Library;
	}
	return $library;
}
wmn();

# http://stackoverflow.com/questions/14348470/is-ajax-in-wordpress
if ( ! function_exists( 'is_ajax' ) ) {
	function is_ajax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? true : false;
	}
}
session_start();
wmn()->log( $_SESSION );
