<?php
//单服每天创建的角色列表
include __API__ . 'lib/ApiBase.class.php';
class Create extends ApiBase {
	protected $paramsExchange = array(
		'date'=>array('date','my_escape_string',2,''),
        'time' => array('time', 'intval', 2, ''),
		'minhour'=>array('minhour', 'intval', 2, 0),
		'maxhour'=>array('maxhour', 'intval', 2, 23),
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
		if (abs(time() - $this->params['time']) > 300 || $this->params['minhour']<0 || $this->params['maxhour']>23) {
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

	//单服每天创建的角色列表
	protected function getData() {
		$start_time=strtotime($this->params['date']." {$this->params['minhour']}:00:00");
		$end_time=strtotime($this->params['date']." {$this->params['maxhour']}:59:59");
		$cache_name="api_create_{$start_time}_{$end_time}";
		$data=array();
		if(S($cache_name)){
			$data=S($cache_name);
		}else{
			$mdb=new Mdb();
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$condition=array('creat_time'=>array('$gte'=>$start_time,'$lte'=>$end_time));
				$list=$mdb->find('characters', $condition, array('account','name','creat_time'));
				foreach ($list as $row){
					$data[]=$row['name']."\t".$row['account']."\t".$row['creat_time'];
				}
			}
			S($cache_name,$data);
		}
		$this->apiReturn(0, $data);
	}

	public function run() {
		$this->timeout()->checkCode()->checkDate()->getData();
	}

}
?>
