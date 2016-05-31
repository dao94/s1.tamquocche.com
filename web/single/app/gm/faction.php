<?php
//帮派查询
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CONFIG__.'game_config.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Skill.class.php';

$action=empty($_GET['action']) ? 'info' : trim($_GET['action']);
$id=empty($_GET['id']) ? '' : my_escape_string(trim($_GET['id']));
$name=empty($_GET['faction_name']) ? '' : my_escape_string(trim($_GET['faction_name']));
$conditions=array(
	'action'=>$action,
	'id'=>$id,
	'name'=>$name,
);
$data=array();
switch ($action){
	case 'info':
		$id ? $condition['_id']=$id : '';
		$name ? $condition['name']=$name : '';
		if(isset($condition)){
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$data=$mdb->findOne('faction', $condition, array());
			if($data){
				//建筑物等级配置
				$building_conf=array('lobby','storage','institute','astrology','dogz');
				foreach ($building_conf as $building){
					$list=$mdb->findOne('faction_'.$building, array('_id'=>$data['_id']), array('level'));
					$data[$building]=$list ? $list['level'] : 0;
				}
				//帮主
				$mdb->selectDb(MONGO_PERFIX.$data['presidentId']%4);
				$character=$mdb->findOne('characters', array('_id'=>floatval($data['presidentId'])), array('name'));
				$data['presidentName']=$character ? $character['name'] : '';
				$data['memberCount']=count($data['memberList']);
				$data['createTime']=isset($data['createTime']) ? date('Y-m-d H:i',$data['createTime']) : '';
				//这里要做出判断 如果是解散帮派 $data['state']==0
				if($data['state']==0){
					//从流水获得
					$mysqli=new DbMysqli();
					$sql="select * from log_faction_dissolution where (faction_id='$id' or faction_name='$name')  ";
					$result=$mysqli->query($sql);
					while($result && $row=$result->fetch_assoc()){
						$member[0]=$row['char_id'];
						if($row['char_id']){
							$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
							$list=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
							$member[4]=$list ? $list['name'] : '';//角色名
						}
						$member[2]=$row['now_facton_contribution'];//当前帮贡
						$member[3]=$row['history_facton_contribution'];//历史帮贡
						$member[1]=$row['faction_position'];//职位
						$member[5]='';//角色等级
						//解散时间
						$member[6]=isset($data['dissolutionTime']) ? date('Y-m-d H:i',$data['dissolutionTime']) : '';
						$k=$row['char_id'];
						$data['memberList'][$k]=$member;

					}

				}else{
					//成员
					$mysqli=new DbMysqli();
					$time=time();
					foreach($data['memberList'] as $key=>$member){
						$mdb->selectDb(MONGO_PERFIX.$member[0]%4);
						$character=$mdb->findOne('characters', array('_id'=>$member[0]), array('name','level'));
						$member[4]=$character ? $character['name'] : '';//角色名
						$member[5]=$character ? $character['level'] : '';//角色等级
						//登陆信息
						$online=is_online($member[0]);//是否在线
						if($online !=1){
							//不在线
							$sql="select login_time,logout_time from log_login where char_id='{$member[0]}' order by id desc limit 1";
							$login=$mysqli->findOne($sql);
							$member[6]=date('m-d H:i',$login['logout_time']);
							$k=$login['logout_time']+$member[0];
						}else{
							//在线
							$member[6]=0;
							$k=$time+$member[0];
						}
						$data['memberList'][$k]=$member;
						unset($data['memberList'][$key]);

						krsort($data['memberList']);
					}

				}

				//技能
				$skill=new Skill();
				foreach ($data['skillList'] as $key=>$item){
					$data['skillList'][$key]=$skill->getName($item);
				}
			}
		}
		break;
}
$log_conf=array();
if(isset($_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['log']['childs'])){
	$logs=$_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['log']['childs'];
	foreach ($logs as $key=>$item){
		if(strstr($key, 'log_faction_')){
			$log_conf['../log/'.$key]=$item['title'];
		}
	}
}
$smarty->assign('log_conf',$log_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('camp_conf',$camp_conf);
$smarty->assign('faction_state_conf',$faction_state_conf);
$smarty->assign('faction_authority_conf',$faction_authority_conf);
$smarty->assign('faction_position_conf',$faction_position_conf);
$smarty->display();