<?php
//道具流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'log_config.php';
include __CONFIG__.'game_config.php';
include __CONFIG__.'attr_config.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$uuid=empty($_GET['uuid']) ? '' : trim($_GET['uuid']);
$item_id=empty($_GET['item_id']) ? '' : floatval($_GET['item_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';
$money_type=empty($_GET['money_type']) ? '' : intval($_GET['money_type']);
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'item_id'=>$item_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'io'=>$io,
	'money_type'=>$money_type,
	'type'=>$type,
	'from'=>$from,
	'uuid'=>$uuid,
);

$data=array();
$page='';
$where='';
if($char_id !='' && $uuid==''){
	$where=" where char_id=$char_id";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
	$where.=$item_id ? " and item_id=$item_id" : '';
	$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
	$where.=$money_type ? " and money_type=$money_type" : '';
	$where.=$io!=='' ? " and io=$io ": '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from log_items $where";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_items $where order by time desc, case io when 0 then left_num end asc,case io when 1 then left_num end desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['daoju']=0;
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}else if($char_id !='' && $uuid!=''){
	$table='log_items';
	$where=" where uuid='$uuid' ";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$item_id ? " and item_id=$item_id" : '';
	$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
	$where.=$money_type ? " and money_type=$money_type" : '';
	$where.=$io!=='' ? " and io=$io ": '';

	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc, case io when 0 then left_num end asc,case io when 1 then left_num end desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['daoju']=1;
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$smarty->assign('is_restore',isset($_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['log']['childs']['log_restore.php']) ? true :false);
$smarty->assign('conditions',$conditions);
$smarty->assign('item_io_conf',$item_io_conf);
$smarty->assign('item_type_conf',$item_type_conf);
$smarty->assign('bag_conf',$bag_conf);
$smarty->assign('center_domain',CENTER_DOMAIN);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();