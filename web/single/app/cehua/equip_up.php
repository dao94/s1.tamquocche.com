<?php

//装备升阶分析
define('__ROOT__',str_replace(array('//','\\'),array('/','/'),dirname(dirname(__DIR__) )));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'game_config.php';

$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));

$conditions=array(
	'action'=>$action,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'today'=>date('Y-m-d',strtotime('today')),
);

$data=array();
$where=" where true ";
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';
$sql="select count(distinct date) as count from stat_equip $where ";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select distinct date from stat_equip $where order by date desc,level asc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$min_date=date('Y-m-d',strtotime('today'));
$max_date=date('Y-m-d',SERVER_OPEN_TIME);
while ($result && $row=$result->fetch_assoc()){
	$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
	$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
}
$sql="select * from stat_equip where date>='$min_date' and date<='$max_date' order by date desc,level asc ";
$array_set=$array_part=array();
$result=$mysqli->query($sql);

$set_conf=array(
1=>__('30级紫装'),
2=>__('30级橙装'),
3=>__('40级紫装'),
4=>__('40级橙装'),
5=>__('50级紫装'),
6=>__('50级橙装'),
7=>__('60级紫装'),
8=>__('60级橙装'),
9=>__('65级紫装'),
10=>__('65级橙装'),
);

//配置等级和颜色
$level_conf=array(
1=>array(1=>30,2=>4),
2=>array(1=>30,2=>5),
3=>array(1=>40,2=>4),
4=>array(1=>40,2=>5),
5=>array(1=>50,2=>4),
6=>array(1=>50,2=>5),
7=>array(1=>60,2=>4),
8=>array(1=>60,2=>5),
9=>array(1=>65,2=>4),
10=>array(1=>65,2=>5),
);

$date='';
while ($result && $row=$result->fetch_assoc()){
	if($date!=$row['date']) {$array_set=$array_part=array();}//日期为不同值时 重新计算 如 2013-03-13 前天 就是2013-03-12
	$level=$row['level']; $colour=$row['colour'];$part=$row['part'];
	isset($array_set[$level][$colour]) ?  $array_set[$level][$colour]+=$row['count'] : $array_set[$level][$colour]=$row['count'];
	isset($array_part[$level][$colour][$part]) ?  $array_part[$level][$colour][$part]+=$row['count'] : $array_part[$level][$colour][$part]=$row['count'];
	$row['week']=date('N',strtotime($row['date']));
	$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
	$data[$key]['set']=$array_set;
	$data[$key]['part']=$array_part;
	$data[$key]['count']=$row['allow_player'];
	$date=$row['date'];
}
$page=$p->show();


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
$smarty->assign('level_conf',$level_conf);
$smarty->assign('set_conf',$set_conf);
$smarty->assign('part_conf',$part_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();

?>