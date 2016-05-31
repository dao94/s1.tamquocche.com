<?php

//活动参与度
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'log_config.php';

$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$today=date('Y-m-d',time());
$where=" where date<'$today'";
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';
$sql="select count(distinct date) as count from stat_activity $where";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select distinct date from stat_activity $where order by date desc,type asc,name asc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$min_date=date('Y-m-d',strtotime('today'));
$max_date=date('Y-m-d',SERVER_OPEN_TIME);
while ($result && $row=$result->fetch_assoc()){
	$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
	$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
}
$sql="select * from stat_activity where date>='$min_date' and date<='$max_date' order by date desc,start_time desc,name asc";
$result=$mysqli->query($sql);
$data=array();
while ($result && $row=$result->fetch_assoc()){
	$row['avg_count']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
	$row['start_time']=date('H:i',$row['start_time']);
	$row['hot_ratio']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
	$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
	
	//当日最高在线
	$sql="select max_count,avg_count from stat_online where date='{$row['date']}'";
	$list=$mysqli->findOne($sql);
	$row['max_online']=empty($list) ? 0 : $list['max_count'];
	$row['avg_online']=empty($list) ? 0 : $list['avg_count'];
	
	$data[$key][]=$row;
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