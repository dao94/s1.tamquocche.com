<?php
//等级流失
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CONFIG__ . 'game_config.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__. 'Mdb.class.php';

$action=empty($_GET['action']) ? 'level_loss' : trim($_GET['action']);
$action_conf=array(
	'level_loss'=>__('等级流失'),
	'stat_upgrade'=>__('升级时间'),
);
$data=array();
$cache_id='';
switch ($action){
	default:
	case 'level_loss':
		$cache_lifetime=1;//缓存时间（单位：小时）
		$time_type=array(
		date('Y-m-d',SERVER_OPEN_TIME+86400)=>__('开服第2天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*2)=>__('开服第3天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*3)=>__('开服第4天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*4)=>__('开服第5天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*5)=>__('开服第6天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*6)=>__('开服第7天'),
		);
		$date=empty($_GET['date']) ? date('Y-m-d',strtotime('today')) : my_escape_string(trim($_GET['date']));
		$day=empty($_GET['day']) ? 3 : intval($_GET['day']);
		$conditions=array(
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
			'action'=>$action,
			'date'=>$date,
			'day'=>$day,	
			'cache_lifetime'=>$cache_lifetime,		
		);
		$cache_id=md5(serialize($conditions));//缓存id
		$total_character_count=0;
		if($date==date('Y-m-d',strtotime('today')) && $day){
			//今天登记流失数据
			$smarty->caching=true;
			$smarty->cache_lifetime=3600*$cache_lifetime;//数据缓存
			$smarty->cache_id=$cache_id;
			if(!$smarty->isCached('level.html',$cache_id)){
				$mdb=new Mdb();
				$condition=array('loginTime'=>array('$lt'=>time()-86400*$day,'$gt'=>0));
				$group=array('level');
				for ($i=0;$i<4;$i++){
					$mdb->selectDb(MONGO_PERFIX.$i);
					
					//创号未进入游戏视为0级玩家
					$list=$mdb->count('characters', array('loginTime'=>array('$exists'=>false)), $group);
					foreach ($list as $row){
						$total_character_count+=$row['value']['count'];
						if(empty($data[0]['count'])){
							$data[0]['count']=$row['value']['count'];
							$data[0]['loss_count']=$row['value']['count'];
						}else{
							$data[0]['count']+=$row['value']['count'];
							$data[0]['loss_count']+=$row['value']['count'];
						}
						$data[0]['level']=0;
					}
					
					$list=$mdb->count('characters', array('loginTime'=>array('$exists'=>true)), $group);
					foreach ($list as $row){
						$data[$row['_id']]['level']=$row['_id'];
						$total_character_count+=$row['value']['count'];
						if(!isset($data[$row['_id']]['count'])){
							$data[$row['_id']]['count']=$row['value']['count'];
							$data[$row['_id']]['loss_count']=0;
						}
						else{
							$data[$row['_id']]['count']+=$row['value']['count'];
						}
					}
						
					$list=$mdb->count('characters', $condition, $group);
					foreach ($list as $row){
						if(!isset($data[$row['_id']]['loss_count']))
						$data[$row['_id']]['loss_count']=$row['value']['count'];
						else
						$data[$row['_id']]['loss_count']+=$row['value']['count'];
					}
				}
				foreach ($data as &$row){
					$row['count_ratio']=$total_character_count ? round($row['count']/$total_character_count,4)*100 : 0;
					$row['loss_ratio']=$row['count'] && isset($row['loss_count']) ? round($row['loss_count']/$row['count'],4)*100 : 0;
				}
				ksort($data);
			}
		}else{
			$mysqli=new DbMysqli();
			$sql="select r.total_character_count,l.level,l.count,l.loss_count from stat_level_loss l,stat_reg r
				where l.date='$date' and r.date=l.date order by level asc";
			$result=$mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				$total_character_count=$row['total_character_count'];
				$row['count_ratio']=$row['total_character_count'] ? round($row['count']/$row['total_character_count'],4)*100 : 0;
				$row['loss_ratio']=$row['count'] ? round($row['loss_count']/$row['count'],4)*100 : 0;
				$data[]=$row;
			}
		}
		$smarty->assign('day_options',range(1,7));
		$smarty->assign('total_character_count',$total_character_count);
		$smarty->assign('time_type',$time_type);
		break;

	case 'stat_upgrade':
		//升级时间
		$date=empty($_GET['date']) ? date('Y-m-d',strtotime('yesterday')) : my_escape_string(trim($_GET['date']));
		$conditions=array(
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
			'action'=>$action,
			'date'=>$date,
			'today'=>date('Y-m-d',strtotime('today')),
		);
		$mysqli=new DbMysqli();
		$sql="select level,min_time,count,sum_time from stat_upgrade where date='$date' order by level asc;";
		$result=$mysqli->query($sql);
		$index=0;
		while ($result && $row=$result->fetch_assoc()){
			$avg_time=$row['count'] ? intval($row['sum_time']/$row['count']) : 0;
			$row['avg_time']=round($avg_time/60,2);
			$row['min_time']=format_interval($row['min_time'],2);
			$data[$index][]=$row;
			$row['level']%30==0 ? $index++ : '';
		}
		$time_conf=array(
		date('Y-m-d',SERVER_OPEN_TIME)=>__('开服当天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*1)=>__('开服2天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*2)=>__('开服3天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*3)=>__('开服4天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*4)=>__('开服5天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*5)=>__('开服6天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*6)=>__('开服7天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*13)=>__('开服14天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*29)=>__('开服30天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*59)=>__('开服60天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*89)=>__('开服90天'),
		date('Y-m-d',SERVER_OPEN_TIME+86400*179)=>__('开服180天'),
		date('Y-m-d',strtotime('yesterday'))=>__('昨天'),
		);
		$smarty->assign('time_conf',$time_conf);
		break;
}

$smarty->assign('action_conf',$action_conf);
$smarty->assign('data',$data);
$smarty->assign('conditions',$conditions);
$smarty->display('',$cache_id);