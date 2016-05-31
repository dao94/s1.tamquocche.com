<?php
//充值申请审核
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

include __CLASSES__ . 'Page.class.php';
include __CLASSES__ . 'Mdb.class.php';
$mysqli = new DbMysqli();
$apply_count = $mysqli->count('select count(`id`) from `internal_account_gold`');
$p = new Page($apply_count);
$apply_list_query = $mysqli->query('select * from `internal_account_gold` order by `apply_ts` desc limit ' . $p->firstRow . ',' . $p->listRows);
$smarty->assign('apply_list_query', $apply_list_query);
$smarty->assign('page', $p->show());
$smarty->assign('gmer', $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT']);
$smarty->display();
?>
