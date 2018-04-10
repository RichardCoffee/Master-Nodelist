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

	protected $ajax = array();
	protected $page_size = 50;

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
		$this->ajax = array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nodepage' => 1,
			'security' => wp_create_nonce( __CLASS__ )
		);
	}

	public function add_actions() {
		add_action( 'wp_enqueue_scripts',        array( $this, 'nodelist_scripts' ) );
		add_action( 'wp_ajax_wmn_show_nodelist', array( $this, 'show_nodelist' ) );
		parent::add_actions();
	}

	public function add_filters() {
#		parent::add_filters(); // adds settings link
	}

	public function nodelist_scripts() {
		if ( get_page_slug() === 'master-nodelist' ) {
			wp_enqueue_script( 'wmn-master-nodelist', wmn_paths()->get_plugin_file_uri( 'js/master-nodelist.js' ), array( 'jquery' ), wmn_paths()->version, true );
			wp_localize_script( 'wmn-master-nodelist', 'nodelist_ajax', $this->ajax );
		}
	}

	public function nodelist_form() { ?>
		<div class="row">
			<div class="col-lg-10 col-md-10 col-sm-9 col-xs-12">
				<h1 class="centered">Master Nodelist</h1>
			</div>
			<div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
				<?php $this->nodelist_select_form()->select(); ?>
			</div>
		</div>
		<div id="tech-nodelist">
		</div>
		<div id="master-nodelist">
		</div><?php
	}

	public function nodelist_select_form() {
		global $wpdb;
		$nodes = $wpdb->get_col( 'SELECT DISTINCT(node) FROM workbook_nodelist' );
		sort( $nodes );
		array_unshift( $nodes, 'Select Node' );
		$args  = array(
			'library'    => 'wmn',
			'field_name' => 'active_node',
			'field_css'  => 'margint1e',
			'choices'    => $nodes,
			'onchange'   => 'load_nodelist();'
		);
		$select = new WMN_Form_Field_Select( $args );
		return $select;
	}

	public function show_nodelist() {
		check_ajax_referer( __CLASS__, 'security' );
wmn(1)->log('show_nodelist');
		$html = 'No nodelist received';
		if ( ! empty( $_POST['active'] ) ) {
			$node = $this->nodelist_select_form()->sanitize( $_POST['active'] );
			if ( ! empty( $node ) ) {
				$html = $this->build_nodelist( $node );
			}
		}
		echo $html;
		wp_die();
	}

	public function build_nodelist( $node ) {
		$html  = wmn()->get_apply_attrs_element( 'h3', [ 'class' => 'centered' ], 'Node selected was ' . $node );
		$data  = $this->retrieve_nodelist( $node );
		$html .= print_r($data);
		return $html;
	}

	public function retrieve_nodelist( $node ) {
		global $wpdb;
		$sql  = "SELECT account, house, ticket, address, viya, subscriber, install, complete, comments";
		$sql .= " FROM workbook_nodelist WHERE node = %s ORDER BY address";
		$prep = $wpdb->prepare( $sql, $node );

		$limit = ( ! empty( $_POST['nodepage'] ) ) ? ( intval( $_POST['nodepage'], 10 ) * $this->page_size ) : $this->page_size;
		$start = $limit - $this->page_size;
		$data  = array();
		for ( $i = $start ; $i < $limit ; $i++ ) {
			if ( $row = $wpdb->get_row( $prep, ARRAY_A, $i ) ) {
				$data[] = $row;
			} else {
				break;
			}
		}
		return $data;
	}

}
