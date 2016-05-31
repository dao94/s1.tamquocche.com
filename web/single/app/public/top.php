<?php

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$server_list_config = __CONFIG__ . 'server_list_config.php';
include $server_list_config;

//feiyin组显示所有的服务器
if (in_array('feiyin', $_SESSION['__' . SERVER_TYPE . '_AGENTS'])) {
	//如果用户个人服务器权限为空的话，显示所有服务器
	if (empty($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'])) {
		$agents_list = array_keys($server_list);
		$agent_server_list = $server_list[SERVER_AGENT];
	} else {
		//所属所有代理的指定服务器		所属代理  和 	个人权限服务器的交集  为个人的服务器列表 避免Sửa了个人的代理而没有Sửa个人单服权限
		$agents_list = array_keys($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST']);
		$agent_server_list = array_flip($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'][SERVER_AGENT]);
	}
	//不属于feiyin组并且当前服在他所在的代理列表中
} elseif (!in_array('feiyin', $_SESSION['__' . SERVER_TYPE . '_AGENTS']) && in_array(SERVER_AGENT, $_SESSION['__' . SERVER_TYPE . '_AGENTS'])) {
	//如果用户个人服务器权限为空的话，显示所有服务器
	if (empty($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'])) {
		//所属所有代理的服务器
		$agents_list = $_SESSION['__' . SERVER_TYPE . '_AGENTS'];
		$agent_server_list = $server_list[SERVER_AGENT];
	} else {
		//所属所有代理的指定服务器		所属代理  和 	个人权限服务器的交集  为个人的服务器列表
		$agents_list = array_keys($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST']);
		$agent_server_list = array_flip($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'][SERVER_AGENT]);
	}
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
	//获取代理服务器列表
	case 'get_server_list':
		$agent = isset($_POST['agent']) ? $_POST['agent'] : '';
		!in_array($agent, $agents_list) && ajax_return('error', __('错误的代理！'));
		if (empty($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'])) {
			//获取代理服务器列表
			$agent_server_list = isset($server_list[$agent]) ? array_keys($server_list[$agent]) : array();
		} else {
			$agent_server_list = $_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'][$agent];
		}
		ajax_return('ok', '', $agent_server_list);
		break;
		//获取跳转登录链接
	case 'jump':
		$agent = isset($_POST['agent']) ? $_POST['agent'] : '';
		$sid = isset($_POST['sid']) ? $_POST['sid'] : '';
		!in_array($agent, $agents_list) && ajax_return('error', __('错误的代理！'));
		if (empty($_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'])) {
			//获取代理服务器列表
			$agent_server_list = isset($server_list[$agent]) ? array_keys($server_list[$agent]) : array();
		} else {
			$agent_server_list = $_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'][$agent];
		}
		!in_array($sid, $agent_server_list) && ajax_return('error', __('错误的服务器id！'));
		isset($_COOKIE['__CODE']) && !empty($_COOKIE['__CODE']) ? $data['code'] = $_COOKIE['__CODE']
			: ajax_return('error', __('登录超时'),array('domain'=>$server_list[$agent][$sid][0],'serverType'=>SERVER_TYPE));
		//校验字段
		$data['time'] = time();
		$data['isSave'] = isset($_COOKIE['__IS_SAVE']) ? intval($_COOKIE['__IS_SAVE']) : 0;
		$data['agent'] = $agent;
		$data['sid'] = $sid;
		$data['account'] = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];
		ksort($data);
		$data['sign'] = md5(http_build_query($data) . PHPRPC_KEY);
		$data['domain'] = $server_list[$agent][$sid][0];
		$sid=strtoupper(substr($sid, 0,1));
		switch ($sid) {
			case 'C':
				$data['serverType'] = 'center';
				break;
			case 'A':
				$data['serverType'] = 'agent';
				break;
			case 'K':
				$data['serverType'] = 'kuafu';
				break;
			default:
				$data['serverType'] = 'single';
				break;
		}
		ajax_return('ok', '', $data);
		break;
}
$smarty->assign('user_account',$_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT']);
//代理列表
$smarty->assign('agents_list', $agents_list);
//服务器列表
$smarty->assign('agent_server_list', $agent_server_list);
$smarty->display();
?>