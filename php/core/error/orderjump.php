<?php

namespace core\Error;
use core\base\TError;

class OrderJump extends TError{
	
	public function __construct($code = 0, Exception $previous = null) {
        parent::__construct( "Try of skipping order steps", $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
	
}

?>