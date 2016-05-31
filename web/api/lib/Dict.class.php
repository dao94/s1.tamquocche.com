<?php
//游戏币流水相关配置字典
include __API__ . 'lib/ApiBase.class.php';
class Dict extends ApiBase {
	protected $paramsExchange = array(
		'date'=>array('date','my_escape_string',2,''),
        'time' => array('time', 'intval', 2, 0),
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
	
	//检测日期格式
	protected function checkDate(){
		if($this->params['date']!=date('Y-m-d',strtotime($this->params['date']))){
			$this->apiReturn(8007);
		}
		return $this;
	}

	//游戏币库存接口
	protected function getData() {
		include __AUTH__.'lang.php';
		include __CONFIG__.'log_config.php';
		include __LIB__.'phprpc_php/phprpc_client.php';
		
		$actions=$action_types=array();
		foreach ($money_type_conf as $type=>$name){
			$actions[]=array('id'=>$type,'name'=>urlencode($name),'type'=>$type);
			$action_types[]=array('id'=>$type,'desc'=>urlencode($name));
		}
		
		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService('http://'.CENTER_DOMAIN.'/center/app/interface/item_info.php');
		$phprpc_client->setTimeout(10);
		$result=$phprpc_client->search();
		$arr=json_decode($result,true);
		$items=array();
		foreach ($arr as $item){
			$items[]=array('item_id'=>$item[0],'name'=>urlencode($item[1]));	
		}
		$data=array(
			'actions'=>$actions,
			'action_types'=>$action_types,
			'items'=>$items,
		);
		$this->apiReturn(0, $data);
	}

	public function run() {
		$this->timeout()->checkCode()->checkDate()->getData();
	}

}
?>
