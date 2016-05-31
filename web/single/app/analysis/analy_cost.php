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
			$td_bg=$j%2==0 ? ' style="background-color:#D9EDF7"' : '';
			$thead .= '<th colspan="5"'.$td_bg.'> {{field}}：{{t_field.' . $j . '}}（{{minmax.' . $j . '}}）' . __('总额') . '：{{sum_money.' . $j . '}}</th>';
		}
		$thead .= '</tr><tr>';
		$tbody = '';
		for ($i = 1; $i <= $cols; $i++) {
			$td_bg=$i%2==0 ? ' style="background-color:#D9EDF7"' : '';
			$thead .= '<th'.$td_bg.'><a href="javascript:;" ng-click="order = \'char_num' . $i . '\';reverse=!reverse">' . __('人数') . '</a></th><th'.$td_bg.'><a href="javascript:;" ng-click="order = \'char_count' . $i . '\';reverse=!reverse">' . __('人次') . '</a></th><th'.$td_bg.'><a href="javascript:;" ng-click="order = \'money_num' . $i . '\';reverse=!reverse">' . __('总额') . '</a></th><th'.$td_bg.'><a href="javascript:;" ng-click="order = \'pre_money' . $i . '\';reverse=!reverse">' . __('人均') . '</a></th><th'.$td_bg.'><a href="javascript:;" ng-click="order = \'bit' . $i . '\';reverse=!reverse">' . __('消费比') . '</a></th>';
			$tbody .= '<td'.$td_bg.'>{{data.char_num' . $i . '}}<ng-switch on="data.s_char_num' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></i></span><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td'.$td_bg.'>{{data.char_count' . $i . '}}<ng-switch on="data.s_char_count' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></i></span><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td'.$td_bg.'>{{data.money_num' . $i . '}}<ng-switch on="data.s_money_num' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></i></span><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td'.$td_bg.'>{{data.pre_money' . $i . '}}<ng-switch on="data.s_pre_money' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></i></span><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td><td'.$td_bg.'>{{data.bit' . $i . '}} %<ng-switch on="data.s_bit' . $i . '"><span ng-switch-when="1"><i class="icon-arrow-up"></i></span><span ng-switch-when="-1"><i class="icon-arrow-down icon-white"></i></span></ng-switch></td>';
		}
		$thead .= '</tr>';
		$html = '<table class="table table-bordered table-condensed table-hover"><thead>' . $thead . '</thead><tbody><tr  ng-repeat="data in datas | orderBy:order:reverse"><td>{{data.name}}</td>' . $tbody . '</tr></tbody></table>';
		exit($html);
		break;
		//ng数据更新
	case 'ajax':
		$no_system='5,6';//非系统产出或消耗渠道
		$params = json_decode(file_get_contents('php://input'), true);
		$field = isset($params['field']) ? trim($params['field']) : 'date';
		$money_type = isset($params['money_type']) ? intval($params['money_type']) : '';
		$io = isset($params['io']) ? intval($params['io']) % 2 : 0;
		$where = " where type not in ($no_system) and io=$io";
		$inner = isset($params['inner']) ? intval($params['inner']) % 3 : 0;
		$start = isset($params['start']) ? intval($params['start']) : '';
		$end = isset($params['end']) ? intval($params['end']) : '';
		$where.=$start ? " and date>=$start" : '';
		$where.=$end ? " and date<=$end" : '';
		$where.=$money_type ? " and money_type=$money_type" : '';
		$where.=$inner != 2 ? " and `inner`= $inner" : '';
		
		$mysqli = new DbMysqli();
		$data = array();
		$sql="select count(distinct $field) as count from stat_cost $where";
		$count = $mysqli->count($sql);
		$count == 0 && ajax_return('error', __('此条件下无数据'));
		include __CONFIG__ . 'log_config.php';
		
		$sql_field = $field == 'date' ? '`date`,`day`,`month`,`week`' : '`' . $field . '`';
		$sql = "select sum(`char_num`) as `s_cn`,sum(`char_count`) as `s_cc`,sum(`money_num`) as `s_money`,`type`, $sql_field from `stat_cost` $where  group by $field,type order by $field desc,type";
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
			$data[$row['type']]['name'] = isset($money_type_conf[$row['type']]) ? $money_type_conf[$row['type']] : $row['type'];
			$data[$row['type']]['char_num' . $i] = intval($row['s_cn']);
			$data[$row['type']]['char_count' . $i] = intval($row['s_cc']);
			$data[$row['type']]['money_num' . $i] = intval($row['s_money']);
			$data[$row['type']]['pre_money' . $i] = $row['s_cn'] == 0 ? 0 : round($row['s_money'] / $row['s_cn'], 2);
			$sum_money[$i] += intval($row['s_money']);
		}
		$mysqli->close();
		//横向比较排序
		//dump($data);
		foreach ($data as $type => &$val) {
			!isset($val['money_num1'])&&$val['money_num1']=0;
			$val['bit1'] = round($val['money_num1'] / $sum_money[1] * 100, 2);
			for ($j = 2; $j <= $count; $j++) {
				!isset($val['char_num' . $j])&&$val['char_num' . $j]=0;
				!isset($val['char_num' . ($j - 1)])&&$val['char_num' . ($j - 1)]=0;
				if ($val['char_num' . $j] > $val['char_num' . ($j - 1)]) {
					$val['s_char_num' . ($j - 1)] = -1;
				} elseif ($val['char_num' . $j] < $val['char_num' . ($j - 1)]) {
					$val['s_char_num' . ($j - 1)] = 1;
				}
				!isset($val['char_count' . $j])&&$val['char_count' . $j]=0;
				!isset($val['char_count' . ($j - 1)])&&$val['char_count' . ($j - 1)]=0;
				if ($val['char_count' . $j] > $val['char_count' . ($j - 1)]) {
					$val['s_char_count' . ($j - 1)] = -1;
				} elseif ($val['char_count' . $j] < $val['char_count' . ($j - 1)]) {
					$val['s_char_count' . ($j - 1)] = 1;
				}
				!isset($val['pre_money' . $j])&&$val['pre_money' . $j]=0;
				!isset($val['pre_money' . ($j - 1)])&&$val['pre_money' . ($j - 1)]=0;
				if ($val['pre_money' . $j] > $val['pre_money' . ($j - 1)]) {
					$val['s_pre_money' . ($j - 1)] = -1;
				} elseif ($val['pre_money' . $j] < $val['pre_money' . ($j - 1)]) {
					$val['s_pre_money' . ($j - 1)] = 1;
				}
				!isset($val['money_num' . $j])&&$val['money_num' . $j]=0;
				!isset($val['money_num' . ($j - 1)])&&$val['money_num' . ($j - 1)]=0;
				if ($val['money_num' . $j] > $val['money_num' . ($j - 1)]) {
					$val['s_money_num' . ($j - 1)] = -1;
				} elseif ($val['money_num' . $j] < $val['money_num' . ($j - 1)]) {
					$val['s_money_num' . ($j - 1)] = 1;
				}
				$val['bit' . $j] = isset($val['money_num' . $j]) ? round($val['money_num' . $j] / $sum_money[$j] * 100, 2) : 0;
				if ($val['bit' . $j] > $val['bit' . ($j - 1)]) {
					$val['s_bit' . ($j - 1)] = -1;
				} elseif ($val['bit' . $j] < $val['bit' . ($j - 1)]) {
					$val['s_bit' . ($j - 1)] = 1;
				}
			}
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
				while ($end_t>=$start_t) {
					$y = date('Y', $end_t);
					$m = date('m', $end_t);
					$start_date=date('Ym01',$end_t);
					$end_date=date('Ymd',mktime(0,0,0,$m+1,0,$y));
					in_array($key, $$field) && array_push($minmax, $start_date . '-' . $end_date);
					$end_t=strtotime('-1 month',strtotime($start_date));
				}
				break;
		}
		sort($data);
		exit(json_encode(array('status' => 'ok', 'data' => $data, 'cols' => $count, 'sum_money' => $sum_money, 't_field' => $$field, 'minmax' => $minmax)));
		break;
	default:
		$smarty->assign('yesterday',date('Ymd',strtotime('yesterday')));
		$smarty->display();
		break;
}
?>
