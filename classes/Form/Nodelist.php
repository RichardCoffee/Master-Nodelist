<?php
/**
 * classes/Plugin/Workbook.php
 *
 */
/**
 * Main plugin class
 *
 */
class WMN_Form_Nodelist {

	protected $ajax = array();
	protected $page_size = 50;

	public function __construct() {
		$this->add_actions();
		$this->ajax = array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nodepage' => ( ! empty( $_POST['nodepage'] ) ) ? intval( $_POST['nodelist'], 10 ) : 1,
			'security' => wp_create_nonce( __CLASS__ )
		);
	}

	public function add_actions() {
		add_action( 'wp_enqueue_scripts',        array( $this, 'nodelist_scripts' ) );
		add_action( 'wp_ajax_wmn_show_nodelist', array( $this, 'show_nodelist' ) );
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
				<?php $this->nodelist_select_field()->select(); ?>
			</div>
		</div>
		<div id="tech-nodelist">
		</div>
		<div id="master-nodelist">
		</div><?php
	}

	public function nodelist_select_field() {
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
		$html = 'No nodelist received';
		if ( ! empty( $_POST['active'] ) ) {
			$node = $this->nodelist_select_field()->sanitize( $_POST['active'] );
			if ( ! empty( $node ) ) {
				$html  = $this->build_header( $node );
				$html .= $this->build_nodelist( $node );
				$html .= $this->build_footer( $node );
			}
		}
		echo $html;
		wp_die();
	}

	public function build_nodelist( $node ) {
		$html  = wmn()->get_apply_attrs_element( 'h3', [ 'class' => 'centered' ], 'Node selected was ' . $node );
		$data  = $this->retrieve_nodelist( $node );
		$html .= print_r( $data, true );
		return $html;
	}

	public function retrieve_nodelist( $node ) {
		global $wpdb;
		$sql   = "SELECT account, house, ticket, address, viya, subscriber, install, complete, comments";
		$sql  .= " FROM workbook_nodelist WHERE node = %s ORDER BY address";
		$prep  = $wpdb->prepare( $sql, $node );
		$cnt   = $wpdb->query( $prep );
		$limit = $this->ajax['nodepage'] * $this->page_size;
		$start = $limit - $this->page_size;
		$data  = array();
		for ( $i = $start ; $i < $limit ; $i++ ) {
			$data[] = $wpdb->get_row( $prep, ARRAY_A, $i );
		}
		return compact( 'cnt', 'start', 'limit', 'i', 'data' );
		return $data;
	}

}
