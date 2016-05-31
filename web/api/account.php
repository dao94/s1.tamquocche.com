<?php
//根据角色名查询玩家账号
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __API__.'lib/Account.class.php';
include __FUNCTIONS__.'function.php';

$obj = Account::factory();
$obj->run();