<?php
/**
 * classes/Query/Nodelist.php
 *
 * @since 20180408
 */
/**
 * handles database tasks
 *
 */
class WMN_Query_Nodelist {

	public function __construct() {
#		global $wpdb;
	}

	public function base_headers() {
		return array(
			'account',
			'house',
			'ticket',
			'name',
			'type',
			'phone',
			'node',
			'desc',
			'address',
		);
	}

	public function create() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE workbook_nodelist ( id int(11) NOT NULL AUTO_INCREMENT,";
		$headers = $this->base_headers();
		$headers[] = 'tech';
		foreach ( $headers as $header ) {
			$sql .= "'$header' text,";
		}
		$sql .= "'insertionDate' datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)";
		$sql .= " ) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function destroy() {
		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE 'workbook_nodelist'") === 'workbook_nodelist') {
			$delete = $wpdb->query( "DROP TABLE IF EXISTS 'workbook_nodelist'" );
		}
	}

}
