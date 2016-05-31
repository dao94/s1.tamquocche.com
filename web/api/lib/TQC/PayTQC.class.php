<?php
class PayTQC extends Pay {
	/*
	 * 参数转换
	 */
	function __construct() {
		//处理url参数, 各个代理的参数可能不同 特别是特殊代理 转换成内部统一参数
		$makeSign = array();
		$makeSign['order'] = $this->params['order_id'] = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
		$makeSign['username'] = $this->params['account'] = isset($_REQUEST['username']) ? urldecode(trim($_REQUEST['username'])) : '';
		$makeSign['gold'] = $this->params['gold']=isset($_REQUEST['gold']) ? intval($_REQUEST['gold']) : 0;
		$makeSign['time'] = $this->params['time'] = isset($_REQUEST['time']) ? intval($_REQUEST['time']) : 0;
		$this->params['sign'] = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
		$this->params['money'] = isset($_REQUEST['money']) ? floatval($_REQUEST['money']) : 0;
		$this->params['sid'] = isset($_REQUEST['server']) ? intval(trim($_REQUEST['server'], 'S')) : 0;
		$this->params['flag'] = self::makeSign($makeSign, PAY_KEY);
	}

	/*
	 * 校验规则
	 */
	static function makeSign($params, $key,$urlencode=true) {
		return md5($params['order'].$params['username'].$params['gold'].$params['time'].$key);
	}

	/*
	 * 4399返回值
	 * 1=充值成功
	 * 2=订单之前已经充值成功
	 * -1=必要参数格式不对或者缺失
	 * -2=验证串匹配不正确
	 * -3=充值所对应的用户在游戏服里面还没有创建角色
	 * -4=请求的时间戳超时
	*/
	protected function apiReturn($ret = 1, $data = array()){
		$this->log['error'] = self::$retMsg[$ret];
		switch ($ret){
			case 8002:
				exit('-2');
				break;
			case 8003:
				exit('-3');
				break;
			case 8001:
				exit('-4');
				break;
			case 0:
				exit('1');
				break;
			case 8005:
				exit('2');
				break;
			default:
				exit('-1');
				break;
		}
		exit;
	}
}
?>