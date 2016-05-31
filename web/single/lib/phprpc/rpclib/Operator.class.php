<?php

class Operator {

	public static function invokeAsync($proxyOjbect, $oper, $backObject, $msg) {

		$cont = new Context();
		$cont->_session = $proxyOjbect->_session;
		$cont->_netInfo = new NetInfo();
		$cont->_netInfo->_netNumber = $proxyOjbect->_netNumber;
		$cont->_netInfo->_netID = $proxyOjbect->_netID;
		$call = new Call();
		$call->_identity = $proxyOjbect->_identity;
		$call->_oper = $oper;
		$call->_msg = $msg;

		//优化部分

		$cont->_messageId = 0;

		if ($backObject != NULL) {

			$cont->_messageId = $cont->_session->insertBackObject(new BindObject($backObject));
		}
		//reset
		$SEND_BUFF = '';
		//head
		//调用头：（调用类型：1字节）+（消息id：4字节）
		$SEND_BUFF .= pack('C', MESSAGETYPE_CALL);
		$SEND_BUFF .= pack('V', $cont->_messageId);
		//（远程调用对象名：1字节字符串长度+字符串）

		$SEND_BUFF .= pack('C', strlen($call->_identity));
		$SEND_BUFF .= $call->_identity;
		//（远程调用方法偏移：2字节）
		$SEND_BUFF .= pack('v', $call->_oper); //在字节流中写入一个 16 位整数。
		if ($call->_msg) {
			$tmsg = Protocol::serializeToArray($call->_msg);
			$SEND_BUFF .= pack('V', strlen($tmsg));
			$SEND_BUFF .= $tmsg;
		} else {

			$SEND_BUFF .= pack('V', 0);
		}

		$cont->_session->rpcBridging()->write($cont->_netInfo, $SEND_BUFF);
	}

	public static function responseAsync($cont, $msg) {

		$SEND_BUFF = '';
		//1 字节 4 字节
		$SEND_BUFF .= pack('CV', MESSAGETYPE_CALLRET, $cont->_messageId);

		if ($msg) {
			$tempMsg = Protocol::serializeToArray($msg);
			$SEND_BUFF .= pack('V', strlen($tempMsg));
			$SEND_BUFF .= $tempMsg;
		} else {
			//4字节
			$SEND_BUFF .= pack('V', 0);
		}

		$cont->_session->rpcBridging()->write($cont->_netInfo, $SEND_BUFF);
	}

	public static $HANDLE_FUNC_DIC = array(
	MESSAGETYPE_CALL => 'handleCall',
	MESSAGETYPE_CALLRET => 'handleCallBack',
	MESSAGETYPE_MQ => 'handleStruct',
	MESSAGETYPE_SYNTIME => 'handleStruct',
	);

	public static function handlePacket($sess, $netInfo, $buf) {
		//调用类型
		$messageType = unpack('CMessageType', substr($buf, 0, 1));
		$tfunc = self::$HANDLE_FUNC_DIC[$messageType['MessageType']];

		if (in_array($tfunc, get_class_methods('Operator'))) {
			return self::$tfunc($sess, $netInfo, $buf);
		}
		return HANDLERETURN_BODY_ERROR;
	}

	public static function handleTimeout($sess) {

		$sess->handleTimeout();
	}

	private static function handleCall($sess, $netInfo, $buf) {


		$cont = new Context();
		$cont->_session = $sess;
		$cont->_netInfo = $netInfo;

		$cont->_netInfo->_netNumber = NetInfo::NET_NUMBER_toSwap($cont->_netInfo->_netNumber);

		$call = new Call();

		$unpackMsgId = unpack('VMsgId', substr($buf, 1, 4)); //读取四个字节的消息id

		$cont->_messageId = $unpackMsgId['MsgId'];
		$unpackIdHead = unpack('CIdHead', substr($buf, 5, 1)); //读取一个字节的调用消息字符串长度
		$idHead = $unpackIdHead['IdHead'];
		$call->_identity = substr($buf, 6, $idHead); //读取调用消息字符
		$unpackOper = unpack('vOper', substr($buf, 6 + $idHead, 2));  //读取两个自己的调用方法偏移
		$call->_oper = $unpackOper['Oper'];
		$unpackMsgLen = unpack('VMsgLen', substr($buf, 8 + $idHead, 4));

		$msgBuf = substr($buf, 12 + $idHead, $unpackMsgLen['MsgLen']);

		$backObj = ObjectMgr::getSingle()->findObject($call->_identity);


		if ($backObj != NULL) {
			$backObj->__dispatch($cont, $call, $msgBuf);
			return HANDLERETURN_OK;
		} else {
			echo 'findObject is not exist!';
			return HANDLERETURN_NOTEXIST;
		}
	}

	private static function handleCallBack($sess, $netInfo, $buf) {

		$cont = new Context();
		$cont->_session = $sess;
		$cont->_netInfo = $netInfo;

		$cont->_netInfo->_netNumber = NetInfo::NET_NUMBER_toSwap($cont->_netInfo->_netNumber);
		$unpackMsgId = unpack('VMsgId', substr($buf, 1, 4));
		$cont->_messageId = $unpackMsgId['MsgId'];

		$unpackMsgLen = unpack('VMsgLen', substr($buf, 5, 4));
		$msgLen = $unpackMsgLen['MsgLen'];


		$msgbuf = substr($buf, 9, $msgLen);

		$arr = $sess->findRemoveBackObject($cont->_messageId);
		$bindObj = $arr[1];
		if ($arr[0]) {
			$bindObj->__back($cont, $msgbuf);
			return HANDLERETURN_OK;
		} else {
			return HANDLERETURN_NOTEXIST;
		}
	}

}

?>