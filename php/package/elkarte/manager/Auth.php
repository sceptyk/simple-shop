<?php

//All function that can be implemented customly by new user
namespace package\elkarte\manager;

use core\base\TManager;

require_once "SSI.php"; //FIXME

class Auth extends TManager{
	
	public function __construct(){
		
	}
	
	public function __destruct(){
		
	}
	
	/**
	 * 
	 */
	public function is_user_logged(){
		
		global $user_info;
		
		$user = array();
		if( isset($user_info) ){
			
			if(isset($user_info['id'])
				&& isset($user_info['name'])
				&& isset($user_info['email'])){
					$user = array(
						"id" => $user_info['id'],
						"name" => $user_info['name'],
						"email" => $user_info['email']
					);
				}
			
		}
		
	}
	
	public function is_user_registered(){
		
	}
	
	public function register_user(){
		
	}
	
	public function login_user(){
		
	}
	
}


?>