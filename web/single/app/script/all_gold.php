<?php
/* 统计脚本
 * 15-22号每日平台总额列表
 *  php all_gold.php --task=all_gold
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
	case 'all_gold':
		//15-22号每日平台总额列表
		$start_date='2014-06-22';
		$end_date='2014-06-22';
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$sql="select * from pay_order where ts>=$i and ts<($i+86400) and is_test=0";
			$result=$mysqli->query($sql);
			$date=date('Y-m-d',$i);
			$money=0;
			while($result && $row=$result->fetch_assoc()){
				$money+=round($row['money']/100,2);
			}
			echo SERVER_AGENT.'_'.SERVER_ID."\t".$date."\t".$money."\t"."\n";
		}
		break;
}

?>
