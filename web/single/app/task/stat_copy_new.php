<?php
/*
 * 副本统计
 * 注：策划规定的entry_id范围如下
 * 乾坤八卦:304000
 * 兵临城下:600100-600200
 * 草船借箭:700100-700200
 * 洛阳攻防战：900300
 * 十面埋伏：500100
 * 蜀道试炼：560100-560200
 * 凤雏秘境:510100
 * 海底总动员	活动	开启等级38	entry_id=630100	Thời gian:15:00-15:30
 * 七擒孟获	活动	开启等级40	entry_id=205500	Thời gian:15:00-15:30
 * php stat_copy_new.php --task=copy
 * php stat_copy_new.php --task=copy  --start_date=2014-02-20 --end_date=2014-02-27
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'StatBoss.class.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();

switch ($params['task']){
	case 'copy':
		//副本类
		include __CONFIG__.'copy_config.php';
		if(empty($copy_config)) exit('Config error');
		foreach ($copy_config as $type=>$items){
			switch ($type){
				case 'copy':
					//副本类
					foreach ($items as $item){
						//进入玩家数和开启次数
						$stat_table=array('name'=>'stat_boss','field'=>'date','where'=>"type='{$item['type']}' and name='{$item['name']}' ");
						$log_table=array('name'=>'log_copy_enter','field'=>'time','where'=>"entry_id>={$item['start_entry_id']} and entry_id<={$item['end_entry_id']}");
						$min_date=$task->getStartDate($start_date, $stat_table,$log_table);
						$start_time=strtotime($min_date);
						$end_time=strtotime($end_date);
						for($i=$start_time;$i<=$end_time;$i+=86400){
							$date=date('Y-m-d',$i);  //深海总动员需要
							//完成次数、人数
							$sql="select count(*) as count,count(distinct char_id) as player from log_copy_enter where " .
									" time>=$i and time<($i+86400) and entry_id>={$item['start_entry_id']} and entry_id<={$item['end_entry_id']} and level>={$item['level_start']} and level<={$item['level_end']}";
							$list=$mysqli->findOne($sql);
							$count=$list ? $list['count'] : 0;
							$player=$list ? $list['player'] : 0;
							if(900300==$item['start_entry_id'] && $item['end_entry_id']==900300){
								//当日满足人数为当天登陆的玩家大于等于开启等级的人数且已经有帮派的玩家
								$sql="select distinct char_id as allow_player from log_login where login_time>=$i and login_time<($i+86400) and level>={$item['level_start']} and level<={$item['level_end']} ";
								$result=$mysqli->query($sql);
								$allow_char=$data_allow_player=array();
								$mdb=new Mdb();
								$count_allow_player=0;
								while($result && $row=$result->fetch_assoc()){
									$char_id=floatval($row['allow_player']);
									$mdb->selectDb(MONGO_PERFIX.'5');
									$list=$mdb->findOne('human_offline', array('_id'=>$char_id), array('attr'));
									if(!empty($list['attr']['faction'])){
										$count_allow_player++;
									}
								}
								$allow_player=$count_allow_player;  //满足人数
							}else if((630100==$item['start_entry_id'] && $item['end_entry_id']==630100) || (205500==$item['start_entry_id'] && $item['end_entry_id']==205500) || (330300==$item['start_entry_id'] && $item['end_entry_id']==330300)){
								//深海总动员 和 七擒孟获  群英会
								//活动开启时，全服在线的，等级大于等于活动开启等级的玩家个数
								$sql="select allow_player from stat_activity where date='$date' and name='{$item['name']}'";
								$list=$mysqli->findOne($sql);
								$allow_player=$list ? $list['allow_player'] : 0;

							}else{
								//当日满足人数：当天登录的玩家，注册时间早于Thời gian，大于等于开启等级的人数
								$sql="select count(distinct char_id) as allow_player from log_login where login_time>=$i and login_time<($i+86400) and level>={$item['level_start']} and level<={$item['level_end']} ";
								$list=$mysqli->findOne($sql);
								$allow_player=$list ? $list['allow_player'] : 0;   //满足人数

							}

							$remark=array();
							$where_entry=" time>=$i and time<($i+86400) and entry_id>={$item['start_entry_id']} and entry_id<={$item['end_entry_id']} and level>={$item['level_start']} and level<={$item['level_end']} ";
							//remark 是一些各各副本boss的特别获取
							if(304000==$item['start_entry_id'] && ($item['end_entry_id']==304000 || $item['end_entry_id']==305000)){
								//乾坤八卦 返回数组
								$remark=boss_304000($where_entry,$item['level_start'],$item['level_end'],$mysqli,$item['type'],$count);
							}else if(305000==$item['start_entry_id'] && $item['end_entry_id']==305000){
								//乾坤八卦 返回数组
								$remark=boss_304000($where_entry,$item['level_start'],$item['level_end'],$mysqli,$item['type'],$count);
							}else if(600100==$item['start_entry_id'] && $item['end_entry_id']==600100){
								//兵临城下 返回数组普通
								$remark=boss_600100($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$item['type'],$player);
							}else if(600200==$item['start_entry_id'] && $item['end_entry_id']==600200){
								//兵临城下 返回数组精英
								$remark=boss_600200($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$item['type'],$player);
							}else if(700100==$item['start_entry_id'] && $item['end_entry_id']==700100){
								//草船借箭 返回数组普通
								$remark=boss_700100($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$item['type'],$player);
							}else if(700200==$item['start_entry_id'] && $item['end_entry_id']==700200){
								//草船借箭 返回数组精英
								$remark=boss_700200($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$item['type'],$player);
							}else if(500100==$item['start_entry_id'] && $item['end_entry_id']==500100){
								//十面埋伏 返回数组
								$remark=boss_500100($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli);
							}else if(800100==$item['start_entry_id'] && $item['end_entry_id']==822000){
								//过关斩将 返回数组
								$remark=boss_800100($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$copy_id_config,$copy_id_jy_config,$copy_id_jy_2_config);
							}else if(560100==$item['start_entry_id'] && $item['end_entry_id']==560100){
								//蜀道试炼 返回数组普通
								$remark=boss_560100($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$item['type']);
							}else if(560200==$item['start_entry_id'] && $item['end_entry_id']==560200){
								//蜀道试炼 返回数组精英
								$remark=boss_560200($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$item['type']);
							}else if(630100==$item['start_entry_id'] && $item['end_entry_id']==630100){
								//深海总动员 返回数组
								//开启时在线玩家
								$start_online=empty($item['start_time']) ? $i : strtotime($date.$item['start_time']);
								$end_online=empty($item['start_time']) ? $start_online+86400 : $start_online+300;
								$sql="select max(count) as count from log_online where time>=$start_online and time<=$end_online";
								$list=$mysqli->findOne($sql);
								$online=$list ? $list['count'] : 0;
								$remark=boss_630100($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli,$online);
							}else if(205500==$item['start_entry_id'] && $item['end_entry_id']==205500){
								//七擒孟获
								$remark=boss_205500($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli);
							}else if(510100==$item['start_entry_id'] && $item['end_entry_id']==510100){
								//凤雏秘境
								$remark=boss_510100($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli);
							}else if(900300==$item['start_entry_id'] && $item['end_entry_id']==900300){
								//洛阳攻防战流水
								$sql="select count(*) from log_fight where time>=$i and time<($i+86400) and entry_id=900300 ";
								$list=$mysqli->count($sql);
								if($list==0) continue;
								$remark=boss_900300($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli);

							}else if(330300==$item['start_entry_id'] && $item['end_entry_id']==330300){
								//群英会流水
								$sql="select count(*) from log_hero_meet where time>=$i and time<($i+86400) and entry_id=330300 ";
								$list=$mysqli->count($sql);
								if($list==0) continue;
								$remark=boss_330300($where_entry,$i,$item['level_start'],$item['level_end'],$mysqli);
							}
							//入库
							$date=date('Y-m-d',$i);
							$time=time();
							$remark=addslashes(json_encode($remark));
							$sql="replace into stat_boss (date,name,type,entry_id,level,count,player,allow_player,remark,time) value
							('$date','{$item['name']}','{$item['type']}','{$item['start_entry_id']}','{$item['level_start']}',$count,$player,$allow_player,'$remark',$time)";
							$mysqli->query($sql);
						}
					}
					break;

			}
		}
		break;

}

//兵临城下精英
function boss_600200($where_entry,$i,$level_start,$level_end,$mysqli,$type,$count){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array_jy=$statBoss->getInfo(600200);  //获得boss array
	$sql="select * from log_boss where $where_entry  order by boss_id asc  ";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		$boss_level=isset($boss_array_jy['layer_list'][$boss_id]['boss_level']) ? $boss_array_jy['layer_list'][$boss_id]['boss_level'] : 1;
		//分别统计各个n阶数量 boss
		isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
	}
	$array['hope_value']='0,0,0';
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}

//兵临城下普通
function boss_600100($where_entry,$i,$level_start,$level_end,$mysqli,$type,$count){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array=$statBoss->getInfo(600100);  //获得boss array
	if($type==0){
		//总
		$type_money=27;  //log_money 花费类型
		$sql="select * from log_money where time>=$i and time<($i+86400) and type=$type_money and money_type=3 order by money_num asc ";
		$result=$mysqli->query($sql);
		$dataHope=array(20=>0,40=>0,60=>0);
		$dataPlayer=array();
		$countMoney=0;
		while($result && $row=$result->fetch_assoc()){
			$money_num=$row['money_num'];
			isset($dataHope[$money_num]) ? $dataHope[$money_num]++: $dataHope[$money_num]=1;
			$countMoney +=$money_num;
		}
		$array['money_num']=$countMoney;//消耗元宝数量
		$array['avg_money']=$count==0 ? 0 : intval($countMoney/$count); ;//人均消费
		$sql="select * from log_boss where $where_entry  order by boss_id asc  ";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$boss_id=$row['boss_id'];
			$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
			//分别统计各个n阶数量 boss
			isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
		}
		$array['hope_value']=implode(",",$dataHope);  //分别统计各祝福值
		$array['level_start']=$level_start;
		$array['level_end']=$level_end;
	}else{
		$sql="select * from log_boss where $where_entry  order by boss_id asc  ";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$boss_id=$row['boss_id'];
			$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
			//分别统计各个n阶数量 boss
			isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
		}
		//分别统计各个n阶 boss
		$array['hope_value']='0,0,0';
		$array['level_start']=$level_start;
		$array['level_end']=$level_end;
	}
	return $array;

}

//草船借箭精英
function boss_700200($where_entry,$i,$level_start,$level_end,$mysqli,$type,$count){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array_jy=$statBoss->getInfo(700200);  //获得boss array
	$sql="select * from log_boss where $where_entry  order by boss_id asc  ";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		$boss_level=isset($boss_array_jy['layer_list'][$boss_id]['boss_level']) ? $boss_array_jy['layer_list'][$boss_id]['boss_level'] : 1;
		//分别统计各个n阶数量 boss
		isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
	}
	$array['hope_value']='0,0,0';
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}

//草船借箭普通
function boss_700100($where_entry,$i,$level_start,$level_end,$mysqli,$type,$count){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array=$statBoss->getInfo(700100);  //获得boss array
	if($type==0){
		//总
		$type_money=32;
		$sql="select * from log_money where time>=$i and time<($i+86400) and type=$type_money and money_type=3 order by money_num asc ";
		$result=$mysqli->query($sql);
		$dataHope=array(20=>0,50=>0,100=>0);
		$dataPlayer=array();
		$countMoney=0;
		while($result && $row=$result->fetch_assoc()){
			$money_num=$row['money_num'];
			isset($dataHope[$money_num]) ? $dataHope[$money_num]++: $dataHope[$money_num]=1;
			$countMoney +=$row['money_num'];
		}
		$array['money_num']=$countMoney;//消耗元宝数量
		$array['avg_money']=$count==0 ? 0 : intval($countMoney/$count); ;//人均消费
		$sql="select * from log_boss where $where_entry order by boss_id asc  ";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$boss_id=$row['boss_id'];
			$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
			//分别统计各个n阶数量 boss
			isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
		}
		$array['hope_value']=implode(",",$dataHope);  //分别统计各祝福值
		$array['level_start']=$level_start;
		$array['level_end']=$level_end;
	}else{
		//普通
		$sql="select * from log_boss where $where_entry  order by boss_id asc  ";
		$result=$mysqli->query($sql);
		while($result && $row=$result->fetch_assoc()){
			$boss_id=$row['boss_id'];
			$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
			//分别统计各个n阶数量 boss
			isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
		}
		$array['hope_value']='0,0,0';
		$array['level_start']=$level_start;
		$array['level_end']=$level_end;
	}
	return $array;
}

//乾坤八卦
function boss_304000($where_entry,$level_start,$level_end,$mysqli,$type,$count){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array=$statBoss->getInfoId(304000);
	$boss_array_jy=$statBoss->getInfoId(305000);
	$sql="select * from log_boss where $where_entry order by boss_id asc ";
	$result=$mysqli->query($sql);
	$data=array();
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		$char_id=$row['char_id'];
		if($row['entry_id']==304000){
			$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
			isset($data[$char_id]) ? $data[$char_id]=$boss_level : $data[$char_id]=$boss_level;
		}else if($row['entry_id']==305000){
			$boss_level=isset($boss_array_jy['layer_list'][$boss_id]['boss_level']) ? $boss_array_jy['layer_list'][$boss_id]['boss_level'] : 1;
			isset($data[$char_id]) ? $data[$char_id]=$boss_level : $data[$char_id]=$boss_level;
		}
	}
	//总通关数
	sort($data);  //键值改为0,1..
	//每一层记录当天的通关比例  1_boss:0,0,0,.......
	$boss_id_rename_array=array(1,2,3,4,5,6,7,8,9,10,11,12);
	for($i=0;$i<count($boss_id_rename_array);$i++){
		$k=0;
		for($j=0;$j<count($data);$j++){
			if($boss_id_rename_array[$i]==$data[$j]){
				$k++;
			}
		}
		$array[($i+1).'_boss']=count($data)==0 ? 0 : round($k/count($data),4)*100;//
	}
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}

//洛阳攻防战
function boss_900300($where_entry,$i,$level_start,$level_end,$mysqli){
	$array=array();
	$type_money=69;
	//当日登陆玩家数
	$sql="select count(distinct char_id) as player_all from log_login where login_time>=$i and login_time<($i+86400) ";
	$list=$mysqli->findOne($sql);
	$array['player_all']=$list ? $list['player_all'] : 0;

	$dataPlayer=$dataPlayerT=array();  //备注 元宝购买的人数 铜钱购买的人数
	$countY=$countT=0;  //元宝购买的次数 铜钱购买的次数
	//铜钱，元宝振奋 鼓舞人数 次数 money_type=3 元宝  --- money_type in (1,2) //铜钱
	$sql="select * from log_money where time>=$i and time<($i+86400) and type=$type_money ";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$player_id=$row['char_id'];
		if($row['money_type']==3){
			//元宝
			isset($dataPlayer[$player_id]) ? $dataPlayer[$player_id]++ : $dataPlayer[$player_id]=1;
			$countY += 1;
		}else{
			//铜钱
			isset($dataPlayerT[$player_id]) ? $dataPlayerT[$player_id]++ : $dataPlayerT[$player_id]=1;
			$countT += 1;
		}
	}
	$array['player_y']=empty($dataPlayer) ? 0 :count($dataPlayer);  //购买元宝玩家人数
	$array['player_t']=empty($dataPlayerT) ? 0 :count($dataPlayerT);  //购买元宝玩家人数
	$array['count_y']=$countY;  //购买元宝玩家的次数
	$array['count_t']=$countT;  //购买铜钱玩家的次数

	//洛阳攻防战流水
	$sql="select * from log_fight where time>=$i and time<($i+86400) and entry_id=900300 ";
	$list=$mysqli->findOne($sql);

	$array['faction_count']=$list ? $list['faction_count'] : 0;//次数
	$array['break_door_time']=$list ? $list['break_door_time'] : '';//城门攻破时间
	$array['host_time']=$list ? $list['host_time'] : '';//城主攻破时间
	$array['belong_faction']=$list ? $list['belong_faction'] : '';//归属帮派
	$mdb=new Mdb();
	$mdb->selectDb(MONGO_PERFIX.'5');
	if(!empty($list['remark'])){
		$remark=explode(',', $list['remark']);//排名前5的帮派id
		foreach ($remark as $faction_id){
			$faction_list=$mdb->findOne('faction', array('_id'=>$faction_id), array('name','memberList'));
			$char_list=$arr=array();
			if(!empty($faction_list['memberList'])){
				foreach ($faction_list['memberList'] as $memberList){
		   			$char_list[]=$memberList[0];
				}
			}
			if($char_list){
				$char_list=implode(',', $char_list);
				$sql="select count(distinct char_id) as player from log_copy_enter where $where_entry and char_id in ($char_list)";
				$faction_player_list=$mysqli->count($sql);
				$arr['faction_player']=intval($faction_player_list['player']);
				$sql="select sum(money_num) as money_num from log_money where time>=$i and time<($i+86400) and money_type=8 and io=0 and char_id in ($char_list)";
				$faction_exploit_list=$mysqli->findOne($sql);
				$arr['faction_exploit']=empty($faction_exploit_list['money_num']) ? 0 : intval($faction_exploit_list['money_num']);
				$arr['faction_name']=$faction_list['name'];
			}
			$arr&&$array['rank'][]=$arr;
		}
	}
	$array['belong_faction_player']=0;//归属帮派参与人数
	$array['belong_faction_exploit']=0;//归属帮派所得功勋
	if(!empty($list['belong_faction'])){
		//第一名帮派归属 参与人数 所得功勋
		$list=$mdb->findOne('faction', array('name'=>$list['belong_faction']), array('memberList'));
		$char_list=array();
		if(!empty($list['memberList'])){
			foreach ($list['memberList'] as $memberList){
	   			$char_list[]=$memberList[0];
			}
		}
		if($char_list){
			$char_list=implode(',', $char_list);
			$sql="select count(distinct char_id) as player from log_copy_enter where $where_entry and char_id in ($char_list)";
			$list=$mysqli->count($sql);
			$array['belong_faction_player']=empty($list['player']) ? 0 : intval($list['player']);
			$sql="select sum(money_num) as money_num from log_money where time>=$i and time<($i+86400) and money_type=8 and io=0 and char_id in ($char_list)";
			$list=$mysqli->findOne($sql);
			$array['belong_faction_exploit']=empty($list['money_num']) ? 0 : intval($list['money_num']);
		}
	}
	return $array;
}
//十面埋伏
function boss_500100($where_entry,$i,$level_start,$level_end,$mysqli){
	$array=array();
	$sql="select * from log_boss where $where_entry order by boss_id asc ";
	$result=$mysqli->query($sql);
	$dataPt=array();

	$statBoss=new StatBoss();
	$boss_array=$statBoss->getInfo(500100);  //获得boss array
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
		isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
	}

	//分别统计各个n阶 boss
	$array['boss_level']=implode(",", $dataPt);  //分别统计各个n阶数量 boss
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}
//过关斩将
function boss_800100($where_entry,$i,$level_start,$level_end,$mysqli,$copy_id_config,$copy_id_jy_config,$copy_id_jy_2_config){
	$array=array();
	$sql="select * from log_boss where $where_entry  order by boss_id asc ";
	$result=$mysqli->query($sql);
	$dataPt=$dataJy=$dataJy2=array();
	while($result && $row=$result->fetch_assoc()){
		$char_id=$row['char_id'];
		if (in_array($row['boss_id'], $copy_id_jy_config)) {
			isset($dataJy[$char_id]) ? $dataJy[$char_id]=$row['boss_id'] : $dataJy[$char_id]=$row['boss_id'];
		}if(in_array($row['boss_id'],$copy_id_jy_2_config)){
			isset($dataJy2[$char_id]) ? $dataJy2[$char_id]=$row['boss_id'] : $dataJy2[$char_id]=$row['boss_id'];
		}else{
			isset($dataPt[$char_id]) ? $dataPt[$char_id]=$row['boss_id'] : $dataPt[$char_id]=$row['boss_id'];
		}

	}
	sort($dataPt);  //键值改为0,1..
	//每一层记录当天的通关比例  boss_1:0,0,0,.......
	for($i=0;$i<count($copy_id_config);$i++){
		$k=0;
		for($j=0;$j<count($dataPt);$j++){
			if($copy_id_config[$i]==$dataPt[$j]){
				$k++;
			}
		}
		$array[($i+1)]=count($dataPt)==0 ? 0 : round($k/count($dataPt),4)*100;//比例

	}
	sort($dataJy);
	//精英模式
	for($i=0;$i<count($copy_id_jy_config);$i++){
		$k=0;
		for($j=0;$j<count($dataJy);$j++){
			if($copy_id_jy_config[$i]==$dataJy[$j]){
				$k++;
			}
		}
		$array[$i.'_jy_boss']=count($dataJy)==0 ? 0 : $k;  //数量
	}
	sort($dataJy2);
	//精英模式
	for($i=0;$i<count($copy_id_jy_2_config);$i++){
		$k=0;
		for($j=0;$j<count($dataJy2);$j++){
			if($copy_id_jy_2_config[$i]==$dataJy2[$j]){
				$k++;
			}
		}
		$array[$i.'_jy_2_boss']=count($dataJy2)==0 ? 0 : $k;  //数量
	}

	return $array;
}

//蜀道试炼精英
function boss_560200($where_entry,$i,$level_start,$level_end,$mysqli,$type){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array_jy=$statBoss->getInfo(560200);  //获得boss array
	$sql="select * from log_boss where $where_entry  order by boss_id asc ";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		$boss_level=isset($boss_array_jy['layer_list'][$boss_id]['boss_level']) ? $boss_array_jy['layer_list'][$boss_id]['boss_level'] : 1;
		//分别统计各个n阶数量 boss
		isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
	}
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}

//蜀道试炼普通
function boss_560100($where_entry,$i,$level_start,$level_end,$mysqli,$type){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array=$statBoss->getInfo(560100);  //获得boss array
	$sql="select * from log_boss where $where_entry  order by boss_id asc ";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
		//分别统计各个n阶数量 boss
		isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
	}
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}

//深海总动员
function boss_630100($where_entry,$i,$level_start,$level_end,$mysqli,$online){
	$array=array();
	$sql="select * from log_deep_sea where time>=$i and time<($i+86400) ";
	$result=$mysqli->query($sql);
	$dataPt=array();
	while($result && $row=$result->fetch_assoc()){
		$char_id=$row['char_id'];
		isset($dataPt[$char_id]) ? $dataPt[$char_id]++ : $dataPt[$char_id]=1;
	}
	//各个角色获取的积分 $dataPt
	$count0=0;$count1=0;$count2=0;$count3=0;$count4=0;  //统计完成的人数
	foreach($dataPt as $key=>$item){
		if($item<4){
			$count0++;
		}else if($item>=4 && $item<=7){
			$count1++;
		}else if($item>=8 && $item<=15){
			$count2++;
		}else if($item>=16 && $item<=31){
			$count3++;
		}else if($item>=32){
			$count4++;
		}
	}
	$array['count0']=$count0;$array['count1']=$count1;$array['count2']=$count2;$array['count3']=$count3;$array['count4']=$count4;
	//当日登陆玩家数
	$sql="select count(distinct char_id) as player_all from log_login where login_time>=$i and login_time<($i+86400) ";
	$list=$mysqli->findOne($sql);
	$array['player_all']=$list ? $list['player_all'] : 0;
	$array['online']=$online;
	return $array;
}

//七擒孟获
function boss_205500($where_entry,$i,$level_start,$level_end,$mysqli){
	$array=array();
	$sql="select * from log_boss where $where_entry  order by boss_id asc ";
	$result=$mysqli->query($sql);
	$dataPt=array();
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		isset($dataPt[$boss_id]) ? $dataPt[$boss_id]++ : $dataPt[$boss_id]=1;
	}
	$array['boss_level']=implode(",", $dataPt);  //分别统计各个n阶数量 boss
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}
//凤雏秘境
function boss_510100($where_entry,$i,$level_start,$level_end,$mysqli){
	$array=array();
	$statBoss=new StatBoss();
	$boss_array=$statBoss->getInfoId(510100);  //获得boss array
	$sql="select * from log_boss where $where_entry  order by boss_id asc ";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$boss_id=$row['boss_id'];
		$boss_level=isset($boss_array['layer_list'][$boss_id]['boss_level']) ? $boss_array['layer_list'][$boss_id]['boss_level'] : 1;
		//分别统计各个n阶数量 boss
		isset($array[$boss_level.'_boss']) ? $array[$boss_level.'_boss']++ : $array[$boss_level.'_boss']=1;
	}
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	return $array;
}

//群英会
function boss_330300($where_entry,$i,$level_start,$level_end,$mysqli){
	$array=array();
	$array['level_start']=$level_start;
	$array['level_end']=$level_end;
	$sql="select * from log_hero_meet where time>=$i and time<($i+86400) order by time asc ";
	$result=$mysqli->query($sql);
	$data=array();
	while($result && $row=$result->fetch_assoc()){
		$remark_1=(array)json_decode($row['num_1'],true);
		$remark_2=(array)json_decode($row['num_2'],true);
		$remark_3=(array)json_decode($row['num_3'],true);
		$remark_4=(array)json_decode($row['num_4'],true);
		$remark_5=(array)json_decode($row['num_5'],true);
		$remark_6=(array)json_decode($row['num_6'],true);
		$remark_7=(array)json_decode($row['num_7'],true);
		$remark_8=(array)json_decode($row['num_8'],true);

		$data['char_name_1']=isset($remark_1['char_name']) ? $remark_1['char_name'] : '';
		$data['fight_max_1']=isset($remark_1['fight_max']) ? $remark_1['fight_max'] : '';
		$data['fight_time_1']=isset($remark_1['fight_time']) ? $remark_1['fight_time'] : '';
		$data['char_name_2']=isset($remark_2['char_name']) ? $remark_2['char_name'] : '';
		$data['fight_max_2']=isset($remark_2['fight_max']) ? $remark_2['fight_max'] : '';
		$data['fight_time_2']=isset($remark_2['fight_time']) ? $remark_2['fight_time'] : '';
		$data['char_name_3']=isset($remark_3['char_name']) ? $remark_3['char_name'] : '';
		$data['fight_max_3']=isset($remark_3['fight_max']) ? $remark_3['fight_max'] : '';
		$data['fight_time_3']=isset($remark_3['fight_time']) ? $remark_3['fight_time'] : '';;
		$data['char_name_4']=isset($remark_4['char_name']) ? $remark_4['char_name'] : '';;
		$data['fight_max_4']=isset($remark_4['fight_max']) ? $remark_4['fight_max'] : '';;
		$data['fight_time_4']=isset($remark_4['fight_time']) ? $remark_4['fight_time'] : '';;
		$data['char_name_5']=isset($remark_5['char_name']) ? $remark_5['char_name'] : '';;
		$data['fight_max_5']=isset($remark_5['fight_max']) ? $remark_5['fight_max'] : '';;
		$data['fight_time_5']=isset($remark_5['fight_time']) ? $remark_5['fight_time'] : '';;
		$data['char_name_6']=isset($remark_6['char_name']) ? $remark_6['char_name'] : '';;
		$data['fight_max_6']=isset($remark_6['fight_max']) ? $remark_6['fight_max'] : '';;
		$data['fight_time_6']=isset($remark_6['fight_time']) ? $remark_6['fight_time'] : '';;
		$data['char_name_7']=isset($remark_7['char_name']) ? $remark_7['char_name'] : '';;
		$data['fight_max_7']=isset($remark_7['fight_max']) ? $remark_7['fight_max'] : '';;
		$data['fight_time_7']=isset($remark_7['fight_time']) ? $remark_7['fight_time'] : '';;
		$data['char_name_8']=isset($remark_8['char_name']) ? $remark_8['char_name'] : '';;
		$data['fight_max_8']=isset($remark_8['fight_max']) ? $remark_8['fight_max'] : '';;
		$data['fight_time_8']=isset($remark_8['fight_time']) ? $remark_8['fight_time'] : '';;
	}
	$array['remark']=!empty($data) ? $data : '';  //分别统计各个n阶数量 boss
	return $array;
}

?>