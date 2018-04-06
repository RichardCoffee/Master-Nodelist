<?php

class WMN_Plugin_Library {

	use WMN_Trait_Attributes;
	use WMN_Trait_Logging;
	use WMN_Trait_Magic;

	public function __construct() {
#		static $called = false;
#		if ( ! $called ) {
			$this->initialize();
#		} else {
#			$this->log( self::$magic__call );
#		}
#		$called = true;
	}

	protected function initialize() {
		self::register_call( array( $this, 'logging_get_calling_function_name' ), 'get_calling_function' );
		self::register_call( array( $this, 'logging_was_called_by' ),             'was_called_by' );
		if ( WP_DEBUG ) {
			add_action( 'deprecated_function_run',    array( $this, 'logging_log_deprecated' ), 10, 3 );
			add_action( 'deprecated_constructor_run', array( $this, 'logging_log_deprecated' ), 10, 3 );
			add_action( 'deprecated_file_included',   array( $this, 'logging_log_deprecated' ), 10, 4 );
			add_action( 'deprecated_argument_run',    array( $this, 'logging_log_deprecated' ), 10, 3 );
			add_action( 'deprecated_hook_run',        array( $this, 'logging_log_deprecated' ), 10, 4 );
			add_action( 'doing_it_wrong_run',         array( $this, 'logging_log_deprecated' ), 10, 3 );
		}
	}

	public function magic__call() {
		return self::$magic__call;
	}


}
