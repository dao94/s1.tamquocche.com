<?php
//禁言、封号、踢人下线
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __FUNCTIONS__.'function.php';
include __API__.'lib/Forbid.class.php';

$obj= Forbid::factory();
$obj->run();
?>