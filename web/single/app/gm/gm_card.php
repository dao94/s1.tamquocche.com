<?php
//卡类领取记录
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';

$action_conf=array(
	'general'=>__('常规卡类'),
	'import'=>__('导卡方式'),
);
$action=empty($_GET['action']) ? 'general' : trim($_GET['action']);
$id=empty($_GET['id']) ? '' : floatval(trim($_GET['id']));//角色id
$char_name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));//角色名
$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));
$code=empty($_GET['code']) ? '' : my_escape_string(trim($_GET['code']));
$start_date=empty($_GET['start_date']) ? '' : trim($_GET['start_date']);
$end_date=empty($_GET['end_date']) ? '' : trim($_GET['end_date']);

$condition=array();
$id ? $condition['charId']=$id : '';
$type ? $condition['type']=$type : '';

if($action=='general'){
	$table_name='usedCard';
	$code ? $condition['code']=$code : '';
}elseif($action=='import'){
	$table_name='import_card';
	$code ? $condition['_id']=$code : '';
	$condition['isused']=1;
	$condition['charId']=array('$exists'=>true);
}else{
	exit;
}

$cond=array();
$start_date ? $cond['$gte']=strtotime($start_date) : '';
$end_date ? $cond['$lte']=strtotime($end_date)+86400 : '';
$cond ? $condition['time']=$cond : '';

$mdb=new Mdb();
if($char_name){
	for($i=0;$i<4;$i++){
		$mdb->selectDb(MONGO_PERFIX.$i);
		$char=$mdb->findOne('characters', array('name'=>$char_name), array('_id'));
		if(!empty($char['_id'])){
			$char_id=floatval($char['_id']);
			break;
		}
	}
	$condition['charId']=isset($char_id) ? $char_id : 0;	
}

$mdb->selectDb(MONGO_PERFIX.'5');
$count=$mdb->count($table_name, $condition);
$p=new Page($count,30);
$list=$mdb->find($table_name, $condition, array(), array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array('time'=>-1)));
$data=array();
$type_list=$char_list=array();
foreach ($list as $row){
	if(isset($type_list[$row['type']])){
		$row['type_name']=$type_list[$row['type']];
	}else{
		$mdb->selectDb(MONGO_PERFIX.'5');
		$card=$mdb->findOne('card', array('type'=>intval($row['type'])), array('name'));
		$row['type_name']=$type_list[$row['type']]=$card ? $card['name'] : $row['type'];
	}
	if(isset($char_list[$row['charId']])){
		$row['char_name']=$char_list[$row['charId']];
	}else{
		$mdb->selectDb(MONGO_PERFIX.$row['charId']%4);
		$char=$mdb->findOne('characters', array('_id'=>floatval($row['charId'])), array('name'));
		$row['char_name']=$char_list[$row['charId']]=$char ? $char['name'] : $row['charId'];
	}
	$row['time']=date('Y-m-d H:i:s',$row['time']);
	$row['code']=isset($row['code']) ? $row['code'] : $row['_id'];
	$data[]=$row;
}

$conditions=array(
	'action'=>$action,
	'id'=>$id,
	'name'=>$char_name,
	'type'=>$type,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'code'=>$code,
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->assign('conditions',$conditions);
$smarty->display();