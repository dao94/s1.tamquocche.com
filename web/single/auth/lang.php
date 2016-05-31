<?php

include_once __FUNCTIONS__ . 'function.php';
include __CLASSES__ . 'Rbac.class.php';

$loginPage = 'login.php';
$tipsPage = '../public/tips.php';
//获取文件在root下的路径
$path = ltrim(str_replace(__ROOT__, '', $_SERVER['SCRIPT_FILENAME']), '/');
$path = explode('/', $path);
//获取当前的应用名：$app 模块名:$module 操作名:$action 如果为首页则 $app 为index.php;
count($path) == 1 ? list($app) = $path : list($app, $module, $action) = $path;

if (isset($smarty)) {
	//当前位置
	if (isset($_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module])) {
		$location = $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]['title'] . " 》 " . $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]['childs'][$action]['title'];
		$smarty->assign('location', $location);
	}
}
?>