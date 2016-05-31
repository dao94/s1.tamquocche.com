<?php
/*
 * 核心数据备份
daily：每天备份产出的数据（充值、在线、道具、货币、登陆等流水）
monthly：按月备份充值
php core_backups.php --task=daily --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __CONFIG__.'log_config.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
$transit_path='/data/backup/transit/daily/';//存放目录
$transit_monthly_path='/data/backup/transit/monthly/';//存放目录
!is_dir($transit_path)&&mkdir($transit_path);
!is_dir($transit_monthly_path)&&mkdir($transit_monthly_path);

$prefix=SERVER_AGENT.'___'.strtoupper(SERVER_ID).'___';
$type="core_{$params['task']}";
switch ($params['task']){
	case 'daily':
		$stat_table=array('name'=>'stat_transit_flag','field'=>'date','where'=>" type='$type'");
		$log_table=array('name'=>'log_online','field'=>'time');
		$start_date=$task->getStartDate($start_date, $stat_table,$log_table);
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$mdb=new Mdb();
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$day=date('Ymd',$i);
			$orders_file=$transit_path.$prefix.$day.'___orders___'.$params['task'].'.txt';
			$coinslog_file=$transit_path.$prefix.$day.'___coinslog___'.$params['task'].'.txt';
			$members_file=$transit_path.$prefix.$day.'___members___'.$params['task'].'.txt';
			$loginlog_file=$transit_path.$prefix.$day.'___loginlog___'.$params['task'].'.txt';
			$onlines_file=$transit_path.$prefix.$day.'___onlines___'.$params['task'].'.txt';
			$saleslog_file=$transit_path.$prefix.$day.'___saleslog___'.$params['task'].'.txt';
			$itemslog_file=$transit_path.$prefix.$day.'___itemslog___'.$params['task'].'.txt';
			$targets_file=$transit_path.$prefix.$day.'___targets___'.$params['task'].'.txt';
			
			//充值记录
			$fp=fopen($orders_file,'w');
			if($fp){
				//1=人民币 2=台币 3=越南币 4=泰铢 5=美元
				$money_type=defined('MONEY_TYPE') ? MONEY_TYPE : 1;
				$money_ratio=array(1=>1,2=>1/5,3=>1/10,4=>1/5,5=>6);
				$rmb="round(money/100*{$money_ratio[$money_type]},2) as rmb";//折合人民币
				$sql="select account,char_name,order_id,round(money/100,2) as money,'$money_type',$rmb,gold,ts,is_first from pay_order 
					where ts>=$i and ts<$i+86400 and is_test=0";
				$result=$mysqli->query($sql);
				while ($row=$result->fetch_assoc()){
					fwrite($fp,implode("\t",$row)."\n");
				}
				fclose($fp);
			}
			
			//货币流水
			$fp=fopen($coinslog_file,'w');
			$account_list=array();
			if($fp){
				$limit=8000;
				$offset=0;
				while ($limit){
					$sql="select char_id,char_name,io,type,money_num,time from log_money where time>=$i and time<$i+86400 and money_type=3 limit $offset,$limit";
					$result=$mysqli->query($sql);
					$count=0;
					while ($row=$result->fetch_assoc()){
						$count++;
						if(isset($account_list[$row['char_id']])){
							$row['char_id']=$account_list[$row['char_id']];
						}else{
							$mdb->selectDb(MONGO_PERFIX.$row['char_id']%4);
							$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('account'));
							$account_list[$row['char_id']]=$char['account'];
							$row['char_id']=$account_list[$row['char_id']];
						}
						$row['type']=$money_type_conf[$row['type']];
						fwrite($fp,implode("\t",$row)."\n");
					}
					if($count<$limit){
						$limit=0;
						break;	
					}
					$offset+=$limit;
				}
				fclose($fp);
			}
			
			//商城流水
			$fp=fopen($saleslog_file,'w');
			if($fp){
				$limit=8000;
				$offset=0;
				while ($limit){
					$sql="select char_id,char_name,mall_type,item_id,item_num,money_type,money_num,time from log_mall 
						where time>=$i and time<$i+86400 limit $offset,$limit";
					$result=$mysqli->query($sql);
					$count=0;
					while ($row=$result->fetch_assoc()){
						$count++;
						if(isset($account_list[$row['char_id']])){
							$row['char_id']=$account_list[$row['char_id']];
						}else{
							$mdb->selectDb(MONGO_PERFIX.$row['char_id']%4);
							$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('account'));
							$account_list[$row['char_id']]=$char['account'];
							$row['char_id']=$account_list[$row['char_id']];
						}
						$row['item_id']=__($row['item_id']);
						$row['mall_type']=$mall_type_conf[$row['mall_type']];
						$row['money_type']=$mall_money_conf[$row['money_type']];
						fwrite($fp,implode("\t",$row)."\n");
					}
					if($count<$limit){
						$limit=0;
						break;	
					}
					$offset+=$limit;
				}
				fclose($fp);
			}
			
			//道具流水
			$fp=fopen($itemslog_file,'w');
			if($fp){
				$filter_type=array(2,4,6,7,10,12,14,23,38,62,66,65);
				$allow_type=array_diff(array_keys($item_type_conf),$filter_type);
				$allow_type=implode(',',$allow_type);
				
				$condition=" time>=$i and time<$i+86400 and type in ($allow_type) and io!=2 and bag_id=1";
				$sql="select min(id) as min_id,max(id) as max_id from log_items where $condition";
				$list=$mysqli->findOne($sql);
				$min_id=empty($list['min_id']) ? 0 : intval($list['min_id']);
				$max_id=empty($list['max_id']) ? 0 : intval($list['max_id']);
				$limit=50000;
				while ($max_id&&$min_id<=$max_id){
					$this_id=$min_id+$limit;
					$sql="select char_id,char_name,io,type,item_id,item_num,time from log_items where id>=$min_id and id<$this_id and $condition";
					$result=$mysqli->query($sql);
					while ($row=$result->fetch_assoc()){
						if(isset($account_list[$row['char_id']])){
							$row['char_id']=$account_list[$row['char_id']];
						}else{
							$mdb->selectDb(MONGO_PERFIX.$row['char_id']%4);
							$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('account'));
							$account_list[$row['char_id']]=$char['account'];
							$row['char_id']=$char['account'];
						}
						$row['type']=$item_type_conf[$row['type']];
						$row['item_id']=__($row['item_id']);
						fwrite($fp,implode("\t",$row)."\n");
					}
					$min_id+=$limit;
				}
				fclose($fp);
			}
			unset($account_list);
			
			//玩家列表
			$fp=fopen($members_file,'w');
			if($fp){
				//备份全部数据
				$fields=array('account','name','creat_time','loginTime','level');
				for($db=0;$db<4;$db++){
					$mdb->selectDb(MONGO_PERFIX.$db);
					$limit=3000;
					$offset=0;
					while ($limit){
						$members=$mdb->find('characters', array('account'=>array('$exists'=>true)), $fields, array('start'=>$offset,'limit'=>$limit));
						foreach ($members as $item){
							//剩余元宝
							$char_id=floatval($item['_id']);
							$list=$mdb->findOne('character_bag', array('_id'=>$char_id), array('_id'=>false,'moneyList'));
							$gold=empty($list['moneyList'][2]) ? 0 : intval($list['moneyList'][2]);
							
							//在线时长
							$sql="select sum(logout_time)-sum(login_time) as time from log_login where login_time>=$i and login_time<$i+86400 and char_id=$char_id and logout_time>0";
							$list=$mysqli->findOne($sql);
							$online_time=empty($list['time'])||$list['time']<0 ? 0 : intval($list['time']);
							
							//最后登录ip
							$sql="select ip from log_login where char_id=$char_id order by id desc limit 1";
							$list=$mysqli->findOne($sql);
							$last_ip=empty($list['ip']) ? '' : $list['ip'];
							!isset($item['loginTime']) ? $item['loginTime']=0 : '';
							fwrite($fp,$item['account']."\t".$item['name']."\t".$item['creat_time']."\t".$online_time."\t".
								$item['loginTime']."\t".$last_ip."\t".$item['level']."\t".$gold."\n");
						}
						if(count($members)<$limit){
							$limit=0;
							break;
						}
						$offset+=$limit;
					}
				}
				fclose($fp);
			}
			
			//登录日志
			$fp=fopen($loginlog_file,'w');
			if($fp){
				$limit=8000;
				$offset=0;
				while ($limit){
					$sql="select account,char_name,login_time,logout_time,ip from log_login 
						where login_time>=$i and login_time<$i+86400 limit $offset,$limit";
					$result=$mysqli->query($sql);
					$count=0;
					while ($row=$result->fetch_assoc()){
						$count++;
						fwrite($fp,implode("\t",$row)."\n");
					}
					if($count<$limit){
						$limit=0;
						break;	
					}
					$offset+=$limit;
				}
				fclose($fp);
			}
			
			//在线人数
			$fp=fopen($onlines_file,'w');
			if($fp){
				$sql="select count,time from log_online where time>=$i and time<$i+86400";
				$result=$mysqli->query($sql);
				while ($row=$result->fetch_assoc()){
					$row['time']=date('Y-m-d H:i',$row['time']);
					fwrite($fp,implode("\t",$row)."\n");
				}
				fclose($fp);
			}
			
			$sql="replace into stat_transit_flag (type,date,flag) value ('$type',$day,$i);";
			$mysqli->query($sql);
		}
		break;
		
	case 'monthly':
		//每月1号备份上一个的全部充值
		$day=date('Ymd',mktime(0,0,0,date('m'),1,date('Y')));
		$start_time=mktime(0,0,0,date('m')-1,1,date('Y'));
		$end_time=strtotime($day);
		$orders_file=$transit_monthly_path.$prefix.$day.'___orders___'.$params['task'].'.txt';
		//充值记录
		$fp=fopen($orders_file,'w');
		if($fp){
			$money_type=defined('MONEY_TYPE') ? MONEY_TYPE : 'RMB';
			$money_ratio=array('RMB'=>1,'TWD'=>1/5,'VND'=>1/10,'THB'=>1/5,'USD'=>4.5);
			$rmb="round(money/100*{$money_ratio[$money_type]},2) as rmb";//折合人民币
			$sql="select account,char_name,order_id,round(money/100,2) as money,'$money_type',$rmb,gold,ts,is_first from pay_order 
				where ts>=$start_time and ts<$end_time and is_test=0";
			$result=$mysqli->query($sql);
			while ($row=$result->fetch_assoc()){
				fwrite($fp,implode("\t",$row)."\n"); 
			}
			fclose($fp);
		}
		break;
}