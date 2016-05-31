<?php
//排行榜
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Top.class.php';
include __AUTH__.'lang.php';

$obj = Top::factory();
$obj->run();
?>