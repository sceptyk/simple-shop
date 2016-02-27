<?php

namespace core\helper;
use core\config\App;
use PDO;
use core\base\THelper;


class Db extends THelper{
	
	private $settings;
	private $connection;
	
	public function __construct(){
		$this -> settings = App::get_database_settings();
		$this -> connect();
		
	}
	
	public function __destruct(){
		$this -> close();
	}
	
	/**
	 * Invoke connection to database
	 */
	public function connect(){
		
		$connection = null;
		
		try{
			$connection = new PDO(
				"mysql:host=" . $this -> settings['HOST'] . ";dbname=" . $this -> settings['NAME'],
				$this -> settings['USER'],
				$this -> settings['PASSWORD'],
				array(
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				)
			);
		}catch(PDOException $e){
			$e -> getMessage();
		}
		
		$this -> connection = $connection;
		
	}
	
	/**
	 * Closes connection
	 */
	private function close(){
		$this -> connection = null;
	}
	
	/**
	 * Single query
	 */
	public function query( $query, $args = array() ){
		
		$sh = null;
		try{
			$sh = $this -> connection -> prepare( $query );
			
			if( !is_array($args) )
				$args = array( $args );
			
			foreach($args as $key => &$arg){
					
				if(is_bool($arg)) $type = PDO::PARAM_BOOL;
				else if(is_numeric($arg)) $type = PDO::PARAM_INT;
				else $type = PDO::PARAM_STR;
				
				$sh -> bindParam(
					is_numeric($key) ? $key + 1 : $key,
					$arg,
					$type
				);
			}
			unset($arg);
			
			$result = $sh -> execute();
		}catch(PDOException $e){
			$this -> log($e -> getMessage());
		}	
		
		if(!$result) $sh = null;
		
		return $sh;
	}
	
	/**
	 * Get last inserted id
	 * @return {String} last inserted id
	 */
	public function last_id(){
		return $this -> connection -> lastInsertId();
	}
		
	/**
	 * Prepared transaction
	 * @param {String} $query
	 * @param {Array} $args
	 * @return {Array} $data - associated fetched data
	 */
	public function fetch_assoc( $query, $args = array() ){
		
		$sh = $this -> query( $query, $args );
		if(!$sh) return null;
		
		$sh -> setFetchMode(PDO::FETCH_ASSOC);
		$data = array();
		while($row = $sh -> fetch()){
			$data[] = $row;
		}
		
		if(count($data) > 0)
			return $data;
		
		return null;
	}
	
	/*START QUERY FUNCTIONS*/
	
	/**
	 * Allows edit prepaired query in custom order
	 * @param $query - prepaired query
	 * Prepaired query: query + !WHERE, !ORDER_BY, !LIMIT etc
	 * @param $extra - value to swap
	 * @param $position - key word for swap (!WHERE, !JOIN, !ORDER_BY, !GROUP_BY, !LIMIT)
	 * @return expanded query
	 */
	public function expand_query($query, $extra, $position){
		
		$pos_and = " " . $position . "AND ";
		
		if($position != "!JOIN"){
			$key = str_replace("_", " ", $position);
			$key = substr($key, 1);
			$key .= " ";
			
			if($position == "!WHERE")
				$con = " AND ";
			else if($position == "!GROUP_BY")
				$con = ", ";
			else if($position == "!ORDER_BY")
				$con = ", ";
			else if($position == "!SET")
				$con = ", ";
		}
		
		$position .= " ";
		
		$query = str_replace($pos_and, $con . $extra . $pos_and, $query);
		$query = str_replace($position, $key . $extra . $pos_and, $query);
		
		return $query;
	}
	
	/**
	 * Clean expanded or prepared query from key words
	 * @param $query - expanded or prepared query
	 * @return clean query for database query
	 */
	public function clean_query($query){
		
		return preg_replace("/!\w+/", "", $query);
	}
	
	/**
	 * Builds dynamic query for select
	 * @param {Array|String} fields - fields to be fetched, 
	 * @important if string function returns array of strings not arrays!
	 * @param {String} table - short table's name
	 * @param {String} (optional) as - alias for table
	 * @param {Array|String} (optional) vars - values for prepared query or single value
	 * @param {Array} (optional) join - join queries
	 * @pattern
	 * array(
	 * 	"join type", // e.g. "INNER JOIN"
	 *  "table_name", // short table name
	 *  "as", // required e.g. "t1"
	 *  "bind query" // e.g "t1.id = t2.id"
	 * )
	 * @param {Array|String} (optional) where - as single name or where conditions as array
	 * @link core\helper\Db::decode_where()
	 * @param {String} (optional) group - group by args
	 * @param {String} (optional) order - order by args
	 * @param {Array|Number} (optional) limit - limit
	 * @important if limit set to 1 function returns single row
	 * 
	 * @return {Array|null} fetched values
	 */
	public function select( $fields, $table, $as = null, $vars = null, $join = null, $where = null, $group = null, $order = null, $limit = null){
		
		$query = "SELECT "
			. (is_array($fields) ? implode( ", ", $fields ) : $fields)
			. "FROM " . Values::get_table($table) . (isset($as) ? " " . $as : "");
		
		if(isset($join)){
			if(is_array($join[0])){
				foreach($join as $part){
					$query .= " " . $part[0] . " " . Values::get_table($part[1]) . " " . $part[2] . " ON " . $part[3];
				}
			}else{
				$query .= " " . $join[0] . " " . Values::get_table($join[1]) . " " . $join[2] . " ON " . $join[3];
			}
			
		}
		
		if(isset($where)){
			$query .= " WHERE ";
			if( is_array($where))
				 $query .= $this -> decode_where($where);
			elseif( is_string($where) )
				$query .= " = ?";
		}
		
		if(isset($group)){
			$query .= " GROUP BY ? ";
			$vars[] = $group;
		}
		
		if(isset($order)){
			$query .= " ORDER BY ? ";
			$vars[] = $order;
		}
		
		$cut = 0;
		if(isset($limit)){
			$query .= " LIMIT ?, ?";
			if( !is_array( $limit ) ){
				if(count($limit) == 1){
					$limit[1] = intval($limit[0]);
					$limit[0] = 0;
				}else{
					$limit[0] = intval($limit[0]);
					$limit[1] = intval($limit[1]);
				}
			}else{
				$limit[1] = intval($limit);
				$limit[0] = 0;
			}
			
			$vars = array_merge( $vars, $limit );
			$cut = $limit[1] - $limit[0];
		}
		
		if( isset($vars) )
		if( !is_array($vars) ){
			$vars = array( $vars );
		}
		
		$result = $this -> fetch_assoc( $query, $vars );
		
		if($result == null) return null;
		
		//if only one value
		if( !is_array( $fields )){
			foreach( $result as &$row ) $result = $result[$field];
			unset($row);
		}
		
		//if limit to 1 row
		if( $cut == 1 ){
			$result = $result[0];
		}
		
		return $result;
	}
	
	/**
	 * Recursive function for where array in select
	 * @param {Array} where - each bundle in new sub-array
	 * @pattern
	 * $where = array(
	 * 	"glue", // "AND" or "OR"
	 * 	"fields", //array as new 'sub-where' expression or as fields (e.g. "id, table.name") or string as field (e.g. "table.id")
	 * 	"sign", //string or array matching fields
	 * 	count //number (optional) if fields string it will be repeat count times
	 * );
	 */
	private function decode_where( $where ){
		
		//Start new bundle
		$query = "(";
		
		//Take 
		$glue = $where[0];
		$fields = $where[1];
		$sign = $where[2];
		$count = $where[3];
			
		//if there is lower level
		if( is_array($fields[0]) ){
			//go deeper for every sub-query
			$query .= $this -> decode_where( $fields[0] );
			for($i=1;$i<count($fields);++$i){
				$query .= " " . $glue . " " . $this -> decode_where( $fields[$i] );
			}
		}else{
					
			//No repetition
			if( is_array($fields) ){
				
				//Check if there is array of signs as well
				$is_sa = is_array( $sign );
				//If sign is an array but length not match take first sign only
				if($is_sa && count($fields) != count($sign)){
					$is_sa = false;
					$sign = $sign[0]; 
				}
				
				$query .= $fields[0] . " " . ($is_sa ? $sign[0] : $sign) . " ?";
				for($i=1;$i<count($fields);++$i){
					$query .= " " . $glue . " " . $fields[$i] . " " . ($is_sa ? $sign[$i] : $sign) . " ?";
				}
				
			}else{
				//With repetition repeat one query
				$query .= $fields . " = ?";
				if(isset($count)){
					for($i=1;$i<$count;++$i){
						$query .= " " . $glue . " " . $fields . $sign . " ?";
					}
				}
			}
			
			
		}
		//Close bundle
		$query .= ")";
		
		return $query;
	}

	/**
	 * Inserts value
	 */
	public function insert( $fields, $table, $vars ){
		
		$query = "INSERT INTO " . App::get_table($table) . " "
			. "(" . ( is_array($fields) ? implode(", ", $fields) : $fields ) . ") "
			. "VALUES "
			. "(";
			
				if(is_array($vars)){
					$length = count($vars);
					if($length > 0){
						$query .= "?";
						for($i=1;$i<$length;++$i){
							$query .= ", ?";
						}
					}
				}else{
					$query .= "?";
					$vars = array( $vars );
				}
		
		$query .= ")";
		
		$this -> query( $query, $vars );
	}
	
	/**
	 * Updates values
	 * @param {Array|String} fields - fields to be updated, 
	 * @important if string function returns array of strings not arrays!
	 * @param {String} table - short table's name
	 * @param {String} (optional) as - alias for table
	 * @param {Array|String} (optional) vars - values for prepared query or single value
	 * @param {Array|String} (optional) where - as single name or where conditions as array
	 * @link core\helper\Db::decode_where()
	 * @param {Array|Number} (optional) limit - limit
	 */
	public function update( $fields, $table, $vars = null, $where = null, $limit = null){
		
		$query = "UPDATE " . App::get_table($table) . " SET ";
		
		if( is_array( $fields ) ){
			$query .= implode(" = ?, ", $fields );
		}elseif( is_string( $fields ) ){
			$query .= $fields;
		}
		$query .= " = ?";
		
		if(isset($where)){
			$query .= " WHERE ";
			if( is_array($where))
				 $query .= $this -> decode_where($where);
			elseif ( is_string($where) )
				$query .= $where . " = ?";
		}
		
		if(isset($limit)){
			$query .= " LIMIT ?, ?";
			if( !is_array( $limit ) ){
				if(count($limit) == 1){
					$limit[1] = intval($limit[0]);
					$limit[0] = 0;
				}else{
					$limit[0] = intval($limit[0]);
					$limit[1] = intval($limit[1]);
				}
			}else{
				$limit[1] = intval($limit);
				$limit[0] = 0;
			}
			
			$vars = array_merge( $vars, $limit );
		}
		
		$this -> query( $query, $vars );
	}
	
	/**
	 * Deletes values
	 * @param {String} table - short table name
	 * @param {Array|String} where
	 * @param {Array|String} limit
	 * @param {Array} vars
	 */
	public function delete( $table, $vars = null, $where = null, $limit = null ){
		
		$query = "DELTE FROM " . Values::get_table($table);
		
		if(isset($where)){
			$query .= " WHERE ";
			if( is_array($where))
				 $query .= $this -> decode_where($where);
			elseif ( is_string($where) )
				$query .= $where . " = ?";
		}
		
		if(isset($limit)){
			$query .= " LIMIT ?, ?";
			if( !is_array( $limit ) ){
				if(count($limit) == 1){
					$limit[1] = intval($limit[0]);
					$limit[0] = 0;
				}else{
					$limit[0] = intval($limit[0]);
					$limit[1] = intval($limit[1]);
				}
			}else{
				$limit[1] = intval($limit);
				$limit[0] = 0;
			}
			
			$vars = array_merge( $vars, $limit );
		}
		
		if( isset($vars))
		if( !is_array($vars)){
			$vars = array($vars);
		}
		
		$this -> query( $query, $vars );
	}
}



?>