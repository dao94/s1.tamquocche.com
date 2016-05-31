<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CONFIG__ . 'source_config.php';
include __CONFIG__ . 'game_config.php';

//客户的版本号
$game_content=file_get_contents(dirname(__ROOT__).'/game.php');
preg_match('/version:\"(.*)\",/iU', $game_content, $matches);
$server_info['client_version']=empty($matches[1]) ? '' : $matches[1];
$version_text=get_server_version();
$version_text_filename=dirname(dirname(dirname(__ROOT__))).'/server_bin/'.$version_text.'/bin/config/version.txt';//旧路径
$version_text_filename_new=dirname(dirname(dirname(dirname(__ROOT__)))).'/server_bin/'.$version_text.'/bin/config/version.txt';//新路径
$version_conf_content='';
if(is_file($version_text_filename)){
	$version_conf_content=file_get_contents($version_text_filename);
}else if(is_file($version_text_filename_new)){
	$version_conf_content=file_get_contents($version_text_filename_new);
}

$server_info['server_version']=$version_conf_content;//服务版本号
$server_info['open_time']=date('Y-m-d H:i:s',SERVER_OPEN_TIME);
$server_info['open_day']=ceil((time()-SERVER_OPEN_TIME)/86400);
$server_info['now_time']=date('Y-m-d H:i:s',time());
$server_info['single_version']=is_file(__ROOT__.'/version.txt') ? file_get_contents(__ROOT__.'/version.txt') : '';//后台版本号
$data_info = S('data_info');
if (!$data_info) {
	include __CLASSES__ . 'Mdb.class.php';
	$mongo = new Mdb();
	$mongo->selectDb(MONGO_PERFIX . '4');
	$register = $mongo->count('account_data', array('char_id' => array('$exists' => true)));
	$mongo->close();
	$mysqli = new DbMysqli();
	$time = time() - 86400 * 3;
	//活跃人数
	$aliver = $mysqli->count('select count(distinct `char_id`) from `log_login` where `logout_time`>=' . $time);
	//在线人数
	$onliner = $mysqli->findOne('select `count` from `log_online` order by `id` desc limit 1');
	//充值情况
	$pay = $mysqli->findOne('select sum(`gold`) as `pay_gold`,sum(`money`) as `pay_money`,count(distinct `char_id`) as `pay_count` from `pay_order`');
	$mysqli->close();
	$data_info['register'] = $register;
	$data_info['aliver'] = $aliver;
	$data_info['onliner'] = $onliner['count'];
	$data_info['pay_gold'] = $pay['pay_gold'];
	$data_info['pay_money'] = $pay['pay_money'];
	$data_info['pay_count'] = $pay['pay_count'];
	$data_info['server_camp']=$mongo->find('server_camp', array(),array('_id'=>false));//合服信息
	S('data_info', $data_info, 600);
}
$smarty->assign('cdn_config',$cdn_config);
$smarty->assign('server_info', $server_info);
$smarty->assign('data_info', $data_info);
$smarty->assign('camp_conf', $camp_conf);
$smarty->display();
?>
