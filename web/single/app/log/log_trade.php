<?php
//交易流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'log_config.php';
include __CLASSES__.'Item.class.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$trade_char_name=empty($_GET['trade_char_name']) ? '' : my_escape_string(trim($_GET['trade_char_name']));
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$min_jade=empty($_GET['min_jade']) ? '' : intval(trim($_GET['min_jade']));
$max_jade=empty($_GET['max_jade']) ? '' : intval(trim($_GET['max_jade']));
$min_gold=empty($_GET['min_gold']) ? '' : intval(trim($_GET['min_gold']));
$max_gold=empty($_GET['max_gold']) ? '' : intval(trim($_GET['max_gold']));
$item_id=empty($_GET['item_id']) ? '' : floatval($_GET['item_id']);
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));//按钮
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'trade_char_name'=>$trade_char_name,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'min_jade'=>$min_jade ? $min_jade : '',
	'max_jade'=>$max_jade ? $max_jade : '',
	'min_gold'=>$min_gold ? $min_gold : '',
	'max_gold'=>$max_gold ? $max_gold : '',
	'item_id'=>$item_id,
	'from'=>$from,
	'type'=>$type,
);
$data=array();
$page='';
if($char_id){
	$Item=new Item();
	$where=" where (ask_char_id=$char_id or answer_char_id=$char_id)";
	$where.=$trade_char_name ? " and (ask_char_name='$trade_char_name' or answer_char_name='$trade_char_name')" :'';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';

	$where.=$min_jade ? " and ((ask_jade>=$min_jade" : ' and ((true';
	$where.=$max_jade ? " and ask_jade<=$max_jade)" : ')';
	$where.=$min_jade ? " or (answer_jade>=$min_jade" : ' or (true';
	$where.=$max_jade ? " and answer_jade<=$max_jade))" : '))';

	$where.=$min_gold ? " and ((ask_gold>=$min_gold" : ' and ((true';
	$where.=$max_gold ? " and ask_gold<=$max_gold)" : ')';
	$where.=$min_gold ? " or (answer_gold>=$min_gold" : ' or (true';
	$where.=$max_gold ? " and answer_gold<=$max_gold))" : '))';
	$where.=$item_id ? " and (ask_item like '%{$item_id},%' or answer_item like '%{$item_id},%')" : '';
	//按钮
	if($type==1){
		//元宝
		$where.=" and (ask_jade >0 or answer_jade > 0)";
	}else if($type==2){
		//铜币
		$where.=" and (ask_gold >0 or answer_gold > 0)";
	}

	$mysqli=new DbMysqli();
	$sql='select count(*) as count from log_trade '.$where;
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_trade $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['ask_item']=json_decode($row['ask_item'],true);
		$row['answer_item']=json_decode($row['answer_item'],true);
		if($type==3){//珍贵  包括橙装以上的
			$k=1;
			if($row['ask_item']!=''){
				$ask_item=$row['ask_item'];
				foreach($ask_item as $item){
					$item_id=$item[0];
					$colour=(int)$Item->getColour($item_id);
					$level=(int)$Item->getLevel($item_id);
					if($colour>=4 && $level>=30)  $k=4 ;//存在 包括橙装以上的
				}
			}
			if($row['answer_item']!=''){
				$answer_item=$row['answer_item'];
				foreach ($answer_item as $item){
					$item_id=$item[0];
					$colour=(int)$Item->getColour($item_id);
					$level=(int)$Item->getLevel($item_id);
					if($colour>=4 && $level>=30)  $k=4 ;//存在 包括橙装以上的
				}
			}


			//包括橙装以上的
			if($k!=4) continue;
		}

		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	//页数做特别处理
	if($type==3){
		$p=new Page(count($data));
	    $list = array();
		$list=array_splice($data,$p->firstRow,$p->listRows);
		foreach ($list as $key=>$row){
			$data[]=$row;
		}
	}
	$page=$p->show();
}

$type_conf=array(
1=>__('元宝交易记录'),
2=>__('铜币交易记录'),
3=>__('珍贵道具交易记录'),
);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();