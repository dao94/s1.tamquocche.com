<?php

//新增充值
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__ . 'Page.class.php';

$start = isset($_GET['start']) ? trim($_GET['start']) : '';
$end = isset($_GET['end']) ? trim($_GET['end']) : '';
$where = '';
if (!empty($start) && !empty($end)) {
	$start_t = strtotime($start);
	$end_t = strtotime($end . ' 23:59:59');
	$where = ' and `ts`>=' . $start_t . ' and `ts` <=' . $end_t;
}
$mysqli = new DbMysqli();

$day_new_count = $mysqli->count('select count(DISTINCT from_unixtime(`ts`,"%Y-%m-%d")) as `count` from `pay_order` where `is_first` =1 ' . $where . ' group by `is_first`');
$p = new Page($day_new_count, 20);
$new_user_query = $mysqli->query('select from_unixtime(`ts`,"%Y-%m-%d") as `day`,datediff(from_unixtime(`ts`,"%Y-%m-%d"),"' . date('Y-m-d', SERVER_OPEN_TIME) . '") as `diff_day`,count(distinct `char_id`) as `c_char`,round(sum(`money`)/100,2) as `s_money`,sum(`gold`) as `s_gold` from `pay_order` where `is_first` =1 ' . $where . ' group by `day` order by day desc limit ' . $p->firstRow . ',' . $p->listRows);

$smarty->assign('start', $start);
$smarty->assign('end', $end);
$smarty->assign('new_user_query', $new_user_query);
$smarty->assign('page', $p->show());
$smarty->display();
$mysqli->close();
?>
