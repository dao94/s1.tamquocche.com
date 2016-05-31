<?php
//充值接口基类（工厂类）
include __API__ . 'lib/ApiBase.class.php';
class Pay extends ApiBase {
	protected $params = array();//内部字段参数
	protected $userInfo = array();//玩家信息
	protected $log = array();//日志记录器
	protected $ip = '';//访问ip
	protected $mysqli = NULL;
	protected $mdb = NULL;
	protected $isAdd = false;//是否为补单

	/*
	 * 参数转换设置字段
	 * 内部字段 => array(url字段，参数校验函数，（0：可有可无，1：必须有，2：参与校验），默认值)
	 */
	protected $paramsExchange = array(
		'sid' => array('sid', 'intval', 2, ''),
		'order_id' => array('order_id', 'my_escape_string', 2, ''),
		'account' => array('account', 'my_escape_string', 2, ''),
		'money' => array('money', 'floatval', 2, ''),
		'gold' => array('gold', 'intval', 2, ''),
		'time' => array('time', 'intval', 2, ''),
		'sign' => array('sign', 'my_escape_string', 1, ''),
	);

	/*
	 * 工厂方法
	 * 根据不同的代理构建不同的充值实例
	 */
	final static function factory($is_add = false) {
		static $payObj = null;
		if (!is_null($payObj)){
			return $payObj;
		}
		$className = __CLASS__ . SERVER_AGENT;
		$classFile = __API__ . '/lib/' . SERVER_AGENT . '/' . $className . '.class.php';
		if (file_exists($classFile)) {
			include $classFile;
			$payObj = new $className($is_add);
		} else {
			$payObj = new self($is_add);
		}
		return $payObj;
	}

	//处理url参数 各个代理的参数可能不同 特别是特殊代理 转换成内部统一参数
	protected function __construct($is_add = false) {
		//是否为补单操作
		if ($is_add === false) {
			$makeSign = array();
			foreach ($this->paramsExchange as $key => $val) {
				($isset = !isset($_REQUEST[$val[0]])) && $val[2] > 0 && $this->apiReturn(8000);
				$this->params[$key] = $isset ? $val[3] : $val[1]($_REQUEST[$val[0]]);
				$val[2] == 2 && $makeSign[$val[0]] = $_REQUEST[$val[0]];
			}
			$this->params['flag'] = self::makeSign($makeSign, PAY_KEY);
		}
		$this->isAdd = $is_add;
	}

	//写日志并返回结果（特殊代理特殊处理）
	protected function writeLog() {
		$this->log['time'] = date('Y-m-d H:i:s');
		$this->log['ip'] = empty($this->ip) ? get_client_ip() : $this->ip;
		$this->log['query'] = substr(http_build_query($_REQUEST), 0,2048);
		write_log($this->log, 'pay_'.date('Ym'));
	}

	/*
	 * 返回接口信息：
	 * 写错误日志并且返回错误信息退出程序
	 * $error 错误代码
	 */
	protected function apiReturn($ret = 1, $data = array()) {
		!isset(self::$retMsg[$ret]) && $ret = 1;
		$return = array(
			'ret' => $ret,
			'msg' => self::$retMsg[$ret],
			'data' => $data
		);
		$this->log['error'] = self::$retMsg[$ret];

		if ($this->mysqli != NULL) {
			$this->mysqli->close();
		}
		if($this->mdb!=NULL){
			$this->mdb->close();
		}
		exit(json_encode($return));
	}

	//超时校验
	protected function timeout() {
		if(defined('SERVER_DEBUG') && SERVER_DEBUG==1){
			return $this;
		}elseif (abs(time() - $this->params['time']) > 180) {
			$this->apiReturn(8001);
		}
		return $this;
	}

	//验证码校验
	protected function checkCode() {
		if ($this->params['sign'] != $this->params['flag']) {
			$this->apiReturn(8002);
		}
		return $this;
	}

	//验证ip是否为白名单ip
	protected function ip() {
		include __CONFIG__.'ip_config.php';
		$this->ip = get_client_ip();
		if (($ips && !in_array($this->ip, $ips)) && $this->ip!=$_SERVER['SERVER_ADDR']) {
			$this->apiReturn(8006);
		}
		return $this;
	}

	//检查货币和金额参数是否对，并且将货币*100不保留小数位
	protected function checkMoney() {
		if (!is_numeric($this->params['money']) || !is_numeric($this->params['gold']) || $this->params['money'] <= 0 || $this->params['gold'] <= 0) {
			$this->apiReturn(8004);
		}
		$this->params['money'] = intval($this->params['money'] * 100);
		return $this;
	}

	//检查是否存在账号，并且获取账号相关信息
	protected function checkAccount() {
		$player = new Player();
		$this->userInfo = $player->player_exists($this->params['sid'] . ':' . $this->params['account'], 2);
		if (empty($this->userInfo)) {
			$this->apiReturn(8003);
		}
		return $this;
	}

	//检测单号是否可用于充值
	protected function checkOrder() {
		$this->mysqli = new DbMysqli();
		$sql = "select count(*) as count from `pay_order` where `order_id`='{$this->params['order_id']}'";
		$count = $this->mysqli->count($sql);
		if ($count>0) {
			$this->apiReturn(8005);//重复订单
		}
		return $this;
	}

	//充值信息入mongo数据库
	protected function addMongo(){
		$this->mdb=new Mdb();
		$this->mdb->selectDb(MONGO_PERFIX.'4');
		$record=array(
			'orderId'=>$this->params['order_id'],//订单号
			'account'=>$this->params['account'],//账号
			'serverId'=>$this->params['sid'],//区id
			'charId'=>$this->userInfo['id'],//角号id
			'state'=>0,//0未用，1已用
			'jade'=>$this->params['gold'],//元宝数
			'time'=>$this->params['time'],//订单时间
		);
		return $this->mdb->insert('pay_order', $record);
	}


	//充值信息入mysql数据库
	protected function addMysql() {
		$year = date('Y', $this->params['time']);
		$month = date('m', $this->params['time']);
		$day = date('d', $this->params['time']);
		$gm=$add_ts='';
		$is_add=$this->isAdd ? 1 : 0;
		if ($is_add){
			$gm=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
			$add_ts=time();//补单时间
		}
		//判断是否为首冲
		$sql="select count(*) as count from pay_order where char_id={$this->userInfo['id']} and is_first=1";
		$count=$this->mysqli->count($sql);
		$is_first=$count>0 ? 0 : 1;
		$sql='insert into pay_order (order_id,sid,account,char_name,char_id,money,gold,ts,is_first,level,is_add,add_ts,gm,y,m,d) value ';
		$sql.="('{$this->params['order_id']}',{$this->params['sid']},'{$this->userInfo['account']}','{$this->userInfo['name']}',{$this->userInfo['id']},
			{$this->params['money']},{$this->params['gold']},{$this->params['time']},$is_first,{$this->userInfo['level']},$is_add,'$add_ts','$gm',$year, $month, $day)";
		return $this->mysqli->query($sql);
	}

	//通知GM协议
	protected function tellGm(){
		$gm=new Gm();
		$rpc='burpc/bg.rpc';
		$rpc_obj='burpc\\Sour_B2uPayOper';
		$async='payOrder_async';
		$msg=array(
			'orderId'=>$this->params['order_id'],
			'charId'=>$this->userInfo['id'],
			'state'=>0,
			'jade'=>$this->params['gold']);
		$gm->async($rpc, $rpc_obj, $async, $msg);
	}

	//充值到数据库操作，并发送协议
	protected function addOrder() {
		if(!$this->addMysql()||!$this->addMongo()){
			$this->apiReturn(1);
		}
		//$this->tellGm();
		$this->payActivity();
		$this->apiReturn(0);
	}

	//获取玩家总充值元宝数
	private function getTotalGold(){
		$sql="select sum(gold) as gold from pay_order where char_id={$this->userInfo['id']}";
		$list=$this->mysqli->findOne($sql);
		$total_gold=empty($list['gold']) ? 0 : intval($list['gold']);
		return $total_gold;
	}

	//开服N天元宝返利
	private function openReturn($activity_conf){
		$reward_email=array();
		$open_time=get_open_time();
		$start_time=strtotime(date('Ymd',$open_time));
		$end_time=strtotime(date('Ymd',$open_time))+86400*$activity_conf['end_day'];
		$return_gold=intval($this->params['gold']*$activity_conf['ratio']/100);
		if(!empty($activity_conf['max_gold'])){
			$total_gold=$this->getTotalGold();//总充值元宝，包括当前订单元宝
			$returned_gold=intval(($total_gold-$this->params['gold'])*($activity_conf['ratio']/100));//已返还的元宝
			$left_gold=$activity_conf['max_gold']-$returned_gold;//剩余返还元宝
			if($left_gold<=0){
				return $reward_email;
			}
			$return_gold=$left_gold>$return_gold ? $return_gold : $left_gold;
		}
		if($this->params['time']>=$start_time&&$this->params['time']<$end_time&&$return_gold>0){
			//在Thời gian范围内，返还元宝
			$reward_email[]=array(
				'title'=>$activity_conf['email_title'],
				'content'=>$activity_conf['email_content'],
				'moneyList'=>array('jade'=>$return_gold),
				'list'=>array(
					array('charId'=>$this->userInfo['id'],'emailId'=>uuid()),
				),
			);
		}
		return $reward_email;
	}

	//充值活动
	protected function payActivity(){
		$pay_activity_file=__CONFIG__.'pay_activity_config.php';
		is_file($pay_activity_file)&&include $pay_activity_file;
		$reward_email=array();//发放奖励邮件

		if(isset($pay_activity_conf['open_return'])){
			$reward_email=$this->openReturn($pay_activity_conf['open_return']);
		}

		if(isset($pay_activity_conf['time_return'])&&is_array($pay_activity_conf['time_return'])){
			foreach ($pay_activity_conf['time_return'] as $time_return){
				$start_time=strtotime($time_return['start_time']);
				$end_time=strtotime($time_return['end_time']);
				$return_gold=intval($this->params['gold']*$time_return['ratio']/100);
				if($this->params['time']>=$start_time&&$this->params['time']<$end_time&&$return_gold>0){
					//在Thời gian范围内，返还元宝
					$reward_email[]=array(
						'title'=>$time_return['email_title'],
						'content'=>$time_return['email_content'],
						'moneyList'=>array('jade'=>$return_gold),
						'list'=>array(
							array('charId'=>$this->userInfo['id'],'emailId'=>uuid()),
						),
					);
				}
			}
		}
		if($reward_email){
			$gm=new Gm();
			$rpc='borpc/boemail.rpc';
			$rpc_obj='borpc\\Sour_B2oEmail';
			$async='b2ocreateEmail_async';
			foreach ($reward_email as $email){
				$gm->async($rpc, $rpc_obj, $async, $email);
			}
			unset($reward_email);
		}
	}

	//程序的唯一执行接口
	public function run() {
		$this->timeout()->ip()->checkCode()->checkMoney()->checkAccount()->checkOrder()->addOrder();
	}

	//补单接口
	public function addRun($params) {
		$this->params = $params;
		$this->log['query'] = http_build_query($this->params);
		$this->checkMoney()->checkAccount()->checkOrder()->addOrder();
	}

	function __destruct(){
		$this->writeLog();
	}
}

?>