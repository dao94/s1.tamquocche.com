<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
$gmer = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];
switch ($type) {
	case 'fool_send':
		//简单模式
		$char_type = isset($_POST['char_type']) ? intval($_POST['char_type']) : 1;
		$reason = isset($_POST['reason']) ? my_escape_string(trim($_POST['reason'])) : '';
		$etitle = isset($_POST['etitle']) ? my_escape_string(trim($_POST['etitle'])) : '';
		$econtent = isset($_POST['econtent']) ? trim(strip_tags($_POST['econtent'], '<a><strong><br>')) : '';
		$player = isset($_POST['player']) ? trim($_POST['player']) : '';
		$player_list = array_unique(explode("\n", $player));
		$reward = isset($_POST['reward']) ? $_POST['reward'] : '';

		//校验字段
		if (empty($player_list) || empty($reward) || empty($reason) || empty($etitle) || empty($econtent))
		ajax_return('error', __('请设置玩家列表、奖励列表、邮件文本内容'));
		$econtent = str_replace(array('<strong>', '</strong>'), array('<b>', '</b>'), $econtent);
		$svemail = array('title' => $etitle, 'content' => $econtent);
		$econtent = my_escape_string($econtent);
		//生成奖励列表
		foreach ($reward as $list) {
			if ($list['type'] === 'item' && !empty($list['num']) && !empty($list['item'])) {
				$item_info = explode('|', $list['item']);
				$svemail['itemList'][] = array('itemId' => $item_info[0], 'number' => $list['num'], 'bind' => intval($list['bind']));
			} elseif (in_array($list['type'], array('gold', 'giftGold', 'jade', 'giftJade')) && !empty($list['num'])) {
				$svemail['moneyList'][$list['type']] = intval($list['num']);
			}
		}
		if(empty($svemail['itemList']) && empty($svemail['moneyList']))	ajax_return('error',__('请配置奖励列表'));
		$reward_list = array();
		isset($svemail['itemList']) && $reward_list['itemList'] = $svemail['itemList'];
		isset($svemail['moneyList']) && $reward_list['moneyList'] = $svemail['moneyList'];
		$reward_list = my_escape_string(json_encode($reward_list));
		//写流水
		$sql = 'insert into `reward_log`(`id`,`account`,`char_id`,`char_name`,`gm`,`reason`,`email_title`,`email_content`,`send_ts`,`reward_list`) values ';
		include __CLASSES__ . 'Player.class.php';
		$player = new Player();
		$player_exists = $player->players_exists($player_list, $char_type);
		$svemail['list'] = array();
		$send_ts = time();
		foreach ($player_exists as $player => $player_info) {
			if ($player_info === null) {   //退出程序通知说明玩家不存在
				ajax_return('error', 'player_exists', $player_exists);
			}
			$emailid = uuid();
			$svemail['list'][] = array('charId' => $player_info['id'], 'emailId' => $emailid);
			$sql .= sprintf('("%s","%s",%s,"%s","%s","%s","%s","%s",%d,\'%s\'),', $emailid, $player_info['account'], 
				$player_info['id'], $player_info['name'], $gmer, $reason, $svemail['title'], $econtent, $send_ts, $reward_list);
		}
		$mysqli = new DbMysqli();
		$my_res = $mysqli->query(rtrim($sql, ','));
		$mysqli->close();
		if ($my_res) {
			include __CLASSES__ . '/Gm.class.php';
			$gm = new Gm();
			$rpc = 'borpc/boemail.rpc';
			$rpc_obj = 'borpc\\Sour_B2oEmail';
			$async = 'b2ocreateEmail_async';
			$gm->async($rpc, $rpc_obj, $async, $svemail);
			ajax_return('ok', __('数据入库成功，发送奖励成功。'));
		} else {
			ajax_return('error', __('数据入库失败，发送奖励失败。'));
		}
		break;
		
	case 'same_send':
		//高级模式
		$char_type = isset($_POST['char_type']) ? intval($_POST['char_type']) : 1;
		$player = isset($_POST['player']) ? trim($_POST['player']) : '';
		$player_list = array_unique(explode("\n", $player));
		$reward = isset($_POST['reward']) ? trim($_POST['reward']) : '';
		$reason = isset($_POST['reason']) ? my_escape_string(trim($_POST['reason'])) : '';
		$etitle = isset($_POST['etitle']) ? my_escape_string(trim($_POST['etitle'])) : '';
		$econtent = isset($_POST['econtent']) ? trim(strip_tags($_POST['econtent'], '<a><strong><br>')) : '';
		//校验字段
		if (empty($player_list) || empty($reward) || empty($reason) || empty($etitle) || empty($econtent))
		ajax_return('error', __('请设置玩家列表、奖励列表、邮件文本内容'));
		//校验字段
		if (empty($player_list) || empty($reward) || empty($reason))
		ajax_return('error', __('请设置玩家列表、奖励列表'));
		//匹配道具奖励
		preg_match_all('#\[item\s+id\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"\s+bind\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/item\]#Us', $reward, $reward_item, PREG_SET_ORDER);

		//匹配货币奖励
		preg_match_all('#\[money\s+type\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/money\]#Us', $reward, $reward_money, PREG_SET_ORDER);
		(count($reward_item) + count($reward_money) > 10) && ajax_return('error', __('奖励数量超出10个限制'));
		$econtent = str_replace(array('<strong>', '</strong>'), array('<b>', '</b>'), $econtent);
		$svemail = array('title' => $etitle, 'content' => $econtent);
		$econtent = my_escape_string($econtent);
		foreach ($reward_item as $item_info) {
			if($item_info[1] && $item_info[2]){
				$svemail['itemList'][] = array('itemId' => $item_info[1], 'number' => $item_info[2], 'bind' => $item_info[3]);
			}
		}
		$money_id_type = array(
			1 => 'gold',
			2 => 'giftGold',
			3 => 'jade',
			4 => 'giftJade'
		);
		foreach ($reward_money as $money_info) {
			if (isset($money_id_type[$money_info[1]])) {
				$svemail['moneyList'][$money_id_type[$money_info[1]]] = $money_info[2];
			}
		}
		//写流水
		$reward_list = array();
		isset($svemail['itemList']) && $reward_list['itemList'] = $svemail['itemList'];
		isset($svemail['moneyList']) && $reward_list['moneyList'] = $svemail['moneyList'];
		$reward_list = my_escape_string(json_encode($reward_list));
		$sql = 'insert into `reward_log`(`id`,`account`,`char_id`,`char_name`,`gm`,`reason`,`email_title`,`email_content`,`send_ts`,`reward_list`) values ';
		include __CLASSES__ . 'Player.class.php';
		$player = new Player();
		$player_exists = $player->players_exists($player_list, $char_type);
		$svemail['list'] = array();
		$send_ts = time();
		foreach ($player_exists as $player => $player_info) {
			if ($player_info === null) {   //退出程序通知说明玩家不存在
				ajax_return('error', 'player_exists', $player_exists);
			}
			$emailid = uuid();
			$svemail['list'][] = array('charId' => $player_info['id'], 'emailId' => $emailid);
			$sql .= sprintf('("%s","%s",%s,"%s","%s","%s","%s","%s",%d,\'%s\'),', $emailid, $player_info['account'], $player_info['id'], $player_info['name'], $gmer, $reason, $svemail['title'], $econtent, $send_ts, $reward_list);
		}

		$mysqli = new DbMysqli();
		$my_res = $mysqli->query(trim($sql, ','));
		$mysqli->close();
		if ($my_res) {
			include __CLASSES__ . '/Gm.class.php';
			$gm = new Gm();
			$rpc = 'borpc/boemail.rpc';
			$rpc_obj = 'borpc\\Sour_B2oEmail';
			$async = 'b2ocreateEmail_async';
			$gm->async($rpc, $rpc_obj, $async, $svemail);
			ajax_return('ok', __('数据入库成功，发送奖励成功。'));
		} else {
			ajax_return('error', __('数据入库失败，发送奖励失败。'));
		}
		break;
		
	case 'diff_send':
		//快速模式,转换再处理
		$reward_text = isset($_POST['reward']) ? trim($_POST['reward']) : '';
		$reward_text = str_replace(array("\t", ",", "，"), array("|", "|", "|"), $reward_text);
		$reward_lines = explode("\n", trim($reward_text));
		$char_type = isset($_POST['char_type']) ? intval($_POST['char_type']) : 1;
		$reason = isset($_POST['reason']) ? my_escape_string(trim($_POST['reason'])) : '';
		$etitle = isset($_POST['etitle']) ? my_escape_string(trim($_POST['etitle'])) : '';
		$econtent = isset($_POST['econtent']) ? trim(strip_tags($_POST['econtent'], '<a><strong><br>')) : '';
		//校验字段
		if (empty($reward_text) || empty($reason) || empty($etitle) || empty($econtent))
		ajax_return('error', __('请设置玩家列表、奖励列表、邮件文本内容'));
		$econtent = str_replace(array('<strong>', '</strong>'), array('<b>', '</b>'), $econtent);
		$svemails = array();
		$player_list = array();
		$money_id_type = array(
			1 => 'gold',
			2 => 'giftGold',
			3 => 'jade',
			4 => 'giftJade'
		);
		foreach ($reward_lines as $reward_line) {
			if (trim($reward_line) !== '') {
				$reward_fields = explode('|', $reward_line);
				$reward_fields_count = count($reward_fields);//如果不存在角色id则进行查询获取角色id
				$player = $reward_fields[0];
				if (!isset($svemails[$player])) {
					$svemails[$player]['title'] = $etitle;
					$svemails[$player]['content'] = $econtent;
					$player_list[] = $player;
				}
				if ($reward_fields_count >= 3) {
					if (isset($money_id_type[$reward_fields[1]])) {
						$svemails[$player]['moneyList'][$money_id_type[$reward_fields[1]]] = intval($reward_fields[2]);
					} else if (in_array($reward_fields[1], array('gold', 'giftGold', 'jade', 'giftJade'))) {
						$svemails[$player]['moneyList'][$reward_fields[1]] = intval($reward_fields[2]);
					} else {
						if($reward_fields[1] && $reward_fields[2]){
							$temp = array('itemId' => $reward_fields[1], 'number' => intval($reward_fields[2]), 'bind' => 0);
							isset($reward_fields[3]) && $temp['bind'] = intval($reward_fields[3]) > 0 ? 1 : 0;
							$svemails[$player]['itemList'][] = $temp;
						}
					}
				} else {
					ajax_return('error', $reward_line . __('格式错误'));
				}
				isset($svemails[$player]['itemList']) && count($svemails[$player]['itemList']) + (isset($svemails[$player]['moneyList']) ? 1 : 0) > 10 && ajax_return('error', $reward_fields[0] . __('奖励数量超出10个限制'));
			}
		}

		//写流水
		$sql = 'insert into `reward_log`(`id`,`account`,`char_id`,`char_name`,`gm`,`reason`,`email_title`,`email_content`,`send_ts`,`reward_list`) values ';
		include __CLASSES__ . 'Player.class.php';
		$player_obj = new Player();
		$player_exists = $player_obj->players_exists($player_list, $char_type);
		$send_ts = time();
		$msg_obj = array();
		$econtent = my_escape_string($econtent);
		foreach ($player_exists as $player => $player_info) {
			if ($player_info === null) {   //退出程序通知说明玩家不存在
				ajax_return('error', 'player_exists', $player_exists);
			}
			!isset($svemails[$player]['itemList']) && !isset($svemails[$player]['moneyList']) && ajax_return('error', $player . __('：没有设置奖励或者格式错误'));
			$emailid = uuid();
			$svemails[$player]['list'][] = array('charId' => $player_info['id'], 'emailId' => $emailid);
			$reward_list = array();
			isset($svemails[$player]['itemList']) && $reward_list['itemList'] = $svemails[$player]['itemList'];
			isset($svemails[$player]['moneyList']) && $reward_list['moneyList'] = $svemails[$player]['moneyList'];
			$reward_list = my_escape_string(json_encode($reward_list));
			$sql .= sprintf('("%s","%s",%s,"%s","%s","%s","%s","%s",%d,\'%s\'),', $emailid, $player_info['account'], $player_info['id'], $player_info['name'], $gmer, $reason, $etitle, $econtent, $send_ts, $reward_list);
			$msg_obj['email'][] = $svemails[$player];
		}
		$mysqli = new DbMysqli();
		$my_res = $mysqli->query(trim($sql, ','));
		$mysqli->close();
		if ($my_res) {
			include __CLASSES__ . '/Gm.class.php';
			$gm = new Gm();
			$rpc = 'borpc/boemail.rpc';
			$rpc_obj = 'borpc\\Sour_B2oEmail';
			$async = 'b2ocreateEmailList_async';
			$gm->async($rpc, $rpc_obj, $async, $msg_obj);
			ajax_return('ok', __('数据入库成功，发送奖励成功。'));
		} else {
			ajax_return('error', __('数据入库失败，发送奖励失败。'));
		}
		break;
		//显示流水
	case 'log':
		include __CLASSES__ . 'Page.class.php';
		$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
		$char_name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));
		$gm=empty($_GET['gm']) ? '' : my_escape_string(trim($_GET['gm']));
		$where='where 1';
		$where.=$start_date ? ' and send_ts>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and send_ts<='.strtotime($end_date) : '';
		$where.=$char_name ? " and char_name='$char_name'" : '';
		$where.=$gm ? " and gm='$gm'" : '';
		
		$mysqli = new DbMysqli();
		$reward_count = $mysqli->count('select count(`id`) from `reward_log`'.$where);
		$p = new Page($reward_count);
		$sql="select * from `reward_log` $where order by `send_ts` desc limit {$p->firstRow},{$p->listRows}";
		$reward_list_query = $mysqli->query($sql);
		$data = array();
		while ($row = $reward_list_query->fetch_assoc()) {
			$reward = json_decode($row['reward_list'], true);
			if (isset($reward['itemList'])) {
				foreach ($reward['itemList'] as &$item) {
					$item['itemId'] = __($item['itemId']);
				}
			}
			$row['reward_list'] = $reward;
			$row['send_ts'] = date('Y-m-d H:i:s', $row['send_ts']);
			$row['get_ts'] = empty($row['get_ts']) ? '' : date('Y-m-d H:i:s', $row['get_ts']);
			$data[] = $row;
		}
		$mysqli->close();
		ajax_return('ok', 'ok', array('data' => $data, 'page' => $p->ajaxShow()));
		break;
		//显示条件发送记录
	case 'tab4_log':
		include __CLASSES__ . 'Mdb.class.php';
		$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
		$gm=empty($_GET['gm']) ? '' : my_escape_string(trim($_GET['gm']));
		$status=isset($_GET['status']) && $_GET['status']!=='' ?  intval($_GET['status']) : '';
		
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '4');
		$where = array(
            '$or' => array(
				array('itemList' => array('$exists' => true)),
				array('moneyList' => array('$exists' => true)),
			)
		);
		$start_date ? $where['starttime']=array('$gte'=>strtotime($start_date)) : '';
		$end_date ? $where['endtime']=array('$lte'=>strtotime($end_date)) : '';
		$gm ? $where['gmer']=$gm : '';
		$status!=='' ? $where['state']=$status : '';	
		$count = $mongo->count('boemail', $where);
		include __CLASSES__ . 'Page.class.php';
		$p = new Page($count);
		$email_list = $mongo->find('boemail', $where, array('playerlist' => 0), array('start' => $p->firstRow, 'limit' => $p->listRows, 'sort' => array('starttime' => -1)));
		$mongo->close();
		$data = array();
		foreach ($email_list as $email) {
			if (isset($email['itemList'])) {
				foreach ($email['itemList'] as &$item) {
					$item['itemId'] = __($item['itemId']);
				}
			}
			$email['starttime'] = date('Y-m-d H:i', $email['starttime']);
			$email['endtime'] = date('Y-m-d H:i', $email['endtime']);
			$email['cut_content']=msubstr($email['content'],0,10);
			$data[] = $email;
		}
		ajax_return('ok', 'ok', array('data' => $data, 'page' => $p->ajaxShow()));
		break;
		
	case 'check':
		//检测玩家是否存在
		include __CLASSES__ . 'Player.class.php';
		$char_type = isset($_POST['char_type']) ? intval($_POST['char_type']) : 1;
		$player = isset($_POST['player']) ? trim($_POST['player']) : '';
		$player_list = array_unique(explode("\n", $player));
		$player = new Player();
		$player_exists = $player->players_exists($player_list, $char_type);
		ajax_return(1, 'player_exists',$player_exists);
		break;
}
$smarty->assign('gmer', $gmer);
$smarty->assign('type', $type);
$smarty->display();
?>
