<?php
//流水物品恢复
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Gm.class.php';

$action=empty($_GET['action']) ? '' : trim($_GET['action']);
switch ($action){
	case 'item':
		//道具恢复
		$char_id=empty($_POST['char_id']) ? 0 : floatval($_POST['char_id']);
		$id=empty($_POST['id']) ? array() : (array)$_POST['id'];
		$reason=empty($_POST['reason']) ? '' : my_escape_string($_POST['reason']);
		if(!$char_id||!$id){
			ajax_return(0, __('参数错误'));
		}
		$id=implode(',', $id);
		$mysqli=new DbMysqli();
		$sql="select * from log_items where char_id=$char_id and id in ($id)";
		$query=$mysqli->query($sql);
		$email_title=__('系统奖励');
		$email_content=__('恭喜获得系统奖励，请记得提取附件，如有问题请及时联系GM。感谢您对《乱舞江山》的支持！');
		$gm=new Gm();
		$rpc='borpc/boemail.rpc';
		$rpc_obj='borpc\\Sour_B2oEmail';
		$async='b2ocreateEmail_async';
		$time=time();
		$gmer=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
		$item_list=array();
		while ($row=$query->fetch_assoc()){
			$item_num=$row['item_num'] ? intval($row['item_num']) : 1;
			$item_list[]=array('itemId'=>$row['item_id'],'number'=>$item_num,'bind'=>intval($row['bind']),'data'=>$row['remark']);
			$sql="insert into log_restore (auto_id,char_id,char_name,item_id,item_num,remark,reason,gmer,time) values ({$row['id']},$char_id,'{$row['char_name']}',{$row['item_id']},$item_num,'{$row['remark']}','$reason','$gmer',$time)";
			$mysqli->query($sql);
		}
		if($item_list){
			$length=10;
			$offset=0;
			while ($length){
				$item_arr=array_slice($item_list, $offset,$length);
				if($item_arr){
					$email=array(
						'title'=>$email_title,
						'content'=>$email_content,
						'itemList'=>$item_arr,
						'list'=>array(array('charId'=>$char_id,'emailId'=>uuid()))
					);
					$gm->async($rpc, $rpc_obj, $async, $email);
				}
				if(count($item_arr)<$length){
					$length=0;
					break;
				}
				$offset+=$length;
			}
			unset($item_list);
		}
		ajax_return(1, __('数据恢复成功'));
		break;
		
	case 'pet':
		//宠物恢复
		$char_id=empty($_POST['char_id']) ? 0 : floatval($_POST['char_id']);
		$id=empty($_POST['id']) ? array() : (array)$_POST['id'];
		$reason=empty($_POST['reason']) ? '' : my_escape_string($_POST['reason']);
		if(!$char_id||!$id){
			ajax_return(0, __('参数错误'));
		}
		$id=implode(',', $id);
		$mysqli=new DbMysqli();
		$sql="select * from log_pet where char_id=$char_id and id in ($id)";
		$query=$mysqli->query($sql);
		$gm=new Gm();
		$rpc='borpc/bo_control.rpc';
		$rpc_obj='borpc\\Sour_B2oBgOper';
		$async='gmRepairPet_async';
		$time=time();
		$gmer=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
		while ($row=$query->fetch_assoc()){
			$msg=array(
				'remark'=>$row['remark'],
				'level'=>intval($row['level']),
				'occ'=>intval($row['original_id']),
				'charId'=>floatval($row['char_id']),
				'name'=>$row['pet_name'],
			);
			$sql="insert into log_restore (auto_id,char_id,char_name,pet_id,original_id,original_name,remark,reason,type,gmer,time) values ({$row['id']},$char_id,'{$row['char_name']}',{$row['pet_id']},{$row['original_id']},'{$row['original_name']}','{$row['remark']}','$reason',2,'$gmer',$time)";
			if($mysqli->query($sql)){
				$gm->async($rpc, $rpc_obj, $async, $msg);
			}else{
				ajax_return(0, __('数据恢复失败'));
			}
		}
		ajax_return(1, __('数据恢复成功'));
		break;
	default:
		ajax_return(0, __('无效请求'));
		break;
}