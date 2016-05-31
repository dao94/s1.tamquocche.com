<?php
//查询玩家是否在本区创建了角色
include __API__ . 'lib/ApiBase.class.php';
class Check extends ApiBase {
	protected $paramsExchange = array(
        'account' => array('accounts', 'array_unique', 2, array()),
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
		static $checkObj = null;
		if (!is_null($checkObj)){
			return $checkObj;
		}
		$className = 'Check' . SERVER_AGENT;
		$classFile = __API__ . '/lib/' . SERVER_AGENT . '/' . $className . '.class.php';
		if (file_exists($classFile)) {
			include $classFile;
			$checkObj = new $className();
		} else {
			$checkObj = new self();
		}
		return $checkObj;
	}

	/*
	 * 超时验证
	 */

	protected function timeout() {
		if (abs(time() - $this->params['time']) > 120) {
			$this->apiReturn(8001);
		}
		return $this;
	}

	/*
	 * 验证码校验
	 */

	protected function checkCode() {
		if ($this->params['sign'] !== $this->params['flag']) {
			$this->apiReturn(8002);
		}
		return $this;
	}

	protected function checkAccounts() {
		//限制十个
		if (!is_array($this->params['accounts']) || count($this->params['accounts']) > 10)
		$this->apiReturn(8007);
		return $this;
	}

	/*
	 * 检查账号信息
	 */
	protected function check() {
		include __CLASSES__ . 'Player.class.php';
		$player = new Player();
		$exists = $player->players_exists($this->params['accounts'], 2);
		$this->apiReturn(0, $exists);
	}

	public function run() {
		$this->timeout()->checkCode()->checkAccounts()->check();
	}

}

?>
