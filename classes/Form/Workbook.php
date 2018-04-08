<?php

class WMN_Form_Workbook extends WMN_Form_Admin {


	protected $capability = 'import';
	protected $slug       = 'workbook';


	public function __construct() {
		add_action( 'admin_enqueue_scripts',       array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_menu',                  array( $this, 'add_menu_option' ) );
		add_action( 'wp_ajax_wmn_import_nodelist', array( $this, 'import_nodelist' ) );
#		add_filter( "form_text_{$this->slug}",     array( $this, 'form_trans_text' ), 10, 2 );
#		parent::__construct();
	}

	public function add_menu_option() {
		if ( current_user_can( $this->capability ) ) {
			$page = __( 'Import Nodelist', 'wmn-workbook' );
			$menu = __( 'Import Nodelist', 'wmn-workbook' );
			$func = array( $this, 'show_import_form' );
			$this->hook_suffix = add_management_page( $page, $menu, $this->capability, $this->slug, $func );
		}
	}

	public function admin_enqueue_scripts( $hook ) {
wmn()->log('admin_enqueue_scripts');
		$paths = wmn_paths();
		wp_enqueue_media();
		wp_enqueue_style(  'wmn-workbook-form.css',  $paths->get_plugin_file_uri( 'css/admin-form.css' ),                 null, $paths->version );
		wp_enqueue_script( 'wmn-import-nodelist.js', $paths->get_plugin_file_uri( 'js/import-nodelist.js' ), array( 'jquery' ), $paths->version, true );
	}

	protected function form_layout( $form = array() ) {
		return $form;
	}
/*
	public function form_trans_text( $text, $orig ) {
#		$text['submit']['object']  = __( 'Privacy', 'tcc-privacy' );
#		$text['submit']['subject'] = __( 'Privacy', 'tcc-privacy' );
		return $text;
	} //*/

	public function show_import_form() { ?>
		<h1 class="centered">
			<?php esc_html_e( 'Import Master Node List', 'wmn-workbook' ); ?>
		</h1>
		<form method='post'>
			<p id="file_status" class="centered">No file selected</p>
			<div id="file_log" class="centered">
			</div>
			<div class="centered">
				<input id="upload_nodelist_button" type="button" class="button" value="<?php _e( 'Choose file to upload', 'wmn-workbook' ); ?>" />
			</div>
		</form><?php
	}

	public function import_nodelist() {
		session_start();
		require_once( 'vendor/autoload.php' );
/*


check for session var


dbf creation

upload file / pick file
*/
/*
#$nodelist = get_attached_file( $_POST['attachment_id'] ); // Full path
$nodelist = ABSPATH . 'wp-content/uploads/2018/04/master-masterMaster-Node-Released-WIP-4.2.18.xlsx';
$helper = fluid();

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$reader->setReadDataOnly( true );
$spreadsheet = $reader->load( $nodelist );

// Use the PhpSpreadsheet object's getSheetCount() method to get a count of the number of WorkSheets in the WorkBook
$sheetCount = $spreadsheet->getSheetCount();
$helper->log('There ' . (($sheetCount == 1) ? 'is' : 'are') . ' ' . $sheetCount . ' WorkSheet' . (($sheetCount == 1) ? '' : 's') . ' in the WorkBook');

$helper->log('Reading the names of Worksheets in the WorkBook');
// Use the PhpSpreadsheet object's getSheetNames() method to get an array listing the names/titles of the WorkSheets in the WorkBook
$sheetNames = $spreadsheet->getSheetNames();
foreach ($sheetNames as $sheetIndex => $sheetName) {
    $helper->log('WorkSheet #' . $sheetIndex . ' is named "' . $sheetName . '"');
}
*/


	}


}
