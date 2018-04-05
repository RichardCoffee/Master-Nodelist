<?php

class WMN_Form_Workbook extends WMN_Form_Admin {


	protected $capability = 'import';
	protected $slug       = 'workbook';


	public function __construct() {
		add_action( 'admin_menu',              array( $this, 'add_menu_option'    ) );
#		add_action( 'tcc_load_form_page',      array( $this, 'wmn_load_form_page' ) );
#		add_filter( "form_text_{$this->slug}", array( $this, 'form_trans_text' ), 10, 2 );
#		parent::__construct();
	}

	public function add_menu_option() {
		if ( current_user_can( $this->capability ) ) {
			$page = __( 'Import Nodelist', 'wmn-workbook' );
			$menu = __( 'Import Nodelist', 'wmn-workbook' );
#			$func = array( $this, $this->render );
			$func = array( $this, 'show_import_form' );
			$this->hook_suffix = add_management_page( $page, $menu, $this->capability, $this->slug, $func );
		}
	}
/*
	public function wmn_load_form_page() {
#		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_theme_scripts' ) );
	} //*/

	public function admin_enqueue_scripts( $hook ) {
		$paths = wmn_paths();
		wp_enqueue_style(  'workbook-form.css', $paths->get_plugin_file_uri( 'css/admin-form.css' ), null, $paths->version );
#		wp_enqueue_script( 'workbook-form.js',  $paths->get_plugin_file_uri( 'js/admin-form.js' ), array( 'jquery' ), $paths->version, true );
	}
/*
	public function enqueue_theme_scripts() {
		$paths = wmn_paths();
#		wp_enqueue_style(  'workbook-form.css', $paths->get_plugin_file_uri( 'css/theme-form.css' ), null, $paths->version );
	} //*/

	protected function form_layout( $form = array() ) {
#		$options = new PMW_Options_Privacy;
#		$form    = $options->default_form_layout();
#		$form['title'] = __( 'Privacy My Way', 'tcc-privacy' );
		return $form;
	}
/*
	public function form_trans_text( $text, $orig ) {
#		$text['submit']['object']  = __( 'Privacy', 'tcc-privacy' );
#		$text['submit']['subject'] = __( 'Privacy', 'tcc-privacy' );
		return $text;
	} //*/

	public function show_import_form() {
		echo '<h1 class="centered">Hello World!</h1>';
	}

}
