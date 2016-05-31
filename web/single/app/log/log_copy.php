<?php
//副本流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Scene.class.php';

$action=empty($_GET['action']) ? 'enter' : trim($_GET['action']);
$action_conf=array(
	'enter'=>__('进入流水'),
	'buy_times'=>__('购买次数'),
	'saodang'=>__('副本扫荡'),
);
$action=array_key_exists($action, $action_conf) ? $action : 'enter';
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$entry_name=empty($_GET['entry_name']) ? '' : trim($_GET['entry_name']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'action'=>$action,
	'char_id'=>$char_id,
	'entry_name'=>$entry_name,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
);

$data=array();
$page='';
if($char_id){
	$table='log_copy_'.$action;
	$where=" where char_id=$char_id";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$scene=new Scene();
	$list=$scene->getList();
	$entry_id=array();
	foreach ($list as $key=>$item){
		if($entry_name&&strpos($item['name'],$entry_name)!==false){
			$entry_id[]=$key;
		}
	}
	$entry_id=implode(',', $entry_id);
	$where.=$entry_id ? " and entry_id in ($entry_id)" : '';

	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['entry_name']=$scene->getName($row['entry_id']);
		isset($row['map_id']) ? $row['map_name']=$scene->getName($row['map_id']) : '';
		if(!empty($row['quit_time'])){
			$row['duration']=format_interval(intval($row['quit_time']-$row['time']));
			$row['quit_time']=date('Y-m-d H:i:s',$row['quit_time']);
			
		}
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();