<?php

namespace core\base;
use Exception;

class Error extends Exception{
	
	public function __construct( $message, $code = 0, Exception $previous = null ) {
        parent::__construct( $message, $code, $previous );
		
		$this->log($message);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
	
	/**
	 * Retrieves previous function from backtrace
	 * @return {String} function - function's name
	 */
	protected function caller(){
		$callers = debug_backtrace();
		$function = $callers[1]['function'];
		
		return $function;
	}
	
	/**
	 * Saves log message
	 * @param {String} message
	 * @param {String} debug - debug level
	 */
	protected function log( $message, $debug = "Error" ){
		$function = $this -> caller();
		file_put_contents(
			App::PHP_PATH . '/logs/console.log',
			date("Y-m-d H:i:s") . " | " . $debug . " in [" . __CLASS__ . " + " . $function . "]: " . $message . "\r\n",
			FILE_APPEND
		);
	}
	
}


?>