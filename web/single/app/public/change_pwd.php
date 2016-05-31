<?php

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

switch ($action) {
	//Sửa密码
	case 'changePwd':
		$oldPwd = isset($_POST['oldPwd']) ? trim($_POST['oldPwd']) : '';
		$newPwd = isset($_POST['newPwd']) ? trim($_POST['newPwd']) : '';

		if (empty($oldPwd) || empty($newPwd) || $oldPwd == $newPwd)
		ajax_return('error', __('新旧密码不能为空,也不能一样！'));

		//if(!preg_match('/(.*(([a-zA-Z]+\d+)|(\d+[a-zA-Z]+)).*)/',$newPwd,$match))ajax_return('error','新密码必须为至少8位的包含数字和字母的字符串，可以使用特殊字符');

		$userInfo['oldPwd'] = md5($oldPwd);
		$userInfo['newPwd'] = md5($newPwd);
		$userInfo['account'] = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];

		//远程调用
		include __LIB__ . 'phprpc_php/phprpc_client.php';

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->setProxy(NULL);
		$phprpc_client->useService('http://' . CENTER_DOMAIN . '/center/app/interface/rbac.php');
		$phprpc_client->setCharset('UTF-8');
		$phprpc_client->setKeyLength(128);
		$phprpc_client->setEncryptMode(2);

		$userInfo = $phprpc_client->changePwd($userInfo);

		if ($userInfo) {
			session_destroy();
			unset($_COOKIE, $_SESSION);
			ajax_return('ok', __('您密码Sửa成功，请重新登录！'));
		} else {
			ajax_return('error', __('密码Sửa失败！'));
		}
		break;
}
$smarty->display('change_pwd.html');
?>