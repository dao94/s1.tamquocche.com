<?php

/*
 * @author wangyi
 * @date 2013-07-31 04:54:11
 */

define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action_player_conf=array(
'all_player'=>__('总体'),
'new_player'=>__('新玩家'),
'old_player'=>__('滚服玩家'),
);
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
$action_player=empty($_GET['action_player']) ? 'all_player' : trim($_GET['action_player']);
$table='';
switch($action_player){
	case 'all_player':
		$table='stat_payrate';
		break;
	case 'new_player':
		$table='stat_payrate_new';
		break;
	case 'old_player':
		$table='stat_payrate_gunfu';
		break;
}

$smarty->assign('action_player',$action_player);
$smarty->assign('action_player_conf',$action_player_conf);
switch ($action) {
	case 'day':
		$day = isset($_REQUEST['day']) ? intval($_REQUEST['day']) : 1;
		$mysqli = new DbMysqli();
		if($table=='stat_payrate_new'){
			//新玩家
			$data_new=array();
			$sql="select * from stat_payrate where day='$day'";
			$data = $mysqli->findOne($sql);
			$data_new['date']=$data ? $data['date'] : '';
			$data_new['day']=$data ? $data['day'] : '';
			foreach($data as $value){
				$sql="select * from stat_payrate_gunfu where day='$day'";
				$list = $mysqli->findOne($sql);
				$data_new['unpayer']=$list&&$data ? ($data['unpayer']-$list['unpayer']) : '';
				$data_new['payer']=$list&&$data ? ($data['payer']-$list['payer']) : '';
				$data_new['np_unpayer']=$list&&$data ? ($data['np_unpayer']-$list['np_unpayer']) : '';
				$data_new['np_payer']=$list&&$data ? ($data['np_payer']-$list['np_payer']) : '';
				$data_new['a_unpayer']=$list&&$data ? ($data['a_unpayer']-$list['a_unpayer']) : '';
				$data_new['a_payer']=$list&&$data ? ($data['a_payer']-$list['a_payer']) : '';
				$data_new['pay1']=$list&&$data ? ($data['pay1']-$list['pay1']) : '';
				$data_new['pay2']=$list&&$data ? ($data['pay2']-$list['pay2']) : '';
				$data_new['pay3']=$list&&$data ? ($data['pay3']-$list['pay3']) : '';
				$data_new['pay4']=$list&&$data ? ($data['pay4']-$list['pay4']) : '';
				$data_new['pay5']=$list&&$data ? ($data['pay5']-$list['pay5']) : '';
				$data_new['pay6']=$list&&$data ? ($data['pay6']-$list['pay6']) : '';
			}
			if ($data_new !== false) {
				echo json_encode($data_new);
			} else {
				echo json_encode(array(
	                'payer' => 0,
	                'unpayer' => 0,
	                'a_payer' => 0,
	                'a_unpayer' => 0,
	                'np_payer' => 0,
	                'np_unpayer' => 0,
	                'pay1' => 0,
	                'pay2' => 0,
	                'pay3' => 0,
	                'pay4' => 0,
	                'pay5' => 0,
	                'pay6' => 0
				));
			}
			setcookie('test', 1);
		}else{
			$sql = 'select * from '.$table.' where day=' . $day;
			$data = $mysqli->findOne($sql);
			if ($data !== false) {
				echo json_encode($data);
			} else {
				echo json_encode(array(
	                'payer' => 0,
	                'unpayer' => 0,
	                'a_payer' => 0,
	                'a_unpayer' => 0,
	                'np_payer' => 0,
	                'np_unpayer' => 0,
	                'pay1' => 0,
	                'pay2' => 0,
	                'pay3' => 0,
	                'pay4' => 0,
	                'pay5' => 0,
	                'pay6' => 0
				));
			}
			setcookie('test', 1);
		}

		exit;
		break;
	case 'date':
		$date = isset($_REQUEST['date']) ? intval($_REQUEST['date']) : date('Y-m-d',strtotime('yesterday'));
		$mysqli = new DbMysqli();
		if($table=='stat_payrate_new'){
			//新玩家
			$data_new=array();
			$sql="select * from stat_payrate where date= $date";
			$data = $mysqli->findOne($sql);
			$sql="select * from stat_payrate_gunfu where date='$date'";
			$list = $mysqli->findOne($sql);
			$data_new['date']=$data ? $data['date'] : '';
			$data_new['day']=$data ? $data['day'] : '';
			$data_new['unpayer']=$list&&$data ? ($data['unpayer']-$list['unpayer']) : '';
			$data_new['payer']=$list&&$data ? ($data['payer']-$list['payer']) : '';
			$data_new['np_unpayer']=$list&&$data ? ($data['np_unpayer']-$list['np_unpayer']) : '';
			$data_new['np_payer']=$list&&$data ? ($data['np_payer']-$list['np_payer']) : '';
			$data_new['a_unpayer']=$list&&$data ? ($data['a_unpayer']-$list['a_unpayer']) : '';
			$data_new['a_payer']=$list&&$data ? ($data['a_payer']-$list['a_payer']) : '';
			$data_new['pay1']=$list&&$data ? ($data['pay1']-$list['pay1']) : '';
			$data_new['pay2']=$list&&$data ? ($data['pay2']-$list['pay2']) : '';
			$data_new['pay3']=$list&&$data ? ($data['pay3']-$list['pay3']) : '';
			$data_new['pay4']=$list&&$data ? ($data['pay4']-$list['pay4']) : '';
			$data_new['pay5']=$list&&$data ? ($data['pay5']-$list['pay5']) : '';
			$data_new['pay6']=$list&&$data ? ($data['pay6']-$list['pay6']) : '';

			if ($data_new !== false) {
				echo json_encode($data_new);
			} else {
				echo json_encode(array(
	                'payer' => 0,
	                'unpayer' => 0,
	                'a_payer' => 0,
	                'a_unpayer' => 0,
	                'np_payer' => 0,
	                'np_unpayer' => 0,
	                'pay1' => 0,
	                'pay2' => 0,
	                'pay3' => 0,
	                'pay4' => 0,
	                'pay5' => 0,
	                'pay6' => 0
				));
			}
		}else{
			$sql = 'select * from '.$table.' where date=' . $date;
			$data = $mysqli->findOne($sql);
			if ($data !== false) {
				echo json_encode($data);
			} else {
				echo json_encode(array(
	                'payer' => 0,
	                'unpayer' => 0,
	                'a_payer' => 0,
	                'a_unpayer' => 0,
	                'np_payer' => 0,
	                'np_unpayer' => 0,
	                'pay1' => 0,
	                'pay2' => 0,
	                'pay3' => 0,
	                'pay4' => 0,
	                'pay5' => 0,
	                'pay6' => 0
				));
			}
		}

		exit;
		break;
}


$smarty->display();
?>
