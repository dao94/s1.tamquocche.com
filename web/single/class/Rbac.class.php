<?php

//包含数据库 使用Rbac函数一定会用到数据库
include __CLASSES__ . 'DbMysqli.class.php';

//权限管理类
class Rbac {

	//创建mysql连接
	private $mysqli;
	//登录玩家信息
	private $userInfo;

	//	登录验证
	public function __construct() {

		$this->mysqli = new DbMysqli();
	}

	/*
	 * 	session开启和设置闲置时间
	 * 	#参数说明#########################
	 */

	static function sessionInit() {
		ini_set('session.gc_maxlifetime', SESSION_TIME_OUT);
		session_start();
		if (!isset($_SESSION['__' . SERVER_TYPE . '_TIME_OUT']) || $_SESSION['__' . SERVER_TYPE . '_TIME_OUT'] > time()) {
			$_SESSION['__' . SERVER_TYPE . '_TIME_OUT'] = time() + SESSION_TIME_OUT;
		}
		if (!isset($_SESSION['__' . SERVER_TYPE . '_LANG'])) {
			isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $languages) : '';
			//默认为中文
			$lang='zh-cn';
			if(defined('DEFAULT_LANG')){
				$lang=strtolower(DEFAULT_LANG);
			}elseif(isset($languages[1])){
				$lang=strtolower($languages[1]);
			}
			switch ($lang) {
				//简体中文
				case 'zh':
				case 'zh-cn':
				default:
					$lang = 'zh-cn';
					break;
					//繁体中文
				case 'zh-tw':
				case 'zh-hk':
					$lang = 'zh-tw';
					break;
					//英文
				case 'en':
				case 'en-us':
					$lang = 'en-us';
					break;
					//越南文
				case 'vi':
				case 'vi-vn':
					$lang = 'vi-vn';
					break;
			}
			$_SESSION['__' . SERVER_TYPE . '_LANG'] = $lang;
		}
		//gettext多语言本地化
		include __LIB__ . 'php-gettext/gettext.inc';
		_setlocale(LC_ALL, $_SESSION['__' . SERVER_TYPE . '_LANG']);
		_bindtextdomain($_SESSION['__' . SERVER_TYPE . '_LANG'], __LANG__);
		_bind_textdomain_codeset($_SESSION['__' . SERVER_TYPE . '_LANG'], 'UTF-8');
		_textdomain($_SESSION['__' . SERVER_TYPE . '_LANG']);
	}

	/*
	 * 	登录验证
	 * 	#参数说明#########################
	 * 	$loginInfo	array()
	 * 	$serverType	center/agent/single
	 * 	$agent		代理名
	 * 	$sid		服务器标识
	 * 	$account	账号
	 * 	$password	密码md5之后的
	 * 	$ip			登录的ip信息
	 * 	#return##########################
	 * 	mix
	 */

	public function login($loginInfo) {
		//查询账号信息	登录什么服查询什么角色组信息
		$serverType = strtolower(trim($loginInfo['serverType']));
		$agent = $loginInfo['agent'];
		$sid = $loginInfo['sid'];
		$ip = $loginInfo['ip'];
		$isSave = isset($loginInfo['isSave']) && $loginInfo['isSave'] > 0 ? 1 : 0;

		//是否为指定的三种标识
		if (!in_array($serverType, array('center', 'agent', 'single')))
		return false;

		//code 登录
		if (isset($loginInfo['code']) && !empty($loginInfo['code'])) {
			$code = $loginInfo['code'];
			$codeSql = "select `account`,`expire` from `rbac_login_key` where `code`='" . $code . "' and `expire`>=" . time();
			$codeInfo = $this->mysqli->findOne($codeSql);
			if (!isset($codeInfo['account']))
			return false;
			$account = $codeInfo['account'];
			$expire = $codeInfo['expire']; //有效期
			//原始账号密码登录
		}elseif (isset($loginInfo['account']) && isset($loginInfo['password'])) {
			$account = $loginInfo['account'];
			$password = $loginInfo['password'];
			$verify = $loginInfo['verify'];
		} else {
			return false;
		}

		//获取代理id
		if ($agent == 'feiyin')
		$agentId = 0;
		else {
			$agentIdSql = 'select `id` from `rbac_agent` where `name`="' . $agent . '"';
			$agentIdQuery = $this->mysqli->fineOne($agentIdSql);
			//不存在此代理
			if (empty($agentIdQuery) || $agentIdQuery === false)
			return false;
			else
			$agentId = $agentIdQuery['id'];
		}

		//验证账号
		$loginSql = "select `id`,`agent_ids`,`account`,`password`,`username`,`role_{$serverType}` as `role_id`,`power`,`error_count`,`lock_time` from `rbac_user` where `account`='{$account}' and `status`=1";

		$userInfo = $this->mysqli->findOne($loginSql);

		if (empty($userInfo)) {
			return false;
		} else {
			//是否存在本代理有权限
			$agents = explode(',', $userInfo['agent_ids']);
			//详细的单服权限
			$powers = json_decode($userInfo['power'], true);
			//未到达解冻期,提示还剩多少秒
			if (($lock_time = $userInfo['lock_time'] - time()) > 0) {
				return array(false, '<span style="color:red;font-weight:bolder">' . __('嘿嘿嘿，由于您的失误，账号已被锁定，') . ceil($lock_time / 60) . __('分钟后再来试一下吧') . '</span>');
				//密码错误 当code登录时不校验密码
			} elseif (isset($password) && $password <> md5($userInfo['password'] . $verify)) {
				//如果密码输入错误，则更新账号表的错误次数，如果为第六次输入错误则同时更新有效期
				if (($error_count = 5 - $userInfo['error_count']) > 0) {
					$sql = 'update `rbac_user` set `error_count`=`error_count`+1 where `id` =' . $userInfo['id'];
					$this->mysqli->query($sql);
					return array(false, '<span style="color:red;font-weight:bolder">' . __('你妹呀，密码错了，再给你') . $error_count . __('次机会') . '</span>');
				} else {
					//更新有效期时间
					$sql = 'update `rbac_user` set `lock_time`=' . (time() + 1800) . ' where `id`=' . $userInfo['id'];
					$this->mysqli->query($sql);
					return array(false, '<span style="color:red;font-weight:bolder">' . __('您的机会用完鸟，为保证账号安全，我们将锁定此账号30分钟') . '</span>');
				}
				//return false;
				//还未分配角色组
			} elseif ($userInfo['role_id'] == -1) {
				return false;
				//如果登录的服不属于账号所在的代理并且账号不是feiyin群组的
			} elseif (!in_array($agentId, $agents) && !in_array(0, $agents)) {
				return false;
				//如果特殊权限组为非空且没有本代理本服的权限则表示无权限
			} elseif (!empty($powers) && !in_array($sid, $powers[$agentId])) {
				return false;
			} else {
				//如果是feiyin权限组人员并且是单服测试服将拥有超级管理员权限
				if (in_array(0, $agents) && $serverType == 'single' && preg_match("/T(\d)+/", $sid))
				$userInfo['role_id'] = 0;
				//非超级管理员时检查角色组是否可用
				if ($userInfo['role_id'] != 0) {
					$roleSql = "select `id` from `rbac_{$serverType}_role` where `id`={$userInfo['role_id']} and `status`=1";
					$roleInfo = $this->mysqli->findOne($roleSql);
					if (empty($roleInfo))
					return false;
				}

				//记录本次登录信息，登陆成功则清除错误次数
				$updateLoginSql = "update `rbac_user` set `login_count`=`login_count`+1,`last_login_ip`='{$ip}',`last_login_time`=" . time() . ",`error_count`=0 where id=" . $userInfo['id'];
				$this->mysqli->query($updateLoginSql);

				//生成验证码并记录
				if (!isset($code) || empty($code)) {
					$code = uuid();
					//如果保存则为7天 如果不保存状态则为1小时
					$expire = $isSave == 1 ? time() + 7 * 86400 : time() + 3600;
					$updateCodeSql = "replace into `rbac_login_key` set `code`='" . $code . "',`expire`=" . $expire . ",`account`='" . $account . "'";
					$this->mysqli->query($updateCodeSql);
				}

				//发送异常登录警报邮件
				//					insert into `iptest` VALUES('127.0.0.1','chunhei2010',1) on DUPLICATE key UPDATE count= count+1
				//记录账号ip登录次数
				$updateLoginIp = "insert into `rbac_login_ip`(`ip`,`account`) values('" . $ip . "','" . $account . "') on DUPLICATE key UPDATE `count`= `count`+1";
				$this->mysqli->query($updateLoginIp);
				//统计ip登录次数
				$ipLoginCountSql = "select `count` from `rbac_login_ip` where `ip`='" . $ip . "' and `account`='" . $account . "'";

				$ipLoginCount = $this->mysqli->findOne($ipLoginCountSql);
				//如果在本ip上登录少于5次则邮件通知
				if ($ipLoginCount['count'] <= 5) {
					$title = '安全警报(Alert)';
					$time = date('Y-m-d H:i:s');
					$content = '您的账号于北京时间' . $time . '在不常用的ip地址[' . $ip . ']上登录了服务器：' . $agent . '_' . $sid . ',请确定是否为您的正常操作，如果不是请及时Sửa密码或者联系管理员,本警报通知5次后自动取消。<br/>';
					$content .= 'Your account has been logged the server \'' . $agent . '_' . $sid . '\' in ip[' . $ip . '] at beijing time ' . $time . ',make sure it was logged by yourself,you must change your password or contact our administrator by reply this email if there is something wrong with that,this alert will be canceled after 5 times.';

					mailer($title, $account, $content);
				}

				$userInfo['server_type'] = $serverType;
				// code保存并返回
				$userInfo['code'] = $code;
				$userInfo['expire'] = $expire;
				$userInfo['is_save'] = $isSave;
				$this->userInfo = $userInfo;
				//如果是中央后台登录直接返回true
				return true;
			}
		}
	}

	/*
	 * 	检查是否登录
	 * 	#参数说明#########################
	 *
	 * 	#return##########################
	 * 	bool true/false
	 */

	static function checkLogin() {
		//session超时判定
		if ($_SESSION['__' . SERVER_TYPE . '_TIME_OUT'] <= time()) {
			$_SESSION = array();
			session_destroy();
			return false;
		}
		//角色id功能相关
		//无菜单项不可用
		if (!isset($_SESSION['__' . SERVER_TYPE . '_ROLE_ID']) || $_SESSION['__' . SERVER_TYPE . '_ROLE_ID'] < 0 || !isset($_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'])){
			return false;
		}
		return true;
	}

	/*
	 * 	获取角色权限和菜单列表
	 * 	#说明#########################
	 * 	本地登录后生成的session信息来获取角色的权限和菜单列表
	 * 	#return##########################
	 * 	array|bool
	 */

	public function getAccessList() {
		$menus = array(); //菜单列表
		$roleId = $this->userInfo['role_id'];    //角色id
		$serverType = $this->userInfo['server_type'];   //服务器代码
		//超级管理员
		if ($roleId == 0) {
			$moduleSql = "select `id`,`node_name` as `name`,`node_title` as `title`,`show` from `rbac_{$serverType}_node` where `level`=1 and `status`=1 and `pid`=0 order by `sort` asc,id asc";
			$moduleResult = $this->mysqli->query($moduleSql);
			while ($module = $moduleResult->fetch_assoc()) {
				$actionSql = "select `id`,`node_name` as `name`,`node_title` as `title`,`show` from `rbac_{$serverType}_node` where `pid`={$module['id']} and `level`=2 and `status`=1 order by `sort` asc,id asc";
				$actionResult = $this->mysqli->query($actionSql);
				$actions = array();
				while ($action = $actionResult->fetch_assoc()) {
					$tmpActionName = $action['name'];
					$action['access'] = $this->userInfo['role_id'];
					unset($action['name'], $action['id']);
					$actions[$tmpActionName] = $action;
				}
				$module['childs'] = $actions;
				$tmpModuleName = $module['name'];
				unset($module['name'], $module['id']);
				$menus[$tmpModuleName] = $module;
			}
		} else {
			//模块权限
			$moduleSql = "select `id`,`node_name` as `name`,`node_title` as `title`,`show` from `rbac_{$serverType}_node` where `level`=1 and `status`=1 and `pid`=0 and `power` &pow(2," . $roleId . ")=pow(2," . $roleId . ") order by `sort` asc,id asc";
			$moduleResult = $this->mysqli->query($moduleSql);
			while ($module = $moduleResult->fetch_assoc()) {
				$actionSql = "select `id`,`node_name` as `name`,`node_title` as `title`,`show` from `rbac_{$serverType}_node` where `pid`={$module['id']} and `level`=2 and `status`=1 and `power` & pow(2," . $roleId . ")=pow(2," . $roleId . ") order by `sort` asc,id asc";
				$actionResult = $this->mysqli->query($actionSql);
				$actions = array();
				while ($action = $actionResult->fetch_assoc()) {
					//缓存权限
					$tmpActionName = $action['name'];
					$action['access'] = $this->userInfo['role_id'];
					unset($action['name'], $action['id']);
					$actions[$tmpActionName] = $action;
				}
				$module['childs'] = $actions;
				$module['childs'] = $actions;
				$tmpModuleName = $module['name'];
				unset($module['name'], $module['id']);
				$menus[$tmpModuleName] = $module;
			}
		}
		//转换
		$agent_list = require(__CONFIG__ . '/agent_list_config.php');
		$tmp_agents = array();
		$tmp_user_server_list = array();
		foreach (explode(',', $this->userInfo['agent_ids']) as $agent_id) {
			$tmp_agents[] = $agent_list[$agent_id];
		}

		if (empty($this->userInfo['power'])) {
			$tmp_user_server_list = array();
		} else {
			$user_server_list = json_decode($this->userInfo['power'], true);
			foreach ($user_server_list as $key => $val) {
				$tmp_user_server_list[$agent_list[$key]] = $val;
			}
		}

		//如果是中央后台登录则直接缓存信息到session
		if ($serverType == 'center') {
			//超级管理员分配rbac模块权限
			if ($this->userInfo['role_id'] == 0) {
				$rbac['title'] = '系统管理';
				$rbac['show'] = 1;
				$rbac['childs'] = array(
                    'user_list.php' => array(
                        'title' => '账号列表',
                        'show' => 1,
                        'access' => $this->userInfo['role_id']
				),
                    'role_list.php' => array(
                        'title' => '角色列表',
                        'show' => 1,
                        'access' => $this->userInfo['role_id']
				),
                    'node_list.php' => array(
                        'title' => '节点列表',
                        'show' => 1,
                        'access' => $this->userInfo['role_id']
				),
				);
				$menus['rbac'] = $rbac;
			}

			$_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'] = $menus;
			$_SESSION['__' . SERVER_TYPE . '_ROLE_ID'] = $this->userInfo['role_id'];
			$_SESSION['__' . SERVER_TYPE . '_USER_NAME'] = $this->userInfo['username'];
			$_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'] = $this->userInfo['account'];
			$_SESSION['__' . SERVER_TYPE . '_AGENTS'] = $tmp_agents;
			$_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'] = $tmp_user_server_list;
			//设置code有效期
			setcookie('__IS_SAVE', $this->userInfo['is_save'], $this->userInfo['expire']);
			setcookie('__CODE', $this->userInfo['code'], $this->userInfo['expire']);
		} else {
			$accessInfo = array();
			$accessInfo['__ROLE_MENU'] = $menus;
			$accessInfo['__ROLE_ID'] = $this->userInfo['role_id'];
			$accessInfo['__USER_NAME'] = $this->userInfo['username'];
			$accessInfo['__USER_ACCOUNT'] = $this->userInfo['account'];
			$accessInfo['__AGENTS'] = $tmp_agents;
			$accessInfo['__USER_SERVER_LIST'] = $tmp_user_server_list;
			$accessInfo['__CODE'] = $this->userInfo['code'];
			$accessInfo['__EXPIRE'] = $this->userInfo['expire'];
			return $accessInfo;
		}
	}

	/*
	 * 	模块操作权限认证
	 * 	#说明#########################
	 * 	验证本模块是否需要认证
	 * 	可以在本函数中添加操作日志
	 * 	#return##########################
	 * 	bool true/flase
	 */

	static function accessAuth($module, $action) {
		//超级管理员
		if ($_SESSION['__' . SERVER_TYPE . '_ROLE_ID'] == 0) {
			//超级管理员无需认证
			return true;
		} else {
			if (Rbac::checkAuth($module, $action)) {
				//如果需要认证，则进行进一步确认认证
				$accessCode = md5($_SESSION['__' . SERVER_TYPE . '_ROLE_ID'] . $module . $action);
				//如果当前操作已经认证过，无需再次认证
				if (isset($_SESSION[$accessCode]) && $_SESSION[$accessCode] === true) {
					return true;
				}
				//比较登录后保存的权限访问列表
				$accessList = $_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'];
				if (!isset($accessList[$module]['childs'][$action]) && $accessList[$module]['childs'][$action]['access'] === $_SESSION['__' . SERVER_TYPE . '_ROLE_ID']) {
					$_SESSION[$accessCode] = false;
					return false;
				} else {
					$_SESSION[$accessCode] = true;
					return true;
				}
			} else {
				return true;
			}
		}
	}

	/*
	 * 	检查模块是否需要认证
	 * 	#参数说明#########################
	 * 	本次操作的模块名和操作名
	 * 	#return##########################
	 * 	bool true/flase
	 */

	static function checkAuth($module, $action) {
		//整个模块不需要认证
		$_module = explode(',', NOT_AUTH_MODULE);
		//某个具体的模块下的某操作不需要认证 array('module1:action1','module2:action2'……);
		$_action = explode(',', NOT_AUTH_ACTION);
		//如果不需要验证
		if (in_array($module, $_module) || in_array($module . ':' . $action, $_action)) {
			return false;
		} else {
			return true;
		}
	}

	/*
	 * 	添加新用户
	 * 	#参数说明#########################
	 * 	$userInfo	array
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function addUser($userInfo) {
		//添加账号信息
		if (!is_array($userInfo))
		return false;
		$userInfo = array_map('my_escape_string', $userInfo);
		//检查账号是否可用
		$userCheckSql = "select `id` from `rbac_user` where `account`='" . $userInfo['account'] . "' limit 1";
		if ($this->mysqli->count($userCheckSql) > 0)
		return false;
		//生成随机密码
		$password = rand_string($len = 10, $type = '', $addChars = '~@#$%^&*-_=+');
		$userInfo['password'] = md5($password);

		//添加账号
		$addUserSql = "insert into `rbac_user`(`account`,`password`,`username`,`agent_ids`,`department`,`role_center`,`role_agent`,`role_single`,`create_time`,`remark`) values ('{$userInfo['account']}','{$userInfo['password']}','{$userInfo['username']}','{$userInfo['agent']}','{$userInfo['department']}',{$userInfo['role_center']},{$userInfo['role_agent']},{$userInfo['role_single']}," . time() . ",'{$userInfo['remark']}')";
		if ($this->mysqli->query($addUserSql)) {
			//发送创号邮件
			$title = '创号成功(Create Account Successful)';
			$to = $userInfo['account'];
			$content = '您的账号创建成功，分配的随机密码为: ' . $password . ' 请及时Sửa您的密码！<br/>';
			$content .='Congratulations! your account has been created successfully,your password is ' . $password . ',Please change your password in time.';
			return mailer($title, $to, $content);
		}
		return false;
	}

	/*
	 * 	永久Xóa用户
	 * 	#参数说明#########################
	 * 	$userId	int
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function delUser($userId) {
		//Xóa账号信息
		$userId = intval($userId);
		$delUserSql = "delete from `rbac_user` where `id`=" . $userId;
		return $this->mysqli->query($delUserSql);
	}

	/*
	 * 	禁用启用用户
	 * 	#参数说明#########################
	 * 	$userId	int	$action
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function enableUser($userId, $action = 'enable') {
		$userId = intval($userId);
		$status = $action == 'enable' ? 1 : 0;
		$enableUserSql = "update `rbac_user` set `status` =	$status where `id`= $userId";
		return $this->mysqli->query($enableUserSql);
	}

	/*
	 * 	密码重置
	 * 	#参数说明#########################
	 * 	$userId
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function resetPwd($userId) {
		$userId = intval($userId);
		$userInfo = $this->mysqli->findOne('select `account`,`username` from `rbac_user` where `id`=' . $userId);
		if (empty($userInfo))
		return false;
		//生成随机密码
		$newPwd = rand_string($len = 10, $type = '', $addChars = '~@#$%^&*-_=+');

		$title = '重置密码(Reset Password)';
		$to = $userInfo['account'];
		$content = '您的密码被重置为: ' . $newPwd . ' 请及时Sửa您的密码！<br/>';
		$content.= 'Your password has been reset to: ' . $newPwd . ' ,please change your password in time.';
		//发送密码Sửa邮件
		if (mailer($title, $to, $content)) {
			return $this->mysqli->query('update `rbac_user` set `password`="' . md5($newPwd) . '" where `id`=' . $userId);
		}
		return false;
	}

	/*
	 * 	Sửa密码
	 * 	#参数说明#########################
	 * 	$userInfo
	 * 	$account账号
	 * 	$oldPwd	旧密码	都为md5之后的密码
	 * 	$newPwd	新密码	都为md5之后的密码
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function changePwd($userInfo) {
		if (!is_array($userInfo))
		return false;
		$userInfo = array_map('trim', $userInfo);
		$userInfo = array_map('my_escape_string', $userInfo);
		if (empty($userInfo['account']) || $userInfo['newPwd'] == $userInfo['oldPwd'])
		return false;
		//校验原始密码
		$oldUserSql = "select `account`,`password` from `rbac_user` where `account` ='" . $userInfo['account'] . "'";
		$oldUser = $this->mysqli->findOne($oldUserSql);

		if ($oldUser['password'] <> $userInfo['oldPwd'])
		return false;
		//更新密码
		$updatePwdSql = "update `rbac_user` set `password`='" . $userInfo['newPwd'] . "' where `account` = '" . $userInfo['account'] . "'";

		if ($this->mysqli->query($updatePwdSql)) {
			//发送密码Sửa邮件
			$title = '改密邮件(Change Password)';
			$to = $userInfo['account'];
			$time = date('Y-m-d H:i:s');
			$content = '您的管理账号在北京时间' . $time . 'Sửa了密码，如有疑问请联系我方管理员。<br/>';
			$content .= 'Your password has been changed at beijing time ' . $time . ',Please contact our administrator or reply this email if your have any questions.';
			mailer($title, $to, $content);
			return true;
		} else {
			return false;
		}
	}

	/*
	 * 	添加新角色组
	 * 	#参数说明#########################
	 * 	$roleInfo	$serverType
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function addRole($roleInfo, $serverType) {
		if (!in_array($serverType, array('center', 'agent', 'single')))
		return false;
		if (!is_array($roleInfo))
		return false;
		$roleInfo = array_map('my_escape_string', $roleInfo);
		//角色名可用否  不能分配超级管理员和 为分配的角色组 两个系统默认组
		if (in_array($roleInfo['role_name'], array('超级管理员', '未分配的角色组')))
		return false;
		$roleNameExistSql = "select count(`id`) from `rbac_" . $serverType . "_role` where `role_name`='" . $roleInfo['role_name'] . "'";
		if ($this->mysqli->count($roleNameExistSql) >= 1)
		return false;
		//获取最小的可用自增id
		$getMinIdSql = "SELECT (CASE WHEN EXISTS(SELECT * FROM rbac_{$serverType}_role b WHERE b.id = 1) THEN MIN(id) + 1 ELSE 1 END) as id FROM rbac_{$serverType}_role WHERE NOT id IN (SELECT a.id - 1 FROM rbac_{$serverType}_role a)";
		$minId = $this->mysqli->findOne($getMinIdSql);
		//限制最大可用角色组id为63
		$roleId = $minId['id'];
		if ($roleId > 63)
		return false;
		//添加角色权限
		$addPowerSql = "update `rbac_{$serverType}_node` set `power`=`power`|pow(2," . $roleId . ") where `id` in(" . $roleInfo['node'] . ") and `power`&pow(2," . $roleId . ")=0";
		if (!$this->mysqli->query($addPowerSql))
		return false;
		$delPowerSql = "update `rbac_{$serverType}_node` set `power`=`power`^pow(2," . $roleId . ") where `id` not in(" . $roleInfo['node'] . ") and `power`&pow(2," . $roleId . ")=pow(2," . $roleId . ")";
		if (!$this->mysqli->query($delPowerSql))
		return false;
		//添加角色
		$addRoleSql = "insert into `rbac_{$serverType}_role`(`id`,`role_name`,`create_time`,`remark`) values ($roleId,'{$roleInfo['role_name']}'," . time() . ",'{$roleInfo['remark']}')";
		if (!$this->mysqli->query($addRoleSql))
		return false;
		$this->updateRoleList();
		return true;
	}

	/*
	 * 	Xóa角色组
	 * 	#参数说明#########################
	 * 	$roleId		$serverType
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function delRole($roleId, $serverType) {
		$roleId = intval($roleId);
		if ($roleId < 1)
		return false;
		if (!in_array($serverType, array('center', 'agent', 'single')))
		return false;
		//解绑账号角色关系
		$delUserRoleSql = "update `rbac_user` set `role_{$serverType}` = -1 where `role_{$serverType}` = " . $roleId;
		if (!$this->mysqli->query($delUserRoleSql))
		return false;
		//解绑节点与角色关系
		$delNodeRoleSql = "update `rbac_{$serverType}_node` set `power`=`power`^pow(2," . $roleId . ") where `power`&pow(2," . $roleId . ")=pow(2," . $roleId . ")";
		if (!$this->mysqli->query($delNodeRoleSql))
		return false;
		//Xóa角色组
		$delRoleSql = "delete from `rbac_{$serverType}_role` where `id` =	" . $roleId;
		if (!$this->mysqli->query($delRoleSql))
		return false;
		$this->updateRoleList();
		return true;
	}

	/*
	 * 	禁用启用角色组
	 * 	#参数说明#########################
	 * 	$roleId	$serverType	$action
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function enableRole($roleId, $serverType, $action = 'enable') {
		$roleId = intval($roleId);
		if ($roleId < 1)
		return false;
		if (!in_array($serverType, array('center', 'agent', 'single')))
		return false;
		$status = $action == 'enable' ? 1 : 0;
		$enableUserSql = "update `rbac_{$serverType}_role` set `status` = $status where `id`= $roleId";
		return $this->mysqli->query($enableUserSql);
	}

	/*
	 * 	禁用启用节点
	 * 	#参数说明#########################
	 * 	$nodeId	$serverType	$action
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function enableNode($nodeId, $serverType, $action = 'enable') {
		$nodeId = intval($nodeId);
		if (!in_array($serverType, array('center', 'agent', 'single')))
		return false;
		$status = $action == 'enable' ? 1 : 0;
		$enableNodeSql = "update `rbac_{$serverType}_node` set `status` = $status where `id`= $nodeId";
		return $this->mysqli->query($enableNodeSql);
	}

	/*
	 * 	隐藏显示节点
	 * 	#参数说明#########################
	 * 	$nodeId	$serverType	$action
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function showNode($nodeId, $serverType, $action = 'show') {
		$nodeId = intval($nodeId);
		if (!in_array($serverType, array('center', 'agent', 'single')))
		return false;
		$show = $action == 'show' ? 1 : 0;
		$showNodeSql = "update `rbac_{$serverType}_node` set `show` = $show where `id`= $nodeId";
		return $this->mysqli->query($showNodeSql);
	}

	/*
	 * 	Xóa节点
	 * 	#参数说明#########################
	 * 	$nodeId	$serverType
	 * 	主节点规律	1,3,5,7,9奇数的10倍数
	 * 	子节点规律	20个空余位置
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function delNode($nodeId, $serverType) {
		if (!in_array($serverType, array('center', 'agent', 'single')))
		return false;
		$nodeId = intval($nodeId);
		//是否为主节点(10,30,50…………)先解绑子节点关系
		if ($nodeId / 10 % 2 === 1) {
			$delSubnodeSql = "update `rbac_{$serverType}_node` set `pid` = -1 where `pid`= $nodeId";
			if (!$this->mysqli->query($delSubnodeSql))
			return false;
		}
		//Xóa本节点
		$delNodeSql = "delete from `rbac_{$serverType}_node` where `id`= $nodeId";
		return $this->mysqli->query($delNodeSql);
	}

	/*
	 * 	添加节点
	 * 	#参数说明#########################
	 * 	$nodeInfo	$serverType
	 * 	主节点规律	1,3,5,7,9奇数的10倍数
	 * 	子节点规律	20个空余位置
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function addNode($nodeInfo, $serverType) {
		//判断参数是否合法，判断节点是否为rbac,禁止添加rbac节点
		if (!in_array($serverType, array('center', 'agent', 'single')) || $nodeInfo['node_name'] == 'rbac')
		return false;
		//判断是否有该节点
		$nodeExistsSql = "select count(`id`) from `rbac_{$serverType}_node` where `node_name`='" . $nodeInfo['node_name'] . "'";
		if ($this->mysqli->count($nodeExistsSql) > 0)
		return false;
		//过滤 组装sql
		$keys = array();
		$values = array();
		foreach ($nodeInfo as $key => $value) {
			if (in_array($key, array('pid', 'sort', 'show'))) {
				$value = intval($value);
				if ($value < 0)
				return false;
				$values[] = $value;
			}else {
				$value = my_escape_string(trim($value));
				if (empty($value))
				return false;
				$values[] = "'" . $value . "'";
			}
			$keys[] = '`' . $key . '`';
		}
		//是否为主节点，主节点的父节点为0
		if ($nodeInfo['pid'] == 0) {
			//获取当前主节点列表
			$nodeIdListSql = "select round(`id`/10) as `id` from `rbac_{$serverType}_node` where `pid`= 0 order by `id` asc";
			$nodeIdList = $this->mysqli->find($nodeIdListSql);
			$nodeId = 1;
			if (!empty($nodeIdList)) {
				$nodeExists = array();
				foreach ($nodeIdList as $node) {
					$nodeExists[] = $node['id'];
				}
				foreach ($nodeIdList as $node) {
					if (in_array($nodeId, $nodeExists)) {
						$nodeId += 2;
					} else {
						break;
					}
				}
			}
			$nodeId = $nodeId * 10;
			$keys[] = '`level`';
			$values[] = 1;
		} else {
			//获取当前子节点列表
			$nodeIdListSql = "select `id` from `rbac_{$serverType}_node` where `pid`= {$nodeInfo['pid']} order by `id` asc";
			$nodeIdList = $this->mysqli->find($nodeIdListSql);
			$nodeId = $nodeInfo['pid'] + 1;
			$nodeIdMax = $nodeInfo['pid'] + 20;
			if (!empty($nodeIdList)) {
				$nodeExists = array();
				foreach ($nodeIdList as $node) {
					$nodeExists[] = $node['id'];
				}
				foreach ($nodeIdList as $node) {
					if (in_array($nodeId, $nodeExists)) {
						$nodeId += 1;
					} else {
						break;
					}
				}
			}
			$keys[] = '`level`';
			$values[] = 2;
		}
		$keys[] = '`id`';
		$values[] = $nodeId;
		//添加
		$addNodeSql = "insert into `rbac_{$serverType}_node` (" . implode(',', $keys) . ") values(" . implode(',', $values) . ")";
		return $this->mysqli->query($addNodeSql);
	}

	/*
	 * 	更新角色列表缓存
	 * 	#参数说明#########################
	 * 	#return##########################
	 * 	bool true/flase
	 */

	public function updateRoleList() {
		//更新角色列表缓存
		$center_role_list = $agent_role_list = $single_role_list = array(
		-1 => '<span style="color:red">未分配角色</span>',
		0 => '超级管理员',
		);
		//中央服
		$center_query = $this->mysqli->query('select `id`,`role_name` from `rbac_center_role`');
		if ($center_query) {
			while ($assoc = $center_query->fetch_assoc()) {
				$center_role_list[$assoc['id']] = $assoc['role_name'];
			}
		}
		//代理中央服
		$agent_query = $this->mysqli->query('select `id`,`role_name` from `rbac_agent_role`');

		if ($agent_query) {
			while ($assoc = $agent_query->fetch_assoc()) {
				$agent_role_list[$assoc['id']] = $assoc['role_name'];
			}
		}
		//单服
		$single_query = $this->mysqli->query('select `id`,`role_name` from `rbac_single_role`');
		if ($single_query) {
			while ($assoc = $single_query->fetch_assoc()) {
				$single_role_list[$assoc['id']] = $assoc['role_name'];
			}
		}
		return file_put_contents(__CONFIG__ . 'role_list_config.php', "<?php \r\n \$center_role_list=" . var_export($center_role_list, true) . ";\r\n \$agent_role_list=" . var_export($agent_role_list, true) . ";\r\n \$single_role_list=" . var_export($single_role_list, true) . "; \r\n ?>");
	}

	/*
	 * 	关闭mysql连接
	 * 	#参数说明#########################
	 *
	 * 	#return##########################
	 *
	 */

	public function close() {
		$this->mysqli->close();
		unset($this->userInfo);
	}

}

Rbac::sessionInit();
?>