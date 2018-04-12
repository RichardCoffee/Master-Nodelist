<?php

class WMN_Form_Field_Date extends WMN_Form_Field_Field {


	public function __construct( $args ) {
		$this->sanitize = array( $this, 'sanitize' );
		parent::__construct( $args );
	}

	public function date() { ?>
		<div class="input-group"><?php
			$this->label();
			$this->input(); ?>
		</div><?php
	}

	public function input() {
		$visible = array(
			'type'  => $this->type,
			'id'    => 'visible_' . $this->field_id,
			'name'  => 'visible_' . $this->field_name,
			'size'  => 10,
			'class' => 'form-control inline date',
			'value' => $this->form_date(),
			'data-altfield' => $this->field_name,
			'onchange'      => 'fix_jquery_datepicker(this);'
		);
		$hidden = array(
			'type'  => 'hidden',
			'id'    => $this->field_id,
			'name'  => $this->field_name,
			'value' => $this->deform_date()
		);
		$this->apply_attrs_element( 'input', $visible );
		$this->apply_attrs_element( 'input', $hidden );
	}

	# convert to unix timestamp
	public function deform_date() {
		$check = intval( $this->value, 10 );
		if ( $check < 3000 ) { // probably a date string
			if ( $unix = strtotime( $this->value ) ) {
				return $unix;
			}
		}
		return $this->value;
	}

	# convert to formatted date
	public function form_date( $reset = false ) {
		//  check for unix time before formatting
		$check = intval($this->value,10);
		if ( $reset && $check < 3000 ) {
			$check = strtotime( $this->value );
		}
		if ( $check > 3000 ) {  // large year value - assumed unix time
			$format = get_option( 'date_format' );
			return date( $format, $check );
		}
		return $this->value;
	}

	public function sanitize( $date ) {
		$format = get_option( 'date_format' );
		$date_format= DateTime::createFromFormat($format, $date);
		if( ! $date_format ) {
			return false;
		}
		return $date;
	}


}
