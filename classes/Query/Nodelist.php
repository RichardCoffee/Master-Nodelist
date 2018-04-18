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

	public static $tech_id = null;

	public function __construct() {
		if ( empty( self::$tech_id ) ) {
			self::$tech_id = get_user_meta( get_current_user_id(), 'tech_id', true );
		}
	}

	public function base_headers() {
		$base = array(
			 0 => 'account',
			 1 => 'house',
			 2 => 'ticket',
			 3 => 'name',
			 4 => 'type',
			 5 => 'phone',
			 6 => 'node',
			 7 => 'descrip',
			 8 => 'address',
			10 => 'viya',
			11 => 'subscriber',
			18 => 'crew',
			20 => 'install',
			21 => 'complete',
			28 => 'comments',
		);
		return $base;
	}

	public function entry_fields() {
		return array( 'viya', 'subscriber', 'install', 'complete', 'comments' );
	}

	public function subscript( $search ) {
		$fields = $this->base_headers();
		return array_search( $search, $fields, true );
	}

	public function top_header() {
		return 'Node Information Detail';
	}

	public function proper_headers() {
		return array(
			'Account #', // 0
			'House ID',
			'Ticket#',
			'Account Name',
			'Account Type',
			'Phone',
			'Node',
			'Node Description',
			'Service Address',
			'Island',
			'Viya Tag', // 10
			'Subscriber GPS Tag',
			'Tagging Company Assigned To:',
			'Tagging Crew ID',
			'Date Tag Assigned to Tech',
			'Date Tagged',
			'Coax Type',
			'Company Drop Assigned to',
			'Drop Install Crew ID',
			'Date: Drop Assigned to Crew',
			'Drop Installed Yes/ Not Required', // 20
			'Drop Complete Date',
			'Install Company Assigned',
			'Install Crew Assigned',
			'Install Date Assigned',
			'Install Yes / Not Required',
			'Install Complete',
			'Docsis 3.0 Upgrade Y / N',
			'Comments/Notes', // 28
		);
	}

	public function header_title( $search ) {
		$title = '';
		$subscript = $this->subscript( $search );
		if ( $subscript ) {
			$titles = $this->proper_headers();
			$title  = $titles[ $subscript ];
		}
		return $title;
	}

	public function create( $file = 'workbook_nodelist' ) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $file ( id int(11) NOT NULL AUTO_INCREMENT,";
		$headers = $this->base_headers();
		foreach( $headers as $header ) {
			$sql .= "`$header` text,";
		}
		$sql .= "`insertionDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id)";
		$sql .= " ) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function destroy( $file = 'workbook_nodelist' ) {
		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$file'") === $file) {
			$wpdb->query( "DROP TABLE IF EXISTS $file" );
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
				if ( ! empty( $row[ $key ] ) ) {
					$record[ $col ] = $row[ $key ];
				}
			}
			if ( ( ! empty( $record ) ) && ( ! empty( $record['node'] ) ) ) {
				$wpdb->insert( 'workbook_nodelist', $record );
				$new++;
			}
		}
		return compact( 'dups', 'new' );
	}

	protected function is_duplicate( $data ) {
		$is_dup = false;
		$where  = array();
		$args   = array();
		foreach( array( 'account', 'house', 'ticket' ) as $key => $text ) {
			if ( ! empty( $data[ $key ] ) ) {
				$where[] = "$text = %s";
				$args[]  = $data[ $key ];
			}
		}
		if ( count( $where ) > 0 ) {
			global $wpdb;
			$sql    = "SELECT ID FROM workbook_nodelist WHERE " . implode( ' AND ', $where );
			$prep   = $wpdb->prepare( $sql, $args );
			$is_dup = $wpdb->get_var( $prep );
		}
		return $is_dup;
	}

	public function retrieve_entry( $id ) {
		global $wpdb;
		$entry = array();
		if ( (int) $id > 0 ) {
			$sql   = "SELECT * FROM workbook_nodelist WHERE id = %d";
			$prep  = $wpdb->prepare( $sql, $id );
			$entry =  $wpdb->get_row( $prep, ARRAY_A );
		}
		return $entry;
	}

	public function save_entry( $data ) {
		global $wpdb;
		if ( ! empty( $data['id'] ) ) {
			$id = $data['id'];
			unset( $data['id'] );
			$data['crew'] = self::$tech_id;
			$update = $wpdb->update( 'workbook_nodelist', $data, [ 'id' => $id ] );
			if ( $update === false ) {
				wmn(1)->log( 'ERROR occurred updating dbf record', "id: $id", $data );
			}
		}
	}

	public function retrieve_tech_entries() {
		if ( ! empty( self::$tech_id ) ) {
			global $wpdb;
			$sql  = "SELECT * FROM workbook_nodelist WHERE crew = %s ORDER BY node";
			$prep = $wpdb->prepare( $sql, self::$tech_id );
			$data = $wpdb->get_results( $prep, ARRAY_A );
			return $data;
		}
		return false;
	}

	public function check_duplicate( $entry ) {
		global $wpdb;
		$sql = "SELECT * FROM workbook_nodelist WHERE crew = %s AND address = %s ORDER BY node";
		$prep = $wpdb->prepare( $sql, self::$tech_id, $entry['address'] );
		$data = $wpdb->get_results( $prep, ARRAY_A );
		if ( ! empty( $data ) ) {
			$cols = $this->entry_fields();
			foreach( $data as $item ) {
				foreach( $cols as $col ) {
					if ( empty( $entry[ $col ] ) && ! empty( $item[ $col ] ) ) {
						$entry[ $col ] = $item[ $col ];
					}
				}
			}
		}
		return $entry;
	}

	public function remove_tech_entries( $data ) {
		$removals = $this->extract_field( 'id', $data );
wmn(1)->log($removals);
	}

	protected function extract_field( $field, $data ) {
		$extracted = array();
		foreach( $data as $item ) {
			if ( ! empty( $item[ $field ] ) ) {
				$extracted[] = $item[ $field ];
			}
		}
		return $extracted;
	}


}
