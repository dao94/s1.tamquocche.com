<?php

/*
 RpcObject	Rpc对象
 BindObject	绑定对象
 ObjectMgr	对象管理
 */

//Rpc对象
class RpcObject {

	// Context  Call 对象
	public function __dispatch($cont, $call, $buf) {
		return DISPATCH_OBJECT_NOTEXIST;
	}

	public function __response($oper, $buf) {

	}

	public function rpcException($oper, $err) {

	}

}

//绑定对象类
final class BindObject {

	private $rpcObject; //	RpcObject 对象
	private $_time;

	public function __construct($rpcObject, $timeout = 5) {

		$this->rpcObject = $rpcObject;   //rpcObj对象
		$this->_time = time() + $timeout; //超时
	}

	public function time() {
		return $this->_time;
	}

	// $cont  Context 对象
	public function __back($cont, $buf) {

		switch ($cont->_dispatchStatus) {

			case DISPATCH_OK:

				$this->rpcObject->__response($buf);

				break;

			case DISPATCH_TIMEOUT:

				$this->rpcObject->__exception(EXCEPTION_TIMEOUT);

				break;
		}
	}

}

//对象管理
class ObjectMgr {

	public static $objectMap = array();
	private static $single = NULL;

	//注册对象  $obj   rpcObject
	public static function registerObject($obj) {
		if (!isset(self::$objectMap[$obj->_identity])) {
			self::$objectMap[$obj->_identity] = $obj;

			return true;
		} else {
			return false;
		}
	}

	public function findObject($id) {
		return self::$objectMap[$id];
	}

	public static function getSingle() {
		if (self::$single == NULL) {

			self::$single = new ObjectMgr();
		}
		return self::$single;
	}

}

?>