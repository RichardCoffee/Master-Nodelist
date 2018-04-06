<?php

include_once( '../classes/Trait/Attributes.php' );
include_once( '../classes/Trait/Logging.php' );
include_once( '../classes/Trait/Magic.php' );
include_once( '../classes/Plugin/Library.php' );

function wmn() {
	static $library;
	if ( empty( $library ) ) {
		$library = new WMN_Plugin_Library;
	}
	return $library;
}

$test = wmn();
print_r($test);

$non_call = wmn()->non_function();
print "non-call: $non_call\n";
