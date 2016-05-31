<?php
//玩家数据Sửa
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Mdb.class.php';

$action=empty($_GET['action']) ? '' : trim($_GET['action']);
$field_conf=array(
	'level'=>__('等级'),
	'exp'=>__('经验'),
	'charm'=>__('魅力'),
	'arena_point'=>__('竞技点'),
	'spirit'=>__('真气'),
	'hunsoul'=>__('元力'),
	'exploit'=>__('功勋'),
	'money_0'=>__('铜币'),
	'money_1'=>__('铜券'),
	'money_2'=>__('元宝'),
	'money_3'=>__('礼券'),
);
switch ($action){
	case 'log':
		include __CLASSES__.'Page.class.php';
		$start_date=empty($_GET['start_date']) ? '' : trim($_GET['start_date']);
		$end_date=empty($_GET['end_date']) ? '' : trim($_GET['end_date']);
		$char_id=empty($_GET['char_id']) ? 0 : floatval($_GET['char_id']);
		$gmer=empty($_GET['gmer']) ? '' : my_escape_string($_GET['gmer']);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date)+86400;
		$where=" where char_id=$char_id";
		$end_date&&$where.=" and time>=$start_time";
		$end_date&&$where.=" and time<$end_time";
		$gmer&&$where.=" and gmer='$gmer'";
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from log_update_data $where";
		$count=$mysqli->count($sql);
		$p=new Page($count);
		$sql="select * from log_update_data $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		$data=array();
		while ($row=$query->fetch_assoc()){
			$row['old_remark']=json_decode($row['old_remark'],true);
			$row['new_remark']=json_decode($row['new_remark'],true);
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$conditions=array(
			'action'=>$action,
			'char_id'=>$char_id,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'gmer'=>$gmer,
		);
		$smarty->assign('conditions',$conditions);
		$smarty->assign('field_conf',$field_conf);
		$smarty->assign('data',$data);
		$smarty->assign('page',$p->show());
		$smarty->display();
		break;

	default:
		$new_remark=$old_remark=array();
		$allow_fields=array_keys($field_conf);
		$fields=$param_fields=empty($_POST['fields']) ? array() : (array)$_POST['fields'];
		$char_id=empty($_POST['char_id']) ? 0 : floatval($_POST['char_id']);
		$char_name=empty($_POST['char_name']) ? '' : my_escape_string($_POST['char_name']);
		$old_data=empty($_POST['old_data']) ? array() : (array)$_POST['old_data'];
		$new_data=empty($_POST['new_data']) ? array() : (array)$_POST['new_data'];
		$reason=empty($_POST['reason']) ? '' : my_escape_string(trim($_POST['reason']));
		sort($allow_fields);
		sort($param_fields);
		if($allow_fields!==$param_fields ||!$char_id || !$reason || !$new_data || count($new_data)!=count($param_fields) || count($old_data)!=count($new_data)){
			ajax_return(0, __('参数错误'));
		}
		foreach ($fields as $key=>$value){
			$old_value=$old_data[$key];
			$new_value=$new_data[$key];
			$$value=($value=='char_id') ? floatval($new_value) : intval($new_value);
			if($old_value!=$new_value){
				$old_remark[$value]=$old_value;
				$new_remark[$value]=$new_value;
			}
		}
		//判断玩家是否在线，而且下线时长大于1分钟，才允许Sửa数据
		$mysqli=new DbMysqli();
		$sql="select logout_time from log_login where char_id=$char_id order by id desc limit 1";
		$list=$mysqli->findOne($sql);
		if(isset($list['logout_time'])&&(empty($list['logout_time'])||time()-$list['logout_time']<60)){
			ajax_return(0, __('玩家必须下线1分钟之后方可Sửa数据'));
		}
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.$char_id%4);
		$condition=array('_id'=>$char_id);
		$result=$mdb->update('characters', $condition, array('$set'=>array('exp'=>$exp,'level'=>$level)));
		if(!$result){
			ajax_return(0, __('经验、等级数据Sửa失败'));
		}
		$result=$mdb->update('friend', $condition, array('$set'=>array('charm'=>$charm)),array('upsert'=>1));
		if(!$result){
			ajax_return(0, __('魅力值Sửa失败'));
		}
		$result=$mdb->update('character_info', $condition, array('$set'=>array('spirit'=>$spirit,'hunsoul'=>$hunsoul,'exploit'=>$exploit)),array('upsert'=>1));
		if(!$result){
			ajax_return(0, __('真气、元力、功勋值Sửa失败'));
		}
		$result=$mdb->update('character_bag', $condition, array('$set'=>array('moneyList.0'=>$money_0,'moneyList.1'=>$money_1,'moneyList.2'=>$money_2,'moneyList.3'=>$money_3)),array('upsert'=>1));
		if(!$result){
			ajax_return(0, __('铜币、铜券、元宝、礼券Sửa失败'));
		}

		$mdb->selectDb(MONGO_PERFIX.'5');
		$result=$mdb->update('arena_player', $condition, array('$set'=>array('point'=>$arena_point)),array('upsert'=>1));
		if(!$result){
			ajax_return(0, __('竞技点Sửa失败'));
		}
		//写流水日志
		if($new_remark&&$old_remark!==$new_remark){
			$time=time();
			$gmer=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
			//货币变动写log_money 流水
			$io=3;//log_money 字段io 3=Sửa
			$type=145;//log_money 字段type项目 145=后台Sửa
			foreach($new_remark as $key=>$new_value){
				$money_type=0;
				switch ($key){
					case 'exploit':
						$money_type=8;//功勋
						break;
					case 'arena_point':
						$money_type=10;//竞技点
						break;
					default:
						if(strstr($key,'money_')){
							$money_type=ltrim($key,'money_')+1;//铜币、铜券、元宝、礼券变化
						}
						break;
				}
				if($money_type>0){
					$old_value=$old_remark[$key];
					$money_num=abs($new_value-$old_value);
					$sql="insert into log_money (char_id,char_name,io,type,money_type,money_num,left_num,level,time) value ($char_id,'$char_name',$io,$type,$money_type,$money_num,$new_value,$level,$time)";
					$mysqli->query($sql);
				}
			}

			$old_remark=json_encode($old_remark);
			$new_remark=json_encode($new_remark);
			$sql="insert into log_update_data (char_id,char_name,old_remark,new_remark,reason,gmer,time) values ($char_id,'$char_name','$old_remark','$new_remark','$reason','$gmer',$time)";
			$mysqli->query($sql);
		}
		ajax_return(1, __('各项数据Sửa成功'));
	break;
}