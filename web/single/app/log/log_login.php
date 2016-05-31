<?php
//登录流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Ip.class.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$ip=empty($_GET['ip']) ? '' : my_escape_string(trim($_GET['ip']));
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
	'ip'=>$ip,
);

$data=array();
$page='';
if($char_id){
	$where=" where char_id=$char_id";
	$where.=$ip ? " and ip='$ip'" : '';
	$where.=$start_date ? ' and login_time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and login_time<'.(strtotime($end_date)+86400) : '';

	$mysqli=new DbMysqli();
	$sql='select count(*) as count from log_login '.$where;
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_login $where order by login_time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$ip=new IP();
	while($result && $row=$result->fetch_assoc()){
		$online_time=$row['logout_time'] ? $row['logout_time']-$row['login_time'] : 0;
		$row['online_length']=$online_time ? intval($online_time/3600).__('小时').intval($online_time%3600/60).__('分') : '';
		$row['login_time']=date('Y-m-d H:i:s',$row['login_time']);
		$row['logout_time']=$row['logout_time'] ? date('Y-m-d H:i:s',$row['logout_time']) : '';
		$location=$ip->getlocation($row['ip']);
		$row['country']=$location['country'];
		$data[]=$row;
	}
	$page=$p->show();
}

$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();