<?php

class ProxyObject {

	public $_session;
	public $_identity;
	public $_netNumber;
	public $_netID;

}

//上下文
final class Context {

	public $_session;   //Session 对象
	public $_netInfo;   // NetInfo 对象
	public $_messageId = 0;
	public $_dispatchStatus = DISPATCH_OK;

}

class Call {

	public $_identity;
	public $_oper = -1;
	public $_msg; //protobuf Message 对象

}

class NetInfo {

	const NET_NUMBER_ERROR = 0;
	const NET_NUMBER_CLIENT = 1;
	const NET_NUMBER_BG = 2;
	const NET_NUMBER_LOGIN = 11;
	const NET_NUMBER_GATE = 21;
	const NET_NUMBER_MAP = 31;
	const NET_NUMBER_USER = 41;
	const NET_NUMBER_COMMON = 51;
	const NET_NUMBER_COMMON_SLAVE = 52;
	const NET_NUMBER_GM = 61;
	const NET_NUMBER_CHAT = 71;

	public $_netID;
	public $_netNumber;  //16 位

	public static function NET_NUMBER_toStruct($sour, $des) {
		return ($sour << 8) + $des;
	}

	public static function NET_NUMBER_toNumber($number) {
		return array($number >> 8, $number & 0x000000FF);
	}

	public static function NET_NUMBER_toSwap($number) {
		$arr = self::NET_NUMBER_toNumber($number);
		$sour = $arr[0];
		$des = $arr[1];
		return self::NET_NUMBER_toStruct($des, $sour);
	}

}

?>