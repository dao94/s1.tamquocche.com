<?php

//策划数据
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$data=array();
if(isset($_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['cehua']['childs'])){
	$logs=$_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['cehua']['childs'];
	foreach ($logs as $key=>$item){
		$data['../cehua/'.$key]=$item['title'];
	}
}
$smarty->assign('data',$data);
$smarty->display();
