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

function wmn( $force_log = false ) {
	static $library;
	if ( empty( $library ) ) {
		$library = new WMN_Plugin_Library;
	}
	if ( $force_log ) {
		$library->logging_force = true;
	}
	return $library;
}

if ( ! function_exists( 'wmn_paths' ) ) {
	function wmn_paths() {
		static $instance = null;
		if ( empty( $instance ) ) {
			$instance = WMN_Plugin_Paths::instance();
		}
		return $instance;
	}
}

# http://stackoverflow.com/questions/14348470/is-ajax-in-wordpress
if ( ! function_exists( 'is_ajax' ) ) {
	function is_ajax() {
		return ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? true : false;
	}
}

function wmn_plugin_load_first() {
	$path = str_replace( WP_PLUGIN_DIR . '/', '', wmn_paths()->file );
	if ( $plugins = get_option( 'active_plugins' ) ) {
		if ( $key = array_search( $path, $plugins ) ) {
			array_splice( $plugins, $key, 1 );
			array_unshift( $plugins, $path );
			update_option( 'active_plugins', $plugins );
		}
	}
}
add_action( 'activated_plugin', 'wmn_plugin_load_first' );
