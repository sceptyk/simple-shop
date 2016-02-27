<?php

namespace core\Error;
use core\base\TError;

class Payfail extends TError{
	
	public function __construct( $error, $code = 0, Exception $previous = null) {
        parent::__construct( "Pay request faild: " + $error, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
	
}

?>