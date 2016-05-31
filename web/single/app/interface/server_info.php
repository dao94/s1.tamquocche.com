<?php

//中央服到单服  Sửa服务器信息
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__ . 'function.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';

/*
 * 	Sửaip白名单
 * 	#参数说明##############
 * 	$iplist  array
 * 	#返回值################
 */

function changeWhiteIp($iplist) {
	$ip_config_f = __CONFIG__ . 'ip_config.php'; //ip白名单配置文件
	$ip_config = "<?php\n\$ips	=	" . var_export($iplist, true) . ";\n?>";
	if (is_writeable($ip_config_f)) {
		return file_put_contents($ip_config_f, $ip_config) > 0 ? "更新ip白名单成功" : "更新ip白名单失败";
	} else {
		return "ip配置文件不可写";
	}
}

/*
 * 	获取和Sửa代理服各链接
 * 	#参数说明##############
 * 	$urls  string
 * 	#返回值################
 */

function changeUrl($urls = '') {
	//如果为空的话则为获取本地列表
	$url_config_f = __CONFIG__ . 'url_config.php'; //游戏Link文件
	if (empty($urls)) {
		include $url_config_f;
		return $url_config;
	} else {
		if (is_writeable($url_config_f)) {
			return file_put_contents($url_config_f, "<?php\n\$url_config = " . $urls . ";\n?>") > 0 ? "更新Link成功" : "更新Link失败";
		} else {
			return "url配置文件不可写";
		}
	}
}

/*
 * 	Sửa代理服标题和开服时间
 * 	#参数说明##############
 * 	$conf  array
 * 	#返回值################
 * 	bool
 */

function changeSerConf($conf = array('key' => '', 'val' => '')) {
	//只支持Sửa标题和开服时间
	if (!in_array($conf['key'], array('title', 'open_time', 'agent')))
	return false;

	//如果为空的话则为获取本地列表
	$server_config_f = __CONFIG__ . 'server_config.php'; //游戏Link文件
	$server_config = array();
	include $server_config_f;
	//保存原始内容
	$server_config['SERVER_AGENT'] = defined('SERVER_AGENT') ? SERVER_AGENT : '';
	$server_config['SERVER_ID'] = defined('SERVER_ID') ? SERVER_ID : '';
	$server_config['SERVER_TITLE'] = defined('SERVER_TITLE') ? SERVER_TITLE : '';
	$server_config['SERVER_OPEN_TIME'] = defined('SERVER_OPEN_TIME') ? SERVER_OPEN_TIME : '';

	//Sửa项  开服时间要转换为本地时间
	$server_config['SERVER_' . strtoupper($conf['key'])] = $conf['key'] == 'open_time' ? strtotime($conf['val']) : addslashes($conf['val']);

	//生成新配置内容
	$server_config_c = '';
	foreach ($server_config as $key => $val) {
		$server_config_c .= "define('" . $key . "','" . $val . "');\n";
	}
	//写入新内容
	if (is_writeable($server_config_f)) {
		return file_put_contents($server_config_f, "<?php\n" . $server_config_c . "?>") > 0 ? true : false;
	} else {
		return false;
	}
}

$server = new PHPRPC_Server();
########接口函数发布区#########################
$server->add('changeWhiteIp');
$server->add('changeUrl');
$server->add('changeSerConf');
########接口函数发布区#########################
$server->setEnableGZIP(true);
$server->setDebugMode(true);
$server->start();
?>