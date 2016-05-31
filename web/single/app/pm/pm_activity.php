<?php
//活动设置
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Activity.class.php';
include __CONFIG__.'activity_config.php';
include __LIB__.'phprpc_php/phprpc_client.php';

$action_conf=array(
	'list'=>__('活动总览'),
	'set_view'=>__('活动设置'),
	'load'=>__('活动导入'),
);

$action=empty($_GET['action']) ? 'list' : trim($_GET['action']);
$type=empty($_GET['type']) ? 1 : intval($_GET['type']);//活动类型
$conditions=array('action'=>$action);
$mdb=new Mdb();
$mdb->selectDb(MONGO_PERFIX.'5');
switch ($action){
	case 'list':
		include __CLASSES__.'Copy.class.php';
		include __CLASSES__.'ActivityXml.class.php';
		$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));
		$gmer=empty($_GET['gmer']) ? '' : my_escape_string(trim($_GET['gmer']));
		$list_rows=empty($_GET['list_rows']) ? 20 : intval(trim($_GET['list_rows']));

		$conditions=array(
			'action'=>$action,
			'gmer'=>$gmer,
			'type'=>$type,
			'list_rows'=>$list_rows,
		);

		$condition=array('type'=>array('$nin'=>array(0,4)));
		$type&&$condition['type']=$type;
		$gmer&&$condition['gmer']=$gmer;

		$total_rows=$mdb->count('activity', $condition);

		$p=new Page($total_rows,$list_rows);
		$result_condition=array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array('start'=>-1));
		$list=$mdb->find('activity', $condition, array(), $result_condition);
		$ActivityXml=new ActivityXml();
		foreach ($list as $row){
			$row['start']=date('Y-m-d H:i:s',$row['start']);
			$row['over']=date('Y-m-d H:i:s',$row['over']);
			if(isset($row['is_xml'])){
				if($row['is_xml']==1){
					$ActivityXml->type=$row['type'];
					$row['xml_config']=$ActivityXml->getList();
				}
			}

			$data[]=$row;
		}

		$copy_name_list=$monster_occ_list=array();
		if(S('copy_name_list')){
			$copy_name_list=S('copy_name_list');
			$monster_occ_list=S('monster_occ_list');
		}else{
			$copy=new Copy();
			$copy_list=$copy->getList();
			$copy_activity=new Copy('copy_activity');
			$copy_activity_list=$copy_activity->getList();
			$copy_list+=$copy_activity_list;
			foreach ($copy_list as $id=>$copys){
				$id&&$copy_name_list[$id]=$copys['name'];
				foreach ($copys['layer_list'] as $layer_list){
					foreach ($layer_list as $row){
						$monster_occ_list[$row['occ']]=$row['name'];
					}
				}
			}
			S('copy_name_list',$copy_name_list,3600);
			S('monster_occ_list',$monster_occ_list,3600);
		}
		$list_rows_conf=range(20,60,20);
		$smarty->assign('list_rows_conf',$list_rows_conf);
		$smarty->assign('data',$data);
		$smarty->assign('copy_name_list',$copy_name_list);
		$smarty->assign('monster_occ_list',$monster_occ_list);
		$smarty->assign('page',$p->show());
		break;

	case 'delete':
		//Xóa记录
		$id_list=empty($_POST['id']) ? array() : (array)$_POST['id'];
		if(!$id_list){
			ajax_return(0, __('参数错误'));
		}

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->setTimeout(10);
		$phprpc_client->setKeyLength(128);
		$phprpc_client->setEncryptMode(2);
		$phprpc_client->useService("http://{$_SERVER['HTTP_HOST']}/single/app/interface/pm_api.php");
		foreach ($id_list as $id){
			$list=$mdb->findOne('activity', array('_id'=>$id), array('type','is_xml'));
			if($list){
				$data=array('action'=>'remove','id'=>$id,'type'=>intval($list['type']),'is_xml'=>intval($list['is_xml']));
				$result=$phprpc_client->pm_activity($data);
				if(!is_array($result)){
					ajax_return(0, __('网络错误'));
				}elseif(isset($result['status'])&&$result['status']==0){
					ajax_return($result['status'], $result['info']);
				}
			}
		}
		ajax_return(1, __('活动Xóa成功'));
		break;

	case 'set_view':
		//活动设置
		$id=empty($_GET['id']) ? 0 : my_escape_string(trim($_GET['id']));
		$data=$mdb->findOne('activity', array('_id'=>$id));
		if($data){
			$type=$data['type'];
			$data['start']=date('Y-m-d H:i:s',$data['start']);
			$data['over']=date('Y-m-d H:i:s',$data['over']);
			if($data['is_xml']){
				include __CLASSES__.'ActivityXml.class.php';
				$ActivityXml=new ActivityXml();
				$ActivityXml->type=$type;
				$data['xml_config']=$ActivityXml->getList();
			}
		}
		switch ($type){
			case 2:
				include __CLASSES__.'Copy.class.php';
				$copy=new Copy();
				$smarty->assign('copy_list',$copy->getList());
				break;
			case 17:
				include __CLASSES__.'Copy.class.php';
				$copy=new Copy('copy_activity');//活动副本
				$smarty->assign('copy_list',$copy->getList());
				break;
		}
		$smarty->assign('data',$data);
		break;

	case 'set':
		$id=empty($_POST['id']) ? '' : my_escape_string(trim($_POST['id']));
		$type=empty($_POST['type']) ? '' : intval(trim($_POST['type']));
		$start_date=empty($_POST['start_date']) ? '' : trim($_POST['start_date']);
		$end_date=empty($_POST['end_date']) ? '' : trim($_POST['end_date']);
		$param=empty($_POST['param']) ? array() : ($_POST['param']);
		$is_xml=empty($_POST['is_xml']) ? 0 : 1;
		$xml_config=empty($_POST['xml_config']) ? array() : ($_POST['xml_config']);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$activity_xml='';
		if($is_xml){
			include __CLASSES__.'ActivityXml.class.php';
			$ActivityXml=new ActivityXml();
			$ActivityXml->param=$param;
			$ActivityXml->type=$type;
			$ActivityXml->xml_config=$xml_config;
			$ActivityXml->start_time=$start_date;
			$ActivityXml->end_time=$end_date;
			$activity_xml=$ActivityXml->getActivityXml();
		}

		//条件判断
		if(date('Y-m-d H:i:s',$start_time)!=$start_date || date('Y-m-d H:i:s',$end_time)!=$end_date){
			ajax_return(0, __('时间设置错误'));
		}

		$data=array(
			'action'=>$id ? 'update' : 'add',
			'id'=>$id ? $id : uniqid(),
			'start'=>strtotime($start_date),
			'over'=>strtotime($end_date),
			'type'=>$type,
			'param'=>$param,
			'gmer'=>$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'],
			'is_xml'=>$is_xml,
			'activity_xml'=>$activity_xml,
			'status'=>1,
		);
		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->setTimeout(10);
		$phprpc_client->setKeyLength(128);
		$phprpc_client->setEncryptMode(2);
		$phprpc_client->useService("http://{$_SERVER['HTTP_HOST']}/single/app/interface/pm_api.php");
		$result=$phprpc_client->pm_activity($data);
		if(is_array($result)&&isset($result['status'])){
			ajax_return($result['status'], $result['info']);
		}else{
			ajax_return(0, __('网络错误'));
		}
		break;

	case 'export':
		//导出配置
		$id_str=empty($_GET['id']) ? '' : my_escape_string(trim($_GET['id']));
		$id_list=explode(',',rtrim($id_str,','));
		if(!$id_list){
			notice(__('参数错误'));
		}
		$list=$mdb->find('activity', array('_id'=>array('$in'=>$id_list)),array('start','over','type','param'));

		header('Content-type:application/octet-stream');
		header('Accept-Ranges:bytes');
		header("Content-Disposition:attachment;filename=pm_activity.txt");
		exit(json_encode($list));
		break;

	case 'load_data':
		//导入配置
		$data=empty($_POST['data']) ? '' : trim($_POST['data']);
		$data=json_decode($data,true);
		if(!$data){
			ajax_return(0, __('配置格式错误'));
		}
		$gmer=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
		$fields=array('_id', 'type','start','over','param');
		sort($fields);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->setTimeout(10);
		$phprpc_client->setKeyLength(128);
		$phprpc_client->setEncryptMode(2);
		$phprpc_client->useService("http://{$_SERVER['HTTP_HOST']}/single/app/interface/pm_api.php");
		foreach ($data as $row){
			$data_field=array_keys($row);
			sort($data_field);
			// $row['param']=json_decode($row['param'],true);
			if($fields!==$data_field || !$row['param']){
				ajax_return(0, __('格式缺失字段'));
			}
			$row['action']='add';
			$row['id']=uniqid();
			$row['gmer']=$gmer;
			$result=$phprpc_client->pm_activity($row);
			if(is_array($result)&&isset($result['status'])&&$result['status']==0){
				ajax_return($result['status'], $result['info']);
			}elseif(!is_array($result)){
				ajax_return(0, __('网络错误'));
			}
		}
		ajax_return(1, __('数据导入成功'));
		break;
}
$smarty->assign('type',$type);
$smarty->assign('activity_config',$activity_config);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->display();
