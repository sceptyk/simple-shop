<?php

namespace package\easyshop\manager;
use core\base\TManager;

use core\helper\Db;
use package\easyshop\manager\Order;
use package\easyshop\config\Values;

class Cart extends TManager{
	
	private $db;
	private $ho;
	private $hu;
	
	public function __construct( $db ){
		
		$this -> db = $this -> get_db();
		$this -> ho = new Order();
		$this -> mu = new User();
	}
	
	/**
	 * Sets up a new transaction hash
	 * @return {String} $transaction
	 */
	public function set_new_transaction(){
		
		return $this -> ho -> set_new_transaction();
	}
	
	/**
	 * Get current transaction from session
	 * @return ${String} $transaction
	 */
	public function get_transaction(){
		
		return $this -> ho -> get_transaction();
	}
	
	/**
	 * Get products to display
	 * @param {Array} filters
	 * @param {Boolean} (optional) admin - whether to gather non-active and out-of-store products
	 */
	public function get_products( $filters, $admin = false ){
		
		$site = $filters['site'];
		$site = intval($site);
		$site = $site < 1 ? 1 : $site;
	
		$limit_size = $filters['persite'];
		$limit_start = ($site - 1) * $limit_size;
		
		$data = array();
		$where = array();
		
		//filter by categories
		if(count($filters['categories']) > 0){
			
			$cats = $filters['categories'];
			$where[] = array(
				"OR",
				"p.category",
				"=",
				count($cats)
			);
		}
		
		//filter by tags
		if(count($filters['tags']) > 0){
			
			$join = array(
				array("INNER JOIN", "itemtag", "bit", "bit.item = p.id"),
				array("INNER JOIN", "tag", "tag", "bit.tag = tag.id")
			);
			
			$tags = $filters['tags'];
			$where[] = array(
				"OR",
				"tag.id",
				"=",
				count($tags)
			);
			$data = array_merge($data, $tags);
			
		}
		
		//filter by min price
		if($filters['minprice'] > 0){
			
			$min = $filters['minprice'];
			$where[] = array( null, "p.price", ">" );
			
			$data[] = $min;
			
		}
		
		//filter by max price
		if($filters['maxprice'] > 0){
			
			$max = $filters['maxprice'];
			$where[] = array( null, "p.price", "<" );
			
			$data[] = $max;
			
		}
		
		//if not admin panel, dont show non-active products and out-of-store ones
		if( !$admin ){
			
			$where[] = array( null, "p.amount", ">" );
			$data[] = 0;
			
			$where[] = array( null, "p.active", "=" );
			$data[] = 1;
				
		}
		
		//gather only products, not options
		$where[] = array( null, "p.type", "=" );
		$data[] = Values::get_item_type("product");
		
		//sort results
		if(isset($filters['sort'])){
			
			$sort = $filters['sort'];
			$order = "";
			
			if($sort == App::get_item_sort('NONE')){
				$order = "p.highlight DESC";
				
			}else if($sort == App::get_item_sort('PRICE_A')){
				$order = "p.price ASC";
				
			}else if($sort == App::get_item_sort('PRICE_D')){
				$order = "p.price DESC";
				
			}else if($sort == App::get_item_sort('NAME_A')){
				$order = "p.name ASC";
				
			}else if($sort == App::get_item_sort('NAME_D')){
				$order = "p.name DESC";
				
			}else if($sort == App::get_item_sort('DISCOUNT_A')){
				$order = "p.discount ASC";
				
			}else if($sort == App::get_item_sort('DISCOUNT_D')){
				$order = "p.discount DESC";
				
			}else if($sort == App::get_item_sort('DATE_A')){
				$order = "p.date ASC";
				
			}else if($sort == App::get_item_sort('DATE_D')){
				$order = "p.date DESC";
			}
		}
		
		$items = $this -> db -> select(
			array("*"),
			"product",
			"p",
			$data,
			$join,
			array("AND", $where),
			null,
			$order,
			array($limit_start, $limit_size)
		);
		
		foreach ($items as &$item) {
			$itemid = $item['id'];
			
			//IMAGES
			$item['images'] = $this -> db -> select(
				"url",
				"image", "",
				$itemid,
				null,
				"item"
			);
			
			//TAGS
			$item['tags'] = $this -> db -> select(
				"name ",
				"tag", "t",
				$itemid,
				array("INNER JOIN", "itemtag", "it", "t.id = it.tag"),
				"it.item"
			);
		}
		
		//Delete reference to last item
		unset($item);
		
		return $items;
		
	}
	
	/**
	 * Retrieves products for shop cart list
	 * @return {Array} 
	 */
	public function get_current_items( ){
				
		$transaction = $this -> get_transaction();
		
		$query = "SELECT "
			. "p.id, "
			. "p.name, "
			. "p.price, "
			. "i.url "
			. "FROM shop_products p "
			. "INNER JOIN shop_images i "
				. "ON i.item = p.id "
			. "INNER JOIN shop_bitemorder b "
				. "ON b.itemid = p.id "
			. "INNER JOIN shop_orders o "
				. "ON o.id = b.orderid "
			. "WHERE o.hash = ?";
			
		$result = $this -> fetch_assoc( $query, array($transaction) );	
		return $result;
	}
	
	/**
	 * Retrieve products for paypal checkout
	 * 
	 * @note Limit to 10 products - paypal ExpressCheckout restriction
	 * @param (optional) $transaction - transaction hash
	 * @return {Array}
	 */
	public function get_items_by_transaction( $transaction ){
		
		if(!$transaction)
			return null;
		
		return $this -> db -> select(
			array(
				"p.name",
				"p.price",
				"io.quantity",
				"p.digital"
			),
			"product", "p",
			$transaction,
			array(
				array("INNER JOIN", "itemorder", "io", "io.itemid = p.id"),
				array("INNER JOIN", "order", "o", "o.id =  io.orderid")
			),
			array("", "o.hash", "="),
			null,
			null,
			10 			
		);
	}
	
	/**
	 * Deletes product TODO FIXME TODO
	 * @param {Number} itemid
	 */
	public function delete_product( $itemid ){
			
			$query = "SELECT hash, file "
				. "FROM shop_products "
				. "WHERE id = ?";
			
			$result = $this -> db -> fetch_assoc( $query, array($itemid) );	
			$hash = $result['hash'];
			$ext = $result['file'];
			
			$query = "DELETE FROM shop_products "
				. "WHERE id = ?";
			
			$this -> db -> query( $query, array($itemid) );
			
			$query = "DELETE FROM shop_bitemorder "
				. "WHERE itemid = ?";
			
			$this -> db -> query( $query, array($itemid) );
			
			$filename = $hash . "." . $ext;
			$path = dirname(__FILE__). "../../download/";
			unlink($path . $filename);
			
			$query = "SELECT url "
				. "FROM shop_images "
				. "WHERE item = ?";
			$result = $this -> db -> fetch_assoc( $query, array($itemid) );
			$path = dirname(__FILE__). "../../data/";
			foreach($result as $img){
				$url = $img['url'];
				unlink($path . $url);
			}
			
			$query = "DELETE FROM shop_images "
				. "WHERE item = ?";
			
			$this -> db -> query( $query, array($itemid) );
			
			$query = "DELETE FROM shop_bitemtag "
				. "WHER item = ?";
			$this -> db -> query( $query, array($itemid) );	
	}
	
	/**
	 * Retrieves categories from db
	 * @return {Array} id, name
	 */
	public function get_categories(){
		
		return $this -> db -> select(
			array("id, name"),
			"category"
		);
	}
	
	/**
	 * Retrieves orders for admin panel review
	 * @important need to be authorized first
	 */
	public function get_orders(){
		
		$query = "SELECT "
						. "o.id, "
						. "c.name, "
						. "c.email, "
						. "o.state "
				. "FROM shop_orders o "
				. "INNER JOIN shop_bclientorder bco "
					. "ON bco.orderid = o.id "
				. "INNER JOIN shop_clients c "
					. "ON c.id = bco.clientid ";
					
			$orders = $this -> db -> fetch_assoc( $query );
			
			//get items for each order
			$query = "SELECT "
				. "p.name, "
				. "i.url "
				. "FROM shop_products p "
				. "INNER JOIN shop_bitemorder b "
					. "ON b.itemid = p.id "
				. "INNER JOIN shop_images i "
					. "ON i.item = p.id "
				. "WHERE b.orderid = ?";
			
			foreach ($orders as &$value) {
				
				$id = $value['id'];
				$result = $this -> db -> fetch_assoc( $query, array($id) );
				$value['items'] = $result;
			}
			unset($value);
			
			return $orders;
	}
	
	/**
	 * Retrieves info about current order for front page shop cart
	 */
	public function get_current_order(){
		
		$order = array();
		
		$order['items'] = $this -> get_current_items();
		$order['amount'] = $this -> get_order_amount();
		
		return $order;
	}
	
	/**
	 * Retrieves amount of order and details of client
	 * @return {Array} - associative array
	 */
	public function get_order_details(){
				
		$transaction = $this -> get_transaction();
		
		return $this -> db -> select(
			"o.amount, c.name, c.email, c.address, c.phone",
			"order", "o",
			array($transaction),
			array(
				array("INNER JOIN", "clientorder", "b", "b.orderid = o.id"),
				array("INNER JOIN", "clientorder", "c", "b.clientid = c.id"),	
			),
			"o.hash",
			null, null,
			1
		);
	}
	
	/**
	 * Retrieve amount price of an order
	 * @param (optional) $transaction
	 * @return {Number} amt - amount of order
	 */
	public function get_order_amount($transaction = null){
		
		return $this -> ho -> get_order_amount($transaction);
	}
	
	/**
	 * Sets new total amount of given order
	 * @param {Number} $amt - amount
	 * @param {String} $transaction
	 */
	public function set_order_amount($amt, $transaction){
			
		$this -> ho -> set_order_amount($amt, $transaction);	
	}
	
	/**
	 * Updates order state according to current state
	 * @param {Int} $state - current state of order
	 * @param {String(40)} $transaction - if null get from session
	 */
	public function update_order_state( $state, $transaction ){
		
		$this -> ho -> update_order_state($state, $transaction);
	}
	
	/**
	 * Update product values
	 * @param {Array} product
	 * @pattern array(
	 * 	"name",
	 * 	"price",
	 * 	"description",
	 * 	"highlight",
	 * 	"id")
	 */
	public function update_product($product){
			
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
	 * @link [core\app\Application]
	 * @return {Number}
	 */
	public function get_order_type(){
		
		return $this -> ho -> get_order_type();
	}
	
	/**
	 * Retrieves order state
	 * @link details in [core\app\Application]
	 * @return {Number}
	 */
	public function get_order_state(){
		
		return $this -> ho -> get_order_state();
	}
	
	/**
	 * Disable order until it is confirmed
	 */
	public function disable_order(){
		
		$this -> ho -> disable_order();
	}
	
	/**
	 * Adds a product to an order
	 * @param $id - product's id
	 * @return $amount - order amount
	 */
	public function add_to_order($id, $transaction = null){
			
		if( !isset($transaction))
			$transaction = $this -> get_transaction();
		
		$orderid = $this -> db -> select(
			"id", "order", "", $transaction, null, "hash", null, null, 1
		);
		
		$this -> db -> insert(
			array("itemid", "orderid"),
			"itemorder",
			array($id, $orderid)
		);
		
		return $this -> db -> select(
			array("*"), "product", "",
			array($id), null, "id"
		);
	}
	
	/**
	 * Adds list of items to order
	 */
	public function add_list_to_order(array $list){
		
		$transaction = $this -> get_transaction();
		$amount = 0;
		foreach($list as &$item){
			
			//grab trustful information from db
			$item = $this -> add_to_order($item['id'], $transaction);
			$amount += $item['price'];
		}
		unset($item);
		
		$this -> db -> update(
			array("state", "amount"),
			"order",
			array(App::get_order_state("adding"), $amount, $transaction),
			"hash"
		);		
	}
	
	/**
	 * Delets product from order
	 * @param {String} itmeid - product id 
	 * @return {Number|null} amount - new amount of order or null
	 */
	public function delete_from_order($itemid){
		
		$transaction = $this -> get_transaction();
		
		if($this -> get_order_state($transaction) < App::get_order_state('paypal')){
			
			$query = "DELETE FROM shop_bitemorder "
				. "WHERE itemid = ? "
				. "AND "
				. "orderid = (SELECT id FROM shop_orders WHERE hash = ?) "
				. "LIMIT 1";
			
			$this -> db -> query( $query, array($itemid, $transaction) );
			
			$query = "SELECT COUNT(*) AS items "
				. "FROM shop_bitemorder b "
				. "INNER JOIN shop_orders o "
					. "ON b.orderid = o.id "
				. "WHERE o.hash = ?";
				
			$result = $this -> db -> fetch_assoc( $query, array($transaction) );
			$items = $result[0]['items'];
			
			if($items > 0){
			
				$query = "SELECT "
					. "(SELECT amount FROM shop_orders WHERE hash = ?) AS amount, "
					. "(SELECT price FROM shop_products WHERE id = ?) AS price";
				
				$result = $this -> fetch_assoc( $query, array($transaction, $itemid) );
				$amount = $result[0]['amount'];
				$price = $result[0]['price'];
				
				$amount = $amount - $price;
				if($amount < 0) $amount = 0;
				
			}else if($items == 0){
					
				$amount = 0;
			}else{
				return null;
			}
			
			$query = "UPDATE shop_orders "
					. "SET amount = ? "
					. "WHERE hash = ?";
				
			$this -> query( $query, array($amount, $transaction) );
				
			return $amount;
		}
		
		return null;
	}

	/**
	 * Retrieve product data to send digital product via email
	 * @param {String} $token - paypal transaction token
	 * @return {Array}
	 */
	public function get_items_to_email( $token ){
		
		$response = array(
			"email" => "",
			"files" => ""
		);
		
		$query = "SELECT "
				. "p.name, "
				. "CONCAT("
					. "p.hash, "
					. "'.', "
					. "p.file"
				. ") AS file "
			. "FROM shop_products p"
			. "INNER JOIN shop_bitemorder bio "
				. "ON bio.itemid = p.id "
			. "INNER JOIN shop_orders o "
				. "ON o.id = bio.orderid "
			. "WHERE "
				. "o.paypal = ? "
				. "AND "
				. "p.digital = 1";
				
		$response['files'] = $this -> fetch_assoc( $query, array($token) );
		$response['email'] = $this -> mu -> get_user_email();
		
		
		return $response;
	}
	
	/**
	 * Adds new product
	 * @param {Array} product - associative array of product values
	 * @pattern
	 * array(
	 * 	{String} type, //"product", "option"
	 * 	{String} title,
	 * 	{Number} price,
	 * 	{String} description,
	 * 	{Number} digital, // (0,1),
	 * 	{Number} parent, //if type = "option" then required
	 * 	{Base64} product,
	 * 	{Array(Base64)} imgs,
	 * 	{Array(String)} tags
	 * );
	 */
	public function add_product($product){
		
		//TODO type: product, option
		//TODO clone product
		//TODO amount of product
		//TODO disable product
		
		$type = $product['type']; //TODO check if type correct else set to default
		
		$title = $product['title'];
		$price = $product['price'];
		$description = $product['description'];
		$category = $product['category'];
		$digital = $product['digital'];
		$product = $product['product'];
		
		$imgs = $product['imgs'];
		$tags = $product['tags'];
		
		//date in milliseconds
		$date = floor(microtime(true) * 1000);
		$hash = uniqid();
		$uniqid = $hash; //<--
		
		//check if category exists
		$categoryid = $this -> db -> select(
			"id",
			"category", null,
			$category, null,
			"name", null, null,
			1
		);
		$result = $this -> db -> fetch_assoc("SELECT id FROM shop_categories WHERE name = ?", array($category));
		if (!isset($categoryid)) {
			//if not exist create a new one
			$this -> db -> insert(
				"name",
				"category",
				$category
			);
			$categoryid = $this -> db -> last_id();
		}
		
		//save product
		$type = "";
		if($digital == 1){
			
			list($type, $data) = explode(';', $product);
			list(, $data) = explode(',', $data);
			list(,$type) = explode(':', $type);
			
			$ext = App::getBase64Type($type); //TODO move to helper
			$path = App::get_app_path() . "/download/";
			$filename = $hash . "." . $ext;
			file_put_contents($path.$filename, $data);
		}
		
		$this -> db -> insert(
			"type, name, description, price, category, digital, file, hash, date",
			"item",
			array(Values::get_product_type($type), $title, $description, $price, $categoryid, $digital, $ext, $hash, $date)
		);
		
		$id = $this -> db -> last_id();
		
		if($type == "option"){
			
			$this -> db -> insert(
				"parent, child",
				"itemoption",
				array($product['parent'], $id)
			);
			
		}
		
		foreach($imgs as $img) {
			
			//handle base_64 image
			list($type, $data) = explode(';', $img);
			list(, $data) = explode(',', $data);
			list(, $ext) = explode('/', $type);
			$data = base64_decode($data);
			$filename = uniqid(true) . "." . $ext;
			$url = $uniqid;
			$path = App::get_app_path() . "/data/";
			if (!file_exists($path . $url))
				mkdir($path . $url, 0755, true);
			$url .= "/" . $filename;
			$path .= $url;
			file_put_contents($path, $data);
			
			$this -> db -> insert(
				"url, item",
				"image",
				array($url, $id)
			);
		}
		
		foreach($tags as $tag){
			
			$tagid = $this -> db -> select(
				"id",
				"tag", "",
				$tag, null,
				"name", null, null,
				1
			);
			
			if ( !isset( $tag ) ) {
				$this -> db -> insert( "name", "tag", $tag );
				$tagid = $this -> db -> last_id();
			}
			
			$this -> db -> insert(
				"item, tag",
				"itemtag",
				array($id, $tagid)
			);
		}
	}

}
