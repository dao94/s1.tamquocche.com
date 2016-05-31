<?php
//特殊账号申请
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';

switch ($type) {
	//内部号申请
	case 'internal':
		$char_type = isset($_REQUEST['char_type']) ? intval($_REQUEST['char_type']) : 1;
		$char_info = isset($_REQUEST['char_info']) ? trim($_REQUEST['char_info']) : '';
		$true_name = isset($_REQUEST['true_name']) ? my_escape_string(trim($_REQUEST['true_name'])) : '';
		$job = isset($_REQUEST['job']) ? my_escape_string(trim($_REQUEST['job'])) : '';
		$source = isset($_REQUEST['source']) ? my_escape_string(trim($_REQUEST['source'])) : '';

		if ($true_name == '' || $job == '' || $source == '' || $char_info == '') {
			ajax_return('error', __('请确认玩家信息'));
		}

		include __CLASSES__ . 'Player.class.php';
		$player_obj = new Player();
		$player_info = $player_obj->player_exists($char_info, $char_type);
		if ($player_info == null) {
			ajax_return('error', __('该玩家不存在'));
		}
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '4');
		$sid_info = $mongo->findOne('account_data', array('char_id' => intval($player_info['id'])), array('serverId' => true));
		$mongo->close();
		$gmer = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];
		$sql = 'insert into `internal_account`(`sid`,`account`,`type`,`char_id`,`char_name`,`true_name`,`job`,`source`,`applyer`,`apply_ts`) values' . sprintf('(%d,"%s",1,%s,"%s","%s","%s","%s","%s",%d)', $sid_info['serverId'], $player_info['account'], $player_info['id'], $player_info['name'], $true_name, $job, $source, $gmer, time());
		$mysqli = new DbMysqli();
		$result = $mysqli->query($sql);
		$mysqli->close();
		if ($result) {
			ajax_return('ok', __('保存申请成功'));
		}
		ajax_return('error', __('保存申请失败'));
		break;
	case 'gmer':
		$char_type = isset($_REQUEST['char_type']) ? intval($_REQUEST['char_type']) : 1;
		$char_info = isset($_REQUEST['char_info']) ? trim($_REQUEST['char_info']) : '';
		$gmer_type = isset($_REQUEST['gmer_type']) ? intval($_REQUEST['gmer_type']) : 1;

		if ($char_info == '') {
			ajax_return('error', __('请确认玩家信息'));
		}

		include __CLASSES__ . 'Player.class.php';
		$player_obj = new Player();
		$player_info = $player_obj->player_exists($char_info, $char_type);
		if ($player_info == null) {
			ajax_return('error', __('该玩家不存在'));
		}
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '4');
		$sid_info = $mongo->findOne('account_data', array('char_id' => floatval($player_info['id'])), array('serverId' => true));
		$mongo->close();
		$gmer = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];
		$sql = 'insert into `internal_account`(`sid`,`account`,`type`,`char_id`,`char_name`,`applyer`,`apply_ts`,`apply_type`) values' . sprintf('(%d,"%s",2,%s,"%s","%s",%d,%d)', $sid_info['serverId'], $player_info['account'], $player_info['id'], $player_info['name'], $gmer, time(), $gmer_type);
		$mysqli = new DbMysqli();
		$result = $mysqli->query($sql);
		$mysqli->close();
		if ($result) {
			ajax_return('ok', __('保存申请成功'));
		}
		ajax_return('error', __('保存申请失败'));
		break;
}

$smarty->display();
?>
