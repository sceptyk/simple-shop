<?php
namespace core\helper;
use core\base\THelper;

class User extends THelper{
	
	private $db;
		
	public function __construct() {
		$this -> db = $this -> get_db();
	}
	
	public function __destruct() {
		
	}
	
	/**
	 * Get user email (for checkout)
	 */
	public function get_client_data($transaction){
			
		$query = "SELECT * "
			. "FROM shop_clients "
			. "WHERE orders.hash = ?"
			. "LIMIT 1";
		
		$rows = $this -> db -> fetch_assoc( $query, array($transaction) );
		return $rows[0];
	}
}