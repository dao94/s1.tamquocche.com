<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
switch ($action) {
	//ng模板更新
	case 'tpl':
		$cols = isset($_REQUEST['cols']) ? intval($_REQUEST['cols']) : 1;
		$thead = '<tr><th rowspan="2" class="span2">' . __('项目') . '</th>';
		for ($j = 1; $j <= $cols; $j++) {
			$td_bg=$j%2==0 ? 'style="background-color:#D9EDF7"' : '';
			$thead .= '<th colspan="5" '.$td_bg.'> {{field}}： {{t_field.' . $j . '}}（{{minmax.' . $j . '}}）' . __('总额') . '：{{sum_money.' . $j . '}}</th>';
		}
		$thead .= '</tr><tr>';
		$tbody = '';
		for ($i = 1; $i <= $cols; $i++) {
			$td_bg=$i%2==0 ? 'style="background-color:#D9EDF7"' : '';
			$thead .= '<th '.$td_bg.'><a href="" ng-click="order = \'char_num' . $i . '\';reverse=!reverse">' . __('人数') . '</a></th><th '.$td_bg.'><a href="" ng-click="order = \'char_count' . $i . '\';reverse=!reverse">' . __('人次') . '</a></th><th '.$td_bg.'><a href="" ng-click="order = \'money_num' . $i . '\';reverse=!reverse">' . __('总额') . '</a></th><th '.$td_bg.'><a href="" ng-click="order = \'pre_money' . $i . '\';reverse=!reverse">' . __('人均') . '</a></th><th '.$td_bg.'><a href="" ng-click="order = \'bit' . $i . '\';reverse=!reverse">' . __('消费比') . '</a></th>';
			$tbody .= '<td '.$td_bg.'>{{data.char_num' . $i . '}}<ng-switch on="data.s_char_num' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></span></i><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td '.$td_bg.'>{{data.char_count' . $i . '}}<ng-switch on="data.s_char_count' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></span></i><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td '.$td_bg.'>{{data.money_num' . $i . '}}<ng-switch on="data.s_money_num' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></span></i><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td '.$td_bg.'>{{data.pre_money' . $i . '}}<ng-switch on="data.s_pre_money' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></span></i><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td '.$td_bg.'>{{data.bit' . $i . '}} %<ng-switch on="data.s_bit' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></span></i><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td>';
		}
		$thead .= '</tr>';
		$html = '<table class="table table-bordered table-condensed table-hover table-striped"><thead>' . $thead . '</thead><tbody><tr ng-repeat="data in datas | filter:item_id | orderBy:order:reverse"><td>{{data.name}}【{{data.id}}】</td>' . $tbody . '</tr></tbody></table>';
		exit($html);
		break;
		//ng数据更新
	case 'ajax':
		$params = json_decode(file_get_contents('php://input'), true);
		$field = isset($params['field']) ? trim($params['field']) : 'date';
		$money_type = isset($params['money_type']) ? $params['money_type'] : array();
		$mall_type = isset($params['mall_type']) ? $params['mall_type'] : array();
		$item_info = isset($params['item_info']) ? trim($params['item_info']) : '|';
		list($item_id) = explode('|', $item_info);
		$mall_list = array();
		$money_list = array();
		foreach ($mall_type as $mall => $mall_info) {
			if ($mall_info['checked'])
			$mall_list[] = $mall;
		}
		empty($mall_list) && ajax_return('error', __('请选择商城'));
		foreach ($money_type as $money => $money_info) {
			if ($money_info['checked'])
			$money_list[] = $money;
		}
		empty($money_list) && ajax_return('error', __('请选择货币'));
		$where = '';
		$inner = isset($params['inner']) ? intval($params['inner']) % 3 : 0;
		$start = isset($params['start']) ? intval($params['start']) : '';
		$end = isset($params['end']) ? intval($params['end']) : '';
		if ($inner != 2) {
			$where .= ' and `inner`=' . $inner;
		}
		if (is_numeric($item_id)) {
			$where .= ' and `item_id`="' . $item_id . '" ';
		}

		//获取道具列表
		$mysqli = new DbMysqli();
		$items_sql = 'select distinct `item_id` from `stat_mall` where `date`>= ' . $start . ' and `date`<=' . $end . ' and `money_type` in(' . implode(',', $money_list) . ') and `mall_type` in (' . implode(',', $mall_list) . ')' . $where;
		$items_query = $mysqli->query($items_sql);
		$data = array();
		$count = $mysqli->count('select count(distinct `' . $field . '`) from `stat_mall` where `date`>= ' . $start . ' and `date`<=' . $end . ' and `money_type` in(' . implode(',', $money_list) . ') and `mall_type` in (' . implode(',', $mall_list) . ')' . $where);
		$count == 0 && ajax_return('error', __('此条件下无数据'));
		while ($item_row = $items_query->fetch_assoc()) {
			$data[$item_row['item_id']]['name'] = __($item_row['item_id']);
			$data[$item_row['item_id']]['id'] = $item_row['item_id'];
			//对道具的所有列初始化
			for ($i = 1; $i <= $count; $i++) {
				$data[$item_row['item_id']]['char_num' . $i] = 0;
				$data[$item_row['item_id']]['s_char_num' . $i] = 0;
				$data[$item_row['item_id']]['char_count' . $i] = 0;
				$data[$item_row['item_id']]['s_char_count' . $i] = 0;
				$data[$item_row['item_id']]['money_num' . $i] = 0;
				$data[$item_row['item_id']]['s_money_num' . $i] = 0;
				$data[$item_row['item_id']]['pre_money' . $i] = 0;
				$data[$item_row['item_id']]['s_pre_money' . $i] = 0;
				$data[$item_row['item_id']]['bit' . $i] = 0;
				$data[$item_row['item_id']]['s_bit' . $i] = 0;
			}
		}
		$sql_field = $field == 'date' ? '`date`,`day`,`month`,`week`' : '`' . $field . '`';
		$sql = 'select `item_id`,sum(`char_num`) as `s_cn`,sum(`char_count`) as `s_cc`,sum(`money_num`) as `s_money`,' . $sql_field . ' from `stat_mall` where `date`>= ' . $start . ' and `date`<=' . $end . ' and `money_type` in(' . implode(',', $money_list) . ') and `mall_type` in (' . implode(',', $mall_list) . ')' . $where . ' group by `' . $field . '`,`item_id` order by `' . $field . '` desc';

		$query = $mysqli->query($sql);
		$i = 0;
		$$field = array(0 => 0);
		$minmax = array(0 => 0);
		$sum_money = array();  //总额
		while ($row = $query->fetch_assoc()) {
			if ($i == 0 || !in_array($row[$field], $$field)) {
				$i++;
				array_push($$field, $row[$field]);
				($field == 'date') && array_push($minmax, __('第') . $row['day'] . __('天') . $row['week'] . __('周') . $row['month'] . __('月'));
				$sum_money[$i] = 0;
			}
			$data[$row['item_id']]['char_num' . $i] = intval($row['s_cn']);
			$data[$row['item_id']]['char_count' . $i] = intval($row['s_cc']);
			$data[$row['item_id']]['money_num' . $i] = intval($row['s_money']);
			$sum_money[$i] += intval($row['s_money']);
			$data[$row['item_id']]['pre_money' . $i] = $row['s_cn'] == 0 ? 0 : round($row['s_money'] / $row['s_cn'], 2);
		}
		$mysqli->close();
		$datas = array();
		//横向比较排序
		foreach ($data as $item_id => $val) {
			$val['bit1'] = round($val['money_num1'] / $sum_money[1] * 100, 2);
			for ($j = 2; $j <= $count; $j++) {
				if ($val['char_num' . $j] > $val['char_num' . ($j - 1)]) {
					$val['s_char_num' . ($j - 1)] = -1;
				} elseif ($val['char_num' . $j] < $val['char_num' . ($j - 1)]) {
					$val['s_char_num' . ($j - 1)] = 1;
				}
				if ($val['char_count' . $j] > $val['char_count' . ($j - 1)]) {
					$val['s_char_count' . ($j - 1)] = -1;
				} elseif ($val['char_count' . $j] < $val['char_count' . ($j - 1)]) {
					$val['s_char_count' . ($j - 1)] = 1;
				}
				if ($val['pre_money' . $j] > $val['pre_money' . ($j - 1)]) {
					$val['s_pre_money' . ($j - 1)] = -1;
				} elseif ($val['pre_money' . $j] < $val['pre_money' . ($j - 1)]) {
					$val['s_pre_money' . ($j - 1)] = 1;
				}
				if ($val['money_num' . $j] > $val['money_num' . ($j - 1)]) {
					$val['s_money_num' . ($j - 1)] = -1;
				} elseif ($val['money_num' . $j] < $val['money_num' . ($j - 1)]) {
					$val['s_money_num' . ($j - 1)] = 1;
				}
				$val['bit' . $j] = round($val['money_num' . $j] / $sum_money[$j] * 100, 2);
				if ($val['bit' . $j] > $val['bit' . ($j - 1)]) {
					$val['s_bit' . ($j - 1)] = -1;
				} elseif ($val['bit' . $j] < $val['bit' . ($j - 1)]) {
					$val['s_bit' . ($j - 1)] = 1;
				}
			}
			$datas[] = $val;
		}
		switch ($field) {
			case 'week':
				$start_t = strtotime($start);
				$end_t = strtotime($end);
				$key = $week[1];
				while ($start_t <= $end_t) {
					$n = date('N', $end_t);
					$s = $end_t - $n * 86400 + 86400;
					$s = $s > $start_t ? $s : $start_t;
					in_array($key, $$field) && array_push($minmax, date('Ymd', $s) . '-' . date('Ymd', $end_t));
					$end_t = $s - 1;
					$key--;
				}
				break;
			case 'month':
				$start_t = strtotime($start);
				$end_t = strtotime($end);
				$key = $month[1];
				while ($start_t <= $end_t) {
					$y = date('Y', $end_t);
					$m = date('m', $end_t);
					$s = $y . $m . '01';
					$s = $s > $start ? $s : $start;
					in_array($key, $$field) && array_push($minmax, $s . '-' . date('Ymd', $end_t));
					if ($m == 1) {
						$m = 12;
						$y -= 1;
					} else {
						$m -= 1;
					}
					$m < 10 && $m = '0' . $m;
					$end_t = strtotime($y . $m . '01') - 1;
				}
				break;
		}
		exit(json_encode(array('status' => 'ok', 'data' => $datas, 'cols' => $count, 'sum_money' => $sum_money, 't_field' => $$field, 'minmax' => $minmax)));
		break;
	default:
		$smarty->assign('yesterday',date('Ymd',strtotime('yesterday')));
		$smarty->display();
		break;
}
?>
