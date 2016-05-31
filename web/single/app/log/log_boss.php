<?php
//boss流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Scene.class.php';

$action = 'enter';
//隐藏进入流水--选择栏
$action_conf=array(
	'enter'=>__('进入流水'),
);
$action=array_key_exists($action, $action_conf) ? $action : 'enter';
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$team_id=empty($_GET['team_id']) ? '' : trim($_GET['team_id']);
$entry_name=empty($_GET['entry_name']) ? '' : trim($_GET['entry_name']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'action'=>$action,
	'char_id'=>$char_id,
	'team_id'=>$team_id,
	'entry_name'=>$entry_name,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
);


$data=array();
$page='';
if($char_id){
	$table='log_boss';
	$where=" where char_id=$char_id";
	$where.=$team_id ? " and team_id='$team_id' " : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$scene=new Scene();
	$list=$scene->getList();
	$entry_id=0;
	foreach ($list as $key=>$item){
		if($item['name']==$entry_name){
			$entry_id=$key;
			break;
		}
	}
	$where.=$entry_name ? " and boss_name='$entry_name'" : '';

	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$dataTeam=array();
	while($result && $row=$result->fetch_assoc()){
		$row['entry_name']=$scene->getName($row['entry_id']);
		if($row['team_id']==''){
			$row['groupName']=$row['char_name'];
		}else{
			$sql="select group_concat(distinct char_name order by char_name asc) as groupName from log_boss where team_id='{$row['team_id']}'  and time={$row['time']} ";
			$list=$mysqli->findOne($sql);
			$row['groupName']=$list ? $list['groupName'] : $row['char_name'];
		}
		isset($row['team_id']) ? $row['team_id']=$row['team_id'] : '';
		isset($row['map_id']) ? $row['map_name']=$scene->getName($row['map_id']) : '';
		isset($row['boss_name']) ? $row['boss_name']=$row['boss_name'] : ' ';
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