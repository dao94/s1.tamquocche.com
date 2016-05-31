<?php
//角色验证
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Check.class.php';
include __FUNCTIONS__.'function.php';

$checkObj = Check::factory();
$checkObj->run();
?>