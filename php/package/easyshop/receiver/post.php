<?php

namespace package\easyshop\receiver;
use core\base\TBridge;

use core\helper\Settings;

use package\easyshop\manager\User;
use package\easyshop\manager\Cart;
use package\easyshop\manager\Auth;
use package\easyshop\manager\Paypal;

use core\Error;

class Post extends TBridge{
	
	private $cart;
	private $auth;
	private $user;
	private $pay;
	
	private $hs;
	
	public function __construct(){
		$this -> cart = new Cart();
		$this -> auth = new Auth();
		$this -> user = new User();
		$this -> pay = new Paypal();
		
		$this -> hs = new Settings();
	}
	
	/**
	 * Checks authorization of request and call proper function
	 */
	public function execute(){
		
		if($this -> auth -> authorize()){
			
			$function = post("tag");
			
			try{
				$data['result'] = call_user_func( array( __CLASS__, $function ) );
				respond( $data, 0 );
			}
			catch(Error\NoAccess $e){
				$msg = $e->getMessage();	
				log($msg);
				respond(array("action" => "logoff", "message" => $msg), 1);
			}
			catch(Exception $e){
				log($e->getMessage());
			}
		}
		
	}
	
	/**
	 * Prints respond for request
	 * @param {Array} data - array of returned values
	 * @param {Number} (optional) error - 0 if no error
	 */
	protected function respond( $data, $error = 1){
		
		$response = array(
			"error" => $error,
			"message" => "",
			"action" => "none"
		);
		
		$response = array_merge($response, $data);
		
		echo json_encode( $response );
			
	}
	
	/**
	 * Retrieve products to display
	 * @post {Array} filters
	 * @return {Array} data
	 */
	protected function get_products(){
		
		$filters = $_POST['filters'];
		$result = array();
		
		if( $this -> auth -> is_user_allowed( Values::get_user_level("admin")) ){
			$result = $this -> cart -> get_products( $filters, true );
		}else{
			$result = $this -> cart -> get_products( $filters );
		}
		
		return $result;
	}
	
	protected function get_product_detail(){
		
	}
	
	/**
	 * Retrieves categories
	 * @return {Array}
	 */
	protected function get_categories(){
		
		return $this -> cart -> get_categories();
		
	}
	
	/**
	 * Retrieves list of orders for admin preview
	 * @throws Error\NoAccess
	 * @return {Array}
	 */
	protected function get_orders(){
		
		if( $this -> auth -> is_user_allowed( App::get_user_level('moderator') ) ){		
			return $this -> cart -> get_orders();
		}else{
			throw Error\NoAccess;
		}
		
	}
	
	/**
	 * Retrieves current order brief details
	 * @return {Array}
	 */
	protected function get_current_order(){
		return $this -> cart -> get_current_order();
	}
	
	/**
	 * Updates product
	 * @post {Array} product
	 * @link core\manager\Cart -> update_product;
	 * @throws Error\NoAccess
	 * @return {Boolean}
	 */
	protected function update_product(){
		
		$product = $_POST['product'];
		
		if( $this -> auth -> is_user_allowed( App::get_user_level('admin') ) ){
			$this -> cart -> update_product( $product );
		}else{
			throw Error\NoAccess;
		}
		
		return true;
	}
	
	/**
	 * Retrieves admin settings
	 * @throws Error\NoAccess
	 * @return {Array}
	 */
	protected function get_settings(){
		
		if($this -> auth -> is_user_allowed( App::get_user_level('admin') )){
			
			$settings = $_POST['settings'];
			return $this -> user -> get_admin_settings($settings);	
			
		}else{
			throw Error\NoAccess;
		}
	}
	
	/**
	 * Updates admin settings
	 */
	protected function set_settings(){
		
		if($this -> auth -> is_user_allowed( App::get_user_level('admin') )){
			
			$settings = $_POST['settings'];
			$this -> user -> update_settings($settings);	
			
		}else{
			throw Error\NoAccess;
		}
	}
	
	/**
	 * Retrieves confirm order details
	 */
	protected function get_confirm(){
		
		$transaction = $this -> cart -> get_transaction();
		
		if( $this -> cart -> get_order_state( $transaction ) == App::get_order_state('paypal')){
			$data['items'] = $this -> cart -> get_items_by_transaction($transaction);
			$data['orderdetails'] = $this -> cart -> get_order_details();
			
			$this -> cart -> disable_order($transaction);
		}else{
			throw Error\OrderJump;
		}
		
		return $data;
	}
	
	/**
	 * Adds product to current order
	 * @post id
	 * @return amount;
	 */
	protected function add_to_order(){
		$id = $_POST['id'];
		
		return $cart -> add_to_order($id);
	}
	
	/**
	 * Adds new product
	 * @post {String} userid
	 * @post {String} userhash
	 * 
	 * @post {String} title
	 * @post {String} price
	 * @post {String} description
	 * @post {String} category
	 * @post {Number} digital
	 * @post {base64} product
	 * @post {Array({base64})} imgs
	 * @post {Array} tags 
	 */
	protected function add_product(){
		
		if($this -> auth -> is_user_allowed(App::get_user_level('admin'))){
			$this -> cart -> add_product(
				array(
					"title" => $_POST['title'],
					"price" => $_POST['price'],
					"description" => $_POST['description'],
					"category" => $_POST['category'],
					"digital" => (int)($_POST['digital']),
					"product" => $_POST['product'],
					
					"imgs" => $_POST['imgs'],
					"tags" => $_POST['tags']
				)
			);
		}else{
			throw Error\NoAccess;
		}
	}
	
	/**
	 * Deletes product
	 * @post {String} userid
	 * @post {String} userhash
	 * @post {String} id - product id
	 */
	protected function delete_product(){
		
		$itemid = $_POST['id'];
		
		if($this -> auth -> is_user_allowed(App::get_user_level("admin"))){
			$this -> cart -> delete_product($itemid);
		}
	}
	
	/**
	 * Deletes product from order
	 */
	protected function delete_from_order(){
		$id = $_POST['id'];
		
		return $this -> cart -> delete_from_order($id);
	}
	
	/**
	 * Logs user
	 * @post {String} username
	 * @post {String} password
	 * @post {String} (optional) remember
	 * @post {Number} level - requested user level, default = "user"
	 */
	protected function log_user(){
			
		$user = array(
			"name" => $_POST['username'],
			"pass" => $_POST['password'],
			"remember" => filter_var($_POST['remember'], FILTER_VALIDATE_BOOLEAN)
		); 
		
		$level = $_POST['level'];
		if( !isset($level) ){
			$level = App::get_user_level('user');
		}
		
		$result = $this -> auth -> login_user($user, $level);
		if($result['logged']){
			return $result['user'];
		}else{
			throw Error\NoAccess;
		}
	}
	
	/**
	 * Registers user
	 * @post username
	 * @post password
	 * @post email
	 */
	protected function register_user(){
		$user = array(
			"name" => $_POST['username'],
			"email" => $_POST['email'],
			"pass" => $_POST['password']
		);
		
		$result = $this -> auth -> register_user($user);
		if($result['status']){
			return $result['status'];
		}else{
			throw Exception("error during registration");
		}
	}
	
	/**
	 * Request precheckout - start checkout (sending selected items)
	 */
	protected function precheckout(){
		
		$items = $_POST['order'];
		
		//TODO
		$this -> cart -> add_list_to_order($items);
		
	}
	
	/**
	 * Request checkout
	 */
	protected function checkout(){
		
		$userdata = $_POST['missing'];
		if( isset($userdata) ){
			$this -> user -> set_user_data($usedata);
		}
		
		$missing = $this -> pay -> check_client_data();
		if( !isset($missing) ){
			$data['url'] = $this -> pay -> request_checkout();
		}else{
			$data['missing'] = $missing;
		}
		
		return $data;
	} 
	
	/**
	 * Request payment
	 */
	 protected function pay(){
	 	
		$paypal = $_POST['paypal'];
		$this -> pay -> request_payment($paypal);
		
	 }
	 
	 /**
	  * Updates user data
	  */
	 protected function update_user(){
	 	
		if( $this -> auth -> is_user_logged() ){
			$id = $_POST['userid'];
			$name = $_POST['username'];
			$pass = $_POST['userpass'];
			$email = $_POST['useremail']; 
			
			$this -> user -> update_user( $id, $name, $pass, $email);
		}
	 }
	 
	 /**
	  * An admin user can add new user with level not higher than his
	  * @post username
	  */
	 protected function add_new_user(){
	 	
		$user = array(
			"name" => $_POST['name'],
			"pass" => $_POST['pass'],
			"email" => $_POST['email'],
			"level" => $_POST['level']
		);
		
		if( $this -> auth -> is_user_allowed( $user['level'] ) ){
			
			$result = $this -> auth -> register_user($user);
			if($result['status']){
				return $result['status'];
			}else{
				throw Exception("error during registration");
			}
		}else{
			throw Error\NoAccess;
		}
		
	 }
}
