<?php
//任务计划管理
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action_conf=array(
	'1d'=>__('每天凌晨'),
	'0h'=>__('每天0点整'),
	'5m'=>__('每5分钟'),
	'30m'=>__('每30分钟'),
	'2h'=>__('每2小时'),
);
$action=empty($_GET['action']) ? '1d' : trim($_GET['action']);
$conditions=array(
	'action'=>$action,
);
//文件命名规则：task_$action 并且放在app/task目录下
$path=__APP__.'task/';
switch ($action){
	default:
		$data='';
		$filename=$path."task_{$action}";
		if(file_exists($filename)){
			$data=file_get_contents($filename);
		}
		$smarty->assign('data',$data);
		$smarty->assign('conditions',$conditions);
		$smarty->assign('action_conf',$action_conf);
		$smarty->display();
		break;

	case 'do':
		$name=empty($_POST['name']) ? '' :trim($_POST['name']);
		$data=empty($_POST['data']) ? '' :trim($_POST['data']);
		$filename=$path."task_{$name}";
		if(!array_key_exists($name, $action_conf) || !$data)
			ajax_return(0, __('参数有误！'));
		$list=explode("\n", $data);
		$data=array();
		foreach ($list as $row){
			if(trim($row)){
				$item=explode(' ', $row);
				$file=empty($item[0]) ? '' : $item[0];
				if(!file_exists($path.$file))
					ajax_return(0, $file.__('文件不存在'));
				if(in_array($row, $data))
					ajax_return(0, $row.__('命令重复存在'));
				$data[]=$file.' '.$item[1];
			}
		}
		$data=implode("\n", $data)."\n";
		if(file_put_contents($filename, $data)===false)
			ajax_return(0, __('命令保存失败'));
		else
			ajax_return(1, __('命令保存成功'));
		break;
}