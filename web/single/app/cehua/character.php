<?php

//角色分布
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__. 'Mdb.class.php';
include __CONFIG__.'game_config.php';

$cache_lifetime=1;//数据缓存时间（单位：小时）
$smarty->caching=true;
$smarty->cache_lifetime=3600*$cache_lifetime;
$action=empty($_GET['action']) ? 'pop' : trim($_GET['action']);
$action_conf=array(
	'pop'=>__('人口分布'),
	'level'=>__('等级分布'),
);
$data=array();
switch ($action){
	case 'pop':
		$conditions=array('action'=>$action,'cache_lifetime'=>$cache_lifetime);
		$cache_id=md5(serialize($conditions));
		if(!$smarty->isCached('character.html',$cache_id)){
			//人口分布
			$level_conf=array();
			$mdb=new Mdb();
			for ($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				for($level=0;$level<=MAX_LEVEL;$level+=10){
					if(!in_array($level, $level_conf)){
						$level_conf[$level]=$level.'-'.($level+9);
					}
					$condition=array('level'=>array('$gte'=>$level,'$lte'=>$level+9));
					$groups=array('gender','occ','camp');
					foreach ($groups as $item){
						$group=array($item);
						$list=$mdb->count('characters', $condition, $group);
						foreach ($list as $row){
							if(empty($data[$item][$row['_id']][$level]))
							$data[$item][$row['_id']][$level]=$row['value']['count'];
							else
							$data[$item][$row['_id']][$level]+=$row['value']['count'];
						}
					}
				}

				foreach ($occ_conf as $occ=>$name){
					$condition=array('occ'=>$occ);
					$groups=array('gender','camp');
					foreach ($groups as $item){
						$group=array($item);
						$list=$mdb->count('characters', $condition, $group);
						foreach ($list as $row){
							if(empty($data[$item][$occ][$row['_id']]))
							$data[$item][$occ][$row['_id']]=$row['value']['count'];
							else
							$data[$item][$occ][$row['_id']]+=$row['value']['count'];
						}
					}
				}
			}
			$total_character_count=$mdb->allCount('characters');
			$smarty->assign('level_conf',$level_conf);
			$smarty->assign('total_character_count',$total_character_count);
		}
		break;

	case 'level':
		//等级分布
		$online=((isset($_GET['online']) && $_GET['online']=='') || !isset($_GET['online']))  ? '' : intval($_GET['online']);
		$gender=((isset($_GET['gender']) && $_GET['gender']=='') || !isset($_GET['gender']))  ? '' : intval($_GET['gender']);
		$camp=isset($_GET['camp'])&&$_GET['camp']!=='' ? intval($_GET['camp']) : '';
		$occ=empty($_GET['occ']) ? 0 : intval($_GET['occ']);
		$conditions=array(
			'online'=>$online,
			'camp'=>$camp,
			'occ'=>$occ,
			'gender'=>$gender,
			'action'=>$action,
			'cache_lifetime'=>$cache_lifetime,
		);
		$cache_id=md5(serialize($conditions));
		if(!$smarty->isCached('character.html',$cache_id)){
			$condition=$online_char=array();
			if($camp!=='') $condition['camp']=$camp;
			if($occ) $condition['occ']=$occ;
			if($gender!='') $condition['gender']=$gender;
			//在线玩家数
			if($online!==''){
				$login_time=time()-86400*2;
				$mysqli=new DbMysqli();
				$sql="select distinct char_id from log_login where login_time>=$login_time and logout_time=0";
				$result=$mysqli->query($sql);
				while ($result && $row=$result->fetch_assoc()){
					$online_char[$row['char_id']%4][]=floatval($row['char_id']);
				}
			}
			$total_character_count=0;
			$mdb=new Mdb();
			for ($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				if(!empty($online_char[$i])){
					$condition['_id']=$online==1 ? array('$in'=>$online_char[$i]) : array('$nin'=>$online_char[$i]);
				}
				$list=$mdb->count('characters', $condition, array('level'));
				foreach ($list as $row){
					$total_character_count+=$row['value']['count'];
					if(empty($data[$row['_id']])){
						$data[$row['_id']]=$row['value']['count'];
					}else{
						$data[$row['_id']]+=$row['value']['count'];
					}
				}
			}
			unset($online_char);
			ksort($data);
			$smarty->assign('total_character_count',$total_character_count);
		}
		break;
}
$smarty->cache_id=$cache_id;
$smarty->assign('conditions',$conditions);
$smarty->assign('gender_conf',$gender_conf);
$smarty->assign('occ_conf',$occ_conf);
$smarty->assign('camp_conf',$camp_conf);
$smarty->assign('action',$action);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('data',$data);
$smarty->display('',$cache_id);