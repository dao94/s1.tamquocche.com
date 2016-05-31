<?php

//觐见分析
define('__ROOT__',str_replace(array('//','\\'),array('/','/'),dirname(dirname(__DIR__) )));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';

$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$where=' where  type in (79,80,81,82,83) and money_type in (1,2)';
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';
$sql="select count(*) as count from stat_money $where ";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select * from stat_money $where  order by date  desc limit {$p->firstRow},{$p->listRows}";

$result=$mysqli->query($sql);
$data=array();
while ($result && $row=$result->fetch_assoc()){
	$row['week']=date('N',strtotime($row['date']));
	if($row['type']==79){
		if($row['money_type']==1){
			$row['whiteJLJB']=$row['money_num'] ? $row['money_num'] : 0;
		}else if($row['money_type']==2){
			$row['whiteJLJQ']=$row['money_num'] ? $row['money_num'] : 0;
		}

	}else if($row['type']==80){
		if($row['money_type']==1){
			$row['greenJLJB']=$row['money_num'] ? $row['money_num'] : 0;
		}else if($row['money_type']==2){
			$row['greenJLJQ']=$row['money_num'] ? $row['money_num'] : 0;
		}

	}else if($row['type']==81){
		if($row['money_type']==1){
			$row['blueJLJB']=$row['money_num'] ? $row['money_num'] : 0;
		}else if($row['money_type']==2){
			$row['blueJLJQ']=$row['money_num'] ? $row['money_num'] : 0;
		}


	}else if($row['type']==82){
		if($row['money_type']==1){
			$row['purpleJLJB']=$row['money_num'] ? $row['money_num'] : 0;
		}else if($row['money_type']==2){
			$row['purpleJLJQ']=$row['money_num'] ? $row['money_num'] : 0;
		}


	}else if($row['type']==83){
		if($row['money_type']==1){
			$row['orangeJLJB']=$row['money_num'] ? $row['money_num'] : 0;
		}else if($row['money_type']==2){
			$row['orangeJLJQ']=$row['money_num'] ? $row['money_num'] : 0;
		}


	}
	$data[]=$row;
}

$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$time_conf=array(
date('Y-m-d',SERVER_OPEN_TIME)=>__('开服当天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*1)=>__('开服2天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*2)=>__('开服3天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*3)=>__('开服4天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*4)=>__('开服5天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*5)=>__('开服6天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*6)=>__('开服7天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*13)=>__('开服14天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*29)=>__('开服30天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*59)=>__('开服60天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*89)=>__('开服90天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*179)=>__('开服180天'),
date('Y-m-d',strtotime('yesterday'))=>__('昨天'),
);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();