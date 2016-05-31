<?php

include __FUNCTIONS__ . 'function.php';
include __CLASSES__ . 'Rbac.class.php';

$loginPage = 'login.php';
$tipsPage = '../public/tips.php';
//获取文件在root下的路径
$path = ltrim(str_replace(__ROOT__, '', $_SERVER['SCRIPT_FILENAME']), '/');
$path = explode('/', $path);
//获取当前的应用名：$app 模块名:$module 操作名:$action 如果为首页则 $app 为index.php;
count($path) == 1 ? list($app) = $path : list($app, $module, $action) = $path;

//如果是一键登录则强制转换为http
/* 	if($_SERVER['HTTPS']=='on'&&isset($_GET['action'])&&(($action=='player_manage.php'&&$_GET['action']=='onekey')||($action=='player_login.php'&&$_GET['action']=='do'))){
 $httpUrl	=	'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
 header('Location: '.$httpUrl);
 exit;
 //非一键登录时http转换为https
 }elseif($_SERVER['HTTPS']<>'on'){
 if(isset($_GET['action'])&&(($action=='player_manage.php'&&$_GET['action']=='onekey')||($action=='player_login.php'&&$_GET['action']=='do'))){
 //任何事都不做默认例外http
 }else{
 $httpsUrl	=	'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
 header('Location: '.$httpsUrl);
 exit;
 }
 } */

//判断是否登录
if (Rbac::checkLogin()) {
	//首页只需要认证是否登录
	if ($app <> 'index.php') {
		//验证页面是否需要认证
		if (Rbac::accessAuth($module, $action) !== true) {
			//跳回主页面
			notice(__('没有权限！'), '../public/main.php');
		}
		if (isset($smarty)) {
			//当前位置
			if (isset($_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module])&&isset($_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]['childs'][$action])) {
				$location = $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]['title'] . " 》 " . $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]['childs'][$action]['title'];
				$smarty->assign('location', $location);
			}
		}

		//整个模块不需要日志
		$_module = explode(',', NO_LOG_MODULE);
		//某个具体的模块下的某操作不需要日志 array('module1:action1','module2:action2'……);
		$_action = explode(',', NO_LOG_ACTION);
		//如果不需要日志
		if (!in_array($module, $_module) && !in_array($module . ':' . $action, $_action)) {
			//写操作日志
			$log['account'] = isset($_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT']) ? $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'] : 'notlogin';
			$log['url'] = $_SERVER['REQUEST_URI']; //访问的url带get参数
			$log['post'] = empty($_POST) ? '' : http_build_query($_POST) ;//POST额外参数
			$log['ip'] = get_client_ip();   //获取ip信息
			$log['time'] = date('Ymd H:i:s', $_SERVER['REQUEST_TIME']); //访问时间
			$log['remark'] = isset($_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]) ? $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]['title'] . ' > ' . $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'][$module]['childs'][$action]['title'] : '--'; //备注
			write_log($log, 'action_'.date('Ymd'));
		}
	}
} else {
	if (is_ajax()) {
		exit(json_encode(array('status' => 'error', 'info' => __('您还未登陆或者登陆已超时，请登陆后再进行操作！'))));
	}
	//跳转到登录页面
	if ($app == 'index.php') {
		header('Location:app/public/login.php');
	} elseif ($module == 'public') {
		header('Location:login.php');
	} else {
		notice(__('您还未登陆或者登陆已超时，请登陆后再进行操作！'), '../public/login.php');
	}
}
?>