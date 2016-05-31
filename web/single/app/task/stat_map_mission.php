<?php
/*
 * 地图任务相关统计
 * mission_loss：任务流失
 * map_mission_loss：地图任务流失
 * php stat_map_mission.php --task=mission_loss --start_date=2013-06-01 --end_date=2013-07-01
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
switch ($params['task']){
	case 'mission_loss':
		//任务流失,连续2天没登陆，为流失玩家
		$task=new Task();
		$stat_table=array('name'=>'stat_mission_loss','field'=>'date');
		$log_table=array('name'=>'log_online','field'=>'time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$mdb=new Mdb();
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$data=array();
			//任务接收
			$sql="select mid,type,num,count(*) as count from log_mission_main_accept where time>=$i and time<$i+86400 group by mid";
			$result=$mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				$data[$row['mid']]['receive']=$row['count'];//接收数
				$data[$row['mid']]['type']=$row['type'];//类型
				$data[$row['mid']]['num']=$row['num'];//序号
			}
			//可交数
			$sql="select mid,count(*) as count from log_mission_main_complete where time>=$i and time<$i+86400 and status=5 group by mid";
			$result=$mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				$data[$row['mid']]['allow_complete']=$row['count'];//可交数
			}
			
			//完成数
			$sql="select mid,count(*) as count,sum(use_time) as sum_use_time from log_mission_main_complete where time>=$i and time<$i+86400 and status=2 group by mid";
			$result=$mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				$data[$row['mid']]['complete']=$row['count'];//完成数
				//总耗时时间
				isset($data[$row['mid']]['sum_use_time']) ? $data[$row['mid']]['sum_use_time']+=intval($row['sum_use_time']) : $data[$row['mid']]['sum_use_time']=intval($row['sum_use_time']);
			}
			
			//2天内未登陆视为流失玩家
			$loss_char=array();
			for ($db=0;$db<4;$db++){
				$mdb->selectDb(MONGO_PERFIX.$db);
				$list=$mdb->find('characters', array('loginTime'=>array('$lt'=>$i-86400)), array('_id'));
				foreach ($list as $row){
					//进行中流失
					$sql="select m1.mid as m1_mid,m2.mid as m2_mid from log_mission_main_accept m1 left join log_mission_main_complete m2 
						on m1.char_id=m2.char_id and m1.mid=m2.mid and m2.status=2 where m2.mid is NULL and m1.char_id={$row['_id']}";
					$loss_list=$mysqli->find($sql);
					foreach ($loss_list as $item){
						isset($data[$item['m1_mid']]['receive_loss']) ? $data[$item['m1_mid']]['receive_loss']++ : $data[$item['m1_mid']]['receive_loss']=1;
					}
					
					//可交状态流失
					$sql="select m1.mid as m1_mid,m2.mid as m2_mid from log_mission_main_complete m1 left join log_mission_main_complete m2 
						on m1.char_id=m2.char_id and m1.mid=m2.mid and m2.status=2 where m1.char_id={$row['_id']} and m1.status=5 and m2.mid is NULL";
					$loss_list=$mysqli->find($sql);
					foreach ($loss_list as $item){
						isset($data[$item['m1_mid']]['allow_complete_loss']) ? $data[$item['m1_mid']]['allow_complete_loss']++ : $data[$item['m1_mid']]['allow_complete_loss']=1;
					}
				}
			}
			
			//前一天任务流失数据
			$the_day_before=date('Y-m-d',$i-86400);
			$sql="select * from stat_mission_loss where date='$the_day_before'";
			$result=$mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				//数据累加
				isset($data[$row['mid']]['receive']) ? '' : $data[$row['mid']]['type']=$row['type'];//类型
				isset($data[$row['mid']]['num']) ? '' : $data[$row['mid']]['num']=$row['num'];//序号
				//接收数
				isset($data[$row['mid']]['receive']) ? $data[$row['mid']]['receive']+=$row['receive'] : $data[$row['mid']]['receive']=$row['receive'];
				isset($data[$row['mid']]['allow_complete']) ? $data[$row['mid']]['allow_complete']+=$row['allow_complete'] : $data[$row['mid']]['allow_complete']=$row['allow_complete'];
				isset($data[$row['mid']]['complete']) ? $data[$row['mid']]['complete']+=$row['complete'] : $data[$row['mid']]['complete']=$row['complete'];
				isset($data[$row['mid']]['sum_use_time']) ? $data[$row['mid']]['sum_use_time']+=$row['sum_use_time'] : $data[$row['mid']]['sum_use_time']=$row['sum_use_time'];
				$data[$row['mid']]['type']=$row['type'];//类型
				$data[$row['mid']]['num']=$row['num'];//序号
			}
				
			$date=date('Y-m-d',$i);
			$time=time();
			foreach ($data as $mid=>$item){
				isset($item['receive']) ? '' : $item['receive']=0;
				isset($item['receive_loss']) ? '' : $item['receive_loss']=0;
				isset($item['complete']) ? '' : $item['complete']=0;
				isset($item['allow_complete_loss']) ? '' : $item['allow_complete_loss']=0;
				$sql="replace into stat_mission_loss (date,mid,num,type,receive,complete,sum_use_time,receive_loss,allow_complete_loss,time)
					value  ('$date','$mid','{$item['num']}','{$item['type']}','{$item['receive']}','{$item['complete']}',
					'{$item['sum_use_time']}','{$item['receive_loss']}','{$item['allow_complete_loss']}',$time)";
					$mysqli->query($sql);
			}
		}
		break;

	case 'map_mission_loss':
		include __CONFIG__.'map_mission_config.php';
		//连续2天没登陆，为流失玩家
		$diff_day=2;
		$open_day=strtotime(date('Ymd',SERVER_OPEN_TIME));
		$today=strtotime('today');
		if($today-$open_day>30*86400 || $today-$open_day<=86400*$diff_day)	exit('Time Out');

		$task=new Task();
		$stat_table=array('name'=>'stat_map_mission','field'=>'date');
		$log_table=array('name'=>'log_login','field'=>'login_time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$start_time=($open_day==$start_time) ? $start_time+86400*$diff_day : $start_time;
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$date=date('Y-m-d',$i);
			//非流失玩家，两天有登陆的玩家
			$sql="select distinct char_id from log_login where login_time>=$i-86400 and login_time<$i+86400 and is_first=0";
			$result=$mysqli->query($sql);
			$not_loss_char=array();
			while ($result && $row=$result->fetch_assoc()){
				$not_loss_char[]=$row['char_id'];
			}
			$not_loss_and=$not_loss_char  ? ' and char_id not in ('.implode(',', $not_loss_char).')': '';
			unset($not_loss_char);
			$time=time();
			foreach ($map_mission_conf as $map_id=>$mission){
				//流失玩家
				switch ($map_id){
					case 200101:
						$last_map_id=200100;//水镜仙境
						break;
					case 200201:
						$last_map_id=200200;//巨鹿
						break;
					default:
						$last_map_id=$map_id;
						break;
				}
				
				$sql="select max(id) as id from log_login where login_time<$i-86400 and last_map_id=$last_map_id and pos_x>0 and pos_y>0 $not_loss_and group by char_id";
				$result=$mysqli->query($sql);
				$mid=implode("','", $mission);
				while ($result && $row=$result->fetch_assoc()){
					$sql="select char_id,pos_x,pos_y from log_login where id={$row['id']}";
					$list=$mysqli->findOne($sql);
					$char_id=floatval($list['char_id']);
					
					//进行中流失
					$sql="select m1.mid as m1_mid,m2.mid as m2_mid from log_mission_main_accept m1 left join log_mission_main_complete m2 
						on m1.char_id=m2.char_id and m1.mid=m2.mid and m2.status=2 where m1.char_id=$char_id and m2.mid is NULL";
					$loss_list=$mysqli->find($sql);
					foreach ($loss_list as $item){
						//入库
						$sql="insert into stat_map_mission (date,char_id,mid,map_id,pos_x,pos_y,time) value
						('$date',$char_id,'{$item['m1_mid']}',{$map_id},{$list['pos_x']},{$list['pos_y']},$time)";
						$mysqli->query($sql);
					}
				}
			}
		}
		break;
}