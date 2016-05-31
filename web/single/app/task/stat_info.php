<?php
/*
 * 按日期统计基本信息
 * register：注册
 * online：在线数据
 * minute_loss：分钟流失
 * online_length：在线时长
 * login：登陆信息
 * keep_day：留存天数统计
 * keep_ratio：留存率统计
 * upgrade：升级时间
 * loader_time:加载时间统计
 * 在线数据
 * php stat_info.php --task=register --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';

if(!isset($argc) || $argc<2) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'register':
		//注册流程统计
		$stat_table=array('name'=>'stat_reg','field'=>'date');
		$start_date=$task->getStartDate($start_date, $stat_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$mdb=new Mdb();
		// *_jtxm2_4.account_data表存储字段配置
		$field_conf=array(
		//account_data(mongo)查询字段=>stat_reg(mysql)存储字段
			'create_time'=>'account_count',//帐号数量（注册连接数）
			'loader_page'=>'loader_page',//web页面完成加载
			'loader_main'=>'loader_main',//完成加载LoaderMain.swf
			'loader_resource'=>'loader_resource',//完成加载resource/Main.swf
			'loader_login'=>'loader_login',//完成加载LoginModule.swf（到达注册页数）
			'loader_character'=>'loader_character',//完成创号
			'loader_game'=>'loader_game',//完成加载GameModule.swf（进入游戏数）
		);
		//总体
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$data=array();
			$mdb->selectDb(MONGO_PERFIX.'4');
			foreach ($field_conf as $key=>$item){
				$condition=array($key=>array('$gte'=>$i,'$lt'=>$i+86400));
				$data[$item]=$mdb->count('account_data', $condition);
			}
			//总帐号数（总注册连接数）
			$condition=array('create_time'=>array('$lt'=>$i+86400));
			$data['total_account_count']=$mdb->count('account_data', $condition);
			//当天角色数
			$condition=array('creat_time'=>array('$gte'=>$i,'$lt'=>$i+86400));
			$data['character_count']=$mdb->allCount('characters', $condition);
			//角色总数
			$condition=array('creat_time'=>array('$lt'=>$i+86400));
			$data['total_character_count']=$mdb->allCount('characters', $condition);

			$data['date']=date('Ymd',$i);
			$data['time']=time();
			$fields=implode(',', array_keys($data));
			$values=implode(',', array_values($data));
			$sql="replace into stat_reg ($fields) values ($values)";
			$mysqli->query($sql);
		}

		//滚服玩家
		$sql="select * from log_old_account";
		$result=$mysqli->query($sql);
		$users=$users_mdb=array();
		while($result && $row=$result->fetch_assoc()){
			$users[]="'{$row['account']}'";
			$users_mdb[]="{$row['account']}";
		}
		$same_str_old='';//滚服玩家 排除掉
		if(!empty($users)){
			$same_str_old=implode(',',$users);
			$same_str_old=$same_str_old=='' ? ' ' : " and account in ($same_str_old) " ;
		}
		//滚服玩家
		if($same_str_old!=''){
			for($i=$start_time;$i<=$end_time;$i+=86400){
				$data=array();
				$mdb->selectDb(MONGO_PERFIX.'4');
				foreach ($field_conf as $key=>$item){
					$condition=array($key=>array('$gte'=>$i,'$lt'=>$i+86400),'account'=>array('$in'=>$users_mdb));
					$data[$item]=$mdb->count('account_data', $condition);
				}
				//总帐号数（总注册连接数）
				$condition=array('create_time'=>array('$lt'=>$i+86400),'account'=>array('$in'=>$users_mdb));
				$data['total_account_count']=$mdb->count('account_data', $condition);
				//当天角色数
				$condition=array('creat_time'=>array('$gte'=>$i,'$lt'=>$i+86400),'account'=>array('$in'=>$users_mdb));
				$data['character_count']=$mdb->allCount('characters', $condition);
				//角色总数
				$condition=array('creat_time'=>array('$lt'=>$i+86400),'account'=>array('$in'=>$users_mdb));
				$data['total_character_count']=$mdb->allCount('characters', $condition);

				$data['date']=date('Ymd',$i);
				$data['time']=time();
				$fields=implode(',', array_keys($data));
				$values=implode(',', array_values($data));
				$sql="replace into stat_reg_gunfu ($fields) values ($values)";
				$mysqli->query($sql);
			}
		}

		break;

	case 'online':
		//统计在线信息
		$stat_table=array('name'=>'stat_online','field'=>'date');
		$log_table=array('name'=>'log_online','field'=>'time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$sql="select max(count) as max_count,min(count) as min_count,avg(count) as avg_count,
				max(ip) as max_ip,min(ip) as min_ip,avg(ip) as avg_ip from log_online where time>=$i and time<$i+86400";
			$data=$mysqli->findOne($sql);
			if($data){
				$data['date']=date('Ymd',$i);
				$data['time']=time();
				$fields=implode(',', array_keys($data));
				$values=implode(',', array_values($data));
				$sql="replace into stat_online ($fields) values ($values)";
				$mysqli->query($sql);
			}
		}
		break;

	case 'minute_loss':
		//分钟流失
		$field_time=array(
			's10'=>array(0,10),//数据库字段=>停留时间范围(0,10)
			's10_s30'=>array(10,30),
			's30_m1'=>array(30,60),
			'm1_m2'=>array(60,120),
			'm2_m3'=>array(120,180),
			'm3_m5'=>array(180,300),
			'm5_m10'=>array(300,600),
			'm10_m20'=>array(600,1200),
			'm20_m30'=>array(1200,1800),
			'm30_m40'=>array(1800,2400),
			'm40_m50'=>array(2400,3000),
			'm50_m60'=>array(3000,3600),
			'm60_m80'=>array(3600,4800),
			'm80_m100'=>array(4800,6000),
			'm100_m120'=>array(6000,7200),
			'h2_h3'=>array(7200,10800),
			'h3_h5'=>array(10800,18000),
			'h5'=>array(18000,86400),
		);
		$stat_table=array('name'=>'stat_minute_loss','field'=>'date');
		$log_table=array('name'=>'log_login','field'=>'login_time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			//初始化数组
			$data=array_combine(array_keys($field_time),array_fill(0,count($field_time),0));
			//秒、分、时各种流失
			$sql="select (logout_time-login_time) as time from log_login where logout_time>=$i and logout_time<$i+86400 and is_first=1";
			$result=$mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				foreach ($field_time as $field=>$item){
					if($row['time']>$item[0] &&  $row['time']<=$item[1]){
						$data[$field]++;
						break;
					}
				}
			}
			//标准流失,连续三天无登陆
			$mdb=new Mdb();
			$condition=array('loginTime'=>array('$lt'=>$i-86400*2));
			$data['standard_loss']=$mdb->allCount('characters', $condition);

			$data['date']=date('Ymd',$i);
			$data['time']=time();
			$fields=implode(',', array_keys($data));
			$values=implode(',', array_values($data));
			$sql="replace into stat_minute_loss ($fields) values ($values)";
			$mysqli->query($sql);
			//次日流失=第一天注册且第二天无登录的玩家÷新玩家×100%
			$condition=array('creat_time'=>array('$gte'=>$i-86400,'$lt'=>$i),'loginTime'=>array('$lt'=>$i));
			$morrow_loss=$mdb->allCount('characters', $condition);
			$sql=sprintf("update stat_minute_loss set morrow_loss=%d where date='%s'",$morrow_loss,date('Y-m-d',$i-86400));
			$mysqli->query($sql);;
		}
		break;

	case 'online_length':
		//每日在线时长
		$field_time=array(
			'm5'=>array(0,300),//数据库字段=>在线时间范围（单位：秒）(0,300)
			'm5_h1'=>array(300,3600),
			'h1_h2'=>array(3600,7200),
			'h2_h3'=>array(7200,10800),
			'h3_h4'=>array(10800,14400),
			'h4_h6'=>array(14400,21600),
			'h6_h8'=>array(21600,28800),
			'h8'=>array(28800,86400),
		);

		$task=new Task();
		$stat_table=array('name'=>'stat_online_length','field'=>'date');
		$log_table=array('name'=>'log_login','field'=>'login_time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$data=array_combine(array_keys($field_time),array_fill(0,count($field_time),0));
			$sql="select char_id,sum(t) as time from (
				select char_id,sum(logout_time)-sum(login_time) as t from log_login where
					login_time>=$i and login_time<$i+86400 and logout_time>0 and logout_time<$i+86400 group by char_id
				union select char_id,($i+86400-login_time) as t from log_login where
					login_time>=$i and login_time<$i+86400 and logout_time=0 group by char_id
				union select char_id,logout_time-$i as t from log_login where
					login_time<$i and logout_time>=$i and logout_time<$i+86400 group by char_id
				) as t group by char_id";
			$result=$mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				foreach ($field_time as $field=>$item){
					if($row['time']>$item[0] &&  $row['time']<=$item[1]){
						$data[$field]++;
						break;
					}
				}
			}
			$data['date']=date('Ymd',$i);
			$data['time']=time();
			$fields=implode(',', array_keys($data));
			$values=implode(',', array_values($data));
			$sql="replace into stat_online_length ($fields) values ($values)";
			$mysqli->query($sql);
		}
		break;

	case 'login':
		//登陆信息
		$task=new Task();
		$stat_table=array('name'=>'stat_login','field'=>'date');
		$log_table=array('name'=>'log_login','field'=>'login_time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$data=array();
			$where=" where login_time>=$i and login_time<$i+86400";
			//登陆次数
			$sql="select count(*) as count from log_login $where";
			$data['login_count']=$mysqli->count($sql);
			//登陆玩家
			$sql="select distinct char_id from log_login $where";
			$result=$mysqli->query($sql);
			$login_player=array();
			while ($result && $row=$result->fetch_assoc()){
				$login_player[]=$row['char_id'];
			}
			$data['login_player']=count($login_player);
			//首次登陆的玩家
			$sql="select  char_id from log_login $where and is_first=1";
			$result=$mysqli->query($sql);
			$login_player_first=array();
			while ($result && $row=$result->fetch_assoc()){
				$login_player_first[]=$row['char_id'];
			}
			//老玩家数：今天登陆的非新玩家数
			$data['old_player']=count(array_diff($login_player, $login_player_first));
			unset($login_player,$login_player_first);
			//活跃用户：玩家当天登录游戏时长大于等于180分钟(10800秒),则算做活跃用户
			$sql="select char_id,sum(t) as time from (
				select char_id,sum(logout_time)-sum(login_time) as t from log_login where
					login_time>=$i and login_time<$i+86400 and logout_time>0 and logout_time<$i+86400 group by char_id
				union select char_id,($i+86400-login_time) as t from log_login where
					login_time>=$i and login_time<$i+86400 and logout_time=0 group by char_id
				union select char_id,logout_time-$i as t from log_login where
					login_time<$i and logout_time>=$i and logout_time<$i+86400 group by char_id
				) as t group by char_id";
			$result=$mysqli->query($sql);
			$data['active_player']=0;
			$char_data=array();//在线时长大于等于5小时玩家
			while ($result && $row=$result->fetch_assoc()){
				if($row['time']>=10800){
					$data['active_player']++;
				}
				if($row['time']>=18000){
					$char_data[]=$row['char_id'];//在线时长大于等于5小时玩家
				}
			}
			//忠诚用户数：连续三天都有登陆记录,且三天登陆在线时长大于等于5小时,则算忠诚用户
			//一天前在线时长大于等于5小时玩家
			$diff_day=1;
			while ($char_data && $diff_day<=2){
				$char_id=implode(',', $char_data);
				$begin_time=$i-86400*$diff_day;
				$finish_time=$begin_time+86400;
				$sql="select char_id,sum(t) as time from (
					select char_id,sum(logout_time)-sum(login_time) as t from log_login where
						login_time>=$begin_time and login_time<$finish_time and logout_time>0 and logout_time<$finish_time and char_id in ($char_id) group by char_id
					union select char_id,($finish_time-login_time) as t from log_login where
						login_time>=$begin_time and login_time<$finish_time and logout_time=0 and char_id in ($char_id) group by char_id
					union select char_id,logout_time-$begin_time as t from log_login where
						login_time<$begin_time and logout_time>=$begin_time and logout_time<$finish_time and char_id in ($char_id) group by char_id
					) as t group by char_id having time>=18000";
				$result=$mysqli->query($sql);
				$char_data=array();
				while ($result && $row=$result->fetch_assoc()){
					$char_data[]=$row['char_id'];//在线时长大于等于5小时玩家
				}
				$diff_day++;
			}
			$data['loyal_player']=count($char_data);//忠诚玩家
			$data['date']=date('Ymd',$i);
			$data['time']=time();
			$fields=implode(',', array_keys($data));
			$values=implode(',', array_values($data));
			$sql="replace into stat_login ($fields) values ($values)";
			$mysqli->query($sql);
			unset($char_data);
		}
		break;

	case 'keep_day':
		//留存天数统计
		$keep_day=array(1,2,3,4,5,6,7,14,21,30);//留存天数
		$task=new Task();
		$stat_table=array('name'=>'stat_keep_day','field'=>'date');
		$log_table=array('name'=>'log_login','field'=>'login_time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$mdb=new Mdb();
		$fields=array('creat_time');
		//总体
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$data[0]=0;//初始化存储数据
			//当天首次登陆玩家
			$sql="select distinct char_id from log_login where login_time>=$i and login_time<$i+86400 and is_first=0";
			$result=$mysqli->query($sql);
			$char_data=array();//分库存储角色id
			while ($result && $row=$result->fetch_assoc()) {
				$char_id=floatval($row['char_id']);
				$char_data[$char_id%4][]=$char_id;
			}
			foreach ($char_data as $db=>$char){
				$mdb->selectDb(MONGO_PERFIX.''.$db);
				$condition=array('_id'=>array('$in'=>$char));
				foreach ($keep_day as $day){
					$create_time=$i-86400*$day;//创建角色时间
					if(date('Ymd',$create_time)>=date('Ymd',SERVER_OPEN_TIME)){
						$condition['creat_time']=array('$gte'=>$create_time,'$lt'=>$create_time+86400);
						$count=$mdb->count('characters', $condition);
						isset($data[$day]) ? $data[$day]+=$count : $data[$day]=$count;
					}
				}
			}
			foreach ($data as $day=>$count){
				$create_date=date('Y-m-d',$i-86400*$day);//创建角色日期
				$sql="select count(*) as count from stat_keep_day where date='$create_date'";
				$set_value="time=".time();
				$set_value.=$day ? ",day{$day}=$count" : '';
				if($mysqli->count($sql)){
					$sql="update stat_keep_day set $set_value where date='$create_date'";
					$mysqli->query($sql);
				}else{
					$sql="insert into stat_keep_day set $set_value,date='$create_date'";
					$mysqli->query($sql);
				}
			}
			unset($char_data,$data);
		}
		//滚服玩家
		$sql="select * from log_old_account";
		$result=$mysqli->query($sql);
		$users=$users_mdb=array();
		while($result && $row=$result->fetch_assoc()){
			$users[]="'{$row['account']}'";
			$users_mdb[]="{$row['account']}";
		}
		$same_str_old='';//滚服玩家
		if(!empty($users)){
			$same_str_old=implode(',',$users);
			$same_str_old=$same_str_old=='' ? ' ' : " and account in ($same_str_old) " ;
		}

		if($same_str_old!=''){
			//滚服玩家
			for($i=$start_time;$i<=$end_time;$i+=86400){
				$data[0]=0;//初始化存储数据
				//当天首次登陆玩家
				$sql="select distinct char_id from log_login where login_time>=$i and login_time<$i+86400 and is_first=0 $same_str_old";
				$result=$mysqli->query($sql);
				$char_data=array();//分库存储角色id
				while ($result && $row=$result->fetch_assoc()) {
					$char_id=floatval($row['char_id']);
					$char_data[$char_id%4][]=$char_id;
				}
				foreach ($char_data as $db=>$char){
					$mdb->selectDb(MONGO_PERFIX.''.$db);
					$condition=array('_id'=>array('$in'=>$char));
					foreach ($keep_day as $day){
						$create_time=$i-86400*$day;//创建角色时间
						if(date('Ymd',$create_time)>=date('Ymd',SERVER_OPEN_TIME)){
							$condition['creat_time']=array('$gte'=>$create_time,'$lt'=>$create_time+86400);
							$condition['account']=array('$in'=>$users_mdb);
							$count=$mdb->count('characters', $condition);
							isset($data[$day]) ? $data[$day]+=$count : $data[$day]=$count;
						}
					}
				}
				foreach ($data as $day=>$count){
					$create_date=date('Y-m-d',$i-86400*$day);//创建角色日期
					$sql="select count(*) as count from stat_keep_day_gunfu where date='$create_date'";
					$set_value="time=".time();
					$set_value.=$day ? ",day{$day}=$count" : '';
					if($mysqli->count($sql)){
						$sql="update stat_keep_day_gunfu set $set_value where date='$create_date'";
						$mysqli->query($sql);
					}else{
						$sql="insert into stat_keep_day_gunfu set $set_value,date='$create_date'";
						$mysqli->query($sql);
					}
				}
				unset($char_data,$data);
			}
		}
		break;

	case 'keep_ratio':
		//玩家留存率统计
		$task=new Task();
		$mdb=new Mdb();
		$condition=array('creat_time'=>array('$lt'=>strtotime('today')),'loginTime'=>array('$gt'=>0));
		$fields=array('creat_time','loginTime');
		//逐库遍历
		$data=array();
		for ($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=5000;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('characters', $condition, $fields, $result_condition);
				foreach ($list as $row){
					$create_time=strtotime(date('Ymd',$row['creat_time']));
					$login_time=strtotime(date('Ymd',$row['loginTime']));
					$diff_day=($login_time-$create_time)/86400;
					if($diff_day){
						$data[$diff_day]=isset($data[$diff_day]) ? $data[$diff_day]+1 : 1;
					}
				}
				if(count($list)<$limit){
					$limit=0;
					break;
				}
				$offset+=$limit;
			}
		}
		//清空数据库
		$sql="truncate stat_keep_ratio";
		$mysqli->query($sql);
		$date=date('Ymd',strtotime('yesterday'));
		$values=array();
		foreach ($data as $day=>$count){
			$values[]="($day,$count,$date)";
		}
		$values=implode(',', $values);
		$sql="replace into stat_keep_ratio (day,count,date) values $values";
		$mysqli->query($sql);
		break;

	case 'upgrade':
		//升级时间
		$stat_table=array('name'=>'stat_upgrade','field'=>'date');
		$log_table=array('name'=>'log_upgrade','field'=>'time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$mdb=new Mdb();
		$sql="select max(new_level) as max_level from log_upgrade";
		$list=$mysqli->findOne($sql);
		if(empty($list['max_level']))	exit('Not Level');
		$max_level=intval($list['max_level']);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$sum_time=$count=0;//初始化
			for ($level=2;$level<=$max_level;$level++){
				if($level==2){
					$sql="select char_id,time from log_upgrade where time>=$i and time<$i+86400 and new_level=$level and old_level=$level-1";
					$result=$mysqli->query($sql);
					while ($result && $row=$result->fetch_assoc()){
						$mdb->selectDb(MONGO_PERFIX.$row['char_id']%4);
						$character=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('_id'=>false,'creat_time'));
						$diff_time=$row['time']-$character['creat_time'];
						$count++;
						$sum_time+=$diff_time;
						!isset($min_time) || $diff_time<$min_time ? $min_time=$diff_time : '';
					}
				}else{
					//升级信息帅选出来并存到stat_upgrade_new、stat_upgrade_old两临时表中
					$sql='truncate stat_upgrade_new;';
					$mysqli->query($sql);
					$sql='truncate stat_upgrade_old;';
					$mysqli->query($sql);

					//升级时间
					$sql="insert into stat_upgrade_new select char_id,$level,time from log_upgrade where time>=$i and time<$i+86400 and new_level=$level and old_level=$level-1;";
					$mysqli->query($sql);
					//上一等级升级时间
					$sql="insert into stat_upgrade_old select char_id,$level,time from log_upgrade where time<$i+86400 and new_level=$level-1 and old_level=$level-2;";
					$mysqli->query($sql);
					//计算升级时间差
					$sql="select count(*) as count,sum(new.time-old.time) as sum_time,min(new.time-old.time) as min_time
						from stat_upgrade_new new,stat_upgrade_old old where new.char_id=old.char_id and new.level=old.level and new.level=$level";
					$list=$mysqli->findOne($sql);
					$count=empty($list['count']) ? 0 : $list['count'];
					$sum_time=empty($list['sum_time']) ? 0 : $list['sum_time'];
					!isset($min_time) || $list['min_time']<$min_time ? $min_time=$list['min_time'] : '';
				}

				//前一天数据
				$previous_date=date('Y-m-d',$i-86400);
				$sql="select * from stat_upgrade where date='$previous_date' and level=$level";
				$list=$mysqli->findOne($sql);
				if($list){
					//数据累计
					$sum_time+=$list['sum_time'];
					$count+=$list['count'];
					$min_time=$min_time<$list['min_time'] ? $min_time : $list['min_time'];
				}
				if($count){
					$date=date('Y-m-d',$i);
					$time=time();
					$sql="replace into stat_upgrade (date,level,min_time,count,sum_time,time) values
						('$date',$level,'$min_time',$count,$sum_time,$time)";
					$mysqli->query($sql);
				}
				if(isset($min_time))
				unset($min_time);
			}
		}
		break;

	case 'loader_time':
		//加载时间统计
		$stat_table=array('name'=>'stat_loader_time','field'=>'date');
		$start_date=$task->getStartDate($start_date, $stat_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'4');
		$loader_login_conf=array(
			array(1,3),
			array(4,6),
			array(7,15),
			array(16,'∞'),
		);
		$stop_login_conf=array(
			array(1,5),
			array(6,20),
			array(21,40),
			array(41,'∞'),
		);
		$loader_game_conf=array(
			array(1,20),
			array(21,30),
			array(31,50),
			array(51,90),
			array(90,'∞'),
		);
		$total_loader_game_conf=array(
			array(1,10),
			array(11,20),
			array(21,30),
			array(31,40),
			array(41,50),
			array(51,60),
			array(61,'∞'),
		);
		for($i=$start_time;$i<=$end_time;$i+=86400){
			//进入创建界面时间
			$loader_login_data=array();
			$condition=array('loader_login'=>array('$gte'=>$i,'$lt'=>$i+86400),'create_time'=>array('$gt'=>0));
			$list=$mdb->find('account_data', $condition, array('char_id','create_time','loader_login','_id'=>false));
			foreach ($list as $row){
				$type=isset($row['char_id']) ? 1 : 0;//0=非创号玩家，1=创号玩家
				$diff_time=$row['loader_login']-$row['create_time'];
				foreach ($loader_login_conf as $item){
					if($diff_time>=$item[0] && ($diff_time<=$item[1] || $item[1]=='∞')){
						$key=$item[0].'-'.$item[1];
						isset($loader_login_data[$type][$key]) ? $loader_login_data[$type][$key]++ : $loader_login_data[$type][$key]=1;
						break;
					}
				}
			}

			//创建页面停留时间
			$stop_login_data=array();
			$condition=array('loader_character'=>array('$gte'=>$i,'$lt'=>$i+86400),'loader_login'=>array('$gt'=>0));
			$list=$mdb->find('account_data', $condition, array('char_id','loader_character','loader_login','_id'=>false));
			foreach ($list as $row){
				$type=isset($row['char_id']) ? 1 : 0;//0=非创号玩家，1=创号玩家
				$diff_time=$row['loader_character']-$row['loader_login'];
				foreach ($stop_login_conf as $item){
					if($diff_time>=$item[0] && ($diff_time<=$item[1] || $item[1]=='∞')){
						$key=$item[0].'-'.$item[1];
						isset($stop_login_data[$type][$key]) ? $stop_login_data[$type][$key]++ : $stop_login_data[$type][$key]=1;
						break;
					}
				}
			}
			//进入游戏时间
			$loader_game_data=$total_loader_game_data=array();
			$condition=array('loader_game'=>array('$gte'=>$i,'$lt'=>$i+86400),'create_time'=>array('$gt'=>0),'loader_character'=>array('$gt'=>0));
			$list=$mdb->find('account_data', $condition, array('char_id','loader_character','loader_game','create_time','_id'=>false));
			foreach ($list as $row){
				$type=isset($row['char_id']) ? 1 : 0;//0=非创号玩家，1=创号玩家
				if($type==1){
					$diff_time=$row['loader_game']-$row['loader_character'];
					foreach ($loader_game_conf as $item){
						if($diff_time>=$item[0] && ($diff_time<=$item[1] || $item[1]=='∞')){
							$key=$item[0].'-'.$item[1];
							isset($loader_game_data[$type][$key]) ? $loader_game_data[$type][$key]++ : $loader_game_data[$type][$key]=1;
							break;
						}
					}

					//进入游戏总耗时
					$total_diff_time=$row['loader_game']-$row['create_time'];
					foreach ($total_loader_game_conf as $item){
						if($diff_time>=$item[0] && ($diff_time<=$item[1] || $item[1]=='∞')){
							$key=$item[0].'-'.$item[1];
							isset($total_loader_game_data[$type][$key]) ? $total_loader_game_data[$type][$key]++ : $total_loader_game_data[$type][$key]=1;
							break;
						}
					}
				}
			}

			foreach ($loader_login_data as $type=>$item){
				$loader_login_str=isset($loader_login_data[$type]) ? addslashes(json_encode($loader_login_data[$type])) : '';
				$stop_login_str=isset($stop_login_data[$type]) ? addslashes(json_encode($stop_login_data[$type])) : '';
				$loader_game_str=isset($loader_game_data[$type]) ? addslashes(json_encode($loader_game_data[$type])) : '';
				$total_loader_game_str=isset($total_loader_game_data[$type]) ? addslashes(json_encode($total_loader_game_data[$type])) : '';
				$date=date('Y-m-d',$i);
				$time=time();
				$sql="replace into stat_loader_time (date,type,loader_login_data,stop_login_data,loader_game_data,total_loader_game_data,time) value
					('$date',$type,'$loader_login_str','$stop_login_str','$loader_game_str','$total_loader_game_str',$time)";
				$mysqli->query($sql);
			}
		}
		break;
}