<?php
class CreateTQC extends Create {
	//参数转换
	function __construct() {
		//处理url参数, 各个代理的参数可能不同 特别是特殊代理 转换成内部统一参数
		$makeSign = array();
		$makeSign['time'] = $this->params['time'] = isset($_REQUEST['time']) ? intval($_REQUEST['time']) : 0;
		$this->params['date'] = isset($_REQUEST['date']) ? my_escape_string(trim($_REQUEST['date'])) : '';
		$this->params['minhour'] = isset($_REQUEST['minhour']) ? intval(trim($_REQUEST['minhour'])) : 0;
		$this->params['maxhour'] = isset($_REQUEST['maxhour']) ? intval(trim($_REQUEST['maxhour'])) : 23;
		$this->params['sign'] = isset($_REQUEST['flag']) ? trim($_REQUEST['flag']) : '';
		$this->params['flag'] = self::makeSign($makeSign, BAK_KEY);
	}

	//校验规则
	static function makeSign($params, $key,$urlencode=true) {
		return md5($params['time'].$key);
	}

	//返回值
	protected function apiReturn($ret=1,$data=array()){
		//-1:参数缺失 -2:验证失败 ,-3:时间验证超时，链接已经失效
		$arr=array();
		switch ($ret){
			default:
			case 8000:
				header('Error-info:-1');
				break;

			case 1:
			case 8002:
			case 8007:
				header('Error-info:-2');
				break;

			case 8001:
				header('Error-info:-3');
				break;

			case '0':
				echo implode("\n",$data);
				break;
		}
		exit;
	}
}
?>