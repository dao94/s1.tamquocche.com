<?php
//伙伴（宠物分析）
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'game_config.php';

$action_conf=array(
	'realm'=>__('境界分析'),
	'pullulate'=>__('灵识分析'),
	'jinjian'=>__('觐见分析'),
	'equip'=>__('装备升级'),
	'card'=>__('天关分析'),
	'analysis'=>__('装备分析'),
);
$action=empty($_GET['action']) ? 'realm' : trim($_GET['action']);
$today=strtotime('today');
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$type=empty($_GET['type']) ? 'history' : trim($_GET['type']);
$time_type=array(
	'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
	'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
	'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
	'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
	'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
);
$type=!array_key_exists($type, $time_type) ? 'history' : $type;
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));


$data=array();

switch ($action){
	case 'realm':
		$where=' where 1';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$table_name='stat_pet_'.$action;//表名
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from $table_name $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from $table_name $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['total_pet_realm']=0;
			foreach ($pet_realm_conf as $key=>$realm){
				$row['total_pet_realm']+=$row['realm'.$key];
				$row['realm'.$key.'_remark']=(array)json_decode($row['realm'.$key.'_remark'],true);
			}
			$data[]=$row;
		}
		break;

	case 'pullulate':
		$where=' where 1';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$table_name='stat_pet_'.$action;//表名
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from $table_name $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from $table_name $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['attack_remark']=(array)json_decode($row['attack_remark']);
			$row['defense_remark']=(array)json_decode($row['defense_remark']);
			$row['hp_remark']=(array)json_decode($row['hp_remark']);
			$row['attack_count']=array_sum($row['attack_remark']);
			$row['defense_count']=array_sum($row['defense_remark']);
			$row['hp_count']=array_sum($row['hp_remark']);
			$data[]=$row;
		}
		break;
	case 'jinjian':
		$where=' where 1';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$table_name='stat_pet_'.$action;//表名
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from $table_name $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from $table_name $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$remark=(array)json_decode($row['remark'],true);
			unset($row['remark']);
			$row=array_merge($row,$remark);
			$data[]=$row;
		}
		break;
	case 'equip':
		//伙伴装备升级
		$where=' where 1';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$table_name='stat_pet_'.$action;//表名
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from $table_name $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from $table_name $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$data[]=$row;
		}
		break;
	case 'card':
		//天关分析
		include __CLASSES__.'PetCard.class.php';
		$petCard=new PetCard();
		$where=' where 1';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$table_name='stat_pet_'.$action;//表名
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from $table_name $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from $table_name $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$name_id=$row['name_id'];
			$row['name']=$petCard->getName($name_id);
			$row['avg_count']=$row['allow_player'] ? intval($row['count']/$row['allow_player']) : 0;//人均关卡
			$row['avg_player']=$row['count_player'] ? round($row['count_player']/$row['allow_player'],2)*100 : 0;//平均参与度
			$data[]=$row;
		}
		break;
	case 'analysis':
		$part_conf=array(
			1=>__('头盔'),
			2=>__('衣甲'),
			3=>__('护符'),
			4=>__('项圈'),
			5=>__('饰品'),
			6=>__('武器'),
		);
		$mysqli=new DbMysqli();
		$where=' where true';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql="select count(*) as count from stat_equip_strong $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from stat_equip_strong $where order by date desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['week']=date('N',strtotime($row['date']));
			$row['strong_fan_remark']=json_decode($row['strong_fan_remark'],true);
			$row['strong_ling_remark']=json_decode($row['strong_ling_remark'],true);
			$row['strong_xian_remark']=json_decode($row['strong_xian_remark'],true);
			$row['strong_shen_remark']=json_decode($row['strong_shen_remark'],true);
			$row['strong_level_remark']=json_decode($row['strong_level_remark'],true);
			$row['strong_level1_remark']=isset($row['strong_level_remark'][1]) ? $row['strong_level_remark'][1] : array();//1-10
			$row['strong_level2_remark']=isset($row['strong_level_remark'][2]) ? $row['strong_level_remark'][2] : array();
			$row['strong_level3_remark']=isset($row['strong_level_remark'][3]) ? $row['strong_level_remark'][3] : array();
			$row['strong_level4_remark']=isset($row['strong_level_remark'][4]) ? $row['strong_level_remark'][4] : array();
			$row['strong_level5_remark']=isset($row['strong_level_remark'][5]) ? $row['strong_level_remark'][5] : array();
			$row['strong_level6_remark']=isset($row['strong_level_remark'][6]) ? $row['strong_level_remark'][6] : array();
			$row['strong_level7_remark']=isset($row['strong_level_remark'][7]) ? $row['strong_level_remark'][7] : array();
			$data[]=$row;
		}
		$smarty->assign('part_conf',$part_conf);
		break;
}
$conditions=array(
	'action'=>$action,
	'type'=>$type,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME)
);
$pullulate_range=array(
	array(26,30),
	array(21,25),
	array(16,20),
	array(11,15),
	array(6,10),
	array(1,5),
);
sort($pullulate_range);
$smarty->assign('pullulate_range',$pullulate_range);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('time_type',$time_type);
$smarty->assign('pet_realm_conf',$pet_realm_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('page',$p->show());
$smarty->assign('data',$data);
$smarty->display();
