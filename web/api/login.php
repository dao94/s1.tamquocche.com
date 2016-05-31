<?php
/*
 * 登录接口
 * 如果为后台登陆已创建账号的话，可以直接生成session，并且直接跳转游戏页面
 */
header('P3P: CP=CAO PSA OUR');
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
//echo __ROOT__.'/config/config.php';die;
include __ROOT__.'/config/config.php';
include __API__.'lib/Login.class.php';
include __FUNCTIONS__.'function.php';;
ogin = Login::factory();
$login->run();
?>
