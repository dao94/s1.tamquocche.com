<?php

include_once 'rpclib/ISocket.class.php';

class Client implements ISocket {

	public $_bridging;
	public static $gClient;
	public $fp;

	public function __construct($host = GM_HOST, $port = GM_PORT) {
		$this->_bridging = new RpcBridging();
		self::$gClient = $this;
		SessionMgr::getSingle()->createSession($this->_bridging, $this);
		$this->fp = @fsockopen($host, $port, $errno, $errstr, 2) or ajax_return('error', 'gm connect failed!');
		//定义两秒超时，只读取解析两秒内的包
		stream_set_timeout($this->fp, 1, 0);
	}

	//发包
	public function rpcWrite($netInfo, $buf) {
		//包长	网络编号	网络ID	pbbuf
		$sendBuf = pack('V', strlen($buf) + 10) . pack('v', $netInfo->_netNumber) . pack('V', $netInfo->_netID) . pack('V', 0) . $buf;
		fwrite($this->fp, $sendBuf);
	}

	//取包
	public function rpcRead() {
		while (!feof($this->fp)) {
			$baglen = fread($this->fp, 4);  //读取4字节的包长
			if (strlen($baglen) < 4)
				break;
			$baglen = unpack('Vbaglen', $baglen);   //包长
			$baglen = $baglen['baglen'];

			$bag = fread($this->fp, $baglen);

			$num = unpack('vnum', substr($bag, 0, 2));
			$id = unpack('Vid', substr($bag, 2, 4));
			$rpcbag = substr($bag, 10, $baglen - 10);
			//处理回包
			$net = new NetInfo();
			$net->_netID = $id['id'];
			$net->_netNumber = $num['num'];
			$sess = $this->rpcBridging()->session();
			Operator::handlePacket($sess, $net, $rpcbag);
		}
	}

	public function rpcBridging() {
		return $this->_bridging;
	}

	public function rpcId() {
		return 0;
	}

	public function __destruct() {
		if (is_resource($this->fp))
		fclose($this->fp);
	}

}

?>