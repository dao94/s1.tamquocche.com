<?php
//角色信息类
class Character{
	var $mdb=null;

	public function __construct(){
		include_once __CLASSES__.'Mdb.class.php';
		$this->mdb=new Mdb();
	}

	public function getCharacterById($id){
		$this->mdb->selectDb(MONGO_PERFIX.$id%4);
		return $this->mdb->findOne('characters',array('_id'=>floatval($id)));
	}

	//基本信息
	public function getCharacterByCondition($condition){
		$char=array();
		for ($i=0;$i<4;$i++){
			$this->mdb->selectDb(MONGO_PERFIX.$i);
			$char=$this->mdb->findOne('characters', $condition);
			if($char)	break;
		}
		return $char;
	}
	
	/**
	 +----------------------------------------------------------
	 * 角色封禁
	 +----------------------------------------------------------
	 * @param int $type 封禁类型 1=禁言 2=封号 3=封ip
	 * @param array $data 入库数据 
	 * $data=array(
	 *	'char_id'=>角色id(int),
	 *	'start_time'=>开始时间（int）,
	 *	'end_time'=>失效时间（int）,
	 *	'type'=>封禁类型（int）
	 *	'ip'=>'封禁IP(string)'
	 * );
	 +----------------------------------------------------------
	 * @return Boolean
	 +----------------------------------------------------------
	 */
	public function forbid($data){
		$result=false;
		$this->mdb->selectDb(MONGO_PERFIX.'5');
		$condition['type']=$data['type'];
		if($data['type']==1 || $data['type']==2){
			$condition['char_id']=$data['char_id'];
		}else if($data['type']==3){
			$condition['ip']=$data['ip'];
		}else{
			return false;
		}
		$count=$this->mdb->count('bg_control', $condition);
		if($count>0){
			//更新数据
			$result=$this->mdb->update('bg_control',$condition,$data);
		}else{
			//插入数据
			$result=$this->mdb->insert('bg_control',$data);
		}
		if(!$result){
			return false;	
		}
		//发协议GM
		include __CLASSES__.'Forbid.class.php';
		$forbid=new Forbid();
		if($data['type']==1){
			$forbid->forbidSpeak($data['char_id'], $data['end_time']);
		}elseif($data['type']==2){
			$forbid->forbidAccount($data['char_id'], $data['end_time']);
		}elseif($data['type']==3){
			$forbid->forbidIp($data['ip'], $data['end_time']);
		}
		return true;
	}

	/**
	 +----------------------------------------------------------
	 * 角色解封
	 +----------------------------------------------------------
	 * @param array $data 入库数据 
	 * $data=array(
	 *	'char_id'=>解封角色id(int),
	 *	'type'=>解封类型（int）
	 *	'ip'=>'解封IP(string)'
	 * );
	 +----------------------------------------------------------
	 * @return Boolean
	 +----------------------------------------------------------
	 */
	public function unforbid($data){
		$this->mdb->selectDb(MONGO_PERFIX.'5');
		$result=$this->mdb->remove('bg_control',$data);
		if($result){
			include __CLASSES__.'Forbid.class.php';
			$forbid=new Forbid();
			if($data['type']==1){
				$forbid->forbidSpeak($data['char_id'], 0);
			}elseif($data['type']==2){
				$forbid->forbidAccount($data['char_id'], 0);
			}elseif($data['type']){
				$forbid->forbidIp($data['ip'], 0);
			}else{
				return false;
			}
			return true;
		}else{
			return false;
		}
	}

	function __destruct(){
		$this->mdb->close();
	}
}