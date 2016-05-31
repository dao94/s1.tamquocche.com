<?php

//充值记录
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__ . 'Page.class.php';

$start = isset($_GET['start']) ? trim($_GET['start']) : '';
$end = isset($_GET['end']) ? trim($_GET['end']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$is_test = isset($_GET['is_test'])&&$_GET['is_test']!=='' ? intval($_GET['is_test']) : '';
$where = array();
if (!empty($start) && !empty($start)) {
	$where[] = ' `ts` >= ' . strtotime($start);
	$where[] = ' `ts` <= ' . (strtotime($end) + 86399);
}
$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
if (!empty($keyword)) {
	$types = array(0 => '`account`', 1 => '`char_name`', 2 => '`order_id`');
	$where[] = $types[$type] . '="' . $keyword . '"';
}
if ($is_test!=='') {
	$where[] = " `is_test`=$is_test";
}
$where_str = '';
if (!empty($where)) {
	$where_str .= ' where ';
	$where_str .= implode(' and ', $where);
}

$orderby = isset($_GET['orderby']) ? intval($_GET['orderby']) : 0;
$orderbys = array('order by `ts` desc', 'order by `ts` asc', 'order by `gold` desc', 'order by `gold` asc');

$mysqli = new DbMysqli();
if ($action == 'output') {
	$order_log_sql = 'select `order_id`,`account`,`char_name`,round(`money`/100,2) as `money`,`gold`,`level`,`sid`,from_unixtime(`ts`,"%Y-%m-%d %H:%i:%s") from `pay_order` ' . $where_str . ' ' . $orderbys[$orderby];
	$order_log_query = $mysqli->query($order_log_sql);
	header("Pragma:public");
	header("Expires:0");
	header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
	header("Content-Type:application/force-download");
	header("Content-Type:application/vnd.ms-execl;charset=gb2312");
	header("Content-Type:application/octet-stream");
	header("Content-Type:application/download");
	header('Content-Disposition:attachment;filename="pay_log.xls"');
	header("Content-Transfer-Encoding:binary");
	$output = iconv('utf-8','gb2312',"订单号\t账号\t角色\t金额\t元宝\t等级\t区服\t时间\n");
	while ($row = $order_log_query->fetch_assoc()) {
		$output .= iconv('utf-8','gb2312',implode("\t", $row)) . "\n";
	}
	exit($output);
}
$count = $mysqli->count('select count(`order_id`) from `pay_order` ' . $where_str);
$p = new Page($count, 20);

$order_log_sql = 'select `order_id`,`account`,`char_id`,`char_name`,round(`money`/100,2) as `money`,`gold`,`ts`,`level`,`sid`,`status` from `pay_order` ' . $where_str . ' ' . $orderbys[$orderby] . ' limit ' . $p->firstRow . ',' . $p->listRows;
$order_log_query = $mysqli->query($order_log_sql);

$overview_sql = 'select count(distinct `char_id`) as `c_char`,sum(`gold`) as `s_g`,sum(`money`) as `s_m` from `pay_order` ' . $where_str;
$overview_query = $mysqli->query($overview_sql);
$overview = $overview_query->fetch_assoc();

$smarty->assign('order_log_query', $order_log_query);
$smarty->assign('overview', $overview);
$smarty->assign('page', $p->show());
$smarty->assign('start', $start);
$smarty->assign('end', $end);
$smarty->assign('is_test', $is_test);
$smarty->assign('keyword', $keyword);
$smarty->assign('type', $type);
$smarty->assign('orderby', $orderby);
$smarty->display();
?>
