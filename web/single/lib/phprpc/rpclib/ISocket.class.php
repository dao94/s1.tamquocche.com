<?php

interface ISocket {

	function rpcWrite($netInfo, $buf); //$netInfo NetInfo 对象

	function rpcBridging();  //返回一个 RpcBridging 对象

	function rpcId();
}

?>