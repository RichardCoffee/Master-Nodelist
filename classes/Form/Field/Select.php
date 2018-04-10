<?php

class WMN_Form_Field_Select extends WMN_Form_Field_Field {

	protected $choices = array();
	protected $type    = 'select';

	public function __construct( $args ) {
		parent::__construct( $args );
		$this->sanitize = array( $this, 'sanitize' );
	}

	public function select() {
		$library = $this->library;
		if ( $this->choices ) {
			if ( ! empty( $layout['text'] ) ) {
				echo '<div class="form-select-text"> ' . esc_attr( $layout['text'] ) . '</div>';
			}
			$select = array(
				'id'    => $this->field_id,
				'name'  => $this->field_name,
				'class' => $this->field_css
			);
			if ( strpos( '[]', $this->field_name ) ) {
				$select['multiple'] = 'multiple';
			}
			if ( $this->onchange ) {
				$select['onchange'] = $this->onchange;
			} ?>
			<select <?php $library()->apply_attrs( $select ); ?>><?php
				if ( is_callable( $this->choices ) ) {
					call_user_func( $this->choices );
				} else if ( is_array( $this->choices ) ) {
					$assoc = is_assoc( $this->choices );
					foreach( $this->choices as $key => $text ) {
						$attrs = array(
							'value'    => ( $assoc ) ? $key : $text,
							'selected' => ( $assoc ) ?
								( (  $key === $this->field_value ) ? 'selected' : '' ) :
								( ( $text === $this->field_value ) ? 'selected' : '' )
						);
						$library()->apply_attrs_element( 'option', $attrs, ' ' . $text . ' ' );
					}
				}?>
			</select><?php
		}
	}


}


/**
 *  check if an array is an assocative array
 *
 * @since 20180410
 * @link https://stackoverflow.com/questions/5996749/determine-whether-an-array-is-associative-hash-or-not
 * @param array $array
 * @return bool
 */
if ( ! function_exists( 'is_assoc' ) ) {
	function is_assoc( array $array ) {
		// Keys of the array
		$keys = array_keys($array);
		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys( $keys ) !== $keys;
	}
}
