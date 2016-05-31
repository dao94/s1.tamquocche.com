<?php
//单服每天创建的角色列表
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Create.class.php';
include __FUNCTIONS__.'function.php';

$obj = Create::factory();
$obj->run();
?>