<?php
//装备流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'log_config.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$zuoqi=isset($_GET['zuoqi']) && $_GET['zuoqi']!=='' ? intval($_GET['zuoqi']) : '';
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
	'zuoqi'=>$zuoqi,
	'io'=>$io,
	'type'=>$type,
);

$data=array();
$page='';
$where='';
if($char_id !=''){
	$where=" where char_id=$char_id ";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<='.(strtotime($end_date)) : '';
	$where.=$zuoqi ? " and zuoqi_type=$zuoqi " : '';
	$where.=$io ? " and io=$io " : '';
	$where.=$type ? ' and xm_type in ('.implode(',', $type).')' : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from log_zuoqi $where";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_zuoqi $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['remark']=(array)json_decode($row['remark'],true);
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$smarty->assign('conditions',$conditions);
$smarty->assign('zuoqi_type_conf',$zuoqi_type_conf);
$smarty->assign('zuoqi_io_conf',$zuoqi_io_conf);
$smarty->assign('zuoqi_xm_conf',$zuoqi_xm_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();