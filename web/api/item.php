<?php
//道具列表接口
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Item.class.php';
include __FUNCTIONS__.'function.php';

$obj = Item::factory();
$obj->run();
?>