<?php
//羽翼分析
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'game_config.php';

$action_conf=array(
'up'=>__('羽翼信息'),
'skill'=>__('羽翼技能'),
);

$action=empty($_GET['action']) ? 'up' : trim($_GET['action']);
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
$type=!array_key_exists($type, $time_type) ? 'history' : $type;
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));


$data=array();
$mysqli=new DbMysqli();

switch($action){
	case 'up':
		//羽翼信息
		$where=" where true ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_wing $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_wing $where order by date desc limit {$p->firstRow},{$p->listRows} ";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['up_remark']=json_decode($row['up_remark'],true);
			$row['skill_remark']=json_decode($row['skill_remark'],true);
			$data[]=$row;
		}
		break;
	case 'skill':
		//羽翼技能
		$where=" where true ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_wing $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_wing $where order by date desc limit {$p->firstRow},{$p->listRows} ";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['up_remark']=json_decode($row['up_remark'],true);
			$row['skill_remark']=json_decode($row['skill_remark'],true);
			$data[]=$row;
		}
		break;
}
$page=$p->show();
$conditions=array(
	'action'=>$action,
	'type'=>$type,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$smarty->assign('time_type',$time_type);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('part_conf',$part_conf);
$smarty->assign('page',$p->show());
$smarty->display();
?>
