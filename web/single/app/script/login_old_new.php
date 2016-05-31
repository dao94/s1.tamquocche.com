<?php
/* 统计脚本
 *  php login_old.php --task=login_old
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit();
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'login_old':
		//世界杯 押注次数 获胜次数 押注金额
		$start_date='2014-06-19';
		$start_time=strtotime($start_date);
		$end_time=strtotime($start_date)+86400-1;
		$open_time=SERVER_OPEN_TIME;
		if($open_time<$end_time){
			
			$total_count=0;
			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and        level>=41 and is_first!=1 ";
			$total_count=$mysqli->count($sql);

			echo SERVER_AGENT.'_'.SERVER_ID."\t".$total_count."\n";

		}
		break;
}

?>
