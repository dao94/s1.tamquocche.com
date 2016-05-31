<?php

//系统广播
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
//广播内容直接保存mongo库不保存mysql

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
switch ($type) {
	case 'log':
		include __CLASSES__ . 'Mdb.class.php';
		include __CLASSES__ . 'Page.class.php';
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '5');
		$count = $mongo->count('bg_broadcast');
		$p = new Page($count);
		$broadcasts = $mongo->find('bg_broadcast', array(), array(), array('start' => $p->firstRow, 'limit' => $p->listRows, 'sort' => array('status' => -1, 'overTm' => -1, 'startTm' => -1)));
		$mongo->close();
		$smarty->assign('page', $p->show());
		$smarty->assign('broadcasts', $broadcasts);
		break;
	case 'syslog':
		include __CLASSES__ . 'Mdb.class.php';
		include __CLASSES__ . 'Page.class.php';
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '5');
		$count = $mongo->count('sys_broadcast');
		$p = new Page($count);
		$broadcasts = $mongo->find('sys_broadcast', array(), array(), array('start' => $p->firstRow, 'limit' => $p->listRows, 'sort' => array('msg_id' => 1)));
		$mongo->close();
		$smarty->assign('page', $p->show());
		$smarty->assign('broadcasts', $broadcasts);
		break;
}
$smarty->assign('gmer', $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT']);
$smarty->assign('type', $type);
$smarty->display();
?>
