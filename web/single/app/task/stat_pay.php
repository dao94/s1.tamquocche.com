<?php

/*
 * php stat_pay.php --task=pay_dist  执行时间：每周一一执行
 * php stat_pay.php --task=pay_rate  执行时间：每天一执行
 */

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__ . 'function.php';
include __CLASSES__ . 'Task.class.php';

if(!isset($argc) || $argc<2) exit('Invalid request');
array_shift($argv);
$task = new Task();
$params = $task->parseArgs($argv); //参数数组
$task->name = $params['task'];
$mysqli = $task->mysqli();
switch ($params['task']) {
	//充值分布
	case 'pay_dist':
		//$params['date']为周结束后的周一 比如要统计10月26号这周充值分布，则$params['date']=20131028
		$date=empty($params['date']) ? date('Ymd',strtotime('today')) : intval($params['date']);
		if(date('N',strtotime($date))!=1){
			exit('Not Time');
		}
		//开服第几周
		$week = ceil((strtotime($date)- SERVER_OPEN_TIME)/(7*86400));
		$end=strtotime($date);
		$start=$end-7*86400;
		$sql = 'select `char_id`,sum(`gold`) as `sg` from `pay_order` where `ts` <' . $end . ' group by `char_id` order by `sg` desc ';

		$query = $mysqli->query($sql);
		//按照元宝计算
		$gts = array(500000, 200000, 100000, 50000, 20000, 10000, 5000, 2000, 1000, 0);
		foreach ($gts as $gt) {
			${'gt' . $gt} = 0;
		}
		$key = 0;
		$payer = 0;
		while ($row = $query->fetch_assoc()) {
			for ($i = $key; $i < 10; $i++) {
				if ($row['sg'] > $gts[$i]) {
					${'gt' . $gts[$i]}++;
					break;
				}
				$key++;
			}
			$payer++;
		}
		include __CLASSES__ . 'Mdb.class.php';
		$mongo = new Mdb();
		$count = $mongo->allCount('characters', array('creat_time'=>array('$lt'=>$end)));
		$mongo->close();
		$unpayer = $count - $payer;
		$week_start=date('Y-m-d',$start);
		$insert_sql = sprintf('replace into `pay_distributed`(`week`,`week_start`,`unpayer`,`payer`,`gt0`,`gt100`,`gt200`,`gt500`,`gt1000`,`gt2000`,`gt5000`,`gt10000`,`gt20000`,`gt50000`) values(%d,"%s",%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d)', $week, $week_start, $unpayer, $payer, $gt0, $gt1000, $gt2000, $gt5000, $gt10000, $gt20000, $gt50000, $gt100000, $gt200000, $gt500000);
		$mysqli->query($insert_sql);
		$mysqli->close();
		break;
		//付费率
	case 'pay_rate':
		$date = isset($params['date']) ? trim($params['date']) : date('Ymd', time() - 86400);
		$start = strtotime($date);
		$end = $start + 86400;
		$open_time_0 = strtotime(date('Y-m-d', SERVER_OPEN_TIME)); //开服单日的零点
		$day = ceil(($start - $open_time_0) / 86400) + 1;
		//总注册用户 account_data char_id exit count()
		include __CLASSES__ . 'Mdb.class.php';
		$mongo = new Mdb();

		//总体玩家
		$register=$mongo->allCount('characters', array('creat_time'=>array('$lt'=>$end)));
		//总充值用户
		$payer = $mysqli->count("select count(distinct `char_id`) as count from `pay_order` where ts<$end");
		$unpayer = $register - $payer;
		//活跃用户数
		$mongo->selectDb(MONGO_PERFIX . '4');
		$alivers = $mongo->find('account_data', array('char_id' => array('$exists' => true), 'last_login' => array('$gte' => $start - 2 * 86400, 'lt' => $end)), array('char_id'));
		$mongo->close();
		$aliver_char = array();
		foreach ($alivers as $aliver) {
			$aliver_char[] = $aliver['char_id'];
		}
		//活跃用户充值人数
		$a_payer = $mysqli->count('select count(distinct `char_id`) as count from `pay_order` where `char_id` in (' . implode(',', $aliver_char) . ')');
		//活跃非充值用户
		$a_unpayer = count($aliver_char) - $a_payer;
		//充值金额区段
		$limits = array(1 => array(1, 1000), 2 => array(1000, 5000), 3 => array(5000, 10000), 4 => array(10000, 50000), 5 => array(50000, 100000), 6 => array(100000, 10000000));
		foreach ($limits as $key => $limit) {
			${'pay' . $key} = $mysqli->count('select char_id,sum(`gold`) as `sum_gold` from `pay_order` group by `char_id` having `sum_gold`>=' . $limit[0] . ' and `sum_gold`<' . $limit[1]);
		}
		//新注册用户
		$np_query = $mysqli->query('select `char_id` from `log_login` where `is_first`=1 and `login_time`>=' . $start . ' and `login_time`<' . $end);
		$np_char = array();
		while ($np_row = $np_query->fetch_assoc()) {
			$np_char[] = $np_row['char_id'];
		}
		if (count($np_char) == 0) {
			$np_payer = 0;
			$np_unpayer = 0;
		} else {
			//新注册用户的付费用户
			$np_payer = $mysqli->count('select count(distinct `char_id`) as count from `pay_order` where `char_id` in (' . implode(',', $np_char) . ')');
			$np_unpayer = count($np_char) - $np_payer;
		}
		$mysqli->query('insert into `stat_payrate`(`date`,`day`,`unpayer`,`payer`,`np_unpayer`,`np_payer`,`a_unpayer`,`a_payer`,`pay1`,`pay2`,`pay3`,`pay4`,`pay5`,`pay6`) values("' . $date . '",' . $day . ',' . $unpayer . ',' . $payer . ',' . $np_unpayer . ',' . $np_payer . ',' . $a_unpayer . ',' . $a_payer . ',' . $pay1 . ',' . $pay2 . ',' . $pay3 . ',' . $pay4 . ',' . $pay5 . ',' . $pay6 . ')');

		//滚服玩家
		$sql="select * from log_old_account";
		$result=$mysqli->query($sql);
		$users=$users_mdb=array();
		while($result && $row=$result->fetch_assoc()){
			$users[]="'{$row['account']}'";
			$users_mdb[]="{$row['account']}";
		}
		$same_str_old='';//滚服玩家 排除掉
		if(!empty($users)){
			$same_str_old=implode(',',$users);
			$same_str_old=$same_str_old=='' ? ' ' : " and account in ($same_str_old) " ;
		}
		if($same_str_old!=''){
			$register=$mongo->allCount('characters', array('creat_time'=>array('$lt'=>$end),'account'=>array('$in'=>$users_mdb)));
			//总充值用户
			$payer = $mysqli->count("select count(distinct `char_id`) as count from `pay_order` where ts<$end $same_str_old");
			$unpayer = $register - $payer;
			//活跃用户数
			$mongo->selectDb(MONGO_PERFIX . '4');
			$alivers = $mongo->find('account_data', array('char_id' => array('$exists' => true), 'last_login' => array('$gte' => $start - 2 * 86400, 'lt' => $end),'account'=>array('$in'=>$users_mdb)), array('char_id'));
			$mongo->close();
			$aliver_char = array();
			if(!empty($aliver_char)){
				foreach ($alivers as $aliver) {
					$aliver_char[] = $aliver['char_id'];
				}
			}
			//活跃用户充值人数
			$a_payer = $mysqli->count('select count(distinct `char_id`) as count from `pay_order` where `char_id` in (' . implode(',', $aliver_char) . ')'.$same_str_old);
			//活跃非充值用户
			$a_unpayer = count($aliver_char) - $a_payer;
			//充值金额区段
			$limits = array(1 => array(1, 1000), 2 => array(1000, 5000), 3 => array(5000, 10000), 4 => array(10000, 50000), 5 => array(50000, 100000), 6 => array(100000, 10000000));
			foreach ($limits as $key => $limit) {
				${'pay' . $key} = $mysqli->count('select char_id,sum(`gold`) as `sum_gold` from `pay_order` where true '.$same_str_old.' group by `char_id` having `sum_gold`>=' . $limit[0] . ' and `sum_gold`<' . $limit[1]);
			}
			//新注册用户
			$np_query = $mysqli->query('select `char_id` from `log_login` where `is_first`=1 and `login_time`>=' . $start . ' and `login_time`<' . $end.$same_str_old);
			$np_char = array();
			while ($np_row = $np_query->fetch_assoc()) {
				$np_char[] = $np_row['char_id'];
			}
			if (count($np_char) == 0) {
				$np_payer = 0;
				$np_unpayer = 0;
			} else {
				//新注册用户的付费用户
				$np_payer = $mysqli->count('select count(distinct `char_id`) as count from `pay_order` where `char_id` in (' . implode(',', $np_char) . ')'.$same_str_old);
				$np_unpayer = count($np_char) - $np_payer;
			}
			$mysqli->query('insert into `stat_payrate_gunfu`(`date`,`day`,`unpayer`,`payer`,`np_unpayer`,`np_payer`,`a_unpayer`,`a_payer`,`pay1`,`pay2`,`pay3`,`pay4`,`pay5`,`pay6`) values("' . $date . '",' . $day . ',' . $unpayer . ',' . $payer . ',' . $np_unpayer . ',' . $np_payer . ',' . $a_unpayer . ',' . $a_payer . ',' . $pay1 . ',' . $pay2 . ',' . $pay3 . ',' . $pay4 . ',' . $pay5 . ',' . $pay6 . ')');
			$mysqli->close();
		}
		break;
	default:
		break;
}
?>