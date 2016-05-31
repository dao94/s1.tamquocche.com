<?php

//登陆验证码获取
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
require __FUNCTIONS__ . 'function.php';
session_start();
$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789~!@#$%^&*()_+-={}[]';
$_SESSION['verify'] = substr(str_shuffle($chars), 0, 4);
ajax_return('ok', '', $_SESSION['verify']);
?>
