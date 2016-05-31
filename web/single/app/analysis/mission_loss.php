<?php

//任务流失
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__.'Mission.class.php';

$mission_type_conf=array(
1=>__('主线'),
2=>__('支线'),
);
$date=empty($_GET['date']) ? date('Y-m-d',strtotime('yesterday')) : my_escape_string(trim($_GET['date']));
$name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));
$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));
$type=array_key_exists($type, $mission_type_conf) ? $type : 1;
$open_date=date('Y-m-d',SERVER_OPEN_TIME);
$conditions=array(
	'date'=>$date,
	'name'=>$name,
	'type'=>$type,
	'open_date'=>$open_date,
);
$where=" where date='$date' and type=$type";
$where.=$name ? " and name='$name'" : '';
$mysqli=new DbMysqli();
$sql="select count(*) as count from stat_mission_loss $where";
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows,30);
$sql="select * from stat_mission_loss $where order by num asc,mid asc limit {$p->firstRow},{$p->listRows}";
$result=$mysqli->query($sql);
$data=array();
$mission=new Mission();
//特殊处理的任务id
$special_mission=array('MAIN_1001_11','MAIN_1001_21','MAIN_1001_31','MAIN_1001_41','MAIN_1002_11','MAIN_1002_21',
	'MAIN_1002_31','MAIN_1002_41','MAIN_1003_11','MAIN_1003_21','MAIN_1003_31','MAIN_1003_41');
while ($result && $row=$result->fetch_assoc()){
	//人数接收率=任务接收数/上一个任务完成数
	if(in_array($row['mid'],$special_mission)){
		//特殊处理新手任务
		$num=substr($row['mid'],5,4);
		$mid=substr_replace($row['mid'],$num-1,5,4);
		$sql="select complete from stat_mission_loss $where and mid='$mid' limit 1";
	}elseif($row['mid']=='MAIN_1004'){
		$sql="select sum(complete) as complete from stat_mission_loss $where and mid like 'MAIN_1003_%'";
	}else{
		$sql="select complete from stat_mission_loss $where and num={$row['num']}-1";
	}
	$list=$mysqli->findOne($sql);
	$previous_complete=$list ? intval($list['complete']) : 0;
	$row['name']=$mission->getName($row['mid']);
	$row['receive_ratio']=$previous_complete ? round($row['receive']/$previous_complete,4)*100 : 0;
	$row['complete_ratio']=$row['receive'] ? round($row['complete']/$row['receive'],4)*100 : 0;
	$row['loss']=$row['receive_loss']+$row['allow_complete_loss'];
	$row['loss_ratio']=$row['receive'] ? round($row['loss']/$row['receive'],4)*100 : 0;
	$avg_use_time=$row['complete'] ? intval($row['sum_use_time']/$row['complete']) : 0;
	$row['avg_hour']=str_pad(intval($avg_use_time/3600),2,'0',STR_PAD_LEFT);
	$row['avg_minute']=str_pad(intval($avg_use_time%3600/60), 2,'0',STR_PAD_LEFT);
	$row['avg_second']=str_pad(intval($avg_use_time%3600%60), 2,'0',STR_PAD_LEFT);
	$data[]=$row;
}
$time_conf=array(
$open_date=>__('开服当天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*2)=>__('开服3天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*6)=>__('开服7天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*13)=>__('开服2周'),
date('Y-m-d',SERVER_OPEN_TIME+86400*29)=>__('开服30天'),
date('Y-m-d',strtotime('yesterday'))=>__('昨天'),
);
$smarty->assign('page',$p->show());
$smarty->assign('conditions',$conditions);
$smarty->assign('mission_type_conf',$mission_type_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('data',$data);
$smarty->display();