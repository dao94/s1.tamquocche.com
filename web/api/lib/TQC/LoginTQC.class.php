<?php
class LoginTQC extends Login {
    /*
     * 参数转换
     */

    function __construct() {
        include __CONFIG__ . 'key_config.php';
        include __CONFIG__ . 'url_config.php';
        $this->urlconfig = $url_config;
        //处理url参数	  各个代理的参数可能不同 特别是特殊代理 转换成内部统一参数
        $makeSign = array();
        $makeSign['username'] = $this->params['account'] = isset($_REQUEST['username']) ? urldecode(trim($_REQUEST['username'])) : '';
        $makeSign['time'] = $this->params['time'] = isset($_REQUEST['time']) ? intval($_REQUEST['time']) : 0;
        $makeSign['server'] = isset($_REQUEST['server']) ? $_REQUEST['server'] : '';
        $makeSign['cm'] = $this->params['cm'] = isset($_REQUEST['cm']) ? intval($_REQUEST['cm']) : 2;
        $this->params['sign'] = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
        $this->params['sid'] = intval(substr($makeSign['server'],1));
        $this->params['flag'] = self::makeSign($makeSign, LOGIN_KEY);
        $this->params['from_flag']=isset($_REQUEST['from_launcher'])&&$_REQUEST['from_launcher']==1 ? 2 : 1;//登录标识 1=web 2=登陆器
        $this->fcmSwitch();
    }

    /*
     * 校验规则
     */
    static function makeSign($params, $key, $urlencode = true) {
        return md5($params['username'] . $params['time'] . $key . $params['cm'] . $params['server']);
    }

}

?>
