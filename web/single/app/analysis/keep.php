<?php

//用户留存统计
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

$action=empty($_GET['action']) ? 'keep_day' : trim($_GET['action']);
$action_conf=array(
	'keep_day'=>__('留存天数统计'),
	'keep_ratio'=>__('玩家留存率'),
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
$action_player=empty($_GET['action_player']) ? 'all_player' : trim($_GET['action_player']);
$action_player_conf=array(
'all_player'=>__('总体'),
'new_player'=>__('新玩家'),
'old_player'=>__('滚服玩家'),
);
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'sort'=>$sort,
	'type'=>$type,
	'open_date'=>$open_date,
);
$sort=($sort==0) ? 'desc' : 'asc';//日期排序
$mysqli=new DbMysqli();
$p='';
switch ($action){
	case 'keep_day':
		switch($action_player){
			case 'all_player':
				$where=" where k.date>='$start_date' and k.date<='$end_date' and k.date=r.date";
				$sql="select count(*) as count from stat_keep_day k,stat_reg r $where";
				$total_rows=$mysqli->count($sql);
				$p=new Page($total_rows,30);
				$sql="select k.*,r.character_count,r.total_character_count from stat_keep_day k,stat_reg r $where
					order by date $sort limit {$p->firstRow},{$p->listRows}";
				$result=$mysqli->query($sql);
				$keep_day=array(1,2,3,4,5,6,7,14,21,30);//留存天数
				$data=array();
				while ($result && $row=$result->fetch_assoc()){
					foreach ($keep_day as $day){
						$row['day'.$day.'_ratio']=$row['character_count'] ? round($row['day'.$day]/$row['character_count'],4)*100 : 0;
					}
					$row['week']=date('N',strtotime($row['date']));
					$data[]=$row;
				}
				break;
			case 'new_player':
				$where=" where k.date>='$start_date' and k.date<='$end_date' and k.date=r.date";
				$sql="select count(*) as count from stat_keep_day k,stat_reg r $where";
				$total_rows=$mysqli->count($sql);
				$p=new Page($total_rows,30);
				$sql="select k.*,r.character_count,r.total_character_count from stat_keep_day k,stat_reg r $where " .
					 " order by date $sort limit {$p->firstRow},{$p->listRows} ";
				$result=$mysqli->query($sql);
				$keep_day=array(1,2,3,4,5,6,7,14,21,30);//留存天数
				$data=array();
				while ($result && $row=$result->fetch_assoc()){
					$sql="select kgf.day1 as day1gf,kgf.day2 as day2gf,kgf.day3 as day3gf,kgf.day4 as day4gf,kgf.day5 as day5gf,kgf.day6 as day6gf,kgf.day7 as day7gf,kgf.day14 as day14gf,kgf.day21 as day21gf,kgf.day30 as day30gf, " .
							" rgf.character_count as character_countgf,rgf.total_character_count as total_character_countgf from stat_keep_day_gunfu kgf,stat_reg_gunfu rgf " .
							" where kgf.date=rgf.date and kgf.date='{$row['date']}' limit 1";
					$list=$mysqli->findOne($sql);
					$row['day1gf']=$list ? $list['day1gf'] : '';
					$row['day2gf']=$list ? $list['day2gf'] : '';
					$row['day3gf']=$list ? $list['day3gf'] : '';
					$row['day4gf']=$list ? $list['day4gf'] : '';
					$row['day5gf']=$list ? $list['day5gf'] : '';
					$row['day6gf']=$list ? $list['day6gf'] : '';
					$row['day7gf']=$list ? $list['day7gf'] : '';
					$row['day14gf']=$list ? $list['day14gf'] : '';
					$row['day21gf']=$list ? $list['day21gf'] : '';
					$row['day30gf']=$list ? $list['day30gf'] : '';
					$row['character_countgf']=$list ? $list['character_countgf'] : '';
					$row['total_character_countgf']=$list ? $list['total_character_countgf'] : '';
					foreach ($keep_day as $day){
						$row['day'.$day.'_ratio']=($row['character_count']-$row['character_countgf']) ? round(($row['day'.$day]-$row['day'.$day.'gf'])/($row['character_count']-$row['character_countgf']),4)*100 : 0;
					}
					$row['total_character_count']=$row['total_character_count']-$row['total_character_countgf'];
					$row['character_count']=$row['character_count']-$row['character_countgf'];
					$row['week']=date('N',strtotime($row['date']));
					$data[]=$row;
				}
				break;
			case 'old_player':
				$where=" where k.date>='$start_date' and k.date<='$end_date' and k.date=r.date";
				$sql="select count(*) as count from stat_keep_day_gunfu k,stat_reg_gunfu r $where";
				$total_rows=$mysqli->count($sql);
				$p=new Page($total_rows,30);
				$sql="select k.*,r.character_count,r.total_character_count from stat_keep_day_gunfu k,stat_reg_gunfu r $where
					order by date $sort limit {$p->firstRow},{$p->listRows}";
				$result=$mysqli->query($sql);
				$keep_day=array(1,2,3,4,5,6,7,14,21,30);//留存天数
				$data=array();
				while ($result && $row=$result->fetch_assoc()){
					foreach ($keep_day as $day){
						$row['day'.$day.'_ratio']=$row['character_count'] ? round($row['day'.$day]/$row['character_count'],4)*100 : 0;
					}
					$row['week']=date('N',strtotime($row['date']));
					$data[]=$row;
				}
				break;
		}
		break;

	case 'keep_ratio':
		//第N天留存
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from stat_keep_ratio k,stat_reg r where k.date=r.date";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows,30);
		$sql="select k.*,r.total_character_count from stat_keep_ratio k,stat_reg r where k.date=r.date order by day asc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$data=array();
		while ($result && $row=$result->fetch_assoc()){
			$row['ratio']=$row['total_character_count'] ? round($row['count']/$row['total_character_count'],4)*100 : 0;
			$data[]=$row;
		}
		break;
}

$smarty->assign('conditions',$conditions);
$smarty->assign('action',$action);
$smarty->assign('action_player',$action_player);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('action_player_conf',$action_player_conf);
$smarty->assign('time_type',$time_type);
$smarty->assign('data',$data);
$smarty->assign('page',$p->show());
$smarty->display();