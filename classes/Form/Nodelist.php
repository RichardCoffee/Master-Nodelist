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
	protected $entry     = 0;
	protected $insert    = 'Select Node';
	protected $node      = '';
	protected $page      = 1;
	protected $page_size = 50;
	protected $query;
	protected $select_opts = array( '', 'Yes', 'Not Installed' );

	use WMN_Trait_Attributes;

	public function __construct() {
		$this->add_actions();
		if ( ! empty( $_POST['active'] ) )   { $this->node  = $this->node_select_field()->sanitize( $_POST['active'] ); }
		if ( ! empty( $_POST['entry'] ) )    { $this->entry = intval( $_POST['entry'],    10 ); }
		if ( ! empty( $_POST['nodepage'] ) ) { $this->page  = intval( $_POST['nodepage'], 10 ); }
		$this->ajax = array(
			'active'   => $this->node,
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'avoid'    => $this->insert,
			'dateform' => 'n/j/y', // get_option( 'date_format' ),
			'nodepage' => $this->page,
			'security' => wp_create_nonce( __CLASS__ )
		);
		$this->query = new WMN_Query_Nodelist();
	}

	protected function add_actions() {
		add_action( 'wp_enqueue_scripts',          [ $this, 'nodelist_scripts' ], 11 );
		add_action( 'wp_ajax_wmn_show_nodelist',   [ $this, 'show_nodelist' ] );
		add_action( 'wp_ajax_wmn_pick_entry',      [ $this, 'pick_entry' ] );
		add_action( 'wp_ajax_wmn_save_entry',      [ $this, 'save_entry' ] );
		add_action( 'wp_ajax_wmn_export_techlist', [ $this, 'export_techlist' ] );
		add_action( 'wp_ajax_wmn_verify_export',   [ $this, 'verify_export' ] );
	}

	public function nodelist_scripts() {
		if ( $this->get_page_slug() === 'master-nodelist' ) {
			$version = wmn_paths()->version;
			$prereq  = array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-datepicker',
				'tcc-library'
			);
			wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'wmn-form-nodelist.css', wmn_paths()->get_plugin_file_uri( 'css/form-nodelist.css' ), null, $version );
			wp_enqueue_script( 'wmn-form-nodelist.js', wmn_paths()->get_plugin_file_uri( 'js/form-nodelist.js' ), $prereq, $version, true );
		}
	}

	public function nodelist_form() { ?>
		<div class="row marginb1e">
			<?php $this->node_select_field()->select(); ?>
		</div>
		<div id="tech-nodelist">
			<?php $this->tech_entries(); ?>
		</div>
		<div id="tech-editlist"></div>
		<div id="master-nodelist">
			<?php $this->display_nodelist(); ?>
		</div><?php
		$this->ajax['active'] = $this->node;
		wp_localize_script( 'wmn-form-nodelist.js', 'nodelist_ajax', $this->ajax );
	}

	protected function node_select_field() {
		global $wpdb;
		$nodes = $wpdb->get_col( 'SELECT DISTINCT(node) FROM workbook_nodelist' );
		sort( $nodes );
		array_unshift( $nodes, $this->insert );
		$args  = array(
			'library'      => 'wmn',
			'field_name'   => 'active_node',
			'field_css'    => 'pull-left',
			'choices'      => $nodes,
			'onchange'     => 'load_nodelist(1);',
			'form_control' =>  false,
			'field_value'  => $this->node
		);
		$select = new WMN_Form_Field_Select( $args );
		return $select;
	}

	public function show_nodelist() {
		check_ajax_referer( __CLASS__, 'security' );
		$this->display_nodelist();
		wp_die();
	}

	protected function display_nodelist() {
		if ( ! empty( $this->node ) ) {
			$this->build_nodelist();
			$this->build_footer();
		}
	}

	protected function back_button( $scroll = false ) {
		if ( $this->page > 1 ) {
			$do_scroll = ( $scroll ) ? 'true' : 'false';
			$attrs = array(
				'class'   => 'btn btn-fluidity pull-left previous-nodepage marginb1e',
				'onclick' => 'load_nodelist(' . ( $this->page - 1 ) . ',' . $do_scroll . ');',
				'title'   => __( 'go to previous page', 'wmn-workbook' )
			);
			$this->apply_attrs_element( 'button', $attrs, __( 'Previous', 'wmn-workbook' ) );
		}
	}

	protected function next_button( $scroll = false ) {
		$max_pages = intval( $this->count / $this->page_size ) + 1;
		if ( $this->page < $max_pages ) {
			$do_scroll = ( $scroll ) ? 'true' : 'false';
			$attrs = array(
				'class'   => 'btn btn-fluidity pull-right next-nodepage marginb1e',
				'onclick' => 'load_nodelist(' . ( $this->page + 1 ) . ',' . $do_scroll . ');',
				'title'   => __( 'go to next page', 'wmn-workbook' )
			);
			$this->apply_attrs_element( 'button', $attrs, __( 'Next', 'wmn-workbook' ) );
		}
	}

	protected function build_nodelist() {
		$data = $this->retrieve_nodelist_data();
		$key  = ( empty( $data[0]['address'] ) ) ? 'descrip' : 'address'; ?>
		<div class="panel panel-fluidity">
			<div class="panel-heading centered"><?php
				$this->back_button(1);
				$this->next_button(1);
				$this->apply_attrs_element( 'h4', [ 'class' => 'centered' ], sprintf( __( 'Listing for node %s', 'wmn-workbook' ), $this->node ) ); ?>
			</div>
			<table class="table">
				<thead>
					<tr>
						<th class="centered"><?php e_esc_html( $this->query->header_title( $key ) ); ?></th>
					</tr>
				</thead>
				<tbody><?php
					foreach( $data as $entry ) { ?>
						<tr onclick="pick_entry( this, <?php echo $entry['id']; ?> );"><?php
#							$this->apply_attrs_element( 'td', [ 'class' => 'hidden' ],  $entry['id'] );
							$this->apply_attrs_element( 'td', [ 'class' => $key ], $entry[ $key ] ); ?>
						</tr><?php
					} ?>
				</tbody>
			</table>
		</div><?php
	}

	protected function build_footer() { ?>
		<div class="row"><?php
			$this->back_button( true );
			$this->next_button( true ); ?>
		</div><?php
	}

	protected function retrieve_nodelist_data() {
		global $wpdb;
		$sql   = "SELECT id, account, house, ticket, descrip, address, viya, subscriber, install, complete, comments";
		$sql  .= " FROM workbook_nodelist WHERE node = %s AND ( complete IS NULL OR complete = '' ) ORDER BY address, descrip";
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

	public function pick_entry() {
		check_ajax_referer( __CLASS__, 'security' );
		if ( $this->entry ) {
			$this->edit_entry();
		}
		wp_die();
	}

	protected function edit_entry() {
		$entry  = $this->query->retrieve_entry( $this->entry );
		$entry  = $this->query->check_duplicate( $entry ); ?>
		<div class="panel panel-fluidity">
			<div class="panel-heading centered">
				<?php $this->apply_attrs_element( 'h4', [ 'class' => 'centered' ], $entry['address'] ); ?>
			</div>
			<div id="edit-entry" class="panel-body">
				<div class="row">
					<?php $this->edit_entry_form( $entry ); ?>
				</div>
			</div>
		</div><?php
	}

	protected function edit_entry_form( $entry ) {
		$editus = $this->query->entry_fields();
		$editus[] = 'submit'; ?>
		<form id="edit-entry-form"><?php
			wp_nonce_field( 'master-nodelist-edit-entry' );
			$attrs = array(
				'type'  => 'hidden',
				'id'    => 'edit_entry_id',
				'name'  => 'id',
				'value' => $entry['id']
			);
			$this->element( 'input', $attrs );
			foreach( $editus as $item ) {
				$attrs = array(
					'description' => $this->query->header_title( $item ),
					'field_id'    => "wmn_$item",
					'field_name'  => $item,
					'field_value' => ( empty( $entry[ $item ] ) ) ? '' : $entry[ $item ],
				);
				$input = null; ?>
				<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><?php
					switch( $item ) {
						case 'install':
							$attrs['description'] = 'Drop Installed';
							$attrs['choices'] = $this->select_opts;
							$input = new WMN_Form_Field_Select( $attrs );
							$input->select();
							break;
						case 'complete':
							$attrs['timestamp'] = false;
							if ( empty( $attrs['field_value'] ) ) {
								$attrs['field_value'] = date( $this->ajax['dateform'] );
							}
							$input = new WMN_Form_Field_Date( $attrs );
							$input->date();
							break;
						case 'submit':
							$this->save_entry_button();
							break;
						default:
							$input = new WMN_Form_Field_Text( $attrs );
							$input->text();
					} ?>
				</div><?php
				unset( $attrs, $input );
			} ?>
		</form><?php
	}

	protected function save_entry_button() {
		$attrs = array(
			'type'    => 'button',
			'id'      => 'save_entry_button',
			'class'   => 'btn btn-fluidity pull-right',
			'onclick' => 'save_entry();',
			'title'   => __( 'Save this entry', 'wmn-workbook' )
		);
		$this->apply_attrs_element( 'button', $attrs, __( 'Save', 'wmn-workbook' ) );
	}

	public function save_entry() {
		check_ajax_referer( 'master-nodelist-edit-entry' );
		$data = $this->sanitize_data( $_POST );
		if ( ! empty( $data ) ) {
			$this->query->save_entry( $data );
		}
		$this->tech_entries();
		wp_die();
	}

	public function sanitize_data( $data ) {
		$out    = array();
		$fields = $this->query->entry_fields();
		array_unshift( $fields, 'id' );
		foreach( $fields as $field ) {
			if ( array_key_exists( $field, $data ) ) {
				switch( $field ) {
					case 'complete':
						$loop = new WMN_Form_Field_Date();
						break;
					case 'id':
						$loop = new WMN_Form_Field_Integer();
						break;
					case 'install':
						$loop = new WMN_Form_Field_Select( [ 'choices' => $this->select_opts ] );
						break;
					default:
						$loop = new WMN_Form_Field_Text();
				}
				$value = $loop->sanitize( $data[ $field ] );
				if ( ! empty( $value ) ) {
					$out[ $field ] = $value;
				}
			}
			unset( $loop, $value );
		}
		return $out;
	}

	protected function tech_entries() {
		$fields = $this->query->entry_fields();
		array_unshift( $fields, 'node' );
		array_unshift( $fields, 'address' );
		$entries = $this->query->retrieve_tech_entries();
		if ( ! empty( $entries ) ) { ?>
			<div class="panel panel-fluidity">
				<div class="panel-heading centered"><?php
					$this->export_button();
					$this->apply_attrs_element( 'h4', [ 'class' => 'centered' ], __( 'Drops of the Day', 'wmn-workbook' ) ); ?>
				</div>
				<table class="table reduced-font">
					<thead>
						<tr><?php
							foreach( $fields as $field ) { ?>
								<th class="centered"><?php e_esc_html( $this->query->header_title( $field ) ); ?></th><?php
							} ?>
						</tr>
					</thead>
					<tbody><?php
						foreach( $entries as $entry ) { ?>
							<tr onclick="pick_entry( this, <?php echo $entry['id']; ?> );"><?php
								foreach( $fields as $field ) {
									$this->apply_attrs_element( 'td', [ 'class' => "centered $field" ], $entry[ $field ] );
								} ?>
							</tr><?php
						}
						$this->node = $entry['node']; ?>
					</tbody>
				</table>
			</div><?php
		}
	}

	protected function export_button() {
		$attrs = array(
			'class'   => 'btn btn-fluidity pull-right marginb1e',
			'onclick' => 'export_techlist();',
			'title'   => __( 'Export nodelist to excel spreadsheet and email to tech', 'wmn-workbook' )
		);
		$this->apply_attrs_element( 'button', $attrs, __( 'Export', 'wmn-workbook' ) );
	}

	public function export_techlist() {
		$export = new WMN_Plugin_Nodelist;
		$export->export_nodelist();
		$attrs = array(
			'class'   => 'btn btn-fluidity',
			'onclick' => 'verify_export();',
			'title'   => __( 'Please verify that the export to the excel spreadsheet has completed successfully', 'wmn-workbook' )
		);
		$button = $this->get_apply_attrs_element( 'button', $attrs, __( 'Verify', 'wmn-workbook' ) );
		echo "<p>Tech List has been exported, and emailed to you.  Please verify. $button</p>";
		$mailto  = 'mailto:richard.coffee@gmail.com?Subject=Export%20Failed';
		$contact = $this->get_apply_attrs_element( 'a', [ 'href' => $mailto ], 'Richard Coffee' );
		echo "<p>If you are unable to verify the successful completion of the export, please contact $contact.</p>";
		wp_die();
	}

	public function verify_export() {
		$this->query->remove_tech_entries();
		echo '<p>Thank you for verifying that the export was successful.</p>';
		echo '<p>Your daily nodelist has been reset.</p>';
		wp_die();
	}

	private function get_page_slug() {
		if ( ! function_exists( 'get_page_slug' ) ) {
			include_once( WMN_WORKBOOK_DIR . 'includes/stand-alone.php' );
		}
		return get_page_slug();
	}


}
