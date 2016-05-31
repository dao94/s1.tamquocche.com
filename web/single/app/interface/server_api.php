<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';
include __AUTH__ . 'lang.php';

/*
 * 服务器热更新
 * 更新的服、更新的线、更新的文件列表
 * $data = array('id'=>,'idx'=>,'files'=>)
 */
function hot_update($data) {
	$ids = array(11, 21, 31, 41, 51, 52, 61, 71);
	if (!in_array($data['id'], $ids)){
		return array('status' => 'error', 'info' => __('服务器') . 'ID' . __('错误'));
	}
	if (empty($data['files'])){
		return array('status' => 'error', 'info' => __('请输入热更新文件列表'));
	}
	$msg_data = array(
        'pathList' => $data['files'],
        'svInfo' => array(
            'serverId' => intval($data['id']),
            'serverIdx' => intval($data['idx'])
	)
	);
	if (isset($data['host']) && !empty($data['host'])) {
		$data['host'] = str_replace('：', ':', $data['host']);
		if (strpos($data['host'], ':') === false) {
			$host = $data['host'];
			$port = GM_PORT;
		} else {
			list($host, $port) = explode(':', $data['host']);
		}
	} else {
		$host = GM_HOST;
		$port = GM_PORT;
	}
	include __CLASSES__ . 'Gm.class.php';
	$gm = new Gm();
	$rpc = 'brrpc/bg.rpc';
	$rpc_obj = 'brrpc\\Sour_B2rBgOper';
	$async = 'hotUpdate_async';
	$gm->async($rpc, $rpc_obj, $async, $msg_data, $host, $port);
	return array('status' => 'ok', 'info' => __('热更新成功'));
}
/*
 * 日志
 */
 function flush_print($data){
 	include __CLASSES__ .'Gm.class.php';
 	$gm = new Gm();
 	$rpc = 'brrpc/bg.rpc';
 	$rpc_obj = 'brrpc\\Sour_B2rBgOper';
 	$async = 'flushPrint_async';
 	$data=array(
 		 'pathList' => 11,
        'svInfo' => array(
            'serverId' => 22,
            'serverIdx' => 33
		)
 	);
 	$gm->async($rpc,$rpc_obj,$async,$data);
 	return array('status'=>'ok','info'=>__('success'));
 }
 /*
  * 后台在线充值
  */
function bgpay_order($data){
	$mysqli = new DbMysqli();
 	$id=$data['id'];//id递增
 	$gmer=$data['gmer'];
 	$status=$data['status'];//拒绝 2 通过 1
 	if($status==2){
		$apply_list_update_sql = 'update `internal_account_gold` set `verify_ts`=' . time() . ',`verifyer`="' . $gmer . '",`status`=2 where `id`=' . $id;
		$apply_update = $mysqli->query($apply_list_update_sql);
		if (!$apply_update)
			return array('status' => 'error', 'info' => __('更改状态失败，审核失败。'));
 	}else{
 		$sql="select * from internal_account_gold where id=$id limit 1";
 		$list=$mysqli->findOne($sql);
 		$char_id=$list ? $list['char_id'] : '';
 		$jade=$list ? $list['jade'] : '';
 		if($char_id!='' && $jade!=''){
 			$apply_list_update_sql = 'update `internal_account_gold` set `verify_ts`=' . time() . ',`verifyer`="' . $gmer . '",`status`=1 where `id`=' . $id ;
			$apply_update = $mysqli->query($apply_list_update_sql);
			if (!$apply_update)
				return array('status' => 'error', 'info' => __('更改状态失败，审核失败。'));

			include __CLASSES__ .'Gm.class.php';
		 	$gm = new Gm();
		 	$rpc = 'burpc/bg.rpc';
		 	$rpc_obj = 'burpc\\Sour_B2uPayOper';
		 	$async = 'bgPayOrder_async';
		 	$data=array(
		 		'charId'=>trim($char_id),
		 		'jade'=>intval($jade),
		 	);
		 	$gm->async($rpc,$rpc_obj,$async,$data);
 		}


 	}

 	return array('status'=>'ok','info'=>__('success'));
}
/*
 * 防止刷新
 * $data = array('src'=>array(),type=>,flag=>)
 */

function avoid_ctrl($data) {
	$msg_data = array(
        'srcList' => $data['src'],
        'type' => $data['type'],
        'flag' => $data['flag']
	);
	if (isset($data['host']) && !empty($data['host'])) {
		$data['host'] = str_replace('：', ':', $data['host']);
		if (strpos($data['host'], ':') === false) {
			$host = $data['host'];
			$port = GM_PORT;
		} else {
			list($host, $port) = explode(':', $data['host']);
		}
	} else {
		$host = GM_HOST;
		$port = GM_PORT;
	}
	include __CLASSES__ . 'Gm.class.php';
	$gm = new Gm();
	$rpc = 'burpc/avoid.rpc';
	$rpc_obj = 'burpc\\Sour_B2uAvoidCtrl';
	$async = 'b2uAvoid_async';
	$gm->async($rpc, $rpc_obj, $async, $msg_data, $host, $port);
	return array('status' => 'ok', 'info' => __('设置防刷成功'));
}

/*
 * 获取流水类型列表
 * $data['type'] = item ,money ,exp
 */

function get_log($data) {
	file_exists(__CONFIG__ . 'log_config.php') && include __CONFIG__ . 'log_config.php';
	if (isset(${$data['type'] . '_type_conf'})) {
		return array('status' => 'ok', 'info' => '', 'data' => ${$data['type'] . '_type_conf'});
	}
	return array('status' => 'error', 'info' => __('错误的参数'));
}

/*
 * 成就称号自动完成
 * $data[char_type] char_list title_list achieve_list
 */

function auto_title($data) {
	$char_type = isset($data['char_type']) ? intval($data['char_type']) : 0;
	$player = isset($data['player']) ? trim($data['player']) : '';
	$player_list = explode("\n", $player);
	//检查称号列表
	$msg_data = array();
	$msg_data['title'] = isset($data['title']) ? $data['title'] : array();
	$msg_data['achieve'] = isset($data['achieve']) ? $data['achieve'] : array();
	if (empty($msg_data['title']) && empty($msg_data['achieve'])) {
		return array('status' => 'error', 'info' => __('请选择至少选择一个称号或者成就'));
	}
	//校验字段
	if (empty($player_list))
	return array('status' => 'error', 'info' => __('请设置玩家列表'));
	//检查角色
	include __CLASSES__ . 'Player.class.php';
	$player = new Player();
	$player_exists = $player->players_exists($player_list, $char_type);
	foreach ($player_exists as $player => $player_info) {
		if ($player_info === null) {   //退出程序通知说明玩家不存在
			return array('status' => 'error', 'info' => 'playernotexits', 'data' => $player_exists);
		}
		$msg_data['charId'][] = $player_info['id'];
	}
	if (isset($data['host']) && !empty($data['host'])) {
		$data['host'] = str_replace('：', ':', $data['host']);
		if (strpos($data['host'], ':') === false) {
			$host = $data['host'];
			$port = GM_PORT;
		} else {
			list($host, $port) = explode(':', $data['host']);
		}
	} else {
		$host = GM_HOST;
		$port = GM_PORT;
	}
	include __CLASSES__ . 'Gm.class.php';
	$gm = new Gm();
	$rpc = 'burpc/title_achieve.rpc';
	$rpc_obj = 'burpc\\Sour_B2uTitleAchieve';
	$async = 'b2uTitleAchieve_async';
	$gm->async($rpc, $rpc_obj, $async, $msg_data, $host, $port);
	return array('status' => 'ok', 'info' => __('设置称号成就成功'));
}

/*
  * 玩家问题
  */
function player_question($data){
	$time=time();
	$id=$data['id'];
	$status=$data['status'];
	$content=$data['content'];
	$email_id=$data['list'][0]['emailId'];
	$char_id=$data['list'][0]['charId'];
	$reply_content=$data['reply_content'];
	$replyer=$data['replyer'];
	$mysqli = new DbMysqli();

	$sql="update gm_question set reply_content='$reply_content',status=$status,email_id='$email_id',
				replyer='$replyer',reply_time=$time where id=$id ";
		$apply_update = $mysqli->query($sql);
		if (!$apply_update)
			return array('status' => 'error', 'info' => __('更改状态失败，审核失败。'));

	include __CLASSES__ . 'Gm.class.php';
	$gm=new Gm();
	$rpc='borpc/boemail.rpc';
	$rpc_obj='borpc\Sour_B2oEmail';
	$async='b2ocreateEmail_async';
	$msg_data=array(
		'title'=>__('GM回复的一封信'),
		'content'=>$content,
		'list'=>array(array('charId'=>floatval($char_id),'emailId'=>$email_id)),
	);
	$gm->async($rpc, $rpc_obj, $async, $msg_data);

	return array('status'=>'ok','info'=>__('success'));
}

$server = new PHPRPC_Server();
########接口函数发布区#########################
$server->add('player_question');
$server->add('hot_update');
$server->add('avoid_ctrl');
$server->add('get_log');
$server->add('auto_title');
$server->add('flush_print');
$server->add('bgpay_order');

########接口函数发布区#########################
$server->setEnableGZIP(true);
$server->setDebugMode(true);
$server->start();
?>
