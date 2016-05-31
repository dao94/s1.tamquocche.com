<?php
class CheckTQC extends Check {
	//参数转换
	function __construct() {
		//处理url参数, 各个代理的参数可能不同 特别是特殊代理 转换成内部统一参数
		$makeSign = array();
		$makeSign['username'] = isset($_REQUEST['username']) ? urldecode(trim($_REQUEST['username'])) : '';
		$makeSign['time'] = $this->params['time'] = isset($_REQUEST['time']) ? intval($_REQUEST['time']) : 0;
		$this->params['sign'] = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
		$this->params['flag'] = self::makeSign($makeSign, BAK_KEY);
		$sid=isset($_REQUEST['server']) ? intval(trim($_REQUEST['server'], 'S')) : 0;
		$this->params['accounts']=array("{$sid}:{$makeSign['username']}");
	}

	//校验规则
	static function makeSign($params, $key,$urlencode=true) {
		return md5($params['time'].$params['username'].$key);
	}

	//返回值
	protected function apiReturn($ret=1,$data=array()){
		//-1:参数缺失 -2:验证失败 -3:用户不存在
		$arr=array();
		switch ($ret){
			case 8000:
				header('Return-Code:-1');
				break;

			case 1:
			case 8001:
			case 8002:
			case 8007:
				header('Return-Code:-2');
				break;

			case 8003:
				header('Return-Code:-3');
				break;

			case 0:
				if(empty($data[$this->params['accounts'][0]])){
					header('Return-Code:-3');
				}else{
					header('Return-Code:0');
					include __AUTH__.'lang.php';
					include __CONFIG__.'game_config.php';

					$item=$data[$this->params['accounts'][0]];
					$char_id=floatval($item['id']);
					$mdb=new Mdb();
					$mdb->selectDb(MONGO_PERFIX.$char_id%4);
					$list=$mdb->findOne('character_bag',array('_id'=>$char_id),array('moneyList'));
					$remain_count=empty($list['moneyList'][3]) ? 0 : $list['moneyList'][3];
					$arr=array(
						array(
							'username'=>$item['account'],
							'role_id'=>$item['id'],
							'role'=>$item['name'],
							'effect_time'=>$this->params['time'],
							'job'=>isset($occ_conf[$item['occ']]) ? $occ_conf[$item['occ']] : $item['occ'],
							'level'=>$item['level'],
							'creation_time'=>$item['creat_time'],
							'last_logon_time'=>isset($item['loginTime']) ? $item['loginTime'] : $item['creat_time'],
							'remain_count'=>$remain_count,
						),
					);
				}
				break;
		}
		exit(json_encode($arr));
	}
}
?>