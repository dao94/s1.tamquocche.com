<?php
include __API__ . 'lib/ApiBase.class.php';
class Online extends ApiBase {
	protected $paramsExchange = array(
		'start_time' => array('start_time', 'my_escape_string', 1, ''),
		'time' => array('end_time', 'intval', 2, ''),
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
		$className = 'Online' . SERVER_AGENT;
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
	
	//时间验证
	protected function checkTime(){
		if($this->params['start_time']!=date('YmdHi',strtotime($this->params['start_time']))){
			$this->apiReturn(8007);
		}elseif(!empty($this->params['end_time'])&&$this->params['end_time']!=date('YmdHi', strtotime($this->params['end_time']))){
			$this->apiReturn(8007);
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
	protected function getOnline(){
		$where='where time>='.strtotime($this->params['start_time']);
		$where.=isset($this->params['end_time']) ? ' and time<='.strtotime($this->params['end_time']) : '';
		$sql="select time,count,ip as ip_count from log_online $where order by time desc";
		$mysqli=new DbMysqli();
		$list=$mysqli->find($sql);
		$this->apiReturn(0,$list);
	}
	
	public function run() {
		$this->timeout()->checkCode()->checkTime()->getOnline();
	}

}

?>
