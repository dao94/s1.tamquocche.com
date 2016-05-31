<?php
//玩家封禁
include __API__ . 'lib/ApiBase.class.php';
class Forbid extends ApiBase {
	protected $paramsExchange = array(
		'type'=>array('type','intval',2,''),//封禁类型 1=禁言 2=封号 3=封IP 4=踢人下线
		'name' => array('name', 'my_escape_string', 0, ''),//角色名
		'ip' => array('name', 'my_escape_string', 0, ''),//  封禁ip
		'ban_time' => array('ban_time', 'intval', 2, ''),//封禁时长
		'time' => array('time', 'intval', 2, ''),
		'reason'=>array('reason','my_escape_string',2,''),
		'sign' => array('sign', 'my_escape_string', 1, '')
	);
	protected $params = array();

	public function __construct(){
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

	//封禁处理
	protected function forbid(){
		include __LIB__ . 'phprpc_php/phprpc_client.php';
		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService('http://'.$_SERVER['HTTP_HOST'].'/single/app/interface/gm_api.php');
		$phprpc_client->setKeyLength(128);
		$phprpc_client->setEncryptMode(2);
		$phprpc_client->setTimeout(8);
		$data=array(
			'type'=>$this->params['type'],
			'ip'=>empty($this->params['ip']) ? '' : $this->params['ip'],
			'name'=>empty($this->params['name']) ? '' : $this->params['name'],
			'time'=>$this->params['ban_time'],//封禁时长
			'reason'=>$this->params['reason'],
			'gm_account'=>'api',
			'gm_ip'=>get_client_ip(),
		);
		$action=$this->params['ban_time']==1 ? 'unforbid' : 'forbid';
		$result=$phprpc_client->$action($data);
		if(is_array($result)&&$result['status']==0){
			switch ($result['info']){
				case 'parameters error':
					$this->apiReturn(8000);
					break;
				case 'user error':
					$this->apiReturn(8003);
					break;
				case 'data exist':
					$this->apiReturn(8008);
					break;
				case 'data not exist':
					$this->apiReturn(8009);
					break;
				default:
					$this->apiReturn(1);
					break;
			}
		}elseif(is_array($result)&&$result['status']==1){
			$this->apiReturn(0);
		}else{
			$this->apiReturn(1);
		}
	}

	public function run() {
		$this->timeout()->checkCode()->forbid();
	}
}

?>
