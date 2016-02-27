<?php

require_once __DIR__ . "/../core/app/Application.php";
use core\App\Application as App;

$receiver = App::get_receiver();
$receiver -> execute();

?>
