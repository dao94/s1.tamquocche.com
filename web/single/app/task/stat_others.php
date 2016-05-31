<?php
/*
 * 按日期统计部分功能
 * shake_tree：摇钱树
 * activity：活动参与度
 * pet_egg：刷宠物蛋
 * box:刷箱子（锦囊）
 * pet:宠物(pet_jinjian)宠物觐见分析
 * allow_player:活动开启时满足人数（活动开启时，全服在线的，等级大于等于活动开启等级的玩家个数）
 * click_box:功能条件框分析
 * php stat_others.php --task=shake_tree --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
//include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __AUTH__.'lang.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'shake_tree':
		//摇钱树统计
		$stat_table=array('name'=>'stat_shake_tree','field'=>'date');
		$log_table=array('name'=>'log_money','field'=>'time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			//平均等级：当天登录玩家的平均等级
			$sql="select avg(level) as avg_level from (select max(level) as level from log_login where login_time>=$i and login_time<$i+86400 group by char_id) t";
			$list=$mysqli->findOne($sql);
			$avg_level=empty($list['avg_level']) ? 0 : intval($list['avg_level']);
			if($avg_level){
				//使用人数
				$sql="select count(*) as count,count(distinct char_id) as player from log_money where time>=$i and time<$i+86400 and (type=12 or type=49) and io=1";
				$list=$mysqli->findOne($sql);
				$count=empty($list['count']) ? 0 : intval($list['count']);
				$player=empty($list['player']) ? 0 : intval($list['player']);
				//加速次数、人数
				$sql="select count(*) as count,count(distinct char_id) as player from log_money where time>=$i and time<$i+86400 and type=49 and io=0";
				$list=$mysqli->findOne($sql);
				$quicken_count=empty($list['count']) ? 0 : intval($list['count']);
				$quicken_player=empty($list['player']) ? 0 : intval($list['player']);
				//金银果次数
				$sql="select count(*) as count from log_money where time>=$i and time<$i+86400 and type=48 and io=1";
				$list=$mysqli->findOne($sql);
				$jinyinguo=empty($list['count']) ? 0 : intval($list['count']);
				//大力一脚人数
				$sql="select count(*) as count,count(distinct char_id) as player from log_money where time>=$i and time<$i+86400 and type=12 and money_type=3 and io=0";
				$list=$mysqli->findOne($sql);
				$foot_count=empty($list['count']) ? 0 : intval($list['count']);
				$foot_player=empty($list['player']) ? 0 : intval($list['player']);
				$date=date('Y-m-d',$i);
				$time=time();
				$sql="replace into stat_shake_tree (date,avg_level,count,player,quicken_count,quicken_player,jinyinguo,foot_count,foot_player,time) values
					('$date',$avg_level,$count,$player,$quicken_count,$quicken_player,$jinyinguo,$foot_count,$foot_player,$time)";
				$mysqli->query($sql);
			}
		}
		break;

	case 'allow_player':
		/*
		 * 满足人数,每5分定时执行一次
		 * 副本：当天登录的玩家，大于等于开启等级的人数
		 * 活动：活动开启时，全服在线的，等级大于等于活动开启等级的玩家个数
		 */
		require __CONFIG__.'activity_join_config.php';
		if(empty($activity_conf)) exit('Config error');
		$nowtime=time();
		$date=date('Y-m-d',$nowtime);
		$mdb=new Mdb();
		foreach ($activity_conf as $type=>$items){
			foreach ($items as $item){
				if(!empty($item['start_time'])&&!empty($item['end_time'])){
					$start_time=strtotime($date.$item['start_time']);
					$end_time=strtotime($date.$item['end_time']);
					if($nowtime<=$end_time&&$nowtime>=$start_time){
						$sql="select count(*) as count from stat_activity where date='$date' and name='{$item['name']}'";
						$count=$mysqli->count($sql);
						if($count==0){
							//当前在线玩家
							$sql="select distinct char_id from log_login where login_time>$nowtime-2*86400 and logout_time=0";
							$query=$mysqli->query($sql);
							$char_list=array();
							while ($row=$query->fetch_assoc()){
								$char_id=floatval($row['char_id']);
								$char_list[$char_id%4][]=$char_id;
							}
							//过滤不满足的的等级
							$allow_player=0;
							for ($i=0;$i<4;$i++){
								$mdb->selectDb(MONGO_PERFIX.$i);
								if(!empty($char_list[$i])){
									$condition=array('level'=>array('$gte'=>$item['level']),'_id'=>array('$in'=>$char_list[$i]));
									$allow_player+=$mdb->count('characters', $condition);
								}
							}

							//数据入库
							$sql="insert into stat_activity (date,name,allow_player,time) value ('$date','{$item['name']}',$allow_player,$nowtime)";
							$mysqli->query($sql);
						}
					}
				}
			}
		}
		break;

	case 'activity':
		//活动参与度
		require __CONFIG__.'activity_join_config.php';
		if(empty($activity_conf)) exit('Config error');
		foreach ($activity_conf as $type=>$items){
			switch ($type){
				case 'mission':
					//任务类
					foreach ($items as $item){
						$stat_table=array('name'=>'stat_activity','field'=>'date','where'=>"type='$type' and name='{$item['name']}'");
						$log_table=array('name'=>'log_mission_daily_complete','field'=>'time','where'=>"type={$item['type']}");
						$min_date=$task->getStartDate($start_date, $stat_table,$log_table);
						$start_time=strtotime($min_date);
						$end_time=strtotime($end_date);
						for($i=$start_time;$i<=$end_time;$i+=86400){
							$date=date('Y-m-d',$i);
							//完成次数、人数
							$activity_start_time=empty($item['start_time']) ? $i : strtotime($date.$item['start_time']);
							$activity_end_time=empty($item['end_time']) ? $i+86400 : strtotime($date.$item['end_time']);
							$sql="select count(*) as count,count(distinct char_id) as player from log_mission_daily_complete where time>=$activity_start_time and time<$activity_end_time and type={$item['type']} and status=2";
							$list=$mysqli->findOne($sql);
							$count=$list ? $list['count'] : 0;
							$player=$list ? $list['player'] : 0;

							if(empty($item['start_time'])){
								//当日满足人数：当天登录的玩家，注册时间早于Thời gian，大于等于开启等级的人数
								$sql="select count(distinct char_id) as allow_player from log_login where login_time>=$i and login_time<$i+86400 and level>={$item['level']}";
							}else{
								//活动开启时，全服在线的，等级大于等于活动开启等级的玩家个数
								$sql="select allow_player from stat_activity where date='$date' and name='{$item['name']}'";
							}
							$list=$mysqli->findOne($sql);
							$allow_player=$list ? $list['allow_player'] : 0;

							//开启时在线：在活动开启时的最高在线
							$start_online=empty($item['start_time']) ? $i : strtotime($date.$item['start_time']);
							$end_online=empty($item['start_time']) ? $start_online+86400 : $start_online+300;
							$sql="select max(count) as count from log_online where time>=$start_online and time<=$end_online";
							$list=$mysqli->findOne($sql);
							$online=$list ? $list['count'] : 0;

							//入库
							$end_online=empty($item['end_time']) ? $i+86400 : strtotime($date.$item['end_time']);
							$time=time();
							$sql="replace into stat_activity (date,name,type,level,count,start_time,end_time,player,allow_player,online,time) value
							('$date','{$item['name']}','$type','{$item['level']}',$count,$start_online,$end_online,$player,$allow_player,$online,$time)";
							$mysqli->query($sql);
						}
					}
					break;

				case 'answer':
					//答题类
					foreach ($items as $item){
						$stat_table=array('name'=>'stat_activity','field'=>'date','where'=>"type='$type' and name='{$item['name']}'");
						$log_table=array('name'=>'log_answer','field'=>'time');
						$min_date=$task->getStartDate($start_date, $stat_table,$log_table);
						$start_time=strtotime($min_date);
						$end_time=strtotime($end_date);
						for($i=$start_time;$i<=$end_time;$i+=86400){
							$date=date('Y-m-d',$i);
							//完成次数、人数
							$sql="select count(*) as count,count(distinct char_id) as player from log_answer where time>=$i and time<$i+86400";
							$list=$mysqli->findOne($sql);
							$count=$list ? $list['count'] : 0;
							$player=$list ? $list['player'] : 0;

							if(empty($item['start_time'])){
								//当日满足人数：当天登录的玩家，注册时间早于Thời gian，大于等于开启等级的人数
								$sql="select count(distinct char_id) as allow_player from log_login where login_time>=$i and login_time<$i+86400 and level>={$item['level']}";
							}else{
								//活动开启时，全服在线的，等级大于等于活动开启等级的玩家个数
								$sql="select allow_player from stat_activity where date='$date' and name='{$item['name']}'";
							}
							$list=$mysqli->findOne($sql);
							$allow_player=$list ? $list['allow_player'] : 0;

							//开启时在线：在活动开启时的最高在线
							$start_online=empty($item['start_time']) ? $i : strtotime($date.$item['start_time']);
							$end_online=empty($item['start_time']) ? $start_online+86400 : $start_online+300;
							$sql="select max(count) as count from log_online where time>=$start_online and time<=$end_online";
							$list=$mysqli->findOne($sql);
							$online=$list ? $list['count'] : 0;

							//入库
							$end_online=empty($item['end_time']) ? $i+86400 : strtotime($date.$item['end_time']);
							$time=time();
							$sql="replace into stat_activity (date,name,type,level,count,start_time,end_time,player,allow_player,online,time) value
							('$date','{$item['name']}','$type','{$item['level']}',$count,$start_online,$end_online,$player,$allow_player,$online,$time)";
							$mysqli->query($sql);
						}
					}
					break;

				case 'copy':
					//副本类
					foreach ($items as $item){
						$stat_table=array('name'=>'stat_activity','field'=>'date','where'=>"type='$type' and name='{$item['name']}'");
						$log_table=array('name'=>'log_copy_enter','field'=>'time','where'=>"entry_id>={$item['start_entry_id']} and entry_id<={$item['end_entry_id']}");
						$min_date=$task->getStartDate($start_date, $stat_table,$log_table);
						$start_time=strtotime($min_date);
						$end_time=strtotime($end_date);
						for($i=$start_time;$i<=$end_time;$i+=86400){
							//完成次数、人数
							$date=date('Y-m-d',$i);
							$activity_start_time=empty($item['start_time']) ? $i : strtotime($date.$item['start_time']);
							$activity_end_time=empty($item['end_time']) ? $i+86400 : strtotime($date.$item['end_time']);
							$sql="select count(*) as count,count(distinct char_id) as player from log_copy_enter where time>=$activity_start_time and time<$activity_end_time and entry_id>={$item['start_entry_id']} and entry_id<={$item['end_entry_id']}";
							$list=$mysqli->findOne($sql);
							$count=$list ? $list['count'] : 0;
							$player=$list ? $list['player'] : 0;

							if(empty($item['start_time'])){
								//当日满足人数：当天登录的玩家，注册时间早于Thời gian，大于等于开启等级的人数
								$sql="select count(distinct char_id) as allow_player from log_login where login_time>=$i and login_time<$i+86400 and level>={$item['level']}";
							}else{
								//活动开启时，全服在线的，等级大于等于活动开启等级的玩家个数
								$sql="select allow_player from stat_activity where date='$date' and name='{$item['name']}'";
							}
							$list=$mysqli->findOne($sql);
							$allow_player=$list ? $list['allow_player'] : 0;

							//开启时在线：在活动开启时的最高在线
							$start_online=empty($item['start_time']) ? $i : strtotime($date.$item['start_time']);
							$end_online=empty($item['start_time']) ? $start_online+86400 : $start_online+300;
							$sql="select max(count) as count from log_online where time>=$start_online and time<=$end_online";
							$list=$mysqli->findOne($sql);
							$online=$list ? $list['count'] : 0;

							//入库
							$end_online=empty($item['end_time']) ? $i+86400 : strtotime($date.$item['end_time']);
							$time=time();
							$sql="replace into stat_activity (date,name,type,level,count,start_time,end_time,player,allow_player,online,time) value
							('$date','{$item['name']}','$type','{$item['level']}',$count,$start_online,$end_online,$player,$allow_player,$online,$time)";
							$mysqli->query($sql);
						}
					}
					break;
			}
		}
		break;

	case 'pet_egg':
		//刷宠物蛋
		$stat_table=array('name'=>'stat_pet_egg','field'=>'date');
		$log_table=array('name'=>'log_money','field'=>'time','where'=>'type=44');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		//单次刷蛋扣10元宝，批量刷蛋扣除100元宝（12次）
		for($i=$start_time;$i<=$end_time;$i+=86400){
			//刷蛋人数
			$sql="select sum(case when (money_num=10 or money_num=8) then 1 when money_num=100 then 12 else 0 end) as count,count(distinct char_id) as player,
				sum(money_num) as money_num from log_money	where time>=$i and time<$i+86400 and type=44 and money_type=3 and io=0";
			$data=$mysqli->findOne($sql);
			//开出稀有蛋次数、人数
			$sql="select count(*) as rare_count,count(distinct char_id) as rare_player from log_pet where time>=$i and time<$i+86400 and type=1";
			$list=$mysqli->findOne($sql);
			$data=array_merge($data,$list);
			$data['date']=date('Ymd',$i);
			$data['time']=time();

			$fields=implode(',', array_keys($data));
			$values=implode(',', array_values($data));
			$sql="replace into stat_pet_egg ($fields) values ($values)";
			$mysqli->query($sql);
		}
		break;

	case 'box':
		//刷箱子（锦囊）
		$stat_table=array('name'=>'stat_box','field'=>'date');
		$log_table=array('name'=>'log_money','field'=>'time','where'=>'type=15');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			//使用人数
			$sql="select char_id,char_name,sum(money_num) as money_num from log_money where time>=$i and time<$i+86400
				and type=15 and money_type=3 and io=0 group by char_id order by money_num desc";
			$result=$mysqli->query($sql);
			$player=$money=0;//使用人数
			$ranking_money=array();//当天排行
			while ($row=$result->fetch_assoc()){
				$player++;
				$money+=$row['money_num'];
				$ranking_money[]=$row;
			}
			//历史排行
			$sql="select char_id,char_name,sum(money_num) as money_num from log_money where time<$i+86400
				and type=15 and money_type=3 and io=0 group by char_id order by money_num desc limit 100";
			$result=$mysqli->query($sql);
			$ranking_history=array();//历史排行
			while ($row=$result->fetch_assoc()){
				$ranking_history[]=$row;
			}

			//产出道具汇总
			$sql="select item_id,sum(item_num) as item_num from log_items where time<$i+86400 and type=23 and io=1 and bag_id=6 group by item_id order by item_num desc";
			$result=$mysqli->query($sql);
			$ranking_item=array();//历史排行
			while ($row=$result->fetch_assoc()){
				$ranking_item[]=$row;
			}

			//入库
			$date=date('Y-m-d',$i);
			$time=time();
			$ranking_money=my_escape_string(json_encode($ranking_money));
			$ranking_history=my_escape_string(json_encode($ranking_history));
			$ranking_item=my_escape_string(json_encode($ranking_item));
			$sql="replace into stat_box (date,player,money,ranking_money,ranking_history,ranking_item,time) values
				('$date',$player,$money,'$ranking_money','$ranking_history','$ranking_item',$time)";
			$mysqli->query($sql);
		}
		break;

	case 'pet_jinjian':
		//宠物觐见分析
		//货币流水 宠物觐见流水
		$stat_table=array('name'=>'stat_pet_jinjian','field'=>'date');
		$log_table=array('name'=>'log_pet_jinjian','field'=>'time');
		$min_date=$task->getStartDate($start_date, $stat_table,$log_table);

		$start_time=strtotime($min_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			//铜钱
			$sql="select * from log_money where time>=$i and time<($i+86400) and type in (77,79,80,81,82,83) ";
			$result=$mysqli->query($sql);
			$remark=$dataPlayer_tb=$dataPlayer_yb=array();
			$countMoney_tb=$countMoney_yb=$yb_count=$tb_count=0;
			while($result && $row=$result->fetch_assoc()){
				$char_id=$row['char_id'];
				if($row['type']==77){
					//元宝
					isset($dataPlayer_yb[$char_id]) ? $dataPlayer_yb[$char_id]++ : $dataPlayer_yb[$char_id]=1;
					$countMoney_yb += $row['money_num'];  //消耗元宝数量
					$yb_count += 1;  //购买元宝玩家的次数
				}else{
					//铜钱
					isset($dataPlayer_tb[$char_id]) ? $dataPlayer_tb[$char_id]++ : $dataPlayer_tb[$char_id]=1;
					$countMoney_tb += $row['money_num'];  //消耗铜钱数量
					$tb_count += 1;  //购买铜钱玩家的次数
				}
			}

			$yb_player=empty($dataPlayer_yb) ? 0 :count($dataPlayer_yb);  //购买元宝玩家人数
		    $tb_player=empty($dataPlayer_tb) ? 0 :count($dataPlayer_tb);  //购买铜钱玩家人数
			$yb_avg=count($dataPlayer_yb)==0 ? 0 : intval($countMoney_yb/count($dataPlayer_yb)); //人均消费
			$tb_avg=count($dataPlayer_tb)==0 ? 0 : intval($countMoney_tb/count($dataPlayer_tb)); //人均消费

			$array=array();
			$vip_array=array(1=>12,2=>14,3=>16,4=>18,5=>20,6=>22,7=>24,8=>26,9=>28,10=>30);
			$sql="select max(vip) as vip,sum(count) as count from log_pet_jinjian where vip>0 and time>=$i and time<($i+86400) and type in (1,2) group by char_id"; //去掉招见
			$result=$mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				$vip=$row['vip'];
				$min_count=$vip_array[$vip];   //例如 vip $row['vip']=1, $min_count=$vip_array[1]=12;
				if($row['count']>=$min_count) isset($remark[$vip.'_vip']) ? $remark[$vip.'_vip']++ : $remark[$vip.'_vip']=1;   //$row['count'] 如果是13 就大于12 说明 $array[1]++;
			}

			$sql="select max(vip) as vip,char_id from log_pet_jinjian where vip>0 and time>=$i and time<($i+86400)  group by char_id";
			$result=$mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				$vip=$row['vip'];
				isset($remark[$vip.'_vip_count']) ? $remark[$vip.'_vip_count']++ : $remark[$vip.'_vip_count']=1;   //$remark['5_vip_count'] 统计vip5的人数
			}

			//入库
			$date=date('Y-m-d',$i);
			$time=time();
			$remark=json_encode($remark);
			$sql="replace into stat_pet_jinjian (date,t_player,t_count,t_pay,t_avg_pay,y_player,y_count,y_pay,y_avg_pay,remark,time) value
			('$date',$tb_player,$tb_count,$countMoney_tb,$tb_avg,$yb_player,$yb_count,$countMoney_yb,$yb_avg,'$remark',$time)";
			$mysqli->query($sql);
		}
		break;

	case 'click_box':
		//功能提醒框
		require __CONFIG__.'click_box_config.php';
		if(empty($click_box_conf)) exit('Config error');
		//等级段配置
		$level_conf=array(
			array(30,40),
			array(41,50),
			array(51,60),
			array(61,100),
		);
		$stat_table=array('name'=>'stat_click_box','field'=>'date');
		$log_table=array('name'=>'log_click_box','field'=>'time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			foreach($click_box_conf as $items){
				$click_where="where time>=$i and time<$i+86400";
				//通过项目类型查询
				$click_where.=$items['type']>0 ? " and type={$items['type']}" : '' ;
				$sql="select count(*) as count,count(distinct char_id) as player from log_click_box $click_where";
				$list=$mysqli->findOne($sql);
				//点击次数
				$count=$list ? $list['count'] : 0;
				//点击人数
				$player=$list ? $list['player'] : 0;
				$allow_where="where login_time>=$i and login_time<$i+86400 and level>={$items['level']}";
				$sql="select count(distinct char_id) as allow_player from log_login $allow_where ";
				$list=$mysqli->findOne($sql);
				//当日登陆玩家中等级大于等于开放等级的玩家数
				$allow_player=$list ? $list['allow_player'] : 0;
				$sql="select level from log_click_box $click_where";
				$result=$mysqli->query($sql);
				$remark=array();
				//各等级段点击次数
				while ($result && $row=$result->fetch_assoc()){
					$level=$row['level'];
					foreach($level_conf as $item){
						if($level>=$item[0] && $level<=$item[1]){
							$key=$item[0].'-'.$item[1];
							isset($remark[$key]) ? $remark[$key]++ : $remark[$key]=1;
						}
					}
				}
				$remark=$remark ? json_encode($remark) : '';
				$date=date('Y-m-d',$i);
				$time=time();
				//入库
				$sql="replace into stat_click_box (date,type,count,player,allow_player,remark,time) values ('$date','{$items['type']}','$count','$player','$allow_player','$remark',$time)";
				$mysqli->query($sql);
			}
		}
		break;
}
