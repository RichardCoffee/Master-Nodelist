<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       fb.com/hussam7ussien
 * @since      1.0.0
 *
 * @package    Wp_Excel_2_Db
 * @subpackage Wp_Excel_2_Db/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Excel_2_Db
 * @subpackage Wp_Excel_2_Db/public
 * @author     Hussam Hussien <hussam7ussien@it-qan.com>
 */
class Wp_Excel_2_Db_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action( 'admin_menu', array($this, 'register_media_selector_settings_page') );
		add_action('wp_ajax_excel_to_dbtable',  array($this, 'excel_to_dbtable') );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Excel_2_Db_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Excel_2_Db_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-excel-2-db-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Excel_2_Db_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Excel_2_Db_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'wp-excel-2-db-public', plugin_dir_url( __FILE__ ) . 'js/wp-excel-2-db-public.js', array( 'jquery' ), $this->version, false );

	}

	
	/**
	 * Register media selector settings page.
	 *
	 * @since    1.0.0
	 */
	public function register_media_selector_settings_page() {
		add_submenu_page( 'options-general.php', 'Excel Uploader', 'Excel Uploader', 'manage_options', 'media-selector', array($this, 'media_selector_settings_page_callback') );

	}


	/**
	 * Media selector settings page callback.
	 *
	 * @since    1.0.0
	 */

	public function media_selector_settings_page_callback() {
	wp_enqueue_media();

		?><form method='post'>
			<p id="file_status">No file selected</p>
			<div id="file_log">
			</div>
			<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Choose file to upload' ); ?>" />
		</form><?php

	}

	/**
	 * Handle ajax requests.
	 *
	 * @since    1.0.0
	 */

	public function excel_to_dbtable() {
		session_start();
		global $wpdb;
		require_once plugin_dir_path(__FILE__) .'phpexcel/Classes/PHPExcel.php';
		$start_index=$_POST['start_index'];//check start index
		//error_log( 'INDEX IS '.$start_index );
		$file_path = get_attached_file( $_POST['attachment_id'] ); // Full path
		$table_name = basename( get_attached_file( $_POST['attachment_id']  ) ); // Just the file name
		$file=explode('.',$table_name);
		$tableName=$file[0];
		$file_version=explode('-',$file[0]);
		if(count($file_version)>0)
			$tableName=$file_version[0];
		if($start_index==1){
			if($wpdb->get_var("SHOW TABLES LIKE '".strtolower($tableName)."'") == strtolower($tableName)) {

				$delete = $wpdb->query("DROP TABLE IF EXISTS `".strtolower($tableName)."`");
			}
		}

		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($file_path);
		$objWorksheet = $objPHPExcel->getActiveSheet();
		//get latest row
		$highestRow = $objWorksheet->getHighestRow();
		//get last column
		$highestColumn = $objWorksheet->getHighestColumn();
		//get last column index
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$headers=array();
		if($start_index>1){
			$headers=$_SESSION[$table_name."_headers"];
		}
		$data=array();
		$row=$start_index;
		//error_log( 'ROW IS '.$row );
		for ($row = $start_index; $row <= $highestRow; ++$row) {

		  for ($col = 0; $col <= $highestColumnIndex; ++$col) {
		  	if($row==1){
		  		$value= html_entity_decode($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
		  		if($value!='')
		  			$headers[$col]= html_entity_decode($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
		  	}
		  	else{
		  		if(sizeof($headers)>$col)
		  			$data[$headers[$col]]=html_entity_decode($objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
		  	}

		  }
		  if($row==1){

			     //table not in database. Create new table
			     $charset_collate = $wpdb->get_charset_collate();

			     $sql = "CREATE TABLE ".strtolower($tableName)." (
			           id int(11) NOT NULL AUTO_INCREMENT,";

				foreach ($headers as $header)
					   $sql.="`$header` text,";

				$sql .= "`insertionDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
						   PRIMARY KEY (id)
				     ) $charset_collate;";
			    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			    dbDelta( $sql );

		  }
		  elseif($row>1){
				$value=$wpdb->insert(strtolower($tableName), $data);
				if(!$value){
					$response=array('status'=>'error','index'=>$row,'type'=>'incomplete');
					echo json_encode($response);
					wp_die();
				}elseif(($row % 10) == 0  && $row < $highestRow){
					$response=array('status'=>'success','index'=>$row,'type'=>'incomplete');
					$_SESSION[$table_name."_headers"]=$headers;
					echo json_encode($response);
					wp_die();
				}
			}

		}
			unset($_SESSION[$table_name."_headers"]);
			$response=array('status'=>'success','index'=>$row,'type'=>'complete');
			echo json_encode($response);
			wp_die();
	}
}
