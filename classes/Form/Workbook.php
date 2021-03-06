<?php

class WMN_Form_Workbook extends WMN_Form_Admin {


	protected $capability = 'import';
	protected $slug       = 'workbook';


	public function __construct() {
		add_action( 'admin_enqueue_scripts',       [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'admin_menu',                  [ $this, 'add_menu_option' ] );
		add_action( 'wp_ajax_wmn_import_nodelist', [ $this, 'import_nodelist' ] );
		add_action( 'wp_ajax_wmn_reset_nodelist',  [ $this, 'reset_nodelist' ] );
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
		$paths = wmn_paths();
		wp_enqueue_media();
		wp_enqueue_style(  'wmn-form-workbook.css', $paths->get_plugin_file_uri( 'css/form-workbook.css' ),       null, $paths->version );
		wp_enqueue_script( 'wmn-form-workbook.js',  $paths->get_plugin_file_uri( 'js/form-workbook.js' ), [ 'jquery' ], $paths->version, true );
	}

	protected function form_layout( $form = array() ) {
		return $form;
	}

	public function show_import_form() { ?>
		<h1 class="centered">
			<?php esc_html_e( 'Import Master Node List', 'wmn-workbook' ); ?>
		</h1>
		<form method='post'>
			<p id="file_status" class="centered">No file selected</p>
			<div id="file_log" class="centered">
			</div>
			<div>
				<div class="centered">
					<input id="upload_nodelist_button" type="button" class="button" value="<?php _e( 'Choose file to import', 'wmn-workbook' ); ?>" />
				</div>
				<div class="pull-right">
					<input id="reset_nodelist_button" type="button" class="button" value="<?php _e( 'Reset nodelist', 'wmn-workbook' ); ?>" />
				</div>
			</div>
		</form>
		<div>
			<?php #phpinfo(); ?>
		</div><?php
	}

	public function import_nodelist() {
		session_start();
		require_once( wmn_paths()->dir . 'vendor/autoload.php' );

		if ( isset( $_SESSION['import_nodelist'] ) ) {
			$data = $_SESSION['import_nodelist'];
			$data['index'] = $_POST['start_index'];
		} else {
			$data = array(
				'count' => 0,
				'file'  => get_attached_file( $_POST['attachment_id'] ), // full path
				'index' => 0,
				'names' => array(),
			);
		}

		$import = new WMN_Query_Nodelist;
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		$reader->setReadDataOnly( true );
		if ( empty( $data['names'] ) ) {
			$data['names'] = $reader->listWorksheetNames( $data['file'] );
			$data['count'] = count( $data['names'] );
		}

		$had_error  = false;
		$skipped    = false;
		$sheet_name = $data['names'][$data['index']];
		$worksheets = $reader->listWorksheetInfo( $data['file'] );
		if ( $worksheets[ $data['index'] ]['totalRows'] === 0 ) {
			$skipped = true;
		} else {
			$reader->setLoadSheetsOnly( $sheet_name );
			$spreadsheet = $reader->load( $data['file'] );
			$sheet_data  = $spreadsheet->getActiveSheet()->toArray( null, false, false, false );
			$results     = $import->import( $sheet_data );
			if ( empty( $results ) ) {
				$had_error = true;
			}
		}

		$_SESSION['import_nodelist'] = $data;
		$response = array(
			'status'  => 'success',
			'index'   => $data['index'],
			'type'    => 'complete',
			'message' => '<p>Master Nodelist successfully imported.</p>',
		);
		if ( $had_error ) {
			$response['status']  = 'error';
			$response['message'] = "ERROR: Worksheet $sheet_name was not imported.  Operation aborted.";
			unset( $_SESSION['import_nodelist'] );
		} else if ( $skipped ) {
			$response['type']    = 'incomplete';
			$response['message'] = "Worksheet $sheet_name skipped.";
		} else if ( ( $data['index'] + 1 ) < $data['count'] ) {
			$response['type']    = 'incomplete';
			$response['message'] = "Worksheet $sheet_name imported.";
			if ( $results['dups'] > 0 ) {
				$response['message'] .= " {$results['dups']} records skipped.";
			}
			if ( $results['new'] > 0 ) {
				$response['message'] .= " {$results['new']} records imported.";
			}
		} else {
			unset( $_SESSION['import_nodelist'] );
		}
		echo json_encode( $response );
		wp_die();
	}

	public function reset_nodelist() {
		$import = new WMN_Query_Nodelist;
		$import->destroy();
		$import->create();
		$response = array(
			'status'  => 'success',
			'message' => __( 'Master nodelist has been reset.', 'wmn-workbook' )
		);
		echo json_encode( $response );
		wp_die();
	}


}
