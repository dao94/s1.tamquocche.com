<?php
session_start();
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'),__DIR__).'/single');
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'url_config.php';
include __CONFIG__ . 'source_config.php';

//游戏入口开关game_flag,后台登陆标识debug，可进游戏
$data=empty($_SESSION['__'.SERVER_TYPE.'_GAME_DATA']) ? '' : $_SESSION['__'.SERVER_TYPE.'_GAME_DATA'];
(!$data || (!is_file(__SWITCH__.'game_flag')&&$data['debug']!==1)) && header('Location:' . $url_config['xuan']) && exit;

header('P3P: CP=CAO PSA OUR');
include __API__ . 'lib/ApiBase.class.php';
$account=$data['account'];
$sid=$data['sid'];
$cm=$data['cm'];
$cm_first = $data['cm_first'];
$time=time();
$params=array('account' => $account, 'sid' => $sid, 'time' => $time);
$sign=ApiBase::makeSign($params, SERVER_KEY, false);
$from_flag = empty($data['from_flag']) ? 1 : intval($data['from_flag']);//登录来源 1=web 2=登陆器
$chat_flag=is_file(__SWITCH__.'chat_flag') ? 1 : 0;//1=开启聊天监控 0=关闭聊天监控
$auto_flag=is_file(__SWITCH__.'auto_flag') ? 1 : 0;//1=自动创号 0=手动创号
if (defined('GAME_HOST')&&GAME_HOST!='') {
	$host = GAME_HOST;
} else {
	if (strpos($_SERVER['HTTP_HOST'], ':') === false) {
		$host = $_SERVER['HTTP_HOST'];
	} else {
		$host_info = parse_url($_SERVER['HTTP_HOST']);
		$host = $host_info['host'];
	}
}
$port = defined('GAME_PORT') ? GAME_PORT : 8000;
$dir = empty($cdn_config) ? './game_res' : $cdn_config[array_rand($cdn_config)].'/game_res';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
<link rel="Bookmark" href="./favicon.ico">
<link rel="Shortcut Icon" href="./favicon.ico" />
<title><?php echo SERVER_TITLE; ?></title>
<style type="text/css">
html,body{height:100%;}
body {margin:0;padding:0;overflow:hidden;background:#000000;font-size:12px;}
#flashconent{width:1030px; height:605px; text-align:center;position:absolute; top:50%; left:50%; margin-left:-516px; margin-top:-303px;}
</style>
<script type="text/javascript">
var lwjs_c={},task_list='';
lwjs_c.agent = '<?php echo SERVER_AGENT; ?>';
lwjs_c.server_id = '<?php echo substr(SERVER_ID,0,1).$sid; ?>';
lwjs_c.usid = <?php echo $sid; ?>;//当前玩家的服id
lwjs_c.account = '<?php echo $account; ?>';
lwjs_c.cm = <?php echo $cm; ?>;
lwjs_c.url = {};
<?php
!isset($url_config) && $url_config = array();
$data['from']=isset($data['from']) ? $data['from'] : SERVER_AGENT;
foreach ($url_config as $key => $val) {
	//混服链接处理
	$url='';
	if (strstr($val,'##')){
		$arr=explode('##', $val);
		foreach ($arr as $v){
			$tmp=explode('@', $v);
			$url=$tmp[1];
			if ($data['from']==$tmp[0]){
				break;
			}
		}
	}else{
		$url=$val;
	}
	$url=str_replace(array('{{sid}}','{{account}}'),array($sid,$account),$url);
	echo "lwjs_c.url.{$key}='{$url}';\n";
}
?>
</script>
<script type="text/javascript" src="./api/js/swfobject.js"></script>
<script type="text/javascript" src="./api/js/lwjsapp-min.js?v=20140808" charset="utf-8"></script>
</head>
<body scroll="no">
<?php include 'game.php';?>
<div id="flashconent">
	<div id="playerdl" style="margin-top:300px;color:#ffffff">Đang kết nối, vui lòng chờ...</div>
</div>
</body>
</html>
