<?php
//觐见流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';

$action_conf=array(
'audience'=>__('觐见'),
'sign'=>__('兵阁符'),
);

$action=empty($_GET['action']) ? 'audience' : trim($_GET['action']);
$from=empty($_GET['from']) ? 'info' : trim($_GET['from']);
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';
$colour=isset($_GET['colour']) && $_GET['colour']!=='' ? intval($_GET['colour']) : '';
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];  //type类型
$sign_name=empty($_GET['sign_name']) ? '' : trim($_GET['sign_name']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));


$conditions=array(
'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
'char_id'=>$char_id,
'action'=>$action,
'from'=>$from,
'type'=>$type,
'io'=>$io,
'colour'=>$colour,
'sign_name'=>$sign_name,
'start_date'=>$start_date,
'end_date'=>$end_date,
);

$colour_conf=array(
0=>__('无'),
1=>__('绿'),
2=>__('蓝'),
3=>__('紫'),
4=>__('橙'),
);

$data=array();
$page='';
switch($action){
	case 'audience':
		//觐见
		$io_conf=array(
		1=>__('进背包'),
		2=>__('出背包'),
		);
		$type_conf=array(
		1=>__('觐见'),
		2=>__('召见'),
		3=>__('拾取'),
		4=>__('出售'),
		);
		$where="where char_id='$char_id' ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$sign_name ? " and sign_name='$sign_name' " : '';
		$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
		$where.=$io ? " and io=$io " : '';
		$where.=$colour!=='' ? " and quality=$colour " : '';
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from log_general_audience $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_general_audience $where order by time desc, case io when 1 then remain  end desc,case io when 2 then  remain end asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$char_list=array();
		while($result && $row=$result->fetch_assoc()){
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 'sign':
		//兵阁符
		$io_conf=array(
		1=>__('进背包'),
		2=>__('出背包'),
		3=>__('更新背包'),
		);
		$type_conf=array(
		1=>__('穿戴兵符'),
		2=>__('脱下兵符'),
		3=>__('交情兑换'),
		4=>__('普通吞噬'),
		5=>__('一键吞噬'),
		);
		$where="where char_id='$char_id' ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$sign_name ? " and sign_name='$sign_name' " : '';
		$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
		$where.=$io ? " and io=$io " : '';
		$where.=$colour!=='' ? " and quality=$colour " : '';
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from log_general_sign $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_general_sign $where order by time desc,remain asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$char_list=array();
		while($result && $row=$result->fetch_assoc()){
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		break;

}
$smarty->assign('colour_conf',$colour_conf);
$smarty->assign('io_conf',$io_conf);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();