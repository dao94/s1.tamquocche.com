<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';
include __AUTH__.'lang.php';
include __CONFIG__.'smarty_config.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Ip.class.php';
include __CLASSES__.'Character.class.php';
class GmApi{
	//封禁api
	function forbid($data){		
		$data['type']=empty($data['type']) ? 0 : intval($data['type']);
		$data['name']=empty($data['name']) ? '' : my_escape_string($data['name']);
		$data['ip']=empty($data['ip']) ? '' : my_escape_string($data['ip']);
		$data['time']=empty($data['time']) ? 0 : intval($data['time']);
		$data['reason']=empty($data['reason']) ? '' : my_escape_string(trim($data['reason']));
		if((!$data['name']&&!$data['ip']) || !$data['time']){
			return array('status'=>0,'info'=>'parameters error');
		}
		
		$character=new Character();
		if($data['type']!=3){
			$list=$character->getCharacterByCondition(array('name'=>$data['name']));
			if(!$list){
				return array('status'=>0,'info'=>'user error');
			}
			$char_id=floatval($list['_id']);
		}
		switch ($data['type']){
			case 1:
			case 2:
				//禁言，封号
				$mysqli=new DbMysqli();
				//检测是否为封禁
				$sql="select count(*) as count from gm_forbid where char_id=$char_id and type={$data['type']} and status=1 and end_time>".time();
				$count=$mysqli->count($sql);
				if($count>0){
					return array('status'=>0,'info'=>'data exist');
				}
				
				$db_data=array(
					'char_id'=>$char_id,
					'start_time'=>time(),
					'end_time'=>time()+$data['time'],
					'type'=>$data['type'],
				);
				//数据入mongo库，同时进行封号处理
				$result=$character->forbid($db_data);
				if(!$result){
					return array('status'=>0,'info'=>'forbid error');
				}
				$id=uuid();
				$sql="insert into gm_forbid (id,char_id,char_name,account,sid,type,reason,reason_text,status,time,start_time,end_time,
					gm_account,gm_ip) value ('$id',$char_id,'{$list['name']}','{$list['account']}',{$list['serverId']},{$data['type']},'{$data['reason']}',
					'{$data['reason_text']}',1,{$data['time']},{$db_data['start_time']},{$db_data['end_time']},'{$data['gm_account']}','{$data['gm_ip']}')";
				$mysqli->query($sql);
				return array('status'=>1, 'info'=>'forbid succeed');
				break;
				
			case 3:
				//封IP
				if(!isset($data['ip']) || !preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $data['ip'])){
					return array('status'=>0,'info'=>'parameters error');
				}
				$data['ip']=my_escape_string($data['ip']);
				$sql="select count(*) as count from gm_forbid where ip='{$data['ip']}' and status=1 and end_time>".time();
				$mysqli=new DbMysqli();
				$count=$mysqli->count($sql);
				if($count>0){
					return array('status'=>0,'info'=>'data exist');
				}
				$db_data=array(
					'ip'=>$data['ip'],
					'start_time'=>time(),
					'end_time'=>time()+$data['time'],
					'type'=>$data['type'],
				);
				//数据入mongo库，同时进行封号处理
				$result=$character->forbid($db_data);
				if(!$result){
					return array('status'=>0,'info'=>'forbid error');
				}
				$id=uuid();
				$sql="insert into gm_forbid (id,ip,type,reason,reason_text,status,time,start_time,end_time,
					gm_account,gm_ip) value ('$id','{$data['ip']}',{$data['type']},'{$data['reason']}',
					'{$data['reason_text']}',1,{$data['time']},{$db_data['start_time']},{$db_data['end_time']},'{$data['gm_account']}','{$data['gm_ip']}')";
				$mysqli->query($sql);
				
				//封ip,踢下线在线玩家
				$login_time=time()-86400*2;
				$sql="select distinct char_id from log_login where login_time>=$login_time and logout_time=0 and ip='{$data['ip']}'";
				$query=$mysqli->query($sql);
				$forbid=new Forbid();
				while ($query && $row=$query->fetch_assoc()){
					$forbid->kickOffline(floatval($row['char_id']));
				}
				
				return array('status'=>1, 'info'=>'forbid succeed');
				break;
			
			case 4:
				$forbid=new Forbid();
				$forbid->kickOffline($char_id);
				return array('status'=>1, 'info'=>'forbid succeed');
				break;
			default:
				return array('status'=>0,'info'=>'type error');
				break;
		}
	}
	
	//解封
	function unforbid($data){
		switch ($data['type']){
			case 1:
			case 2:
				//解封号、解禁言
				$char_name=empty($data['name']) ? '' : my_escape_string($data['name']);
				$mysqli=new DbMysqli();
				$sql="select char_id from gm_forbid where char_name='$char_name' and type={$data['type']} and status=1 and end_time>".time();
				$list=$mysqli->findOne($sql);
				if(!$list){
					return array('status'=>0,'info'=>'data not exist');
				}
				$char_id=floatval($list['char_id']);
				$character=new Character();
				$character->unforbid(array('char_id'=>$char_id,'type'=>intval($data['type'])));

				$allow_time=time();
				$sql="update gm_forbid set status=2,allow_gm_account='{$data['gm_account']}',allow_gm_ip='{$data['gm_ip']}',allow_reason='{$data['reason']}',
					allow_time=$allow_time where char_id=$char_id and type={$data['type']} and status=1 and end_time>".time();
				$mysqli->query($sql);
				return array('status'=>1,'info'=>'unforbid succeed');
				break;
				
			case 3:
				//解ip
				$ip=empty($data['ip']) ? '' : my_escape_string($data['ip']);
				$mysqli=new DbMysqli();
				$sql="select count(*) as count from gm_forbid where ip='{$data['ip']}' and type={$data['type']} and status=1 and end_time>".time();
				$count=$mysqli->count($sql);
				if(!$count){
					return array('status'=>0,'info'=>'data not exist');
				}
				
				$character=new Character();
				$character->unforbid(array('ip'=>$data['ip'],'type'=>intval($data['type'])));
				$allow_time=time();
				$sql="update gm_forbid set status=2,allow_gm_account='{$data['gm_account']}',allow_gm_ip='{$data['gm_ip']}',allow_reason='{$data['reason']}',
					allow_time=$allow_time where ip='{$data['ip']}' and type={$data['type']} and status=1 and end_time>".time();
				$mysqli->query($sql);
				return array('status'=>1,'info'=>'unforbid succeed');
				break;
				
			default:
				return array('status'=>0,'info'=>'type error');
				break;
		}
	}

	//中央后台封禁接口
	function forbidFromCenter($data){
		include __CONFIG__.'game_config.php';
		//禁言、封号、封IP
		foreach ($data as $key=>$value){
			$value=trim($value);
			$value!=='' ? $$key=$value : '';
		}
		if(!isset($type) || !isset($time) || !isset($reason)){
			 return array('status'=>0, 'info'=>__('非法请求'));
		}
		if(!array_key_exists($type, $forbid_type_conf)){
			 return array('status'=>0, 'info'=>__('你到底想干嘛'));
		}
		$type=intval($type);
		$start_time=time();
		$end_time=$time ? $start_time+$time : $start_time+86400*365*20;
		$id=uuid();
		//$gm_account=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
		$reason_text=isset($reason_text) ? $reason_text : '';
		//$gm_ip=get_client_ip();
		$data=array('start_time'=>$start_time,'end_time'=>$end_time,'type'=>$type);
		$character=new Character();
		$log_data=array();
		$log_data['id']=$id;
		$log_data['type']=$type;
		$log_data['reason']=$reason;
		$log_data['reason_text']=$reason_text;
		$log_data['status']=1;
		$log_data['time']=$time;
		$log_data['start_time']=$start_time;
		$log_data['end_time']=$end_time;
		$log_data['gm_account']=$gm_account;
		$log_data['gm_ip']=$gm_ip;
		$log_data['server_info']=SERVER_AGENT .'_'. SERVER_ID;;
		if($type==1 || $type==2 || $type==4){
			//禁言、封号
			if(!isset($char_id) && !isset($name) && !isset($account)){
				return array('status'=>0, 'info'=>__('参数有误'));
			}
			$condition=array();
			isset($char_id) ? $condition['_id']=floatval($char_id) : '';
			isset($name) ? $condition['name']=$name : '';
			if(isset($account)){
				$condition['account']=$account;
				$condition['serverId']=isset($sid) ? intval($sid) : intval(substr(SERVER_ID,1));//剔除首字母
			}

			$time=array_key_exists($time,$forbid_time_conf) ? $time : 0;
			$info=$character->getCharacterByCondition($condition);
			if(!$info){
				return array('status'=>0, 'info'=>__('玩家不存在'));
			}
			$char_id=$data['char_id']=floatval($info['_id']);
			$char_name=$info['name'];
			$account=$info['account'];
			$sid=$info['serverId'];
			$sql="select count(*) as count from gm_forbid where char_id=$char_id and type=$type and status=1 and end_time>".time();
			$mysqli=new DbMysqli();
			$count=$mysqli->count($sql);
			if($count>0){
				return array('status'=>0, 'info'=>__('此玩家已被封禁'));
			}
			$sql="insert into gm_forbid (id,char_id,char_name,account,sid,type,reason,reason_text,status,time,start_time,end_time,
				gm_account,gm_ip) value ('$id',$char_id,'$char_name','$account',$sid,$type,$reason,'$reason_text',1,$time,$start_time,
				$end_time,'$gm_account','$gm_ip')";
			$log_data['char_id']=$char_id;
			$log_data['char_name']=$char_name;
			$log_data['account']=$account;
			$log_data['sid']=$sid;
		}elseif($type==3){
			//封IP
			if(!isset($ip) || !preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)){
				return array('status'=>0, 'info'=>__('请正确填写封禁IP'));
			}
			$ip=$data['ip']=my_escape_string($ip);
			$sql="select count(*) as count from gm_forbid where ip='$ip' and status=1 and end_time>".time();
			$mysqli=new DbMysqli();
			$count=$mysqli->count($sql);
			if($count>0){
				return array('status'=>0, 'info'=>__('此IP已被封禁'));
			}
			$sql="insert into gm_forbid (id,ip,type,reason,reason_text,status,time,start_time,end_time,gm_account,gm_ip)
				value ('$id','$ip',$type,$reason,'$reason_text',1,$time,$start_time,$end_time,'$gm_account','$gm_ip')";
			$log_data['ip']=$ip;
		}
		if($type==4){
			//踢人下线不保存数据
			include __CLASSES__.'Forbid.class.php';
			$forbid=new Forbid();
			$forbid->kickOffline($char_id);
		}else{
			//非踢人下线保存数据
			$result=$character->forbid($data);//mongo数据处理,同时进行封号处理
			if($result){
				$result=$mysqli->query($sql);
			}
			else{
				return array('status'=>0, __('数据插入失败'));
			}
			if($type==3){
				//封ip,踢下线在线玩家
				$login_time=$start_time-86400*2;
				$sql="select distinct char_id from log_login where login_time>=$login_time and logout_time=0 and ip='$ip'";
				$query=$mysqli->query($sql);
				$forbid=new Forbid();
				while ($query && $row=$query->fetch_assoc()){
					$forbid->kickOffline(floatval($row['char_id']));
				}
			}
		}
		return array('status'=>1, 'info'=>__('封禁成功'),'log_data'=>$log_data);
	}

	//中央后台解封接口
	function unforbidFromCenter($data){
		//解封
		if(empty($data['id']) || empty($data['allow_reason'])){
			return array('status'=>0, 'info'=>__('非法请求'));
		}
		$id=my_escape_string(trim($data['id']));
		$allow_reason=my_escape_string(trim($data['allow_reason']));
		$mysqli=new DbMysqli();
		$sql="select * from gm_forbid where id='$id' limit 1";
		$list=$mysqli->findOne($sql);
		if(!$list){
			return array('status'=>0, 'info'=>__('数据缺失'));
		}
		$type=intval($list['type']);
		$character=new Character();
		switch ($type){
			case 1:
			case 2:
				$char_id=floatval($list['char_id']);
				$result=$character->unforbid(array('char_id'=>$char_id,'type'=>$type));
				break;
			case 3:
				$ip=$list['ip'];
				$character->unforbid(array('ip'=>$ip,'type'=>$type));
				break;
		}
		//$allow_gm_account=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
		//$allow_gm_ip=get_client_ip();
		$allow_time=time();
		$status=2;
		$sql="update gm_forbid set status=$status,allow_gm_account='$allow_gm_account',allow_gm_ip='$allow_gm_ip',
			allow_reason='$allow_reason',allow_time=$allow_time where id='$id'";
		if($mysqli->query($sql)){
			$data=array(
				'id'=>$id,
				'status'=>$status,
				'allow_reason'=>$allow_reason,
				'allow_gm_account'=>$allow_gm_account,
				'allow_gm_ip'=>$allow_gm_ip,
				'allow_time'=>$allow_time,
			);
			return array('status'=>1, 'info'=>__('解封成功'),'log_data'=>$data);
		}else{
			return array('status'=>1, 'info'=>__('数据异常'));
		}
	}
}
$server = new PHPRPC_Server();
$server->add(get_class_methods(GmApi),new GmApi);
$server->setEnableGZIP(true);
$server->setDebugMode(true);
$server->start();