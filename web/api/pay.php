<?php
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
include __CONFIG__.'key_config.php';
include __FUNCTIONS__.'function.php';
include __API__.'lib/Pay.class.php';

write_log(http_build_query($_REQUEST), 'pay_request_'.date('Ym'));

$payObj = Pay::factory();
$payObj->run();
?>