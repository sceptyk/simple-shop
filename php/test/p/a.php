<?php

namespace p;
use r\B;
use Exception;

require_once "../r/b.php";

class A{
	
	public function __construct(){
		
		echo "a";
		
		//$b = new B;
		//$b -> write();
		
		$this -> write();
	}
	
	public function __destruct(){
		
	}
	
	public function write(){
		$function = "hello";
		try{
			call_user_func( array(__CLASS__, $function) );
		}catch(Exception $e){
			echo $e -> getMessage();
			//log($e);
		}
	}
	
	private function hello(){
		throw new Exception("bye");
		echo "Hello";
	}
}

new A;

?>