<?php
//超值vip
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

//超值vip 配置
$items_conf=array(
'22200001073'=>__('极速成长计划(7天)'),
'22200001154'=>__('极速成长计划(15天)'),
'22200002073'=>__('土豪育成计划(7天)'),
'22200002154'=>__('土豪育成计划(15天)'),
'22200003073'=>__('神装速成计划(7天)'),
'22200003154'=>__('神装速成计划(15天)'),
);
$items=array_keys($items_conf);
$items_str=implode(',',$items);//获得查询条件 item_id in ($items_str)
$start_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$end_date=date('Y-m-d',SERVER_OPEN_TIME+86400*16);//  截止购买时间  开服1到15天
$start_date=empty($_GET['start_date']) ? $start_date : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? $end_date : my_escape_string(trim($_GET['end_date']));
$start_time=strtotime($start_date);
$end_time=strtotime($end_date);
$where=" where true ";
$where.=$start_date ? " and time>=$start_time" : '';
$where.=$end_date ? " and time<=$end_time " : '';
$sql="select item_id,time from log_items $where and type=15 and io=1 and item_id in ($items_str) order by time asc";
$mysqli=new DbMysqli();
$query=$mysqli->query($sql);
$data=$array=array();
while($row=$query->fetch_assoc()){
	$date=date('Y-m-d',$row['time']);
	$array[$row['item_id']]=isset($array[$row['item_id']]) ? ++$array[$row['item_id']] : $array[$row['item_id']]=1;//累计各个成长计划的数据
	foreach($items_conf as $key=>$item){
		$data[$date][$key]=isset($array[$key]) ? $array[$key] : 0;
	}
	$data[$date]['vip']=array_sum($array);
}
krsort($data);
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('items_conf',$items_conf);
$smarty->display();