<?php
namespace r\s;

class C{
	
	public function __construct(){
		
		echo "c";
		
	}
	
	public function __destruct(){
		
	}
	
	public function write(){
		$callers=debug_backtrace();
		echo $callers[1]['function'];
		echo end(explode("\\", __CLASS__)) . " -> " . __FUNCTION__;
	}
	
}

?>