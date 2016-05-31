<?php
//C++相关工具
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action=empty($_GET['action']) ? '' : trim($_GET['action']);
switch ($action){
	case 'log_line':
		$status_conf=array(0=>__('隐藏'),1=>__('开启'));
		$mysqli=new DbMysqli();
		$time=time()-86400;
		$sql="select max(time) as time,type,line from log_line where time>=$time group by type,line";
		$list=$mysqli->find($sql);
		$data=array();
		foreach ($list as $row){
			$sql="select * from log_line where time={$row['time']} and type={$row['type']} and line={$row['line']} limit 1";
			$item=$mysqli->findOne($sql);
			$item['time']=date('Y-m-d H:i:s',$item['time']);
			$data[]=$item;
		}
		ajax_return(1, 'data list',$data);
		break;
	case 'set_line':
		$opt=isset($_POST['opt']) && $_POST['opt']!=='' ? intval($_POST['opt']) : '';
		$type=empty($_POST['type']) ? 0 : intval($_POST['type']);
		$line=empty($_POST['line']) ? 0 : intval($_POST['line']);
		if($opt==='' || !$type || !$line){
			ajax_return(0, __('参数错误'));
		}
		include __CLASSES__.'Gm.class.php';
		$gm = new Gm();
		$rpc = 'blrpc/bllogin.rpc';
		$rpc_obj = 'blrpc\\Sour_B2lLoginCtrl';
		$async = 'b2lControlLine_async';
		$msg=array(
			'opt'=>$opt,
			'type'=>$type,
			'index'=>$line,
		);
		$gm->async($rpc, $rpc_obj, $async, $msg,GM_HOST,BL_PORT);
		ajax_return(1, __('设置成功'));
		break;
}
include __CONFIG__ . 'title_config.php';
$smarty->assign('title_conf', $title_conf);
$smarty->assign('achieve_conf', $achieve_conf);
$smarty->display();
?>
