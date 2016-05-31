<?php
//充值订单查询
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __FUNCTIONS__.'function.php';
include __API__.'lib/Order.class.php';

$obj = Order::factory();
$obj->run();
?>