<?php

//更新服务器列表
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __ROOT__.'/config/agent_list_config.php';
include __FUNCTIONS__.'function.php';
include __LIB__ . 'phprpc_php/phprpc_client.php';

$action=empty($_GET['action']) ? '' : trim($_GET['action']);
$phprpc_client = new PHPRPC_Client();
$phprpc_client->useService('http://'.CENTER_DOMAIN.'/center/app/interface/rbac.php');
$phprpc_client->setKeyLength(128);
$phprpc_client->setEncryptMode(2);
$phprpc_client->setTimeout(5);

switch ($action){
	default:
		$serverFile = __CONFIG__ . 'server_list_config.php';
		$sInfo['domain'] = $_SERVER["HTTP_HOST"];
		$sInfo['serverType'] = SERVER_TYPE;
		$sInfo['agent'] = SERVER_AGENT;
		$sInfo['sid'] = SERVER_ID;
		$sInfo['open_time'] = SERVER_OPEN_TIME;
		$sInfo['debug'] = defined('SERVER_DEBUG') ? SERVER_DEBUG : 0;//是否为测试服
		
		//远程调用
		$server_list = $phprpc_client->updateSerList($sInfo);
		is_a($server_list, 'PHPRPC_Error') && exit;
		echo file_put_contents($serverFile, "<?php\n\$server_list = " . var_export($server_list, true) . ";?>");	
		break;
		
	case 'agent':
		$agent_config_file = __CONFIG__ . 'agent_list_config.php';
		//远程调用
		$agent_list = $phprpc_client->getAgentList();
		is_a($agent_list, 'PHPRPC_Error') && exit;
		echo file_put_contents($agent_config_file, "<?php\n\$agent_list = " . var_export($agent_list, true) . ";?>");
		break;
		
	case 'lianyun':
		$lianyun_config_file = __CONFIG__ . 'lianyun_list_config.php';
		//远程调用
		$ragent_list = array_flip($agent_list);
		$agent_id=$ragent_list[SERVER_AGENT];
		//echo $agent_id;
		$lianyun_list = $phprpc_client->getLianyunList($agent_id);
		//print_r($lianyun_list);
		is_a($lianyun_list, 'PHPRPC_Error') && exit;
		echo file_put_contents($lianyun_config_file, "<?php\n\$LianYun_List = " . var_export($lianyun_list, true) . ";?>");
		break;
}
?>