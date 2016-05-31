<?php

//每日登录注册统计
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__. 'Mdb.class.php';

$today=strtotime('today');
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$time_type=array(
	'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
	'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
	'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
	'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
	'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
);
$type=empty($_GET['type']) ? 'near30' : trim($_GET['type']);
$type=!array_key_exists($type, $time_type) ? 'near30' : $type;
$start_date=empty($_GET['start_date']) ? date('Y-m-d',$today-86400*30) : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['end_date']));
$sort=isset($_GET['sort']) ? intval($_GET['sort']) : 0;
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'type'=>$type,
	'open_date'=>$open_date,
	'sort'=>$sort,
);
$sort=($sort==0) ? 'desc' : 'asc';//日期排序

$mysqli=new DbMysqli();
$where=" where l.date>='$start_date' and l.date<='$end_date' and r.date=l.date";
$sql='select count(*) as count from stat_reg r,stat_login l '.$where;
$total_count=$mysqli->count($sql);
$p=new Page($total_count,30);
$sql="select r.character_count,r.total_character_count,l.* from stat_reg r,stat_login l $where
	order by l.date $sort limit {$p->firstRow},{$p->listRows}";
$data=$mysqli->find($sql);
foreach ($data as &$row){
	$row['week']=date('N',strtotime($row['date']));
	$row['login_ratio']=empty($row['login_player']) ? 0 : round($row['login_count']/$row['login_player'],2);
}

$smarty->assign('conditions',$conditions);
$smarty->assign('time_type',$time_type);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();