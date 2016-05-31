<?php
/* 统计脚本
 *  需求：服务器：4399的 12-15服。
	菜单：①5天内有登陆的玩家的角色名、等级、战斗力
 * action:操作名称
 *		1.login_player_five_fight:5天内有登陆的玩家的角色名、等级、战斗力
 *	执行命令：	  php login_player_five_fight.php --task=login_player_five_fight
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'login_player_five_fight':
		//导出5天内登陆的玩家  5万 10万 20万 50级以上 60级以上 玩家
		$filename=SERVER_AGENT.'_'.SERVER_ID.'_login_five_fight.txt';
		$fp=fopen($filename, 'w');
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$limit=500;
		$offset=0;
		$login_player=$count_40level=$count_50level=$count_60level=$count_5w_fight=$count_10w_fight=$count_20w_fight=0;
		$today=strtotime('today')+86400;
		$before_5day=strtotime('today')-86400*5;
		$condition=array('saveTime'=>array('$gte'=>$before_5day,'$lt'=>$today));
		$fields = array('attr');
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $mdb->find('human_offline',$condition,$fields, $result_condition);
			foreach($list as $items){
					$login_player++;
					if($items['attr']['fight']>=50000)$count_5w_fight++;
					if($items['attr']['fight']>=100000)$count_10w_fight++;
					if($items['attr']['fight']>=200000)$count_20w_fight++;
					if($items['attr']['level']>=40)$count_40level++;
					if($items['attr']['level']>=50)$count_50level++;
					if($items['attr']['level']>=60)$count_60level++;
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		//导出前一天的最高在线
		$yesterday=date('Y-m-d',strtotime('yesterday'));
		$sql="select * from stat_online where date='$yesterday' limit 1";
		//echo $sql."\n";
		$list=$mysqli->findOne($sql);
		$max_count=empty($list) ? 0 : $list['max_count'];
		//print_r($list);
		//导出前三天平均充值
		$start_time=strtotime('today')-86400*3;
		$end_time=strtotime('today')+86400;
		$sql="select * from pay_order where ts>=$start_time and ts<$end_time and is_test=0";
		$result=$mysqli->query($sql);
		$money_count=0;
		while($result && $row=$result->fetch_assoc()){
			$money_count+=round($row['money']/100,2);
		}
		//echo $money_count."\n";
		$avg_money=round($money_count/3,2);
		echo SERVER_AGENT.'_'.SERVER_ID."\t".$login_player."\t".$max_count."\t".$avg_money."\t".$count_5w_fight."\t".$count_10w_fight."\t".$count_20w_fight."\t".$count_40level."\t".$count_50level."\t".$count_60level."\t"."\n";

//		$text=iconv('utf-8','gbk',SERVER_AGENT.'_'.SERVER_ID."\t".$login_player."\t".$max_count."\t".$avg_money."\t".$count_5w_fight."\t".$count_10w_fight."\t".$count_20w_fight."\t".$count_50level."\t".$count_60level."\t");
//		fwrite($fp,$text);
//		fclose($fp);
		break;
}

?>
