<?php
//首页检测是否登录没有登录则跳转到登录页面
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), __DIR__));
include __ROOT__.'/config/config.php';
include __AUTH__ . '/auth.php';

//跳转保存当前页
$current=empty($_SESSION['__'.SERVER_TYPE.'_CURRENT']) ? 'base/online.php' : $_SESSION['__'.SERVER_TYPE.'_CURRENT'];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="Bookmark" href="../favicon.ico">
<link rel="Shortcut Icon" href="../favicon.ico" />
<title><?php echo __('GM Tool - Tam Quốc Web -').' -- '.SERVER_AGENT.'_'.SERVER_ID; ?></title>
</head>
<frameset frameborder="0" framespacing="0" border="0" rows="62,*" id="frame-top">
	<frame src="app/public/top.php" name="top" frameborder="0" scrolling='no' noresize="noresize" marginwidth="0" marginheight="0">
	<frameset frameborder="0" framespacing="0" border="0" cols="155,7,*" id="frame-body">
		<frame src="app/public/menu.php" id="menu-frame" frameborder="0" noresize="noresize" name="menu">
		<frame src="app/public/drag.html" id="drag-frame" name="drag-frame" noresize="noresize" frameborder="no" scrolling="no">
		<frame src="app/<?php echo $current;?>" id="main-frame" frameborder="0" noresize="noresize" name="main">
	</frameset>
</frameset>
<noframes></noframes>
</html>
