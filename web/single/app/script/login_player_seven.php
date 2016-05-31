<?php
/* 统计脚本
 *  需求：服务器：91的 1-11服。
	菜单：①7天内有登陆的玩家的角色名、等级、战斗力
 * action:操作名称
 *		1.login_player_seven:7天内有登陆的玩家的角色名、等级、战斗力
 *	执行命令：	  php login_player_seven.php --task=login_player_seven
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'login_player_seven':
		//导出7天内登陆的玩家
		$filename=SERVER_AGENT.'_'.SERVER_ID.'_login_player_seven.txt';
		$fp=fopen($filename, 'a+');
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$limit=500;
		$offset=0;
		$today=strtotime('today')+86400;
		$before_7day=strtotime('today')-86400*7;
		$condition=array('saveTime'=>array('$gte'=>$before_7day,'$lt'=>$today));
		$fields = array('attr');
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $mdb->find('human_offline',$condition,$fields, $result_condition);
			foreach($list as $items){
					$text=iconv('utf-8','gbk',$items['attr']['name']."\t".$items['attr']['fight']."\t".$items['attr']['level']."\n");
					fwrite($fp,$text);
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		fclose($fp);
		break;
}

?>