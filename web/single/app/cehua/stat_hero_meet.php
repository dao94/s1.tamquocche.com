<?php
//群英会分析
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'game_config.php';

$today=strtotime('today');
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$type=empty($_GET['type']) ? 'history' : trim($_GET['type']);
$type_conf=array(1=>__('char_name_'),2=>__('fight_max_'),3=>__('fight_time_'));
$time_type=array(
	'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
	'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
	'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
	'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
	'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
);
$type=!array_key_exists($type, $time_type) ? 'history' : $type;
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));

$fight_conf=array(
1=>__('1v2'),
2=>__('1v4'),
3=>__('1v8'),
4=>__('1v16'),
5=>__('1v32'),
6=>__('1v64'),
7=>__('1v128'),
8=>__('1v256'),
);


$data=array();
$mysqli=new DbMysqli();
$where=" where entry_id=330300 ";
$where .=$start_date ? " and date<='$start_date' " : '';
$where .=$end_date ? " and date>='$end_date' " : '';
$sql="select count(distinct date) as count from stat_boss $where ";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select * from stat_boss $where order by date desc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
while($result && $row=$result->fetch_assoc()){
	$row['week']=date('N',strtotime($row['date']));
	//参与度
	$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
	$remark=(array)json_decode($row['remark'],true);
	unset($row['remark']);
	$row=array_merge($row,$remark);
	$data[]=$row;
}

$page=$p->show();
$conditions=array(
	'action'=>$action,
	'type'=>$type,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$smarty->assign('fight_conf',$fight_conf);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('time_type',$time_type);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('part_conf',$part_conf);
$smarty->assign('page',$p->show());
$smarty->display();
?>
