<?php
//关卡流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'game_config.php';
include __CLASSES__.'PetCard.class.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
);

$data=array();
$page='';
$where='';
if($char_id !=''){
	$petCard=new PetCard();
	$where=" where char_id=$char_id ";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from log_pass_card $where";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_pass_card $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$name_id=$row['title'];
		$row['name']=$petCard->getName($name_id);
		$row['reward']=(array)json_decode($row['reward'],true);

		foreach ($row['reward'] as $key=>$items){
			switch ($key){
				case 'modelList':
					break;
				case 'itemList':
					foreach($items as $k=>$item){
						$row[$key][$k]['itemId']=$item['itemId'];
						$row[$key][$k]['number']=$item['number'];
					}
					break;
				case 'petExp':
					$row[$key]=$items;
					break;
			}
		}
		unset($row['reward']);
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();
}

$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();