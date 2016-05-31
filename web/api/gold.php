<?php
//元宝库存量
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Gold.class.php';
include __FUNCTIONS__.'function.php';

$obj = Gold::factory();
$obj->run();
?>