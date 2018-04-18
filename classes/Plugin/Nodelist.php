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
	protected $name_template = 'St. Croix_Daily_Crew%tech-%loca_%date.xlsx';
	protected $subject       = 'Daily';
	protected $writer;       # \PhpOffice\PhpSpreadsheet\Writer\Xlsx

	public function __construct() {
		$this->query = new WMN_Query_Nodelist;
		add_action( 'wp_mail_failed',       [ $this, 'wp_mail_failed' ] );
		add_filter( 'wp_mail',              [ $this, 'wp_mail' ] );
		add_filter( 'wp_mail_from',         [ $this, 'wp_mail' ] );
		add_filter( 'wp_mail_from_name',    [ $this, 'wp_mail' ] );
#		add_filter( 'wp_mail_content_type', [ $this, 'wp_mail' ] );
#		add_filter( 'wp_mail_charset',      [ $this, 'wp_mail' ] );
	}

	public function export_nodelist() {
		$data = $this->query->retrieve_tech_entries();
		if ( ! empty( $data ) ) {
			$this->create_spreadsheet( $data[ ( count( $data ) -1 ) ][21] ); // TODO: extract index from TCC_Query_Nodelist
			$this->write_spreadsheet( $data );
#			$this->save_spreadsheet();
			$this->email_spreadsheet();
		}
	}

# https://wordpress.stackexchange.com/questions/243261/right-way-to-download-file-from-source-to-destination
	protected function create_spreadsheet( $date ) {
		$template      = WP_CONTENT_DIR . $this->file_template;
		$tech_data     = array(
			WMN_Query_Nodelist::$tech_id, // get_user_meta( get_current_user_id(), 'tech_id', true ),
			'ROOM203', // get_user_meta( get_current_user_id(), 'tech_location', true ),
			date( 'm-d-y' ) // TODO: extract date from nodelist data
		);
		$this->filename = get_temp_dir() . str_replace( [ '%tech', '%loca', '%date' ], $tech_data, $this->name_template );

		echo "<p>template: $template</p>";
		echo "<p>filename: {$this->filename}</p>";

		$system = new WP_Filesystem_Direct( array() );
		$system->copy( $template, $filename, true );
	}

	protected function write_spreadsheet( $data ) {
#		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $this->filename );

#		$worksheet = $spreadsheet->getActiveSheet();

#		$worksheet->getCell('A1')->setValue('John');
#		$worksheet->getCell('A2')->setValue('Smith');

#		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
#		$writer->setPreCalculateFormulas(false);
#		$writer->save("05featuredemo.xlsx");
	}

#	protected function save_spreadsheet() { }

	protected function email_spreadsheet() {
		$tech = get_userdata( get_current_user_id() );
		$to   = $tech->user_email;
		$message = 'Enclosed:  One spreadsheet';
		$headers = array(
			'From' => "{$this->from_name} <{$this->from_email}>", // get_bloginfo('admin_email'),
			'Reply-To' => 'richard.coffee@gmail.com'
		);
		if ( wp_mail( $to, $this->subject, $message, $headers, [ $this->filename ] ) ) {
			$system = new WP_Filesystem_Direct( array() );
			$system->delete( $this->filename );
		}
	}

	public function wp_mail_failed( WP_Error $err ) {
		wmn(1)->log( $err );
	}

	public function wp_mail( $args ) {
/*		static $track = false;
		if ( $track ) {
			if ( $args === 'wordpress@workbook.jamesgaither.online' ) {
				return 'nodelist@workbook.jamesgaither.online';
			}
			if ( $args === 'WordPress' ) {
				$track = false;
				return 'Nodelist Online';
			}
		}
		if ( is_array( $args ) && isset( $args['subject'] ) && ( $args['subject'] === $this->subject ) ) {
			$track = true;
		} //*/
		wmn(1)->log( $track, $args );
		return $args;
	}

}
