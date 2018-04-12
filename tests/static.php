<?php

class Static_Base {
	public static $courier;
}

class One extends Static_Base {
	public function __construct() {
		self::$courier = 'Child One';
	}
}

class Two extends Static_Base {
	public function __construct() {
		self::$courier = 'Child Two';
	}
}

$a = new One;
echo "{$a::$courier}\n";

$b = new Two;
echo "{$b::$courier}\n";

echo "{$a::$courier}\n";
