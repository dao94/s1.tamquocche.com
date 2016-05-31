<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$switch_type_conf=array(
	1=>array('name'=>__('防沉迷'),'file'=>'fcm.txt'),//防沉迷
	2=>array('name'=>__('游戏入口'),'file'=>'game_flag'),//游戏入口
	3=>array('name'=>__('聊天监控'),'file'=>'chat_flag'),//聊天监控
	4=>array('name'=>__('自动创号'),'file'=>'auto_flag'),//自动创号
);

$switch_action_conf=array(
	1=>__('开启'),
	0=>__('关闭'),
);

$flag=array();
foreach($switch_type_conf as $type=>$arr){
	if(is_file(__SWITCH__.$arr['file'])){
		$flag[$type]=1;//开启
	}else{
		$flag[$type]=0;//关闭
	}
}

$smarty->assign('flag',$flag);
$smarty->assign('switch_action_conf',$switch_action_conf);
$smarty->assign('switch_type_conf',$switch_type_conf);
$smarty->display();
?>