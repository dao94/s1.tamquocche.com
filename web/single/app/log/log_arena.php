<?php
//竞技场流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$status=isset($_GET['status']) && $_GET['status']!=='' ? intval($_GET['status']) : '';
$fight_char_name=empty($_GET['fight_char_name']) ? '' : my_escape_string(trim($_GET['fight_char_name']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'status'=>$status,
	'fight_char_name'=>$fight_char_name,
	'from'=>$from,
);


$data=array();
$page='';
if($char_id){
	$where=" where (winner_id=$char_id or loser_id=$char_id)";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	//从角色名获取角色id
	$mdb=new Mdb();
	if($fight_char_name!=''){
		$char_id='';
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$char=$mdb->findOne('characters', array('name'=>$fight_char_name), array('_id'));
			if($char) {$char_id=$char['_id'];break;}
		}
		$where.=$char_id ? " and (winner_id='$char_id' or loser_id='$char_id')" : " and winner_id='' ";
	}
	$where.=$status!=='' ? " and status=$status" :'';
	$mysqli=new DbMysqli();
	$sql='select count(*) as count from log_arena '.$where;
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_arena $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$char_list=array();
	while($result && $row=$result->fetch_assoc()){
		if(isset($char_list[$row['winner_id']])){
			$row['winner_name']=$char_list[$row['winner_id']];
		}else{
			$mdb->selectDb(MONGO_PERFIX.(floatval($row['winner_id'])%4));
			$char=$mdb->findOne('characters', array('_id'=>floatval($row['winner_id'])), array('name'));
			$row['winner_name']=$char ? $char['name'] : '';
			$char_list[$row['winner_id']]=$row['winner_name'];
		}
		if(isset($char_list[$row['loser_id']])){
			$row['loser_name']=$char_list[$row['loser_id']];
		}else{
			$mdb->selectDb(MONGO_PERFIX.(floatval($row['loser_id'])%4));
			$char=$mdb->findOne('characters', array('_id'=>floatval($row['loser_id'])), array('name'));
			$row['loser_name']=$char ? $char['name'] : '';
			$char_list[$row['loser_id']]=$row['loser_name'];
		}
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$status_conf=array(
	0=>__('打平'),
	1=>__('胜负'),
	2=>__('逃跑'),
);
$smarty->assign('status_conf',$status_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();