<?php

/*
 * @author wangyi
 * @date 2013-05-17 11:33:41
 * 特殊号管理
 */

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 1;
!in_array($type, array(1, 2)) && exit(forbid);
$mysqli = new DbMysqli();
$count = $mysqli->count('select count(`sid`) from `internal_account` where `type`=' . $type);
include __CLASSES__ . 'Page.class.php';
$p = new Page($count);

$sql = 'select * from `internal_account` where `type` =' . $type . ' order by `status` limit ' . $p->firstRow . ',' . $p->listRows;
$query = $mysqli->query($sql);
$smarty->assign('page', $p->show());
$smarty->assign('query', $query);
$smarty->assign('type', $type);
$smarty->assign('gmer', $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT']);
$mysqli->close();
$smarty->display();
?>
