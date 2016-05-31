<?php
//奖励申请审核
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

include __CLASSES__ . 'Page.class.php';
include __CLASSES__ . 'Mdb.class.php';
$mysqli = new DbMysqli();
$internal_accounts = $mysqli->query('select `char_id` from `internal_account` where `status`=1 and `type`=1');
$mongo = new Mdb();
$inter_gold_info = array();
//获取内部号的元宝余额
while ($internal_account = $internal_accounts->fetch_assoc()) {
	$mongo->selectDb(MONGO_PERFIX . ($internal_account['char_id'] % 4));
	$moneyList = $mongo->findOne('character_bag', array('_id' => floatval($internal_account['char_id'])), array('moneyList' => true));
	$inter_gold_info[$internal_account['char_id']] = is_null($moneyList['moneyList']) ? 0 : $moneyList['moneyList'][2];
}
$mongo->close();
$apply_count = $mysqli->count('select count(`id`) from `reward_apply`');
$p = new Page($apply_count);
$apply_list_query = $mysqli->query('select a.*,b.`status` as `internal` from `reward_apply`as `a` left join `internal_account` as `b` on a.`char_id`=b.`char_id` and b.type=1 order by `apply_ts` desc limit ' . $p->firstRow . ',' . $p->listRows);
$smarty->assign('apply_list_query', $apply_list_query);
$smarty->assign('inter_gold_info', $inter_gold_info);
$smarty->assign('page', $p->show());
$smarty->assign('gmer', $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT']);
$smarty->display();
?>
