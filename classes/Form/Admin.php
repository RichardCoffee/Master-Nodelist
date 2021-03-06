<?php

/*
 *  classes/Form/Admin.php
 *
 *  copyright 2014-2018, The Creative Collective, the-creative-collective.com
 *
 *  I sure hope that Fields API thing works out, cause then I can get rid of this monstrosity.
 */

abstract class WMN_Form_Admin {

	protected $current   = '';
	protected $form      =  array();
	protected $form_opts =  array();
	protected $form_text =  array();
	protected $hook_suffix;
	protected $options;
	protected $prefix    = 'wmn_options_';
	protected $register;
	protected $render;
	protected $slug      = 'default_page_slug';
	public    $tab       = 'about';
	protected $type      = 'single'; # two values: single, tabbed
	protected $validate;

	use WMN_Trait_Attributes;
	use WMN_Trait_Logging;

	abstract protected function form_layout( $option );
	public function description() { return ''; }

	protected function __construct() {
		$this->screen_type();
		add_action( 'admin_init', [ $this, 'load_form_page' ] );
	}

	public function load_form_page() {
		global $plugin_page;
		if ( ( $plugin_page === $this->slug ) || ( ( $refer = wp_get_referer() ) && ( strpos( $refer, $this->slug ) ) ) ) {
			if ( $this->type === 'tabbed' ) {
				if ( isset( $_POST['tab'] ) ) {
					$this->tab = sanitize_key( $_POST['tab'] );
				} else if ( isset( $_GET['tab'] ) )  {
					$this->tab = sanitize_key( $_GET['tab'] );
				} else if ( $trans = get_transient( 'WMN_TAB' ) ) {
					$this->tab = $trans;
				} else if ( defined( 'WMN_TAB' ) ) {
					$this->tab = WMN_TAB;
				}
				set_transient( 'WMN_TAB', $this->tab, ( DAY_IN_SECONDS * 5 ) );
			}
			$this->form_text();
			$this->form = $this->form_layout();
			if ( ( $this->type === 'tabbed' ) && ! isset( $this->form[ $this->tab ] ) ) {
				$this->tab = 'about';
			}
			$this->determine_option();
			$this->get_form_options();
			$func = $this->register;
			$this->$func();
			do_action( 'tcc_load_form_page' );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		}
	}

	public function admin_enqueue_scripts( $hook_suffix ) {
		wp_enqueue_media();
		wp_enqueue_style(  'admin-form.css', get_theme_file_uri( 'css/admin-form.css' ), [ 'wp-color-picker' ] );
		wp_enqueue_script( 'admin-form.js',  get_theme_file_uri( 'js/admin-form.js' ),   [ 'jquery', 'wp-color-picker' ], false, true );
		$options = apply_filters( 'tcc_form_admin_options_localization', array() );
		if ( $options ) {
			$options = $this->normalize_options( $options, $options );
			wp_localize_script( 'admin-form.js', 'tcc_admin_options', $options );
		}
	}

	protected function normalize_options( $new, $old ) {
		if ( isset( $old['showhide'] ) ) {
			$new['showhide'] = array_map( [ $this, 'normalize_showhide' ], $old['showhide'] );
		}
		return $new;
	}

	public function normalize_showhide( $item ) {
		$default = array(
			'origin' => null,
			'target' => null,
			'show'   => null,
			'hide'   => null,
		);
		return array_merge( $default, $item );
	}


  /**  Form text functions  **/

	private function form_text() {
	$text = array(
		'error'  => array(
			'render'    => _x( 'ERROR: Unable to locate function %s', 'string - a function name', 'wmn-workbook' ),
			'subscript' => _x( 'ERROR: Not able to locate form data subscript:  %s', 'placeholder will be an ASCII character string', 'wmn-workbook' )
		),
		'submit' => array(
			'save'      => __( 'Save Changes', 'wmn-workbook' ),
			'object'    => __( 'Form', 'wmn-workbook' ),
			'reset'     => _x( 'Reset %s', 'placeholder is a noun, may be plural', 'wmn-workbook' ),
			'subject'   => __( 'Form', 'wmn-workbook' ),
			'restore'   => _x( 'Default %s options restored.', 'placeholder is a noun, probably singular', 'wmn-workbook' )
		),
		'media'  => array(
			'title'     => __( 'Assign/Upload Image', 'wmn-workbook' ),
			'button'    => __( 'Assign Image', 'wmn-workbook' ),
			'delete'    => __( 'Unassign Image', 'wmn-workbook' )
		)
	);
	$this->form_text = apply_filters( 'form_text_' . $this->slug, $text, $text );
	}


  /**  Register Screen functions **/

	private function screen_type() {
		$this->register = 'register_' . $this->type . '_form';
		$this->render   =   'render_' . $this->type . '_form';
		$this->options  =   'render_' . $this->type . '_options';
		$this->validate = 'validate_' . $this->type . '_form';
	}

	public function register_single_form() {
		register_setting( $this->current, $this->current, [ $this, $this->validate ] );
		$title = ( isset( $this->form['title']    ) ) ? $this->form['title']    : '';
		$desc  = ( isset( $this->form['describe'] ) ) ? $this->form['describe'] : 'description';
		$desc  = ( is_array( $desc ) ) ? $desc : ( ( method_exists( $this, $desc ) ) ? [ $this, $desc ] : $desc );
		add_settings_section( $this->current, $title, $desc, $this->current );
		foreach( $this->form['layout'] as $item => $data ) {
			if ( is_string( $data ) ) {
				continue;	#	skip string variables
			}
			$this->register_field( $this->current, $this->current, $item, $data );
		}
	}

  public function register_tabbed_form() {
    $validater = (isset($this->form['validate'])) ? $this->form['validate'] : $this->validate;
    foreach($this->form as $key=>$section) {
      if (!((array)$section===$section)) continue; // skip string variabler
      if (!($section['option']===$this->current)) continue;
      $validate = (isset($section['validate'])) ? $section['validate'] : $validater;
      $current  = (isset($this->form[$key]['option'])) ? $this->form[$key]['option'] : $this->prefix.$key;
      #register_setting($this->slug,$current,array($this,$validate));
      register_setting($current,$current,[$this,$validate]);
      $title    = (isset($section['title']))    ? $section['title']    : '';
      $describe = (isset($section['describe'])) ? $section['describe'] : 'description';
      $describe = (is_array($describe)) ? $describe : [$this,$describe];
      #add_settings_section($current,$title,$describe,$this->slug);
      add_settings_section($current,$title,$describe,$current);
      foreach($section['layout'] as $item=>$data) {
        $this->register_field($current,$key,$item,$data);
      }
    }
  } //*/

  private function register_field($option,$key,$itemID,$data) {
    if (is_string($data))        return; // skip string variables
    if (!isset($data['render'])) return;
    if ($data['render']=='skip') return;
/*    if ($data['render']=='array') {
      $count = max(count($data['default']),count($this->form_opts[$key][$itemID]));
      for ($i=0;$i<$count;$i++) {
        $label  = "<label for='$itemID'>{$data['label']} ".($i+1)."</label>";
        $args   = array('key'=>$key,'item'=>$itemID,'num'=>$i);
#        if ($i+1==$count) { $args['add'] = true; }
        add_settings_field("{$item}_$i",$label,array($this,$this->options),$this->slug,$current,$args);
      }
    } else { //*/
      $label = $this->field_label($itemID,$data);
      $args  = array('key'=>$key,'item'=>$itemID);
      #add_settings_field($itemID,$label,array($this,$this->options),$this->slug,$option,$args);
      add_settings_field($itemID,$label,[$this,$this->options],$option,$option,$args);
#    }
  }

	private function field_label( $ID, $data ) {
		$data  = array_merge( [ 'help' => '', 'label' => '' ], $data );
		$attrs = array(
			'title' => $data['help'],
		);
		if ( in_array( $data['render'], [ 'display', 'radio_multiple' ] ) ) {
			return $this->get_element( 'span', $attrs, $data['label'] );
		} else if ( $data['render'] === 'title' ) {
			$attrs['class'] = 'form-title';
			return $this->get_element( 'span', $attrs, $data['label'] );
		} else {
			$attrs['for'] = $ID;
			return $this->get_element( 'label', $attrs, $data['label'] );
		}
		return '';
	}

  private function sanitize_callback($option) {
    $valid_func = "validate_{$option['render']}";
    if (method_exists($this,$valid_func)) {
      $retval = array($this,$valid_func);
    } else if (function_exists($valid_func)) {
      $retval = $valid_func;
    } else {
      $retval = 'wp_kses_post';
    }
    return $retval;
  }


  /**  Data functions  **/

	private function determine_option() {
		if ( $this->type === 'single' ) {
			$this->current = $this->prefix . $this->slug;
		} else if ( $this->type === 'tabbed' ) {
			if ( isset( $this->form[ $this->tab ]['option'] ) ) {
				$this->current = $this->form[ $this->tab ]['option'];
			} else {
				$this->current = $this->prefix . $this->tab;
			}
		}
	}

	protected function get_defaults( $option = '' ) {
		if ( empty( $this->form ) ) {
			$this->form = $this->form_layout();
		}
		$defaults = array();
		if ( $this->type === 'single' ) {
			foreach( $this->form['layout'] as $ID => $item ) {
				if ( is_string( $item ) || empty( $item['default'] ) ) {
					continue;
				}
				$defaults[ $ID ] = $item['default'];
			}
		} else {  #  tabbed page
			if ( isset( $this->form[ $option ] ) ) {
				foreach( $this->form[ $option ]['layout'] as $key => $item ) {
					if ( empty( $item['default'] ) ) {
						continue;
					}
					$defaults[ $key ] = $item['default'];
				}
			} else {
				$this->logg( sprintf( $this->form_text['error']['subscript'], $option ), 'stack' );
			}
		}
		return $defaults;
	} //*/

	private function get_form_options() {
		$this->form_opts = get_option( $this->current );
		if ( empty( $this->form_opts ) ) {
			$option = explode( '_', $this->current );
			$this->form_opts = $this->get_defaults( $option[2] );
			add_option( $this->current, $this->form_opts );
		}
	}


  /**  Render Screen functions  **/

	public function render_single_form() { ?>
		<div class="wrap">
			<?php settings_errors(); ?>
			<form method="post" action="options.php"><?php
#				do_action( 'form_admin_pre_display' );
#				do_action( 'form_admin_pre_display_' . $this->current );
				settings_fields( $this->current );
				do_settings_sections( $this->current );
#				do_action( 'form_admin_post_display_' . $this->current );
#				do_action( 'form_admin_post_display' );
				$this->submit_buttons(); ?>
			</form>
		</div><?php //*/
	}

  public function render_tabbed_form() {
    $active_page = sanitize_key( $_GET['page'] ); ?>
    <div class="wrap">
      <div id="icon-themes" class="icon32"></div>
      <h1 class='centered'>
        <?php echo esc_html($this->form['title']); ?>
      </h1><?php
      settings_errors(); ?>
      <h2 class="nav-tab-wrapper"><?php
        $refer = "admin.php?page=$active_page";
        foreach($this->form as $key=>$menu_item) {
          if (is_string($menu_item)) continue;
          $tab_css  = 'nav-tab';
          $tab_css .= ($this->tab==$key) ? ' nav-tab-active' : '';
          $tab_ref  = "$refer&tab=$key"; ?>
          <a href='<?php echo esc_attr($tab_ref); ?>' class='<?php echo esc_attr($tab_css); ?>'>
            <?php echo esc_html($menu_item['title']); ?>
          </a><?php
        } ?>
      </h2>
      <form method="post" action="options.php">
        <input type='hidden' name='tab' value='<?php echo esc_attr( $this->tab ); ?>'><?php
        $current  = (isset($this->form[$this->tab]['option'])) ? $this->form[$this->tab]['option'] : $this->prefix.$this->tab;
        do_action( "form_admin_pre_display_{$this->tab}" );
        settings_fields($current);
        do_settings_sections($current);
        do_action("form_admin_post_display_{$this->tab}");
        $this->submit_buttons($this->form[$this->tab]['title']); ?>
      </form>
    <div><?php //*/
  }

  private function submit_buttons($title='') {
    $buttons = $this->form_text['submit']; ?>
    <p><?php
      submit_button($buttons['save'],'primary','submit',false); ?>
      <span style='float:right;'><?php
        $object = (empty($title)) ? $buttons['object'] : $title;
        $reset  = sprintf($buttons['reset'],$object);
        submit_button($reset,'secondary','reset',false); ?>
      </span>
    </p><?php
  }

	public function render_single_options( $args ) {
		extract( $args );  #  array( 'key'=>$key, 'item'=>$item, 'num'=>$i);
		$data   = $this->form_opts;
		$layout = $this->form['layout'];
		$this->apply_attrs_tag( 'div', $this->render_attributes( $layout[ $item ] ) );
			if ( empty( $layout[ $item ]['render'] ) ) {
				e_esc_html( $data[ $item ] );
			} else {
				$func  = 'render_' . $layout[ $item ]['render'];
				$name  = $this->current . '[' . $item . ']';
				$value = ( isset( $data[ $item ] ) ) ? $data[ $item ] : '';
				if ( $layout[ $item ]['render'] === 'array' ) {
					$name .= '[' . $num . ']';
					#if ( isset( $add ) && $add ) { $layout[ $item ]['add'] = true; }
					$value = ( isset( $data[ $item ][ $num ] ) ) ? $data[ $item ][ $num ] : '';
				}
				$field = str_replace( array( '[', ']' ), array( '_', '' ), $name );
				$fargs = array(
					'ID'     => $field,
					'value'  => $value,
					'layout' => $layout[ $item ],
					'name'   => $name,
				);
				if ( method_exists( $this, $func ) ) {
					$this->$func( $fargs );
				} else if ( function_exists( $func ) ) {
					$func( $fargs );
				} else {
					$this->logg( sprintf( $this->form_text['error']['render'], $func ) );
				}
			} ?>
		</div><?php
	}

	public function render_tabbed_options( $args ) {
		extract( $args );  #  $args = array( 'key' => {group-slug}, 'item' => {item-slug})
		$data   = $this->form_opts;
		$layout = $this->form[ $key ]['layout'];
		$this->apply_attrs_tag( 'div', $this->render_attributes( $layout[ $item ] ) );
		if ( empty( $layout[ $item ]['render'] ) ) {
			e_esc_html( $data[$item] );
		} else {
			$func = "render_{$layout[$item]['render']}";
			$name = $this->current . "[$item]";
			if ( ! isset( $data[ $item ] ) ) {
				$data[ $item ] = ( empty( $layout[ $item ]['default'])) ? '' : $layout[ $item ]['default'];
			}
			$fargs = array(
				'ID'     => $item,
				'value'  => $data[ $item ],
				'layout' => $layout[ $item ],
				'name'   => $name
			);
			if ( method_exists( $this, $func ) ) {
				$this->$func( $fargs );
			} elseif ( function_exists( $func ) ) {
				$func( $fargs );
			} else {
				$this->log( sprintf( $this->form_text['error']['render'], $func ) );
			}
		}
		echo "</div>"; //*/
	}

  public function render_multi_options($args) {
  }

	private function render_attributes( $layout ) {
		$attrs = array();
		$attrs['class'] = ( ! empty( $layout['divcss'] ) ) ? $layout['divcss'] : '';
		$attrs['title'] = ( isset( $layout['help'] ) )     ? $layout['help']   : '';
		if ( ! empty( $layout['showhide'] ) ) {
			$state = array_merge( [ 'show' => null, 'hide' => null ], $layout['showhide'] );
			$attrs['data-item'] = ( isset( $state['item'] ) ) ? $state['item'] : $state['target'];
			$attrs['data-show'] = $state['show'];
			$attrs['data-hide'] = $state['hide'];
		}
		return $attrs;
	}


  /**  Render Items functions
    *
    *
    *  $data = array('ID'=>$field, 'value'=>$value, 'layout'=>$layout[$item], 'name'=>$name);
    *
    **/

	// FIXME:  needs add/delete/sort
	private function render_array( $data ) {
		extract( $data );  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		if ( ! isset( $layout['type'] ) ) { $layout['type'] = 'text'; }
		if ( $layout['type'] === 'image' ) {
			$this->render_image( $data );
		} else {
			$this->render_text( $data );
		}
	}

	private function render_checkbox( $data ) {
		extract( $data );	#	associative array: keys are 'ID', 'value', 'layout', 'name'
		$cbinput = array(
			'type' => 'checkbox',
			'id'   => $ID,
			'name' => $name,
			'value' => 'yes',
			'onchange' => ( isset( $layout['change'] ) ) ? $layout['change'] : '',
		); ?>
		<label>
			<input <?php $this->apply_attrs( $cbinput ); ?>
				<?php checked( $value, 'yes' ); ?> />&nbsp;
			<span>
				<?php echo esc_html( $layout['text'] ); ?>
			</span>
		</label><?php
	}

	private function render_checkbox_multiple( $data ) {
		extract( $data );	#	associative array: keys are 'ID', 'value', 'layout', 'name'
		if ( empty( $layout['source'] ) ) {
			return;
		}
		if ( ! empty( $layout['text'] ) ) { ?>
			<div>
				<?php e_esc_html( $layout['text'] ); ?>
			</div><?php
		}
		foreach( $layout['source'] as $key => $text ) {
			$check = isset( $value[ $key ] ) ? true : false;
			$attrs = array(
				'type'  => 'checkbox',
				'id'    => $ID . '-' . $key,
				'name'  => $name . '[' . $key . ']',
				'value' => $key,
			); ?>
			<div>
				<label>
					<input <?php $this->apply_attrs( $attrs ); ?> <?php checked( $check ); ?> />&nbsp;
					<span>
						<?php echo esc_html( $text ); ?>
					</span>
				</label>
			</div><?php
		}
	}

	private function render_colorpicker($data) {
		extract($data);  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		$attrs = array(
			'type'  => 'text',
			'class' => 'form-colorpicker',
			'name'  => $name,
			'value' => $value,
			'data-default-color' => $layout['default']
		);
		$this->apply_attrs_element( 'input', $attrs );
		$text = ( ! empty( $layout['text'] ) ) ? $layout['text'] : '';
		if ( ! empty( $text ) ) {
			echo esc_html( '&nbsp;' );
			$this->apply_attrs_element( 'span', [ 'class' => 'form-colorpicker-text' ], $text );
		}
	}

	private function render_display( $data ) {
		extract( $data );  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		if ( isset( $layout['default'] ) && ! empty( $value ) ) { e_esc_html( $value ); }
		if ( ! empty( $layout['text'] ) ) { $this->element( 'span', [ ], ' ' . $layout['text'] ); }
	}

	private function render_font( $data ) {
		extract( $data );  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		$attrs = array(
			'id'       => $ID,
			'name'     => "{$name}[]",
			'multiple' => ''
		);
		if ( isset( $layout['change'] ) ) {
			$attrs['onchange'] = $layout['change'];
		}
		$this->tag( 'select', $attrs );
			foreach( $layout['source'] as $key => $text ) {
				$attrs = [ 'value' => $key ];
				$attrs = $this->selected( $attrs, $key, $value );
				$this->element( 'option', $attrs, ' ' . $key . ' ' );
			} ?>
		</select><?php
		if ( ! empty( $data['layout']['text'] ) ) {
			$this->element( 'span', [ ], ' ' . $data['layout']['text'] );
		}
	}

	private function render_image( $data ) {
		extract( $data );  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		$media   = $this->form_text['media'];
		$img_css = 'form-image-container' . ( ( empty( $value ) ) ? ' hidden' : '');
		$btn_css = 'form-image-delete' . ( ( empty( $value ) ) ? ' hidden' : '');
		if ( isset( $layout['media'] ) ) { $media = array_merge( $media, $layout['media'] ); } ?>
		<div data-title="<?php e_esc_attr( $media['title'] ); ?>"
			  data-button="<?php e_esc_attr( $media['button'] ); ?>" data-field="<?php e_esc_attr( $ID ); ?>">
			<button type="button" class="form-image">
				<?php e_esc_html( $media['button'] ); ?>
			</button>
			<input id="<?php e_esc_attr( $ID ); ?>_input" type="text" class="hidden" name="<?php e_esc_attr( $name ); ?>" value="<?php e_esc_html( $value ); ?>" />
			<div class="<?php e_esc_attr( $img_css ); ?>">
				<img id="<?php e_esc_attr( $ID ); ?>_img" src="<?php e_esc_attr( $value ); ?>" alt="<?php e_esc_attr( $value ); ?>">
			</div>
			<button type="button" class="<?php e_esc_attr( $btn_css ); ?>">
				<?php e_esc_html( $media['delete'] ); ?>
			</button>
		</div><?php
	}

	private function render_radio($data) {
		extract( $data );	#	associative array: keys are 'ID', 'value', 'layout', 'name'
		if ( empty( $layout['source'] ) ) return;
		$radio_attrs = array(
			'type'     => 'radio',
			'name'     => $name,
			'onchange' => ( isset( $layout['change'] ) ) ? $layout['change'] : '',
		); ?>
		<div><?php
			if ( isset( $layout['text'] ) ) {
				$uniq = uniqid(); ?>
				<div id="<?php e_esc_attr( $uniq ); ?>">
					<?php e_esc_html( $layout['text'] ); ?>
				</div><?php
				$radio_attrs['aria-describedby'] = $uniq;
			}
			foreach( $layout['source'] as $key => $text ) {
				$radio_attrs['value'] = $key; ?>
				<div>
					<label>
						<input <?php $this->apply_attrs( $radio_attrs ); ?> <?php checked( $value, $key ); ?>><?php
						if ( isset( $layout['src-html'] ) ) {
							echo wp_kses( $text, wmn()->kses() );
						} else {
							e_esc_html( $text );
						}
						if ( isset( $layout['extra_html'][ $key ] ) ) {
							echo wp_kses( $layout['extra_html'][ $key ], wmn()->kses() );
						} ?>
					</label>
				</div><?php
			}
			if ( isset( $layout['postext'] ) ) { ?>
				<div>
					<?php e_esc_html( $layout['postext'] ) ; ?>
				</div><?php
			} ?>
		</div><?php
	} //*/

	#	Note:  this has limited use - only displays yes/no radios
	private function render_radio_multiple( $data ) {
		extract( $data );   #   associative array: keys are 'ID', 'value', 'layout', 'name'
		if ( empty( $layout['source'] ) ) return;
		$pre_css   = ( isset( $layout['textcss'] ) ) ? $layout['textcss'] : '';
		$pre_text  = ( isset( $layout['text'] ) )    ? $layout['text']    : '';
		$post_text = ( isset( $layout['postext'] ) ) ? $layout['postext'] : '';
		$preset    = ( isset( $layout['preset'] ) )  ? $layout['preset']  : 'no'; ?>
		<div class="radio-multiple-div">
			<div class="<?php e_esc_attr( $pre_css ); ?>">
				<?php e_esc_html( $pre_text ); ?>
			</div>
			<div class="radio-multiple-header">
				<span class="radio-multiple-yes"><?php esc_html_e( 'Yes', 'wmn-workbook' ); ?></span>&nbsp;
				<span class="radio-multiple-no" ><?php esc_html_e( 'No', 'wmn-workbook' ); ?></span>
			</div><?php
			foreach( $layout['source'] as $key => $text ) {
				$check  = ( isset( $value[ $key ] ) ) ? $value[ $key ] : $preset; ?>
				<div class="radio-multiple-list-item">
					<label>
						<input type="radio" value="yes" class="radio-multiple-list radio-multiple-list-yes"
						       name="<?php echo esc_attr( $name.'['.$key.']' ) ; ?>"
						       <?php checked( $check, 'yes' ); ?> />&nbsp;
						<input type="radio" value="no" class="radio-multiple-list radio-multiple-list-no"
						       name="<?php echo esc_attr( $name.'['.$key.']' ) ; ?>"
						       <?php checked( $check, 'no' ); ?> />
						<span class="radio-multiple-list-text">
							<?php echo wp_kses( $text, wmn()->kses() ); ?>
						</span>
					</label>
				</div><?php
			} ?>
			<div class="radio-multiple-post-text">
				<?php echo esc_html( $post_text ) ; ?>
			</div>
		</div><?php
	}

	private function render_select( $data ) {
		extract( $data );  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		if ( empty( $layout['source'] ) ) {
			return;
		}
		if ( ! empty( $layout['text'] ) ) {
			$this->element( 'div', [ 'class' => 'form-select-text' ], $layout['text'] );
		}
		$attrs = array(
			'id'   => $ID,
			'name' => $name
		);
		if ( ! ( strpos( '[]', $name ) === false ) ) {
			$attrs['multiple'] = 'multiple';
		}
		if ( isset( $layout['change'] ) ) {
			$attrs['onchange'] = $layout['change'];
		}
		$this->tag( 'select', $attrs );
			$source_func = $layout['source'];
			if ( is_array( $source_func ) ) {
				foreach( $source_func as $key => $text ) {
					$attrs = [ 'value' => $key ];
					$attrs = $this->selected( $attrs, $key, $value );
					$this->element( 'option', $attrs, ' ' . $text . ' ' );
				}
			} elseif ( method_exists( $this, $source_func ) ) {
				$this->$source_func( $value );
			} elseif ( function_exists( $source_func ) ) {
				$source_func( $value );
			} ?>
		</select><?php
	}

	private function render_select_multiple( $data ) {
		$data['name'] .= '[]';
		render_select( $data );
	}

	private function render_spinner( $data ) {
		extract($data);  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		$attrs = array(
			'type'  => 'number',
			'class' => 'small-text',
			'id'    => $ID,
			'name'  => $name,
			'min'   => '1',
			'step'  => '1',
			'value' => $value,
		);
		$this->apply_attrs_tag( 'input', $attrs );
		if ( ! empty( $layout['stext'] ) ) { e_esc_attr( $layout['stext'] ); }
	}

	private function render_text( $data ) {
		extract( $data );  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
		if ( ! empty( $layout['text'] ) ) {
			$this->element( 'p', [ ], ' ' . $layout['text'] );
		}
		$attrs = array(
			'type'  => 'text',
			'id'    => $ID,
			'class' => ( isset( $layout['class'] ) )  ? $layout['class'] : 'regular-text',
			'name'  => $name,
			'value' => $value,
			'title' => ( isset( $layout['help'] ) )   ? $layout['help']  : '',
			'placeholder' => ( isset( $layout['place'] ) ) ? $layout['place'] : '',
			'onchange'    => ( isset( $layout['change'] ) ) ? $layout['change']  : '',
		);
		$this->element( 'input', $attrs );
		if ( ! empty( $layout['stext'] ) ) {
			e_esc_html( ' ' . $layout['stext'] );
		}
		if ( ! empty( $layout['etext'] ) ) {
			$this->element( 'p', [ ], ' ' . $layout['etext'] );
		}
	}

	private function render_text_color( $data ) {
		$this->render_text( $data );
		$basic = explode( '[', $data['name'] );
		$index = substr( $basic[1], 0, -1 ) . '_color';
		$data['name']  = $basic[0] . '[' . $index . ']';
		$data['value'] = ( isset( $this->form_opts[ $index ] ) ) ? $this->form_opts[ $index ] : $data['layout']['color'];
		$data['layout']['default'] = $data['layout']['color'];
		$data['layout']['text']    = '';
		$this->render_colorpicker( $data );
	}

	private function render_title( $data ) {
		extract( $data );  #  array('ID'=>$item, 'value'=>$data[$item], 'layout'=>$layout[$item], 'name'=>$name)
/*		if ( ! empty( $layout['text'] ) ) {
			$data['layout']['text'] = "<b>{$layout['text']}</b>";
		} */
		$this->render_display( $data );
	}

  /**  Validate functions  **/

	public function validate_single_form( $input ) {
		$output = $this->get_defaults();
		if ( isset( $_POST['reset'] ) ) {
			$object = ( isset( $this->form['title'] ) ) ? $this->form['title'] : $this->form_test['submit']['object'];
			$string = sprintf( $this->form_text['submit']['restore'], $object );
			add_settings_error( $this->slug, 'restore_defaults', $string, 'updated fade' );
			return $output;
		}
		foreach( $input as $ID => $data ) {
			$item = $this->form['layout'][ $ID ];
			$multiple = array( 'array', 'radio_multiple' );
			if ( in_array( $item['render'], $multiple ) ) {
				$item['render'] = ( isset( $item['type'] ) ) ? $item['type'] : 'text';
				$vals = array();
				foreach( $data as $key => $indiv ) {
					$vals[ $key ] = $this->do_validate_function( $indiv, $item );
				}
				$output[ $ID ] = $vals;
			} else {
				$output[ $ID ] = $this->do_validate_function( $data, $item );
			}
		}
		// check for required fields FIXME: notify user
		foreach( $this->form['layout'] as $ID => $item ) {
			if ( is_array( $item ) && isset( $item['require'] ) ) {
				if ( empty( $output[ $ID ] ) ) {
					$output[ $ID ] = $item['default'];
				}
			}
		}
		return apply_filters( "{$this->slug}_validate_settings", $output, $input );
	}

  public function validate_tabbed_form($input) {
    $option = sanitize_key( $_POST['tab'] );
    $output = $this->get_defaults($option);
    if (isset($_POST['reset'])) {
      $object = (isset($this->form[$option]['title'])) ? $this->form[$option]['title'] : $this->form_test['submit']['object'];
      $string = sprintf($this->form_text['submit']['restore'],$object);
      add_settings_error('creatom','restore_defaults',$string,'updated fade');
      return $output;
    }
    foreach($input as $key=>$data) {
      $item = (isset($this->form[$option]['layout'][$key])) ? $this->form[$option]['layout'][$key] : array();
      if ((array)$data==$data) {
        foreach($data as $ID=>$subdata) {
          $output[$key][$ID] = $this->do_validate_function($subdata,$item);
        }
      } else {
        $output[$key] = $this->do_validate_function($data,$item);
      }
    }
    return apply_filters($this->current.'_validate_settings',$output,$input);
  }

	private function do_validate_function( $input, $item ) {
		if ( empty( $item['render'] ) ) {
			$item['render'] = 'non_existing_render_type';
		}
		$func = ( isset( $item['validate'] ) ) ? $item['validate'] : 'validate_' . $item['render'];
		if ( method_exists( $this, $func ) ) {
			$output = $this->$func( $input );
		} elseif ( function_exists( $func ) ) {
			$output = $func( $input );
		} else { // FIXME:  test for data type?
			$output = $this->validate_text( $input );
			$this->logg( 'missing validation function: ' . $func, $item, $input );
		}
		return $output;
	}

	private function validate_checkbox( $input ) {
		return sanitize_key( $input );
	}

	private function validate_checkbox_multiple( $input ) {
		return sanitize_key( $input );
	}

	private function validate_colorpicker( $input ) {
		return ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $input ) ) ? $input : '';
	}

	private function validate_font( $input ) {
		$this->logg( $input );
		return $input; // FIXME NOW!
	}

	private function validate_image( $input ) {
		return apply_filters( 'pre_link_image', $input );
	}

  private function validate_post_content($input) {
    return wp_kses_post($input);
  }

  private function validate_radio($input) {
    return sanitize_key($input);
  }

	private function validate_radio_multiple( $input ) {
		return sanitize_key( $input );
	}

  private function validate_select($input) {
    return sanitize_file_name($input);
  }

	private function validate_select_multiple( $input ) {
		return array_map( array( $this, 'validate_select' ), $input ); // FIXME
	}

	private function validate_spinner( $input ) {
		return $this->validate_text( $input );
	}

	protected function validate_text( $input ) {
		return wp_kses_data( $input );
	}

  private function validate_text_color($input) {
    return $this->validate_text($input);
  }

	private function validate_url( $input ) {
		return apply_filters( 'pre_link_url', $input );
	}


}	#	end of WMN_Form_Admin class


if ( ! function_exists('e_esc_html') ) {
	#   This is just a shorthand function
	function e_esc_html( $string ) {
		echo esc_html( $string );
	}
}
