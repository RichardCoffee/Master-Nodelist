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
		} else {
			add_shortcode( 'wmn-nodelist', array( $this, 'nodelist_form' ) );
		}
	}

	public function add_actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'nodelist_scripts' ) );
		parent::add_actions();
	}

	public function add_filters() {
#		parent::add_filters(); // adds settings link
	}

	public function nodelist_scripts() {
		if ( get_page_slug() === 'master-nodelist' ) {
			wp_enqueue_script( 'wmn-master-nodelist', wmn_paths()->get_plugin_file_uri( 'js/master-nodelist.js' ), array( 'jquery' ), wmn_paths()->version, true );
		}
	}

	public function nodelist_form() { ?>
		<div class="row">
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<h1 class="centered">Master Nodelist</h1>
			</div>
			<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
				<?php $this->nodelist_select_form(); ?>
			</div>
		</div>
		<div id="master-nodelist">
		</div><?php
	}

	public function nodelist_select_form() {
		global $wpdb;
		$sql   = "SELECT DISTINCT(node) FROM workbook_nodelist";
#		$prep  = $wpdb->prepare( $sql );
#		$nodes = $wpdb->get_col( $prep );
		$nodes = $wpdb->get_col( $sql );
		sort( $nodes );
		array_unshift( $nodes, 'Select Node' );
		$args  = array(
			'library'    => 'wmn',
			'field_name' => 'active_node',
			'field_css'  => 'margint1e',
			'choices'    => $nodes
		);
		$select = new WMN_Form_Field_Select( $args );
		$select->select();
	}

}
