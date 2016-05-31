<?php

//提示信息页面
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$jumpUrl = isset($_GET['jumpUrl']) ? urldecode($_GET['jumpUrl']) : '';
$message = isset($_GET['message']) ? urldecode($_GET['message']) : '';

$smarty->assign('message', $message);
$smarty->assign('jumpUrl', $jumpUrl);
$smarty->display();
?>