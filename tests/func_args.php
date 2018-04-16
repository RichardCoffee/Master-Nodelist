<?php


function func_args( $test = 'content' ) {
	echo "Args:  " . func_num_args() . "\n";
}

func_args( 'test' );

func_args();
