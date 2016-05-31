<?php

//玩家封禁
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__ .'auth.php';
include __CONFIG__.'game_config.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Character.class.php';
include __CLASSES__.'Forbid.class.php';
include __CLASSES__.'Ip.class.php';

$ip=empty($_GET['ip']) ? '' : my_escape_string(trim($_GET['ip']));
$start_date=empty($_GET['start_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['end_date']));
$status=empty($_GET['status']) ? '' : intval($_GET['status']);
$conditions=array(
	'ip'=>$ip,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'status'=>$status,
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
);

$mysqli=new DbMysqli();
$where=' where login_time>='.strtotime($start_date).' and login_time<'.(strtotime($end_date)+86400);
$data=array();
if($ip){
	//查询封禁玩家
	$sql="select distinct char_id from gm_forbid where type=2 and status=1 and end_time>=".time();
	$result=$mysqli->query($sql);
	$forbid_char=array();
	while ($result && $row=$result->fetch_assoc()){
		$forbid_char[]=floatval($row['char_id']);
	}
	$forbid_char_str=$forbid_char ? implode(',', $forbid_char) : '';
	$where.=" and ip='$ip'";
	$where.=$status==1 && $forbid_char_str ? " and char_id in ($forbid_char_str)" : '';
	$where.=$status==2 && $forbid_char_str ? " and char_id not in ($forbid_char_str)" : '';
		
	$sql="select count(distinct char_id) as count from log_login $where";
	$total_row=$mysqli->count($sql);
	$p=new Page($total_row,30);
	$sql="select char_id,max(login_time) as login_time from log_login $where group by char_id order by login_time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$character=new Character();
	while ($result && $row=$result->fetch_assoc()){
		$char_id=floatval($row['char_id']);
		$info=(array)$character->getCharacterById($char_id);
		if($info){
			$row['name']=$info['name'];
			$row['level']=$info['level'];
			$row['account']=$info['account'];
			$row['sid']=$info['serverId'];
			$row['creat_time']=date('Y-m-d H:i:s',$info['creat_time']);
			$row['login_time']=date('Y-m-d H:i:s',$row['login_time']);
		}
		//是否已经封禁
		$sql="select count(*) as count from gm_forbid where char_id=$char_id and type=2 and status=1 and end_time>".time();
		$count=$mysqli->count($sql);
		$row['status']=$count>0 ? 1 : 2;
		$data[]=$row;
	}
	unset($forbid_char);
}else{
	//封禁ip列表
	$sql="select distinct ip from gm_forbid where type=3 and status=1 and end_time>=".time();
	$result=$mysqli->query($sql);
	$forbid_ip=array();
	while ($result && $row=$result->fetch_assoc()){
		$forbid_ip[]=$row['ip'];
	}
	$forbid_ip_str=$forbid_ip ? "'".implode("','", $forbid_ip)."'" : '';
	$where.=$status==1 && $forbid_ip_str ? " and ip in ($forbid_ip_str)" : '';
	$where.=$status==2 && $forbid_ip_str ? " and ip not in ($forbid_ip_str)" : '';
	$sql="select count(distinct ip) as count from log_login $where";
	$total_row=$mysqli->count($sql);
	$p=new Page($total_row,30);
	$sql="select ip,count(distinct char_id) as count from log_login $where group by ip order by count desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$ip=new Ip();
	while ($result && $row=$result->fetch_assoc()){
		$location=$ip->getlocation($row['ip']);
		$row['country']=$location['country'];
		$row['status']=in_array($row['ip'], $forbid_ip) ? 1 : 2;
		$data[]=$row;
	}
	unset($forbid_ip);
}
$page=$p->show();
$status_conf=array(1=>__('已封禁'),2=>__('未封禁'));
$smarty->assign('status_conf',$status_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('page',$page);
$smarty->assign('data',$data);
$smarty->display();
