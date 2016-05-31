<?php
/*
 * 凌晨0点执行脚本
 * level：等级流失
 * fight：战斗力分布
 * money:货币实际剩余量
 * strong:强化分析
 * gem:宝石分析
 * god：神化分析
 * pet:宠物（pet）分析
 * pet_equip:装备升级
 * pet_card:宠物天关
 * equip_strong:装备详细强化分析
 * check_point_zzsf：征战天下下
 * wuhun:武魂
 * ride_jl:坐骑精炼
 * home:家园
 * offline_arena_rank:武斗场排行
 * php stat_day_ended.php --task=level --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('today')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'level':
		//等级流失 连续3天未登录判定为流失(为了保证数据准确性，凌晨12点执行)
		$mdb=new Mdb();
		$data=array();
		$condition=array('loginTime'=>array('$lt'=>strtotime('today')-86400*3));
		$group=array('level');
		for ($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			//创号未进入游戏视为0级玩家
			$list=$mdb->count('characters', array('loginTime'=>array('$exists'=>false)), $group);
			foreach ($list as $row){
				if(empty($data[0]['count'])){
					$data[0]['count']=$row['value']['count'];
					$data[0]['loss_count']=$row['value']['count'];
				}else{
					$data[0]['count']+=$row['value']['count'];
					$data[0]['loss_count']+=$row['value']['count'];
				}
			}

			$list=$mdb->count('characters', array('loginTime'=>array('$exists'=>true)), $group);
			foreach ($list as $row){
				if(empty($data[$row['_id']]['count']))
					$data[$row['_id']]['count']=$row['value']['count'];
				else
					$data[$row['_id']]['count']+=$row['value']['count'];
			}

			$list=$mdb->count('characters', $condition, $group);
			foreach ($list as $row){
				if(empty($data[$row['_id']]['loss_count']))
					$data[$row['_id']]['loss_count']=$row['value']['count'];
				else
					$data[$row['_id']]['loss_count']+=$row['value']['count'];
			}
		}

		foreach ($data as $level=>$item){
			$item['date']=date('Ymd',strtotime('yesterday'));
			$item['time']=time();
			$item['level']=$level;
			$fields=implode(',', array_keys($item));
			$values=implode(',', array_values($item));
			$sql="insert into stat_level_loss ($fields) values ($values)";
			$mysqli->query($sql);
		}
		break;

	case 'fight':
		//战斗力分布
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$min_level=30;
		//从排行榜获取最高等级
		$list=$mdb->max('human_rank', array('lvl'), array('lvl'=>array('$gte'=>$min_level)));
		if(empty($list[0]['value']['lvl_max']))	exit('No data');
		$max_level=intval($list[0]['value']['lvl_max']);

		//RMB玩家
		$sql='select distinct char_id from pay_order';
		$result=$mysqli->query($sql);
		$pay_char=array();//充值玩家
		while ($result && $row=$result->fetch_assoc()){
			$pay_char[]=floatval($row['char_id']);
		}

		//流失天数配置
		$loss_day_conf=array(0,2,3,7);
		//战斗力分布配置
		$fight_conf=array(
			array(1,1530),
			array(1531,2070),
			array(2071,2380),
			array(2381,3220),
			array(3221,3400),
			array(3401,4600),
			array(4601,5950),
			array(5951,8050),
			array(8051,8500),
			array(8501,11500),
		);
		$date=date('Y-m-d',strtotime('yesterday'));
		$time=time();
		$sql_initial='insert into stat_fight (date,level,type,day,count,fight_sum,fight_max,remark,time) value ';
		for ($level=$min_level;$level<=$max_level;$level++){
			//此等级的流失玩家
			foreach ($loss_day_conf as $day){
				$loss_char=array();//流失玩家
				if($day>0){
					for ($i=0;$i<4;$i++){
						$mdb->selectDb(MONGO_PERFIX.$i);
						$condition=array('level'=>$level,'loginTime'=>array('$lt'=>strtotime('today')-86400*$day));
						$list=$mdb->find('characters', $condition, array('_id'));
						foreach ($list as $row){
							$loss_char[]=$row['_id'];
						}
					}
				}

				$mdb->selectDb(MONGO_PERFIX.'5');
				//RMB玩家且排除N天未登录
				$char_data=array_diff($pay_char,$loss_char);
				if($char_data){
					$condition=array('attr.level'=>$level,'_id'=>array('$in'=>$char_data));
					$list=$mdb->find('human_offline', $condition, array('attr.fight','_id'=>false));
					$count=$fight_sum=$fight_max=0;
					$remark=array();
					foreach ($list as $row){
						$count++;
						$fight=empty($row['attr']['fight']) ? 0 : $row['attr']['fight'];
						$fight_sum+=$fight;
						$fight_max=$fight>$fight_max ? $fight : $fight_max;
						//战斗力范畴统计
						foreach ($fight_conf as $item){
							if($fight>=$item[0] && $fight<=$item[1]){
								$key=$item[0].'-'.$item[1];
								isset($remark[$key]) ? $remark[$key]++ : $remark[$key]=1;
								break;
							}
						}
					}
					$remark=$remark ? json_encode($remark) : '';
					$sql=$sql_initial." ('$date',$level,1,$day,$count,$fight_sum,$fight_max,'$remark',$time)";
					$mysqli->query($sql);
				}

				//非RMB玩家且排除N天未登录
				$condition=array('attr.level'=>$level);
				$char_data=array_unique(array_merge($pay_char,$loss_char));
				$char_data ? $condition['_id']=array('$nin'=>$char_data) : '';
				$list=$mdb->find('human_offline', $condition, array('attr.fight','_id'=>false));
				$count=$fight_sum=$fight_max=0;
				$remark=array();
				foreach ($list as $row){
					$count++;
					$fight=empty($row['attr']['fight']) ? 0 : $row['attr']['fight'];
					$fight_sum+=$fight;
					$fight_max=$fight>$fight_max ? $fight : $fight_max;
					//战斗力范畴统计
					foreach ($fight_conf as $item){
						if($fight>=$item[0] && $fight<=$item[1]){
							$key=$item[0].'-'.$item[1];
							isset($remark[$key]) ? $remark[$key]++ : $remark[$key]=1;
							break;
						}
					}
				}
				$remark=$remark ? json_encode($remark) : '';
				$sql=$sql_initial." ('$date',$level,2,$day,$count,$fight_sum,$fight_max,'$remark',$time)";
				$mysqli->query($sql);
			}
		}
		break;

	case 'money':
		//货币实际剩余量
		$mdb=new Mdb();
		$data=$not_loss_data=array();
		$today=strtotime('today');
		$opne_time=strtotime(date('Ymd',SERVER_OPEN_TIME));
		$opne_gt_3day=($today-$opne_time)/86400>3 ? true : false;//开服是否大于3天
		//遍库统计
		for ($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$not_loss_character=array();//非流失玩家 3天内有登陆
			if($opne_gt_3day){
				$limit=3000;
				$offset=0;
				while ($limit>0){
					$result_condition=array('start'=>$offset,'limit'=>$limit);
					$list=$mdb->find('characters', array('loginTime'=>array('$gte'=>time()-86400*3)), array('_id'), $result_condition);
					foreach ($list as $row){
						$not_loss_character[]=$row['_id'];
					}
					if(count($list)<$limit){
						$limit=$offset=0;
						break;
					}
					$offset+=$limit;
				}
			}

			$limit=3000;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('character_bag', array(), array('_id','moneyList'), $result_condition);
				foreach ($list as $row){
					foreach ($row['moneyList'] as $key=>$count){
						isset($data[$key+1]) ? $data[$key+1]+=$count : $data[$key+1]=$count;
						//非流失玩家货币存量
						if(in_array($row['_id'],$not_loss_character)){
							isset($not_loss_data[$key+1]) ? $not_loss_data[$key+1]+=$count : $not_loss_data[$key+1]=$count;
						}
					}
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
		}

		//入库
		if($data){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			foreach ($data as $money_type=>$money_num){
				if($money_num){
					$not_loss_money_num=isset($not_loss_data[$money_type]) ? $not_loss_data[$money_type] : 0;
					//开服三天之内都为非流失玩家
					$not_loss_money_num=$opne_gt_3day ? $not_loss_money_num : $money_num;
					$sql="insert into stat_money_real (date,money_type,money_num,not_loss_money_num,time) value ('$date',$money_type,$money_num,$not_loss_money_num,$time)";
					$mysqli->query($sql);
				}
			}
		}
		break;

	case 'strong':
		//强化部位分析
		include __CONFIG__.'game_config.php';
		$mdb=new Mdb();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=500;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('character_item_2', array(), array('part'), $result_condition);
				foreach ($list as $row){
					foreach ($row['part'] as $part=>$part_list){
						if(isset($part_list['strongList'])&&is_array($part_list['strongList'])){
							foreach ($part_list['strongList'] as $type=>$strong){
								//$type强化火种类型（凡火、灵火等）
								if($strong[0]){
									isset($data[$type][$strong[0]]) ? $data[$type][$strong[0]]++ : $data[$type][$strong[0]]=1;
								}
							}
						}
					}
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
		}

		$sql_field='';//mysql字段赋值
		foreach ($data as $type=>$items){
			//入库前数据处理
			ksort($items);
			$field='strong'.($type+1);
			$field_remark=$field.'_remark';
			$sql_field.=",$field=".array_sum($items);
			$sql_field.=sprintf(",$field_remark='%s'",json_encode($items));
		}
		if($sql_field){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			$sql="insert into stat_strong set date='$date',time=$time $sql_field";
			$mysqli->query($sql);
		}
		break;

	case 'pet':
		//宠物分析-境界分析
		include __CONFIG__.'game_config.php';
		$mdb=new Mdb();
		$data=array();
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
						isset($data[$type][$level]) ? $data[$type][$level]++ : $data[$type][$level]=1;
					}
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
		}

		$sql_field='';//mysql字段赋值
		foreach ($data as $type=>$items){
			//入库前数据处理
			ksort($items);
			$field='realm'.$type;
			$field_remark=$field.'_remark';
			$sql_field.=",$field=".array_sum($items);
			$sql_field.=sprintf(",$field_remark='%s'",json_encode($items));
		}
		if($sql_field){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			$sql="insert into stat_pet_realm set date='$date',time=$time $sql_field";
			$mysqli->query($sql);
		}

		//宠物分析-灵石分析
		$data=array();
		$pullulate_range=array(
			array(26,30),
			array(21,25),
			array(16,20),
			array(11,15),
			array(6,10),
			array(1,5),
		);

		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=2000;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('pets', array(), array('petList'), $result_condition);
				foreach ($list as $row){
					foreach ($row['petList'] as $pet_list){
						foreach ($pet_list['pullulateList'] as $type=>$pullulate){
							$data[$type.'_max']=!isset($data[$type.'_max']) || $pullulate>$data[$type.'_max'] ? $pullulate : $data[$type.'_max'];
							foreach ($pullulate_range as $arr){
								if($pullulate>$arr[0]){
									$key=$arr[0].'-'.$arr[1];
									isset($data[$type.'_remark'][$key]) ? $data[$type.'_remark'][$key]++ : $data[$type.'_remark'][$key]=1;
									break;
								}
							}
						}
					}
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
		}

		if($data){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			ksort($data['attack_remark']);
			ksort($data['hp_remark']);
			ksort($data['defense_remark']);
			$attack_remark=json_encode($data['attack_remark']);
			$hp_remark=json_encode($data['hp_remark']);
			$defense_remark=json_encode($data['defense_remark']);
			$sql="insert into stat_pet_pullulate set date='$date',attack_max={$data['attack_max']},hp_max='{$data['hp_max']}',defense_max='{$data['defense_max']}',
				attack_remark='$attack_remark',hp_remark='$hp_remark',defense_remark='$defense_remark',time=$time";
			$mysqli->query($sql);
		}
		break;

	case 'god':
		//神化分析
		include __CONFIG__.'game_config.php';
		$mdb=new Mdb();
		$data=array();

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
						isset($data['shenhua'][$part][$deilyLevel]) ? $data['shenhua'][$part][$deilyLevel]++ : $data['shenhua'][$part][$deilyLevel]=1;
					}
				}
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		if($data){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			ksort($data['shenhua']);
			$array_shenhua=array();
			foreach($data['shenhua'] as $part=>$items){
				ksort($items);
				$array_shenhua[$part]=$items;
			}
			$shenhua_remark=json_encode($array_shenhua);
			$shenhua_total=0;
			foreach($array_shenhua as $part=>$items){
				$shenhua_total+=array_sum($items);
			}

			$sql="insert into stat_god set date='$date'," .
					"shenhua_total=$shenhua_total," .
					"shenhua_remark='$shenhua_remark',allow_player=$allow_player,time=$time";
			$mysqli->query($sql);
		}

		break;

	case 'gem':
		//宝石分析
		include __CONFIG__.'game_config.php';
		$mdb=new Mdb();
		$data=array();

		$array_equip=array();
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
					//宝石充能
					if(isset($part_list['gemList'])&&is_array($part_list['gemList'])){
						foreach ($part_list['gemList'][0] as $type=>$gem){
							if($type==2){ //
								//$type  宝石充能 1,2...
								if($gem[0]){
									isset($data['gem_chongneng'][$part][$gem[0]]) ? $data['gem_chongneng'][$part][$gem[0]]++ : $data['gem_chongneng'][$part][$gem[0]]=1;
								}
								//$type  宝石神炼 1,2...
								if($gem[1]){
									isset($data['gem_shenlian'][$part][$gem[1]]) ? $data['gem_shenlian'][$part][$gem[1]]++ : $data['gem_shenlian'][$part][$gem[1]]=1;
								}
							}

						}
					}
					//宝石雕刻
					if(isset($part_list['carveLevel'])&&!empty($part_list['carveLevel'])){
						$carveLevel=$part_list['carveLevel'];
						isset($data['gem_diaoke'][$part][$carveLevel]) ? $data['gem_diaoke'][$part][$carveLevel]++ : $data['gem_diaoke'][$part][$carveLevel]=1;
					}
				}
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}
		if($data){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			ksort($data['gem_chongneng']);
			ksort($data['gem_shenlian']);
			ksort($data['gem_diaoke']);
			$array_chongneng=$array_shenlian=$array_diaoke=array();
			foreach($data['gem_chongneng'] as $part=>$items){
				ksort($items);
				$array_chongneng[$part]=$items;
			}
			foreach($data['gem_shenlian'] as $part=>$items){
				ksort($items);
				$array_shenlian[$part]=$items;
			}
			foreach($data['gem_diaoke'] as $part=>$items){
				ksort($items);
				$array_diaoke[$part]=$items;
			}
			$gem_chongneng_remark=json_encode($array_chongneng);
			$gem_shenlian_remark=json_encode($array_shenlian);
			$gem_diaoke_remark=json_encode($array_diaoke);
			$gem_chongneng_count=$gem_shenlian_count=$gem_diaoke_count=0;
			foreach($array_chongneng as $items){
				$gem_chongneng_count+=array_sum($items);
			}
			foreach($data['gem_shenlian'] as $items){
				$gem_shenlian_count+=array_sum($items);
			}
			foreach($data['gem_diaoke'] as $items){
				$gem_diaoke_count+=array_sum($items);
			}

			$sql="insert into stat_gem set date='$date'," .
					"gem_chongneng_count=$gem_chongneng_count," .
					"gem_shenlian_count=$gem_shenlian_count," .
					"gem_diaoke_count=$gem_diaoke_count," .
					"gem_chongneng_remark='$gem_chongneng_remark',gem_shenlian_remark='$gem_shenlian_remark',gem_diaoke_remark='$gem_diaoke_remark',allow_player=$allow_player,time=$time";
			$mysqli->query($sql);
		}
		break;
	case 'pet_equip':
		//宠物装备升级
		$data=array();
		$date=date('Y-m-d',strtotime('yesterday'));
		$time=strtotime('yesterday');
		$count=0;
		$sql="select * from log_money where type=135 and money_type=3 and time>=$time and time<($time+86400)";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$char_id=$row['char_id'];
			$data[$char_id]=isset($data[$char_id]) ? $data[$char_id]++ : $data[$char_id]=1;//人数
			++$count;//总次数
		}
		$player_count=count($data);
		$avg_count=$player_count ? intval($count/$player_count) : 0;//人均次数
		$time=time();
		$sql="replace stat_pet_equip set date='$date',player_count='$player_count',avg_count='$avg_count',count='$count',time=$time";
		$mysqli->query($sql);
		break;
	case 'pet_card':
		//宠物天关分析
		$data=$max=array();
		$date=date('Y-m-d',strtotime('yesterday'));
		$time=strtotime('yesterday');
		$sql="select count(distinct char_id) as allow_player from log_login where login_time>=$time and login_time<($time+86400) and level>=45 ";
		$list=$mysqli->findOne($sql);
		$allow_player=$list ? $list['allow_player'] : 0;   //满足人数

		//闯关人数
		$sql="select count(distinct char_id) as count from log_copy_enter where entry_id=332100 and time>=$time and time<($time+86400)";
		$list=$mysqli->findOne($sql);
		$count_player=$list ? $list['count'] : 0;

		$count=0;
		$sql="select * from log_pass_card where time>=$time and time<($time+86400)";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$card_id=intval($row['title']);
			//关卡数
			if($card_id>=3000){
				$count+=$card_id%3000;
			}else if($card_id>=2000 && $card_id<3000){
				$count+=$card_id%2000;
			}else{
				$count+=$card_id%1000;
			}
			//最高关卡名称
			$max[$card_id]=(empty($max[$card_id]) || $card_id>=$max[$card_id]) ? $card_id : $max[$card_id];
		}
		$name_id=max($max);
		$time=time();
		$sql="replace stat_pet_card set date='$date',count_player='$count_player',allow_player='$allow_player',count='$count',name_id='$name_id',time=$time";
		$mysqli->query($sql);
		break;

		case 'equip_strong':
		//装备强化详细分析
		$mdb=new Mdb();
		$data=array();

		//当日登录的≥45级上玩家
		$time=strtotime('yesterday');
		$sql="select count(distinct char_id) as allow_player from log_login where login_time>=$time and login_time<($time+86400) and level>=45 ";
		$list=$mysqli->findOne($sql);
		$allow_player=$list ? $list['allow_player'] : 0;   //满足人数

		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=500;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('pet_equipment_bag', array(), array('itemList'), $result_condition);
				if(!empty($list)){

					foreach($list as $items){
						if(!empty($items['itemList'])){
							foreach($items['itemList'] as $key=>$row)
							$part=$key+1; //部位
							$rank=isset($row[3]['rank']) ? $row[3]['rank'] : '';//品阶
							$level=isset($row[3]['level']) ? $row[3]['level'] : '';//等级
							if($part<=6){
								if($level<=10){
									isset($data['level'][0][$part][$level]) ? $data['level'][0][$part][$level]++ : $data['level'][0][$part][$level]=1;
								}else if($level>10 && $level<=20){
									isset($data['level'][1][$part][$level]) ? $data['level'][1][$part][$level]++ : $data['level'][1][$part][$level]=1;
								}else if($level>20 && $level<=30){
									isset($data['level'][2][$part][$level]) ? $data['level'][2][$part][$level]++ : $data['level'][2][$part][$level]=1;
								}else if($level>30 && $level<=40){
									isset($data['level'][3][$part][$level]) ? $data['level'][3][$part][$level]++ : $data['level'][3][$part][$level]=1;
								}else if($level>40 && $level<=50){
									isset($data['level'][4][$part][$level]) ? $data['level'][4][$part][$level]++ : $data['level'][4][$part][$level]=1;
								}else if($level>50 && $level<=60){
									isset($data['level'][5][$part][$level]) ? $data['level'][5][$part][$level]++ : $data['level'][5][$part][$level]=1;
								}else if($level>60){
									isset($data['level'][6][$part][$level]) ? $data['level'][6][$part][$level]++ : $data['level'][6][$part][$level]=1;
								}

								if($rank<=10){
									isset($data['fan'][0][$part][$rank]) ? $data['fan'][0][$part][$rank]++ : $data['fan'][0][$part][$rank]=1;
								}else if($rank>10 && $rank<=20){
									isset($data['ling'][1][$part][$rank]) ? $data['ling'][1][$part][$rank]++ : $data['ling'][1][$part][$rank]=1;
								}else if($rank>20 && $rank<=30){
									isset($data['xian'][2][$part][$rank]) ? $data['xian'][2][$part][$rank]++ : $data['xian'][2][$part][$rank]=1;
								}else if($rank>30 && $rank<=40){
									isset($data['shen'][3][$part][$rank]) ? $data['shen'][3][$part][$rank]++ : $data['shen'][3][$part][$rank]=1;
								}
							}


						}

					}
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
		}
		if($data){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			$array_fan=$array_ling=$array_xian=$array_shen=$array_level=array();
			$strong_fan_count=$strong_ling_count=$strong_xian_count=$strong_shen_count=$strong_level_count=0;
			if(isset($data['fan'])){
				ksort($data['fan']);
				foreach($data['fan'] as $type=>$items){
					ksort($items);
					$array_fan=$items;
					foreach($items as $part=>$item){
						$strong_fan_count+=array_sum($item);
					}
				}
			}
			if(isset($data['ling'])){
				ksort($data['ling']);
				foreach($data['ling']  as $type=>$items){
					ksort($items);
					$array_ling=$items;
					foreach($items as $part=>$item){
						$strong_ling_count+=array_sum($item);
					}
				}
			}
			if(isset($data['xian'])){
				ksort($data['xian']);
				foreach($data['xian'] as $type=>$items){
					ksort($items);
					$array_xian=$items;
					foreach($items as $part=>$item){
						$strong_xian_count+=array_sum($item);
					}


				}
			}
			if(isset($data['shen'])){
				ksort($data['shen']);
				foreach($data['shen'] as $key=>$items){
					ksort($items);
					$array_shen=$items;
					foreach($items as $part=>$item){
						$strong_shen_count+=array_sum($item);
					}
				}
			}
			if(isset($data['level'])){
				ksort($data['level']);
				foreach($data['level'] as $key=>$items){
					ksort($items);
					$array_level[$key+1]=$items;
					foreach($items as $part=>$item){
						$strong_level_count+=array_sum($item);
					}
				}
			}
			$strong_fan_remark=json_encode($array_fan);
			$strong_ling_remark=json_encode($array_ling);
			$strong_xian_remark=json_encode($array_xian);
			$strong_shen_remark=json_encode($array_shen);
			$strong_level_remark=json_encode($array_level);

			$sql="replace stat_equip_strong set date='$date'," .
					"strong_fan_count=$strong_fan_count," .
					"strong_ling_count=$strong_ling_count," .
					"stong_xian_count=$strong_xian_count," .
					"stong_shen_count=$strong_shen_count," .
					"strong_fan_remark='$strong_fan_remark',strong_ling_remark='$strong_ling_remark',strong_xian_remark='$strong_xian_remark',strong_shen_remark='$strong_shen_remark',strong_level_remark='$strong_level_remark',allow_player=$allow_player,time=$time";
			$mysqli->query($sql);
		}
		break;
	case 'check_point_zzsf':
		//征战天下
		$mdb=new Mdb();
		$allow_join=$join_count=0;
		$city_count=array();
		$chapter_count=array();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			//满足人数：截止当前日期位置，本服满足37级的玩家人数
			$allow_join+=$mdb->count('characters', array('level'=>array('$gte'=>37)));
			$list=$mdb->find('check_point_zzsf', array('citys'=>array('$ne'=>array())), array('citys'));
			foreach ($list as $row){
				$join_count++;
				foreach ($row['citys'] as $city_id=>$item){
					isset($city_count[$city_id]) ? $city_count[$city_id]++ : $city_count[$city_id]=1;
					isset($chapter_count[$city_id][$item['progress']]) ? $chapter_count[$city_id][$item['progress']]++ : $chapter_count[$city_id][$item['progress']]=1;
				}
			}
		}

		$date=date('Y-m-d',strtotime('yesterday'));
		$city_count=json_encode($city_count);
		$chapter_count=json_encode($chapter_count);
		$time=time();
		$sql="insert into stat_check_point_zzsf (date,allow_join,join_count,city_count,chapter_count,time) value ('$date',$allow_join,$join_count,'$city_count','$chapter_count',$time)";
		$mysqli->query($sql);
		break;
	case 'wuhun':
		//武魂
		$mdb=new Mdb();
		$total_count=0;//武魂总数
		$total_list=$level_list=$offering_level_list=array();
		$time=time();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=800;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('wardrobe', array('moduleModelList.2'=>array('$ne'=>array())), array('moduleModelList','offeringLvl'),$result_condition);
				foreach ($list as $row){
					if(isset($row['moduleModelList'][2])&&is_array($row['moduleModelList'][2])){
						foreach($row['moduleModelList'][2] as $item){
							$total_count++;
							$item_id=(string)$item[0];//武魂道具id
							$level=intval($item[6]['level']);
							if($item[6]['validTime']>$time){
								isset($total_list[$level]) ? $total_list[$level]++ : $total_list[$level]=1;
								isset($level_list[$item_id][$level]) ? $level_list[$item_id][$level]++ : $level_list[$item_id][$level]=1;
							}
						}
					}
					isset($offering_level_list[$row['offeringLvl']]) ? $offering_level_list[$row['offeringLvl']]++ : $offering_level_list[$row['offeringLvl']]=1;
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
			}
		}
		$date=date('Y-m-d',strtotime('yesterday'));
		$total_list=json_encode($total_list);
		$level_list=json_encode($level_list);
		$offering_level_list=json_encode($offering_level_list);
		$sql="insert into stat_wuhun (date,total_count,total_list,level_list,offering_level_list,time) value ('$date',$total_count,'$total_list','$level_list','$offering_level_list',$time)";
		$mysqli->query($sql);
		break;
	case 'ride_jl':
		//坐骑精炼
		$mdb=new Mdb();
		$data=$data_fan=$data_ling=$data_xian=$data_shen=array();
		$allow_player=$join_count=0;
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			//满足人数
			$allow_player+=$mdb->count('characters',array('level'=>array('$gte'=>45)));
			$limit=500;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('character_item_4', array(), array('part'), $result_condition);
				if(!empty($list)){
					foreach($list as $items){
						if(!empty($items['part'])){
							foreach($items['part'] as $key=>$row){
								$part=$key+1; //部位
								$rank=!empty($row['rank']) ? $row['rank'] : 0;//品阶
								if($rank>0) $join_count++;  //参与人数
								if($part<=4){  //目前只有四个部位
									if($rank>0 && $rank<=10){
										isset($data_fan[$part][$rank]) ? $data_fan[$part][$rank]++ : $data_fan[$part][$rank]=1;
									}else if($rank>10 && $rank<=20){
										isset($data_ling[$part][$rank]) ? $data_ling[$part][$rank]++ : $data_ling[$part][$rank]=1;
									}else if($rank>20 && $rank<=30){
										isset($data_xian[$part][$rank]) ? $data_xian[$part][$rank]++ : $data_xian[$part][$rank]=1;
									}else if($rank>30 && $rank<=40){
										isset($data_shen[$part][$rank]) ? $data_shen[$part][$rank]++ : $data_shen[$part][$rank]=1;
									}
								}

							}
						}
					}
				}
				if(count($list)<$limit){
					$limit=$offset=0;
					break;
				}
				$offset+=$limit;
		    }
		}

		if($data_fan || $data_ling || $data_xian || $data_shen){
			$date=date('Y-m-d',strtotime('yesterday'));
			$time=time();
			ksort($data_fan);
			ksort($data_ling);
			ksort($data_xian);
			ksort($data_shen);
			$array_fan=$array_ling=$array_xian=$array_shen=array();
			foreach($data_fan as $part=>$items){
				ksort($items);
				$array_fan[$part]=$items;
			}
			foreach($data_ling as $part=>$items){
				ksort($items);
				$array_ling[$part]=$items;
			}
			foreach($data_xian as $part=>$items){
				ksort($items);
				$array_xian[$part]=$items;
			}
			foreach($data_shen as $part=>$items){
				ksort($items);
				$array_shen[$part]=$items;

			}
			$ride_fan_remark=json_encode($array_fan);
			$ride_ling_remark=json_encode($array_ling);
			$ride_xian_remark=json_encode($array_xian);
			$ride_shen_remark=json_encode($array_shen);

			$sql="insert into stat_ride_jl set date='$date'," .
					"ride_fan_remark='$ride_fan_remark'," .
					"ride_ling_remark='$ride_ling_remark'," .
					"ride_xian_remark='$ride_xian_remark'," .
					"ride_shen_remark='$ride_shen_remark'," .
					"allow_player=$allow_player," .
					"join_count=$join_count," .
					"time=$time";
			$mysqli->query($sql);
		}
		break;
	case 'home':
		//家园
		$fine_conf=array(
			array(2560,9999),
			array(1280,2560),
			array(640,1280),
			array(320,640),
			array(140,320),
			array(60,140),
			array(0,60),
		);
		
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$offset=0;
		$limit=500;
		$buy_level=$access_count=$call_peri=$make_furniture=$day_make_furniture=$skill_count=$slavey_level=$fine=array();
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list=$mdb->find('homeland', array(), array('buyLv','accessSumCount','themaid','allFine'), $result_condition);
			foreach ($list as $row){
				$level=$row['buyLv'];
				isset($buy_level[$level]) ? $buy_level[$level]++ : $buy_level[$level]=1;
				isset($access_count[$level]) ? $access_count[$level]+=$row['accessSumCount'] : $access_count[$level]=$row['accessSumCount'];
				//丫鬟传艺(技能)
				if(!empty($row['themaid']['skills'])){
					foreach($row['themaid']['skills'] as $skill_id=>$skills){
						$skill_level=$skills['level'];
						isset($skill_count[$skill_id][$skill_level]) ? $skill_count[$skill_id][$skill_level]++ : $skill_count[$skill_id][$skill_level]=1;
					}
				}
				//丫鬟等级
				isset($slavey_level[$row['themaid']['lv']]) ? $slavey_level[$row['themaid']['lv']]++ : $slavey_level[$row['themaid']['lv']]=1;
				
				//舒适度
				if(isset($row['allFine'])&&$row['allFine']){
					foreach ($fine_conf as $key=>$item){
						if($row['allFine']>=$item[0] && $row['allFine']<$item[1]){
							isset($fine[$level][$key]) ? $fine[$level][$key]++ : $fine[$level][$key]=1;
							break;
						}
					}
				}
			}
			if(count($list)<$limit){
				$limit=0;
				break;
			}
			$offset+=$limit;
		}
		$yesterday=strtotime('yesterday');
		foreach ($buy_level as $home_type=>$value){
			//仙女开启次数
			$sql="select count(*) as count from log_call_peri where home_type=$home_type";
			$count=$mysqli->count($sql);
			isset($call_peri[$home_type]) ? $call_peri[$home_type]+=$count : $call_peri[$home_type]=$count;
			//制造家具总数
			$sql="select count(*) as count from log_make_furniture where home_type=$home_type and result=1";
			$count=$mysqli->count($sql);
			isset($make_furniture[$home_type]) ? $make_furniture[$home_type]+=$count : $make_furniture[$home_type]=$count;
			//当天制造家具人数、数量
			$sql="select count(distinct char_id) as player,count(*) as count from log_make_furniture where home_type=$home_type and result=1 and time>=$yesterday and time<($yesterday+86400)";
			$list=$mysqli->findOne($sql);
			isset($day_make_furniture[$home_type]['player']) ? $day_make_furniture[$home_type]['player']+=$list['player'] : $day_make_furniture[$home_type]['player']=$list['player'];
			isset($day_make_furniture[$home_type]['count']) ? $day_make_furniture[$home_type]['count']+=$list['count'] : $day_make_furniture[$home_type]['count']=$list['count'];
		}
		$date=date('Y-m-d',$yesterday);
		$buy_level=json_encode($buy_level);
		$access_count=json_encode($access_count);
		$call_peri=json_encode($call_peri);
		$make_furniture=json_encode($make_furniture);
		$day_make_furniture=json_encode($day_make_furniture);
		$skill_count=json_encode($skill_count);
		$slavey_level=json_encode($slavey_level);
		$fine=json_encode($fine);
		$time=time();
		$sql="insert into stat_home (date,buy_level,access_count,call_peri,make_furniture,day_make_furniture,skill_count,slavey_level,fine,time) value ('$date','$buy_level','$access_count','$call_peri','$make_furniture','$day_make_furniture','$skill_count','$slavey_level','$fine','$time')";
		$mysqli->query($sql);
		break;
	case 'offline_arena_rank':
		//武斗场
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$list=$mdb->find('offline_arena', array(), array('index'), array('start'=>0,'limit'=>10,'sort'=>array('index'=>1)));
		foreach ($list as $row){
			$human_offline=$mdb->findOne('human_offline', array('_id'=>$row['_id']), array('attr.name','attr.fight','attr.faction'));
			$row['name']=empty($human_offline['attr']['name']) ? '' : $human_offline['attr']['name'];
			$row['fight']=empty($human_offline['attr']['fight']) ? '' : $human_offline['attr']['fight'];
			$row['faction']=empty($human_offline['attr']['faction']) ? '' : $human_offline['attr']['faction'];
			$data[]=$row;
		}
		$date=date('Y-m-d',strtotime('yesterday'));
		$data=addslashes(json_encode($data));
		$time=time();
		$sql="insert into stat_offline_arena_rank (date,data,time) value ('$date','$data',$time)";
		$mysqli->query($sql);
		break;
}