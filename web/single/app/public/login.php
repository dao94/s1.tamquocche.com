<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'lang.php';

$smarty->assign('account', isset($_COOKIE['__USER_ACCOUNT']) ? $_COOKIE['__USER_ACCOUNT'] : '');
$smarty->assign('lang', $_SESSION['__' . SERVER_TYPE . '_LANG']);
$smarty->display();
?>