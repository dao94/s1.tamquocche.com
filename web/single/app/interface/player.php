<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';
include __AUTH__ . 'lang.php';

class Player{
	private $mdb=null;

	public function __construct(){
		include __CLASSES__.'Mdb.class.php';
		include __CONFIG__.'game_config.php';
		$this->mdb=new Mdb();
	}

	//角色名模糊查询
	public function getNameList($keyword){
		$data=array();
		$condition=array('name'=>new MongoRegex("/$keyword/"));
		$fields=array('name');
		for($i=0;$i<4;$i++){
			$this->mdb->selectDb(MONGO_PERFIX.$i);
			$list=$this->mdb->find('characters',$condition,$fields);
			$data=array_merge($data,$list);
		}
		return json_encode($data);
	}

	//帐号模糊查询
	public function getAccountList($keyword){
		$condition=array('account'=>new MongoRegex("/$keyword/"));
		$fields=array('account','serverId','_id'=>false);
		$this->mdb->selectDb(MONGO_PERFIX.'4');
		$data=$this->mdb->find('account_data',$condition,$fields);
		return json_encode($data);
	}

	//帮派模糊查询
	public function getFactionList($keyword){
		$condition=array('name'=>new MongoRegex("/$keyword/"));
		$this->mdb->selectDb(MONGO_PERFIX.'5');
		$fields=array('name','_id'=>false);
		$data=$this->mdb->find('faction',$condition,$fields);
		return json_encode($data);
	}
}
//发布接口
$server = new PHPRPC_Server();
$server->add(get_class_methods(Player),new Player);
$server->setEnableGZIP(true);
$server->start();
unset($server);
?>
