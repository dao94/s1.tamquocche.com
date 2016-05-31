<?php
//武魂统计
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'log_config.php';

$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$where=' where true';
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';
$sql="select count(*) as count from stat_wuhun $where";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select * from stat_wuhun $where order by date desc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$data=array();
$level_conf=array();
while ($result && $row=$result->fetch_assoc()){
	$row['week']=date('N',strtotime($row['date']));
	$row['total_list']=(array)json_decode($row['total_list'],true);
	$row['level_list']=(array)json_decode($row['level_list'],true);
	$row['offering_level_list']=(array)json_decode($row['offering_level_list'],true);
	ksort($row['offering_level_list']);
	$data[]=$row;
	$level_conf=array_unique(array_merge($level_conf,array_keys($row['total_list'])));
}
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
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
sort($level_conf);
$smarty->assign('level_conf',$level_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();