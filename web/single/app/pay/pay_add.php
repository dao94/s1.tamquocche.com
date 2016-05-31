<?php

//充值补单
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

switch ($type) {
	//补单操作
	case 'addorder':
		$params['sid'] = isset($_POST['sid']) ? intval($_POST['sid']) : 0;
		$params['order_id'] = isset($_POST['order_id']) ? trim($_POST['order_id']) : '';
		$params['account'] = isset($_POST['account']) ? trim($_POST['account']) : '';
		$params['money'] = isset($_POST['money']) ? trim($_POST['money']) : 0;
		$params['gold'] = isset($_POST['gold']) ? trim($_POST['gold']) : 0;
		$time = isset($_POST['time']) ? trim($_POST['time']) : '';  //充值时间
		if ($params['sid'] < 1 || empty($params['account'])) {
			exit(json_encode(array('ret' => __('请正确填写账号信息'))));
		}
		if (empty($time) || date('Y-m-d H:i:s', $ts = strtotime($time)) != $time || $ts > time()) {
			exit(json_encode(array('ret' => __('充值时间有误'))));
		}
		$params['time'] = $ts;
		include __API__ . 'lib/Pay.class.php';
		$payObj = Pay::factory(true);
		$payObj->addRun($params);
		break;
	case 'log':
		include __CLASSES__ . 'Page.class.php';
		$mysqli = new DbMysqli();
		$add_order_count = $mysqli->count('select count(`order_id`) as `count` from `pay_order` where `is_add`=1');
		$p = new Page($add_order_count, 20);
		$add_order_query = $mysqli->query('select `order_id`,`account`,`char_id`,`char_name`,`ts`,`add_ts`,`gm`,round(`money`/100,2) as `money`,`gold` from `pay_order` where `is_add`=1 order by `add_ts` desc limit ' . $p->firstRow . ',' . $p->listRows);
		$smarty->assign('add_order_query', $add_order_query);
		$smarty->assign('page', $p->show());
		break;
}
$smarty->assign('type', $type);
$smarty->display();
?>
