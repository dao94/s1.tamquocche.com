<?php

/*
 * @author wangyi
 * @date 2013-05-09 11:01:33
 * 语言设置
 */

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

switch ($type) {
	case 'set':
		$lang = isset($_POST['lang']) ? trim($_POST['lang']) : 'zh-cn';
		$_SESSION['__' . SERVER_TYPE . '_LANG'] = $lang;
		ajax_return('ok', __('语言设置成功'));
		break;
}
$smarty->display('rbac_lang.html');
?>
