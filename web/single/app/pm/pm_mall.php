<?php
//珍宝阁
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Gm.class.php';

$currency_conf=array(
	1=>__('铜币'),
	2=>__('铜券'),
	3=>__('元宝'),
	4=>__('礼券'),
);
$limit_type_conf=array(
	1=>__('总限制'),
	2=>__('每日限制'),
);

$action=empty($_GET['action']) ? 'list' : trim($_GET['action']);
$data=array();
$activity_type=4;//activity表type=4是珍宝阁

$rpc='brrpc/bractivity.rpc';
$rpc_obj='brrpc\\Sour_B2rActivity';
$async='b2rUpdateActivity_async';

switch ($action){
	case 'list':
		$id=empty($_GET['id']) ? '' : my_escape_string($_GET['id']);
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$condition=array('type'=>$activity_type);
		$data=$mdb->find('activity', $condition, array(), array('sort'=>array('index'=>1)));
		$now=date('Y-m-d H:i:s',time());
		foreach ($data as &$row){
			$row['start']=date('Y-m-d H:i:s',$row['start']);
			$row['over']=date('Y-m-d H:i:s',$row['over']);
			$row['now']=$now;
		}
		//Sửa
		if($id){
			$info=$mdb->findOne('activity', array('type'=>$activity_type,'item_id'=>$id));
			if($info){
				$info['start']=date('Y-m-d H:i:s',$info['start']);
				$info['over']=date('Y-m-d H:i:s',$info['over']);
				$smarty->assign('info',$info);
			}
		}
		break;

	case 'save':
		//新增和Sửa
		$id=empty($_POST['id']) ? '' : my_escape_string($_POST['id']);
		$start=empty($_POST['start']) ? '' : my_escape_string(trim($_POST['start']));
		$over=empty($_POST['over']) ? '' : my_escape_string(trim($_POST['over']));
		$item=empty($_POST['item']) ? '' : my_escape_string($_POST['item']);
		$bind=empty($_POST['bind']) ? 0 : intval($_POST['bind']);
		$recommend=empty($_POST['recommend']) ? 0 : intval($_POST['recommend']);
		$currency=empty($_POST['currency']) ? 0 : intval($_POST['currency']);
		$old=empty($_POST['old']) ? 0 : intval($_POST['old']);
		$new=empty($_POST['new']) ? 0 : intval($_POST['new']);
		$total=empty($_POST['total']) ? 100000 : intval($_POST['total']);
		$limit=empty($_POST['limit']) ? 100000 : intval($_POST['limit']);
		$limit_type=empty($_POST['limit_type']) ? 0 : intval($_POST['limit_type']);
		$index=empty($_POST['index']) ? 0 : intval($_POST['index']);
		list($item_id,$item_name)=explode('|',$item.'|');
		if(empty($start)||date('Y-m-d H:i:s',strtotime($start))!=$start){
			ajax_return(0, __('请正确输入上架时间'));
		}elseif(empty($over)||date('Y-m-d H:i:s',strtotime($over))!=$over){
			ajax_return(0, __('请正确输入下架时间'));
		}elseif($start>=$over){
			ajax_return(0, __('上架时间不得大于下架时间'));
		}elseif(empty($item_id)||__($item_id)==$item_id){
			ajax_return(0, __('找不到道具：').$item_id);
		}elseif(!isset($currency_conf[$currency])){
			ajax_return(0, __('货币类型选择有误'));
		}elseif(!$old||$old<0){
			ajax_return(0, __('原价必须为正整数'));
		}elseif(!$new||$new<0){
			ajax_return(0, __('现价必须为正整数'));
		}elseif($total<=0){
			ajax_return(0, __('全服限购个数必须为正整数'));
		}elseif($limit<=0){
			ajax_return(0, __('个人限购个数必须为正整数'));
		}elseif(!isset($limit_type_conf[$limit_type])){
			ajax_return(0, __('个人限购类型选择有误'));
		}
		$gmer=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];

		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		if(!$id&&!$index){
			//新增,获取最大index
			$list=$mdb->find('activity', array('type'=>$activity_type), array('index'),array('sort'=>array('index'=>-1),'limit'=>1));
			$index=empty($list[0]['index']) ? 1 : intval($list[0]['index'])+1;
		}
		$start=strtotime($start);
		$over=strtotime($over);
		$time=time();

		$record=array(
			'type'=>$activity_type,
			'start'=>$start,
			'over'=>$over,
			'item_id'=>$item_id,
			'bind'=>$bind,
			'recommend'=>$recommend,
			'currency'=>$currency,
			'old'=>$old,
			'new'=>$new,
			'total'=>$total,
			'limit'=>$limit,
			'limit_type'=>$limit_type,
			'index'=>$index,
			'gmer'=>$gmer,
			'time'=>$time
		);
		if($id){
			$result=$mdb->update('activity', array('type'=>$activity_type,'item_id'=>$item_id), array('$set'=>$record));
		}else{
			$result=$mdb->insert('activity', $record);
		}
		if($result){
			$gm=new Gm();
			$msg=array('type'=>$activity_type);
			$gm->async($rpc,$rpc_obj,$async,$msg);
			ajax_return(1, __('数据保存成功'));
		}else{
			ajax_return(0, __('数据保存失败'));
		}
		break;

	case 'move':
		$id=empty($_POST['id']) ? '' : my_escape_string($_POST['id']);
		$type=empty($_POST['type']) ? '' : intval($_POST['type']);
		if(!$id||!$type){
			ajax_return(0, __('参数错误'));
		}
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$list=$mdb->findOne('activity', array('type'=>$activity_type,'item_id'=>$id), array('index'));
		if(!$list){
			ajax_return(0, __('数据已不存在'));
		}
		$index=intval($list['index']);
		switch ($type){
			case 1:
				//上移
				$list=$mdb->find('activity', array('type'=>$activity_type,'index'=>array('$lt'=>$index)), array('item_id','index'), array('sort'=>array('index'=>-1),'limit'=>1));
				break;
			case 2:
				//下移
				$list=$mdb->find('activity', array('type'=>$activity_type,'index'=>array('$gt'=>$index)), array('item_id','index'), array('sort'=>array('index'=>1),'limit'=>1));
				break;
			default:
				ajax_return(0, __('操作类型错误'));
				break;
		}
		$current_index=empty($list[0]['index']) ? $index : intval($list[0]['index']);
		$old_id=empty($list[0]['item_id']) ? $id : $list[0]['item_id'];

		$current_result=$mdb->update('activity', array('type'=>$activity_type,'item_id'=>$id), array('$set'=>array('index'=>$current_index)));
		$old_result=$mdb->update('activity', array('type'=>$activity_type,'item_id'=>$old_id), array('$set'=>array('index'=>$index)));
		if($current_result&&$current_result){
			//$gm=new Gm();
			//$msg=array('type'=>$activity_type);
			//$gm->async($rpc,$rpc_obj,$async,$msg);
			$data=array('old_id'=>$old_id);
			ajax_return(1, __('移动成功'),$data);
		}else{
			ajax_return(0, __('移动失败'));
		}
		break;

	case 'delete':
		//Xóa卡类记录(中央服)
		$id=empty($_POST['id']) ? array() : (array)$_POST['id'];
		if(!$id){
			ajax_return(0, __('参数错误'));
		}
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$result=$mdb->remove('activity', array('type'=>$activity_type,'item_id'=>array('$in'=>$id)));
		if($result){
			$gm=new Gm();
			$msg=array('type'=>$activity_type);
			$gm->async($rpc,$rpc_obj,$async,$msg);
			ajax_return(1, __('记录已Xóa'));
		}else{
			ajax_return(0, __('记录Xóa失败'));
		}
		break;
	case 'gm_update':
		$gm=new Gm();
		$msg=array('type'=>$activity_type);
		$gm->async($rpc,$rpc_obj,$async,$msg);
		ajax_return(1, __('更新成功'));
		break;
}

$conditions=array(
	'action'=>$action,
);
$action_conf=array('list'=>__('活动总览'));
$smarty->assign('action_conf',$action_conf);
$smarty->assign('currency_conf',$currency_conf);
$smarty->assign('limit_type_conf',$limit_type_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->display();