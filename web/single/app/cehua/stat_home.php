<?php
//家园统计
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'log_config.php';

$tab=empty($_GET['tab']) ? 'tab1' : trim($_GET['tab']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$where=' where true';
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';
$sql="select count(*) as count from stat_home $where";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select * from stat_home $where order by date desc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$data=$slavey_level_conf=$skill_level_conf=array();
while ($result && $row=$result->fetch_assoc()){
	$row['week']=date('N',strtotime($row['date']));
	$row['buy_level']=json_decode($row['buy_level'],true);
	$row['call_peri']=json_decode($row['call_peri'],true);
	$row['access_count']=json_decode($row['access_count'],true);
	$row['make_furniture']=json_decode($row['make_furniture'],true);
	$row['day_make_furniture']=json_decode($row['day_make_furniture'],true);
	ksort($row['buy_level']);
	
	//丫鬟等级
	$row['slavey_level']=json_decode($row['slavey_level'],true);
	$slavey_level_conf=array_unique(array_merge($slavey_level_conf,array_keys($row['slavey_level'])));
	sort($slavey_level_conf);
	
	//丫鬟传艺
	$row['skill_count']=(array)json_decode($row['skill_count'],true);
	foreach ($row['skill_count'] as $skill){
		$skill_level_conf=array_unique(array_merge($skill_level_conf,array_keys($skill)));
	}
	sort($skill_level_conf);
	
	//舒适度
	$row['fine']=(array)json_decode($row['fine'],true);

	$data[]=$row;
}
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'tab'=>$tab,
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
$buy_level_conf=array(
	1=>__('小民房'),
	2=>__('大宅院'),
	3=>__('大豪宅'),
);
$skill_conf=array(
	0=>__('琴·演曲'),
	1=>__('棋·对弈'),
	2=>__('书·练笔'),
	3=>__('画·绘色'),
	4=>__('诗·咏情'),
	5=>__('词·生调'),
);
$fine_conf=array(
	array(2560,9999),
	array(1280,2560),
	array(640,1280),
	array(320,640),
	array(140,320),
	array(60,140),
	array(0,60),
);
krsort($fine_conf);
$smarty->assign('fine_conf',$fine_conf);
$smarty->assign('skill_conf',$skill_conf);
$smarty->assign('skill_level_conf',$skill_level_conf);
$smarty->assign('slavey_level_conf',$slavey_level_conf);
$smarty->assign('buy_level_conf',$buy_level_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();