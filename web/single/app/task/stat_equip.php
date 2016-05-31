<?php
/*
 * 装备升阶分析  human_equip
 * 坐骑分析  stat_ride
 * 注：
    数量：当日玩家所装备的n级n品质的装备数量总和
    百分比：装备数量/当日30级以上玩家*12*100%
 * php d:\phpNew\single\app\task\stat_equip.php --task=human_equip
 */
 define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Item.class.php';
include __CONFIG__.'game_config.php';

if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');

array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$task->name=$params['task'];
$mysqli=$task->mysqli();

switch ($params['task']){
	case 'stat_ride':
		$mdb=new Mdb();
		$array_ride=$array_show=array();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=500;
			$offset=0;
			$fields = array('rides','show');
			while($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list = $mdb->find('ride', array(), $fields, $result_condition);
				foreach($list as $items){
					foreach($items['rides'] as $type=>$row){
						$level=$row[0];   //如 陆行系_1阶  $array_ride[$type][$level]
						isset($array_ride[$type+1][$level]) ? $array_ride[$type+1][$level]++ : $array_ride[$type+1][$level]=1;
					}
					if(isset($items['show'])){
						foreach($items['show'] as $row){
							$is_Special=isset($row['isDefault']) ? $row['isDefault'] : 1; //1为默认非特殊外观
							$type=isset($row['series']) ? $row['series'] : 1;  //默认为为陆行系
							$show_level=isset($row['rideLv']) ? $row['rideLv'] : 1;  //默认等级为1
							if($is_Special!=1){
								isset($array_show[$type][$show_level]) ? $array_show[$type][$show_level]++ : $array_show[$type][$show_level]=1;
							}

						}
					}

				}

				if(count($list) < $limit) break;
				$offset +=$limit;

			}
		}

		$sql_field='';//mysql字段赋值
		foreach ($array_ride as $type=>$items){
			//入库前数据处理  ride
			ksort($items);
			$trans = array_flip($items);//键值 互换
			$sql_field.=",type=".($type+1);//陆行系
			$sql_field.=",ride_count=".array_sum($items);//总数量
			$sql_field.=",max_level=".max($trans);//最大阶
			$sql_field.=sprintf(",ride_remark='%s'",json_encode($items));  //json

			foreach($array_show as $type=>$itemsShow){
				//入库前数据处理  show
				ksort($itemsShow);
				$sql_field.=sprintf(",show_remark='%s'",json_encode($itemsShow));  //外观
			}

			if($sql_field){
				$mysqli=new DbMysqli();
				$date=date('Y-m-d',strtotime('yesterday'));
				$time=time();
				$sql="replace into stat_ride set date='$date' $sql_field,time=$time";
				$mysqli->query($sql);
			}
		}


		break;

	case 'human_equip':
		//人物装备
		$mdb=new Mdb();
		$Item=new Item();
		$mysqli=new DbMysqli();
		$array_equip=array();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$limit=500;
		$offset=0;
		$allow_player=0;
		$condition=array('attr.level'=>array('$gte'=>30));
		$fields = array('equip');
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $mdb->find('human_offline',$condition, $fields, $result_condition);
			foreach($list as $items){
				$allow_player++;
				foreach($items['equip'] as $item){
					if($item['item']){
						$item_id=$item['item'][0];
						$colour=(int)$Item->getColour($item_id);
						$level=(int)$Item->getLevel($item_id);
						$part=(int)$Item->getPart($item_id);
						//13武饰 14翅膀 15时装除外
						if($part!=13 && $part!=14 && $part!=15){
							//30 40 50 60 套装
							if(($level>=30 && $level!=31) && ($colour==4 || $colour==5)){
								isset($array_equip[$level][$colour][$part]) ? $array_equip[$level][$colour][$part]++ : $array_equip[$level][$colour][$part]=1;
							}
						}

					}
				}
			}
			if(count($list) < $limit) break;
			$offset +=$limit;
		}

		foreach ($array_equip as $level=>$items){
			//等级
			ksort($items);
			foreach($items as $colour=>$sets){
				//颜色
				ksort($sets);
				foreach($sets as $part=>$value){
					$sql_field='';//mysql字段赋值
					$sql_field .=",level=".$level;
					$sql_field .=",colour=".$colour;
					$sql_field .=",part=".$part;
					$sql_field .=",count=".$value;
					$sql_field .=",allow_player=".$allow_player;
					if($sql_field){
						$date=date('Y-m-d',strtotime('yesterday'));
						$time=time();
						$sql="replace into stat_equip set date='$date' $sql_field,time=$time";
						$mysqli->query($sql);
					}

				}
			}
		}

		break;
}
?>