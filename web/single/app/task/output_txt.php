<?php
/*
 * 输出txt文件
 * log_items：道具流水
 * left_items:道具库存
 * php output_txt.php --task=log_items --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __CONFIG__.'log_config.php';
include __CONFIG__.'game_config.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['start_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'log_items':
		SERVER_AGENT!='TQC'&&exit('Permission denied');
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date);
		$path='/data/logs/game_item_log/';
		!is_dir($path)&&mkdir($path,0777,true);
		$prefix='lwjs_';
		!is_dir($path)&&exit('Directory does not exist');
		$mdb=new Mdb();
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$day=date('Ymd',$i+86400);
			$itemslog_file=$path.$prefix.$day.'.log';
			$fp=fopen($itemslog_file,'a+');
			if($fp){
				$filter_type=array(2,4,6,7,10,12,14,23,38,62,66,65);
				$allow_type=array_diff(array_keys($item_type_conf),$filter_type);
				$allow_type=implode(',',$allow_type);

				$condition=" time>=$i and time<$i+86400 and type in ($allow_type) and io!=2 and bag_id=1";
				$sql="select min(id) as min_id,max(id) as max_id from log_items where $condition";
				$list=$mysqli->findOne($sql);
				$min_id=empty($list['min_id']) ? 0 : intval($list['min_id']);
				$max_id=empty($list['max_id']) ? 0 : intval($list['max_id']);
				$limit=30000;
				while ($max_id&&$min_id<=$max_id){
					$this_id=$min_id+$limit;
					$sql="select char_id,char_name,io,type,item_id,item_num,time from log_items where id>=$min_id and id<$this_id and $condition";
					$result=$mysqli->query($sql);
					while ($row=$result->fetch_assoc()){
						if(isset($account_list[$row['char_id']])){
							$row['account']=$account_list[$row['char_id']];
						}else{
							$mdb->selectDb(MONGO_PERFIX.$row['char_id']%4);
							$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('account'));
							$account_list[$row['char_id']]=$char['account'];
							$row['account']=$char['account'];
						}
						$row['type_desc']=$item_type_conf[$row['type']];
						$row['count']=0;
						switch ($row['type']){
							case 15:
								$row['type']=2;//购买
								break;
							default:
								$row['type']=$row['io']==1 ? 1 : 3;
								break;
						}
						$row['item_name']=__($row['item_id']);
						$record=$row['account']."\t".$row['char_name']."\t".$row['item_id']."\t".$row['item_name']."\t";
						$record.=$row['item_num']."\t".$row['type']."\t".$row['type_desc']."\t".$row['count']."\t".$row['time']."\n";
						fwrite($fp,$record);
					}
					$min_id+=$limit;
				}
				fclose($fp);
			}
			unset($account_list);
		}
		break;

	case 'left_items':
		//道具库存
		SERVER_AGENT!='TQC'&&exit('Permission denied');
		$path='/data/logs/game_item_left/';
		!is_dir($path)&&mkdir($path,0777,true);
		$prefix='lwjs_';
		!is_dir($path)&&exit('Directory does not exist');
		$day=date('Ymd',time());
		$left_item_file=$path.$prefix.$day.'.log';
		$fp=fopen($left_item_file,'a+');
		(!$fp)&&exit('File create failed');
		$mdb=new Mdb();
		$character_list=array();
		$time=time();
		foreach ($bag_conf as $bag_id=>$bag_name){
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$limit=1000;
				$offset=0;
				while ($limit>0){
					$result_condition=array('start'=>$offset,'limit'=>$limit);
					$list=$mdb->find('character_item_'.$bag_id, array(), array('item_l'), $result_condition);
					foreach ($list as $row){
						$char_id=(string)$row['_id'];
						if(isset($character_list[$char_id])){
							$account=$character_list[$char_id]['account'];
							$name=$character_list[$char_id]['name'];
						}else{
							$info=$mdb->findOne('characters', array('_id'=>floatval($char_id)), array('_id'=>false,'account','name'));
							$character_list[$char_id]=$info;
							$account=empty($info['account']) ? '' : $info['account'];
							$name=empty($info['name']) ? '' : $info['name'];
						}
						$item_list=empty($row['item_l']) ? array() : $row['item_l'];
						$data=array();
						foreach ($item_list as $item){
							$item_id=(string)$item[1];
							isset($data[$item_id][$item[4]]) ? $data[$item_id][$item[4]]+=$item[2] : $data[$item_id][$item[4]]=$item[2];
						}
						foreach ($data as $item_id=>$items){
							foreach ($items as $bind=>$count){
								$is_bind=$bind==1 ? 0 : 1;
								$type=1;
								$type_desc='';
								$duration=0;//表示装备耐久度,或者到期时间
								$record=$item_id."\t".__($item_id)."\t".$is_bind."\t".$count."\t";
								$record.=$type."\t".$type_desc."\t".$duration."\t".$time."\n";
								fwrite($fp,$record);
							}
						}
					}
					if(count($list)<$limit){
						$limit=0;
						break;
					}
					$offset+=$limit;
				}
			}
		}
		fclose($fp);
		unset($character_list);
		break;
}