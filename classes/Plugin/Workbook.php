<?php
/**
 * classes/Plugin/Workbook.php
 *
 */
/**
 * Main plugin class
 *
 */
class WMN_Plugin_Workbook extends WMN_Plugin_Plugin {

	use WMN_Trait_Singleton;

	public function initialize() {
		if ( ( ! WMN_Register_Workbook::php_version_check() ) || ( ! WMN_Register_Workbook::wp_version_check() ) ) {
			return;
		}
		register_deactivation_hook( $this->paths->file, array( 'WMN_Register_Workbook', 'deactivate' ) );
		register_uninstall_hook(    $this->paths->file, array( 'WMN_Register_Workbook', 'uninstall'  ) );
		$this->add_actions();
		$this->add_filters();
		if ( is_admin() ) {
			new WMN_Form_Workbook;
		}
	}

	public function add_actions() {
		parent::add_actions();
	}

	public function add_filters() {
		parent::add_filters();
	}

}
