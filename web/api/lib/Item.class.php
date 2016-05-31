<?php
include __API__ . 'lib/ApiBase.class.php';
class Item extends ApiBase {
	protected $paramsExchange = array(
        'time' => array('time', 'intval', 2, ''),
        'sign' => array('sign', 'my_escape_string', 1, '')
	);
	protected $params = array();
	
	public function __construct() {
		$makeSign = array();
		foreach ($this->paramsExchange as $key => $val) {
			($isset = !isset($_REQUEST[$val[0]])) && $val[2] > 0 && $this->apiReturn(8000);
			$this->params[$key] = $isset ? $val[3] : $val[1]($_REQUEST[$val[0]]);
			$val[2] == 2 && $makeSign[$val[0]] = $_REQUEST[$val[0]];
		}
		$this->params['flag'] = self::makeSign($makeSign, BAK_KEY);
	}

	//工厂方法
	final static function factory() {
		static $obj = null;
		if (!is_null($obj)){
			return $obj;
		}
		$className = __CLASS__ . SERVER_AGENT;
		$classFile = __API__ . '/lib/' . SERVER_AGENT . '/' . $className . '.class.php';
		if (file_exists($classFile)) {
			include $classFile;
			$obj = new $className();
		} else {
			$obj = new self();
		}
		return $obj;
	}

	//超时验证
	protected function timeout() {
		if (abs(time() - $this->params['time']) > 300) {
			$this->apiReturn(8001);
		}
		return $this;
	}

	//验证码校验
	protected function checkCode() {
		if ($this->params['sign'] !== $this->params['flag']) {
			$this->apiReturn(8002);
		}
		return $this;
	}

	//道具列表
	protected function getData() {
		$cache_name='api_item';
		if(S($cache_name)){
			$data=S($cache_name);
		}else{
			include __LIB__.'phprpc_php/phprpc_client.php';
			$phprpc_client = new PHPRPC_Client();
			$phprpc_client->useService('http://'.CENTER_DOMAIN.'/center/app/interface/item_info.php');
			$phprpc_client->setKeyLength(128);
			$phprpc_client->setEncryptMode(2);
			$phprpc_client->setTimeout(20);
			$data=$phprpc_client->search('|');
			if(is_a($data, 'PHPRPC_Error')){
				$this->apiReturn(1, $data);
			}else{
				S($cache_name,$data,3600*6);
			}
		}
		$this->apiReturn(0, $data);
	}

	public function run() {
		$this->timeout()->checkCode()->getData();
	}

}
?>
