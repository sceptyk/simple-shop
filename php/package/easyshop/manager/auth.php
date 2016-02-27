<?php

namespace package\easyshop\manager;
use core\base\TManager;

class Auth extends TManager{
	
	private $db;
	
	public function __construct( $db ){
		$this -> db = $this -> get_db();
	}
	
	public function authorize(){
		
		if(session_id() = '')
			session_start();
		
		$session = $_POST['session'];
		$timestamp = $_POST['timestamp'];
		
		if( check_session($session) && check_timestamp($timestamp) )
			return true;
		else
			return false;
	}
	
	/**
	 * Check if user has permission for an action
	 * @param $userlevel
	 * @post $userid
	 * @post $userhash
	 * @return {Boolean}
	 */
	public function is_user_allowed($level){
		
		$userid = $_POST['userid'];
		$userhash = $_POST['userhash'];
		
		if(!isset($userid) || !isset($userhash)){
			return false;
		}
		
		if($this -> get_user_level($userid, $userhash) >= $level){
			return true;
		}
		
		return false;
	}
	
	/**
	 * Checks if user credentials are all right
	 * @post userid
	 * @post userhash
	 * @return {Boolean}
	 */
	public function is_user_logged(){
		$userid = $_POST['userid'];
		$userhash = $_POST['userhash'];
		
		if(!isset($userid) || !isset($userhash)){
			return false;
		}
		
		$hash = $this -> db -> select(
			"hash",
			"user", "",
			array($userhash, $userid),
			null,
			"uniqueid",
			null, null,
			1
		);
		
		if( $hash == $userhash ){
			return true;
		}
		
		return false;
	}
	
	/**
	 * Retrieves user level
	 * @param {String} userid
	 * @param {String} userhash
	 * @return {Number} user level or -1 as error
	 */
	public function get_user_level($userid, $userhash){
		
		if(isset($username) && isset($userhash)){
			$table_name = App::get_table("user");
			$query = "SELECT level "
				. "FROM $table_name "
				. "WHERE uniqueid = ? AND userhash = ?";
				
			$result = $this -> db -> fetch_assoc( $query, array($userid, $userhash) );
			
			return $result[0]['level'];
		
		}
		
		return -1;
	}

	/**
	 * Sets user level
	 * @param {String} userid
	 * @param {String} userhash
	 * @param {String} childid
	 * @param {Number} level
	 */
	public function set_user_level($userid, $userhash, $childid, $level){
		
		if($this -> get_user_level($userid, $userhash) > App::$DB_TABLE_USER_LEVELS['SUPERADMIN']){ //TODO constants array
			
			$table_name = App::get_table("user");
			$query = "UPDATE $table_name "
				. "SET level = ? "
				. "WHERE uniqueid = ?";
			$this -> db -> query($query, array($level, $childid));
		}
		
	}
	
	/**
	 * Gives user authorization
	 * @param {Array} $user - ("name", "pass")
	 * @param {Integer} $level - level of authorization
	 * @return {Array} $response - ("logged" - whether is user was logged successfully or not, "message", "user" - username and userhash)
	 */
	public function login_user($user, $level){
		
		$response = array(
			"logged" => false,
			"message" => "Connection initialized",
			"user" => array()
		);
		
		$name = $user['name'];
		$pass = $user['pass'];
		$remember = $user['remember'];
		
		$table = App::get_table("user");
		
		$query = "SELECT uniqueid, salt, password, level "
			. "FROM $table "
			. "WHERE name = ?";
			
		$result = $this -> fetch_assoc( $query, array($name) );
		$salt = $result['salt'];
		$saved_password = $result['password'];
		
		if($saved_password == $this -> hash_password($salt, $pass)){
			
			$saved_level = $result['level'];
			
			if($saved_level >= $level){
			
				$uniqueid = $result['uniqueid'];
				$userhash = $this -> hash_user();
				
				$query = "UPDATE $table "
					. "SET "
					. "hash = ? "
					. "WHERE uniqueid = ?";
					
				$this -> query( $query, array($userhash, $uniqueid) );
				
				$user = array(
					"hash" => $userhash,
					"uniqueid" => $uniqueid
				);
				
				ob_start();
				if(session_id() == "")
					session_start();
				session_regenerate_id();
				
				$_SESSION['userid'] = $uniqueid;
				$_SESSION['userhash'] = $userhash;
				
				ob_end_flush();
				
				if($remember === true){
					
					$expire = time() + (30 * 24 * 60 * 60);
					$path = "/";
					$domain = App::get_app_path();
					
					setcookie("userid", $uniqueid, $expire, $path, $domain, false, true);
					setcookie("userhash", $userhash, $expire, $path, $domain, false, true);
				}
				
				$response['user'] = $user;
				$response['message'] = "Logged in";
				$response['logged'] = true;
				
			}else{
				$response['message'] = "User has no authority";	
			}
			
		}else{
			
			$response['message'] = "Incorrect name or password";
			
		}
		
		return $response;
	}
	
	/**
	 * Check if user exists
	 * @return {Array} $data:
	 * 			code - 1 if user does not exist
	 * 			code - 0 if user exists
	 * 			code - -1 if table empty
	 */
	private function user_exists($username, $email){
		
		$query = "SELECT id "
			. "FROM shop_users "
			. "WHERE name = ? "
			. "OR "
			. "email = ?";
		
		$result = $this -> db -> fetch_assoc( $query, array($username, $email));
		
		if(count($result) == 0){
			return false;
		}
		
		return true;
		
	}
	
	/**
	 * Registers new user
	 * @param {Array} $user - basic fields ("name", "email", "pass", (optional) level)
	 * @return {Boolean} $result - true if no errors
	 */
	public function register_user( $user ){
		
		$username = $user['name'];
		$email = $user['email'];
		$password = $user['pass'];
		$level = $user['level'];
		
		$result = $this -> user_exists($username, $email);
		
		if($result['status'] != 0){
					
			$uniqueid = uniqid("", true);
			$salt = uniqid(mt_rand(1000000, 9999999), true); //30 chars
			$hashed = hash_password($salt, $password);
			
			$query = "INSERT INTO shop_users "
				. "("
					. "uniqueid, "
					. "name, "
					. "email, "
					. "password, "
					. "salt, "
					. "level"
				. ") "
				. "VALUES (?, ?, ?, ?, ?)";
				
				if( !isset( $level ) )  $level = "user";
				$level = App::get_user_level($level);
			$this -> db -> query( $query, array($uniqueid, $username, $email, $hashed, $salt, $level) ); 
				
			if($result['status'] == -1){
				
				$query = "UPDATE shop_users "
					. "SET "
					. "level = ? "
					. "LIMIT 1";
				
				$this -> db -> query( $query, array(App::get_user_level('superadmin')) );
			}
			
			$result['status'] = true;
		}
		
		return $result;
	} 
	
	/**
	 * Hash password
	 * @param $salt
	 * @param $password
	 * @return hashed password
	 */
	public function hash_password($salt, $password){
				
		$hash = hash("sha512", $password . $salt, false);
		
		return $hash;
	}
	
	/**
	 * Generates a unique login session hash for a user
	 * @return hash
	 */
	private function hash_user(){
		
		$hash = hash("sha256", mt_rand(), false);
		
		return $hash;
	}
	
	private function random_string( $length ){
		
	}
	
	public function check_session( $session ){
		
		return $session == session_id();
	}
	
	/**
	 * Setting up a new session (Not implemented) FIXME
	 */
	public function start_session(){
		
		return session_start();
	}
	
	//Time in secs (time window 30secs)
	public function check_timestamp( $timestamp ){
			
		$window = 30; //30 secs
		return abs(time() - $timestamp) < $window;
	}
	
}


?>