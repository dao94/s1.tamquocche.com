<?php
!defined('__ROOT__') && exit('forbid');

####################数据库配置#################################
define('MYSQL_HOST',    '42.112.20.26');
define('MYSQL_PORT',    '3306');
define('MYSQL_USER',    'root');
define('MYSQL_PWD',     'tamquocche28@#$');
define('MYSQL_DB',      SERVER_AGENT.'_'.SERVER_ID.'_lwjs');

define('MONGO_HOST',    '42.112.20.26');
define('MONGO_PORT',    '27017');
define('MONGO_USER',    'tamquocche_mongo');
define('MONGO_PWD',     '407723083e3d');
define('MONGO_GAME',    'admin');
define('MONGO_PERFIX',  SERVER_AGENT.'_'.SERVER_ID.'_lwjs_');
?>
