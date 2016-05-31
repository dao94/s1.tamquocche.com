<?php
//玩家查询
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CONFIG__.'game_config.php';
include __CLASSES__.'Mdb.class.php';
include __ROOT__.'/config/agent_list_config.php';
include __ROOT__.'/config/lianyun_list_config.php';

$action=empty($_GET['action']) ? 'info' : trim($_GET['action']);
$id=empty($_GET['id']) ? '' : floatval($_GET['id']);
$name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));
$account=empty($_GET['account']) ? '' : my_escape_string(trim($_GET['account']));
$sid=empty($_GET['sid']) ? '' : intval(trim($_GET['sid']));
$conditions=array(
	'action'=>$action,
	'id'=>$id,
	'name'=>$name,
	'account'=>$account,
	'sid'=>$sid,
);

$data=array();
switch ($action){
	case 'info':
		//查询玩家信息
		$id&&$condition['_id']=$id;
		$name&&$condition['name']=$name;
		$account&&$condition['account']=$account;
		($sid!==''&&$account)&&$condition['serverId']=$sid;
		$list=array();
		$mdb=new Mdb();
		if(isset($condition)){
			for ($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$character=$mdb->find('characters', $condition,array('account','name','serverId','occ','level','loginTime'));
				$list=array_merge($list,$character);
			}
		}
		//echo '<pre>';
		//print_r($list);exit;
			$ragent_list = array_flip($agent_list);
			$agent_id=$ragent_list[SERVER_AGENT];
			$lianyun_power_list=$_SESSION['__' . SERVER_TYPE . '__LIANYUN_POWER'][$agent_id];
			$lianyun_list_temp=$LianYun_List;
			//print_r($lianyun_list_temp);exit;
			$pattern='';
		
			if(is_array($lianyun_list_temp)&&count($lianyun_list_temp)&&is_array($lianyun_power_list)&&count($lianyun_power_list)){
						if(count($lianyun_power_list)!=count($lianyun_list_temp)){
								$d_flag=false;
								foreach($lianyun_power_list as $key => $value){
										if($lianyun_list_temp[$key]['query_key']==''){
												$d_flag=true;
												unset($lianyun_list_temp[$key]);
										}
								}
								if($d_flag){
										foreach($lianyun_power_list as $key => $value){
												unset($lianyun_list_temp[$key]);
										}
										foreach($lianyun_list_temp as $lianyun_temp){
												//$where.=" and account not like '%".$lianyun_temp['query_key']."'";
												$pattern = $pattern=='' ? $lianyun_temp['query_key'] : $pattern.'|'.$lianyun_temp['query_key'];
										}
										foreach($list as $key => $one_player){
												if(preg_match('/'.$pattern.'/isU',$one_player['account'])) unset($list[$key]);
										}
								}
								else{
										$lianyun_power=$lianyun_power_list;
										foreach($lianyun_power as $key => $value){
												//$where_add.=" or account like '%".$lianyun_list_temp[$key]['query_key']."'";
												$pattern = $pattern=='' ? $lianyun_list_temp[$key]['query_key'] : $pattern.'|'.$lianyun_list_temp[$key]['query_key'];
										}
										foreach($list as $key => $one_player){
												if(!preg_match('/'.$pattern.'/isU',$one_player['account'])) unset($list[$key]);
										}
								}
						}
						else{
								//do nothing
						}
			}
			else{
					//do nothing
			}
		if(count($list)==1){
			//一个账号只有一个角色，赋予id值，方便统一查询
			$id=empty($list[0]['_id']) ? 0 : $list[0]['_id'];
		}else{
			//一个账号多个角色，列表显示角色
			$smarty->assign('list',$list);
			$smarty->assign('occ_conf',$occ_conf);
			goto search_end;//跳转搜索结尾
		}
		$mdb->selectDb(MONGO_PERFIX.$id%4);
		$character=$mdb->findOne('characters', array('_id'=>$id));
		if(!$character){
			goto search_end;//跳转搜索结尾
		}
		include __CLASSES__.'Gm.class.php';
		include __CLASSES__.'Skill.class.php';
		include __CLASSES__.'Ride.class.php';
		include __CONFIG__.'exp_config.php';
		include __CONFIG__.'title_config.php';
		include __CLASSES__.'Wing.class.php';

		$char_id=floatval($character['_id']);
		$is_online=is_online($char_id);//是否在线

		if($is_online){
			//玩家在线，发送协议，请求服务端更新玩家数据
			$gm=new Gm();
			$rpc='borpc/bo_control.rpc';
			$rpc_obj='borpc\\Sour_B2oBgOper';
			$async='saveHumanData_async';
			$msg_data=array('id'=>$char_id);
			$gm->async($rpc, $rpc_obj, $async, $msg_data);
		}

		$mdb->selectDb(MONGO_PERFIX.'5');
		$human_offline=(array)$mdb->findOne('human_offline', array('_id'=>$char_id),array('attr','saveTime'));
		$attr=isset($human_offline['attr']) ? $human_offline['attr'] : array();//基本属性
		!empty($human_offline['saveTime'])&&$attr['gather_time']=date('Y-m-d H:i:s',$human_offline['saveTime']);//数据采集时间

		$mdb->selectDb(MONGO_PERFIX.$char_id%4);
		$character['max_exp']=isset($human_exp_conf[$character['level']]) ? $human_exp_conf[$character['level']] : 0;
		$character_bag=(array)$mdb->findOne('character_bag', array('_id'=>$char_id));//钱包
		$character_info=(array)$mdb->findOne('character_info', array('_id'=>$char_id));//vip信息
		$title=(array)$mdb->findOne('title', array('_id'=>$char_id));//称号
		$friend=(array)$mdb->findOne('friend', array('_id'=>$char_id), array('charm'));//好友魅力值
		$wardrobe=(array)$mdb->findOne('wardrobe', array('_id'=>$char_id));
		$wardrobe['wardrobe_level']=$wardrobe ? $wardrobe['lvl'] : 0;//衣柜等级
		$data=array_merge($attr,$character_bag,$character_info,$title,$friend,$wardrobe,$character);//数据整合
		
		$daily_active=(array)$mdb->findOne('daily_active', array('_id'=>$char_id), array('point'));//活跃度
		$data['daily_active']=empty($daily_active['point']) ? 0 : $daily_active['point'];

		$hun_soul=$mdb->findOne('hun_soul', array('_id'=>$char_id), array('lv'));//元神等级
		$data['soul_level']=empty($hun_soul['lv']) ? 0 : $hun_soul['lv'];
		
		$ride=(array)$mdb->findOne('ride', array('_id'=>$char_id));
		$Ride=new Ride();
		$Skill=new Skill();
		if($ride){
			//坐骑形象
			foreach ($ride['show'] as $model=>&$show){
				$show['name']=$Ride->getModelName($model);
			}
			$ride_skill=array();
			if(isset($ride['skill'])){
				foreach ($ride['skill'] as $skill_id){
					$skill_name=$Skill->getName($skill_id);
					$ride_skill[$skill_name]=$skill_id%1000;//技能等级
				}
			}
			$ride['skill']=$ride_skill;
			$data['ride']=$ride;
		}

		$human_skill=(array)$mdb->findOne('human_skill', array('_id'=>$char_id));//技能列表
		//无双技能
		$data['base_skill']=array();
		if(!empty($human_skill['baseSkill'])){
			foreach ($human_skill['baseSkill'] as $key=>$items){
				foreach ($items as $skill=>$item){
					$big_skill_name=$Skill->getName($skill);
					$skill_list=array();
					foreach ($item as $skill_id=>$level){
						$skill_name=$Skill->getName($skill_id);
						$skill_list[$skill_name]=$level;
					}
					$skill_list ? $data['base_skill'][$key][$big_skill_name]=$skill_list : '';
				}
			}
		}

		//帮派技能
		$data['faction_skill_list']=array();
		if(!empty($human_skill['factionSkills'])){
			foreach ($human_skill['factionSkills'] as $skill_id=>$item){
				$skill_id=$skill_id+$item[0];
				$skill_name=$Skill->getName($skill_id);
				$skill_name ? $data['faction_skill_list'][$skill_name]=$item[0] : '';
			}
		}

		//阵魂技能
		$mdb->selectDb(MONGO_PERFIX.'5');
		$pet_skill=(array)$mdb->findOne('pet_fight', array('_id'=>$char_id));//技能列表

		$data['pet_skill_list']=array();
		if(!empty($pet_skill['soulSkillList'])){
			$skill_list=array();
			foreach ($pet_skill['soulSkillList'] as $key=>$item){
				$skill_name=$Skill->getName($item[1]);
				$skill_name ? $data['pet_skill_list'][$skill_name]=$item[1] : '';
			}
		}

		$data['online']=$is_online;//是否在线
		//最后登录ip
		$sql="select ip from log_login where char_id=$char_id order by id desc limit 1";
		$mysqli=new DbMysqli();
		$list=$mysqli->findOne($sql);
		$data['last_ip']=empty($list['ip']) ? '' : $list['ip'];//登录ip
		
		unset($character,$character_info,$human_offline,$character_bag,$pets,$skill_list,$ride);

		$data['onlineTime']=empty($data['onlineTime']) ? '' : format_interval($data['onlineTime']);
		$data['creat_time']=empty($data['creat_time']) ? '' : date('Y-m-d H:i:s',$data['creat_time']);
		$data['loginTime']=empty($data['loginTime']) ? '' : date('Y-m-d H:i:s',$data['loginTime']);
		$data['leaveTime']=empty($data['leaveTime']) ? '' : date('Y-m-d H:i:s',$data['leaveTime']);

		$mdb->selectDb(MONGO_PERFIX.'5');
		//竞技点
		$arena_player=$mdb->findOne('arena_player', array('_id'=>$char_id), array('point'));//竞技点
		$data['arena_point']=empty($arena_player['point']) ? 0 : $arena_player['point'];
		//结婚信息
		$wedding=$mdb->findOne('wedding', array('_id'=>$char_id,'weddingState'=>array('$gt'=>0)), array('_id'=>false,'weddingTime','wife','ringLv','weddingLv'));
		if($wedding){
			$wedding['weddingTime']=date('Y-m-d H:i:s',$wedding['weddingTime']);//结婚时间
			$wedding['weddingLv']=isset($wedding_type_conf[$wedding['weddingLv']]) ? $wedding_type_conf[$wedding['weddingLv']] : $wedding['weddingLv'];//婚礼类型
			isset($ring_name_conf[$wedding['ringLv']])&&$wedding['ringName']=$ring_name_conf[$wedding['ringLv']];
			isset($ring_title_conf[$wedding['ringLv']])&&$wedding['ringTitle']=$ring_title_conf[$wedding['ringLv']];
			$mdb->selectDb(MONGO_PERFIX.$wedding['wife']%4);
			$wife_info=$mdb->findOne('characters', array('_id'=>$wedding['wife']), array('_id'=>false,'name'));
			$wedding['wife_name']=$wife_info ? $wife_info['name'] : '';
			$data=array_merge($data,$wedding);
		}

		//羽翼信息
		$Wing=new Wing();
		$mdb->selectDb(MONGO_PERFIX.$char_id%4);
		$fields=array('advLvl','bless','skillList');
		$wing=$mdb->findOne('wing', array('_id'=>$char_id),$fields);
		$wing_array=$wing_skill=array();
		if($wing){
			$wing_array['advLvl']=$wing['advLvl'];
			$wing_array['bless']=$wing['bless'];
			foreach($wing['skillList'] as $item){
				$skill_name=$Wing->getName($item[0]+$wing['advLvl']);
				$wing_skill[$skill_name]['id']=$item[0];//技能id
				$wing_skill[$skill_name]['level']=$item[1];//技能等级
				$wing_skill[$skill_name]['percent']=$item[2];//熟练度
			}
			$wing_array['skill']=$wing_skill;
			$data['wing']=$wing_array;
		}
		
		//保护锁信息
		$user_setting=$mdb->findOne('user_setting', array('_id'=>$char_id), array('lockInfo'));
		$data['lock_info']=empty($user_setting['lockInfo']) ? array() : $user_setting['lockInfo'];
		$data['lock_info']['total_count']=$mdb->allCount('user_setting', array('lockInfo.lock'=>array('$gt'=>0)));//使用保护锁人数
		unset($user_setting);
		
		$lock_conf=array(0=>__('未激活'),1=>__('上锁'),__('暂时解锁'));
		$smarty->assign('lock_conf',$lock_conf);
		$smarty->assign('bag_conf',$bag_conf);
		$smarty->assign('skill_type_conf',$skill_type_conf);
		$smarty->assign('title_conf',$title_conf+$achieve_conf);
		$smarty->assign('ride_type_conf',$ride_type_conf);
		$smarty->assign('occ_conf',$occ_conf);
		$smarty->assign('camp_conf',$camp_conf);
		$smarty->assign('faction_position_conf',$faction_position_conf);
		$smarty->assign('gender_conf',$gender_conf);
		break;

	case 'equip_bag':
		include __CLASSES__.'Item.class.php';
		$id=empty($_POST['id']) ? '' : floatval($_POST['id']);//角色id
		!$id&&ajax_return(0, __('参数有误'));
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.$id%4);
		$list=$mdb->findOne('character_item_2', array('_id'=>$id));
		$Item=new Item();
		$item_list=array();
		foreach ((array)$list['item_l'] as $item){
			$item_id=$item[1];
			$colour=(int)$Item->getColour($item_id);
			$level=(int)$Item->getLevel($item_id);
			$part=(int)$Item->getPart($item_id);
			$atrr=(array)$Item->getList($item[3]);//附加属性
			$item_list[$part]=array(
				'part'=>isset($part_conf[$part]) ? $part_conf[$part] : $part,
				'item_name'=>__((string)$item_id),
				'colour'=>isset($colour_conf[$colour]) ? $colour_conf[$colour] : $colour,
				'level'=>$part==13 || $part==15 ? 1 : $level,//时装默认等级1
				'attr'=>implode('', $atrr),
			);
		}
		foreach ((array)$list['part'] as $key=>$items){
			$part=$key+1;//部位
			//强化等级信息
			if(isset($items['strongList'])){
				foreach($items['strongList'] as $k=>$item){
					$item_list[$part]['part']=isset($part_conf[$part]) ? $part_conf[$part] : $part;
					$item_list[$part]['strong'.($k+1)]=$item[0];
				}
			}
			if(isset($items['gemList'][0])){
				foreach($items['gemList'][0] as $k=>$item){
					$item_list[$part]['part']=isset($part_conf[$part]) ? $part_conf[$part] : $part;
					$item_list[$part]['gem'.($k+1)]=$item[0];//宝石等级
					$item_list[$part]['gem_elite'.($k+1)]=$item[1];//宝石神lian
				}
			}
			isset($items['deilyLevel'])&&$item_list[$part]['deily_level']=$items['deilyLevel'];//神化等级
			isset($items['carveLevel'])&&$item_list[$part]['carve_level']=$items['carveLevel'];//宝石雕刻等级
		}
		ksort($item_list);
		ajax_return(1, 'Item list',$item_list);
		break;

	case 'other_bag':
		//人物背包、仓库背包道具列表
		include __CLASSES__.'Item.class.php';
		$id=empty($_POST['id']) ? '' : floatval($_POST['id']);//角色id
		!$id&&ajax_return(0, __('参数有误'));

		$data=array();
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.$id%4);
		$Item=new Item();
		$show_bag_conf=array(1,3,6);//要展示的背包
		foreach ($bag_conf as $bag_id=>$bag_name){
			if(in_array($bag_id, $show_bag_conf)){
				$list=$mdb->findOne('character_item_'.$bag_id, array('_id'=>$id));
				$data[$bag_name]['size']=$list['size'];
				$data[$bag_name]['count']=count($list['item_l']);
				$data[$bag_name]['list']=array();
				if(!empty($list['item_l'])){
					foreach ($list['item_l'] as $item_l){
						$atrr=(array)$Item->getList($item_l[3]);//附加属性
						$data[$bag_name]['list'][]=array(
							'item_id'=>$item_l[1],
							'item_name'=>__((string)$item_l[1]),
							'item_num'=>$item_l[2],
							'bind'=>$item_l[4],
							//'start_time'=>empty($item_l[5]) ? '' : date('y-m-d H:i',$item_l[5]),
							//'end_time'=>empty($item_l[6]) ? '' : date('y-m-d H:i',$item_l[6]),
							'attr'=>implode('', $atrr),//附加属性
						);
					}
				}
			}
		}
		ajax_return(1, 'Item list',$data);
		break;

	case 'pet':
		//宠物信息
		$id=empty($_GET['id']) ? '' : floatval($_GET['id']);//玩家id
		(!$id)&&ajax_return(0, __('参数有误'));

		include __CLASSES__.'PetRealm.class.php';
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$human_offline=$mdb->findOne('human_offline', array('_id'=>$id), array('pets'));
		$pets=empty($human_offline['pets']['petList']) ? array() : $human_offline['pets']['petList'];
		$offline_pets=array();//离线宠物信息
		foreach ($pets as $pet){
			$pet_id=(string)$pet['id'];
			$pets[]=$pet['id'];
			$offline_pets[$pet_id]=$pet;
		}


		$mdb->selectDb(MONGO_PERFIX.$id%4);
		$data=$mdb->findOne('pets', array('_id'=>$id));
		if($data){
			include __CLASSES__.'Skill.class.php';
			include __CLASSES__.'Pet.class.php';
			$Skill=new Skill();
			$Pet=new Pet();


			//兵符阁(道具列表)
			foreach ($data['hunt']['bag']['itemList'] as &$item){
				$item['name']=$Pet->getItemName($item['id']);
				$item['level']=$Pet->getItemLevel($item['id']);
			}

			$hunt_pet_list=array();
			foreach($data['hunt']['bag']['petList'] as &$pet_list){
				$pet_id=(string)$pet_list['petId'];
				foreach ($pet_list['itemList'] as &$item){
					$item['name']=$Pet->getItemName($item['id']);
					$item['level']=$Pet->getItemLevel($item['id']);
				}
				$hunt_pet_list[$pet_id]=$pet_list;
			}
			$data['hunt']['bag']['petList']=$hunt_pet_list;

			foreach ($data['petList'] as &$pet){
				$pet_id=(string)$pet['id'];
				$mdb->selectDb(MONGO_PERFIX.$pet['id']%4);
				$pet_bag=$mdb->findOne('pet_equipment_bag', array('_id'=>$pet['id']));
				if(!empty($pet_bag)){
					foreach($pet_bag['itemList'] as $lists){
						$data['equip_bag'][$pet_id]['item_id']=isset($lists[1]) ? $lists[1] : '';
						$data['equip_bag'][$pet_id]['level']=isset($lists[3]['level']) ? $lists[3]['level'] : '';
					}
				}
			}

			$PetRealm=new PetRealm();//境界名称
			foreach ($data['petList'] as &$pet){
				foreach ($pet['modelList'] as $model){
					$pet['modelName'][]=$Pet->getModelName($model);
				}

				$pet_id=(string)$pet['id'];
				$offline_pet=isset($offline_pets[$pet_id]) ? $offline_pets[$pet_id] : array();
				$pet=array_merge($offline_pet,$pet);
				$pet['skillList']=empty($pet['skillList']) ? array() : $pet['skillList'];
				//技能列表
				foreach ($pet['skillList'] as &$skill_list){
					$skill_list['skill']=$Skill->getName($skill_list['skillId']+$skill_list['level']);
					$desc=array();
					foreach ($skill_list['list'] as $key=>$value){
						$value&&$desc[]=$Skill->getName($skill_list['skillId']+$key+1);
					}
					$skill_list['desc']=$desc;
				}

				//境界等级，祝福值
				$mdb->selectDb(MONGO_PERFIX.$pet['id']%4);
				$pet_realm=$mdb->findOne('pet_realm', array('_id'=>$pet['id']));
				$pet['realm_name']=isset($pet_realm['realmLv']) ? $PetRealm->getName($pet_realm['realmLv']) : '';
				$pet['blessing']=isset($pet_realm['blessing']) ? $pet_realm['blessing'] : 0;

			}
			!empty($data['time'])&&$data['time']=date('Y-m-d H:i:s',$data['time']);
			$smarty->assign('pet_realm_conf',$pet_realm_conf);
		}
		break;

	case 'offline':
		//踢下线
		$char_id=empty($_POST['id']) ? 0 : floatval($_POST['id']);
		empty($char_id)&&ajax_return(0, __('参数错误'));
		include __CLASSES__.'Forbid.class.php';
		$forbid=new Forbid();
		$forbid->kickOffline($char_id);
		ajax_return(1, __('踢下线成功'));
		break;
	case 'reset_lock':
		//重置保护锁
		$char_id=empty($_POST['id']) ? 0 : floatval($_POST['id']);
		empty($char_id)&&ajax_return(0, __('参数错误'));
		include __CLASSES__.'Gm.class.php';
		$gm=new Gm();
		$rpc='burpc/avoid.rpc';
		$rpc_obj='burpc\\Sour_B2uResetProtectLock';
		$async='b2uReset_async';
		$msg=array('doubleinfo'=>$char_id);
		$gm->async($rpc,$rpc_obj,$async,$msg);
		ajax_return(1, __('重置保护锁成功'));
		break;
}
$log_conf=array();
if(isset($_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['log']['childs'])){
	$logs=$_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['log']['childs'];
	foreach ($logs as $name=>$childs){
		(!strstr($name, 'log_faction_')&&$name!='log_restore.php')&&$log_conf['../log/'.$name]=$childs['title'];
	}
}
$smarty->assign('log_conf',$log_conf);
search_end:
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->display();