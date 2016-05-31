<?php
//战斗力分布
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

$action_conf=array(
	'list'=>__('列表'),
	'pie_chart'=>__('饼状图'),
);
$action=empty($_GET['action']) ? 'list' : trim($_GET['action']);
$day_conf=array(0,2,3,7);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$date=empty($_GET['date']) ? '' : my_escape_string(trim($_GET['date']));
$min_level=empty($_GET['min_level']) ? '' : intval(trim($_GET['min_level']));
$max_level=empty($_GET['max_level']) ? '' : intval(trim($_GET['max_level']));
$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));
$day=empty($_GET['day']) ? 0 : intval(trim($_GET['day']));
$day=in_array($day, $day_conf) ? $day : 0;
$conditions=array(
	'action'=>$action,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'type'=>$type,
	'day'=>$day,
	'min_level'=>$min_level,
	'max_level'=>$max_level,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
);
$where=" where day=$day";
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';
$where.=$type ? " and type=$type" : '';
$where.=$min_level ? " and level>=$min_level" : '';
$where.=$min_level ? " and level<=$max_level" : '';
$mysqli=new DbMysqli();
$data=array();
switch ($action){
	case 'list':
	default:
		$sql="select count(distinct date) as count from stat_fight $where";
		$total_rows=$mysqli->count($sql);
		//查询最高等级
		$sql="select max(level) as level from stat_fight $where";
		$list=$mysqli->findOne($sql);
		$max_level=intval($list['level']);
		$level_conf=array();
		for ($i=30;$i<$max_level;$i+=5){
			$level_conf[intval($i/5)]['title']=array($i,$i+4);
			for ($j=$i;$j<$i+5;$j++){
				$level_conf[intval($i/5)]['list'][]=$j;
			}
		}
		$p=new Page($total_rows);
		$sql="select distinct date from stat_fight $where order by date desc limit {$p->firstRow},{$p->listRows};";
		$result=$mysqli->query($sql);
		$date_list=array();
		while ($result && $row=$result->fetch_assoc()){
			$date_list[]=$row['date'];
		}
		$date_str=$date_list ? "'".implode("','", $date_list)."'" : '';
		$sql="select * from stat_fight $where and date in ($date_str) order by date desc,level asc";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$key=intval($row['level']/5);
			//等级段人数
			isset($data[$row['date']]['item'][$key]['count']) ? $data[$row['date']]['item'][$key]['count']+=$row['count'] : $data[$row['date']]['item'][$key]['count']=$row['count'];
			//等级段总战斗力
			isset($data[$row['date']]['item'][$key]['fight_sum']) ? $data[$row['date']]['item'][$key]['fight_sum']+=$row['fight_sum'] : $data[$row['date']]['item'][$key]['fight_sum']=$row['fight_sum'];
			//等级段平均战斗力
			$data[$row['date']]['item'][$key]['fight_avg']=empty($data[$row['date']]['item'][$key]['count']) ? 0
			: intval($data[$row['date']]['item'][$key]['fight_sum']/$data[$row['date']]['item'][$key]['count']);
			//等级段最高战斗力
			if((isset($data[$row['date']]['item'][$key]['fight_max']) && $row['fight_max']>$data[$row['date']]['item'][$key]['fight_max'])
			|| !isset($data[$row['date']]['item'][$key]['fight_max']))
			$data[$row['date']]['item'][$key]['fight_max']=$row['fight_max'];
			$data[$row['date']]['week']=date('N',strtotime($row['date']));
			$data[$row['date']]['open_day']=intval((strtotime($row['date'])-SERVER_OPEN_TIME)/86400)+1;
			//详细等级最高战斗力
			if((isset($data[$row['date']]['list'][$key][$row['level']]['fight_max']) && $row['fight_max']>$data[$row['date']]['list'][$key][$row['level']]['fight_max'])
			|| !isset($data[$row['date']]['list'][$key][$row['level']]['fight_max']))
			$data[$row['date']]['list'][$key][$row['level']]['fight_max']=$row['fight_max'];
			isset($data[$row['date']]['list'][$key][$row['level']]['fight_sum']) ? $data[$row['date']]['list'][$key][$row['level']]['fight_sum']+=$row['fight_sum']
			: $data[$row['date']]['list'][$key][$row['level']]['fight_sum']=$row['fight_sum'];
			isset($data[$row['date']]['list'][$key][$row['level']]['count']) ? $data[$row['date']]['list'][$key][$row['level']]['count']+=$row['count']
			: $data[$row['date']]['list'][$key][$row['level']]['count']=$row['count'];
		}
		$smarty->assign('page',$p->show());
		$smarty->assign('level_conf',$level_conf);
		break;
			
	case 'pie_chart':
		$date=$date ? $date : date('Y-m-d',strtotime('yesterday'));
		$sql="select remark from stat_fight $where and date='$date' and remark!=''";
		$result=$mysqli->query($sql);
		$total_count=0;
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			foreach ($remark as $key=>$count){
				isset($data[$key]) ? $data[$key]+=$count : $data[$key]=$count;
				$total_count+=$count;
			}
		}
		arsort($data);
		$smarty->assign('total_count',$total_count);
		$conditions['date']=$date;
		break;
}

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
$type_conf=array(1=>__('RMB玩家'),2=>__('非RMB玩家'));
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('day_conf',$day_conf);
$smarty->assign('data',$data);
$smarty->display();