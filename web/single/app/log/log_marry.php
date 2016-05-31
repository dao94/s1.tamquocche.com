<?php
//结婚流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'game_config.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$ring=isset($_GET['ring']) && $_GET['ring']!=='' ? intval($_GET['ring']) : '';
$marry=isset($_GET['marry']) && $_GET['marry']!=='' ? intval($_GET['marry']) : '';
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
	'marry'=>$marry,
	'ring'=>$ring,
);

$data=array();
$page='';
$where='';
if($char_id !=''){
	$where=" where char_id=$char_id ";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
	$where.=$marry === '' ?  '' : " and marry_type=$marry ";
	$where.=$ring ? " and ring_type=$ring " : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from log_marry $where";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_marry $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$smarty->assign('conditions',$conditions);
$smarty->assign('marry_type_conf',$marry_type_conf);
$smarty->assign('ring_type_conf',$ring_title_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();