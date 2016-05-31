<?php
/* 统计脚本  策划数据
 *		php cehua_data.php --task=wing
 *		php cehua_data.php --task=gem
 *		php cehua_data.php --task=god
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
$task->name=$params['task'];
$mysqli=$task->mysqli();

$data=array();
$mdb=new Mdb();
switch ($params['task']){
	case 'wing':
		$mdb->selectDb(MONGO_PERFIX.'5');
		$fields=array('advLvl');
		$start_time=SERVER_OPEN_TIME;//开服时间
		//6月8日 时间
		$date_time=strtotime('2014-06-08');
		if($start_time<=$date_time){
			//翅膀分析
			$day_7=$start_time+86400*7;
			$start_time=$day_7;
			$end_time=time();
			$adv_level='';
			for($i=$start_time;$i<$end_time;$i+=86400){
				$sql="select distinct char_id from log_login where login_time>=$i and login_time<($i+86400) ";
				$result=$mysqli->query($sql);
				while($result&&$row=$result->fetch_assoc()){
					$char_id=floatval($row['char_id']);//角色id
					$mdb->selectDb(MONGO_PERFIX.$char_id%4);
					$fields=array('advLvl');
					$list=$mdb->findOne('wing', array('_id'=>$char_id),$fields);
					if(isset($list['advLvl'])&&!empty($list['advLvl'])){
						//阶数统计
						$adv_level=$list['advLvl'];
						$data['level'][$adv_level]=empty($data['level'][$adv_level]) ? $data['level'][$adv_level]=1 : ++$data['level'][$adv_level];
					}
				}
			}
			//总注册玩家
			$condition=array('creat_time'=>array('$lt'=>time()));
			$data['total_character_count']=$mdb->allCount('characters', $condition);
			if(isset($data['level'])){
				ksort($data['level']);
				//输出结果
				$str='';
				$str.=SERVER_AGENT.'_'.SERVER_ID."\t";
				$str.=$data['total_character_count']."\t";
				foreach($data['level'] as $value){
					$str.=$value."\t";
				}
				$str .="\n";
				echo $str;
			}

		}
		break;
	case 'gem':
		//宝石雕刻
		$mdb->selectDb(MONGO_PERFIX.'5');
		$fields=array('advLvl');
		$start_time=SERVER_OPEN_TIME;//开服时间
		//6月8日 时间
		$date_time=strtotime('2014-06-08');
		if($start_time<=$date_time){
			//不计算开服时间7天内的数据
			$day_7=$start_time+86400*7;
			$condition=array('saveTime'=>array('$gt'=>$day_7),'attr.level'=>array('$gte'=>40));
			$limit=500;
			$offset=0;
			$fields = array('equip');
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list = $mdb->find('human_offline',$condition, $fields, $result_condition);
				foreach($list as $items){
					foreach($items['equip'] as $key=>$part_list){
						$part=$key+1; //部位
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
			//总注册玩家
			$condition=array('creat_time'=>array('$lt'=>time()));
			$data['total_character_count']=$mdb->allCount('characters', $condition);
			if(isset($data['gem_diaoke'])){
				ksort($data['gem_diaoke']);
				//输出结果
				foreach($data['gem_diaoke'] as $part=>$items){
					ksort($items);
					$str='';
					$str.=SERVER_AGENT.'_'.SERVER_ID."\t";
					$str.=$data['total_character_count']."\t";
					foreach($items as $value){
						$str.=$value."\t";
					}
					$str .="\n";
					echo $str;
				}
			}

		}
		break;
	case 'god':
		$mdb->selectDb(MONGO_PERFIX.'5');
		$fields=array('advLvl');
		$start_time=SERVER_OPEN_TIME;//开服时间
		//6月8日 时间
		$date_time=strtotime('2014-06-08');
		if($start_time<=$date_time){
			$day_7=$start_time+86400*7;
			$limit=500;
			$offset=0;
			$condition=array('saveTime'=>array('$gt'=>$day_7),'attr.level'=>array('$gte'=>50));
			$fields = array('equip');
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list = $mdb->find('human_offline',$condition, $fields, $result_condition);
				foreach($list as $items){
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
			//总注册玩家
			$condition=array('creat_time'=>array('$lt'=>time()));
			$data['total_character_count']=$mdb->allCount('characters', $condition);
			if(isset($data['shenhua'])){
				ksort($data['shenhua']);
				//输出结果
				foreach($data['shenhua'] as $items){
					ksort($items);
					$str='';
					$str.=SERVER_AGENT.'_'.SERVER_ID."\t";
					$str.=$data['total_character_count']."\t";
					foreach($items as $value){
						$str.=$value."\t";
					}
					$str .="\n";
					echo $str;
				}
			}


		}
		break;
}

?>
