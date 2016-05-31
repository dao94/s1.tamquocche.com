<?php

//rpc桥接
class RpcBridging {

	private $socket;  //ISocket 实现的对象
	private $_session;  //session 对象

	public function session() {
		return $this->_session;
	}

	public function id() {

		return $this->socket->rpcId;
	}

	public function bindSession($sess) {

		$this->_session = $sess;
	}

	public function bindSocket($sock) {

		$this->socket = $sock;
	}

	//$netInfo NetInfo 对象
	public function write($netInfo, $buf) {

		//$buf->position	=	0;
		//接口实现 rpcWrite
		return $this->socket->rpcWrite($netInfo, $buf);
	}

	public function handlePacket($netInfo, $buf) {

		return Operator::handlePacket($this->_session, $netInfo, $buf);
	}

	public function handleTimeout() {

		Operator::handleTimeout($this->_session);
	}

}

?>