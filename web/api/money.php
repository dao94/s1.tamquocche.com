<?php
//单服每天创建的角色列表
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Money.class.php';
include __AUTH__.'lang.php';

$obj = Money::factory();
$obj->run();
?>