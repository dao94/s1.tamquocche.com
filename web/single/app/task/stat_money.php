<?php
/*
 * 统计货币相关信息
 * sum：货币产出和消耗汇总
 * php stat_money.php --task=sum --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'sum':
		//货币产出和消耗汇总
		$stat_table=array('name'=>'stat_money','field'=>'date');
		$log_table=array('name'=>'log_money','field'=>'time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$sql="select type,money_type,io,count(distinct char_id) as count,sum(money_num) as money_num from log_money where time>=$i and time<($i+86400) and (io=0 or io=1) group by type,money_type,io";
			$result=$mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				$row['date']=date('Ymd',$i);
				$row['time']=time();
				$row['sid']=intval(substr(SERVER_ID,1));
				$fields=implode(',', array_keys($row));
				$values=implode(',', array_values($row));
				$sql="replace into stat_money ($fields) values ($values)";
				$mysqli->query($sql);
			}
		}
		break;
}