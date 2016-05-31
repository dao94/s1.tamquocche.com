<?php
//接口基础类
class ApiBase {

	//统一错误码列表
	static protected $retMsg = array(
		0 => 'success',
		1 => 'failed',
		8000 => 'params too few',
		8001 => 'time out',
		8002 => 'sign error',
		8003 => 'none characters',
		8004 => 'money or gold error',
		8005 => 'useless order',
		8006 => 'ip forbid',
		8007 => 'params out of range',
		8008 => 'data exist',
		8009 => 'data not exist',
		8010 => 'balance is not enough',
	);

	/*
	 * 统一校验码规则
	 * $params 校验参数
	 * $key 秘钥
	 * $urlencode 是否开启字段urlencode 默认开启，false 时不开启（专为服务器端登陆校验）
	 */
	public static function makeSign($params, $key, $urlencode = true) {
		unset($params['sign']);
		ksort($params);
		if ($urlencode === true) {
			return md5(http_build_query($params) . $key);
		} else {
			$md5 = array();
			foreach ($params as $k => $v) {
				$md5[] = $k . '=' . $v;
			}
			return md5(implode('&', $md5) . $key);
		}
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
		exit(json_encode($return));
	}
}

?>
