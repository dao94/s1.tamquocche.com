<?php
//等级信息推送接口
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __FUNCTIONS__.'function.php';
include __API__.'lib/Push.class.php';

$obj= Push::factory();
$obj->run();
?>