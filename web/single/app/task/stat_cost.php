<?php

/*
 * @author wangyi
 * @date 2013-07-22 09:59:26
 * 消费相关统计
 * cost 消费产出监控
 * php stat_cost.php --task=cost --date=2013-06-01
 * mall 商城监控
 * php stat_cost.php --task=mall --date=2013-06-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__ . 'function.php';
include __CLASSES__ . 'Task.class.php';

if ($argc < 2)
exit('Invalid request');
array_shift($argv);
$task = new Task();
$params = $task->parseArgs($argv); //参数数组
$task->name = $params['task'];
$mysqli = $task->mysqli();
switch ($params['task']) {
	//消费产出监控
	case 'cost':
		//内部号
		$inner_sql = 'select `char_id` from `internal_account` where `type`=1 and `status`=1';
		$inner_query = $mysqli->query($inner_sql);
		$inners = array();
		while ($inner_row = $inner_query->fetch_assoc()) {
			$inners[] = $inner_row['char_id'];
		}

		$where = empty($inners) ? '' : ' and `char_id` in(' . implode(',', $inners) . ')';
		$unwhere = empty($inners) ? '' : ' and `char_id` not in(' . implode(',', $inners) . ')';
		$date = isset($params['date']) ? trim($params['date']) : date('Ymd', time() - 86400);
		$start = strtotime($date);
		$end = $start + 86400;
		$diff_year = date('Y', $start) - date('Y', SERVER_OPEN_TIME);
		$month = $diff_year * 12 + date('n', $start) - date('n', SERVER_OPEN_TIME) + 1;
		$open_time_0 = strtotime(date('Y-m-d', SERVER_OPEN_TIME)); //开服单日的零点
		$open_week_0 = $open_time_0 - 86400 * (date('N', $open_time_0) - 1);  //开服周一零点
		// $week = ceil(($start - $open_week_0) / 86400 / 7) + 1;
		$week = floor(($start - $open_week_0) / 86400 / 7) + 1;
		$day = ceil(($start - $open_time_0) / 86400) + 1;
		//查询内部号的消费产出情况
		if (!empty($inners)) {
			$inner_cost_sql = 'insert into `stat_cost`(`date`,`money_type`,`io`,`type`,`inner`,`money_num`,`char_count`,`char_num`,`day`,`week`,`month`) select "' . $date . '" as `date`,`money_type`,`io`,`type`,1,sum(`money_num`) as `sg`,count(`char_id`) as `char_count`,count(distinct `char_id`) as `char_num`,' . $day . ' as `day`,' . $week . ' as `week`,' . $month . ' as `month` from `log_money` where `time`>=' . $start . ' and `time` <=' . $end . $where . ' group by `io`,`money_type`,`type`';
			$inner_cost_query = $mysqli->query($inner_cost_sql);
		}
		//非内部号
		$uninner_cost_sql = 'insert into `stat_cost`(`date`,`money_type`,`io`,`type`,`inner`,`money_num`,`char_count`,`char_num`,`day`,`week`,`month`) select "' . $date . '" as `date`,`money_type`,`io`,`type`,0,sum(`money_num`) as `sg`,count(`char_id`) as `char_count`,count(distinct `char_id`) as `char_num`,' . $day . ' as `day`,' . $week . ' as `week`,' . $month . ' as `month` from `log_money` where `time`>=' . $start . ' and `time` <=' . $end . $unwhere . ' group by `io`,`money_type`,`type`';
		$uninner_cost_query = $mysqli->query($uninner_cost_sql);
		$mysqli->close();
		break;
		//商城监控
	case 'mall':
		//内部号
		$inner_sql = 'select `char_id` from `internal_account` where `type`=1 and `status`=1';
		$inner_query = $mysqli->query($inner_sql);
		$inners = array();
		while ($inner_row = $inner_query->fetch_assoc()) {
			$inners[] = $inner_row['char_id'];
		}
		$where = empty($inners) ? '' : ' and `char_id` in(' . implode(',', $inners) . ')';
		$unwhere = empty($inners) ? '' : ' and `char_id` not in(' . implode(',', $inners) . ')';
		$date = isset($params['date']) ? trim($params['date']) : date('Ymd', time() - 86400);
		$start = strtotime($date);
		$end = $start + 86400;
		$diff_year = date('Y', $start) - date('Y', SERVER_OPEN_TIME);
		$month = $diff_year * 12 + date('n', $start) - date('n', SERVER_OPEN_TIME) + 1;
		$open_time_0 = strtotime(date('Y-m-d', SERVER_OPEN_TIME)); //开服单日的零点
		$open_week_0 = $open_time_0 - 86400 * (date('N', $open_time_0) - 1);  //开服周一零点
		//    $week = ceil(($start - $open_week_0) / 86400 / 7) + 1;

		$week = floor(($start - $open_week_0) / 86400 / 7) + 1;

		$day = ceil(($start - $open_time_0) / 86400) + 1;
		//内部号商城购买情况
		if (!empty($inners)) {
			$inner_mall_sql = 'insert into `stat_mall`(`date`,`item_id`,`mall_type`,`money_type`,`inner`,`money_num`,`char_count`,`char_num`,`day`,`week`,`month`) select "' . $date . '" as `date`,`item_id`,`mall_type`,`money_type`,1,sum(`money_num`) as `sg`,count(`char_id`) as `char_count`,count(distinct `char_id`) as `char_num`,' . $day . ' as `day`,' . $week . ' as `week`,' . $month . ' as `month` from `log_mall` where `time`>=' . $start . ' and `time` <=' . $end . $where . ' group by `item_id`,`money_type`,`mall_type`';
			$inner_mall_query = $mysqli->query($inner_mall_sql);
		}
		//外部号商城购买情况
		$uninner_mall_sql = 'insert into `stat_mall`(`date`,`item_id`,`mall_type`,`money_type`,`inner`,`money_num`,`char_count`,`char_num`,`day`,`week`,`month`) select "' . $date . '" as `date`,`item_id`,`mall_type`,`money_type`,0,sum(`money_num`) as `sg`,count(`char_id`) as `char_count`,count(distinct `char_id`) as `char_num`,' . $day . ' as `day`,' . $week . ' as `week`,' . $month . ' as `month` from `log_mall` where `time`>=' . $start . ' and `time` <=' . $end . $unwhere . ' group by `item_id`,`money_type`,`mall_type`';
		$uninner_mall_query = $mysqli->query($uninner_mall_sql);
		$mysqli->close();
		break;
}
?>
