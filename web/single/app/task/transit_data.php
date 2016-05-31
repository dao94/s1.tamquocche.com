<?php
//运输单服数据到中央后台和代理中央后台
/*
 * pay_order：充值订单
 * online:每日分时在线
 * fight:战斗力
 * user_keep:推送留存率
 * stat_reg:注册数（账号、角色）
 * money_keep:货币滞留率
 * money_keep_detail:货币滞留率详细
 * stat_online_player:登录人数和每日在线统计
 * stat_cost:消费与产出
 * reward_verify:奖励审核
 * player_question:玩家问题
 * pay_verify:充值审核
 * server_open_count:服务器开服当天注册数
 * active_player:统计活跃玩家分等级数量
 * php transit_data.php --task=login_player --start_date=2014-01-01 --end_date=2013-07-01
 * */

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Transit.class.php';
include __CONFIG__.'log_config.php';
include __LIB__.'phprpc_php/phprpc_client.php';

$open_time=SERVER_OPEN_TIME;
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期

if(!isset($argc) || $argc<2 || time()<$open_time+1800 || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组transit
$task->name=$params['task'];
$mysqli=$task->mysqli();
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? 0 : trim($params['end_date']);
$start_date=$start_date&&strtotime($open_date)>strtotime($start_date) ? $open_date : $start_date;
$start_time=empty($start_date) ? 0 : strtotime($start_date);
$end_time=empty($end_date) ? 0 : strtotime($end_date)+86400;
switch ($params['task']){
	default:
		sleep(rand(0,20));
		$Transit=new Transit();
		if(method_exists($Transit,$params['task'])){
			$Transit->$params['task']('center',$start_time,$end_time);//推送到中央后台
		}
		break;
}