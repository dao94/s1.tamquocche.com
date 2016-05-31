<?php

//充值分布
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';


$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : 'total';
$mysqli = new DbMysqli();

//获取代理id
function getAgentId(){
	$agent_list=array();
	$agent_config_file=__CONFIG__.'agent_list_config.php';
	if(file_exists($agent_config_file)){
		include $agent_config_file;
	}
	if(empty($agent_list) || !in_array(SERVER_AGENT,$agent_list)){
		exit('Not found');
	}else{
		return array_search(SERVER_AGENT,$agent_list);
	}
}


switch ($action) {
	case 'week':
		$count = $mysqli->count('select count(`week`) from `pay_distributed`');
		include __CLASSES__ . 'Page.class.php';
		$p = new Page($count);
		$sql = 'select * from `pay_distributed` order by `week` asc limit ' . $p->firstRow . ',' . $p->listRows;
		$query = $mysqli->query($sql);
		$smarty->assign('query', $query);
		$smarty->assign('page', $p->show());
		break;
	case 'total':
		$today=strtotime('today');
		$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
		$start_date=empty($_GET['start_date']) ? $open_date : my_escape_string($_GET['start_date']);
		$end_date=empty($_GET['end_date']) ? date('Y-m-d',$today) : my_escape_string($_GET['end_date']);
		$first=isset($_GET['first'])&&$_GET['first']!='' ?  intval($_GET['first']) : '';//是否首充
		$type=isset($_GET['type']) ?  trim($_GET['type']) : 'history';//时间类型
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date)+86400;
		$where=$first ? ' where is_first=1' : " where ts>=$start_time and ts<$end_time";
		$action_first=isset($_GET['action_first']) ?  trim($_GET['action_first']) : 'all_player';//玩家类型

		include __CLASSES__.'Page.class.php';

		//总注册数
		include __CLASSES__ . 'Mdb.class.php';
		$mongo = new Mdb();
		$total_first=array();
		$register='';
		$conditions=array(
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'type'=>$type,
			'today'=>date('Y-m-d',time()),
			'action_first'=>$action_first,
		);
		$time_type=array(
			'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
			'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
			'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
			'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
			'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
		);
		$action_conf=array(
				'all_player'=>__('总体'),
				'new_player'=>__('新玩家'),
				'old_player'=>__('滚服玩家'),
			);
		if($first){
			switch($action_first){
				case 'all_player':
					//总体
					//72小时内有登录的玩家
					$ts = time() - 72 * 60 * 60;
					$aliveSql = 'select count(distinct `char_id`) from `log_login` where `login_time` >' . $ts . ' or `logout_time` > ' . $ts;
					$aliver = $mysqli->count($aliveSql);
					$sql = "select sum(`gold`) as `sg` from `pay_order` $where  group by `char_id` order by `sg` desc";
					$query = $mysqli->query($sql);
					//充值元宝范围配置
					$pay_range_conf=array(500000, 100000, 50000, 20000,10000,5000,2000,1000,500,20,10);
					$sum_gold=$payer=0;
					$data=array();
					while ($row = $query->fetch_assoc()) {
						$payer++;//玩家数
						$sum_gold += $row['sg'];//总元宝数
						foreach ($pay_range_conf as $key=>$val){
							if($row['sg']>=$val){
								isset($data[$val]) ? $data[$val]++ : $data[$val]=1;
								break;
							}
						}
					}
					$register=$mongo->allCount('characters',array('creat_time'=>array('$gte'=>$start_time,'$lt'=>$end_time)));
					//首日充值情况
					$open_start_time=strtotime(date('Y-m-d',SERVER_OPEN_TIME));
					$open_end_time=$open_start_time+86400;
					$sql="select count(distinct char_id) as count,sum(gold) as gold from pay_order where ts>=$open_start_time and ts<$open_end_time";
					$list=$mysqli->findOne($sql);
					$total_first['pay_count']=empty($list['count']) ? 0 : intval($list['count']);
					$total_first['gold_count']=empty($list['gold']) ? 0 : intval($list['gold']);
					$total_first['reg_count']=$mongo->allCount('characters', array('creat_time'=>array('$gte'=>$open_start_time,'$lt'=>$open_end_time)));


					$sql="select count(distinct y,m,d) as count from pay_order $where";
					$total_count=$mysqli->count($sql);
					$p=new Page($total_count);

					$sql="select distinct y,m,d from pay_order $where order by y desc,m desc,d desc limit {$p->firstRow},{$p->listRows}";
					$query=$mysqli->query($sql);
					$max_time=0;
					$min_time=time();
					while ($row = $query->fetch_assoc()) {
						$time=mktime(0,0,0,$row['m'],$row['d'],$row['y']);
						$max_time=$time>$max_time ? $time: $max_time;
						$min_time=$time<$min_time ? $time : $min_time;
					}
					$where.=$max_time&&$min_time ? " and ts>=$min_time and ts<$max_time+86400" : '';

					//分天，玩家充值分布
					$sql="select y,m,d,char_id,sum(gold) as gold from pay_order $where group by y,m,d,char_id";
					$query=$mysqli->query($sql);
					$day_data=array();
					$item_count=count($pay_range_conf);
					while ($row = $query->fetch_assoc()) {
						$date=date('Y-m-d',mktime(0,0,0,$row['m'],$row['d'],$row['y']));
						!isset($day_data[$date]['week'])&&$day_data[$date]['week']=date('N',strtotime($date));
						isset($day_data[$date]['count']) ? $day_data[$date]['count']++ : $day_data[$date]['count']=1;
						foreach ($pay_range_conf as $k=>$val){
							if ($row['gold'] >= $val) {
								isset($day_data[$date]['distributed'][$val]) ? $day_data[$date]['distributed'][$val]++ : $day_data[$date]['distributed'][$val]=1;
								break;
							}
						}
					}
					krsort($day_data);
					$smarty->assign('day_data', $day_data);
					$smarty->assign('page',$p->show());
					break;
				case 'new_player':
					//新玩家
					$sql="select * from log_old_account";
					$result=$mysqli->query($sql);
					$users=$users_mdb=array();
					while($result && $row=$result->fetch_assoc()){
						$users[]="'{$row['account']}'";
						$users_mdb[]="{$row['account']}";
					}
					$same_str='';//滚服玩家 排除掉
					if(!empty($users)){
						$same_str=implode(',',$users);
						$same_str=$same_str=='' ? ' ' : " and account not in ($same_str) " ;
					}
					//72小时内有登录的玩家
					$ts = time() - 72 * 60 * 60;
					$aliveSql = 'select count(distinct `char_id`) from `log_login` where `login_time` >' . $ts . ' or `logout_time` > ' . $ts .$same_str;
					$aliver = $mysqli->count($aliveSql);
					$sql = "select sum(`gold`) as `sg` from `pay_order` $where $same_str  group by `char_id` order by `sg` desc";
					$query = $mysqli->query($sql);
					//充值元宝范围配置
					$pay_range_conf=array(500000, 100000, 50000, 20000,10000,5000,2000,1000,500,20,10);
					$sum_gold=$payer=0;
					$data=array();
					while ($row = $query->fetch_assoc()) {
						$payer++;//玩家数
						$sum_gold += $row['sg'];//总元宝数
						foreach ($pay_range_conf as $key=>$val){
							if($row['sg']>=$val){
								isset($data[$val]) ? $data[$val]++ : $data[$val]=1;
								break;
							}
						}
					}
					$condition=array('creat_time'=>array('$gte'=>$start_time,'$lt'=>$end_time),'account'=>array('$nin'=>$users_mdb));
					$register=$mongo->allCount('characters',$condition);
					//首日充值情况
					$open_start_time=strtotime(date('Y-m-d',SERVER_OPEN_TIME));
					$open_end_time=$open_start_time+86400;
					$sql="select count(distinct char_id) as count,sum(gold) as gold from pay_order where ts>=$open_start_time and ts<$open_end_time $same_str";
					$list=$mysqli->findOne($sql);
					$total_first['pay_count']=empty($list['count']) ? 0 : intval($list['count']);
					$total_first['gold_count']=empty($list['gold']) ? 0 : intval($list['gold']);
					$total_first['reg_count']=$mongo->allCount('characters', array('creat_time'=>array('$gte'=>$open_start_time,'$lt'=>$open_end_time),'account'=>array('$nin'=>$users_mdb)));


					$sql="select count(distinct y,m,d) as count from pay_order $where  $same_str ";
					$total_count=$mysqli->count($sql);
					$p=new Page($total_count);

					$sql="select distinct y,m,d from pay_order $where $same_str order by y desc,m desc,d desc limit {$p->firstRow},{$p->listRows}";
					$query=$mysqli->query($sql);
					$max_time=0;
					$min_time=time();
					while ($row = $query->fetch_assoc()) {
						$time=mktime(0,0,0,$row['m'],$row['d'],$row['y']);
						$max_time=$time>$max_time ? $time: $max_time;
						$min_time=$time<$min_time ? $time : $min_time;
					}
					$where.=$max_time&&$min_time ? " and ts>=$min_time and ts<$max_time+86400" : '';

					//分天，玩家充值分布
					$sql="select y,m,d,char_id,sum(gold) as gold from pay_order $where $same_str group by y,m,d,char_id";
					$query=$mysqli->query($sql);
					$day_data=array();
					$item_count=count($pay_range_conf);
					while ($row = $query->fetch_assoc()) {
						$date=date('Y-m-d',mktime(0,0,0,$row['m'],$row['d'],$row['y']));
						!isset($day_data[$date]['week'])&&$day_data[$date]['week']=date('N',strtotime($date));
						isset($day_data[$date]['count']) ? $day_data[$date]['count']++ : $day_data[$date]['count']=1;
						foreach ($pay_range_conf as $k=>$val){
							if ($row['gold'] >= $val) {
								isset($day_data[$date]['distributed'][$val]) ? $day_data[$date]['distributed'][$val]++ : $day_data[$date]['distributed'][$val]=1;
								break;
							}
						}
					}
					krsort($day_data);
					$smarty->assign('day_data', $day_data);
					$smarty->assign('page',$p->show());
					break;
				case 'old_player':
					//滚服玩家
					$sql="select * from log_old_account";
					$result=$mysqli->query($sql);
					$users=$users_mdb=array();
					while($result && $row=$result->fetch_assoc()){
						$users[]="'{$row['account']}'";
						$users_mdb[]="'{$row['account']}'";
					}
					$same_str='';//滚服玩家 排除掉
					if(!empty($users)){
						$same_str=implode(',',$users);
						$same_str=$same_str=='' ? ' ' : " and account in ($same_str) " ;
					}else{
						$same_str=$same_str=='' ? " and false " : ' ' ;
					}

					//72小时内有登录的玩家
					$ts = time() - 72 * 60 * 60;
					$aliveSql = 'select count(distinct `char_id`) from `log_login` where `login_time` >' . $ts . ' or `logout_time` > ' . $ts .$same_str;
					$aliver = $mysqli->count($aliveSql);
					$sql = "select sum(`gold`) as `sg` from `pay_order` $where $same_str  group by `char_id` order by `sg` desc";
					$query = $mysqli->query($sql);
					//充值元宝范围配置
					$pay_range_conf=array(500000, 100000, 50000, 20000,10000,5000,2000,1000,500,20,10);
					$sum_gold=$payer=0;
					$data=array();
					while ($row = $query->fetch_assoc()) {
						$payer++;//玩家数
						$sum_gold += $row['sg'];//总元宝数
						foreach ($pay_range_conf as $key=>$val){
							if($row['sg']>=$val){
								isset($data[$val]) ? $data[$val]++ : $data[$val]=1;
								break;
							}
						}
					}

					$condition=array('creat_time'=>array('$gte'=>$start_time,'$lt'=>$end_time),'account'=>array('$in'=>$users_mdb));
					$register=$mongo->allCount('characters',$condition);
					//首日充值情况
					$open_start_time=strtotime(date('Y-m-d',SERVER_OPEN_TIME));
					$open_end_time=$open_start_time+86400;
					$sql="select count(distinct char_id) as count,sum(gold) as gold from pay_order where ts>=$open_start_time and ts<$open_end_time $same_str";
					$list=$mysqli->findOne($sql);
					$total_first['pay_count']=empty($list['count']) ? 0 : intval($list['count']);
					$total_first['gold_count']=empty($list['gold']) ? 0 : intval($list['gold']);
					$total_first['reg_count']=$mongo->allCount('characters',$condition);

					$sql="select count(distinct y,m,d) as count from pay_order_single $where $same_str ";
					$total_count=$mysqli->count($sql);
					$p=new Page($total_count);

					$sql="select distinct y,m,d from pay_order $where $same_str order by y desc,m desc,d desc limit {$p->firstRow},{$p->listRows}";
					$query=$mysqli->query($sql);
					$max_time=0;
					$min_time=time();
					while ($row = $query->fetch_assoc()) {
						$time=mktime(0,0,0,$row['m'],$row['d'],$row['y']);
						$max_time=$time>$max_time ? $time: $max_time;
						$min_time=$time<$min_time ? $time : $min_time;
					}
					$where.=$max_time&&$min_time ? " and ts>=$min_time and ts<$max_time+86400" : '';

					//分天，玩家充值分布
					$sql="select y,m,d,char_id,sum(gold) as gold from pay_order $where $same_str group by y,m,d,char_id";
					$query=$mysqli->query($sql);
					$day_data=array();
					$item_count=count($pay_range_conf);
					while ($row = $query->fetch_assoc()) {
						$date=date('Y-m-d',mktime(0,0,0,$row['m'],$row['d'],$row['y']));
						!isset($day_data[$date]['week'])&&$day_data[$date]['week']=date('N',strtotime($date));
						isset($day_data[$date]['count']) ? $day_data[$date]['count']++ : $day_data[$date]['count']=1;
						foreach ($pay_range_conf as $k=>$val){
							if ($row['gold'] >= $val) {
								isset($day_data[$date]['distributed'][$val]) ? $day_data[$date]['distributed'][$val]++ : $day_data[$date]['distributed'][$val]=1;
								break;
							}
						}
					}
					krsort($day_data);
					$smarty->assign('day_data', $day_data);
					$smarty->assign('page',$p->show());
					break;
			}

		}else{

			//72小时内有登录的玩家
			$ts = time() - 72 * 60 * 60;
			$aliveSql = 'select count(distinct `char_id`) from `log_login` where `login_time` >' . $ts . ' or `logout_time` > ' . $ts;
			$aliver = $mysqli->count($aliveSql);
			$sql = "select sum(`gold`) as `sg` from `pay_order` $where  group by `char_id` order by `sg` desc";
			$query = $mysqli->query($sql);
			//充值元宝范围配置
			$pay_range_conf=array(500000, 100000, 50000, 20000,10000,5000,2000,1000,500,20,10);
			$sum_gold=$payer=0;
			$data=array();
			while ($row = $query->fetch_assoc()) {
				$payer++;//玩家数
				$sum_gold += $row['sg'];//总元宝数
				foreach ($pay_range_conf as $key=>$val){
					if($row['sg']>=$val){
						isset($data[$val]) ? $data[$val]++ : $data[$val]=1;
						break;
					}
				}
			}

			$register=$mongo->allCount('characters',array('creat_time'=>array('$gte'=>$start_time,'$lt'=>$end_time)));
			//首日充值情况
			$open_start_time=strtotime(date('Y-m-d',SERVER_OPEN_TIME));
			$open_end_time=$open_start_time+86400;
			$sql="select count(distinct char_id) as count,sum(gold) as gold from pay_order where ts>=$open_start_time and ts<$open_end_time";
			$list=$mysqli->findOne($sql);
			$total_first['pay_count']=empty($list['count']) ? 0 : intval($list['count']);
			$total_first['gold_count']=empty($list['gold']) ? 0 : intval($list['gold']);
			$total_first['reg_count']=$mongo->allCount('characters', array('creat_time'=>array('$gte'=>$open_start_time,'$lt'=>$open_end_time)));

			$sql="select count(distinct y,m,d) as count from pay_order $where";
			$total_count=$mysqli->count($sql);
			$p=new Page($total_count);

			$sql="select distinct y,m,d from pay_order $where order by y desc,m desc,d desc limit {$p->firstRow},{$p->listRows}";
			$query=$mysqli->query($sql);
			$max_time=0;
			$min_time=time();
			while ($row = $query->fetch_assoc()) {
				$time=mktime(0,0,0,$row['m'],$row['d'],$row['y']);
				$max_time=$time>$max_time ? $time: $max_time;
				$min_time=$time<$min_time ? $time : $min_time;
			}
			$where.=$max_time&&$min_time ? " and ts>=$min_time and ts<$max_time+86400" : '';

			//分天，玩家充值分布
			$sql="select y,m,d,char_id,sum(gold) as gold from pay_order $where group by y,m,d,char_id";
			$query=$mysqli->query($sql);
			$day_data=array();
			$item_count=count($pay_range_conf);
			while ($row = $query->fetch_assoc()) {
				$date=date('Y-m-d',mktime(0,0,0,$row['m'],$row['d'],$row['y']));
				!isset($day_data[$date]['week'])&&$day_data[$date]['week']=date('N',strtotime($date));
				isset($day_data[$date]['count']) ? $day_data[$date]['count']++ : $day_data[$date]['count']=1;
				foreach ($pay_range_conf as $k=>$val){
					if ($row['gold'] >= $val) {
						isset($day_data[$date]['distributed'][$val]) ? $day_data[$date]['distributed'][$val]++ : $day_data[$date]['distributed'][$val]=1;
						break;
					}
				}
			}
			krsort($day_data);
			$smarty->assign('day_data', $day_data);
			$smarty->assign('page',$p->show());
		}
		$smarty->assign('action_first', $action_first);
		$smarty->assign('time_type', $time_type);
		$smarty->assign('conditions', $conditions);
		$smarty->assign('action_conf', $action_conf);
		sort($pay_range_conf);
		$smarty->assign('total_first',$total_first);
		$smarty->assign('first', $first);
		$smarty->assign('payer', $payer);
		$smarty->assign('register', $register);
		$smarty->assign('sum_gold', $sum_gold);
		$smarty->assign('aliver', $aliver);
		$smarty->assign('pay_range_conf',$pay_range_conf);
		$smarty->assign('data',$data);
		break;

	case 'first_level':
		//首充等级
		$today=strtotime('today');
		$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
		$start_date=empty($_GET['start_date']) ? $open_date : my_escape_string($_GET['start_date']);
		$end_date=empty($_GET['end_date']) ? date('Y-m-d',$today) : my_escape_string($_GET['end_date']);
		$type=isset($_GET['type']) ?  trim($_GET['type']) : 'history';//时间类型
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date)+86400;
		$where="where ts>=$start_time and ts<$end_time and is_first=1 and is_test=0";

		$sql="select level,count(*) as count,sum(gold) as gold from pay_order $where group by level";
		$data=$mysqli->find($sql);
		$conditions=array(
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'type'=>$type,
			'today'=>date('Y-m-d',time()),
		);
		$time_type=array(
			'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
			'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
			'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
			'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
			'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
		);
		$smarty->assign('conditions', $conditions);
		$smarty->assign('time_type', $time_type);
		$smarty->assign('data', $data);
		break;
}
$mysqli->close();
$smarty->assign('action', $action);
$smarty->display();
?>
