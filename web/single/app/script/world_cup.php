<?php
/* 统计脚本
 *  php world_cup.php --task=world_cup
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
	case 'world_cup':
		//世界杯 押注次数 获胜次数 押注金额
		$start_date='2014-06-12';
		$end_date='2014-06-22';
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$sql="select * from log_money where money_type=3 and type=124 and time>=$i and time<($i+86400)";
			$result=$mysqli->query($sql);
			$date=date('Y-m-d',$i);
			$jade_count=$jade_yz=0;
			while($result && $row=$result->fetch_assoc()){
				if($row['money_type']==3) $jade_count++;//押注次数
				if($row['money_type']==3 && $row['io']==0) $jade_yz+=$row['money_num'];//押注金额
			}
			$shibai=$win=$shibai_jade=$win_jade=0;
			$sql="select * from log_mail where title='世界杯疯狂猜想大奖' and content like '尊敬的玩家，很遗憾%' and send_time>=$i and send_time<($i+86400)";
			$result=$mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				++$shibai;
				$shibai_jade+=$row['jade'];//押注金额
			}

			$sql="select * from log_mail where title='世界杯疯狂猜想大奖' and content like'尊敬的玩家，恭喜您%' and send_time>=$i and send_time<($i+86400) ";
			$result=$mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				++$win;
				$win_jade+=$row['jade'];//押注金额
			}
			echo SERVER_AGENT.'_'.SERVER_ID."\t".$date."\t".$jade_count."\t".$jade_yz."\t".$shibai."\t".$shibai_jade."\t".$win."\t".$win_jade."\t"."\n";
		}
		break;
}

?>
