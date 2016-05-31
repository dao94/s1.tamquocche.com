<?php
//邮件流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'log_config.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$type=empty($_GET['type']) ? '' : intval($_GET['type']);
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'type'=>$type,
	'from'=>$from,
);

$data=array();
$page='';
if($char_id){
	$where=" where (sender_id=$char_id or receive_id=$char_id)";
	$where.=$start_date ? ' and send_time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and send_time<'.(strtotime($end_date)+86400) : '';
	$where.=$type ? " and type=$type" : '';

	$mysqli=new DbMysqli();
	$sql='select count(*) as count from log_mail '.$where;
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_mail $where order by send_time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['send_time']=date('Y-m-d H:i:s',$row['send_time']);
		$row['receive_time']=$row['receive_time'] ? date('Y-m-d H:i:s',$row['receive_time']) : '';
		$row['delete_time']=$row['delete_time'] ? date('Y-m-d H:i:s',$row['delete_time']) : '';
		$row['accessory']=(array)json_decode(trim($row['accessory']),true);//附件
		$data[]=$row;
	}
	$page=$p->show();
}
$smarty->assign('conditions',$conditions);
$smarty->assign('mail_type_conf',$mail_type_conf);
$smarty->assign('center_domain',CENTER_DOMAIN);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();