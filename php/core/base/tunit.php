<?php

/*
 * 
 */

namespace core\base;
use core\app\Application as App;

class TUnit{
	
	public function __construct(){
	}
	
	public function __destruct(){
		
	}
	
	protected function post($key, $value = null){
		
		if($value != null){
			return $_POST[$key];
		}else{
			$_POST[$key] = $value;
		}
	}
	
	protected function set_hook($hook){
		App::set_hook($hook);
	}
	
	protected function add_hook($hook, $function){
		App::hook($hook, $function);
	}
	
	protected function invoke_hook($hook, $args = null){
		$functions = App::get_hooks($hook);
		foreach ($functions as $function) {
			if(function_exists($function)){
				$function($args);
			}
		}
	}
	
	protected function get_db(){
		return App::get_database();
	}
}



?>