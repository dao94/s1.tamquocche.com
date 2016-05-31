<?php
//英雄殿
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

$action_conf=array(
	'kill'=>__('击杀分析'),
	'call'=>__('召唤分析'),
);
$action=empty($_GET['action']) ? 'kill' : trim($_GET['action']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$start_time=$start_date ? strtotime($start_date) : 0;
$end_time=$end_date ? strtotime($end_date)+86400 : 0;

$where='where true';
$start_time&&$where.=" and time>=$start_time";
$end_time&&$where.=" and time<$end_time";
$mysqli=new DbMysqli();
$sql="select distinct from_unixtime(time,'%Y-%m-%d') as date from log_hero_call $where order by date desc";
$query=$mysqli->query($sql);
$list=array();
$total_rows=0;
while ($row=$query->fetch_assoc()){
	$list[]=$row['date'];
	$total_rows++;
}
$p=new Page($total_rows);
$date_list=array_slice($list, $p->firstRow,$p->listRows);
$date_list&&$max_time=strtotime($date_list[0])+86400;
$date_list&&$min_time=strtotime($date_list[count($date_list)-1]);
$data=array();
switch ($action){
	case 'kill':
		$boss_list=array();
		if(isset($min_time)&&isset($max_time)){
			$sql="select from_unixtime(time,'%Y-%m-%d') as date,boss_id,boss_name,count(*) as count from log_hero_call where time>=$min_time and time<$max_time group by date,boss_id";
			$query=$mysqli->query($sql);
			while ($row=$query->fetch_assoc()){
				if(!array_key_exists($row['boss_id'], $boss_list)){
					$boss_list[$row['boss_id']]=$row['boss_name'];
				}
				
				$this_day_start=strtotime($row['date']);
				$sql="select count(*) as kill_count,min(cost_time) as min_cost_time,max(cost_time) as max_cost_time,avg(cost_time) as avg_cost_time from log_hero_boss 
					where time>=$this_day_start and time<$this_day_start+86400 and boss_id={$row['boss_id']}";
				$list=$mysqli->findOne($sql);
				$list&&$data[$row['date']][$row['boss_id']]=$list;//boss击杀分析
				$data[$row['date']][$row['boss_id']]['call_count']=$row['count'];//召唤次数
				$data[$row['date']][$row['boss_id']]['boss_name']=$row['boss_name'];//boss名称
				
				//参与人数
				$sql="select char_list from log_hero_boss where time>=$this_day_start and time<($this_day_start+86400) and boss_id={$row['boss_id']}";
				$char_query=$mysqli->query($sql);
				$char_list=array();
				while ($item=$char_query->fetch_assoc()){
					$arr=(array)json_decode($item['char_list'],true);
					$char_list=array_unique(array_merge($char_list,$arr));
				}
				$data[$row['date']][$row['boss_id']]['player']=count($char_list);//参与人数
			}
		}
		$smarty->assign('boss_list',$boss_list);
		break;
		
	case 'call':
		$count_list=$cost_money=array();
		if(isset($min_time)&&isset($max_time)){
			//玩家招呼次数
			$sql="select from_unixtime(time,'%Y-%m-%d') as date,count(*) as count from log_hero_call where time>=$min_time and time<$max_time group by date,char_id";
			$query=$mysqli->query($sql);
			while ($row=$query->fetch_assoc()){
				if(!in_array($row['count'],$count_list)){
					$count_list[]=$row['count'];
				}
				if(!array_key_exists($row['date'], $cost_money)){
					//总消耗元宝
					$this_day_start=strtotime($row['date']);
					$sql="select sum(money_num) as money_num from log_money where time>=$this_day_start and time<($this_day_start+86400) and money_type=3 and type=90 and io=0";
					$list=$mysqli->findOne($sql);
					$cost_money[$row['date']]=$list ? $list['money_num'] : 0;
				}
				$data[$row['date']]['money']=$cost_money[$row['date']];//总消耗元宝
				isset($data[$row['date']]['player']) ? $data[$row['date']]['player']++ : $data[$row['date']]['player']=1;//总召唤人数
				isset($data[$row['date']]['call'][$row['count']]) ? $data[$row['date']]['call'][$row['count']]++ : $data[$row['date']]['call'][$row['count']]=1;//召唤人数
			}
		}
		sort($count_list);
		$smarty->assign('count_list',$count_list);
		break;
}
$conditions=array(
	'action'=>$action,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME)
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
$smarty->assign('page',$p->show());
$smarty->display();
