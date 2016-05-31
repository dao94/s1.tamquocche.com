<?php
//商城消费
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CONFIG__ . 'log_config.php';

$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';

switch ($action) {
	//商城流水
	case 'mall_log':
		$start = isset($_REQUEST['start']) ? trim($_REQUEST['start']) : '';
		$end = isset($_REQUEST['end']) ? trim($_REQUEST['end']) : '';
		$char = isset($_REQUEST['charinfo']) ? trim($_REQUEST['charinfo']) : '';
		$item = isset($_REQUEST['item_id']) ? trim($_REQUEST['item_id']) : '';
		$money = isset($_REQUEST['money']) ? intval($_REQUEST['money']) : 0;
		$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
		$where = array();
		if ($start != '') {
			$where[] = '`time`>=' . strtotime($start);
		}
		if ($end != '') {
			$where[] = '`time`<' . (strtotime($end) + 86400);
		}
		if ($char != '') {
			$where[] = '`char_name` = "' . my_escape_string($char) . '"';
		}
		if ($item != '') {
			$where[] = '`item_id` ="' . my_escape_string($item) . '"';
		}
		if ($money > 0) {
			$where[] = '`money_type` = ' . $money;
		}
		$where = !empty($where) ? ' where ' . implode(' and ', $where) : '';
		$mysqli = new DbMysqli();
		if($type=='export'){
			//数据导出
			set_time_limit(120);
			header ('Pragma:public');
			header('Expires:0');
			Header("Accept-Ranges:bytes");
			header('Cache-Control:cache,must-revalidate'); 
			header('Content-Type:application/force-download');
			header('Content-Type:application/octet-stream');
			header('Content-Type:application/download');
			header('Content-Transfer-Encoding:binary');
			header('Content-Type:application/vnd.ms-excel;');
			header('Content-Disposition:attachment;filename=log_mall.xls');
			header('Cache-Control: max-age=0');
			
			$header=sprintf("%s\t%s\t%s\t%s\t%s\t%s\t%s\n",__('角色名'),__('道具'),__('数量'),__('单价'),__('货币'),__('商城类型'),__('时间'));
			echo iconv('UTF-8','GBK',$header);

			$limit=5000;
			$offset=0;
			while ($limit>0){
				$sql="select char_name,item_id,item_num,money_num,money_type,mall_type,time from log_mall $where order by time desc limit $offset,$limit";
				$query=$mysqli->query($sql);
				$count=0;
				while ($row=$query->fetch_assoc()){
					$count++;
					$row['item_id']=__($row['item_id']);
					$row['time']=date('Y-m-d H:i:s',$row['time']);
					$row['money_type']=isset($money_class_conf[$row['money_type']]) ? $money_class_conf[$row['money_type']] : $row['money_type'];
					$row['mall_type']=isset($mall_type_conf [$row['mall_type']]) ? $mall_type_conf [$row['mall_type']] : $row['mall_type'];
					echo iconv('UTF-8','GBK',implode("\t",$row)."\n");	
				}
				if($count<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
			exit;
		}
		
		$count = $mysqli->count('select count(`id`) from `log_mall`' . $where);
		include __CLASSES__ . 'Page.class.php';
		$p = new Page($count);
		$query = $mysqli->query('select `char_id`,`char_name`,`money_type`,`money_num`,`item_id`,`item_num`,`mall_type`,`time` from `log_mall` ' . $where . ' order by `time` desc limit ' . $p->firstRow . ',' . $p->listRows);
		$data = array();
		while ($row = $query->fetch_assoc()) {
			$row['item_id'] = __($row['item_id']);
			$row['time'] = date('Y-m-d H:i:s', $row['time']);
			$row['money_type'] = $mall_money_conf[$row['money_type']];
			$data[] = $row;
		}
		
		//购买小结
		$total=array();
		if($char||$item){
			$sql="select money_type,sum(money_num*item_num) as money_num,sum(item_num) as item_num from log_mall $where group by money_type";
			$list=$mysqli->find($sql);
			foreach ($list as $row){
				isset($total['item_num']) ? $total['item_num']+=$row['item_num'] : $total['item_num']=$row['item_num'];
				$total['money_type_'.$row['money_type']]=$row['money_num'];
			}
		}
		ajax_return('ok', 'ok', array('data' => $data,'total'=>$total, 'page' => $p->ajaxShow()));
		break;
		
	case 'mall_total':
		//购买道具统计
		$params = json_decode(file_get_contents('php://input'), true);
		$start = isset($params['start']) ? trim($params['start']) : '';
		$end = isset($params['end']) ? trim($params['end']) : '';
		$money_type = isset($params['money']) ? intval($params['money']) : 0;
		$item = isset($params['item']) ? trim($params['item']) : '';
		$where = array();
		if ($start != '') {
			$where[] = '`time`>=' . strtotime($start);
		}
		if ($end != '') {
			$where[] = '`time`<' . (strtotime($end) + 86400);
		}
		if ($money_type > 0) {
			$where[] = '`money_type`=' . $money_type;
		}
		if ($item != '') {
			$where[] = '`item_id`="' . my_escape_string($item) . '"';
		}
		$where = !empty($where) ? ' where ' . implode(' and ', $where) : '';
		$sql = "select `item_id`,sum(`item_num`) as `s_item`,sum(`money_num`*item_num) as `s_money`,`money_type` from `log_mall` $where group by `item_id`,`money_type`";
		$mysqli = new DbMysqli();
		$query = $mysqli->query($sql);
		$data = array();
		while ($row = $query->fetch_assoc()) {
			$row['item_id'] = __($row['item_id']);
			$row['money_type'] = $mall_money_conf[$row['money_type']];
			$row['s_money'] = intval($row['s_money']);
			$row['s_item'] = intval($row['s_item']);
			$data[] = $row;
		}
		ajax_return('ok', 'ok', $data);
		break;
		
	case 'cost_sort':
		//购买排行
		$where = '';
		if (isset($_REQUEST['first'])) {
			$start = strtotime(date('Y-m-d', SERVER_OPEN_TIME));
			$end = $start + 86400;
			$where = ' where `time`>=' . $start . ' and `time`<' . $end;
		}
		$sql = 'select `item_id`,sum(`item_num`) as `s_item`,sum(`money_num`*item_num) as `s_money`,`money_type` from `log_mall` ' . $where . ' group by `item_id`,`money_type` order by `item_id`';
		$mysqli = new DbMysqli();
		$query = $mysqli->query($sql);
		$data = array();
		$i = -1;
		$pre_item = '';
		while ($row = $query->fetch_assoc()) {
			if ($pre_item != $row['item_id']) {
				$i++;
				$data[$i] = array('name' => __($row['item_id']), 'num' => 0, 'f1' => 0, 'f2' => 0, 'f3' => 0, 'f4' => 0, 'f5' => 0, 'f6' => 0, 'f7' => 0, 'f8' => 0, 'f9' => 0);
			}
			$data[$i]['f' . $row['money_type']] += $row['s_money'];
			$data[$i]['num'] += $row['s_item'];
		}
		ajax_return('ok', 'ok', $data);
		break;
}
$smarty->assign('mall_money_conf', $mall_money_conf);
$smarty->display();
?>
