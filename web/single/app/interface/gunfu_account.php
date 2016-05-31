<?php
//获取单服传送数据
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';
include __AUTH__ . 'lang.php';

class Transit{
	//滚服玩家
	var $mongo;
	function gunfu_account($data_account){
		include __CLASSES__.'Mdb.class.php';
		$this->mongo = new Mdb();
		$data=$users_array=array();//当前服的玩家数
		$this->mongo->selectDb(MONGO_PERFIX.'4');
		$limit=5000;
		$offset=0;

		$condition=array('char_id' => array('$exists' => true),'account'=>array('$in'=>$data_account));
		$fields = array('account');
		while($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $this->mongo->find('account_data', $condition, $fields, $result_condition);
			if(!empty($list)){
				foreach($list as $items){
					$users_array[]=$items['account'];  //账户数组
				}
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		return $users_array;
	}
}
$server = new PHPRPC_Server();
$server->add(get_class_methods(Transit),new Transit);
$server->setEnableGZIP(true);
$server->start();
?>