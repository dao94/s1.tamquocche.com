<?php
//封禁类
class Forbid{
	var $rpc='burpc/bg.rpc';
	var $rpc_obj='burpc\Sour_B2uBgOper';
	var $gm=null;

	//构造函数
	public function __construct(){
		include_once __CLASSES__.'Gm.class.php';
		$this->gm=new Gm();
	}

	//禁言
	public function forbidSpeak($char_id,$time){
		$this->rpc='borpc/bo_control.rpc';
		$this->rpc_obj='borpc\Sour_B2oBgOper';
		$async='controlSpeak_async';
		$msg_data=array(
			'charId'=>array($char_id),
			'time'=>$time,
		);
		$this->gm->async($this->rpc,$this->rpc_obj,$async,$msg_data);
	}

	//封号
	public function forbidAccount($char_id,$time){
		$async='controlAccount_async';
		$msg_data=array(
			'charId'=>array($char_id),
			'time'=>$time,
		);
		$this->gm->async($this->rpc,$this->rpc_obj,$async,$msg_data);
		$this->kickOffline($char_id);//踢人下线
	}

	//踢人下线
	public function kickOffline($char_id){
		$async='kickHuman_async';
		$msg_data=array(
			'doubleinfo'=>floatval($char_id),
		);
		$this->gm->async($this->rpc,$this->rpc_obj,$async,$msg_data);
	}

	//封IP
	public function forbidIp($ip,$time){
		$async='controlIp_async';
		$msg_data=array(
			'key'=>$ip,
			'value'=>$time,
		);
		$this->gm->async($this->rpc,$this->rpc_obj,$async,$msg_data);
	}

	public function __destruct(){
		unset($this->gm);
	}
}