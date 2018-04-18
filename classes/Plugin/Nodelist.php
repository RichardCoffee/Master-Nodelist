<?php
/*
 * handles exporting tech nodelist to spreadsheet
 *
 */
class WMN_Plugin_Nodelist {

	protected $template = '/uploads/2018/04/export-template.xlsx';
	protected $writer; # \PhpOffice\PhpSpreadsheet\Writer\Xlsx

	public function __construct() {
#		$this->query = new WMN_Query_Nodelist();
	}

	public function export_nodelist() {
#		$data = $this->query->retrieve_tech_entries();
#		if ( ! empty( $data ) ) {
			$this->create_spreadsheet();
#			$this->write_spreadsheet( $data );
#			$this->save_spreadsheet();
#			$this->email_spreadsheet();
#		}
	}

	protected function create_spreadsheet() {


		$location = 'ROOM203';

		$tmp = get_temp_dir();
		$template = WP_CONTENT_DIR . $this->template;
		$list_name = 'St. Croix_Daily_Crew' . WMN_Query_Nodelist::$tech_id . '-' . $location . '_' . date( 'm-d-y' ) . '.xlsx';

		echo "<p>template: $template</p>";
		echo "<p>temp dir: $tmp</p>";
		echo "<p>export name: $list_name</p>";

#		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx( $spreadsheet );
#		$writer->save("05featuredemo.xlsx");
	}

	protected function write_spreadsheet( $data ) { }
	protected function save_spreadsheet() { }
	protected function email_spreadsheet() { }

}
