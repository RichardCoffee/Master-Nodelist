<?php
/**
 * classes/Plugin/Nodelist.php
 *
 */
require_once( ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php' );
require_once( ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php' );
/*
 * handles exporting tech nodelist to spreadsheet
 *
 */
class WMN_Plugin_Nodelist {

	protected $file_template = '/uploads/2018/04/export-template.xlsx';
	protected $filename;
	protected $from_email    = 'nodelist@workbook.jamesgaither.online';
	protected $from_name     = 'Nodelist Online';
	protected $message       = 'Enclosed:  One spreadsheet';
	protected $name_template = 'St. Croix_Daily_Crew%tech-%loca_%date.xlsx';
	protected $reply_to      = 'richard.coffee@gmail.com';
	protected $subject       = 'Daily';
	protected $writer;       # \PhpOffice\PhpSpreadsheet\Writer\Xlsx

	public function __construct() {
		$this->query = new WMN_Query_Nodelist;
		add_action( 'wp_mail_failed',       [ $this, 'wp_mail_failed' ] );
		add_filter( 'wp_mail',              [ $this, 'wp_mail_filter' ] );
		add_filter( 'wp_mail_from',         [ $this, 'wp_mail_filter' ] );
		add_filter( 'wp_mail_from_name',    [ $this, 'wp_mail_filter' ] );
#		add_filter( 'wp_mail_content_type', [ $this, 'wp_mail_filter' ] );
#		add_filter( 'wp_mail_charset',      [ $this, 'wp_mail_filter' ] );
		# TODO: check transient for files left from botched runs
	}

	public function export_nodelist() {
		$data = $this->query->retrieve_tech_entries();
		if ( ! empty( $data ) ) {
			$count = count( $data );
			$this->generate_filename( $data[ --$count ]['complete'] );
			$this->write_spreadsheet( $data );
			$this->email_spreadsheet();
		}
	}

# https://wordpress.stackexchange.com/questions/243261/right-way-to-download-file-from-source-to-destination
	protected function generate_filename( $date ) {
		$template  = WP_CONTENT_DIR . $this->file_template;
		$tech_data = array(
			WMN_Query_Nodelist::$tech_id, // get_user_meta( get_current_user_id(), 'tech_id', true ),
			'ROOM203', // get_user_meta( get_current_user_id(), 'tech_location', true ),
			date( 'm-d-y' ) // TODO: extract date from nodelist data
		);
		$this->filename = get_temp_dir() . str_replace( [ '%tech', '%loca', '%date' ], $tech_data, $this->name_template );

		echo "<p>template: $template</p>";
		echo "<p>filename: {$this->filename}</p>";

	}

	protected function write_spreadsheet( $data ) {
		require_once( wmn_paths()->dir . 'vendor/autoload.php' );
		$template    = WP_CONTENT_DIR . $this->file_template;
		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $template );
		$worksheet   = $spreadsheet->getActiveSheet();

wmn(1)->log( $data );

		$base_ref  = $this->query->base_headers();
		$fields    = $this->query->entry_fields();
		$excel_row = 3;
		foreach( $data as $entry ) {
			foreach( $entry as $key => $value ) {
				$column = array_search( $key, $base_ref );
				$cell   = $this->determine_column( $column ) . $excel_row;
wmn(1)->log("cell: $cell = $key / $value");
				$worksheet->getCell( $cell )->setValue( $value );
			}
			$excel_row++;
		}
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
		$writer->save( $this->filename );
		# TODO: set file transient
	}

	protected function determine_column( $offset ) {
		$col = ord('A') + $offset;
		if ( $col > ord('Z') ) {
			$offset = $offset - ( ord('Z') - ord('A') );
			return 'A' . $this->determine_column( $offset );
		}
		return chr( $col );
	}

	protected function email_spreadsheet() {
		$tech = get_userdata( get_current_user_id() );
		$to   = $tech->user_email;
		$headers = array(
			'From' => "{$this->from_name} <{$this->from_email}>", // get_bloginfo('admin_email'),
			'Reply-To' => $this->reply_to
		);
		if ( wp_mail( $to, $this->subject, $this->message, $headers, [ $this->filename ] ) ) {
			$system = new WP_Filesystem_Direct( array() );
			$system->delete( $this->filename );
			# TODO: remove file transient
		}
	}

	public function wp_mail_failed( WP_Error $err ) {
		wmn(1)->log( $err );
	}

	public function wp_mail_filter( $args ) {
		static $track = false;
		if ( $track && is_string( $args ) ) {
			if ( $args === 'wordpress@workbook.jamesgaither.online' ) {
				$args = $this->from_email;
			}
			if ( $args === 'WordPress' ) {
				$track = false;
				$args = $this->from_name;
			}
		}
		if ( is_array( $args ) && isset( $args['subject'] ) && ( $args['subject'] === $this->subject ) ) {
			$track = true;
		}
		return $args;
	}

}
