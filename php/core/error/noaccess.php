<?php

namespace core\error;
use core\base\TError;

class NoAccess extends TError{
	
	 public function __construct( $code = 0, Exception $previous = null) {
        parent::__construct("Not authorized user access", $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
	
	
}
