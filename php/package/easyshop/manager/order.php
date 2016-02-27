<?php
namespace package\easyshop\manager;
use core\base\TManager;

class Order extends TManager{
	
	private $db;	
		
	public function __construct() {
		$this -> db = $this -> get_db();
	}
	
	public function __destruct() {
		
	}
	
	/**
	 * Sets up a new transaction hash
	 * @return {String} $transaction
	 */
	public function set_new_transaction(){
		
		//date in days
		$date = time() / (60*60*24);
		$week_before = $date - 7;
		
		//delete old orders
		$query = "DELETE FROM shop_orders "
			. "WHERE date < ?"
			. "AND "
			. "state < ?";
		
		$this -> db -> query( $query, array($week_before, App::$DB_ORDER_STATE['PAID']) );
		
		$transaction = sha1(mt_rand());
		$_SESSION['transaction'] = $transaction;
		
		$query = "INSERT INTO shop_orders " 
			. "(hash, date) "
			. "VALUES "
			. "(?, ?)";

		$this -> db -> query( $query, array($transaction, $date) );
		
		return $transaction;
	}
	
	/**
	 * Get current transaction from session
	 * @return ${String} $transaction
	 */
	public function get_transaction(){
		
		ob_start();
		if (session_id() == ''){
			session_start();
			session_regenerate_id();
		}

		$transaction = $_SESSION['transaction'];
		if (!isset($transaction)) {
			$transaction = $this -> set_new_transaction();
		}

		ob_end_flush();
		
		return $transaction;
	}
	
	/**
	 * Authorize paypal transaction
	 */
	public function check_transaction( $transaction, $token ){
		
		$transaction_saved = $this -> db -> get_transaction();
		
		if($transaction == $transaction_saved){
				
			$token_saved = $this -> db -> get_paypal_token();
			if($token == $token_saved)
				return true;
		}
		
		return false;
	}
	
	/**
	 * Sets new total amount of given order
	 * @param {Number} $amt - amount
	 * @param {String} $transaction
	 */
	public function set_order_amount($amt, $transaction){
		
		$query = "UPDATE shop_orders "
			. "SET amount = ? "
			. "WHERE hash = ?";
			
		$result = $this -> db -> query( $query, array($amt, $transaction) );
	}
	
	/**
	 * Updates order state according to current state
	 * @param {Int} $state - current state of order
	 * @param {String(40)} $transaction - if null get from session
	 */
	public function update_order_state( $state, $transaction ){
		
		$query = "UPDATE shop_orders "
			. "SET state = ? "
			. "WHERE hash = ?";
			
		$this -> db -> query( $query, array($state, $transaction) );
		
	}
	
	/**
	 * Update product values
	 * @param {Array} $product - ("name", "price", "description", "highlight", "id")
	 */
	public function update_product($userid, $userhash, $product){
			
		$query = "UPDATE shop_products "
			. "SET "
			. "name = ?, "
			. "price = ?, "
			. "description = ?, "
			. "highlight = ? "
			. "WHERE id = ?";
				
		$data = array(
			$product['name'],
			$product['price'],
			$product['description'],
			$product['highlight'],
			$product['id']
		);
			
		$this -> db -> query( $query, $data );
	}
	
	/**
	 * Returns the type of order
	 * 0 - nondigital
	 * 1 - digital
	 * 2 - mixed
	 * @return {Number}
	 */
	public function get_order_type($transaction = null){
		
		if($transaction == null)
			$transaction = $this -> get_transaction();
			
		$query = "SELECT "
				. "SUM( IF(items.digital = 1, 1, 0) ) AS digital, "
				. "SUM( IF(items.digital = 0, 1, 0) ) AS nondigital "
			. "FROM shop_products items "
			. "INNER JOIN shop_bitemorder bio "
				. "ON bio.itemid = items.id "
			. "INNER JOIN shop_orders orders "
				. "ON orders.id = bio.orderid "
			. "WHERE orders.hash = ?";
		
		$result = $this -> fetch_assoc( $query , array($transaction) );
		
		$digital = $result[0]['digital'];
		$nondigital = $result[0]['nondigital'];
		
		$type = 0;
		
		if($digital > 0){
			$type = $SHOP_ORDER_TYPE['DIGITAL'];
			if($nondigital > 0){
				$type = $SHOP_ORDER_TYPE['MIXED'];
			}
		}
		
		return $type;
		
	}
	
	/**
	 * Retrieves order state
	 * @link details in [core\config\App]
	 * @return {Number}
	 */
	public function get_order_state($transaction = null){
		
		if($transaction == null)
			$transaction = $this -> get_transaction();
		
		$query = "SELECT state "
			. "FROM shop_orders "
			. "WHERE hash = ? "
			. "LIMIT 1";
			
		$result = $this -> fetch_assoc( $query, array($transaction) );
		
		return $result[0]['state'];
	}
	
	/**
	 * Retrieves amount of order and details of client
	 * @return {Array} - associative array
	 */
	public function get_order_details(){
				
		$transaction = $this -> get_transaction();
		
		$query = "SELECT * "	
			. "FROM shop_orders o "
			. "WHERE o.hash = ? "
			. "LIMIT 1";
			
		$result = $this -> fetch_assoc( $query, array($transaction) );
		
		return $result[0];
	}
	
	/**
	 * Retrieve amount price of an order
	 * @param (optional) $transaction
	 * @return {Number} amt - amount of order
	 */
	public function get_order_amount($transaction = null){
		
		if($transaction == null)
			$transaction = $this -> get_transaction();
		
		$query = "SELECT amount "
			. "FROM shop_orders "
			. "WHERE hash = ? "
			. "LIMIT 1";
		
		$rows = $this -> fetch_assoc( $query, array($transaction) );
		$amt = $rows[0]['amount'];
		
		return $amt;
	}
	
	/**
	 * Disable order until it is confirmed
	 */
	public function disable_order($transaction = null){
		
		if($transaction == null)
			$transaction = $this -> get_transaction();
		
		$query = "UPDATE shop_orders "
			. "SET "
				. "state = ? "
			. "WHERE hash = ? "
			. "LIMIT 1";
			
		$this -> query( $query, array(App::get_order_state('paused'), $transaction) );	
		
	}
	
	/**
	 * Updates an order adding paypal transaction identification hash
	 */
	public function save_purchase_id( $token, $id ){
		
		$query = "INSERT INTO shop_purchases "
			. "("
				. "orderid, "
				. "paypalid, "
				. "date"
			. ") "
			. "VALUES "
			. "("
				. "(SELECT id FROM shop_orders WHERE paypal = ?), "
				. "?, "
				. "NOW()"
			. ")";
			
		$this -> query($query, array($token, $paypalid));
		
		$transaction = $this -> set_new_transaction();
		$this -> update_order_state(App::$DB_ORDER_STATE['PAID']);
	}
}
	