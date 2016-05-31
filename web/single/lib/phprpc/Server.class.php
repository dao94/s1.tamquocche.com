<?php

include_once 'rpclib/ISocket.class.php';

class Server implements ISocket {

	public $_bridging;
	public static $gServer;

	public function __construct() {
		$this->_bridging = new RpcBridging();
		self::$gServer = $this;
		SessionMgr::getSingle()->createSession($this->_bridging, $this);
	}

	public function rpcWrite($netInfo, $buf) {

		Client::$gClient->_bridging->handleTimeout();
		Client::$gClient->_bridging->handlePacket($netInfo, $buf);
		Client::$gClient->_bridging->handleTimeout();

		return strlen($buf);
	}

	public function rpcBridging() {
		return $this->_bridging;
	}

	public function rpcId() {
		return 1;
	}

}

?>