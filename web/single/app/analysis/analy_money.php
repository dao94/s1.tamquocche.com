<?php

/*
 * @author wangyi
 * @date 2013-07-20 10:47:14
 * 货币监控
 */

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
switch ($type) {
	case 'log':
		$money = isset($_POST['money']) ? intval($_POST['money']) : 0;
		$io = isset($_POST['io']) ? intval($_POST['io']) : 0;
		$char = isset($_POST['char']) ? trim($_POST['char']) : '';
		// $start = isset($_POST['start']) ? trim($_POST['start']) : '';
		// $end = isset($_POST['end']) ? trim($_POST['end']) : '';

		$start = time()-7*86400;
		$end = time();
		$mysqli = new DbMysqli();
		$count = $mysqli->count('select count(distinct `char_id`) from `log_money` where `io`=1 and `money_type`=3 and `time`>='.$start.' and time<='.$end);
		include __CLASSES__ . 'Page.class.php';
		$p = new Page($count);
		$sql = 'select `char_id`,`char_name`,`io`,sum(`money_num`) as `sm` from `log_money` where `io`=1 and `money_type`=3 and `time`>='.$start.' and time<='.$end .' group by `char_id` order by `sm` desc limit ' . $p->firstRow . ',' . $p->listRows;
		$query = $mysqli->query($sql);
		$data = array();
		$i = 1;
		while ($row = $query->fetch_assoc()) {
			$row['sort'] = ($p->nowPage-1)*$p->listRows+$i;
			$data[] = $row;
			$i++;
		}
		$mysqli->close();
		ajax_return('ok', 'ok', array('data' => $data, 'page' => $p->ajaxShow()));
		break;
	case 'sort':

		break;
}
$smarty->display();
?>