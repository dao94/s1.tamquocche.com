<?php
if(!defined('__ROOT__'))	exit('Error');
//基础配置文件：路径配置、数据库配置（其他基准不变动配置）
####################服务器配置###############################
//服务器类型（中央服：center  代理中央服：agent  单服：single）
define('SERVER_TYPE', 'single');
####################session配置信息#########################
define('SESSION_TIME_OUT', 3600);

####################目录配置#################################
//外部类库
define('__LIB__', __ROOT__ . '/lib/');
//配置文件
define('__CONFIG__', __ROOT__ . '/config/');
//自写类库
define('__CLASSES__', __ROOT__ . '/class/');
//运行时文件（smarty编译文件、数据缓存文件）
define('__RUNTIME__', __ROOT__ . '/runtime/');
//日志
define('__LOGS__', __ROOT__ . '/logs/');
//功能函数
define('__FUNCTIONS__', __ROOT__ . '/functions/');
//xml配置文件
define('__XML__', __ROOT__ . '/xml/');
//开关文件
define('__SWITCH__', __ROOT__ . '/switch/');
//外部api接口
define('__API__', dirname(__ROOT__) . '/api/');
//公共文件（js、css、images）
define('__PUBLIC__', __ROOT__ . '/public/');
//语言包
define('__LANG__', __ROOT__ . '/lang/');
//权限验证脚本，所有的文件包含
define('__AUTH__', __ROOT__ . '/auth/');
//应用路劲
define('__APP__', __ROOT__ . '/app/');
//登录页面
define('__LOGIN__', __APP__ . 'public/login.php');
//提示信息页
define('__TIPS__', __APP__ . 'public/tips.php');
//首页
define('__INDEX__', __ROOT__ . '/index.php');
//整个模块不需要认证
define('NOT_AUTH_MODULE', 'interface,task');
//某个具体的模块下的某操作不需要认证 array('module1:action1','module2:action2'……);
define('NOT_AUTH_ACTION', 'public:tips.php,public:test.php');
//是否开启日志功能
define('LOG_SWITCH', true);
//整个模块不需要日志
define('NO_LOG_MODULE', 'interface,task');
//某个具体的模块下的某操作不需要日志 array('module1:action1','module2:action2'……);
define('NO_LOG_ACTION', 'public:top.php,public:tips.php,public:change_pwd.php,public:menu.php');
####################公共key配置##############################
//远程调用key
define('PHPRPC_KEY', '8WDIiwS=NS7dF%4@FgyOJqR*');
//登陆与服务器约定的key不能Sửa
define('SERVER_KEY', 'ASDDFDSAG');
###################中央服地址配置############################
define('CENTER_DOMAIN', '14.17.107.250:8888');
####################服务器配置###############################
file_exists(__CONFIG__ . 'server_config.php') && include(__CONFIG__ . 'server_config.php');
####################数据库配置#################################
file_exists(__CONFIG__ . 'db_config.php') && include(__CONFIG__ . 'db_config.php');
?>
