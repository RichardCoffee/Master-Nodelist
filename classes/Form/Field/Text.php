<?php

class WMN_Form_field_Text extends WMN_Form_Field_Field {



	public function text() {
		$this->add_form_control_css(); ?>
		<div class="input-group"><?php
			$this->label();
			$this->input(); ?>
		</div><?php
	}


}
