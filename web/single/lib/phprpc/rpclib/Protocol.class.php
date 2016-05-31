<?php

final class Protocol {

	const MSG_PRINT_ENCODE = 1;
	const MSG_PRINT_DECODE = 2;
	const MSG_PRINT_ALL = 3; //self::MSG_PRINT_ENCODE | self::MSG_PRINT_DECODE;

	private static $_msgPrint = 0;

	// msg Message 对象
	private static function printMessage($msg, $msgFlag) {
		if ($msgFlag & self::$_msgPrint) {
			if ($msgFlag & self::MSG_PRINT_ENCODE) {
				echo "--send \n";
			} else {
				echo "--recv \n";
			}

			echo $msg->serialize() . "\n";  //Protobuf PBMessage 对象 调用__toString()魔术方法
		}
	}

	//设置message打印
	public static function setMsgPrintFlag($msgFlag) {
		self::$_msgPrint = $msgFlag;
	}

	public static function parseFromArray($msg, $buf) {
		$msg->ParseFromString($buf);     //PBmessage 函数
		self::printMessage($msg, self::MSG_PRINT_DECODE);
	}

	public static function serializeToArray($msg) {
		$buf = '';
		if ($msg) {
			self::printMessage($msg, self::MSG_PRINT_ENCODE);
			$buf = $msg->serialize();
		}

		return $buf;
	}

}

?>