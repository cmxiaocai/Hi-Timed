<?php

ini_set('date.timezone','Asia/Shanghai');
define('APPLICATION_PATH', dirname(dirname(__FILE__)) );
include APPLICATION_PATH.'/bootstrap.php';

$uri   = isset($_GET['r']) ? $_GET['r'] : 'task/list';
$Route = new SimpleRoute();
$Route->mapped($uri);
$Route->callAction();

?>