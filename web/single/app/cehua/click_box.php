<?php
//功能提醒框
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'log_config.php';
include __CONFIG__.'click_box_config.php';

$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date'])); //声明开始时间
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));       //声明结束时间
$date=empty($_GET['date']) ? '' : my_escape_string(trim($_GET['date']));                   //声明日期
$today=date('Y-m-d',time());

//项目等级段值
$levels=array(
	1=>__('30-40'),
	2=>__('41-50'),
	3=>__('51-60'),
	4=>__('61-100'),
);
//搜索条件
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
    'date'=>$date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
);

//查询条件
$where=" where true ";
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';

$mysqli=new DbMysqli();
$sql="select count(distinct date) as count from stat_click_box $where";
$total_rows=$mysqli->count($sql);//记录总行数
$p=new Page($total_rows);
//按日期分类查询结果集
$sql="select distinct date from stat_click_box $where order by date desc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$min_date=date('Y-m-d',strtotime('today'));
$max_date=date('Y-m-d',SERVER_OPEN_TIME);
while ($result && $row=$result->fetch_assoc()){
	$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
	$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
}
$sql="select * from stat_click_box where date>='$min_date' and date<='$max_date' order by date desc,type asc ";
$result=$mysqli->query($sql);
$data=array();
//遍历结果集
while($result && $row=$result->fetch_assoc()){
	$row['remark']=(array)json_decode($row['remark'],true);  //转换remark格式，成为$row数组中的一个元素组
	ksort($row['remark']);
	$row['click_ratio']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
	$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
	$data[$key][]=$row;
}
//print_r($data);
$page=$p->show(); //调用分页类


//时间选择配置
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

//变量赋值
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('levels',$levels);
$smarty->assign('click_box_conf',$click_box_conf);
$smarty->assign('page',$page);
$smarty->assign('data',$data);
$smarty->display();



