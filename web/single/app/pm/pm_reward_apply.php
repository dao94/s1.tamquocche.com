<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
switch ($type) {
	//按照角色
	case 'char_send':
		$char_type = isset($_POST['char_type']) ? intval($_POST['char_type']) : 1;
		$player = isset($_POST['player']) ? trim($_POST['player']) : '';
		$player = str_replace(array("\t", ',', '，'), array('|', '|', '|'), $player);
		$player_list = array_unique(explode("\n", $player));
		$reward_type = isset($_POST['reward_type']) ? intval($_POST['reward_type']) : 0;
		$reason = isset($_POST['reason']) ? my_escape_string(trim($_POST['reason'])) : '';
		$etitle = isset($_POST['etitle']) ? my_escape_string(trim($_POST['etitle'])) : '';
		$econtent = isset($_POST['econtent']) ? my_escape_string(trim(strip_tags($_POST['econtent'], '<a><strong><br>'))) : '';
		//校验字段
		if (empty($player_list) || empty($reason) || empty($etitle) || empty($econtent))
		ajax_return('error', __('请设置玩家列表、奖励列表、邮件文本内容'));
		//相同的奖励
		$sql = 'insert into `reward_apply`(`applyer`,`account`,`char_id`,`char_name`,`reward_list`,`apply_ts`,`reason`,`email_title`,`email_content`) values ';
		if ($reward_type == 0) {
			$reward = isset($_POST['reward']) ? $_POST['reward'] : array();
			empty($reward) && ajax_return('error', __('请设置奖励列表'));
			$reward_list = array();
			foreach ($reward as $list) {
				if ($list['type'] === 'item') {
					$item_info = explode('|', $list['item']);
					$reward_list['itemList'][] = array('itemId' => $item_info[0], 'number' => intval($list['num']), 'bind' => intval($list['bind']));
				} elseif (in_array($list['type'], array('gold', 'giftGold', 'jade', 'giftJade'))) {
					$reward_list['moneyList'][$list['type']] = intval($list['num']);
				}
			}
			$reward_list = my_escape_string(json_encode($reward_list));
			include __CLASSES__ . 'Player.class.php';
			$player = new Player();
			$player_exists = $player->players_exists($player_list, $char_type);
			$apply_ts = time();

			foreach ($player_exists as $player => $player_info) {
				if ($player_info === null) {   //退出程序通知说明玩家不存在
					ajax_return('error', 'player_exists', $player_exists);
				}
				$sql .= sprintf('("%s","%s",%s,"%s",\'%s\',%d,"%s","%s","%s"),', $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'], $player_info['account'], $player_info['id'], $player_info['name'], $reward_list, $apply_ts, $reason, $etitle, $econtent);
			}
		} else {
			$reward_lines = $player_list;
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
						$player_list[] = $player;
					}
					if ($reward_fields_count >= 3) {
						if (isset($money_id_type[$reward_fields[1]])) {
							$svemails[$player]['moneyList'][$money_id_type[$reward_fields[1]]] = intval($reward_fields[2]);
						} else if (in_array($reward_fields[1], array('gold', 'giftGold', 'jade', 'giftJade'))) {
							$svemails[$player]['moneyList'][$reward_fields[1]] = intval($reward_fields[2]);
						} else {
							$temp = array('itemId' => $reward_fields[1], 'number' => intval($reward_fields[2]));
							isset($reward_fields[4]) && $temp['bind'] = intval($reward_fields[4]) > 0 ? 1 : 0;
							$svemails[$player]['itemList'][] = $temp;
						}
					} else {
						ajax_return('error', $reward_line . __('格式错误'));
					}
					isset($svemails[$player]['itemList']) && count($svemails[$player]['itemList']) + (isset($svemails[$player]['moneyList']) ? 1 : 0) > 10 && ajax_return('error', $reward_fields[0] . __('奖励数量超出10个限制'));
				}
			}

			include __CLASSES__ . 'Player.class.php';
			$player = new Player();
			$player_exists = $player->players_exists($player_list, $char_type);
			$apply_ts = time();
			$msg_obj = array();
			foreach ($player_exists as $player => $player_info) {
				if ($player_info === null) {   //退出程序通知说明玩家不存在
					ajax_return('error', 'player_exists', $player_exists);
				}
				!isset($svemails[$player]['itemList']) && !isset($svemails[$player]['moneyList']) && ajax_return('error', $player . __('：没有设置奖励或者格式错误'));
				$reward_list = array();
				isset($svemails[$player]['itemList']) && $reward_list['itemList'] = $svemails[$player]['itemList'];
				isset($svemails[$player]['moneyList']) && $reward_list['moneyList'] = $svemails[$player]['moneyList'];
				$reward_list = my_escape_string(json_encode($reward_list));
				$sql .= sprintf('("%s","%s",%s,"%s",\'%s\',%d,"%s","%s","%s"),', $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'], $player_info['account'], $player_info['id'], $player_info['name'], $reward_list, $apply_ts, $reason, $etitle, $econtent);
			}
		}

		//插入数据库
		$mysqli = new DbMysqli();
		$my_res = $mysqli->query(trim($sql, ','));
		$mysqli->close();
		if ($my_res) {
			ajax_return('ok', __('数据入库成功，提交申请成功。'));
		} else {
			ajax_return('error', __('数据入库失败，提交申请失败。'));
		}
		break;
		//申请日志
	case 'log':
		include __CLASSES__ . 'Page.class.php';
		$mysqli = new DbMysqli();
		$apply_count = $mysqli->count('select count(`id`) from `reward_apply`');
		$p = new Page($apply_count);
		$apply_list_query = $mysqli->query('select * from `reward_apply` order by `apply_ts` desc limit ' . $p->firstRow . ',' . $p->listRows);
		$smarty->assign('apply_list_query', $apply_list_query);
		$smarty->assign('page', $p->show());
		break;
}
$smarty->assign('type', $type);
$smarty->display();
?>
