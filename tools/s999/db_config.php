<?php
!defined('__ROOT__') && exit('forbid');

####################数据库配置#################################
define('MYSQL_HOST',    '10.0.0.28');
define('MYSQL_PORT',    '3306');
define('MYSQL_USER',    'tamquocche_mysql');
define('MYSQL_PWD',     'tamquocche_mysql@1234156');
define('MYSQL_DB',      SERVER_AGENT.'_'.SERVER_ID.'_lwjs');

define('MONGO_HOST',    '10.0.0.28');
define('MONGO_PORT',    '27017');
define('MONGO_USER',    'tamquocche_mongo');
define('MONGO_PWD',     '407723083e3d');
define('MONGO_GAME',    'admin');
define('MONGO_PERFIX',  SERVER_AGENT.'_'.SERVER_ID.'_lwjs_');
?>