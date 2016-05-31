<?php
//加载时间统计
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Page.class.php';

$action=empty($_GET['action']) ? 'loader_time' : trim($_GET['action']);
$action_conf=array(
	'loader_time'=>__('加载时间'),
	'total_loader_time'=>__('总耗时'),
);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$type=isset($_GET['type']) ? intval($_GET['type']) : 1;
$conditions=array(
	'action'=>$action,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'type'=>$type,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME)
);

$mysqli=new DbMysqli();
$where=" where type=$type";
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';
$sql='select count(*) as count from stat_loader_time '.$where;
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows,5);
$sql="select * from stat_loader_time $where order by date desc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$data=array();
while ($result && $row=$result->fetch_assoc()){
	$row['loader_login_data']=(array)json_decode($row['loader_login_data'],true);
	$row['loader_login_count']=array_sum($row['loader_login_data']);
	$row['stop_login_data']=(array)json_decode($row['stop_login_data'],true);
	$row['stop_login_count']=array_sum($row['stop_login_data']);
	$row['loader_game_data']=(array)json_decode($row['loader_game_data'],true);
	$row['loader_game_count']=array_sum($row['loader_game_data']);
	$row['total_loader_game_data']=(array)json_decode($row['total_loader_game_data'],true);
	$row['total_loader_game_count']=array_sum($row['total_loader_game_data']);
	$row['week']=date('w',strtotime($row['date']));
	$data[]=$row;
}

$loader_login_conf=array(
	array(1,3),
	array(4,6),
	array(7,15),
	array(16,'∞'),
);
$stop_login_conf=array(
	array(1,5),
	array(6,20),
	array(21,40),
	array(41,'∞'),
);
$loader_game_conf=array(
	array(1,20),
	array(21,30),
	array(31,50),
	array(51,90),
	array(90,'∞'),
);
$total_loader_game_conf=array(
	array(1,10),
	array(11,20),
	array(21,30),
	array(31,40),
	array(41,50),
	array(51,60),
	array(61,'∞'),
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
$smarty->assign('action_conf',$action_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('loader_login_conf',$loader_login_conf);
$smarty->assign('stop_login_conf',$stop_login_conf);
$smarty->assign('loader_game_conf',$loader_game_conf);
$smarty->assign('total_loader_game_conf',$total_loader_game_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->assign('conditions',$conditions);
$smarty->display();