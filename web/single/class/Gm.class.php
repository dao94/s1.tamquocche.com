<?php

include __CLASSES__ . 'GmBase.class.php';
class Gm extends GmBase {

	public function __construct() {
		$this->autoload();
	}

	private function autoload() {
		static $loaded = array();
		foreach ($this->_autoload as $file_key => $file_val) {
			if (!isset($loaded[$file_key])) {
				include __LIB__ . 'phprpc/' . $file_val;
				$loaded[$file_key] = '1';
			}
		}
		\DrSlump\Protobuf::autoload();
	}

	private function _autoload($rpc) {
		static $loaded = array();
		!isset($this->_loadconfig[$rpc]) && exit('can\'t find this rpcc');
		foreach ($this->_loadconfig[$rpc] as $file_key => $file_val) {
			if (!isset($loaded[$file_key])) {
				include_once __LIB__ . 'phprpc/idl/' . $file_val;
				$loaded[$file_key] = '1';
			}
		}
	}

	private function createClient($host = GM_HOST, $port = GM_PORT) {
		$this->_client = new Client($host, $port);
	}

	private function createSour($rpc_obj = '') {
		$this->_sour = new $rpc_obj();
		ObjectMgr::registerObject($this->_sour);
		$this->_sour->_session = $this->_client->rpcBridging()->session();
	}

	/*
	 * rpc调用接口
	 * $rpc rpc包 一般为 ****.rpc 文件的名字
	 * $rpc_obj  本次使用的rpc对象 一般为 Sour_***的类名  在$rpc.php文件中找到次类
	 * $async    本次rpc对象调用的async接口 一般在$rpc_obj类中的名为 ***_async的方法名
	 * $msg_data 本次rpc调用所使用的protobuf对象 这个对象会根据上面的几个参数确定对象名，并根据本参数自动构建
	 * 如过async 接口需要使用回调对象，也会自动创建，但是在对应的Sour_包里面请手动编写Des_**实现对应的虚函数
	 */

	public function async($rpc, $rpc_obj, $async, $msg_data, $host = GM_HOST, $port = GM_PORT) {
		//加载类文件
		$this->_autoload($rpc);
		//创建客户端连接
		$this->createClient($host, $port);
		//创建源对象
		$this->createSour($rpc_obj);
		
		$msg = $this->_interfaceconfig[$rpc][$rpc_obj][$async][0];
		//创建msg对象
		$pbmsg = new $msg();
		$this->createMsg($msg_data, $pbmsg);
		//rpc调用 判断是否有回调
		if (count($this->_interfaceconfig[$rpc][$rpc_obj][$async]) > 2) {
			$desmsg = new $this->_interfaceconfig[$rpc][$rpc_obj][$async][1]();
			$this->_sour->{$async}($desmsg, $pbmsg);
		} else {
			$this->_sour->{$async}($pbmsg);
		}
		$this->_client->rpcRead();
	}

	/*
	 * 自动创建protomsg
	 */

	private function createMsg($msg_data, &$msg) {
		$msg_class = get_class($msg);
		foreach ($msg_data as $field_key => $field_val) {
			$_prefix = $this->_analyzeconfig[$msg_class][$field_key][0];
			$_type = $this->_analyzeconfig[$msg_class][$field_key][1];
			if ($_type === 1) {
				switch ($_prefix) {
					case 'add':
						foreach ($field_val as $sub_val) {
							$msg->{$_prefix . ucfirst($field_key)}($sub_val);
						}
						break;
					case 'set':
						$msg->{$_prefix . ucfirst($field_key)}($field_val);
						break;
				}
			} else {
				switch ($_prefix) {
					case 'add':
						foreach ($field_val as $sub_msg_data) {
							$sub_msg = new $_type();
							$this->createMsg($sub_msg_data, $sub_msg);
							$msg->{$_prefix . ucfirst($field_key)}($sub_msg);
						}
						break;
					case 'set':
						$sub_msg = new $_type();
						$this->createMsg($field_val, $sub_msg);
						$msg->{$_prefix . ucfirst($field_key)}($sub_msg);
						break;
				}
			}
		}
	}

}

?>
