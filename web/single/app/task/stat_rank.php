<?php

/*
 * 排行榜存储到mysql 每天凌晨0点执行  鲜花排行中午12点执行
 * stat_rank表 action字段说明：personal=个人排行 money=货币排行 faction=帮派排行 pet=宠物排行 equip=装备排行 flower=鲜花排行
 * php stat_rank.php --task=personal
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __CONFIG__ . 'game_config.php';

if(!isset($argc) || $argc<2) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$task->name=$params['task'];
$mysqli=$task->mysqli();
$date=date('Y-m-d',strtotime('yesterday'));
$limit=500;//只抓取前500名
switch ($params['task']){
	case 'personal':
		//个人排行
		$type_conf=array(
			'lvl'=>'等级排行',
			'fight'=>'战力排行',
		);
		$mdb=new Mdb();
		foreach ($type_conf as $type=>$name){
			$data=array();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$result_condition=array('limit'=>$limit,'sort'=>array($type=>-1));
			$list=$mdb->find('human_rank', array(), array("$type"), $result_condition);
			foreach ($list as $key=>$row){
				$char_id=floatval($row['_id']);
				$row['num']=$key+1;//名次
				$mdb->selectDb(MONGO_PERFIX.''.$char_id%4);
				$condition=array('_id'=>$char_id);
				$fields=array('name','account','camp','occ','level','serverId');
				$character=$mdb->findOne('characters', $condition, $fields);
				//帮派信息
				$mdb->selectDb(MONGO_PERFIX.'5');
				//存在有bug，暂时不处理
				$condition=array(
					'state'=>1,
					'memberList'=>array('$elemMatch'=>array('$all'=>array($char_id)))
				);
				$faction=$mdb->findOne('faction', $condition, array('name'));
				$row['faction_name']=$faction ? $faction['name'] : '';
				$data[]=array_merge($row,$character);
			}
			if($data){
				//入库
				$rank=addslashes(json_encode($data));
				$time=time();
				$sql="insert into stat_rank (date,action,type,rank,time) value ('$date','{$params['task']}','$type','$rank',$time)";
				$mysqli->query($sql);
			}
		}
		break;
		
	case 'money':
		//货币排行
		$type_conf=array(
			0=>'铜币排行',
			1=>'铜券排行',
			2=>'元宝排行',
			3=>'礼券排行',
		);
		$mdb=new Mdb();
		foreach ($type_conf as $type=>$name){
			$data=array();
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$result_condition=array('start'=>0,'limit'=>$limit,'sort'=>array("moneyList.$type"=>-1));
				$list=$mdb->find('character_bag', array("moneyList.$type"=>array('$gt'=>0)), array('moneyList.$.'.$type), $result_condition);
				foreach ($list as $row){
					$data[$row['_id']]=$row['moneyList'][0];
				}
			}
			arsort($data);
			$data=array_slice($data,0,$limit,true);
			$num=0;
			foreach ($data as $char_id=>$count){
				$num++;
				$row=array('num'=>$num);
				$mdb->selectDb(MONGO_PERFIX.$char_id%4);
				$condition=array('_id'=>floatval($char_id));
				$list=$mdb->findOne('characters', $condition, array('name','account','occ','level','loginTime','creat_time'));
				$row=array_merge($row,$list);
				//其他货币信息
				$list=$mdb->findOne('character_bag', $condition, array('moneyList'));
				$row=array_merge($row,$list);
				$data[$char_id]=$row;
			}
			if($data){
				//入库
				$rank=addslashes(json_encode($data));
				$time=time();
				$sql="insert into stat_rank (date,action,type,rank,time) value ('$date','{$params['task']}','$type','$rank',$time)";
				$mysqli->query($sql);
			}
		}
		break;

	case 'faction':
		//帮派排行
		$type_conf=array(
			'faction'=>'帮派排行',
		);
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		foreach ($type_conf as $type=>$name){
			$data=array();
			$result_condition=array('limit'=>$limit,'sort'=>array('copyLevel'=>-1));
			$list=$mdb->find('faction_rank', array(), array(), $result_condition);
			foreach ($list as $key=>$row){
				$row['num']=$key+1;//名次
				$mdb->selectDb(MONGO_PERFIX.'5');
				$faction=$mdb->findOne('faction', array('_id'=>$row['_id']), array('presidentId'));
				if($faction){
					$row['president_id']=$faction['presidentId'];
					$mdb->selectDb(MONGO_PERFIX.$faction['presidentId']%4);
					$char=$mdb->findOne('characters', array('_id'=>floatval($faction['presidentId'])), array('name'));
					$row['president_name']=$char ? $char['name'] : '';
				}
				$data[]=$row;
			}
			if($data){
				//入库
				$rank=addslashes(json_encode($data));
				$time=time();
				$sql="insert into stat_rank (date,action,type,rank,time) value ('$date','{$params['task']}','$type','$rank',$time)";
				$mysqli->query($sql);
			}
		}
		break;

	case 'pet':
		//宠物排行
		$type_conf=array(
			'fight'=>'战力排行',
			'attack'=>'攻击排行',
			'defense'=>'防御排行',
			'maxHp'=>'生命排行',
		);
		$mdb=new Mdb();
		foreach ($type_conf as $type=>$name){
			$data=array();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$result_condition=array('limit'=>$limit,'sort'=>array($type=>-1));
			$list=$mdb->find('pet_rank', array(), array("$type",'owner','name'), $result_condition);
			foreach ($list as $key=>$row){
				$char_id=floatval($row['owner']);
				$row['num']=$key+1;//名次
				$fields=array('attr.name','pets.petList.$');
				$condition=array('_id'=>$char_id,'pets.petList'=>array('$elemMatch'=>array('id'=>floatval($row['_id']))));
				$character=$mdb->findOne('human_offline', $condition, $fields);
				if($character){
					$row['char_name']=$character['attr']['name'];
					$row['original_name']=empty($character['pets']['petList'][0]['originalName']) ? '' : $character['pets']['petList'][0]['originalName']; 
				}
				$data[]=$row;
			}
			if($data){
				//入库
				$rank=addslashes(json_encode($data));
				$time=time();
				$sql="insert into stat_rank (date,action,type,rank,time) value ('$date','{$params['task']}','$type','$rank',$time)";
				$mysqli->query($sql);
			}
		}
		break;

	case 'equip':
		//装备排行
		$type_conf=array(
			'weapon'=>'武器排行',
			'armor'=>'防具排行',
			'trinket'=>'饰品排行',
		);
		include __CLASSES__.'Item.class.php';
		$mdb=new Mdb();
		$item=new Item();
		foreach ($type_conf as $type=>$name){
			$data=array();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$count=$mdb->count($type.'_rank');
			$result_condition=array('limit'=>$limit,'sort'=>array('fight'=>-1));
			$fields=array('_id'=>false,'id','occ','name','fight');
			$list=$mdb->find($type.'_rank', array(), array(), $result_condition);
			foreach ($list as $key=>$row){
				$char_id=floatval($row['id']);
				$row['num']=$key+1;//名次
				$fields=array('attr.name','equip');
				$condition=array('_id'=>$char_id);
				$character=$mdb->findOne('human_offline', $condition, $fields);
				$row['attr']=array();
				if($character){
					$attr=isset($character['equip'][$row['part']-1]['item'][3]) ? $character['equip'][$row['part']-1]['item'][3] : '';//附加属性
					$attr=$item->getAttr(json_decode($attr,true));
					$row['attr']=$attr;
					$row['char_name']=$character['attr']['name']; 
				}
				$row['occ_name']=isset($occ_conf[$row['occ']]) ? $occ_conf[$row['occ']] : $row['occ'];
				$row['part']=isset($row['part'])&&isset($part_conf[$row['part']-1]) ? $part_conf[$row['part']-1] : '';
				$data[]=$row;
			}
			if($data){
				//入库
				$rank=addslashes(json_encode($data));
				$time=time();
				$sql="insert into stat_rank (date,action,type,rank,time) value ('$date','{$params['task']}','$type','$rank',$time)";
				$mysqli->query($sql);
			}
		}
		break;
		
	case 'flower':
		//鲜花排行,中午12点执行
		$date=intval(date('Ymd',time()));
		$hour=date('H',time());
		date('H',time())!=12&&exit('Time out');
		$type_conf=array(
			1=>'风流帅哥',
			0=>'绝色美女',
		);
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		foreach ($type_conf as $type=>$name){
			$data=array();
			$count=$mdb->count('flower_rank');
			$result_condition=array('limit'=>$limit,'sort'=>array('num'=>-1));
			$list=$mdb->find('flower_rank', array('date'=>$date,'gender'=>$type), array(), $result_condition);
			foreach ($list as $key=>$row){
				$char_id=floatval($row['_id']);
				$row['charm']=$row['num'];//魅力值
				$row['num']=$key+1;//名次
				$character=$mdb->findOne('human_offline', array('_id'=>$char_id), array('attr.name','attr.faction'));
				$row['char_name']=empty($character['attr']['name']) ? '' : $character['attr']['name'];
				$row['faction']=empty($character['attr']['faction']) ? '' : $character['attr']['faction'];
				$data[]=$row;
			}
			if($data){
				//入库
				$rank=addslashes(json_encode($data));
				$time=time();
				$sql="insert into stat_rank (date,action,type,rank,time) value ('$date','{$params['task']}','$type','$rank',$time)";
				$mysqli->query($sql);
			}
		}
		break;
}