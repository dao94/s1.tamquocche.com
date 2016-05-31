<?php

//玩家封禁
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__ .'auth.php';
include __CONFIG__.'smarty_config.php';
include __CONFIG__.'game_config.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Ip.class.php';
include __CLASSES__.'Character.class.php';
include __LIB__.'phprpc_php/phprpc_client.php';

$action_conf=array(
	'manage'=>__('封禁管理'),
	//'ip'=>__('IP查询'),
	'batch'=>__('批量封禁'),
);
$action=empty($_GET['action']) ? 'manage' : trim($_GET['action']);
$data=$conditions=array();
switch ($action){
	case 'manage':
	default:
		//查询
		$type=empty($_GET['type']) ? '' : intval($_GET['type']);
		$type=!array_key_exists($type, $forbid_type_conf) ? 2 : $type;
		$char_name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));
		$account=empty($_GET['account']) ? '' : my_escape_string(trim($_GET['account']));
		$gm_account=empty($_GET['gm_account']) ? '' : my_escape_string(trim($_GET['gm_account']));
		$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
		$status=isset($_GET['status']) && $_GET['status']!==''  ? intval($_GET['status']) : '';
		$reason=isset($_GET['reason']) && $_GET['reason']!==''  ? intval($_GET['reason']) : '';
		$ip=empty($_GET['ip']) ? '' : my_escape_string(trim($_GET['ip']));

		$where=" where type=$type";
		$where.=$char_name ? " and char_name='$char_name'" : '';
		$where.=$account ? " and account='$account'" : '';
		$where.=$gm_account ? " and gm_account='$gm_account'" : '';
		$where.=$start_date ? ' and start_time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and start_time<'.(strtotime($end_date)+86400) : '';
		$where.=$status ? " and status=$status" : '';
		$where.=$reason ? " and reason=$reason" : '';
		$where.=$ip ? " and ip='$ip'" : '';
		//过期查询
		if($status==3){
			$where.=" and end_time<=".time();
		}

		$mysqli=new DbMysqli();
		$sql='select count(*) from gm_forbid'.$where;
		$total_row=$mysqli->count($sql);
		$p=new Page($total_row);
		$sql="select * from gm_forbid $where order by start_time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$nowtime=time();
		$Ip=new Ip();
		while ($result && $row=$result->fetch_assoc()){
			//判断已过期
			if($row['end_time']<$nowtime && $row['status']==1){
				$row['status']=3;
				$sql="update gm_forbid set status=3 where id='{$row['id']}'";
				$mysqli->query($sql);
			}
			if($row['ip']){
				$location=$Ip->getlocation($row['ip']);
				$row['country']=$location['country'];
			}
			$row['start_time']=date('Y-m-d H:i',$row['start_time']);
			$row['end_time']=date('Y-m-d H:i:s',$row['end_time']);
			$data[]=$row;
		}
		$conditions=array(
			'action'=>$action,
			'char_name'=>$char_name,
			'account'=>$account,
			'gm_account'=>$gm_account,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'type'=>$type,
			'status'=>$status,
			'reason'=>$reason,
			'ip'=>$ip,
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		$smarty->assign('page',$p->show());
		break;

	case 'forbid':
		//禁言、封号、封IP
		foreach ($_POST as $key=>$value){
			$value=trim($value);
			$value!=='' ? $$key=$value : '';
		}
		!isset($type) || !isset($time) || !isset($reason) ? ajax_return(0, __('非法请求')) : '';
		!array_key_exists($type, $forbid_type_conf) ? ajax_return(0, __('你到底想干嘛')) : $type=intval($type);
		$start_time=time();
		$end_time=$time ? $start_time+$time : $start_time+86400*365*20;
		$id=uuid();
		$gm_account=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
		$reason_text=isset($reason_text) ? $reason_text : '';
		$gm_ip=get_client_ip();
		$data=array('start_time'=>$start_time,'end_time'=>$end_time,'type'=>$type);
		$character=new Character();
		if($type==1 || $type==2 || $type==4){
			//禁言、封号
			if(!isset($char_id) && !isset($name) && !isset($account)){
				ajax_return(0, __('参数有误'));
			}

			//只填写账号时封账号所有角色
			if(($type==1 || $type==2)&&isset($account)&&!isset($char_id) && !isset($name) && !isset($sid)){
				$condition=array();
				$condition['account']=$account;
				$player_list=array();
				$fields=array('_id');
				$mdb=new Mdb();
				for ($i=0;$i<4;$i++){
					$mdb->selectDb(MONGO_PERFIX.$i);
					$row=$mdb->find('characters', $condition,$fields);
					$player_list=array_merge($player_list,$row);
				}
				$phprpc_client = new PHPRPC_Client();
				$phprpc_client->setTimeout(10);
				$phprpc_client->setKeyLength(128);
				$phprpc_client->setEncryptMode(2);
				$phprpc_client->useService("http://{$_SERVER['HTTP_HOST']}/single/app/interface/gm_api.php");
				foreach($player_list as $player){
					$data=array();
					$data['char_id']=$player['_id'];
					$data['gm_account']=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
					$data['gm_ip']=get_client_ip();
					$data['type']=$type;
					$data['time']=$time;
					$data['reason']=$reason;
					$result=$phprpc_client->forbidFromCenter($data);
					//print_r($result);
				}
				ajax_return(1, __('封禁成功'));
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
				ajax_return(0, __('玩家不存在'));
			}
			$char_id=$data['char_id']=floatval($info['_id']);
			$char_name=$info['name'];
			$account=$info['account'];
			$sid=$info['serverId'];
			$sql="select count(*) as count from gm_forbid where char_id=$char_id and type=$type and status=1 and end_time>".time();
			$mysqli=new DbMysqli();
			$count=$mysqli->count($sql);
			if($count>0){
				ajax_return(0, __('此玩家已被封禁'));
			}
			$sql="insert into gm_forbid (id,char_id,char_name,account,sid,type,reason,reason_text,status,time,start_time,end_time,
				gm_account,gm_ip) value ('$id',$char_id,'$char_name','$account',$sid,$type,$reason,'$reason_text',1,$time,$start_time,
				$end_time,'$gm_account','$gm_ip')";
		}elseif($type==3){
			//封IP
			if(!isset($ip) || !preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)){
				ajax_return(0, __('请正确填写封禁IP'));
			}
			$ip=$data['ip']=my_escape_string($ip);
			$sql="select count(*) as count from gm_forbid where ip='$ip' and status=1 and end_time>".time();
			$mysqli=new DbMysqli();
			$count=$mysqli->count($sql);
			if($count>0){
				ajax_return(0, __('此IP已被封禁'));
			}
			$sql="insert into gm_forbid (id,ip,type,reason,reason_text,status,time,start_time,end_time,gm_account,gm_ip)
				value ('$id','$ip',$type,$reason,'$reason_text',1,$time,$start_time,$end_time,'$gm_account','$gm_ip')";
		}
		if($type==4){
			//踢人下线不保存数据
			include __CLASSES__.'Forbid.class.php';
			$forbid=new Forbid();
			$forbid->kickOffline($char_id);
		}else{
			//非踢人下线保存数据
			$result=$character->forbid($data);//mongo数据处理,同时进行封号处理
			$result ? $result=$mysqli->query($sql) : ajax_return(0, __('数据插入失败'));
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
		ajax_return(1, __('封禁成功'));
		break;

	case 'unforbid':
		//解封
		if(empty($_POST['id']) || empty($_POST['allow_reason'])){
			ajax_return(0, __('非法请求'));
		}
		$id=my_escape_string(trim($_POST['id']));
		$allow_reason=my_escape_string(trim($_POST['allow_reason']));
		$mysqli=new DbMysqli();
		$sql="select * from gm_forbid where id='$id' limit 1";
		$list=$mysqli->findOne($sql);
		if(!$list){
			ajax_return(0, __('数据缺失'));
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
		$allow_gm_account=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
		$allow_gm_ip=get_client_ip();
		$allow_time=time();
		$status=2;
		$sql="update gm_forbid set status=$status,allow_gm_account='$allow_gm_account',allow_gm_ip='$allow_gm_ip',
			allow_reason='$allow_reason',allow_time=$allow_time where id='$id'";
		if($mysqli->query($sql)){
			$data=array(
				'status'=>$forbid_status_conf[2],
				'allow_reason'=>$allow_reason,
				'allow_gm_account'=>$allow_gm_account,
			);
			ajax_return(1, __('解封成功'),$data);
		}else{
			ajax_return(1, __('数据异常'));
		}
		break;

	case 'bath':
		//批量封禁
		break;

	case 'ip':
		//IP查询
		$ip=empty($_GET['ip']) ? '' : my_escape_string(trim($_GET['ip']));
		$start_date=empty($_GET['start_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['end_date']));
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date)+86400;
		$start_time=($end_time-$start_time)>86400*60 ? $end_time-86400*60 : $start_time;
		$start_date=date('Y-m-d',$start_time);
		$status=empty($_GET['status']) ? '' : intval($_GET['status']);
		$conditions=array(
			'action'=>$action,
			'ip'=>$ip,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'status'=>$status,
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);

		$mysqli=new DbMysqli();
		$where=" where login_time>=$start_time and login_time<$end_time";
		if($ip){
			//查询封禁玩家
			$sql="select distinct char_id from gm_forbid where type=2 and status=1 and end_time>=".time();
			$result=$mysqli->query($sql);
			$forbid_char=array();
			while ($result && $row=$result->fetch_assoc()){
				$forbid_char[]=floatval($row['char_id']);
			}
			$forbid_char_str=$forbid_char ? implode(',', $forbid_char) : '';
			$where.=" and ip='$ip'";
			$where.=$status==1 && $forbid_char_str ? " and char_id in ($forbid_char_str)" : '';
			$where.=$status==2 && $forbid_char_str ? " and char_id not in ($forbid_char_str)" : '';
				
			$sql="select count(distinct char_id) as count from log_login $where";
			$total_row=$mysqli->count($sql);
			$p=new Page($total_row,30);
			$sql="select distinct char_id from log_login $where limit {$p->firstRow},{$p->listRows}";
			$result=$mysqli->query($sql);
			$character=new Character();
			while ($result && $row=$result->fetch_assoc()){
				$char_id=floatval($row['char_id']);
				$info=(array)$character->getCharacterById($char_id);
				if($info){
					$row['name']=$info['name'];
					$row['level']=$info['level'];
					$row['account']=$info['account'];
					$row['sid']=$info['serverId'];
					$row['creat_time']=date('Y-m-d H:i',$info['creat_time']);
				}
				//是否已经封禁
				$sql="select count(*) as count from gm_forbid where char_id=$char_id and type=2 and status=1 and end_time>".time();
				$count=$mysqli->count($sql);
				$row['status']=$count>0 ? 1 : 2;
				$data[]=$row;
			}
			unset($forbid_char);
		}else{
			//封禁ip列表
			$sql="select distinct ip from gm_forbid where type=3 and status=1 and end_time>=".time();
			$result=$mysqli->query($sql);
			$forbid_ip=array();
			while ($result && $row=$result->fetch_assoc()){
				$forbid_ip[]=$row['ip'];
			}
			$forbid_ip_str=$forbid_ip ? "'".implode("','", $forbid_ip)."'" : '';
			$where.=$status==1 && $forbid_ip_str ? " and ip in ($forbid_ip_str)" : '';
			$where.=$status==2 && $forbid_ip_str ? " and ip not in ($forbid_ip_str)" : '';
			$sql="select count(distinct ip) as count from log_login $where";
			$total_row=$mysqli->count($sql);
			$p=new Page($total_row,30);
			$sql="select ip,count(distinct char_id) as count from log_login $where group by ip order by count desc limit {$p->firstRow},{$p->listRows}";
			$result=$mysqli->query($sql);
			$ip=new Ip();
			while ($result && $row=$result->fetch_assoc()){
				$location=$ip->getlocation($row['ip']);
				$row['country']=$location['country'];
				$row['status']=in_array($row['ip'], $forbid_ip) ? 1 : 2;
				$data[]=$row;
			}
			unset($forbid_ip);
		}
		$page=$p->show();
		$status_conf=array(1=>__('已封禁'),2=>__('未封禁'));
		$smarty->assign('status_conf',$status_conf);
		$smarty->assign('page',$p->show());
		break;
}
$smarty->assign('conditions',$conditions);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('forbid_time_conf',$forbid_time_conf);
$smarty->assign('forbid_reason_conf',$forbid_reason_conf);
$smarty->assign('forbid_type_conf',$forbid_type_conf);
$smarty->assign('forbid_status_conf',$forbid_status_conf);
$smarty->assign('data',$data);
$smarty->display();