<?php

//定义状态配置
//DispatchStatus

define('DISPATCH_OK', 0);
define('DISPATCH_TIMEOUT', 1);
define('DISPATCH_EXCEPTION', 2);
define('DISPATCH_OBJECT_NOTEXIST', 3);
define('DISPATCH_OPERATION_NOTEXIST', 4);

//HANDLERETURN

define('HANDLERETURN_OK', 0);
define('HANDLERETURN_LEN_ERROR', 1);
define('HANDLERETURN_BODY_ERROR', 2);
define('HANDLERETURN_NOTEXIST', 3);

//MessageType

define('MESSAGETYPE_CALL', 0);
define('MESSAGETYPE_CALLRET', 1);
define('MESSAGETYPE_MQ', 2);
define('MESSAGETYPE_SYNTIME', 3);

//ResponseType

define('RESPONSEIMD', 0);
define('RESPONSEDELAY', 1);

//RpcException
define('EXCEPTION_OVERFLOW', 101);
define('EXCEPTION_TIMEOUT', 102);
define('EXCEPTION_PACKERROR', 201);

include 'Session.class.php'; //会话
include 'RpcBridging.class.php'; //rpc桥接
include 'Rpc.class.php';  //rpc对象
include 'ProxyObject.class.php';
include 'Protocol.class.php';  //pbbuff 解析
include 'Operator.class.php';  //核心调用
?>