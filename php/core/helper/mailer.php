<?php
namespace core\manager;
use core\base\THelper;

class Mailer extends THelper{
	
	
	public function __construct() {
		
	}
	
	public function __destruct() {
		
	}
	
	public function send_file($files, $entries){
		
		require_once "helper/HtmlHelper.php";
		$hh = new HtmlHelper();
		
		$settings = $this -> db -> get_shop_email_settings();
		$path = $settings['root'] . "/download/";	
		
		$boundary = md5(mt_rand());
		$eol = PHP_EOL;
		
		$message = $hh -> get_email_content($entries);
		
		$header  = "From: " . $settings['name'] . $eol
			.  "Reply-to: " . $settings['c_email'] . $eol
			.  "MIME-Version: 1.0" . $eol
			.  "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"" . $eol . $eol
			.  "This is a multi-part message in MIME format." . $eol
			
			.  "--" . $boundary . $eol
			
			.  "Content-type:text/html; charset=UTF-8" . $eol
			.  "Content-Transfer-Encoding: 7bit" . $eol . $eol
			.  $message . $eol . $eol //TODO
			
			.  "--" . $boundary . $eol;
			
			foreach( $files as $file ){
				$filename = $path . $file['src'];
				//In case of error try fread
				$attachment = chunk_split(base64_encode(file_get_contents($filename)));
			
				$header .=  "Content-Type: application/octet-stream; name=\"". $file['name'] . "\"" . $eol
					.  "Content-Transfer-Encoding: base64" . $eol
					.  "Content-Disposition: attachment" . $eol . $eol
					.  $attachment . $eol . $eol
					
					.  "--" . $boundary . $eol;
			}
		
		$result = mail($entries['email'], $entries['title'], "", $header);
		//TODO handle errors
	}
}
?>