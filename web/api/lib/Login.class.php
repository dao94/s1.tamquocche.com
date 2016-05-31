<?php
//登录接口基类（工厂类）
include __API__ . 'lib/ApiBase.class.php';
class Login extends ApiBase {

	//url参数
	protected $params = array();
	protected $userInfo = array();
	/*
	 * 参数转换设置字段
	 * 内部字段 => array(url字段，参数校验函数，（0：可有可无，1：必须有，2：参与校验），默认值)
	 */
	protected $paramsExchange = array(
				'sid' => array('sid', 'intval', 2, ''),
				'account' => array('account', 'my_escape_string', 2, ''),
				'time' => array('time', 'intval', 2, ''),
				'sign' => array('sign', 'my_escape_string', 1, ''),
				'cm' => array('cm', 'intval', 0, 2),
				'debug' => array('debug', 'intval', 0, 0),
	);
	//游戏Link
	protected $urlconfig = array();

	function __construct() {
		include __CONFIG__ . 'key_config.php';
		include __CONFIG__ . 'url_config.php';
		$this->urlconfig = $url_config;
		//处理url参数	  各个代理的参数可能不同 特别是特殊代理 转换成内部统一参数
		$makeSign = array();
		foreach ($this->paramsExchange as $key => $val) {
			($isset = !isset($_REQUEST[$val[0]])) && $val[2] > 0 && $this->gotourl($this->urlconfig['xuan']);
			$this->params[$key] = $isset ? $val[3] : $val[1]($_REQUEST[$val[0]]);
			$val[2] == 2 && $makeSign[$val[0]] = $_REQUEST[$val[0]];
		}
		$this->params['flag'] = self::makeSign($makeSign, LOGIN_KEY);
		$this->fcmSwitch();
		$this->urlconfig['xuan']=str_replace(array('{{sid}}','{{account}}'),array($this->params['sid'],$this->params['account']),$this->urlconfig['xuan']);

		/*
		 * 参数字段说明：
		 * account 玩家账号
		 * sid     玩家区服
		 * time    时间戳
		 * cm      cm信息
		 * cm_first 第一次弹出时间
		 * flag    makeSign校验
		 * sign    平台校验参数字段
		 */
	}

	//工厂方法
	final static function factory() {
		//判断是否为后台登录
		if (isset($_GET['debug'])&&$_GET['debug']==1) {
			return new self();
		}
		$className = __CLASS__ . SERVER_AGENT;
		$classFile = __API__ . 'lib/' . SERVER_AGENT . '/' . $className . '.class.php';
		if (file_exists($classFile)) {
			include $classFile;
			$loginObj = new $className();
		} else {
			$loginObj = new self();
		}
		return $loginObj;
	}

	//超时验证
	protected function timeout() {
		if(defined('SERVER_DEBUG') && SERVER_DEBUG==1){
			return $this;
		}elseif (abs(time() - $this->params['time']) > 300) {
			$this->gotourl($this->urlconfig['xuan']);
		}
		return $this;
	}

	//验证码校验
	protected function checkCode() {
		if ($this->params['sign']=='' || $this->params['sign'] !== $this->params['flag']) {
			$this->gotourl($this->urlconfig['xuan']);
		}
		return $this;
	}

	//账号校验和新建
	protected function checkAccount() {
            	include __CLASSES__ . 'Mdb.class.php';
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '4');
		$where = $msg_data = array('account' => $this->params['account'], 'serverId' => intval($this->params['sid']));
		$user_info = $mongo->findOne('account_data', $where, array('account', 'char_id', 'serverId'));
		if (empty($user_info)) {
			$where['create_time'] = time();
			$result=$mongo->insert('account_data', $where);
			if(!$result){
				$this->gotourl($this->urlconfig['xuan']);
			}

			/*
			include __CLASSES__ . 'Gm.class.php';
			$gm = new Gm();
			$rpc = 'blrpc/bllogin.rpc';
			$rpc_obj = 'blrpc\Sour_B2lLoginCtrl';
			$async = 'b2lCreateAccount_async';
			$gm->async($rpc, $rpc_obj, $async, array('account' => $this->params['account'], 'serverId' => intval($this->params['sid'])), GM_HOST, BL_PORT);
			*/
		}
		$this->saveInfo();
		$this->gotourl('../index.php');
	}

	//保存登陆信息
	protected function saveInfo() {
		session_start();
		$_SESSION['__' . SERVER_TYPE . '_GAME_DATA'] = array(
			'account' => $this->params['account'],
			'sid' => $this->params['sid'],
			'cm' => $this->params['cm'],
			'cm_first' => $this->params['cm_first'],
			'from_flag'=>isset($this->params['from_flag']) ? intval($this->params['from_flag']) : 1,
			'debug'=>isset($this->params['debug']) ? $this->params['debug'] : 0,
		);
	}

	//统一的跳转接口
	protected function gotourl($where) {
		header('Location:' . $where);
		exit;
	}

	//判断防沉迷信息
	protected function fcmSwitch() {
		$switch = __SWITCH__ . 'fcm.txt';
		//文件存在表示防沉迷为开着的
		if (is_file($switch)) {
			$this->params['cm_first'] = intval(file_get_contents($switch));
			return;
		};
		$this->params['cm_first'] = 30;
		$this->params['cm'] = 1;
	}

	//运行方法（特殊代理特殊处理） 登录成功或者失败时的链接跳转
	public function run() {
		$this->timeout()->checkCode()->checkAccount();
	}
}
?>
