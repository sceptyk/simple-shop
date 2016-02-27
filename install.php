<?php
require_once "php/core/app/Application.php";

use core\app\Application as App;
use core\helper\Db;

//fetch db settings first TODO
if(isset($_POST['config_data'])){
	
	$dbhost = $_POST['host'];
	$dbuser = $_POST['user'];
	$dbpass = $_POST['pass'];
	$dbname = $_POST['name'];
	
	//DEFINED values
	$handle = fopen("php/core/config/constant.php", "w+");
	$defines = "<?php\r\n"
		. "define('APP_PATH', '" . __DIR__ . "');\r\n"
		. "define('PHP_PATH', APP_PATH . '/php');\r\n"
		. "define('APP_PREFIX', 'shop_');\r\n"//TODO check if available
		. 'define("APP_RECEIVER", "package\easyshop\receiver\Post");\r\n'
		. "define('DB_HOST', '$dbhost');\r\n"
		. "define('DB_USER', '$dbuser');\r\n"//TODO ask for values
		. "define('DB_PASSWORD', '$dbpass');\r\n"
		. "define('DB_NAME', '$dbname')\r\n"
		. "global \$include_library; \$include_library = array();\r\n"
	. "?>";
	fwrite($handle, $defines);
	fclose($handle);
	


//create tables if needed
		
		$connection = new Db();
		
		$table_products = "CREATE TABLE IF NOT EXISTS shop_products "
			. "("
				. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
				. "type INT(1) NOT NULL DEFAULT '0', "
				. "name VARCHAR(50), "
				. "description TEXT, "
				. "price REAL, "
				. "tax REAL NOT NULL DEFAULT '0', "
				. "discount REAL NOT NULL DEFAULT '0', "
				. "amount INT(6), "
				. "highlight INT(1) NOT NULL DEFAULT '0', "
				. "category INT(12), "
				. "digital INT(1) NOT NULL, "
				. "file CHAR(4), "
				. "hash CHAR(13), "
				. "active INT(1) NOT NULL DEFAULT '1', "
				. "date BIGINT(14)"
			. ");";
		
		$table_bproducts = "CREATE TABLE IF NOT EXISTS shop_bind_products "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "parentid INT(12), "
			. "childid INT(12)"
			. ");";
			
		$table_categories  = "CREATE TABLE IF NOT EXISTS shop_categories "
			. "("
				. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
				. "name VARCHAR(30), "
				. "category INT(12)"
			. ");";
			
		$table_images = "CREATE TABLE IF NOT EXISTS shop_images "
			. "("
				. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
				. "url VARCHAR(100), "
				. "item INT"
			. ");";
			
		$table_orders = "CREATE TABLE IF NOT EXISTS shop_orders "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "hash CHAR(40) NOT NULL, "
			. "state INT(3) NOT NULL DEFAULT '0', "
			. "amount INT(5), "
			. "paypal CHAR(20), "
			. "date INT(6)"
			. ");";
			
		$table_bitemorder = "CREATE TABLE IF NOT EXISTS shop_bind_product_order "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "itemid INT(12), "
			. "quantity INT(9) NOT NULL DEFAULT '1', "
			. "orderid INT(12)"
			. ");";
			
		$table_purchases = "CREATE TABLE IF NOT EXISTS shop_purchases "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "orderid INT(12), "
			. "paypalid VARCHAR(17), "
			. "date DATETIME"
			. ");";
			
		$table_tags = "CREATE TABLE IF NOT EXISTS shop_tags "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "name VARCHAR(20)"
			. ");";
		
		$table_bitemtag = "CREATE TABLE IF NOT EXISTS shop_bind_product_tag "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "item INT(12), "
			. "tag INT(12)"
			. ");";
		
		$table_settings = "CREATE TABLE IF NOT EXISTS shop_settings "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "name VARCHAR(100), "
			. "value TEXT, "
			. "level INT(1) NOT NULL DEFAULT '" . App::get_user_level('admin') . "'"
			. ");";
		
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
		
		$table_clients = "CREATE TABLE IF NOT EXISTS shop_clients "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "name VARCHAR(20), "
			. "email VARCHAR(50), "
			. "address VARCHAR(100), "
			. "phone VARCHAR(20), "
			. "userid CHAR(30) NOT NULL DEFAULT '0'"
			. ");";
			
		$table_bclientorder = "CREATE TABLE IF NOT EXISTS shop_bclientorder "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "clientid INT(12), "
			. "orderid INT(12)"
			. ")";
			
		$table_users = "CREATE TABLE IF NOT EXISTS shop_users "
			. "("
			. "id INT(12) UNSIGNED AUTO_INCREMENT PRIMARY KEY, "
			. "uniqueid CHAR(23), "
			. "email VARCHAR(50), "
			. "name VARCHAR(20), "
			. "salt CHAR(30), "
			. "password CHAR(128), "
			. "loghash CHAR(64), "
			. "level INT(1) NOT NULL"
			. ");";
		
		$user = array(
			"email" => "your@email.com",
			"name" => "admin",
			"password" => "admin",
			"level" => App::get_user_level('SUPERADMIN')
		);
		$connection -> register_user($user);
		
		//echo $table_bitemtag."\r\n";
		//echo $table_categories."\r\n";
		//echo $table_images."\r\n";
		//echo $table_orders."\r\n";
		//echo $table_products."\r\n";
		//echo $table_purchases."\r\n";
		//echo $table_settings."\r\n";
		//echo $table_tags."\r\n";
		//echo $table_users."\r\n";
		
		///*
		$r = $connection->query($table_categories);
		if(!$r	) print "Error creating TABLE_CATEGORIES ".$r->error."\r\n";
		///*
		$table_images = $connection->query($table_images);
		if(!$table_images) echo "Error creating TABLE_IMAGES"."\r\n";
		
		$table_orders = $connection->query($table_orders);
		if(!$table_orders) echo "Error creating TABLE_ORDERS"."\r\n";
		
		$table_bitemorder = $connection->query($table_bitemorder);
		if(!$table_bitemorder) echo "Error creating TABLE_BINDITEMORDER"."\r\n";
		
		$table_products = $connection->query($table_products);
		if(!$table_products) echo "Error creating TABLE_PRODUCTS"."\r\n";
		
		$table_bproducts = $connection->query($table_bproducts);
		if(!$table_bproducts) echo "Error creating TABLE_BIND_PRODUCTS"."\r\n";
		
		$table_purchases = $connection->query($table_purchases);
		if(!$table_purchases) echo "Error creating TABLE_PURCHASES"."\r\n";
		
		$table_tags = $connection->query($table_tags);
		if(!$table_purchases) echo "Error creating TABLE_TAGS"."\r\n";
		
		$table_bitemtag = $connection->query($table_bitemtag);
		if(!$table_purchases) echo "Error creating TABLE_BIND_PRODUCT_TAG"."\r\n";
		
		$table_clients = $connection->query($table_clients);
		if(!$table_clients) echo "Error creating TABLE_CLIENTS"."\r\n";
		
		$table_bclientorder = $connection->query($table_bclientorder);
		if(!$table_bclientorder) echo "Error creating TABLE_BIND_CLIENT_ORDER"."\r\n";
		
		$table_users = $connection->query($table_users);
		if(!$table_users) echo "Error creating TABLE_USERS"."\r\n";
		
		$table_settings = $connection->query($table_settings);
		if(!$table_settings) echo "Error creating TABLE_SETTINGS"."\r\n";
		
		$insert_settings = $connection->query($row_settings);
		
		if(!function_exists("curl_version")) echo "Error: cURL is not enabled on server"."\r\n";
		//*/
		
		
		
		//settings are editable via administrator panel
		
		echo "<br>";
		
		echo "NOW GO TO /admin AND CHANGE SETTINGS ACCORDING TO YOUR DETAILS";
		echo "<br>";
		echo "LOGIN: admin, PASSWORD: admin";
		echo "<br>";
		echo "DELETE THIS FILE (install.php) IF NO ERROR ELSEWISE WAIT FOR A MIRACLE";
		echo "<br>";
		echo "CONTACT: filip@nexbox.eu";
		
		}else{
	
?>
	<h1>Database settings</h1>
	<form action="" method="post">
		<label for="host">Host address: </label>
		<input type="text" name="host" />
		<label for="user">User name: </label>
		<input type="text" name="user" />
		<label for="pass">Password: </label>
		<input type="password" name="pass" />
		<label for="name">Database name: </label>
		<input type="text" name="name" />
		
		<input type="hidden" name="config_data" value="true" />
		
		<input type="submit" value="Submit" />
	</form>

<?php	} ?>
