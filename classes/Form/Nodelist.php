<?php
/**
 * classes/Form/Nodelist.php
 *
 */
/**
 * Handles nodelist on front end
 *
 */
class WMN_Form_Nodelist {

	protected $ajax      = array();
	protected $count     = 0;
	protected $node      = '';
	protected $page      = 1;
	protected $page_size = 50;

	use WMN_Trait_Attributes;

	public function __construct() {
		$this->add_actions();
		if ( ! empty( $_POST['active'] ) ) {
			$this->node = $this->node_select_field()->sanitize( $_POST['active'] );
		}
		if ( ! empty( $_POST['nodepage'] ) ) {
			$this->page = intval( $_POST['nodepage'], 10 );
		}
		$this->ajax = array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nodepage' => $this->page,
			'security' => wp_create_nonce( __CLASS__ )
		);
	}

	protected function add_actions() {
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
			<?php $this->node_select_field()->select(); ?>
			<h1 class="centered">Master Nodelist</h1>
		</div>
		<div id="tech-nodelist"></div>
		<div id="tech-editlist"></div>
		<div id="master-nodelist"></div><?php
	}

	protected function node_select_field() {
		global $wpdb;
		$nodes = $wpdb->get_col( 'SELECT DISTINCT(node) FROM workbook_nodelist' );
		sort( $nodes );
		array_unshift( $nodes, 'Select Node' );
		$args  = array(
			'library'    => 'wmn',
			'field_name' => 'active_node',
			'field_css'  => 'pull-left',
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
			$header = '';//$this->build_header();
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
				'class'   => 'btn btn-fluidity pull-left previous-nodepage marginb1e',
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
				'class'   => 'btn btn-fluidity pull-right next-nodepage marginb1e',
				'onclick' => 'load_nodelist(' . ( $this->page + 1 ) . ');',
				'title'   => __( 'go to next page', 'wmn-workbook' )
			);
			$html = $this->get_apply_attrs_element( 'button', $attrs, __( 'Next', 'wmn-workbook' ) );
		}
		return $html;
	}

	protected function build_nodelist() {
		$query = new WMN_Query_Nodelist;
		$data  = $this->retrieve_nodelist_data();
		ob_start(); ?>
		<div class="panel panel-fluidity">
			<div class="panel-heading centered"><?php
				echo $this->back_button();
				echo $this->next_button();
				$this->apply_attrs_element( 'h4', [ 'class' => 'centered' ], sprintf( __( 'Listing for node %s', 'wmn-workbook' ), $this->node ) ); ?>
			</div>
			<table class="table">
				<thead>
					<tr>
						<th class="centered"><?php e_esc_html( $query->header_title( 'address' ) ); ?></tr>
					</tr>
				</thead>
				<tbody><?php
					foreach( $data as $entry ) { ?>
						<tr><?php
							$this->apply_attrs_element( 'td', [ 'class' => 'hidden' ],  $entry['id'] );
							$this->apply_attrs_element( 'td', [ 'class' => 'address' ], $entry['address'] ); ?>
						</tr><?php
					} ?>
				</tbody>
			</table>
		</div><?php
		return ob_get_clean();
	}

	protected function retrieve_nodelist_data() {
		global $wpdb;
		$sql   = "SELECT id, account, house, ticket, address, viya, subscriber, install, complete, comments";
		$sql  .= " FROM workbook_nodelist WHERE node = %s ORDER BY address";
		$prep  = $wpdb->prepare( $sql, $this->node );
		$count = $wpdb->query( $prep );
		$limit = $this->ajax['nodepage'] * $this->page_size;
		$start = $limit - $this->page_size;
		$data  = array();
		for ( $i = $start ; $i < min( $limit, $count ) ; $i++ ) {
			$data[] = $wpdb->get_row( $prep, ARRAY_A, $i );
		}
		$this->count = $count;
		return $data;
	}

}
