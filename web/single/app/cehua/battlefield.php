<?php
//战场
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'log_config.php';

//平均在线取值范围 20:00-20:30
$avg_time=array('20:00','20:30');
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$start_time=$start_date ? strtotime($start_date) : 0;
$end_time=$end_date ? strtotime($end_date)+86400 : strtotime('tomorrow');
$where=' where 1';
$where.=$start_time ? " and end_time>=$start_time" : '';
$where.=$end_time ? " and end_time<$end_time" : '';
$sql="select count(distinct from_unixtime(end_time,'%Y%m%d')) as count from log_battlefield $where";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select distinct from_unixtime(end_time,'%Y%m%d') as date from log_battlefield $where order by end_time desc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$min_time=time();
$max_time=strtotime(date('Ymd',SERVER_OPEN_TIME))+86400;
while ($result && $row=$result->fetch_assoc()){
	$max_time=strtotime($row['date'])+86400>$max_time ? strtotime($row['date'])+86400 : $max_time;
	$min_time=strtotime($row['date'])<$min_time ? strtotime($row['date']) :  $min_time;
}
$sql="select from_unixtime(end_time,'%Y-%m-%d') as date,count(*) as count,avg_online,sum(kill_count) as kill_count,
	sum(assist_count) as assist_count,sum(occupy_count) as occupy_count,sum(self_destruction) as self_destruction,
	sum(case when win=1 then 1 else 0 end) as win1,sum(case when win=2 then 1 else 0 end) as win2,
	sum(case when score1=5000 then 1 when score2=5000 then 1 else 0 end) as completion_count
	from log_battlefield where end_time>=$min_time and end_time<$max_time group by date order by date desc";
$result=$mysqli->query($sql);
$data=array();
while ($result && $row=$result->fetch_assoc()){
	if(empty($row['avg_online'])){
		$online_start=strtotime($row['date'].$avg_time[0]);
		$online_end=strtotime($row['date'].$avg_time[1]);
		$sql="select avg(count) as avg_online from log_online where time>=$online_start and time<=$online_end";
		$list=$mysqli->findOne($sql);
		$row['avg_online']=intval($list['avg_online']);
		if (!empty($row['avg_online'])){
			$time=strtotime($row['date']);
			$sql="update log_battlefield set avg_online={$row['avg_online']} where end_time>=$time and end_time<($time+86400)";
			$mysqli->query($sql);
		}
	}
	//参与人数,剔除重复进入战场
	$this_day=strtotime($row['date']);
	$sql="select char_list from log_battlefield where start_time>=$this_day and start_time<($this_day+86400)";
	$list=$mysqli->find($sql);
	$char_data=array();
	foreach($list as $item){
		$item['char_list']=str_replace(array('{',',}'),array('[',']'),$item['char_list']);
		$char_list=json_decode($item['char_list'],true);
		$char_data=array_merge($char_data,$char_list);
	}
	$row['join_in']=count(array_unique($char_data));
	
	$row['week']=date('N',$this_day);
	//在线参与率=参与人数/平均在线人数
	$row['online_ratio']=$row['avg_online'] ? round($row['join_in']/$row['avg_online'],4)*100 : 0;
	//人均杀人=杀人数总和/参与人数
	$row['avg_kill']=$row['join_in'] ? round($row['kill_count']/$row['join_in'],2) : 0;
	//人均助攻=助攻数总和/参与人数
	$row['avg_assist']=$row['join_in'] ? round($row['assist_count']/$row['join_in'],2) : 0;
	//人均占领据点=占领数总和/参与人数
	$row['avg_occupy']=$row['join_in'] ? round($row['occupy_count']/$row['join_in'],2) : 0;
	//人均使用自爆技能数=自爆数总和/参与人数
	$row['avg_self_destruction']=$row['join_in'] ? round($row['self_destruction']/$row['join_in'],2) : 0;
	//铁血胜率=胜利场次/战场开启场次
	$row['win1_ratio']=$row['count'] ? round($row['win1']/$row['count'],4)*100 : 0;
	//黄巾胜率=胜利场次/战场开启场次
	$row['win2_ratio']=$row['count'] ? round($row['win2']/$row['count'],4)*100 : 0;
	//完整场数比=达到5K分（最好是读配置）的场数（一方阵营达到5K即战场胜利）/总场数
	$row['completion_ratio']=$row['count'] ? round($row['completion_count']/$row['count'],4)*100 : 0;
	$data[]=$row;
}
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME)
);
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
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();