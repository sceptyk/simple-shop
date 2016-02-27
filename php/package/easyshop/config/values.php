<?php

namespace package\easyshop\config;

class Values{
	
	public function __construct(){
		
	}
	
	public function __destruct(){
		
	}
	
	public static $DB_TABLE_NAMES = array(
		"category" => "categories",
		"client" => "clients",
		"image" => "images",
		"order" => "orders",
		"item" => "products",
		"product" => "products",
		"purchase" => "purchases",
		"setting" => "settings",
		"tag" => "tags",
		"user" => "users",
		
		"clientorder" => "bind_client_order",
		"itemorder" => "bind_product_order",
		"itemtag" => "bind_product_tag",
		"itemoption" => "bind_products"
	);
	
	public static $DB_TABLE_USER_LEVELS = array(
		"USER" => 0,
		"CLIENT" => 1,
		"MODERATOR" => 2,
		"ADMIN" => 3,
		"SUPERADMIN" => 4
	);
	
	public static $DB_ORDER_STATE = array(
		"CREATE" => 0,
		"ADDING" => 1,
		"CHECKOUT" => 2,
		"PAYPAL" => 3,
		//warning
		"PAUSED" => 100,
		"ABORTED" => 101,
		//success
		"PAID" => 200,
		"SENT" => 201,
		"DELIVERED" => 202
	);
	
	public static $DB_ITEM_TYPE = array(
		"PRODUCT" => 0,
		"OPTION" => 1
	);
	
	public static $SHOP_ITEM_SORT = array(
		"NONE" => 0,
		"PRICE_A" => 1,
		"PRICE_D" => 2,
		"NAME_A" => 3,
		"NAME_D" => 4,
		"DISCOUNT_A" => 5,
		"DISCOUNT_D" => 6,
		"DATE_A" => 7,
		"DATE_D" => 8
	);
	
	public static $SHOP_ORDER_TYPE = array(
		"NONDIGITAL" => 0,
		"DIGITAL" => 1,
		"MIXED" => 2
	);
	
	public static $DEBUG_LEVEL = array(
		"MESSAGE" => 0,
		"NOTE" => 1,
		"DEBUG" => 2,
		"WARNING" => 3,
		"ERROR" => 4
	);
	
	/**
	 * Retrieves table name
	 * @param {String} name
	 */
	public static function get_table($name){
		return APP_PREFIX . self::$DB_TABLE_NAMES[$name];
	}
	
	/**
	 * Retrieves user level
	 * @param {String} name
	 */
	public static function get_user_level($name){
		return self::$DB_TABLE_USER_LEVELS[ strtoupper($name) ];
	}
	
	/**
	 * Retrieves order state
	 * @param {String} name
	 */
	public static function get_order_state($name){
		return self::$DB_ORDER_STATE[ strtoupper($name) ];
	}
	
	/**
	 * Retrieves item sort type
	 * @param {String} name
	 */
	public static function get_item_sort($name){
		return self::$SHOP_ITEM_SORT[ strtoupper($name) ];
	}
	
	/**
	 * Returns item type code
	 */
	public static function get_item_type($type){
		return self::$DB_ITEM_TYPE[ strtoupper($type) ];
	}
}

?>