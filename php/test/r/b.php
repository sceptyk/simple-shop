<?php

namespace r;

set_include_path(realpath("../"));
spl_autoload_extensions(".php");
spl_autoload_register();


class B extends s\C{
	
	public function __construct(){
		parent::__construct();
		
		echo "b";
	}
	
	public function __destruct(){
		
	}
	
}

?>