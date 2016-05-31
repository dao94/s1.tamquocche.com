<?php
//强化部位分析
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'game_config.php';

$today=strtotime('today');
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$type=empty($_GET['type']) ? 'history' : trim($_GET['type']);
$time_type=array(
	'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
	'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
	'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
	'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
	'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
);
$action=empty($_GET['action']) ? 'all' : my_escape_string(trim($_GET['action']));
$type=!array_key_exists($type, $time_type) ? 'history' : $type;
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));

$mysqli=new DbMysqli();
$action_conf=array(
'all'=>__('总'),
'detail'=>__('装备等级和强化'),
);
switch($action){
	case 'all':
		$where=' where true';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(*) as count from stat_strong $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from stat_strong $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['strong1_remark']=json_decode($row['strong1_remark'],true);
			$row['strong2_remark']=json_decode($row['strong2_remark'],true);
			$row['strong3_remark']=json_decode($row['strong3_remark'],true);
			$row['strong4_remark']=json_decode($row['strong4_remark'],true);
			$row['strong5_remark']=json_decode($row['strong5_remark'],true);

			$row['total_strong']=$row['strong1']+$row['strong2']+$row['strong3']+$row['strong4']+$row['strong5'];
			$data[]=$row;
		}
		break;
	case 'detail':
		$part_conf=array(
			1=>__('头盔'),
			2=>__('衣甲'),
			3=>__('护符'),
			4=>__('项圈'),
			5=>__('饰品'),
			6=>__('武器'),
		);
		$where=' where true';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(*) as count from stat_equip_strong $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from stat_equip_strong $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['strong_fan_remark']=json_decode($row['strong_fan_remark'],true);
			$row['strong_ling_remark']=json_decode($row['strong_ling_remark'],true);
			$row['strong_xian_remark']=json_decode($row['strong_xian_remark'],true);
			$row['strong_shen_remark']=json_decode($row['strong_shen_remark'],true);
			$row['strong_level_remark']=json_decode($row['strong_level_remark'],true);
			$row['strong_level1_remark']=isset($row['strong_level_remark'][1]) ? $row['strong_level_remark'][1] : '';//1-10
			$row['strong_level2_remark']=isset($row['strong_level_remark'][2]) ? $row['strong_level_remark'][2] : '';
			$row['strong_level3_remark']=isset($row['strong_level_remark'][3]) ? $row['strong_level_remark'][3] : '';
			$row['strong_level4_remark']=isset($row['strong_level_remark'][4]) ? $row['strong_level_remark'][4] : '';
			$row['strong_level5_remark']=isset($row['strong_level_remark'][5]) ? $row['strong_level_remark'][5] : '';
			$row['strong_level6_remark']=isset($row['strong_level_remark'][6]) ? $row['strong_level_remark'][6] : '';
			$row['strong_level7_remark']=isset($row['strong_level_remark'][7]) ? $row['strong_level_remark'][7] : '';
			$data[]=$row;
		}
		break;
}

$conditions=array(
	'type'=>$type,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'action'=>$action,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$smarty->assign('action_conf',$action_conf);
$smarty->assign('part_conf',$part_conf);
$smarty->assign('time_type',$time_type);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();
