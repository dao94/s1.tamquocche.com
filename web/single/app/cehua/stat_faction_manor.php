<?php
//领地分析
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__.'Scene.class.php';

$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$start_time=$start_date ? strtotime($start_date) : 0;
$end_time=$end_date ? strtotime($end_date)+86400 : 0;

$where=' where true';
$where.=$start_date ? " and time>=$start_time" : '';
$where.=$end_date ? " and time<$end_time" : '';
$sql="select count(distinct from_unixtime(time,'%Y-%m-%d')) as count from log_faction_manor $where";
$mysqli=new DbMysqli();
$total_rows=$mysqli->count($sql);
$p=new Page($total_rows);
$sql="select distinct from_unixtime(time,'%Y-%m-%d') as date from log_faction_manor $where order by date desc limit {$p->firstRow},{$p->listRows}";
$list=$mysqli->find($sql);
$data=array();
$Scene=new Scene();
if($list){
	$max_date=isset($list[0]['date']) ? $list[0]['date'] : '';
	$min_date=isset($list[count($list)-1]['date']) ? $list[count($list)-1]['date'] : '';
	$max_time=$max_date ? strtotime($max_date)+86400 : 0;
	$min_time=$min_date ? strtotime($min_date) : 0;
	$sub_where=' where true';
	$sub_where.=$min_time ? " and time>=$min_time" : '';
	$sub_where.=$max_time ? " and time<$max_time" : '';
	$sql="select map_id,from_unixtime(time,'%Y-%m-%d') as date,sum(join_player) as player,sum(cost_time) as cost_time,sum(occupy_count) as occupy_count,win_faction_name
		 from log_faction_manor $sub_where group by date,map_id order by date desc";
	$query=$mysqli->query($sql);
	$map_level_conf=array(
		200601=>38,//隆中
		201401=>40,//新野
		200701=>50,//长坂坡
		200801=>60,//赤壁
		//5=>70,
		//6=>80,
	);
	while ($row=$query->fetch_assoc()){
		$this_day=strtotime($row['date']);
		$row['week']=date('N',$this_day);
		//满足人数 N级且当天有登陆的玩家
		$min_level=isset($map_level_conf[$row['map_id']]) ? $map_level_conf[$row['map_id']] : 38; 
		$sql="select count(distinct char_id) as allow_join from log_login where login_time>=$this_day and login_time<($this_day+86400) and level>=$min_level";
		$allow_list=$mysqli->findOne($sql);
		$row['allow_join']=empty($allow_list['allow_join']) ? 0 : $allow_list['allow_join'];
		//参与度
		$row['join_ratio']=$row['allow_join'] ? round($row['player']/$row['allow_join'],4)*100 : 0;
		$row['map_name']=$Scene->getName($row['map_id']);
		$row['min_level']=$min_level;
		$data[$row['date']][]=$row;
	}
}
		
$conditions=array(
	'today'=>date('Y-m-d',strtotime('today')),
	'start_date'=>$start_date,
	'end_date'=>$end_date,
);
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