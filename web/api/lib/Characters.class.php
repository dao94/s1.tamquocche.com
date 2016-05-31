<?php
include __API__ . 'lib/ApiBase.class.php';
class Characters extends ApiBase {
	protected $paramsExchange = array(
		'date'=>array('date','my_escape_string',2,''),
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
	
	//检测日期格式
	protected function checkDate(){
		if($this->params['date']!=date('Y-m-d',strtotime($this->params['date']))){
			$this->apiReturn(8007);
		}
		return $this;
	}

	//单服每天角色等级＋在线时长
	protected function getData() {
		$start_time=strtotime($this->params['date']);
		$end_time=$start_time+86400;
		$cache_name="api_characters_{$end_time}";
		$data='';
		if(S($cache_name)){
			$data=S($cache_name);
		}else{
			$mdb=new Mdb();
			$mysqli=new DbMysqli();
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$condition=array('creat_time'=>array('$lt'=>$end_time),'loginTime'=>array('$gte'=>$start_time));
				$list=$mdb->find('characters', $condition, array('account','name','level'));
				foreach ($list as $row){
					//当天在线时长
					$sql="select sum(logout_time)-sum(login_time) as time from log_login where login_time>=$start_time 
						and login_time<$end_time and char_id={$row['_id']} and logout_time>0";
					$login=$mysqli->findOne($sql);
					$online=intval($login['time']);
					$data.=$row['name']."\t".$row['account']."\t".$row['level']."\t".$online."\n";
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
