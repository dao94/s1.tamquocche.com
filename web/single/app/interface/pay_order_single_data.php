<?php
//获取单服传送数据
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';
include __AUTH__ . 'lang.php';

class Transit{

	/*
	 * 插入数据
	 * $fields:字段名
	 * $table：表名
	 * $data:数据
	*/
	private function insert_data($table,$fields,$data){
		if(empty($table)||empty($fields)||!is_array($fields)||empty($data)||!is_array($data)){
			return false;
		}
		sort($fields);
		$sql=sprintf('replace into %s (%s) values ',$table,implode(',',$fields));
		foreach ($data as $row){
			$keys=array_keys($row);
			if($fields!==$keys){
				return false;
			}
			$sql.="('".implode("','",array_values($row))."'),";
		}
		$sql=rtrim($sql,',');
		$mysqli=new DbMysqli();
		if($mysqli->query($sql)){
			return true;
		}else{
			return false;
		}
	}

	//玩家订单单服
	function pay_order_single($data){
		$fields=array('agent_id','sid','date','order_id','sid_single','account','char_name','char_id','money','gold','ts','is_first',
						'level','is_add','status','add_ts','gm','y','m','d','is_test','time');
		$result=$this->insert_data('pay_order_single',$fields,$data);
		return $result;
	}
}
$server = new PHPRPC_Server();
$server->add(get_class_methods(Transit),new Transit);
$server->setEnableGZIP(true);
$server->start();