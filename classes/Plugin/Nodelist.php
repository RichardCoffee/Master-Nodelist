<?php
/*
 * handles exporting tech nodelist to spreadsheet
 *
 */
class WMN_Plugin_Nodelist {

	protected $file_template = '/uploads/2018/04/export-template.xlsx';
	protected $name_template = 'St. Croix_Daily_Crew%tech-%loca_%date.xlsx';
	protected $writer;       # \PhpOffice\PhpSpreadsheet\Writer\Xlsx

	public function __construct() {
		$this->query = new WMN_Query_Nodelist();
	}

	public function export_nodelist() {
		$data = $this->query->retrieve_tech_entries();
		if ( ! empty( $data ) ) {
			$this->create_spreadsheet();
#			$this->write_spreadsheet( $data );
#			$this->save_spreadsheet();
#			$this->email_spreadsheet();
		}
	}

	protected function create_spreadsheet() {

		$template      = WP_CONTENT_DIR . $this->file_template;
		$tech_data     = array(
			WMN_Query_Nodelist::$tech_id,
			'ROOM203', // get_user_meta( get_current_user_id(), 'tech_location', true ),
			date( 'm-d-y' )
		);
		$filename = get_temp_dir() . str_replace( [ '%tech', '%loca', '%date' ], $tech_data, $this->name_template );

		echo "<p>template: $template</p>";
		echo "<p>filename: $filename</p>";
		$system = new WP_Filesystem_Direct;
		$system->copy( $template, $filename, true );
#		copy $template to $filename;

	}

	protected function write_spreadsheet( $data ) {
#		$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template.xlsx');

#		$worksheet = $spreadsheet->getActiveSheet();

#		$worksheet->getCell('A1')->setValue('John');
#		$worksheet->getCell('A2')->setValue('Smith');

#		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
#		$writer->setPreCalculateFormulas(false);
#		$writer->save("05featuredemo.xlsx");
	}

	protected function save_spreadsheet() { }
	protected function email_spreadsheet() { }

}
