<?php
//排行榜
include __API__ . 'lib/ApiBase.class.php';
class Top extends ApiBase {
	protected $paramsExchange = array(
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
		if (abs(time() - $this->params['time']) > 500) {
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

	//获取排行榜信息
	protected function getData() {
		$game_config=__CONFIG__.'game_config_'.SERVER_AGENT.'.php';
		if(is_file($game_config)){
			include $game_config;
		}else{
			include __CONFIG__.'game_config.php';
		}
		$date=date('Y-m-d',$this->params['time']-86400);
		$mysqli=new DbMysqli();
		//人物战力排行
		$sql="select rank from stat_rank where date='$date' and action='personal' and type='fight'";
		$list=$mysqli->findOne($sql);
		$personal_fight=empty($list) ? array() : json_decode($list['rank'],true);
		$personal_fight=array_slice($personal_fight,0,200);
		foreach ($personal_fight as &$row){
			$row['occ']=isset($occ_conf[$row['occ']]) ? $occ_conf[$row['occ']] : $row['occ'];
			$row['camp']=isset($camp_conf[$row['camp']]) ? $camp_conf[$row['camp']] : $row['camp'];
		}
		//人物等级排行
		$sql="select rank from stat_rank where date='$date' and action='personal' and type='lvl'";
		$list=$mysqli->findOne($sql);
		$personal_lvl=empty($list) ? array() : json_decode($list['rank'],true);
		$personal_lvl=array_slice($personal_lvl,0,200);
		foreach ($personal_lvl as &$row){
			$row['occ']=isset($occ_conf[$row['occ']]) ? $occ_conf[$row['occ']] : $row['occ'];
			$row['camp']=isset($camp_conf[$row['camp']]) ? $camp_conf[$row['camp']] : $row['camp'];
		}
		//宠物战力
		$sql="select rank from stat_rank where date='$date' and action='pet' and type='fight'";
		$list=$mysqli->findOne($sql);
		$pet_fight=empty($list) ? array() : json_decode($list['rank'],true);
		$pet_fight=array_slice($pet_fight,0,200);
		
		//铜币排行
		$sql="select rank from stat_rank where date='$date' and action='money' and type='0'";
		$list=$mysqli->findOne($sql);
		$money_rank=empty($list) ? array() : json_decode($list['rank'],true);
		$money_rank=array_slice($money_rank,0,200);
		foreach ($money_rank as &$row){
			$row['occ']=isset($occ_conf[$row['occ']]) ? $occ_conf[$row['occ']] : $row['occ'];
		}
		
		if(!$personal_fight && !$personal_lvl && !$pet_fight && !$money_rank){
			$this->apiReturn(0);	
		}
		$data=array(
			'desc'=>array(
				'personal_fight'=>array(
					'name'=>__('人物战力排行'),
					'show'=>array(
						'_id'=>__('角色id'),
						'fight'=>__('战斗力'),
						'num'=>__('排名'),
						'faction_name'=>__('帮派名称'),
						'account'=>__('账号'),
						'camp'=>__('阵营'),
						'name'=>__('玩家角色'),
						'occ'=>__('职业'),
						'level'=>__('等级'),
						'serverId'=>__('区服'),
					),
				),
				'personal_lvl'=>array(
					'name'=>__('人物等级排行'),
					'show'=>array(
						'_id'=>__('角色id'),
						'lvl'=>__('等级'),
						'num'=>__('排名'),
						'faction_name'=>__('帮派名称'),
						'account'=>__('账号'),
						'camp'=>__('阵营'),
						'name'=>__('玩家角色'),
						'occ'=>__('职业'),
						'serverId'=>__('区服'),
					),
				),
				'pet_fight'=>array(
					'name'=>__('宠物战力排行'),
					'show'=>array(
						'_id'=>__('宠物id'),
						'fight'=>__('战斗力'),
						'name'=>__('宠物名称'),
						'owner'=>__('角色id'),
						'num'=>__('排名'),
						'char_name'=>__('角色名'),
						'account'=>__('账号'),
					),
				),
				'money_rank'=>array(
					'name'=>__('铜币排行'),
					'show'=>array(
						'_id'=>__('角色id'),
						'account'=>__('账号'),
						'name'=>__('角色名称'),
						'level'=>__('角色等级'),
						'num'=>__('排名'),
						'loginTime'=>__('最近登陆时间'),
						'creat_time'=>__('角色创建时间'),
						'occ'=>__('职业'),
						'moneyList'=>array(
							0=>__('铜币'),
							1=>__('铜券'),
							2=>__('元宝'),
							3=>__('礼券'),
						)
					),
				),
			),
			'data'=>array(
				'personal_fight'=>$personal_fight,
				'personal_lvl'=>$personal_lvl,
				'pet_fight'=>$pet_fight,
				'money_rank'=>$money_rank,
			)
		);
		$this->apiReturn(0, $data);
	}

	public function run() {
		$this->timeout()->checkCode()->getData();
	}

}
?>
