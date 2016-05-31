<?php
//坐骑分析
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

$today=strtotime('today');
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$type=empty($_GET['type']) ? 'history' : trim($_GET['type']);
$action=empty($_GET['action']) ? 'ride' : my_escape_string(trim($_GET['action']));
$action_conf=array(
'ride'=>__('坐骑阶数'),
'jinglian'=>__('坐骑精炼'),
);
$time_type=array(
	'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
	'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
	'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
	'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
	'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
);
//部位
$part_conf=array(
	0=>__('全部'),
	1=>__('鞭具'),
	2=>__('鞍具'),
	3=>__('蹬具'),
	4=>__('蹄铁'),

);
$type=!array_key_exists($type, $time_type) ? 'history' : $type;
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$data=array();
$mysqli=new DbMysqli();
switch($action){
	case 'ride':
		//坐骑阶数
		$where=" where true ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_ride $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select distinct date from stat_ride $where order by date desc,type asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$min_date=date('Y-m-d',strtotime('today'));
		$max_date=date('Y-m-d',SERVER_OPEN_TIME);
		while ($result && $row=$result->fetch_assoc()){
			$max_date=$row['date']>$max_date ? $row['date'] : $max_date;
			$min_date=$row['date']<$min_date ? $row['date'] :  $min_date;
		}
		$sql="select * from stat_ride where date>='$min_date' and date<='$max_date' order by date desc,type asc ";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['ride_remark']=json_decode($row['ride_remark'],true);
			$row['show_remark']=json_decode($row['show_remark'],true);
			$key=$row['date'].'('.__('周').date('N',strtotime($row['date'])).')';
			$data[$key][]=$row;
		}
		$page=$p->show();
		break;
	case 'jinglian':
		//坐骑精炼
		$where=" where true ";
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(distinct date) as count from stat_ride_jl $where ";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows,10);
		$sql="select * from stat_ride_jl $where order by date desc limit {$p->firstRow},{$p->listRows} ";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['ride_fan_remark']=json_decode($row['ride_fan_remark'],true);
			$row['ride_ling_remark']=json_decode($row['ride_ling_remark'],true);
			$row['ride_xian_remark']=json_decode($row['ride_xian_remark'],true);
			$row['ride_shen_remark']=json_decode($row['ride_shen_remark'],true);
			//参与度
			$row['avg_count']=$row['allow_player'] ? round($row['join_count']/$row['allow_player'],4)*100 : 0;
			//全部处理
			foreach($row['ride_fan_remark'] as $part=>$items){
				foreach($items as $level=>$item){
					$row['fan_level'][$level]=isset($row['fan_level'][$level]) ?  $row['fan_level'][$level]+=$item : $row['fan_level'][$level]=$item;
				}
			}
			foreach($row['ride_ling_remark'] as $part=>$items){
				foreach($items as $level=>$item){
					$row['ling_level'][$level]=isset($row['ling_level'][$level]) ?  $row['ling_level'][$level]+=$item : $row['ling_level'][$level]=$item;
				}
			}
			foreach($row['ride_xian_remark'] as $part=>$items){
				foreach($items as $level=>$item){
					$row['xian_level'][$level]=isset($row['xian_level'][$level]) ?  $row['xian_level'][$level]+=$item : $row['xian_level'][$level]=$item;
				}
			}
			foreach($row['ride_shen_remark'] as $part=>$items){
				foreach($items as $level=>$item){
					$row['shen_level'][$level]=isset($row['shen_level'][$level]) ?  $row['shen_level'][$level]+=$item : $row['shen_level'][$level]=$item;
				}
			}

			$data[]=$row;
		}
		$page=$p->show();
		break;
}

$conditions=array(
	'type'=>$type,
	'action'=>$action,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$smarty->assign('action_conf',$action_conf);
$smarty->assign('time_type',$time_type);
$smarty->assign('part_conf',$part_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();
?>
