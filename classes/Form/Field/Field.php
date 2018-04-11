<?php

/*
 *  File:  classes/Form/Field/Field.php
 *
 *  Note:  The sanitize callback may be called twice, as per https://core.trac.wordpress.org/ticket/21989
 */

abstract class WMN_Form_Field_Field {

	protected $field_css  = '';         # field css
	protected $field_default;           # default value
	protected $field_help = '';         # used for tooltip text
	protected $field_id;                # field id
	protected $field_name;              # field name
	protected $field_postext = '';      # text shown below input
	protected $field_pretext = '';      # text shown above input
	protected $type          = 'text';  # input type
	protected $field_value;             # field value
	protected $label_css   = '';        # label css
	protected $description = '';        # label text
	protected $onchange    = null;      # onchange attribute
	protected $placeholder = '';        # placeholder text
#	protected $post_id;                 # wordpress post id number
	protected $sanitize   = 'esc_attr'; # default sanitize method
	protected $see_label  = true;       # is the label visible?

	use WMN_Trait_Attributes;
	use WMN_Trait_Magic;
	use WMN_Trait_ParseArgs;

	public function __construct( $args ) {
		$this->parse_args( $args );
		if ( ( empty( $this->placeholder ) ) && ( ! empty( $this->description ) ) ) {
			$this->placeholder = $this->description;
		}
		if ( empty( $this->field_id ) ) {
			$this->field_id = $this->field_name;
		}
	}

	public function input() {
		$attrs = array(
			'id'          => $this->field_id,
			'type'        => $this->type,
			'class'       => $this->field_css,
			'name'        => $this->field_name,
			'value'       => $this->field_value,
			'placeholder' => $this->placeholder,
		);
		$this->apply_attrs_tag( 'input', $attrs );
	}

	protected function label() {
		if ( empty( $this->description ) ) {
			return '';
		}
		$attrs = array(
			'id'         => $this->field_id . '_label',
			'class'      => $this->label_css . ( ! $this->see_label ) ? ' screen-reader-text' : '',
			'for'        => $this->field_id,
		);
		return $this->get_apply_attrs_element( 'label', $attrs, $this->description );
	}

	public function sanitize( $input ) {
		if ( $this->sanitize && is_callable( $this->sanitize ) ) {
			$output = call_user_func( $this->sanitize, $input );
		} else {
			$output = wp_strip_all_tags( stripslashes( $input ) );
		}
		return $output;
	}

}
