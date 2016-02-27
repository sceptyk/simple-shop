<?php
namespace core\helper;
use core\base\THelper;

class Settings extends THelper{
	
	private $db;
	
	public function __construct( $db ){
		$this -> db = $this -> get_db();
	}
	
	/**
	 * Saves a single setting pair
	 * @param {String} name
	 * @param {String} value
	 */
	private function set_setting( $name, $value ){
		
		$this -> db -> insert(
			array("name", "value"),
			"setting",
			array($name, $value)
		);
	}
	
	/**
	 * Saves settings, arguments as $name and $value or as associative array in first argument
	 * @param {Array} name - array of names or associative array of pairs
	 * @param {Array} value - array of values or null
	 */
	public function set_settings( $name, $value = null ){
		
		try{
			foreach ($name as $key => $value) 
				$this -> set_setting($key, $value);
				
		}catch(Exception $e){
			
		}
		
	}
	
	/**
	 * Retrieves setting value from database
	 * @param {String} name
	 */
	public function get_setting( $name ){
		
		return $this -> db -> select(
			"value",
			"setting", "",
			$name,
			null, null, null, null,
			1
		);
	}
	
	/**
	 * Retrieves bundle of settings
	 * @param {Array} name - settings' names
	 * @return {Array} value - associative array name => value
	 */
	public function get_settings( $name ){
			
		$value = array();
			
		foreach ($name as $key) {
			$value[$key] = $this -> get_setting( $key );
		}
		
		return $value;
	}
	
	/**
	 * Check if setting exists
	 * @param {String} name
	 */
	public function setting_exists( $name ){
		
		if( !isset( $this -> get_setting($name) ) ){
			return false;
		}
		
		return true;
	}
	
	/**
	 * Updates setting value
	 * @param {String} name
	 * @param {String} value - new value
	 */
	public function update_setting( $name, $newvalue ){
		
		if( $this -> setting_exists($name) ){
			
			$this -> db -> update(
				"value",
				"setting",
				array( $newvalue, $name ), 
				"name"
			);
			
		}else{
			$this -> set_setting($name, $newvalue);
		}
		
	}
	
	/**
	 * Updates bundle of settings
	 * @param {Array} pair - associative array of pairs name => value
	 */
	public function update_settings( $pair ){
		foreach ($pair as $key => $value) {
			$this -> update_setting($key, $value);
		}
	}
	
}
