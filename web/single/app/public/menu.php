<?php

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$smarty->assign('current',isset($_SESSION['__'.SERVER_TYPE.'_CURRENT']) ? $_SESSION['__'.SERVER_TYPE.'_CURRENT'] : '');
$smarty->assign('role_menu', $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU']);
$smarty->display();
?>