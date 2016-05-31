<?php
//根据角色查询玩家账号
include __API__ . 'lib/ApiBase.class.php';
class Account extends ApiBase {
	protected $paramsExchange = array(
		'name' => array('name', 'my_escape_string', 2, ''),
		'sid' => array('sid', 'intval', 2, ''),
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
		if(defined('SERVER_DEBUG') && SERVER_DEBUG==1){
			return $this;
		}elseif (abs(time() - $this->params['time']) > 300) {
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
	
	//获取在线数据
	protected function getAccount(){
		$Character=new Character();
		$condition=array('name'=>$this->params['name'],'serverId'=>$this->params['sid']);
		$list=$Character->getCharacterByCondition($condition);
		if(empty($list['name'])){
			$this->apiReturn(8003);
		}
		$this->apiReturn(0,$list);
	}
	
	public function run() {
		$this->timeout()->checkCode()->getAccount();
	}

}

?>
