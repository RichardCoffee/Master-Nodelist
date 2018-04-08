<?php
/**
 * Workbook Node List
 *
 * @package   Workbook Node List
 * @author    Richard Coffee <richard.coffee@rtcenterprises.net>
 * @copyright 2018 Richard Coffee <richard.coffee@rtcenterprises.net>
 * @license   GPLv2  <need uri here>
 * @link      rtcenterprises.net
 *
 * @wordpress-plugin
 * Plugin Name:       Workbook Node List
 * Plugin URI:        rtcenterprises.net
 * Description:       Import and interact with the master node list spreadsheet
 * Version:           1.0.0
 * Requires at least: 4.7.0
 * Requires WP:       4.7.0
 * Tested up to:      4.9.5
 * Requires PHP:      5.6.0
 * Author:            Richard Coffee
 * Author URI:        rtcenterprises.net
 * License:           GPLv2
 * Text Domain:       wmn-workbook
 * Domain Path:       /languages
 * Tags:              excel
 */

/**
 * check to see if wp is running
 *
 * @link https://github.com/helgatheviking/Nav-Menu-Roles/blob/master/nav-menu-roles.php
 */
if ( ! defined('ABSPATH') || ! function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'WMN_WORKBOOK_DIR' , plugin_dir_path( __FILE__ ) );

require_once( 'functions.php' );

$plugin = WMN_Plugin_Workbook::get_instance( array( 'file' => __FILE__ ) );

register_activation_hook( __FILE__, array( 'WMN_Register_Workbook', 'activate' ) );
