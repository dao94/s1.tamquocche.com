<?php
//聊天信息推送接口
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __FUNCTIONS__.'function.php';
include __API__.'lib/Chat.class.php';

$obj= Chat::factory();
$obj->run();
?>