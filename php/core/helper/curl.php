<?php
namespace core\helper;
use core\base\THelper;

class Curl extends THelper{
	
	private $curl;
	
	public function __construct($url){
		$this -> curl = curl_init();
		
		curl_setopt($this -> curl, CURLOPT_URL, $url);
		curl_setopt($this -> curl, CURLOPT_RETURNTRANSFER, true);
		
	}
	
	public function __destruct(){
		
	}
	
	/**
	 * Handle post messaging
	 * @param {Array} $data - array of data
	 * @param {Boolean} $nvp - will parse respond to an array
 	 */
	public function send_post($data, $nvp = false){
		
		curl_setopt_array($this -> curl, array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query($data)
		));
		
		$response = curl_exec($this -> curl);
		if($nvp){
			$response = $this -> parse_nvp($response);
		}
		
		//TODO handle errors
		curl_close($this -> curl);
		return $response;
	}
	
	/**
	 * Parses name-value-pair string to associative array
	 * @param {String} $nvp - name value pair
	 */
	private function parse_nvp($nvp){
		$data = array();
		parse_str($nvp, $data);
		
		return $data;
	}
	
}
	

?>