<?php

//充值排名
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__ . 'Page.class.php';
include __CONFIG__ . 'game_config.php';
include __CLASSES__ . 'Mdb.class.php';

$type = isset($_GET['type']) ? trim($_GET['type']) : 'total';
$mysqli = new DbMysqli();

switch ($type) {
	case 'output':
		$rankings_sql = 'select account,char_id,char_name,level,sum(`money`/100) as `s_money`,sum(`gold`) as `s_gold`,count(`order_id`) as `c_order`,  from_unixtime(max(`ts`),"%Y-%m-%d %H:%i:%s") as `max_ts` from pay_order group by `char_id` order by `s_gold` desc';
		$rankings_query = $mysqli->query($rankings_sql);

		header("Pragma:public");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl;charset=gb2312");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="pay_rank.xls"');
		header("Content-Transfer-Encoding:binary");
		$output = "排行\t账号\t角色ID\t角色\t等级\t充值总额\t元宝总额\t充值次数\t最后充值时间\t职业\n";
		
		$i = 1;
		$mdb=new Mdb();
		while ($rank_row = $rankings_query->fetch_assoc()) {
			//玩家等级 职业
			$mdb->selectDb(MONGO_PERFIX.$rank_row['char_id']%4);
			$char=$mdb->findOne('characters', array('_id'=>floatval($rank_row['char_id'])), array('level','occ'));
			if($char){
				$rank_row['level']=isset($char['level']) ? $char['level'] : '';
				$rank_row['occ']=isset($occ_conf[$char['occ']]) ? $occ_conf[$char['occ']] : '';
			}
			$output .= $i . "\t" . implode("\t", $rank_row) . "\n";
			$i++;
		}
		$mysqli->close();
		exit($output);
		break;
	case 'total':
	default:
		//开始日期  结束日期
		$start = isset($_GET['start']) ? trim($_GET['start']) : '';
		$end = isset($_GET['end']) ? trim($_GET['end']) : '';
		$char_type = isset($_GET['char_type']) ? intval($_GET['char_type']) : 0;
		$char_info = isset($_GET['char_info']) ? trim($_GET['char_info']) : '';

		$pay_rank_data = S('pay_rank');
		if (!$pay_rank_data) {
			//充值总用户数
			$payerQuery = $mysqli->query('select distinct `char_id` from `pay_order`');
			$paychars = array();
			while ($payer = $payerQuery->fetch_assoc()) {
				$paychars[] = $payer['char_id'];
			}
			$c_payer = count($paychars);
			$gt200payerQuery = $mysqli->query('select `char_id` from `pay_order` group by `char_id` having sum(`gold`)>=2000');
			$gt200paychars = array();
			while ($gt200payer = $gt200payerQuery->fetch_assoc()) {
				$gt200paychars[] = $gt200payer['char_id'];
			}
			$c_gt200payer = count($gt200paychars);
			//充值玩家5天有登陆的玩家数
			$time = time() - 5 * 86400;
			$c_notloginPayer = $mysqli->count('select count(distinct `char_id`) as `count` from `log_login` where `login_time`>=' . $time . ' and `char_id` in (' . implode(',', $paychars) . ')');
			$c_notlogingt200Payer = $mysqli->count('select count(distinct `char_id`) as `count` from `log_login` where `login_time`>=' . $time . ' and `char_id` in (' . implode(',', $gt200paychars) . ')');
			$pay_rank_data['c_payer'] = $c_payer;
			$pay_rank_data['c_notloginpayer'] = $c_payer-$c_notloginPayer;
			$pay_rank_data['c_notlogingt200payer'] = $c_gt200payer-$c_notlogingt200Payer;
			$pay_rank_data['c_gt200payer'] = $c_gt200payer;
			S('pay_rank', $pay_rank_data,10);
		}
		
		$mongo = new Mdb();
		$rankings = array();
		$today=strtotime('today');
		$where = '';

		if (!empty($char_info)) {
			$where = $char_type ? '`char_name` = "' . $char_info . '"' : '`account` = "' . $char_info . '"';
			$payerList = $mysqli->find('select account,char_name,char_id,level,sum(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,  max(`ts`) as `max_ts` from `pay_order` where ' . $where.' group by char_id');
			if (!empty($payerList)) {
				$payerQuery = $mysqli->query('select sum(`gold`) as `s_gold`,`char_id` from pay_order group by `char_id` order by `s_gold` desc ');
				$i = 0;
				while ($row = $payerQuery->fetch_assoc()) {
					++$i;
					foreach ($payerList as $payerInfo){
						if ($row['char_id'] == $payerInfo['char_id']) {
							$mongo->selectDb(MONGO_PERFIX . ($row['char_id'] % 4));
							$moneyList = $mongo->findOne('character_bag', array('_id' => floatval($row['char_id'])), array('moneyList' => true));
							$payerInfo['l_gold'] = is_null($moneyList['moneyList']) ? 0 : $moneyList['moneyList'][2];
							$loginInfo = $mongo->findOne('characters', array('_id' => floatval($row['char_id'])), array('loginTime', 'leaveTime','occ'));
							$lastLoginTime = isset($loginInfo['loginTime']) ? $loginInfo['loginTime'] : 0;
							$payerInfo['no_pay_days'] = intval(($today - strtotime(date('Ymd',$payerInfo['max_ts']))) / 86400);
							$payerInfo['no_login_days'] = intval(($today - strtotime(date('Ymd',$lastLoginTime))) / 86400);
							$payerInfo['rank'] = $i;
							$payerInfo['occ']=isset($occ_conf[$loginInfo['occ']]) ? $occ_conf[$loginInfo['occ']] : '';
							$rankings[] = $payerInfo;
						}
					}
				}
			}
			$p = new Page(0);
		} else {
			if (!empty($start) && !empty($end)) {
				$start_t = strtotime($start);
				$end_t = strtotime($end . ' 23:59:59');
				$where .= ' where `ts` >= ' . $start_t . ' and `ts` <= ' . $end_t;
			}
			//充值玩家数
			$count = $mysqli->count('select count(distinct `char_id`) as `count` from `pay_order`' . $where);
			$p = new Page($count, 20);
			//玩家充值排名
			$rankings_sql = 'select account,char_name,char_id,level,sum(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order`,  max(`ts`) as `max_ts` from pay_order ' . $where . ' group by `char_id` order by `s_gold` desc limit ' . $p->firstRow . ',' . $p->listRows;
			$rankings_query = $mysqli->query($rankings_sql);
			$i = 1;
			while ($row = $rankings_query->fetch_assoc()) {
				$row['rank'] = $p->firstRow + ($i++);
				$mongo->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$moneyList = $mongo->findOne('character_bag', array('_id' => floatval($row['char_id'])), array('moneyList' => true));
				$row['l_gold'] = is_null($moneyList['moneyList']) ? 0 : $moneyList['moneyList'][2];
				$loginInfo = $mongo->findOne('characters', array('_id' => floatval($row['char_id'])), array('level','loginTime', 'leaveTime','occ'));
				$lastLoginTime = isset($loginInfo['loginTime']) ? $loginInfo['loginTime'] : 0;
				$row['no_pay_days'] = intval(($today - strtotime(date('Ymd',$row['max_ts']))) / 86400);
				$row['no_login_days'] = intval(($today - strtotime(date('Ymd',$lastLoginTime))) / 86400);
				$row['level']=$loginInfo['level'];
				$row['occ']=isset($occ_conf[$loginInfo['occ']]) ? $occ_conf[$loginInfo['occ']] : '';
				$rankings[] = $row;
			}
		}
		$mongo->close();
		//充值总额
		$sum_gold_query = $mysqli->query('select sum(`gold`) as `sum_gold` from pay_order');
		$sum_gold_row = $sum_gold_query->fetch_assoc();
		$smarty->assign('start', $start);
		$smarty->assign('pay_rank_data', $pay_rank_data);
		$smarty->assign('end', $end);
		$smarty->assign('char_type', $char_type);
		$smarty->assign('char_info', $char_info);
		$smarty->assign('rankings', $rankings);
		$smarty->assign('sum_gold', $sum_gold_row['sum_gold']);
		break;
	case 'single':
		//日期
		$day = isset($_GET['day']) ? trim($_GET['day']) : date('Y-m-d');
		$where = '';
		$start_t = strtotime($day);
		$end_t = strtotime($day . ' 23:59:59');
		$where .= ' where `ts` >= ' . $start_t . ' and `ts` <= ' . $end_t;
		//充值总额
		$sum_gold_query = $mysqli->query('select sum(`gold`) as `sum_gold` from pay_order' . $where);
		$sum_gold_row = $sum_gold_query->fetch_assoc();
		//充值玩家数
		$count = $mysqli->count('select count(distinct `char_id`) as `count` from `pay_order`' . $where);
		$p = new Page($count, 20);
		//玩家充值排名
		$rankings_sql = 'select account,char_name,char_id,sum(`gold`) as `s_gold`,round(sum(`money`)/100,2) as `s_money`,count(`order_id`) as `c_order` from pay_order ' . $where . ' group by `char_id` order by `s_gold` desc limit ' . $p->firstRow . ',' . $p->listRows;
		$rankings_query = $mysqli->query($rankings_sql);
		$smarty->assign('p_firstRow', $p->firstRow);
		$smarty->assign('rankings_query', $rankings_query);
		$smarty->assign('sum_gold', $sum_gold_row['sum_gold']);
		$smarty->assign('day', $day);
		break;
	
	case 'order':
		//获取某玩家详细订单
		$char_id=empty($_POST['char_id']) ? 0 : floatval($_POST['char_id']);
		if(empty($char_id)){
			ajax_return(0, __('参数错误'));
		}
		$sql="select ts,money,gold,char_name,level,order_id from pay_order where char_id=$char_id order by ts desc";
		$result=$mysqli->query($sql);
		$data=array();
		while ($row=$result->fetch_assoc()){
			$row['ts']=date('Y-m-d H:i:s',$row['ts']);
			$row['money']=$row['money']/100;
			$data[]=$row;
		}
		ajax_return(1, 'order list',$data);
		break;
}
$mysqli->close();
$smarty->assign('type', $type);
$smarty->assign('page', $p->show());
$smarty->display();
?>
