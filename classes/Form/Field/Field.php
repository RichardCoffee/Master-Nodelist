<?php

/*
 *  File:  classes/Form/Field/Field.php
 *
 *  Note:  The sanitize callback may be called twice, as per https://core.trac.wordpress.org/ticket/21989
 *
 *  Todo: property names should conform to attribute names
 */

abstract class WMN_Form_Field_Field {

	protected $field_css   = '';         #  field css
	protected $default     = '';         ## default value
	protected $field_help  = '';         #  used for tooltip text
	protected $field_id    = '';         #  field id
	protected $field_name  = '';         #  field name
	protected $type        = 'text';     ## input type
	protected $field_value = '';         #  field value
	protected $label_css   = '';         #  label css
	protected $description = '';         ## label text
	protected $onchange    = null;       #  onchange attribute
	protected $placeholder = '';         #  placeholder text
#	protected $post_id;                  #  wordpress post id number
	protected $sanitize    = 'esc_attr'; #  default sanitize method
	protected $see_label   = true;       #  is the label visible?
	protected $form_control = true;      #  add form-control css

	protected static $date_format = 'm/d/y';

	use WMN_Trait_Attributes;
	use WMN_Trait_Magic;
	use WMN_Trait_ParseArgs;

	public function __construct( $args = array() ) {
#		if ( empty( self::$date_format ) ) {
#			self::$date_format = get_option( 'date_format' );
#		}
		$this->parse_args( $args );
		if ( ( empty( $this->placeholder ) ) && ( ! empty( $this->description ) ) ) {
			$this->placeholder = $this->description;
		}
		if ( empty( $this->field_id ) ) {
			$this->field_id = $this->field_name;
		}
		if ( $this->form_control ) {
			$this->add_form_control_css();
		}
		$this->field_value = $this->sanitize( $this->field_value );
	}

	public function get_date_format() {
		return static::$date_format;
	}

	public function input() {
		$this->element( 'input', $this->get_input_attributes() );
	}

	public function get_input() {
		return $this->get_element( 'input', $this->get_input_attributes() );
	}

	protected function get_input_attributes() {
		$attrs = array(
			'id'          => $this->field_id,
			'type'        => $this->type,
			'class'       => $this->field_css,
			'name'        => $this->field_name,
			'value'       => $this->field_value,
			'placeholder' => $this->placeholder,
		);
		return $attrs;
	}

	protected function label() {
		if ( empty( $this->description ) ) {
			return;
		}
		$this->element( 'label', $this->get_label_attributes(), $this->description );
	}

	protected function get_label() {
		if ( empty( $this->description ) ) {
			return '';
		}
		return $this->get_element( 'label', $this->get_label_attributes(), $this->description );
	}

	protected function get_label_attributes() {
		$attrs = array(
			'id'    => $this->field_id . '_label',
			'class' => $this->label_css . ( ! $this->see_label ) ? ' screen-reader-text' : '',
			'for'   => $this->field_id,
		);
		return $attrs;
	}

	protected function add_form_control_css( $new = 'form-control' ) {
		$css = explode( ' ', $this->field_css );
		if ( ! in_array( $new, $css ) ) {
			$css[] = $new;
		}
		$this->field_css = implode( ' ', $css );
	}

	public function sanitize( $input ) {
		if ( $this->sanitize && is_callable( $this->sanitize ) ) {
			$output = call_user_func( $this->sanitize, $input );
		} else {
			$output = wp_strip_all_tags( wp_unslash( $input ) );
		}
		return $output;
	}

}
