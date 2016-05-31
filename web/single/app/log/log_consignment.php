<?php
//寄售流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'log_config.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$min_jade=empty($_GET['min_jade']) ? '' : intval(trim($_GET['min_jade']));
$max_jade=empty($_GET['max_jade']) ? '' : intval(trim($_GET['max_jade']));
$min_gold=empty($_GET['min_gold']) ? '' : intval(trim($_GET['min_gold']));
$max_gold=empty($_GET['max_gold']) ? '' : intval(trim($_GET['max_gold']));
$item_id=empty($_GET['item_id']) ? '' : floatval($_GET['item_id']);
$money_type=empty($_GET['money_type']) ? '' : intval($_GET['money_type']);
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];
$from_char_name=empty($_GET['from_char_name']) ? '' : trim($_GET['from_char_name']);
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'min_jade'=>$min_jade ? $min_jade : '',
	'max_jade'=>$max_jade ? $max_jade : '',
	'min_gold'=>$min_gold ? $min_gold : '',
	'max_gold'=>$max_gold ? $max_gold : '',
	'item_id'=>$item_id,
	'type'=>$type,
	'money_type'=>$money_type,
	'from_char_name'=>$from_char_name,
	'from'=>$from,
);
$data=array();
$page='';
if($char_id){
	$where=" where char_id=$char_id";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$min_jade ? " and item_id=-2 and item_num>=$min_jade" : '';
	$where.=$max_jade ? " and item_id=-2 and item_num<=$max_jade" : '';
	$where.=$min_gold ? " and item_id=-1 and item_num>=$min_gold" : '';
	$where.=$max_gold ? " and item_id=-1 and item_num<=$max_gold" : '';
	$where.=$item_id ? " and item_id=$item_id" : '';
	$where.=$money_type ? " and money_type=$money_type" : '';
	$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
	//从角色名获取角色id
	$mdb=new Mdb();
	if($from_char_name!=''){
		$char_id='';
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$char=$mdb->findOne('characters', array('name'=>$from_char_name), array('_id'));
			if($char) {$char_id=$char['_id'];break;}
		}
		$where.=$char_id ? " and from_char_id='$char_id' " : " and winner_id='' ";
	}

	$mysqli=new DbMysqli();
	$sql='select count(*) as count from log_consignment '.$where;
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_consignment $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$mdb=new Mdb();
	$char_list=array();
	while($result && $row=$result->fetch_assoc()){
		if(isset($char_list[$row['char_id']])){
			$row['char_name']=$char_list[$row['char_id']];
		}else{
			$mdb->selectDb(MONGO_PERFIX.floatval($row['char_id'])%4);
			$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
			$row['char_name']=$char_list[$row['char_id']]=$char ? $char['name'] : '';
		}
		if(isset($char_list[$row['from_char_id']])){
			$row['from_char_name']=$char_list[$row['from_char_id']];
		}else{
			$mdb->selectDb(MONGO_PERFIX.floatval($row['from_char_id'])%4);
			$char=$mdb->findOne('characters', array('_id'=>floatval($row['from_char_id'])), array('name'));
			$row['from_char_name']=$char_list[$row['from_char_id']]=$char ? $char['name'] : '';
		}
		$row['valid_time']=date('Y-m-d H:i:s',$row['valid_time']);
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}
$type_conf=array(
	1=>__('上架'),
	2=>__('购买'),
	3=>__('过期下架'),
	4=>__('手动下架'),
	5=>__('售出'),
);
$smarty->assign('money_class_conf',$money_class_conf);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();