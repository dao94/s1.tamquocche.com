<?php
/* 统计脚本
 *	php player_equip.php --task=ride
 *	php player_equip.php --task=wing
 *	php player_equip.php --task=pet
 *	php player_equip.php --task=strong
 *	php player_equip.php --task=equip
 *	php player_equip.php --task=gem
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'ride':
		//总注册玩家数
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$count_register = $mdb->count('account_data', array('char_id' => array('$exists' => true)));

		//坐骑系
		$array_ride=array();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=500;
			$offset=0;
			$fields = array('rides');
			while($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list = $mdb->find('ride', array(), $fields, $result_condition);
				foreach($list as $items){
					foreach($items['rides'] as $type=>$row){
						$level=!empty($row[0]) ? $row[0] : 0;   //如 陆行系_1阶  $array_ride[$type][$level]
						isset($array_ride[$level]) ? $array_ride[$level]++ : $array_ride[$level]=1;
					}
				}
				if(count($list) < $limit) break;
				$offset +=$limit;
			}
		}
		ksort($array_ride);
		$str='';
		for($i=1;$i<=10;$i++){
			$ride=isset($array_ride[$i]) ? $array_ride[$i] : 0;
			$str.=$ride."\t";
		}

		echo SERVER_AGENT.'_'.SERVER_ID."\t".$count_register."\t".$str."\n";
		break;

	case 'wing':
		//总注册玩家数
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$count_register = $mdb->count('account_data', array('char_id' => array('$exists' => true)));
		//翅膀
		$array_wing=array();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=500;
			$offset=0;
			$fields = array('advLvl');
			while($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list = $mdb->find('wing', array(), $fields, $result_condition);
				if($list){
					foreach($list as $items){
						$adv_level=!empty($items['advLvl']) ? $items['advLvl'] : 0;
						$array_wing[$adv_level]=empty($array_wing[$adv_level]) ? $array_wing[$adv_level]=1 : ++$array_wing[$adv_level];
					}
				}

				if(count($list) < $limit) break;
				$offset +=$limit;
			}
		}
		ksort($array_wing);
		$str='';
		for($i=1;$i<=10;$i++){
			$wing=isset($array_wing[$i]) ? $array_wing[$i] : 0;
			$str.=$wing."\t";
		}
		echo SERVER_AGENT.'_'.SERVER_ID."\t".$count_register."\t".$str."\n";
		break;
	case 'pet':
		//总注册玩家数
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$count_register = $mdb->count('account_data', array('char_id' => array('$exists' => true)));
		//伙伴境界
		$mdb=new Mdb();
		$array_pet=array();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=3000;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('pet_realm', array(), array('realmLv'), $result_condition);
				foreach ($list as $row){
					//每10级一个境界
					$type=intval($row['realmLv']/11);
					$level=$type ? $row['realmLv']%($type*10+1)+1 : $row['realmLv'];
					if($level>0){
						isset($array_pet[$type][$level]) ? $array_pet[$type][$level]++ : $array_pet[$type][$level]=1;
					}
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
		}
		ksort($array_pet);
		$str='';
		for($i=0;$i<4;$i++){
			for($j=1;$j<=10;$j++){
				$pet=isset($array_pet[$i][$j]) ? $array_pet[$i][$j] : 0;
				$str.=$pet."\t";
			}
		}
		echo SERVER_AGENT.'_'.SERVER_ID."\t".$count_register."\t".$str."\n";
		break;
	case 'strong':
		//总注册玩家数
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$count_register = $mdb->count('account_data', array('char_id' => array('$exists' => true)));
		//装备强化分部位
		$array_fan=$array_ling=$array_xian=$array_shen=array();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$limit=500;
		$offset=0;
		$allow_player=0;  //开启人数
		$condition=array('attr.level'=>array('$gte'=>40));
		$fields = array('equip');
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $mdb->find('human_offline',$condition, $fields, $result_condition);
			foreach($list as $items){
				foreach($items['equip'] as $key=>$part_list){
					$part=$key+1; //部位
					//强化
					if(isset($part_list['strongList'])&&is_array($part_list['strongList'])){
							foreach ($part_list['strongList'] as $type=>$strong){
								//$type强化火种类型（凡火、灵火等）
								if($type==0){
									//凡火
									if($strong[0]){
										isset($array_fan[$part][$strong[0]]) ? $array_fan[$part][$strong[0]]++ : $array_fan[$part][$strong[0]]=1;
									}
								}else if($type==1){
									//灵火
									if($strong[0]){
										isset($array_ling[$part][$strong[0]]) ? $array_ling[$part][$strong[0]]++ : $array_ling[$part][$strong[0]]=1;
									}
								}else if($type==2){
									//仙火
									if($strong[0]){
										isset($array_xian[$part][$strong[0]]) ? $array_xian[$part][$strong[0]]++ : $array_xian[$part][$strong[0]]=1;
									}
								}else if($type==3){
									//神火
									if($strong[0]){
										isset($array_shen[$part][$strong[0]]) ? $array_shen[$part][$strong[0]]++ : $array_shen[$part][$strong[0]]=1;
									}
								}

							}
					}

				}
			}

			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		ksort($array_fan);
		ksort($array_ling);
		ksort($array_xian);
		ksort($array_shen);
		$str='';
		/*
		for($i=1;$i<=12;$i++){
			for($j=1;$j<=12;$j++){
				$ride=isset($array_fan[$i][$j]) ? $array_fan[$i][$j] : 0;
				$str.=$ride."\t";
			}

		}
		
		
		for($i=1;$i<=12;$i++){
			for($j=1;$j<=12;$j++){
				$ride=isset($array_ling[$i][$j]) ? $array_ling[$i][$j] : 0;
				$str.=$ride."\t";
			}

		}
		*/
		
		for($i=1;$i<=12;$i++){
			for($j=1;$j<=12;$j++){
				$ride=isset($array_xian[$i][$j]) ? $array_xian[$i][$j] : 0;
				$str.=$ride."\t";
			}

		}
		/*
		for($i=1;$i<=12;$i++){
			for($j=1;$j<=12;$j++){
				$ride=isset($array_shen[$i][$j]) ? $array_shen[$i][$j] : 0;
				$str.=$ride."\t";
			}

		}
		*/
		echo SERVER_AGENT.'_'.SERVER_ID."\t".$count_register."\t".$str."\n";
		break;
	case 'equip':
		//装备神化
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$count_register = $mdb->count('account_data', array('char_id' => array('$exists' => true)));
		$array_equip=array();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$limit=500;
		$offset=0;
		$allow_player=0;  //开启人数
		$condition=array('attr.level'=>array('$gte'=>50));
		$fields = array('equip');
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $mdb->find('human_offline',$condition, $fields, $result_condition);
			foreach($list as $items){
				$allow_player++;
				foreach($items['equip'] as $key=>$part_list){
					$part=$key+1; //部位
					//神化
					if(isset($part_list['deilyLevel'])&&!empty($part_list['deilyLevel'])){
						$deilyLevel=$part_list['deilyLevel'];
						isset($array_equip[$part][$deilyLevel]) ? $array_equip[$part][$deilyLevel]++ : $array_equip[$part][$deilyLevel]=1;
					}
				}
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		ksort($array_equip);
		for($i=1;$i<=12;$i++){
			for($j=1;$j<=12;$j++){
				$shen=isset($array_equip[$i][$j]) ? $array_equip[$i][$j] : 0;
				$str.=$shen."\t";
			}

		}

		echo SERVER_AGENT.'_'.SERVER_ID."\t".$count_register."\t".$str."\n";
		break;
	case 'gem':
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$count_register = $mdb->count('account_data', array('char_id' => array('$exists' => true)));
		//总注册玩家数
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$count_register = $mdb->count('account_data', array('char_id' => array('$exists' => true)));
		//宝石雕刻
		$array_gem=array();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$limit=500;
		$offset=0;
		$allow_player=0;  //开启人数
		$condition=array('attr.level'=>array('$gte'=>40));
		$fields = array('equip');
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $mdb->find('human_offline',$condition, $fields, $result_condition);
			foreach($list as $items){
				$allow_player++;
				foreach($items['equip'] as $key=>$part_list){
					$part=$key+1; //部位
					//宝石雕刻
					if(isset($part_list['carveLevel'])&&!empty($part_list['carveLevel'])){
						$carveLevel=$part_list['carveLevel'];
						isset($array_gem[$part][$carveLevel]) ? $array_gem[$part][$carveLevel]++ : $array_gem[$part][$carveLevel]=1;
					}
				}
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		ksort($array_gem);
		for($i=1;$i<=12;$i++){
			for($j=1;$j<=12;$j++){
				$gem=isset($array_gem[$i][$j]) ? $array_gem[$i][$j] : 0;
				$str.=$gem."\t";
			}

		}

		echo SERVER_AGENT.'_'.SERVER_ID."\t".$count_register."\t".$str."\n";
		break;
}

?>
