<?php
/**
 * classes/Form/Baselist.php
 *
 */
/**
 * Handles nodelist on front end
 *
 */
class WMN_Form_Baselist {

	protected $ajax      = array();
	protected $count     = 0;
	protected $node      = '';
	protected $page      = 1;
	protected $page_size = 50;

	use WMN_Trait_Attributes;

	public function __construct() {
		$this->add_actions();
		if ( ! empty( $_POST['nodepage'] ) ) {
			$this->page = intval( $_POST['nodepage'], 10 );
		}
		$this->ajax = array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nodepage' => $this->page,
			'security' => wp_create_nonce( __CLASS__ )
		);
		if ( ! empty( $_POST['active'] ) ) {
			$this->node = $this->nodelist_select_field()->sanitize( $_POST['active'] );
		}
	}

	protected function add_actions() {
		add_action( 'wp_enqueue_scripts',        [ $this, 'nodelist_scripts' ] );
		add_action( 'wp_ajax_wmn_show_nodelist', [ $this, 'show_nodelist' ] );
		add_action( 'wp_ajax_nopriv_wmn_show_nodelist', [ $this, 'show_nodelist' ] );
	}

	public function nodelist_scripts() {
		if ( get_page_slug() === 'master-nodelist' ) {
			wp_enqueue_script( 'wmn-master-nodelist', wmn_paths()->get_plugin_file_uri( 'js/form-nodelist.js' ), [ 'jquery' ], wmn_paths()->version, true );
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

	protected function nodelist_select_field() {
		global $wpdb;
		$nodes = $wpdb->get_col( 'SELECT DISTINCT(node) FROM workbook_nodelist' );
		sort( $nodes );
		array_unshift( $nodes, 'Select Node' );
		$args  = array(
			'library'    => 'wmn',
			'field_name' => 'active_node',
			'field_css'  => 'margint1e',
			'choices'    => $nodes,
			'onchange'   => 'load_nodelist(1);'
		);
		$select = new WMN_Form_Field_Select( $args );
		return $select;
	}

	public function show_nodelist() {
		check_ajax_referer( __CLASS__, 'security' );
		$html = 'No nodelist received';
		if ( ! empty( $this->node ) ) {
			$nodes  = $this->build_nodelist();
			$header = $this->build_header();
			$footer = $this->build_footer();
			$html   = $header . $nodes . $footer;
		}
		echo $html;
		wp_die();
	}

	protected function build_header() {
		$html  = '<div class="row">';
		$html .= $this->back_button();
		$html .= $this->next_button();
		$html .= $this->get_apply_attrs_element( 'h3', [ 'class' => 'centered' ], 'Node selected was ' . $this->node );
		$html .= '</div>';
		return $html;
	}

	protected function build_footer() {
		$html  = '<div class="row">';
		$html .= $this->back_button();
		$html .= $this->next_button();
		$html .= '</div>';
		return $html;
	}

	protected function back_button() {
		$html = '';
		if ( $this->page > 1 ) {
			$attrs = array(
				'class'   => 'btn btn-fluidity pull-left previous-nodepage margint1e',
				'onclick' => 'load_nodelist(' . ( $this->page - 1 ) . ');',
				'title'   => __( 'go to previous page', 'wmn-workbook' )
			);
			$html = $this->get_apply_attrs_element( 'button', $attrs, __( 'Previous', 'wmn-workbook' ) );
		}
		return $html;
	}

	protected function next_button() {
		$html = '';
		$max_pages = intval( $this->count / $this->page_size ) + 1;
		if ( $this->page < $max_pages ) {
			$attrs = array(
				'class'   => 'btn btn-fluidity pull-right next-nodepage margint1e',
				'onclick' => 'load_nodelist(' . ( $this->page + 1 ) . ');',
				'title'   => __( 'go to next page', 'wmn-workbook' )
			);
			$html = $this->get_apply_attrs_element( 'button', $attrs, __( 'Next', 'wmn-workbook' ) );
		}
		return $html;
	}

	protected function build_nodelist() {
		$data = $this->retrieve_nodelist_data();
		$html = print_r( $data, true );
		return $html;
	}

	protected function retrieve_nodelist_data() {
		global $wpdb;
		$sql   = "SELECT account, house, ticket, address, viya, subscriber, install, complete, comments";
		$sql  .= " FROM workbook_nodelist WHERE node = %s ORDER BY address";
		$prep  = $wpdb->prepare( $sql, $this->node );
		$count = $wpdb->query( $prep );
		$limit = min( ( $this->ajax['nodepage'] * $this->page_size ), $count );
		$start = $limit - $this->page_size;
		$data  = array();
		for ( $i = $start ; $i < $limit ; $i++ ) {
			$data[] = $wpdb->get_row( $prep, ARRAY_A, $i );
		}
		$this->count = $count;
		return compact( 'count', 'start', 'limit', 'i', 'data' );
		return $data;
	}

}
