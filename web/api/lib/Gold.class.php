<?php
//单服每天创建的角色列表
include __API__ . 'lib/ApiBase.class.php';
class Gold extends ApiBase {
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
		$start_time=strtotime($this->params['date']);
		$end_time=$start_time+86400;
		$cache_name="api_gold_{$start_time}";
		if(S($cache_name)){
			$data=S($cache_name);
		}else{
			$mysqli=new DbMysqli();
			//当天服中剩余的游戏币总数
			$where="where date='{$this->params['date']}' and money_type=3";
			$sql="select money_num from stat_money_real $where";
			$list=$mysqli->findOne($sql);
			$data['remain_count']=empty($list['money_num']) ? 0 : intval($list['money_num']);
			
			//当天总充值游戏币数目
			$sql="select sum(gold) as gold from pay_order where ts>=$start_time and ts<$end_time and is_test=0";
			$list=$mysqli->findOne($sql);
			$data['pay_count']=empty($list['gold']) ? 0 : intval($list['gold']);
			
			//非充值产出游戏币总数
			$sql="select sum(money_num) as money_num from log_money where time>=$start_time and time<$end_time and type!=62 and io=1 and money_type=3";
			$list=$mysqli->findOne($sql);
			$data['given_count']=empty($list['money_num']) ? 0 : $list['money_num'];
			
			//当天玩家消耗游戏币数目
			$sql="select sum(money_num) as money_num from log_money where time>=$start_time and time<$end_time and money_type=3 and io=0 and type not in (5,6) group by money_type";
			$list=$mysqli->findOne($sql);
			$data['used_count']=empty($list['money_num']) ? 0 : $list['money_num'];
			
			S($cache_name,$data);
		}
		$this->apiReturn(0, $data);
	}

	public function run() {
		$this->timeout()->checkCode()->checkDate()->getData();
	}

}
?>
