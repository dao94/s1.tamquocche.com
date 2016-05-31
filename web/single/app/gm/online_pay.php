<?php
//后台在线充值
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';

$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$char_name=empty($_GET['char_name']) ? '' : floatval($_GET['char_name']);
$jade=empty($_GET['jade']) ? '' : floatval($_GET['jade']);
$action=empty($_GET['action']) ? 'list'  : trim($_GET['action']);

$action_conf=array(
'list'=>__('申请'),
'log'=>__('申请日志'),
);

$conditions=array(
	'char_id'=>$char_id,
	'char_name'=>$char_name,
	'jade'=>$jade,
	'action'=>$action,
);
switch ($action){
	case 'list':
		break;
	case 'log':
		$mysqli = new DbMysqli();
		$apply_count = $mysqli->count('select count(`id`) from `internal_account_gold`');
		$p = new Page($apply_count);
		$apply_list_query = $mysqli->query('select * from `internal_account_gold` order by `apply_ts` desc limit ' . $p->firstRow . ',' . $p->listRows);
		$smarty->assign('apply_list_query', $apply_list_query);
		$smarty->assign('page', $p->show());
		$smarty->assign('gmer', $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT']);
		break;
	case 'set':
		$param1 = !empty($_POST['param1']) ? my_escape_string(trim($_POST['param1'])) : '';
		$param2 = !empty($_POST['param2']) ? my_escape_string(trim($_POST['param2'])) : '';
		$param3 = !empty($_POST['param3']) ? my_escape_string(trim($_POST['param3'])) : '';
		$param4 = !empty($_POST['param4']) ? my_escape_string(trim($_POST['param4'])) : '';
		$param5 = !empty($_POST['param5']) ? my_escape_string(trim($_POST['param5'])) : '';
		$param6 = !empty($_POST['param6']) ? my_escape_string(trim($_POST['param6'])) : '';
		$param7 = !empty($_POST['param7']) ? my_escape_string(trim($_POST['param7'])) : '';
		$list_array=$param1_array=$param2_array=$param3_array=$param4_array=$param5_array=$param6_array=$param7_array=array();
		if($param1!='')$param1_array=explode("####",$param1);
		if($param2!='')$param2_array=explode("####",$param2);
		if($param3!='')$param3_array=explode("####",$param3);
		if($param4!='')$param4_array=explode("####",$param4);
		if($param5!='')$param5_array=explode("####",$param5);
		if($param6!='')$param6_array=explode("####",$param6);
		if($param7!='')$param7_array=explode("####",$param7);
		array_push($list_array,$param1_array, $param2_array,$param3_array,$param4_array,$param5_array,$param6_array,$param7_array);
		include __CLASSES__ . 'Player.class.php';
		$player_obj = new Player();
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '4');
		$mysqli = new DbMysqli();
		$result=false;
		//申请元宝
		foreach ($list_array as $list){
			if(isset($list[0]) || isset($list[1])){
				if($list[0]!='' || $list[1]!=''){
					$char_info=empty($list[0]) ? $list[1] : $list[0];
					$char_type=empty($list[0]) ? 3 : 1;
					$player_info = $player_obj->player_exists($char_info, $char_type);
					if ($player_info == null) {
						ajax_return('error', $list[0].$list[1].__('该玩家不存在'));
					}
					$jade=intval($list[2]);
					$reward_list=array();
					$reward_list['moneyList']['jade'] = intval($list[2]);//元宝列表
					$reward_list = my_escape_string(json_encode($reward_list));
					$sid_info = $mongo->findOne('account_data', array('char_id' => floatval($player_info['id'])), array('serverId' => true));
					$gmer = $_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'];
					$sql = 'insert into `internal_account_gold`(`sid`,`account`,`type`,`char_id`,`char_name`,`applyer`,`apply_ts`,`status`,`jade`,`reward_list`) values' . sprintf('(%d,"%s",1,%s,"%s","%s",%d,0,%d,\'%s\')', $sid_info['serverId'], $player_info['account'], $player_info['id'], $player_info['name'], $gmer, time(),$jade,$reward_list);
					$result = $mysqli->query($sql);
				}
			}
		}
		if ($result) {
			ajax_return('ok', __('申请成功'));
		}
		ajax_return('error', __('申请失败'));
		break;
}
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->display();