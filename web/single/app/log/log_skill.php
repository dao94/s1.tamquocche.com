<?php
//技能流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Skill.class.php';
include __CONFIG__.'log_config.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$min_level=empty($_GET['min_level']) ? '' : intval($_GET['min_level']);
$max_level=empty($_GET['max_level']) ? '' : intval($_GET['max_level']);
$type=empty($_GET['type']) ? '' : intval($_GET['type']);
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'min_level'=>$min_level,
	'max_level'=>$max_level,
	'type'=>$type,
	'from'=>$from,
);

$data=array();
$page='';
if($char_id){
	$where=" where char_id=$char_id";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$min_level ? " and old_level>=$min_level" : '';
	$where.=$max_level ? " and old_level<=$max_level" : '';
	$where.=$type ? " and type=$type" : '';

	$mysqli=new DbMysqli();
	$sql='select count(*) as count from log_skill '.$where;
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_skill $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$skill=new Skill();
	while($result && $row=$result->fetch_assoc()){
		$row['skill_name']=$skill->getName($row['skill_id']);
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$skill_type_conf=array(1=>__('学习'),2=>__('升级'));
$smarty->assign('conditions',$conditions);
$smarty->assign('skill_type_conf',$skill_type_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();