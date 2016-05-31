<?php
//副本分析
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'log_config.php';

$action_conf=array(
304000=>__('乾坤八卦'),
600100=>__('兵临城下'),
700100=>__('草船借箭'),
800100=>__('过关斩将'),
900100=>__('伏魔八卦阵'),
330100=>__('竞技场'),
900300=>__('洛阳攻防战'),
500100=>__('十面埋伏'),
560100=>__('蜀道试炼'),
630100=>__('海底总动员'),
205500=>__('七擒孟获'),
510100=>__('凤雏秘境'),
);
$action=empty($_GET['action']) ? 304000 : trim($_GET['action']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$type=isset($_GET['type']) ? trim($_GET['type']) : '';
$conditions=array(
	'action'=>$action,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'today'=>date('Y-m-d',strtotime('today')),
);
$level_conf=array(
	0=>__('通关层数37级'),
	1=>__('通关层数50级'),
//	2=>__('通关层数60级'),
);
$official_conf=array(
1=>__('少尉'),
2=>__('中尉'),
3=>__('上尉'),
4=>__('少校'),
5=>__('中校'),
6=>__('上校'),
7=>__('少将'),
8=>__('中将'),
9=>__('上将'),
);

$official_jy_conf=array(
11=>__('曹魏八骑'),
);

$boss_level_jy_conf=array(
11=>__('神将·曹仁'),
12=>__('神将·曹洪'),
13=>__('神将·曹真'),
14=>__('神将·曹纯'),
15=>__('神将·曹休'),
16=>__('神将·夏侯惇'),
17=>__('神将·夏侯渊'),
18=>__('神将·夏侯尚'),
);

$official_jy_2_conf=array(
21=>__('五子良将'),
);

$boss_level_jy_2_conf=array(
21=>__('神将·张辽'),
22=>__('神将·徐晃'),
23=>__('神将·张合'),
24=>__('神将·于禁'),
25=>__('神将·乐进'),
);


$boss_level_conf=array(
1=>__('一星'),
2=>__('二星'),
3=>__('三星'),
4=>__('四星'),
5=>__('五星'),
6=>__('六星'),
7=>__('七星'),
8=>__('八星'),
9=>__('九星'),
10=>__('十星'),
);

$data=$type_conf=array();
$page='';
switch ($action){
	case 304000:
		//乾坤八卦
		$where=" where (entry_id=$action or entry_id=305000) ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct date from stat_boss $where order by date desc,type asc,name asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$min_date=date('Y-m-d',strtotime('today'));
		$max_date=date('Y-m-d',SERVER_OPEN_TIME);
		while ($result && $row=$result->fetch_assoc()){
			$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
			$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
		}
		$sql="select * from stat_boss where date>='$min_date' and date<='$max_date' and (entry_id=$action or entry_id=305000) order by date desc,type asc ";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			//平均进入
			$row['avg_player']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
			$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
			$data[$key][]=$row;
		}
		$page=$p->show();
		break;
	case 600100:
		//兵临城下
		$type_conf=array(
			'blcx_pt'=>__('普通'),
			'blcx_jy'=>__('精英'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 'blcx_pt';
		$entry_id='';//进入entry_id
		if($type=='blcx_pt')
			{$where=" where entry_id=600100  "; $entry_id=" entry_id=600100 ";}
		else {$where=" where entry_id=600200 "; $entry_id=" entry_id=600200 ";}
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct date from stat_boss $where order by date desc,type asc,name asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$min_date=date('Y-m-d',strtotime('today'));
		$max_date=date('Y-m-d',SERVER_OPEN_TIME);
		while ($result && $row=$result->fetch_assoc()){
			$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
			$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
		}
		$sql="select * from stat_boss where date>='$min_date' and date<='$max_date' and $entry_id order by date desc,type asc ";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			//平均进入
			$row['avg_player']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
			$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
			//祝福值
			$row['row_hope_value']=explode(",", $row['hope_value']);
			$data[$key][]=$row;
		}
		$page=$p->show();
		break;
	case 700100:
		//草船借箭
		$type_conf=array(
			//'ccjj_pt'=>__('普通'),
			'ccjj_jy'=>__('精英'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 'ccjj_jy';
		$entry_id='';//进入entry_id
		if($type=='ccjj_pt')
			{$where=" where entry_id=700100  "; $entry_id=" entry_id=700100 ";}
		else {$where=" where entry_id=700200 "; $entry_id=" entry_id=700200 ";}
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct date from stat_boss $where order by date desc,type asc,name asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$min_date=date('Y-m-d',strtotime('today'));
		$max_date=date('Y-m-d',SERVER_OPEN_TIME);
		while ($result && $row=$result->fetch_assoc()){
			$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
			$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
		}
		$sql="select * from stat_boss where date>='$min_date' and date<='$max_date' and $entry_id order by date desc,type asc ";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			//平均进入
			$row['avg_player']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
			//祝福值
			$row['row_hope_value']=explode(",", $row['hope_value']);
			$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
			$data[$key][]=$row;
		}
		$page=$p->show();
		break;
	case 800100:
	//过关斩将
		$where=" where entry_id=$action ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_boss  $where order by date desc,type asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			//平均进入
			$row['avg_player']=$row['player'] ? round($row['count']/$row['player'],2) : 0;

			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 900100:
		//伏魔八卦阵
		$start_time=$start_date ? strtotime($start_date) : 0;
		$end_time=$end_date ? strtotime($end_date)+86400 : 0;
		$where=' where 1';
		$where.=$start_time ? " and end_time>=$start_time" : '';
		$where.=$end_time ? " and end_time<$end_time" : '';
		$sql="select count(distinct from_unixtime(end_time,'%Y%m%d')) as count from log_faction_copy $where";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct from_unixtime(end_time,'%Y%m%d') as date from log_faction_copy $where order by end_time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$min_time=time();
		$max_time=SERVER_OPEN_TIME;
		while ($result && $row=$result->fetch_assoc()){
			$max_time=strtotime($row['date'])>$max_time ? strtotime($row['date'])+86400 : $max_time;
			$min_time=strtotime($row['date'])<$min_time ? strtotime($row['date']) :  $min_time;
		}
		$sql="select from_unixtime(end_time,'%Y-%m-%d') as date,count(*) as count,avg_online,sum(join_in) as join_in,max(join_in) as max_join_in,
			min(join_in) as min_join_in,max(end_wave_num) as max_wave_num,min(end_wave_num) as min_wave_num,sum(end_wave_num) as sum_wave_num
			from log_faction_copy where end_time>=$min_time and end_time<$max_time group by date order by date desc";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			if(empty($row['avg_online'])){
				$time=strtotime($row['date']);
				$sql="select avg(count) as avg_online from log_online where time>=$time and time<($time+86400)";
				$list=$mysqli->findOne($sql);
				$row['avg_online']=intval($list['avg_online']);
				if (!empty($row['avg_online'])){
					$sql="update log_faction_copy set avg_online={$row['avg_online']} where end_time>=$time and end_time<($time+86400)";
					$mysqli->query($sql);
				}
			}
			$row['week']=date('w',strtotime($row['date']));
			//平均进入副本玩家数：当日进入副本总人数/当日副本数
			$row['avg_join_in']=$row['join_in'] ? round($row['count']/$row['join_in'],4)*100 : 0;
			$row['avg_wave_num']=$row['count'] ? intval($row['sum_wave_num']/$row['count']) : 0;
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case '330100':
		//竞技场
		$where=" where entry_id=$action";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(*) as count from stat_copy $where";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_copy $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			$row['avg_join_in']=$row['join_in'] ? round($row['count']/$row['join_in'],4)*100 : 0;
			//参与度：参与人数/当天满足的人数
			$row['join_in_ratio']=$row['allow_join'] ? round($row['join_in']/$row['allow_join'],4)*100 : 0;
			//人均消费
			$row['avg_money']=$row['athletics_player'] ? round($row['athletics_money']/$row['athletics_player'],2) : 0;
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 900300:
	//洛阳攻防战
		$where=" where entry_id=$action ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_boss  $where order by date desc,type asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			//平均进入
			$row['avg_player']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
			isset($row['break_door_time']) ? '' : $row['break_door_time']=0;
			$row['break_door_time']=$row['break_door_time']==0 ? '' : intval(($row['break_door_time']/60)).'分';
			$row['host_time']=!isset($row['host_time']) || $row['host_time']==0 ? '' : intval(($row['host_time']/60)).'分';
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 500100:
	//十面埋伏
		$where=" where entry_id=$action ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_boss  $where order by date desc,type asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			//平均进入
			$row['avg_player']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case '560100':
		//蜀道试炼
		$type_conf=array(
			'sdsl_pt'=>__('普通'),
			'sdsl_jy'=>__('精英'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 'sdsl_pt';
		$entry_id='';//进入entry_id
		if($type=='sdsl_pt')
			 {$where=" where entry_id=560100 "; $entry_id=" entry_id=560100 ";}
		else {$where=" where entry_id=560200 "; $entry_id=" entry_id=560200 ";}
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct date from stat_boss $where order by date desc,type asc,name asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$min_date=date('Y-m-d',strtotime('today'));
		$max_date=date('Y-m-d',SERVER_OPEN_TIME);
		while ($result && $row=$result->fetch_assoc()){
			$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
			$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
		}
		$sql="select * from stat_boss where date>='$min_date' and date<='$max_date' and $entry_id order by date desc,type asc ";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			//平均进入
			$row['avg_player']=$row['player'] ? round($row['count']/$row['player'],2) : 0;
			$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
			$data[$key][]=$row;
		}
		$page=$p->show();
		break;
	case '630100':
		//海底总动员
		$where=" where entry_id=$action ";
		$where .=$start_date ? " and date<='$start_date' " : '';
		$where .=$end_date ? " and date>='$end_date' " : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_boss $where order by date desc,type asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;

			//当日最高在线
			$sql="select max_count,avg_count from stat_online where date='{$row['date']}'";
			$list=$mysqli->findOne($sql);
			$row['max_online']=empty($list) ? 0 : $list['max_count'];
			$row['avg_online']=empty($list) ? 0 : $list['avg_count'];

			$data[]=$row;
		}
		$page=$p->show();
		break;
	case '205500':
		//七擒孟获
		$where=" where entry_id=$action ";
		$where .=$start_date ? " and date<='$start_date' " : '';
		$where .=$end_date ? " and date>='$end_date' " : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_boss $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;

			//击杀boss比例
			$dataLevelAll=$dataPercentAll=array();
			$dataLevelAll=explode(",", $row['boss_level']);
            for($i=0;$i<count($dataLevelAll);$i++){
            	$dataPercentAll[]=$dataLevelAll[$i];
            }
			$row['row_boss_level_all']=$dataPercentAll;

			$data[]=$row;
		}

		$page=$p->show();
		break;
	case '510100':
		//凤雏秘境
		$where=" where entry_id=$action ";
		$where .=$start_date ? " and date<='$start_date' " : '';
		$where .=$end_date ? " and date>='$end_date' " : '';
		$sql="select count(distinct date) as count from stat_boss $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from stat_boss $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$row['week']=date('N',strtotime($row['date']));
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['player']/$row['allow_player'],4)*100 : 0;
			$data[]=$row;
		}
		$page=$p->show();
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
$smarty->assign('official_jy_2_conf',$official_jy_2_conf);
$smarty->assign('boss_level_jy_2_conf',$boss_level_jy_2_conf);
$smarty->assign('official_jy_conf',$official_jy_conf);
$smarty->assign('boss_level_jy_conf',$boss_level_jy_conf);
$smarty->assign('boss_level_conf',$boss_level_conf);
$smarty->assign('official_conf',$official_conf);
$smarty->assign('level_conf',$level_conf);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();
?>
