<?php

namespace core\Error;
use core\base\TError;

class Queryfail extends TError{
	
	public function __construct( $query, $code = 0, Exception $previous = null) {
        parent::__construct( "Query failed: " + $query, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
	
}

?>