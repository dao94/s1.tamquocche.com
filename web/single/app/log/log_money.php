<?php
//货币流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'log_config.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';
$money_type=empty($_GET['money_type']) ? '' : intval($_GET['money_type']);
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];
$min_money_num=empty($_GET['min_money_num']) ? '' : intval($_GET['min_money_num']);
$max_money_num=empty($_GET['max_money_num']) ? '' : intval($_GET['max_money_num']);
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'io'=>$io,
	'money_type'=>$money_type,
	'type'=>$type,
	'min_money_num'=>$min_money_num,
	'max_money_num'=>$max_money_num,
	'from'=>$from,
);

$data=array();
$page='';
if($char_id){
	$where=" where char_id=$char_id and money_type not in (5,6)";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
	$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
	$where.=$money_type ? " and money_type=$money_type" : '';
	$where.=$io!=='' ? " and io=$io ": '';
	$where.=$min_money_num ? " and money_num>=$min_money_num ": '';
	$where.=$max_money_num ? " and money_num<=$max_money_num ": '';

	$mysqli=new DbMysqli();
	$sql='select count(*) as count from log_money '.$where;
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select char_name,money_type,io,type,money_num,left_num,time from log_money $where
		order by time desc limit {$p->firstRow},{$p->listRows}";
	$data=$mysqli->find($sql);
	$page=$p->show();
}

$smarty->assign('conditions',$conditions);
$smarty->assign('money_io_conf',$money_io_conf);
$smarty->assign('money_type_conf',$money_type_conf);
$smarty->assign('money_class_conf',$money_class_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();