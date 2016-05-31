<?php
//征战天下统计
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
$sql="select count(*) as count from stat_check_point_zzsf $where";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select * from stat_check_point_zzsf $where order by date desc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$data=array();
while ($result && $row=$result->fetch_assoc()){
	$row['week']=date('N',strtotime($row['date']));
	//参与度
	$row['join_ratio']=$row['allow_join'] ? round($row['join_count']/$row['allow_join'],4)*100 : 0;
	$row['city_count']=json_decode($row['city_count'],true);
	$row['city_total_count']=array_sum($row['city_count']);
	$row['chapter_count']=json_decode($row['chapter_count'],true);
	$data[]=$row;
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
$city_conf=array(
	1=>__('洛阳'),
	2=>__('官渡'),
	101=>__('隐世之地'),
	3=>__('西凉'),
	201=>__('无双之地'),
	4=>__('成都'),
	102=>__('隐世之地'),
	5=>__('汉中'),
	103=>__('隐世之地'),
	202=>__('无双之地'),
);
$smarty->assign('city_conf',$city_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();