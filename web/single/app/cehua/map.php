<?php
//地图流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CONFIG__ . 'game_config.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Scene.class.php';

$cache_lifetime=1;//缓存时间（单位：小时）
$smarty->caching=true;
$smarty->cache_lifetime=3600*$cache_lifetime;//数据缓存1小时
$action=empty($_GET['action']) ? 'map_loss' : trim($_GET['action']);
$action_conf=array(
	'map_loss'=>__('地图流失'),
	'map_mission_loss'=>__('地图任务流失'),
);
$cache_id='';
$conditions=array();
switch ($action){
	default:
	case 'map_loss':
		//地图流失
		$type=empty($_GET['type']) ? 'list' : trim($_GET['type']);
		$min_level=empty($_GET['min_level']) ? 1 : intval($_GET['min_level']);
		$max_level=empty($_GET['max_level']) ? MAX_LEVEL : intval($_GET['max_level']);
		$day=empty($_GET['day'])  ? 3 : intval($_GET['day']);
		$online=((isset($_GET['online']) && $_GET['online']=='') || !isset($_GET['online']))  ? '' : intval($_GET['online']);
		$map_id=empty($_GET['map_id']) ? 0 : intval($_GET['map_id']);
		$level=empty($_GET['level']) ? 0 : intval($_GET['level']);
		$conditions=array(
			'action'=>$action,
			'type'=>$type,
			'online'=>$online,
			'min_level'=>$min_level,
			'max_level'=>$max_level,
			'day'=>$day,
			'cache_lifetime'=>$cache_lifetime,
			'map_id'=>$map_id,
			'level'=>$level,
		);
		$cache_id=md5(serialize($conditions));//缓存id
		$total_count=$total_loss_count=0;//查询范围玩家数和流失玩家数
		$time=time();
		$data=array();
		switch ($type){
			case 'list':
				if(!$smarty->isCached($type,$cache_id)){
					if($online!==''){
						//在线玩家
						$login_time=$time-86400*2;
						$mysqli=new DbMysqli();
						$sql="select distinct char_id from log_login where login_time>=$login_time and logout_time=0";
						$result=$mysqli->query($sql);
						while ($result && $row=$result->fetch_assoc()){
							$online_char[$row['char_id']%4][]=intval($row['char_id']);
						}
					}

					$mdb=new Mdb();
					$condition=array('level'=>array('$gte'=>$min_level,'$lte'=>$max_level));
					$fields=array('_id','loginTime');
					$loss_data=array();
					for ($i=0;$i<4;$i++){
						if($online===1 && !empty($online_char[$i])){
							//在线玩家
							$condition['_id']=array('$in'=>$online_char[$i]);
						}elseif($online===0 && !empty($online_char[$i])){
							//离线玩家
							$condition['_id']=array('$nin'=>$online_char[$i]);
						}
						$all_char=$loss_char=array();
						$mdb->selectDb(MONGO_PERFIX.$i);
						$limit=3000;
						$offset=0;
						while ($limit>0){
							$result_condition=array('start'=>$offset,'limit'=>$limit);
							$list=$mdb->find('characters', $condition, $fields, $result_condition);
							foreach ($list as $row){
								$total_count++;
								$all_char[]=$row['_id'];
								if(!(!empty($row['loginTime']) && $time-$row['loginTime']<=86400*$day)){
									//流失玩家
									$online!==1 ? $loss_char[]=$row['_id'] : '';
								}
							}
							if(count($list)<$limit){
								$offset=$limit=0;
								break;
							}
							$offset+=$limit;
						}

						$locations=$mdb->find('location', array('_id'=>array('$in'=>$all_char)), array('curScene.mapId','_id'));
						foreach ($locations as $location){
							$map_id=empty($location['curScene']['mapId']) ? '' : $location['curScene']['mapId'];
							if(isset($data[$map_id])){
								$data[$map_id]++;
								if(in_array($location['_id'], $loss_char))
								$loss_data[$map_id]++;
							}elseif($map_id){
								$data[$map_id]=1;
								$loss_data[$map_id]=($online===1 || !in_array($location['_id'], $loss_char)) ? 0 : 1;//选择在线,流水玩家无效
							}
						}
					}
					arsort($data);
					$scene=new Scene();
					$total_loss_count=array_sum($loss_data);
					foreach ($data as $map_id=>$count){
						$loss_count=isset($loss_data[$map_id]) ? $loss_data[$map_id] : 0;
						$row=array(
							'name'=>$scene->getName($map_id),
							'count'=>$count,
							'ratio'=>$total_count ? round($count/$total_count,4)*100 : 0,
							'loss_count'=>$loss_count,
							'loss_ratio'=>$total_loss_count ? round($loss_count/$total_loss_count,4)*100 : 0,
						);
						$data[$map_id]=$row;
					}
					unset($loss_data,$online_char,$all_char);
				}
				break;

			case 'level':
				//地图等级流失
				if(!$smarty->isCached($type,$cache_id)){
					$mdb=new Mdb();
					$condition=array('level'=>array('$gte'=>$min_level,'$lte'=>$max_level));
					$fields=array('_id','loginTime','level');
					$location_fields=array('_id','curScene.x','curScene.y');
					$loss_data=$pos_data=array();
					for ($i=0;$i<4;$i++){
						$all_char=$loss_char=array();
						$mdb->selectDb(MONGO_PERFIX.$i);
						$limit=5000;
						$offset=0;
						while ($limit>0){
							$result_condition=array('start'=>$offset,'limit'=>$limit);
							$list=$mdb->find('characters', $condition, $fields, $result_condition);
							foreach ($list as $row){
								$all_char[$row['_id']]=$row['level'];
								if(!(!empty($row['loginTime']) && $time-$row['loginTime']<=86400*$day)){
									$loss_char[$row['_id']]=$row['level'];//流失玩家
								}
							}
							if(count($list)<$limit){
								$offset=$limit=0;
								break;
							}
							$offset+=$limit;
						}

						//遍历循环location表
						$limit=5000;
						$offset=0;
						$location_condition=array('_id'=>array('$in'=>array_keys($all_char)),'curScene.mapId'=>$map_id);
						while ($limit>0){
							$result_condition=array('start'=>$offset,'limit'=>$limit);
							$list=$mdb->find('location', $location_condition, $location_fields, $result_condition);
							foreach ($list as $row){
								$total_count++;
								$level=$all_char[$row['_id']];//玩家等级
								$is_loss=array_key_exists($row['_id'], $loss_char);//是否为流失玩家
								if($is_loss){
									$pos_data[]=array('x'=>$row['curScene']['x'],'y'=>$row['curScene']['y']);//流失玩家坐标点集合
								}
								if(isset($data[$level]) && $level){
									$data[$level]++;
									$is_loss ? $loss_data[$level]++ : '';
								}else{
									$data[$level]=1;
									$loss_data[$level]=$is_loss ? 1 : 0;
								}
							}
							if(count($list)<$limit){
								$offset=$limit=0;
								break;
							}
							$offset+=$limit;
						}
					}
					$mdb->close();
					ksort($data);
					$scene=new Scene();
					$total_loss_count=array_sum($loss_data);
					$lt_level_count=0;
					foreach ($data as $level=>$count){
						$loss_count=isset($loss_data[$level]) ? $loss_data[$level] : 0;
						$row=array(
							'level'=>$level,
							'count'=>$count,
							'gte_level_count'=>$total_count-$lt_level_count,
							'ratio'=>$total_count ? round($count/$total_count,4)*100 : 0,
							'loss_count'=>$loss_count,
							'loss_ratio'=>$total_loss_count ? round($loss_count/$total_loss_count,4)*100 : 0,
						);
						$lt_level_count+=$count;
						$data[$level]=$row;
					}
					$image_data=$scene->plot($map_id,$pos_data,$conditions);
					unset($loss_data,$all_char,$pos_data);
					$conditions['map_name']=$scene->getName($map_id);
					$image_data['data'].='?t='.time();
					$smarty->assign('image_data',$image_data);
				}
				break;
					
			case 'plot':
				//指定流失玩家等级描点
				if(!$map_id || !$level || !$day)	ajax_return(0, __('参数有误！'));
				$pos_data=array();
				if(!S($cache_id)){
					$mdb=new Mdb();
					$condition=array('level'=>$level);
					$fields=array('_id','loginTime','level');
					$location_fields=array('_id','curScene.x','curScene.y');
					for ($i=0;$i<4;$i++){
						$loss_char=array();
						$mdb->selectDb(MONGO_PERFIX.$i);
						$list=$mdb->find('characters', $condition, $fields);
						foreach ($list as $row){
							if(!(!empty($row['loginTime']) && $time-$row['loginTime']<=86400*$day)){
								$loss_char[$row['_id']]=$row['level'];//流失玩家
							}
						}
							
						//遍历循环location表
						$location_condition=array('_id'=>array('$in'=>array_keys($loss_char)),'curScene.mapId'=>$map_id);
						$list=$mdb->find('location', $location_condition, $location_fields);
						foreach ($list as $row){
							$pos_data[]=array('x'=>$row['curScene']['x'],'y'=>$row['curScene']['y']);//流失玩家坐标点集合
						}
					}
					$mdb->close();
					S($cache_id,$pos_data,3600*$cache_lifetime);
				}else{
					$pos_data=S($cache_id);
				}
				$scene=new Scene();
				$image_data=$scene->plot($map_id,$pos_data,$conditions);
				ajax_return($image_data['status'], $image_data['info'],$image_data['data']);
				break;
		}
		$smarty->assign('day_options',range(1,7));
		$smarty->assign('total_count',$total_count);
		$smarty->assign('total_loss_count',$total_loss_count);
		$smarty->assign('data',$data);
		break;

	case 'map_mission_loss':
		//任务地图配置
		include __CONFIG__.'map_mission_config.php';
		include __CLASSES__.'Mission.class.php';
		$date=empty($_GET['date']) ? date('Y-m-d',strtotime('yesterday')) : my_escape_string(trim($_GET['date']));
		$map_id=empty($_GET['map_id']) ?  '' : intval($_GET['map_id']);
		$map_id=array_key_exists($map_id, $map_mission_conf) ? $map_id : 200101;
		$mid=empty($_GET['mid']) ?  $map_mission_conf[$map_id][0] : my_escape_string(trim($_GET['mid']));
		$conditions=array(
			'action'=>$action,
			'date'=>$date,
			'map_id'=>$map_id,
			'mid'=>$mid,
			'today'=>date('Y-m-d',strtotime('today')),
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		$cache_id=md5(serialize($conditions));//缓存id
		if(!$smarty->isCached($action,$cache_id)){
			$mysqli=new DbMysqli();
			$sql="select pos_x,pos_y from stat_map_mission where date='$date' and map_id=$map_id and mid='$mid'";
			$result=$mysqli->query($sql);
			$pos_data=array();
			while ($result && $row=$result->fetch_assoc()){
				$pos_data[]=array('x'=>$row['pos_x'],'y'=>$row['pos_y']);//流失玩家坐标点集合
			}
			$sql="select map_id,count(*) as count from stat_map_mission where date='$date' and mid='$mid' group by map_id order by count desc";
			$result=$mysqli->query($sql);
			$data=array();
			$total_count=0;
			while ($result && $row=$result->fetch_assoc()){
				$data[$row['map_id']]=$row['count'];
				$total_count+=$row['count'];
			}
				
			$scene=new Scene();
			$image_data=$scene->plot($map_id,$pos_data,$conditions);
			unset($pos_data);
			$mission=new Mission();
			$time_conf=array(
			date('Y-m-d',SERVER_OPEN_TIME+86400*2)=>__('开服3天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*3)=>__('开服4天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*4)=>__('开服5天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*5)=>__('开服6天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*6)=>__('开服7天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*13)=>__('开服14天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*29)=>__('开服30天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*59)=>__('开服60天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*89)=>__('开服90天'),
			date('Y-m-d',SERVER_OPEN_TIME+86400*179)=>__('开服180天'),
			date('Y-m-d',strtotime('yesterday'))=>__('昨天'),
			);
			$smarty->assign('time_conf',$time_conf);
			$smarty->assign('mission_list',$mission->getList());
			$smarty->assign('map_mission_conf',$map_mission_conf);
			$smarty->assign('scene_list',$scene->getList());
			$smarty->assign('image_path',$image_data['data'].'?t='.time());
			$smarty->assign('data',$data);
			$smarty->assign('total_count',$total_count);
		}
		break;
}
$smarty->assign('conditions',$conditions);
$smarty->assign('action_conf',$action_conf);
$smarty->display('',$cache_id);