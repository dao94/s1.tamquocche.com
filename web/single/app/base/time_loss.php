<?php
//时间流失
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

$action=empty($_GET['action']) ? 'everyday_loss' : trim($_GET['action']);
$action_conf=array(
	'everyday_loss'=>__('每日流失'),
	'minute_loss'=>__('分钟流失'),
	'online_length'=>__('在线时长'),
);
$today=strtotime('today');
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$time_type=array(
	'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
	'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
	'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
	'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
	'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
);
$type=empty($_GET['type']) ? 'near30' : trim($_GET['type']);
$type=!array_key_exists($type, $time_type) ? 'near30' : $type;
$start_date=empty($_GET['start_date']) ? date('Y-m-d',$today-86400*30) : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['end_date']));
$sort=isset($_GET['sort']) ? intval($_GET['sort']) : 0;
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'sort'=>$sort,
	'type'=>$type,
	'open_date'=>$open_date,
);
$sort=($sort==0) ? 'desc' : 'asc';//日期排序
$listRows=30;//每页显示记录
switch ($action){
	case 'everyday_loss':
	default:
		//每日流失
		$mysqli=new DbMysqli();
		$where=" where l.date>='$start_date' and l.date<='$end_date' and l.date=r.date";
		$sql="select count(*) as count from stat_login l,stat_reg r $where";
		$total_count=$mysqli->count($sql);
		$p=new Page($total_count,$listRows);
		$sql="select r.date,r.character_count,r.total_character_count,l.old_player from stat_login l,stat_reg r
		$where order by date $sort limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$data=array();
		$a=$result;
		while ($result && $row=$result->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['subtotal_player']=$row['character_count']+$row['old_player'];//玩家小计
			//流失率= 100%-（当天老玩家数 ÷ 前一天累计玩家数 ）×100%
			$sql=sprintf("select total_character_count from stat_reg where date='%s'",date('Y-m-d',strtotime($row['date'])-86400));
			$list=$mysqli->findOne($sql);
			$row['loss']=$list['total_character_count'] ? (1-round($row['old_player']/$list['total_character_count'],4))*100 : 0;
			$data[]=$row;
		}
		break;

	case 'minute_loss':
		//分钟流失
		$where=" where m.date>='$start_date' and m.date<='$end_date' and m.date=r.date";
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from stat_minute_loss m $where";
		$total_count=$mysqli->count($sql);
		$p=new Page($total_count,$listRows);
		$sql="select r.character_count,r.total_character_count,m.* from stat_minute_loss m,stat_reg r $where order by date $sort limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$data=array();
		$fields=array('s10','s10_s30','s30_m1','m1_m2','m2_m3','m3_m5','m5_m10','m10_m20','m20_m30',
			'm30_m40','m40_m50','m50_m60','m60_m80','m80_m100','m100_m120','h2_h3','h3_h5','h5','morrow_loss');
		$merge_fields=array('m30_h1'=>array('m30_m40','m40_m50','m50_m60'),'h1_h3'=>array('m60_m80','m80_m100','m100_m120','h2_h3'));//合并字段
		while ($result && $row=$result->fetch_assoc()){
			foreach ($fields as $field){
				foreach ($merge_fields as $key=>$item){
					if(in_array($field,$item)){
						//合并
						isset($row[$key]) ? $row[$key]+=$row[$field] : $row[$key]=$row[$field];
						$row[$key.'_loss']=(isset($row[$key])&&$row['character_count']) ? round($row[$key]/$row['character_count'],4)*100 : 0;
					}
					$row[$field.'_loss']=$row['character_count'] ? round($row[$field]/$row['character_count'],4)*100 : 0;
				}
			}
			$row['standard_loss_loss']=$row['total_character_count'] ? round($row['standard_loss']/$row['total_character_count'],4)*100 : 0;
			$data[]=$row;
		}
		//dump($data);
		break;

	case 'online_length':
		//在线时长
		$where=" where o.date>='$start_date' and o.date<='$end_date' and l.date=o.date";
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from stat_online_length o,stat_login l $where";
		$total_count=$mysqli->count($sql);
		$p=new Page($total_count,$listRows);
		$sql="select l.login_player,o.* from stat_online_length o,stat_login l $where order by date $sort limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$data=array();
		$fields=array('m5'=>'#EEDFBF','m5_h1'=>'#D0CABA','h1_h2'=>'#ED4FA3','h2_h3'=>'#CCCC00',
			'h3_h4'=>'#990000','h4_h6'=>'#0057CC','h6_h8'=>'#2C2C2C','h8'=>'#000000');
		while ($result && $row=$result->fetch_assoc()){
			foreach ($fields as $field=>$colour){
				$row[$field.'_ratio']=$row['login_player'] ? round($row[$field]/$row['login_player'],4)*100 : 0;
			}
			$row['week']=date('N',strtotime($row['date']));
			$data[]=$row;
		}
		$smarty->assign('fields',$fields);
		break;
}

$smarty->assign('conditions',$conditions);
$smarty->assign('action',$action);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('time_type',$time_type);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();