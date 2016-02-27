<?php
namespace package\easyshop\manager;
use core\base\TManager;

use core\helper\Curl;
use core\helper\Settings;

use package\easyshop\manager\User;
use package\easyshop\manager\Order;

class Paypal extends TManager{

	private $hc;
	private $hs;
	private $ho;
	private $hm;
	private $db;
	private $user;
	
	private $pay_service = 'https://api-3t.sandbox.paypal.com/nvp'; //FIXME
	private $authorize_url = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token="; //FIXME
	
	private $settings = array();
	private $payee = array();
	
	public function __construct() {

		$this -> hs = new Settings();
		$this -> mu = new User();
		$this -> hc = new Curl($this -> pay_service);
		$this -> ho = new Order();
		$this -> hm = new Mailer();
		$this -> db = $this -> get_db();
		
		$this -> settings = $this -> ms -> get_shop_paypal_settings();
		
		$this -> payee = array(
			"USER" => urlencode($this -> settings['user']),
			"PWD" => urlencode($this -> settings['pwd']),
			"SIGNATURE" => urlencode($this -> settings['signature'])
		);
	}

	public function __destruct() {

	}
	
	public function request_checkout(){
		
		$this -> mc -> update_order_state( App::get_order_state('CHECKOUT') );
		$email = $this -> mu -> get_user_email();
		
		$transaction = $this -> ho -> get_transaction();
		
		$return_url = App::get_app_path();
		
		$info = array(
			"METHOD" => "SetExpressCheckout",
			"RETURNURL" => $return_url . "?paypal=$transaction",
			"CANCELURL" => $return_url . "?error=102", //$this -> settings['cancelurl']; FIXME
			"VERSION" => $this -> settings['version'],
			"BRANDNAME" => $this -> settings['name'],
			//"LOGOIMG" => $this -> settings['logoimg'], //FIXME logo image 190x60
			"EMAIL" => $email,
			"LANDINGPAGE" => "Billing",
			"PAYMENTREQUEST_0_CURRENCYCODE" => $this -> settings['currency'],
			"PAYMENTREQUEST_0_PAYMENTACTION" => "Sale"
		);
		
		$items = $this -> process_items();
		
		if(!isset($items)) return $return_url . "?error=101";
		
		$result = $this -> ch -> send_post(array_merge($this -> payee, $info, $items), true);
		$token = $result['TOKEN'];
		
		$this -> ho -> save_paypal_token($token, $transaction);
		
		return $this -> authorize_url . $token;
		
	}
	
	private function request_details(){
		$info = array(
			"METHOD" => "GetExpressCheckoutDetails",
			"VERSION" => $this -> settings['version'],
			"TOKEN" => $token
		);
		
		$result = $this -> ch -> send_post(array_merge($this -> payee, $info, $items), true);

		return $result;
	}
	
	public function request_payment($paypal){
		$transaction = $paypal['transaction'];
		$token = $paypal['token'];
		$payerid = $paypal['payerid'];
		
		$response = array(
			"success" => false,
			"request" => array()
		);
		
		if( $this -> ho -> get_order_state() == App::get_order_state("paypal"))
		if(	$this -> check_transaction($transaction, $token) ){
			
			if(!isset($payerid)){
				$result = $this -> send_details_request($token);
				
				if( $this -> is_request_success($result) ){
					$payerid = $result['PAYERID'];
				}
			}
			
			$amt = $this -> db -> get_order_amount($transaction);
				
			$info = array(
				"METHOD" => "DoExpressCheckoutPayment",
				"VERSION" => $this -> settings['version'],
				"TOKEN" => $token,
				"PAYERID" => $payerid,
				"PAYMENTREQUEST_0_PAYMENTACTION" => "Sale",
				"PAYMENTREQUEST_0_AMT" => $amt,
				"PAYMENTREQUEST_0_CURRENCYCODE" => $this -> settings['currency']
			);
			
			$result = $this -> ch -> send_post(array_merge($this -> payee, $info), true);
			
			if( $this -> is_request_success($result) ){
					
				$paypalid = $detail['PAYMENTINFO_0_TRANSACTIONID'];
				$response['request'] = $this -> successful_transaction($token, $paypalid);
					
				$response['success'] = true;
			}
			
		}
		
		return $response;
	}

	/**
	 * Sum up the cart content
	 * @param {String(40)} transaction - id of transaction
	 * @return {Object} associative array of param pairs
	 */
	private function process_items() {

		//TODO check for user discount code
		$row = $this -> mc -> get_items_by_transaction();
		
		$items = array();
		$item_count = 0; //Paypal accepts up to 10 items
		$amt = 0;
		
		while ($item_count < count($row)) {
			$item = $row[$item_count];
			$n = strval($item_count);
			
			$items["L_PAYMENTREQUEST_0_ITEMCATEGORY" . $n] = ($item['digital'] == 1 ? "Digital" : "Physical");
			$items["L_PAYMENTREQUEST_0_NAME" . $n] = $item['name'];
			$items["L_PAYMNETREQUEST_0_QTY" . $n] = intval($item['quantity']);
			$items["L_PAYMENTREQUEST_0_AMT" . $n] = doubleval($item['price']);
			$items["L_PAYMENTREQUEST_0_TAXAMT" . $n] = doubleval($item['tax']);
			
			$amt += $item['quantity'] * $item['price'];
			$item_count++;
		}
		
		//check sum
		$saved_amt = $this -> db -> get_order_amount($transaction);
		$saved_amt = doubleval($saved_amt);
		
		if($saved_amt != $amt) return null;
		
		$items['PAYMENTREQUEST_0_AMT'] = $amt;
		$items['PAYMENTREQUEST_0_ITEMAMT'] = $amt;
		
		//$this -> db -> save_amount($transaction, $amt);
		
		return $items;
	}	

	public function is_request_success( $result ){
		if( $result['ACK'] == "Success" ){
			return true;
		}else{
			throw Error\Payfail($result['error']);
			return false;
		}
	}
	
	private function check_paypal_token($token){
		
		$transaction = $this -> ho -> get_transaction();
		
		$query = "SELECT paypal "
			. "FROM shop_orders "
			. "WHERE hash = ?";
		
		$rows = $this -> db -> fetch_assoc( $query, array($transaction) );
		if(!$rows) return false;
		$saved_token = $rows[0]['paypal'];
			
		if($saved_token != $token) return false;
		
		return true;
	}
	
	/**
	 * On successfully ended paypal transaction
	 * @param $token
	 * @param $paypalid
	 * @return {Boolean} true if ask user for registration
	 */
	public function successful_transaction($token, $paypalid) {
		
		$this -> db -> save_paypal_purchase($token, $paypalid);
		
		$result = $this -> db -> get_items_to_email($token);
		
		$shop_name = $this -> hs -> get_setting("es_name");
		
		$entries = array(
			"email" => $result['email'],
			"title" => $shop_name,
			"?headercolor?" => "#16233b",
			"?logocolor?" => "#16233b",
			"?title?" => "",
			"?subtitle?" => "You have just bought a new theme.",
			"?text?" => "Yes, that's all. Fast and easy access is our priority. Now you can enjoy your purchased item which you can find attached to this message as an attachement. Our service though is not finished and we encourage you to visit our forum to get additional tips and support. Enjoy!",
			"?note?" => "In case of any problem, please contact as via 'Report problem' link at the bottom.",
			"?website?" => "studiocrimes.com"
		); 
		
		$this -> hm -> send_file( $result['files'], $entries );
			
		//ask for registration
		if( !$this -> db -> is_client_known() ){
			return true;
		}
		
		return false;
	}

	public function cancelled_transaction() {

	}
	
	/**
	 * Checks if all needed data is set, if not returns missing fields
	 * @return {Array|null}
	 */
	public function check_client_data(){
		
		$transaction = $this -> db -> get_transaction();
		$order_type = $this -> db -> get_order_type($transaction);
		
		$user_data = $this -> custom -> get_user_data($order_type);
		
		if($user_data == null){
				
			$user_data = $this -> db -> get_user_data($transaction);	
			
		}else{
			
			$this -> db -> set_user_data($user_data, $transaction);
		}
		
		$missing = array(
			"name" => true,
			"email" => true,
			"address" => true,
			"phone" => true
		);
		
		//not needed fields
		if($order_type == $SHOP_ORDER_TYPE['DIGITAL']){
			$missing['address'] = false;
			$missing['phone'] = false;
		}
		
		if(isset($user_data['name']))
			$missing['name'] = false;
		
		if(isset($user_data['email']))
			$missing['email'] = false;
		
		if(isset($user_data['address']))
			$missing['address'] = false;
			
		if(isset($user_data['phone']))
			$missing['phone'] = false;
		
		//TODO rebind user with new transaction id after transaction end
		
		$GOT_DATA = true;
		foreach($missing as $miss) if($miss) $GOT_DATA = false;
		
		if($GOT_DATA) return null;
		
		return $missing;
	}
	
	/**
	 * Save paypal token to current transaction. needed for latter authorization.
	 * @param {String(20)} $token - paypal token
	 * @param {String(40)} $transaction - transaction hash
	 */
	public function save_paypal_token($token, $transaction){
			
		$token = urldecode($token);
		
		$query = "UPDATE shop_orders "
			. "SET paypal = ? "
			. "WHERE hash = ?";
		
		$result = $this -> db -> query( $query, array($token, $transaction) );
		
		if(isset($result)){
				
			$this -> mc -> update_order_state( App::get_order_state("paypal"), $transaction );	
			return true;
		}
		
		return false;
	}
	
	/**
	 * Retrieves paypal token of trnasaction
	 * @return {String}
	 */
	public function get_paypal_token($transaction = null){
		
		if($transaction == null)
			$transaction = $this -> ho -> get_transaction();
		
		$query = "SELECT paypal "
			. "FROM shop_orders "
			. "WHERE hash = ?";
			
		$result = $this -> db -> fetch_assoc( $query, array($transaction) );
		
		return $result[0]['paypal'];
	}
	
	/**
	 * Updates an order adding paypal transaction identification hash
	 */
	public function save_paypal_purchase( $token, $paypalid ){
			
		//TODO save client;
		$this -> ho -> save_purchase_id( $token, $paypalid );	
	}

}
?>