<?php
//答题流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Scene.class.php';
include __CLASSES__.'Mdb.class.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$entry_name=empty($_GET['entry_name']) ? '' : trim($_GET['entry_name']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'entry_name'=>$entry_name,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
);


$data=array();
$page='';
if($char_id){
	$table='log_answer';
	$where=" where char_id=$char_id";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';

	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$mdb=new Mdb();
	while($result && $row=$result->fetch_assoc()){
		if($row['char_id']){
			$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
			$list=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
			$row['char_name']=$list ? $list['name'] : '';
		}
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();