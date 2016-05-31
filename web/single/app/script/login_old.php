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
		$start_date='2014-07-02';
		$start_time=strtotime($start_date);
		$end_time=strtotime($start_date)+86400-1;
		$open_time=SERVER_OPEN_TIME;
		if($open_time<$end_time){
			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=40 and is_first!=1 ";
			$count_40=$mysqli->count($sql);

			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=45 and level>=41 and is_first!=1 ";
			$count_45=$mysqli->count($sql);

			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=50 and level>=46 and is_first!=1 ";
			$count_50=$mysqli->count($sql);

			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=55 and level>=51 and is_first!=1 ";
			$count_55=$mysqli->count($sql);

			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=60 and level>=56 and is_first!=1 ";
			$count_60=$mysqli->count($sql);

			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=65 and level>=61 and is_first!=1 ";
			$count_65=$mysqli->count($sql);


			$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time level>65 and is_first!=1 ";
			$count_70=$mysqli->count($sql);

			$total_count=$count_40+$count_45+$count_50+$count_55+$count_60+$count_65;

			echo SERVER_AGENT.'_'.SERVER_ID."\t".$total_count."\t".$count_40."\t".$count_45."\t".$count_50."\t".$count_55."\t".$count_60."\t".$count_65."\t".$count_70."\t"."\n";

		}
		break;
}

?>
