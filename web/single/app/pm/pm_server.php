<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
//Link
$url_f = __CONFIG__ . 'url_config.php';
file_exists($url_f) && include $url_f;
!isset($url_config) && $url_config = array();
$smarty->assign('url_config', $url_config);
//ip配置
$ip_f = __CONFIG__ . 'ip_config.php';
file_exists($ip_f) && include $ip_f;
!isset($ips) && $ips = array();
$smarty->assign('ips', $ips);
$smarty->display();
?>