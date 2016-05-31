<?php

//充值统计
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__ . 'Page.class.php';

$action_conf=array(
'all_player'=>__('总体'),
'new_player'=>__('新玩家'),
'old_player'=>__('滚服玩家'),
);

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

$action=empty($_GET['action']) ? 'all_player' : trim($_GET['action']);
$output=empty($_GET['output']) ? '' : trim($_GET['output']);
$start = isset($_GET['start']) ? trim($_GET['start']) : '';
$end = isset($_GET['end']) ? trim($_GET['end']) : '';
$where = ' where is_test=0 ';
$where.=$start ? ' and ts>='.strtotime($start) : '';
$where.=$end ? ' and ts<'.(strtotime($end)+86400) : '';
$today=strtotime('today');
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$time_type=array(
	'open7'=>array(__('开服7天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*6)),
	'open14'=>array(__('开服14天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*13)),
	'open30'=>array(__('开服30天'),$open_date,date('Y-m-d',SERVER_OPEN_TIME+86400*29)),
	'near30'=>array(__('近30天'),date('Y-m-d',$today-86400*30),date('Y-m-d',$today)),
	'history'=>array(__('历史数据'),$open_date,date('Y-m-d',$today)),
);
$type=empty($_GET['type']) ? 'history' : trim($_GET['type']);
$type=!array_key_exists($type, $time_type) ? 'history' : $type;
$mysqli = new DbMysqli();
$conditions=array(
	'action'=>$action,
);
$roll_up_data=$page=$roll_up_data_old=$roll_up_data_new=$same_str='';
switch ($action){
	case 'all_player':
		//总体
		$count = $mysqli->count('select count(distinct `y`,`m`,`d`) from `pay_order`' . $where);
		$p = new Page($count, 20);
		//查询日期内的每日充值统计
		$date_count_sql = 'SELECT `y` as `year`,`m` as `month`,`d` as `day`,SUM(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,count(distinct `char_id`) as `c_char` from `pay_order` ' . $where . ' GROUP BY `y`,`m`,`d` order by y desc,m desc,d desc limit ' . $p->firstRow . ',' . $p->listRows;
		$date_count_query = $mysqli->query($date_count_sql);
		////查询日期内的最大充值
		$max = 0;
		$date_count_data = array();
		while ($date_count_row = $date_count_query->fetch_assoc()) {
			$date_count_data[] = $date_count_row;
			$max = max($max, $date_count_row['s_gold']);
		}
		$smarty->assign('date_count_data', $date_count_data);
		$smarty->assign('max', $max);
		$roll_up_data = S('roll_up_data');
		if (!$roll_up_data) {
			//所有充值的月结年结
			$roll_up_sql = 'SELECT `y` as `year`,`m` as `month`,SUM(gold) as `s_gold`,COUNT(DISTINCT char_id) as `c_char`,count(order_id) as `c_order`,round(sum(`money`)/100,2) as `s_money` FROM pay_order GROUP BY y,m WITH ROLLUP';
			$roll_up_query = $mysqli->query($roll_up_sql);
			$roll_up_data = array();
			while ($roll_up_row = $roll_up_query->fetch_assoc()) {
				$roll_up_data[] = $roll_up_row;
			}
			S('roll_up_data', $roll_up_data, 10);
		}
		$page=$p->show();
		if($output=='output'){
			$date_count_sql = "SELECT `y` as `year`,`m` as `month`,`d` as `day`,SUM(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,count(distinct `char_id`) as `c_char` from `pay_order` $where GROUP BY `y`,`m`,`d` order by y desc,m desc,d desc";
			$date_count_query = $mysqli->query($date_count_sql);
			$roll_up_data = S('roll_up_data');

			$output = iconv('utf-8','gb2312',"日期\t充值人数\t充值次数\t充值金额\tARPU\t充值元宝\n");
			while ($row = $date_count_query->fetch_assoc()) {
				$output .= $row['year'] . '-' . $row['month'] . '-' . $row['day'] . "\t" . $row['c_char'] . "\t" . $row['c_order'] . "\t" . $row['s_money'] . "\t" . round($row['s_money'] / $row['c_char'], 2) . "\t" . $row['s_gold'] . "\n";
			}
			$output .= iconv('utf-8','gb2312',"小结\t充值人数\t充值次数\t充值金额\tARPU\t充值元宝\n");
			foreach ($roll_up_data as $roll_up_row) {
				$output .=$roll_up_row['year'] . "-" . $roll_up_row['month'] . "\t" . $roll_up_row['c_char'] . "\t" . $roll_up_row['c_order'] . "\t" . $roll_up_row['s_money'] . "\t" . round($roll_up_row['s_gold'] / $roll_up_row['c_char'], 2) . "\t" . $roll_up_row['s_gold'] . "\n";
			}
			header("Pragma:public");
			header("Expires:0");
			header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
			header("Content-Type:application/force-download");
			header("Content-Type:application/vnd.ms-execl;charset=gb2312");
			header("Content-Type:application/octet-stream");
			header("Content-Type:application/download");
			header('Content-Disposition:attachment;filename="pay_statistics.xls"');
			header("Content-Transfer-Encoding:binary");
			exit($output);
		}
		break;
	case 'new_player':
		//新玩家
		$sql="select * from log_old_account";
		$result=$mysqli->query($sql);
		$users=array();
		while($result && $row=$result->fetch_assoc()){
			$users[]="'{$row['account']}'";;
		}
		$same_str='';//滚服玩家 排除掉
		if(!empty($users)){
			$same_str=implode(',',$users);
			$same_str=$same_str=='' ? ' ' : " and account not in ($same_str) " ;
		}
		$sql="select count(distinct `y`,`m`,`d`) from pay_order $where $same_str ";
		$count = $mysqli->count($sql);
		$p = new Page($count, 20);
		//查询日期内的每日充值统计
		$date_count_sql = 'SELECT `y` as `year`,`m` as `month`,`d` as `day`,SUM(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,count(distinct `char_id`) as `c_char` from `pay_order` ' . $where .$same_str.'  GROUP BY `y`,`m`,`d` order by y desc,m desc,d desc limit ' . $p->firstRow . ',' . $p->listRows;
		$date_count_query = $mysqli->query($date_count_sql);
		////查询日期内的最大充值
		$max = 0;
		$date_count_data = array();
		while ($date_count_row = $date_count_query->fetch_assoc()) {
			$date_count_data[] = $date_count_row;
			$max = max($max, $date_count_row['s_gold']);
		}
		$smarty->assign('date_count_data', $date_count_data);
		$smarty->assign('max', $max);
		$roll_up_data_new = S('roll_up_data_new');
		if (!$roll_up_data_new) {
			//所有充值的月结年结
			$roll_up_sql = 'SELECT `y` as `year`,`m` as `month`,SUM(gold) as `s_gold`,COUNT(DISTINCT char_id) as `c_char`,count(order_id) as `c_order`,round(sum(`money`)/100,2) as `s_money` FROM pay_order where true '.$same_str.' GROUP BY y,m WITH ROLLUP';
			$result = $mysqli->query($roll_up_sql);
			$roll_up_data_new=array();
			while($result && $row=$result->fetch_assoc()){
				$roll_up_data_new[]=$row;
			}
			S('roll_up_data_new', $roll_up_data_new, 60);
		}
		$page=$p->show();
		if($output=='output'){
			$date_count_sql = 'SELECT `y` as `year`,`m` as `month`,`d` as `day`,SUM(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,count(distinct `char_id`) as `c_char` from `pay_order`'. $where.$same_str.'  GROUP BY `y`,`m`,`d` order by y desc,m desc,d desc';
			$date_count_query = $mysqli->query($date_count_sql);
			$roll_up_data_new = S('roll_up_data_new');

			$output = iconv('utf-8','gb2312',"日期\t充值人数\t充值次数\t充值金额\tARPU\t充值元宝\n");
			while ($row = $date_count_query->fetch_assoc()) {
				$output .= $row['year'] . '-' . $row['month'] . '-' . $row['day'] . "\t" . $row['c_char'] . "\t" . $row['c_order'] . "\t" . $row['s_money'] . "\t" . round($row['s_money'] / $row['c_char'], 2) . "\t" . $row['s_gold'] . "\n";
			}
			$output .= iconv('utf-8','gb2312',"小结\t充值人数\t充值次数\t充值金额\tARPU\t充值元宝\n");
			foreach ($roll_up_data_new as $roll_up_row) {
				$output .=$roll_up_row['year'] . "-" . $roll_up_row['month'] . "\t" . $roll_up_row['c_char'] . "\t" . $roll_up_row['c_order'] . "\t" . $roll_up_row['s_money'] . "\t" . round($roll_up_row['s_gold'] / $roll_up_row['c_char'], 2) . "\t" . $roll_up_row['s_gold'] . "\n";
			}
			header("Pragma:public");
			header("Expires:0");
			header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
			header("Content-Type:application/force-download");
			header("Content-Type:application/vnd.ms-execl;charset=gb2312");
			header("Content-Type:application/octet-stream");
			header("Content-Type:application/download");
			header('Content-Disposition:attachment;filename="pay_statistics.xls"');
			header("Content-Transfer-Encoding:binary");
			exit($output);
		}
		break;
	case 'old_player':
		//滚服
		$sql="select * from log_old_account";
		$result=$mysqli->query($sql);
		$users=array();
		while($result && $row=$result->fetch_assoc()){
			$users[]="'{$row['account']}'";
		}
		$same_str='';//滚服玩家 排除掉
		if(!empty($users)){
			$same_str=implode(',',$users);
			$same_str=$same_str=='' ? ' ' : " and account in ($same_str) " ;
		}else{
			$same_str=$same_str=='' ? " and false " : ' ' ;
		}
		$sql="select count(distinct `y`,`m`,`d`) from pay_order $where $same_str ";
		$count = $mysqli->count($sql);
		$p = new Page($count, 20);
		//查询日期内的每日充值统计
		$date_count_sql = 'SELECT `y` as `year`,`m` as `month`,`d` as `day`,SUM(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,count(distinct `char_id`) as `c_char` from `pay_order` ' . $where .$same_str.' GROUP BY `y`,`m`,`d` order by y desc,m desc,d desc limit ' . $p->firstRow . ',' . $p->listRows;
		$date_count_query = $mysqli->query($date_count_sql);
		////查询日期内的最大充值
		$max = 0;
		$date_count_data = array();
		while ($date_count_row = $date_count_query->fetch_assoc()) {
			$date_count_data[] = $date_count_row;
			$max = max($max, $date_count_row['s_gold']);
		}
		$smarty->assign('date_count_data', $date_count_data);
		$smarty->assign('max', $max);
		$roll_up_data_old = S('roll_up_data_old');
		if (!$roll_up_data_old) {
			//所有充值的月结年结
			$roll_up_sql = 'SELECT `y` as `year`,`m` as `month`,SUM(gold) as `s_gold`,COUNT(DISTINCT char_id) as `c_char`,count(order_id) as `c_order`,round(sum(`money`)/100,2) as `s_money` FROM pay_order where true '.$same_str.' GROUP BY y,m WITH ROLLUP';
			$result = $mysqli->query($roll_up_sql);
			$roll_up_data_old=array();
			while($result && $row=$result->fetch_assoc()){
				$roll_up_data_old[]=$row;
			}
			S('roll_up_data_old', $roll_up_data_old, 60);
		}
		$page=$p->show();
		if($output=='output'){
			$date_count_sql = 'SELECT `y` as `year`,`m` as `month`,`d` as `day`,SUM(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,count(distinct `char_id`) as `c_char` from `pay_order`'. $where.$same_str.' GROUP BY `y`,`m`,`d` order by y desc,m desc,d desc';
			$date_count_query = $mysqli->query($date_count_sql);
			$roll_up_data_old = S('roll_up_data_old');

			$output = iconv('utf-8','gb2312',"日期\t充值人数\t充值次数\t充值金额\tARPU\t充值元宝\n");
			while ($row = $date_count_query->fetch_assoc()) {
				$output .= $row['year'] . '-' . $row['month'] . '-' . $row['day'] . "\t" . $row['c_char'] . "\t" . $row['c_order'] . "\t" . $row['s_money'] . "\t" . round($row['s_money'] / $row['c_char'], 2) . "\t" . $row['s_gold'] . "\n";
			}
			$output .= iconv('utf-8','gb2312',"小结\t充值人数\t充值次数\t充值金额\tARPU\t充值元宝\n");
			foreach ($roll_up_data_old as $roll_up_row) {
				$output .=$roll_up_row['year'] . "-" . $roll_up_row['month'] . "\t" . $roll_up_row['c_char'] . "\t" . $roll_up_row['c_order'] . "\t" . $roll_up_row['s_money'] . "\t" . round($roll_up_row['s_gold'] / $roll_up_row['c_char'], 2) . "\t" . $roll_up_row['s_gold'] . "\n";
			}
			header("Pragma:public");
			header("Expires:0");
			header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
			header("Content-Type:application/force-download");
			header("Content-Type:application/vnd.ms-execl;charset=gb2312");
			header("Content-Type:application/octet-stream");
			header("Content-Type:application/download");
			header('Content-Disposition:attachment;filename="pay_statistics.xls"');
			header("Content-Transfer-Encoding:binary");
			exit($output);
		}
		break;
}

$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('roll_up_data', $roll_up_data);
$smarty->assign('roll_up_data_old', $roll_up_data_old);
$smarty->assign('roll_up_data_new', $roll_up_data_new);
$smarty->assign('page', $page);
$smarty->assign('start', $start);
$smarty->assign('end', $end);
$smarty->assign('time_type', $time_type);
$smarty->assign('type', $type);
$smarty->display();
$mysqli->close();
?>
