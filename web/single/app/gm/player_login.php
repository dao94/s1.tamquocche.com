<?php

//玩家登录
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Character.class.php';
include __CONFIG__.'key_config.php';

$action=empty($_GET['action']) ? 'view' : trim($_GET['action']);
switch ($action){
	case 'login':
		//玩家登录
		foreach ($_POST as $key=>$value){
			$$key=$value;
		}
		if(empty($name) && empty($account))
			ajax_return(0, __('请输入角色名或账号!'));
		if(isset($name) && empty($name))
			ajax_return(0, __('请输入角色名!'));
		if(isset($account) && empty($account))
			ajax_return(0, __('请输入账号!'));
		isset($name) ? $condition['name']=$name : '';
		isset($account) ? $condition['account']=$account : '';
		if(isset($condition['account'])){
			$condition['serverId']=empty($sid) ? intval(substr(SERVER_ID,1)) : intval($sid);
		}
		$character=new Character();
		$char=$character->getCharacterByCondition($condition);
		if(!$char && empty($new)){
			ajax_return(0, __('玩家不存在！'));
		}elseif(!$char && $new==1){
			$account='lwjs_'.md5(md5($account));//测试账号
		}
		//一键登录，判断玩家是否在线
		if(isset($one_key) && $one_key==1){
			$mysqli=new DbMysqli();
			$login_time=time()-86400*2;//过滤异常在线
			$sql="select logout_time from log_login where char_id={$char['_id']} and login_time>=$login_time order by id desc limit 1";
			$list=$mysqli->findOne($sql);
			if($list && empty($list['logout_time'])){
				ajax_return(0, __('此账号在线，请和该玩家提前沟通'));
			}
		}

		$data['account']=$char ? $char['account'] : $account;
		$data['sid']=$char ? $char['serverId'] : intval(substr(SERVER_ID,1));
		$data['time']=time();
		ksort($data);
		$data['sign']=md5(http_build_query($data).LOGIN_KEY);
		$data['cm']=isset($_POST['cm']) ? intval($_POST['cm']) : 1;
		$data['debug']=1;
		$url='../../../api/login.php?'.http_build_query($data);
		ajax_return(1, $data['account'],$url);
		break;
}
$smarty->display();