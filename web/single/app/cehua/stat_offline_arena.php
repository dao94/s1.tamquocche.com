<?php
//武斗场分析
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

$action_conf=array(
	'stat'=>__('统计数据'),
	'rank'=>__('排行榜'),
);
$action=empty($_GET['action']) ? 'stat' : trim($_GET['action']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$start_time=$start_date ? strtotime($start_date) : 0;
$end_time=$end_date ? strtotime($end_date)+86400 : 0;
$data=array();
switch ($action){
	case 'stat':
		$where=' where true';
		$where.=$start_date ? " and end_time>=$start_time" : '';
		$where.=$end_date ? " and end_time<$end_time" : '';
		$sql="select count(distinct from_unixtime(end_time,'%Y%m%d')) as count from log_offline_arena $where";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct from_unixtime(end_time,'%Y-%m-%d') as date from log_offline_arena $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$list=$mysqli->find($sql);
		if($list){
			$max_date=isset($list[0]['date']) ? $list[0]['date'] : '';
			$min_date=isset($list[count($list)-1]['date']) ? $list[count($list)-1]['date'] : '';
			$max_time=$max_date ? strtotime($max_date)+86400 : 0;
			$min_time=$min_date ? strtotime($min_date) : 0;
			$sub_where=' where true';
			$sub_where.=$min_time ? " and end_time>=$min_time" : '';
			$sub_where.=$max_time ? " and end_time<$max_time" : '';
			$sql="select from_unixtime(end_time,'%Y-%m-%d') as date,count(distinct char_id) as player,count(*) as count from log_offline_arena $sub_where group by date order by date desc";
			$query=$mysqli->query($sql);
			while ($row=$query->fetch_assoc()){
				$this_day=strtotime($row['date']);
				$row['week']=date('N',$this_day);
				//满足人数 38级且当天有登陆的玩家
				$sql="select count(distinct char_id) as allow_join from log_login where login_time>=$this_day and login_time<($this_day+86400) and level>=38";
				$allow_list=$mysqli->findOne($sql);
				$row['allow_join']=empty($allow_list['allow_join']) ? 0 : $allow_list['allow_join'];
				//参与度
				$row['join_ratio']=$row['allow_join'] ? round($row['player']/$row['allow_join'],4)*100 : 0;
				$row['avg_count']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
				//元宝购买武斗次数 type=96
				$sql="select count(*) as buy_count from log_money where time>=$this_day and time<($this_day+86400) and io=0 and type=96 and money_type=3";
				$buy_list=$mysqli->findOne($sql);
				$row['buy_count']=empty($buy_list['buy_count']) ? 0 : $buy_list['buy_count'];
				$data[]=$row;
			}
		}
		$smarty->assign('page',$p->show());	
		break;
	case 'rank':
		$where=' where true';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(*) as count from stat_offline_arena_rank $where";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_offline_arena_rank $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['data']=json_decode($row['data'],true);
			$data[]=$row;
		}
		$smarty->assign('page',$p->show());
		break;
}
$conditions=array(
	'action'=>$action,
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
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->display();