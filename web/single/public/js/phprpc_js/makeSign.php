<?php
define('__ROOT__',str_replace(array('//', '\\', 'public/js/phprpc_js'), array('/', '/', ''), __DIR__));
include __ROOT__. '/config/config.php';
include __CLASSES__ . 'Rbac.class.php';
//没有登陆不能使用phprpc_js
!Rbac::checkLogin() && exit('forbid');
isset($_POST['phprpc_id']) && isset($_POST['phprpc_time']) && isset($_POST['phprpc_func']) && exit(md5($_POST['phprpc_id'] . PHPRPC_KEY . $_POST['phprpc_time'] . $_POST['phprpc_func']));
?>
