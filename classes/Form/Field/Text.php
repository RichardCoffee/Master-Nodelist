<?php

class WMN_Form_Field_Text extends WMN_Form_Field_Field {


	public function text() { ?>
		<div class="input-group">
			<span class="input-group-addon"></span>
<?php
#			$this->addon();
			$this->label();
			$this->input(); ?>
		</div><?php
	}


}
