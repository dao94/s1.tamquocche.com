<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';
include __AUTH__ . 'lang.php';

 /*
  * 合服数据
  */
function stat_hefu_data($data){
		//导出5天内登陆的玩家  5万 10万 20万 50级以上 60级以上 玩家
		include __CLASSES__.'Mdb.class.php';
		$mysqli = new DbMysqli();
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$limit=2000;
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
		//导出前三天平均充值
		$start_time=strtotime('today')-86400*3;
		$end_time=strtotime('today')+86400;
		$sql="select * from pay_order where ts>=$start_time and ts<$end_time and is_test=0";
		$result=$mysqli->query($sql);
		$money_count=0;
		while($result && $row=$result->fetch_assoc()){
			$money_count+=round($row['money']/100,2);
		}
		$avg_money=round($money_count/3,2);
		$array=array(
			'server'=>SERVER_AGENT.'_'.SERVER_ID,
			'login_player'=>$login_player,
			'max_count'=>$max_count,
			'avg_money'=>$avg_money,
			'count_5w_fight'=>$count_5w_fight,
			'count_10w_fight'=>$count_10w_fight,
			'count_20w_fight'=>$count_20w_fight,
			'count_40level'=>$count_40level,
			'count_50level'=>$count_50level,
			'count_60level'=>$count_60level,
		);
		return array('status' => 'ok', 'info' => $array);
}


$server = new PHPRPC_Server();
########接口函数发布区#########################
$server->add('stat_hefu_data');

########接口函数发布区#########################
$server->setEnableGZIP(true);
$server->setDebugMode(true);
$server->start();
?>
