<?php

session_start();
session_destroy();
unset($_SESSION);
//是否保持
setcookie('hold', 1, time() + 5);
//如果不设置保存状态则推出时清理code
if (!isset($_COOKIE['__IS_SAVE']) || $_COOKIE['__IS_SAVE'] == 0) {
	setcookie('__CODE', NULL);
	setcookie('__IS_SAVE', NULL);
	//setcookie('__USER_ACCOUNT', NULL);
}
header('Location:login.php');
?>