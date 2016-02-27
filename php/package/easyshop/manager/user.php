<?php

namespace package\easyshop\manager;
use core\base\TManger;

use core\helper\Order;
use core\helper\Settings;
use core\helper\User as helperUser;

use package\easyshop\manager\Auth;


class User extends TManager{
	
	private $db;
	private $ho;
	private $hs;
	private $hu;
	private $auth;
	
	public function __construct(){
		$this -> db = $this -> get_db();
		$this -> ho = new Order();
		$this -> hs = new Settings();
		$this -> hu = new helperUser;
		$this -> auth = new Auth();
	}
	
	/**
	 * Retrieves user data needed to complete order by order type
	 * @param {Number} $order_type
	 * @param {String} $transaction
	 * @return {Array}
	 */
	public function get_user_data($order_type, $transaction = null){
				
		if($transaction == null)
			$transaction = $this -> ho -> get_transaction();
		
		$query = "SELECT c.name, c.email, c.address, c.phone "
			. "FROM shop_clients c "
			. "INNER JOIN shop_bclientorder b "
				. "ON b.clientid = c.id "
			. "INNER JOIN shop_orders o "
				. "ON o.id = b.orderid "
			. "WHERE o.hash = ? "
			. "LIMIT 1";
				
		$data = $this -> fetch_assoc( $query, array($transaction) );
		
		return $data[0];
	}
	
	/**
	 * Get current client
	 */
	 public function get_client($transaction = null){
	 	
	 }
	
	/**
	 * Check if user is logged in
	 * @return {Boolean}
	 */
	public function is_client_known($transaction = null){
		
		if($transaction == null)
			$transaction = $this -> ho -> get_transaction();
		
		$query = "SELECT c.userid "
			. "FROM shop_clients c "
			. "INNER JOIN shop_bclientorder bco "
			. "ON bco.clientid = c.id "
			. "INNER JOIN shop_orders o "
			. "ON bco.orderid = o.id "
			. "WHERE hash = ? "
			. "LIMIT 1";
			
		$result = $this -> fetch_assoc( $query, array($transaction) );
		
		if( $result[0]['userid'] != 0 ){
			return true;
		}
		
		return false;
	}
	
	/**
	 * Sets data of client binded to an order
	 * @param {Array} $data - client data ("name", "address", "email", "phone")
	 */
	public function set_user_data($data, $transaction = null){
		
		if($transaction == null)
			$transaction = $this -> ho -> get_transaction();
		
		if(isset($data['user'])){
				
			//TODO bind with user or custom
			
		}else{
			
			//try if client exists
			$query = "SELECT c.id "
				. "FROM shop_clients c "
				. "INNER JOIN shop_bclientorder b "
					. "ON b.clientid = c.id "
				. "INNER JOIN shop_orders o "
					. "ON b.orderid = o.id "
				. "WHERE o.hash = ?";
			
			$result = $this -> fetch_assoc( $query, array($transaction) );
			$clientid;
			
			//if not insert one
			if(!isset($result)){
				
				$query = "INSERT INTO shop_clients VALUES ()";
				$this -> query( $query );
				$clientid = $this -> last_id();
				
				$query = "INSERT INTO shop_bclientorder "
					. "("
						. "clientid, "
						. "orderid" 
					. ") "
					. "VALUES "
					. "("
						. "?, "
						. "(SELECT id FROM shop_orders WHERE hash = ?)"
					. ")";
					
				$this -> query( $query, array($clientid, $transaction) );
			}else{
				$clientid = $result[0]['id'];
			}
			
			//update data of client in current order
			$prepair = "UPDATE shop_clients c !SET !WHERE !END";
			$values = array();
			
			if(isset($data['name'])){
				$set = "c.name = ?";
				$prepair = $this -> expand_query($prepair, $set, "!SET");
				$values[] = $data['name'];
			}
			
			if(isset($data['address'])){
				$set = "c.address = ?";
				$prepair = $this -> expand_query($prepair, $set, "!SET");
				$values[] = $data['address'];
			}
			
			if(isset($data['email'])){
				$set = "c.email = ?";
				$prepair = $this -> expand_query($prepair, $set, "!SET");
				$values[] = $data['email'];
			}
			
			if(isset($data['phone'])){
				$set = "c.phone = ?";
				$prepair = $this -> expand_query($prepair, $set, "!SET");
				$values[] = $data['phone'];
			}
			
			$where = "c.id = ?";
			$prepair = $this -> expand_query($prepair, $where, "!WHERE");
			$values[] = $clientid;
			
			$query = $this -> clean_query($prepair);
			
			$this -> query( $query, $values );
		}
		
		
	}
	
	/**
	 * Get user email (for checkout)
	 */
	public function get_user_email( $transaction = null ){
		
		if($transaction == null){
			$transaction = $this -> ho -> get_transaction();
		}
		$client = $this -> hu -> get_client_data($transaction);
		
		return $client['email'];
	}

	/**
	 * Updates user settings
	 */
	public function update_user( $userid, $username, $userpass, $useremail ){
		
		$salt = $this -> db -> select(
			"salt",
			"user", "",
			array($userid),
			null,
			"uniqueid",
			null, null,
			1
		);
		
		$userpass = $this -> auth -> hash_password($salt, $userpass);
		
		$this -> db -> update(
			array("name, password, email"),
			"user",
			array($username, $userpass, $useremail, $userid),
			"uniqueid"
		);
	}
}

?>