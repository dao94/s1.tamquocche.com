<?php
//统计数据，角色创建数，登陆人数，最高在线
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Stat.class.php';
include __FUNCTIONS__.'function.php';

$obj = Stat::factory();
$obj->run();