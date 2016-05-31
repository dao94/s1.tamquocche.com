<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__ . 'Activity.class.php';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

switch ($type) {
	//活动设置
	case 'activity_set':
		$activity_text = isset($_POST['activity']) ? trim($_POST['activity']) : '';
		$act = new Activity();
		$activitys = $act->getActivity($activity_text);
		include __CLASSES__ . 'Mdb.class.php';
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '5');
		$activity_types = array();
		foreach ($activitys as $activity) {
			$activity['_id'] = uniqid('act_');
			$activity['set_time'] = time();
			$activity['gmer'] = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];
			$activity['status'] = 0;
			//检测在该时间段是否有活动
			$count = $mongo->count('activity', array(
                'type' => $activity['type'],
                '$or' => array(
			array('start' => array('$lte' => $activity['start']), 'over' => array('$gte' => $activity['start'])),
			array('start' => array('$gte' => $activity['start'], '$lte' => $activity['over']))
			)
			)
			);
			$count > 0 && ajax_return('error', __('在同时间段已有活动') . $activity['type']);
			$insert_result = $mongo->insert('activity', $activity);
			!$insert_result && ajax_return('error', __('入库失败，设置活动失败'));
			!in_array($activity['type'], $activity_types) && $activity_types[] = $activity['type'];
		}
		$mongo->close();
		ajax_return('ok', __('活动设置成功'));
		break;
		
	case 'log':
		//日志查询
		include __CLASSES__ . 'Mdb.class.php';
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX.'5');
		$count = $mongo->count('activity');
		include __CLASSES__ . 'Page.class.php';
		$p = new Page($count);
		$activitys = $mongo->find('activity', array('type'=>1), array('type', 'txt', 'start', 'over', 'set_time', 'gmer', 'status'), array('limit' => $p->listRows, 'start' => $p->firstRow, 'sort' => array('set_time' => -1)));

		$activity_obj = new Activity();
		$smarty->assign('activity_obj', $activity_obj);
		$smarty->assign('activitys', $activitys);
		$smarty->assign('page', $p->show());
		break;
}
$smarty->assign('act_info', Activity::getInfo());
$smarty->assign('type', $type);
$smarty->display();
?>
