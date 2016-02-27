<?php
	
	function print_html_as_var(){
		$handler = fopen(dirname(__FILE__) . "/../html/probe.html", "r");
		if($handler){
			echo "<code>";
			echo "var <var>div</var> = ";
			while(($line = fgets($handler)) !== false){
				//echo "<br />";
				echo "<pre style='display:inline-block'>";
				$i = 0;
				while($line[$i++] == "\t") echo "	";
				echo "+ </pre>";
				echo "\"" . htmlspecialchars($line) . "\"";
			}
			echo ";";
			echo "</code>";
		}
		fclose($handler);
	}
	
	function print_html_helper(){
		require_once "helper/HtmlHelper.php";
		$hh = new HtmlHelper();
		echo $hh -> get_email_content(null);
	}
	
	function print_html_helper_as_html(){
		require_once "helper/HtmlHelper.php";
		$hh = new HtmlHelper();
		echo htmlspecialchars($hh -> get_email_content(null));
	}
	
	function print_server(){
		foreach($_SERVER as $key => $value){
			echo $key, ": ", $value;
			echo "<br>";
		}
	}
	
	function print_dir(){
		echo dirname(__FILE__);
	}
	
	function print_var(){
		$insert_settings = "INSERT INTO shop_settings "
			. "("
				. "name, "
				. "value"
			. ")"
			. "VALUES "
			. "('paypal_user', ''), "
			. "('paypal_pwd', ''), "
			. "('paypal_signature', ''), "
			. "('paypal_version', '122'), "
			. "('paypal_currency', 'EUR'), "
			. "('es_logoimg', ''), "
			. "('es_customer_email', 'customer@easyshop.com'), "
			. "('es_main_email', 'easyshop@easyshop.com'), "
			. "('es_name', 'Easy Shop');";
		echo $insert_settings;
	}
	
	function create_file(){
		$handle = fopen("config/app.php", "w+");
		$defines = "<?php\r\n"
			. "define('APP_PATH', '" . __DIR__ . "');\r\n"
		. "?>";
		fwrite($handle, $defines);
		fclose($handle);
	}
	
	print_var();
	//print_server();
	//print_dir();
	//print_html_helper();
	//print_html_helper_as_html()
	//phpinfo();
	
?>
