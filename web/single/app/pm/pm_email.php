<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
$gmer = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];
switch ($type) {
	//按照角色
	case 'char_send':
		$char_type = isset($_POST['char_type']) ? intval($_POST['char_type']) : 0;
		$player = isset($_POST['player']) ? trim($_POST['player']) : '';
		$player_list = explode("\n", $player);
		$etitle = isset($_POST['etitle']) ? my_escape_string(trim($_POST['etitle'])) : '';
		$econtent = isset($_POST['econtent']) ? trim(strip_tags($_POST['econtent'], '<a><strong><br>')) : '';
		//校验字段
		if (empty($player_list) || empty($etitle) || empty($econtent))
		ajax_return('error', __('请设置玩家列表、奖励列表、邮件文本内容'));
		//检查角色
		include __CLASSES__ . 'Player.class.php';
		$player = new Player();
		$player_exists = $player->players_exists($player_list, $char_type);
		$econtent = str_replace(array('<strong>', '</strong>'), array('<b>', '</b>'), $econtent);
		$svemail = array('title' => $etitle, 'content' => $econtent);
		$econtent=my_escape_string($econtent);
		foreach ($player_exists as $player => $player_info) {
			if ($player_info === null) {   //退出程序通知说明玩家不存在
				ajax_return('error', 'player_exists', $player_exists);
			}
			$emailid = uniqid('email_');
			$svemail['list'][] = array('charId' => $player_info['id'], 'emailId' => $emailid);
		}
		$sql = 'insert into `email_log`(`char_info`,`email_title`,`email_content`,`gm`,`ts`) values ';
		$sql .= sprintf('(\'%s\',"%s","%s","%s",%d)', my_escape_string(json_encode($player_exists)), $etitle, $econtent, $gmer, time());
		//保存数据库发送
		$mysqli = new DbMysqli();
		$my_res = $mysqli->query($sql);
		$mysqli->close();
		if ($my_res) {
			include __CLASSES__ . '/Gm.class.php';
			$gm = new Gm();
			$rpc = 'borpc/boemail.rpc';
			$rpc_obj = 'borpc\Sour_B2oEmail';
			$async = 'b2ocreateEmail_async';
			$gm->async($rpc, $rpc_obj, $async, $svemail);
			ajax_return('ok', __('数据入库成功，发送邮件成功。'));
		} else {
			ajax_return('error', __('数据入库失败，发送邮件失败。'));
		}
		break;
		//日志
	case 'log':
		$start = isset($_REQUEST['start']) ? trim($_REQUEST['start']) : '';
		$end = isset($_REQUEST['end']) ? trim($_REQUEST['end']) : '';
		$char = isset($_REQUEST['char']) ? trim($_REQUEST['char']) : '';
		$gmer = isset($_REQUEST['gmer']) ? trim($_REQUEST['gmer']) : '';
		$keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
		$where = array();
		if ($start != '' && $end != '' && $start < $end) {
			$where[] = ' `ts` >=' . strtotime($start) . ' and `ts` <=' . strtotime($end);
		}
		if ($char != '') {
			$where[] = ' `char_info` like "%' . str_replace('\u', '%\u', trim(json_encode(my_escape_string($char)), '"')) . '%" ';
		}
		if ($gmer != '') {
			$where[] = ' `gm` = "' . my_escape_string($gmer) . '" ';
		}
		if ($keyword != '') {
			$where[] = ' `email_title` like "%' . my_escape_string($keyword) . '%" or `email_content` like "%' . my_escape_string($keyword) . '%" ';
		}
		$where_sql = '';
		if (!empty($where)) {
			$where_sql = ' where ' . implode(' and ', $where);
		}

		include __CLASSES__ . 'Page.class.php';
		$mysqli = new DbMysqli();
		$email_count = $mysqli->count('select count(`id`) from `email_log`' . $where_sql);
		$p = new Page($email_count);
		$email_list_query = $mysqli->query('select * from `email_log` ' . $where_sql . ' order by `ts` desc limit ' . $p->firstRow . ',' . $p->listRows);
		$data = array();
		while ($row = $email_list_query->fetch_assoc()) {
			$row['char_info'] = json_decode($row['char_info'], true);
			$row['ts'] = date('Y-m-d H:i:s', $row['ts']);
			$row['content']=$row['email_content'];
			$data[] = $row;
		}
		$mysqli->close();
		ajax_return('ok', 'ok', array('data' => $data, 'page' => $p->ajaxShow()));
		break;
		//全服条件发送记录
	case 'tab4_log':
		include __CLASSES__ . 'Mdb.class.php';
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '4');
		$count = $mongo->count('boemail', array('itemList' => array('$exists' => false), 'moneyList' => array('$exists' => false)));
		include __CLASSES__ . 'Page.class.php';
		$p = new Page($count);
		$email_list = $mongo->find('boemail', array('itemList' => array('$exists' => false), 'moneyList' => array('$exists' => false)), array('playerlist' => 0), array('start' => $p->firstRow, 'limit' => $p->listRows, 'sort' => array('starttime' => -1)));
		$mongo->close();
		$data = array();
		foreach ($email_list as $email) {
			$email['starttime'] = date('Y-m-d H:i:s', $email['starttime']);
			$email['endtime'] = date('Y-m-d H:i:s', $email['endtime']);
			$data[] = $email;
		}
		ajax_return('ok', 'ok', array('data' => $data, 'page' => $p->ajaxShow()));
		break;
}
$smarty->assign('gmer', $gmer);
$smarty->display();
?>
