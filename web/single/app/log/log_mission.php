<?php
//任务流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Mission.class.php';

$action=empty($_GET['action']) ? 'main' : trim($_GET['action']);
$action_conf=array(
	'main'=>__('主支线任务'),
	'daily'=>__('日常任务'),
);
$action=array_key_exists($action, $action_conf) ? $action : 'main';
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$mid=empty($_GET['mid']) ? '' : my_escape_string(trim($_GET['mid']));
$type=empty($_GET['type']) ? '' : intval($_GET['type']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'action'=>$action,
	'char_id'=>$char_id,
	'mid'=>$mid,
	'type'=>$type,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
);
$data=array();
$page='';
if($char_id){
	$where=" where char_id=$char_id";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$type ? " and type=$type" : '';
	$where.=$mid ? " and mid='$mid'" : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) from log_mission_{$action}_accept $where";
	$count=$mysqli->count($sql);
	$p=new Page($count);
	$sql="select * from log_mission_{$action}_accept $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$query=$mysqli->query($sql);
	$mission=new Mission();
	while ($row=$query->fetch_assoc()){
		//完成时间
		$sql="select time,level,use_time from log_mission_{$action}_complete where char_id={$char_id} and mid='{$row['mid']}' and status=2 and time-use_time={$row['time']}";
		$list=$mysqli->findOne($sql);
		$row['complete_time']=empty($list['time']) ? '' : date('Y-m-d H:i:s',$list['time']);
		$row['complete_level']=empty($list['level']) ? '' : intval($list['level']);
		$row['use_time']=empty($list['use_time']) ? '' : intval($list['use_time']);
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$row['mission_name']=$mission->getName($row['mid']);
		$data[]=$row;
	}
	$page=$p->show();
}

$mission_type_conf=array(
	1=>__('主线'),
	2=>__('支线'),
	3=>__('皇榜'),
	4=>__('押镖'),
	5=>__('帮派'),
	//6=>__('真气'),
	7=>__('宠物'),
	8=>__('真气'),
);
$smarty->assign('mission_type_conf',$mission_type_conf);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();