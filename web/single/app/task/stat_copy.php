<?php
/*
 * 副本统计
 * 注：策划规定的entry_id范围如下
 * 竞技场:330100  //其他副本已经拿到 stat_copy_new.php文件实现
 * php stat_copy.php --task=800100-820100 --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';

if(!isset($argc) || $argc<2) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
$entry_id=explode('-', $params['task']);
$where_entry=count($entry_id)==2 ? ' entry_id>='.intval($entry_id[0]).' and entry_id<='.intval($entry_id[1]) : ' entry_id='.intval($entry_id[0]);

//进入玩家数和开启次数
$stat_table=array('name'=>'stat_copy','field'=>'date','where'=>$where_entry);
$log_table=array('name'=>'log_copy_enter','field'=>'time','where'=>$where_entry);
$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
$start_time=strtotime($start_date);
$end_time=strtotime($end_date);
$data=array();
for($i=$start_time;$i<=$end_time;$i+=86400){
	$sql="select count(*) as count,count(distinct char_id) as join_in from log_copy_enter where time>=$i and time<($i+86400) and $where_entry";
	$list=$mysqli->findOne($sql);
	$data[$i]['count']=$list ? $list['count'] : 0;//次数
	$data[$i]['join_in']=$list ? $list['join_in'] : 0;//人数
}
switch ($params['task']){
	case '330100':
		//竞技场分析
		$mdb=new Mdb();
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$remark=array();
			//满足人数，当天登陆，等级>=45级，军衔>=二阶校尉
			$sql="select char_id from log_login where login_time>=$i and login_time<$i+86400 and level>=43 group by char_id";
			$result=$mysqli->query($sql);
			$char_list=array();
			while ($result&&$row=$result->fetch_assoc()){
				$char_list[$row['char_id']%4][]=floatval($row['char_id']);
			}
			$remark['allow_join']=0;//当天满足条件的人数
			foreach ($char_list as $db=>$item){
				$mdb->selectDb(MONGO_PERFIX.$db);
				$condition=array('_id'=>array('$in'=>$item),'jiangGuan.curChallengeId'=>array('$gte'=>7));
				$remark['allow_join']+=$mdb->count('challenge', $condition);
			}

			//购买竞技点人数 type=61
			$sql="select count(distinct char_id) as athletics_player,sum(money_num) as athletics_money from log_money
				where time>=$i and time<$i+86400 and type=61 and money_type=3 and io=0";
			$list=$mysqli->findOne($sql);
			$remark['athletics_player']=intval($list['athletics_player']);//购买竞技点人数
			$remark['athletics_money']=intval($list['athletics_money']);//元宝消费总额

			//总场数<=5场的平均战斗力
			$sql="select count(*) as count,char_id from log_copy_enter where time>=$i and time<$i+86400 and entry_id={$params['task']} group by char_id";
			$result=$mysqli->query($sql);
			$mdb->selectDb(MONGO_PERFIX.'5');
			$count1=$count2=$count3=$fight1=$fight2=$fight3=0;
			while ($result&&$row=$result->fetch_assoc()){
				$list=$mdb->findOne('human_offline', array('_id'=>floatval($row['char_id'])), array('attr.fight'));
				if(!empty($list['attr']['fight'])){
					if($row['count']<=5){
						$count1++;
						$fight1+=$list['attr']['fight'];
					}elseif($row['count']>5&&$row['count']<15){
						$count2++;
						$fight2+=$list['attr']['fight'];
					}else{
						$count3++;
						$fight3+=$list['attr']['fight'];
					}
				}
			}
			$remark['avg_fight1']=$count1 ? intval($fight1/$count1) : 0;
			$remark['avg_fight2']=$count2 ? intval($fight2/$count2) : 0;
			$remark['avg_fight3']=$count3 ? intval($fight3/$count3) : 0;

			//入库 以330101入库
			$date=date('Y-m-d',$i);
			$time=time();
			$remark=json_encode($remark);
			$sql="replace into stat_copy (date,entry_id,count,join_in,remark,time) value ('$date',{$entry_id[0]},{$data[$i]['count']},{$data[$i]['join_in']},'$remark',$time)";
			$mysqli->query($sql);
		}
		break;
}