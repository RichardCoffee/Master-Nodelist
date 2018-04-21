<?php

class WMN_Plugin_Library {

	use WMN_Trait_Attributes;
	use WMN_Trait_Logging;
	use WMN_Trait_Magic;

	public function __construct() {
		$this->initialize();
		$this->logging_check_function();
	}

	protected function initialize() {
		$this->attributes_register__call();
		$this->logging_register__call();
		if ( WP_DEBUG && function_exists( 'add_action' ) ) {
			add_action( 'deprecated_function_run',    array( $this, 'logging_log_deprecated' ), 10, 3 );
			add_action( 'deprecated_constructor_run', array( $this, 'logging_log_deprecated' ), 10, 3 );
			add_action( 'deprecated_file_included',   array( $this, 'logging_log_deprecated' ), 10, 4 );
			add_action( 'deprecated_argument_run',    array( $this, 'logging_log_deprecated' ), 10, 3 );
			add_action( 'deprecated_hook_run',        array( $this, 'logging_log_deprecated' ), 10, 4 );
			add_action( 'doing_it_wrong_run',         array( $this, 'logging_log_deprecated' ), 10, 3 );
		}
	}


}
