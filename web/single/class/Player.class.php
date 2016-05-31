<?php
include __CLASSES__ . 'Mdb.class.php';
class Player {
	private $mongo = null;
	public function __construct() {
		$this->mongo = new Mdb();
	}

	/*
	 * 批量玩家查询
	 * 1:玩家角色id
	 * 2:玩家区账号 格式 区id:账号名  只是中文冒号，区id为数字
	 * 3:玩家角色名
	 */

	public function players_exists($player_list, $type = 1,$is_update=0) {
		$result = array();
		foreach ($player_list as $player) {
			$result[$player] = $this->player_exists($player, $type,$is_update);
		}
		return $result;
	}

	//单个玩家查询
	public function player_exists($player, $type = 1,$is_update=0) {
		$fields = array('_id', 'account', 'name', 'level','occ','creat_time','gender','loginTime','exp','camp');
		switch ($type) {
			//玩家id
			case 1:
				$dbs = MONGO_PERFIX . (intval($player) % 4);
				$table = 'characters';
				$key = '_id';
				$this->mongo->selectDb($dbs);
				if ($char_info = $this->mongo->findOne($table, array($key => floatval($player)), $fields)) {
					return array('id' => $char_info['_id'], 'account' => $char_info['account'], 'name' => $char_info['name'], 'level' => $char_info['level']);
				} else {
					return null;
				};
				break;
				//玩家账号
			case 2:
				$this->mongo->selectDb(MONGO_PERFIX.'4');
				//区id:账号 支持中文：
				$player = str_replace('：', ':', $player);
				$player = explode(':', $player);
				if (count($player) != 2){
					return null;
				}
				$char_id_info = $this->mongo->findOne('account_data', array('serverId' => intval($player[0]), 'account' => $player[1]), array('char_id'));
				if (empty($char_id_info) || !isset($char_id_info['char_id'])){
					//如果角色id不存在表示没创建角色直接返回null
					return null;
				}
				$char_id=floatval($char_id_info['char_id']);
				//通知GM更新数据
				if($is_update){
					if(is_online($char_id)){
						include __CLASSES__.'Gm.class.php';
						//玩家在线，发送协议，请求服务端更新玩家数据
						$gm=new Gm();
						$rpc='borpc/bo_control.rpc';
						$rpc_obj='borpc\\Sour_B2oBgOper';
						$async='saveHumanData_async';
						$msg_data=array('id'=>$char_id);
						$gm->async($rpc, $rpc_obj, $async, $msg_data);
					}
				}
				$this->mongo->selectDb(MONGO_PERFIX.$char_id%4);
				if ($char_info = $this->mongo->findOne('characters', array('_id' => $char_id), $fields)) {
					$char_info['id']=$char_info['_id'];
					unset($char_info['_id']);
					return $char_info;
				} else {
					return null;
				}
				break;
				//玩家角色查询
			case 3:
				$table = 'characters';
				$key = 'name';
				for ($i = 0; $i < 4; ++$i) {
					$this->mongo->selectDb(MONGO_PERFIX . $i);
					//找到之后直接返回
					if ($char_info = $this->mongo->findOne($table, array($key => trim($player)), $fields)) {
						return array('id' => $char_info['_id'], 'account' => $char_info['account'], 'name' => $char_info['name'], 'level' => $char_info['level']);
					}
				}
				return null;
				break;
		}
	}

	function __destruct() {
		$this->mongo->close();
	}

}

?>
