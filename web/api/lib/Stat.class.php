<?php
include __API__ . 'lib/ApiBase.class.php';
class Stat extends ApiBase {
	protected $paramsExchange = array(
		'date' => array('date', 'my_escape_string', 2, ''),
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
	
	protected function checkDate(){
		if($this->params['date']!=date('Y-m-d',strtotime($this->params['date']))){
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
	
	//获取数据
	protected function getData(){
		$start_time=strtotime($this->params['date']);
		$end_time=$start_time+86400;
		$mdb=new Mdb();
		$data['character_count']=$mdb->allCount('characters', array('creat_time'=>array('$gte'=>$start_time,'$lt'=>$end_time)));
	
		//登陆人数
		$mysqli=new DbMysqli();
		$sql="select count(distinct char_id) as count from log_login where login_time>=$start_time and login_time<$end_time";
		$list=$mysqli->findOne($sql);
		$data['login_people']=empty($list['count']) ? 0 : intval($list['count']);
		
		//在线峰值
		$sql="select max(count) as count from log_online where time>=$start_time and time<$end_time";
		$list=$mysqli->findOne($sql);
		$data['max_count']=empty($list['count']) ? 0 : intval($list['count']);
		
		$this->apiReturn(0,$data);
	}
	
	public function run() {
		$this->timeout()->checkCode()->checkCode()->getData();
	}

}

?>
