<?php
//货币滞留率
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__.'log_config.php';

//非系统产出或消耗渠道
$no_system='5,6';
$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));
$type=array_key_exists($type, $money_class_conf) ? $type : 1;
$action=empty($_GET['action']) ? 'list' : trim($_GET['action']);
$data=array();
switch ($action){
	default:
	case 'list':
		$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
		$conditions=array(
			'action'=>$action,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'type'=>$type,
			'today'=>date('Y-m-d',strtotime('today')),
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		$where=" where money_type=$type";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$where.=$no_system ? " and type not in ($no_system)" : '';
		$mysqli=new DbMysqli();
		$sql="select count(distinct date) as count from stat_money $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct date from stat_money $where order by date desc limit {$p->firstRow},{$p->listRows};";
		$result=$mysqli->query($sql);
		$date_list=array();
		while ($result && $row=$result->fetch_assoc()){
			$date_list[]=$row['date'];
		}
		$date_str=$date_list ? "'".implode("','", $date_list)."'" : '';
		$sql="select date,io,sum(money_num) as money_num from stat_money $where and date in ($date_str) group by date,io order by date desc";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$data[$row['date']]['list'][$row['io']]['money_num']=$row['money_num'];
			//截止今天总产出和总消耗
			$sql="select count(distinct date) as count,sum(money_num) as total_money_num from stat_money where date<='{$row['date']}' and money_type=$type and type not in ($no_system) and io={$row['io']}";
			$list=$mysqli->findOne($sql);
			$data[$row['date']]['list'][$row['io']]['total_money_num']=isset($list['total_money_num']) ? $list['total_money_num'] : 0;
			$data[$row['date']]['list'][$row['io']]['avg_money_num']=empty($list['count']) ? 0 : round($list['total_money_num']/$list['count'],0);
			//昨日实际存量
			if(!isset($data[$row['date']]['yesterday_real_money_num'])){
				$yesterday=date('Y-m-d',strtotime($row['date'])-86400);
				$sql="select money_num,not_loss_money_num from stat_money_real where date='$yesterday' and money_type=$type";
				$list=$mysqli->findOne($sql);
				$data[$row['date']]['yesterday_real_money_num']=isset($list['money_num']) ? $list['money_num'] : 0;
			}
			//今日实际存量
			if(!isset($data[$row['date']]['real_money_num'])){
				$sql="select money_num,not_loss_money_num from stat_money_real where date='{$row['date']}' and money_type=$type";
				$list=$mysqli->findOne($sql);
				$data[$row['date']]['real_money_num']=isset($list['money_num']) ? $list['money_num'] : 0;
				//非流失玩家货币存量
				$data[$row['date']]['not_loss_money_num']=isset($list['not_loss_money_num']) ? $list['not_loss_money_num'] : 0;
			}
			!isset($data[$row['date']]['week']) ? $data[$row['date']]['week']=date('N',strtotime($row['date'])) : '';
			if(isset($data[$row['date']]['list'][0]['money_num']) && isset($data[$row['date']]['list'][1]['money_num'])){
				//今日理论存量=昨日实际存量 +今日产出 -今日消耗
				$data[$row['date']]['ideal_money']=$data[$row['date']]['yesterday_real_money_num']+$data[$row['date']]['list'][1]['money_num']-$data[$row['date']]['list'][0]['money_num'];
				//当日滞留率=（1-当天消耗/当天产出)*100%
				$data[$row['date']]['keep_ratio']=empty($data[$row['date']]['list'][1]['money_num']) ? 0 :
					(1-round($data[$row['date']]['list'][0]['money_num']/$data[$row['date']]['list'][1]['money_num'],4))*100;
				//实际理论差=实际库存-理论存量
				$data[$row['date']]['diff_money_num']=$data[$row['date']]['real_money_num']-$data[$row['date']]['ideal_money'];
			}
			//总滞留率=（1-总消耗/总产出）*100%
			if(isset($data[$row['date']]['list'][0]['total_money_num']) && isset($data[$row['date']]['list'][1]['total_money_num'])){
				$data[$row['date']]['total_keep_ratio']=empty($data[$row['date']]['list'][1]['total_money_num']) ? 0 :
					(1-round($data[$row['date']]['list'][0]['total_money_num']/$data[$row['date']]['list'][1]['total_money_num'],4))*100;
			}
			//非流失玩家滞留率=( 1 - 总消耗 / (总产量 - 今日实际存量 + 非流失存量 ) )
			if(isset($data[$row['date']]['list'][0]['total_money_num']) && isset($data[$row['date']]['list'][1]['total_money_num']) && 
				isset($data[$row['date']]['real_money_num']) && isset($data[$row['date']]['not_loss_money_num'])){
				//(总产量 - 今日实际存量 + 非流失存量 )
				$money_num=$data[$row['date']]['list'][1]['total_money_num']-$data[$row['date']]['real_money_num']+$data[$row['date']]['not_loss_money_num'];
				$data[$row['date']]['not_loss_keep_ratio']=$money_num ? round(1-$data[$row['date']]['list'][0]['total_money_num']/$money_num,5)*100 : 0;
			}
			//报警机制(大于0.001) =(今日实际存量-今日理论存量)/今日理论存量
			$data[$row['date']]['alarm']=empty($data[$row['date']]['ideal_money']) ? 0 :
				($data[$row['date']]['real_money_num']-$data[$row['date']]['ideal_money'])/$data[$row['date']]['ideal_money'];
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
		$smarty->assign('time_conf',$time_conf);
		$smarty->assign('page',$p->show());
		break;

	case 'detail':
		//产出和消耗明细
		$date=empty($_GET['date']) ? '' : my_escape_string(trim($_GET['date']));
		$conditions=array(
			'action'=>$action,
			'date'=>$date,
			'type'=>$type,
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		$mysqli=new DbMysqli();
		$where=" where date='$date' and money_type=$type";
		$where.=$no_system ? " and type not in ($no_system)" : '';
		$sql="select type,io,money_num,count from stat_money $where order by money_num desc";
		$result=$mysqli->query($sql);
		while ($result && $row=$result->fetch_assoc()){
			$data[$row['io']][$row['type']]['type']=isset($money_type_conf[$row['type']]) ? $money_type_conf[$row['type']] : '';
			$data[$row['io']][$row['type']]['money_num']=$row['money_num'];
			$data[$row['io']][$row['type']]['count']=$row['count'];
			$data[$row['io']][$row['type']]['avg_money_num']=$row['count'] ? intval($row['money_num']/$row['count']) : 0;
			if($row['io']==0){
				isset($conditions['consume']) ? $conditions['consume']+=$row['money_num'] : $conditions['consume']=$row['money_num'];
			}elseif($row['io']==1){
				isset($conditions['output']) ? $conditions['output']+=$row['money_num'] : $conditions['output']=$row['money_num'];
			}
		}
		break;
}
$smarty->assign('conditions',$conditions);
$smarty->assign('money_class_conf',$money_class_conf);
$smarty->assign('data',$data);
$smarty->display();
