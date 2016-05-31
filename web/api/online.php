<?php
//在线人数接口
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __FUNCTIONS__.'function.php';
include __API__.'lib/Online.class.php';

$onlineObj = Online::factory();
$onlineObj->run();
?>