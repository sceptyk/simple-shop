<?php

/**
 * Credentials here
 * 
 * Documentation form:
 * Description
 * @important (optional) important note
 * ---
 * @deprecated
 * ---
 * @param {type|type|...} (optional) name - description
 * @important (optional) important note about param
 * @link (optional) reference to other function
 * @pattern (optional) pattern of param ( 1 or more )
 * ---
 * @post {type|type|...} (optional) name - post variable $_POST['name']
 * ...
 * ---
 * @throws
 * ---
 * @return {type|type|...} description
 * ...
 */

namespace core\app;

include_once("constants.php");

set_include_path(PHP_PATH);
spl_autoload_extensions(".php");
spl_autoload_register();

App::init();

class App{
	
	private static $Receiver = null;
	private static $hooks = array();
	private static $db = null;
	
	/**
	 * Retrieves initial settings for database connection
	 * @return {Array}
	 */
	public static function get_database_settings(){
		return array(
			"HOST" => DB_HOST,
			"USER" => DB_USER,
			"PASSWORD" => DB_PASSWORD,
			"NAME" => DB_NAME
		);
	}
	
	/**
	 * Retrieves application php path
	 */
	public static function get_php_path(){
		return PHP_PATH;
	}
	
	/**
	 * Retrieves application path
	 */
	public static function get_app_path(){
		return APP_PATH;
	}
	
	/**
	 * Retrieves api point receiver
	 */
	public static function get_receiver(){
		
		$receiver = APP_RECEIVER;
		
		if(self::$Receiver === null){
			if(class_exists($receiver))
				self::$Receiver = new $receiver();
		}
		
		return self::$Receiver;
	}
	
	/**
	 * Define hook point
	 */
	public static function set_hook( $hook ){
			
		if( !array_key_exists( $hook, self::$hooks ) ){
			self::$hooks[$hook] = array();
		}
		
	}
	
	/**
	 * Push new hooked function
	 * @param {String} hook - hook name
	 * @param {String} function - full function root
	 * @return {Boolean} false if there is no hook
	 */
	public static function hook( $hook, $function ){
				
		if( !array_key_exists( $hook, self::$hooks ) ){
			self::$hooks[$hook] = array();
		}	
		
		self::$hooks[$hook][] = $function;
		
		return false;
	}
	
	/**
	 * Retrieves hooked functions
	 * @param {String} hook
	 * @return {Array|null}
	 */
	public static function get_hooks( $hook ){
		if( !array_key_exists( $hook, self::$hooks ) ){
			return null;
		}	
		
		return self::$hooks;
	}
	
	/**
	 * Retrieves database handler
	 */
	public static function get_database(){
		if(self::$db === null){
			self::$db = new core\helper\Db();
		}
		
		return self::$db;
	}
	
	/**
	 * Retrieves product image url
	 */
	public static function getItemImageSrc($url){
		return "data/" . $url;
	}
	
	
	/**
	 * Allows to receive file extension from base64 mime type
	 */
	public static function getBase64Type($mime){
		
		if($mime == "application/x-7z-compressed"){
			return "7z";
		}
		else if($mime == "text/x-c"){
			return "c";
		}
		else if($mime == "text/css"){
			return "css";
		}
		else if($mime == "text/x-java-source,java"){
			return "java";
		}
		else if($mime == "application/javascript"){
			return "js";
		}
		else if($mime == "image/jpeg"){
			return "jpg";
		}
		else if($mime == "application/x-msdownload"){
			return "exe";
		}
		else if($mime == "application/vnd.ms-excel"){
			return "xls";
		}
		else if($mime == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
			return "xlsx";
		}
		else if($mime == "application/vnd.openxmlformats-officedocument.wordprocessingml.document"){
			return "docx";
		}
		else if($mime == "application/vnd.openxmlformats-officedocument.presentationml.presentation"){
			return "pptx";
		}
		else if($mime == "video/x-ms-wmv"){
			return "wmv";
		}
		else if($mime == "text/vnd.fly"){
			return "fly";
		}
		else if($mime == "video/mpeg"){
			return "mpeg";
		}
		else if($mime == "video/mp4"){
			return "mp4";
		}
		else if($mime == "application/mp4"){
			return "mp4";
		}
		else if($mime == "image/tiff"){
			return "tiff";
		}
		else if($mime == "text/plain"){
			return "txt";
		}
		else if($mime == "audio/x-wav"){
			return "wav";
		}
		else if($mime == "application/zip"){
			return "zip";
		}
		else if($mime == "application/x-zip-compressed"){
			return "zip";
		}
		else if($mime == "image/png"){
			return "png";
		}
		
	}
}


?>