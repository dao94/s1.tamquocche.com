<?php
class ForbidTQC extends Forbid {
	//参数转换
	function __construct() {
		//处理url参数, 各个代理的参数可能不同 特别是特殊代理 转换成内部统一参数
		$makeSign = array();
		$makeSign['type']=isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
		$makeSign['nickname'] = isset($_REQUEST['nickname']) ? urldecode(trim($_REQUEST['nickname'])) : '';
		$makeSign['minutes'] = isset($_REQUEST['minutes']) ? intval($_REQUEST['minutes']) : 0;
		$makeSign['banTime'] = isset($_REQUEST['banTime']) ? intval($_REQUEST['banTime']) : 0;
		$makeSign['time'] = isset($_REQUEST['time']) ? intval($_REQUEST['time']) : 0;
		$makeSign['reason'] = isset($_REQUEST['reason']) ? urldecode(trim($_REQUEST['reason'])) : '';
		$makeSign['username'] = isset($_REQUEST['username']) ? urldecode(trim($_REQUEST['username'])) : '';
		if(!$makeSign['type'] || !$makeSign['nickname'] || (!$makeSign['minutes']&&!$makeSign['banTime']&&$makeSign['type']!=4)){
			$this->apiReturn(8000);
		}
		$this->params['sign'] = isset($_REQUEST['ticket']) ? $_REQUEST['ticket'] : '';
		$this->params['type']=$makeSign['type'];
		$this->params['name']=$makeSign['nickname'];
		$this->params['ban_time']=empty($makeSign['minutes']) ? $makeSign['banTime']*60 : $makeSign['minutes']*60;
		$this->params['time']=$makeSign['time'];
		$this->params['reason']=$makeSign['reason'];
		$this->params['flag'] = self::makeSign($makeSign, LOGIN_KEY);
	}

	//校验规则
	static function makeSign($params, $key,$urlencode=true) {
		switch ($params['type']){
			case 1:
				//禁言
				return md5($params['nickname'].$params['minutes'].$params['time'].$key);
				break;
			case 2:
				//封号
				return md5($params['nickname'].$params['username'].$params['banTime'].$params['time'].$key);
				break;
			case 4:
				//踢人下线
				return md5($params['nickname'].$params['time'].$key);
				break;
			default:
				return null;
		}
	}

	//返回值
	protected function apiReturn($ret=1,$data=array()){
		$arr=array();
		switch ($ret){
			case 8000:
				$arr=array('code'=>0,'msg'=>'parameters error');
				break;
			case 8001:
				$arr=array('code'=>0,'msg'=>'time error');
				break;
			case 8002:
				$arr=array('code'=>0,'msg'=>'sign error');
				break;
			case 8003:
				$arr=array('code'=>0,'msg'=>'user error');
				break;
			case 0:
				$arr=array('code'=>1,'msg'=>'succeed');
				break;
			default:
				$arr=array('code'=>0,'msg'=>'failed');
				break;
		}
		exit(json_encode($arr));
	}
}
?>