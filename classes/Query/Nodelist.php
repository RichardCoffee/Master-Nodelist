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
		$base = array(
			'account',
			'house',
			'ticket',
			'name',
			'type',
			'phone',
			'node',
			'descrip',
			'address',
			10 => 'viya',
			11 => 'subscriber',
			18 => 'install',
			19 => 'complete',
			28 => 'comments',
		);
		return $base;
	}

	public function top_header() {
		return 'Node Information Detail';
	}

	public function proper_headers() {
		return array(
			'Account #',
			'House ID',
			'Ticket#',
			'Account Name',
			'Account Type',
			'Phone',
			'Node',
			'Node Description',
			'Service Address',
			'Island',
			'Viya Tag',
			'Subscriber GPS Tag',
			'Tagging Company Assigned To:',
			'Tagging Crew ID',
			'Date Tag Assigned to Tech',
			'Date Tagged',
			'Coax Type',
			'Company Drop Assigned to',
			'Drop Install Crew ID',
			'Date: Drop Assigned to Crew',
			'Drop Installed Yes/ Not Required',
			'Drop Complete Date',
			'Install Company Assigned',
			'Install Crew Assigned',
			'Install Date Assigned',
			'Install Yes / Not Required',
			'Install Complete',
			'Docsis 3.0 Upgrade Y / N',
			'Comments/Notes',
		);
	}

	public function create() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE workbook_nodelist ( id int(11) NOT NULL AUTO_INCREMENT,";
		$headers = $this->base_headers();
		foreach( $headers as $header ) {
			$sql .= "`$header` text,";
		}
		$sql .= "`insertionDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)";
		$sql .= " ) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function destroy() {
		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE 'workbook_nodelist'") === 'workbook_nodelist') {
			$delete = $wpdb->query( "DROP TABLE IF EXISTS workbook_nodelist" );
		}
	}

	public function import( $data ) {
		global $wpdb;
$dups = $new = 0;
		$columns = $this->base_headers();
		foreach( $data as $index => $row ) {
			$record = array();
			if ( $index < 2 ) {
				continue;
			}
			if ( $this->is_duplicate( $row ) ) {
$dups++;
				continue;
			}
			foreach( $columns as $key => $col ) {
				if ( $row[ $key ] ) {
					$record[ $col ] = $row[ $key ];
				}
			}
$new++;
			$wpdb->insert( 'workbook_nodelist', $record );
		}
wmn(1)->log(
	"Dups: $dups",
	" New: $new"
);
		return true;
	}

	protected function is_duplicate( $data ) {
		global $wpdb;
		$sql    = "SELECT ID FROM workbook_nodelist WHERE account = %s AND house = %s AND ticket = %s";
		$prep   = $wpdb->prepare( $sql, $data[0], $data[1], $data[2] );
		$exists = $wpdb->get_var( $prep );
		return $exists;
	}


}
