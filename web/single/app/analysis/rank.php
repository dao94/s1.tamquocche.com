<?php
//排行榜
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__. 'Mdb.class.php';
include __CONFIG__ . 'game_config.php';

$action_conf=array(
	'personal'=>__('个人排行'),
	'money'=>__('货币排行'),
	'faction'=>__('帮派排行'),
	'pet'=>__('宠物排行'),
	'equip'=>__('装备排行'),
	'flower'=>__('鲜花排行'),
	'arena'=>__('竞技场排行'),
);
$action=empty($_GET['action']) ? 'personal' : trim($_GET['action']);
$type=isset($_GET['type']) ? trim($_GET['type']) : '';
$date=empty($_GET['date']) ? date('Y-m-d') : my_escape_string(trim($_GET['date']));
$limit=empty($_GET['limit']) ? 100 : intval(trim($_GET['limit']));
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'action'=>$action,
	'date'=>$date,
	'limit'=>$limit,
);

$data=$type_conf=array();
$list_rows=$limit>20 ? 20 : $limit;
$page='';
switch ($action){
	case 'personal':
		//个人排行
		$type_conf=array(
			'fight'=>__('战力排行'),
			'lvl'=>__('等级排行'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 'fight';
		if($date==date('Y-m-d')){
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$count=$mdb->count('human_rank');
			$total_rows=$count>$limit ? $limit : $count;
			$p=new Page($total_rows,$list_rows);
			$result_condition=array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array($type=>-1,'lvl_time'=>1));
			$list=$mdb->find('human_rank', array(), array("$type"), $result_condition);
			foreach ($list as $key=>$row){
				$char_id=floatval($row['_id']);
				$row['num']=($p->nowPage-1)*$list_rows+$key+1;//名次
				$mdb->selectDb(MONGO_PERFIX.''.$char_id%4);
				$condition=array('_id'=>$char_id);
				$fields=array('name','account','camp','occ');
				$character=(array)$mdb->findOne('characters', $condition, $fields);
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
			$page=$p->show();
		}
		break;
		
	case 'money':
		//货币排行
		$type_conf=array(
			0=>__('铜币排行'),
			1=>__('铜券排行'),
			2=>__('元宝排行'),
			3=>__('礼券排行'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 2;
		if($date==date('Y-m-d')){
			$mdb=new Mdb();
			$data=array();
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$result_condition=array('start'=>0,'limit'=>300,'sort'=>array("moneyList.$type"=>-1));
				$list=$mdb->find('character_bag', array("moneyList.$type"=>array('$gt'=>0)), array('moneyList.$.'.$type), $result_condition);
				foreach ($list as $row){
					$data[$row['_id']]=$row['moneyList'][0];
				}
			}
			arsort($data);
			$p=new Page(count($data),$list_rows);
			$data=array_slice($data,$p->firstRow,$p->listRows,true);
			$num=$p->firstRow;
			foreach ($data as $char_id=>$count){
				$num++;
				$row=array('num'=>$num);
				$mdb->selectDb(MONGO_PERFIX.$char_id%4);
				$condition=array('_id'=>floatval($char_id));
				$list=(array)$mdb->findOne('characters', $condition, array('name','account','level','loginTime','creat_time'));
				$row=array_merge($row,$list);
				//其他货币信息
				$list=(array)$mdb->findOne('character_bag', $condition, array('moneyList'));
				$row=array_merge($row,$list);
				$data[$char_id]=$row;
			}
			$page=$p->show();
		}
		break;

	case 'faction':
		//帮派排行
		$type_conf=array(
			'faction'=>__('帮派排行'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 'faction';
		if($date==date('Y-m-d')){
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$count=$mdb->count('faction_rank');
			$total_rows=$count>$limit ? $limit : $count;
			$p=new Page($total_rows,$list_rows);
			$result_condition=array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array('copyLevel'=>-1));
			$list=$mdb->find('faction_rank', array(), array(), $result_condition);
			foreach ($list as $key=>$row){
				$row['num']=($p->nowPage-1)*$list_rows+$key+1;//名次
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
			$page=$p->show();
		}
		break;

	case 'pet':
		//宠物排行
		$type_conf=array(
			'fight'=>__('战力排行'),
			'attack'=>__('攻击排行'),
			'defense'=>__('防御排行'),
			'maxHp'=>__('生命排行'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 'fight';
		if($date==date('Y-m-d')){
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$count=$mdb->count('pet_rank');
			$total_rows=$count>$limit ? $limit : $count;
			$p=new Page($total_rows,$list_rows);
			$result_condition=array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array($type=>-1));
			$list=$mdb->find('pet_rank', array(), array("$type",'owner','name'), $result_condition);
			foreach ($list as $key=>$row){
				$char_id=floatval($row['owner']);
				$row['num']=($p->nowPage-1)*$list_rows+$key+1;//名次
				$fields=array('attr.name','pets.petList.$');
				$condition=array('_id'=>$char_id,'pets.petList'=>array('$elemMatch'=>array('id'=>floatval($row['_id']))));
				$character=$mdb->findOne('human_offline', $condition, $fields);
				if($character){
					$row['char_name']=$character['attr']['name'];
					$row['original_name']=empty($character['pets']['petList'][0]['originalName']) ? '' : $character['pets']['petList'][0]['originalName']; 
				}
				$data[]=$row;
			}
			$page=$p->show();
		}
		break;

	case 'equip':
		//装备排行
		$type_conf=array(
			'weapon'=>__('武器排行'),
			'armor'=>__('防具排行'),
			'trinket'=>__('饰品排行'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? $type : 'weapon';
		if($date==date('Y-m-d')){
			include __CLASSES__.'Item.class.php';
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$count=$mdb->count($type.'_rank');
			$total_rows=$count>$limit ? $limit : $count;
			$p=new Page($total_rows,$list_rows);
			$result_condition=array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array('fight'=>-1));
			$list=$mdb->find($type.'_rank', array(), array(), $result_condition);
			$item=new Item();
			foreach ($list as $key=>$row){
				$char_id=floatval($row['id']);
				$row['num']=($p->nowPage-1)*$list_rows+$key+1;//名次
				$fields=array('attr.name','equip');
				$condition=array('_id'=>$char_id);
				$character=$mdb->findOne('human_offline', $condition, $fields);
				$row['attr']=array();
				if($character){
					$attr=$character['equip'][$row['part']-1]['item'][3];//附加属性
					$attr=$item->getAttr(json_decode($attr,true));
					$row['attr']=$attr;
					$row['char_name']=$character['attr']['name']; 
				}
				$row['occ_name']=isset($occ_conf[$row['occ']]) ? $occ_conf[$row['occ']] : $row['occ'];
				$row['part']=isset($part_conf[$row['part']-1]) ? $part_conf[$row['part']-1] : $row['part'];
				$data[]=$row;
			}
			$page=$p->show();
		}
		break;
		
	case 'flower':
		//鲜花排行
		$type_conf=array(
			1=>__('风流帅哥'),
			0=>__('绝色美女'),
		);
		$type=$conditions['type']=array_key_exists($type, $type_conf) ? intval($type) : 1;
		if($date==date('Y-m-d')){
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$condition=array('date'=>intval(date('Ymd',strtotime($date))),'gender'=>$type);
			$count=$mdb->count('flower_rank',$condition);
			$total_rows=$count>$limit ? $limit : $count;
			$p=new Page($total_rows,$list_rows);
			$result_condition=array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array('num'=>-1));
			$list=$mdb->find('flower_rank', $condition, array(), $result_condition);
			foreach ($list as $key=>$row){
				$char_id=floatval($row['_id']);
				$row['charm']=$row['num'];//魅力值
				$row['num']=($p->nowPage-1)*$list_rows+$key+1;//名次
				$character=$mdb->findOne('human_offline', array('_id'=>$char_id), array('attr.name','attr.faction'));
				$row['char_name']=empty($character['attr']['name']) ? '' : $character['attr']['name'];
				$row['faction']=empty($character['attr']['faction']) ? '' : $character['attr']['faction'];
				$data[]=$row;
			}
			$page=$p->show();
		}
		break;
	
	case 'arena':
		//竞技场排名 
		$start_time=strtotime($date);
		$end_time=$start_time+86400;
		$limit=$limit>100 ? 100 : $limit;
		$sql="select winner_id,count(*) as winner_count,sum(winner_score) as winner_score from log_arena where time>=$start_time and time<$end_time group by winner_id order by winner_count desc limit $limit";
		$mysqli=new DbMysqli();
		$result=$mysqli->query($sql);
		$mdb=new Mdb();
		$sort=array();
		while ($row=$result->fetch_assoc()){
			$mdb->selectDb(MONGO_PERFIX.$row['winner_id']%4);
			$char=$mdb->findOne('characters', array('_id'=>floatval($row['winner_id'])), array('name'));
			$row['winner_name']=$char ? $char['name'] : '';
			//失败场次
			$sql="select count(*) as loser_count,sum(loser_score) as loser_score from log_arena where time>=$start_time and time<$end_time and loser_id={$row['winner_id']}";
			$list=$mysqli->findOne($sql);
			$row['count']=$list['loser_count']+$row['winner_count'];
			$row['score']=$list['loser_score']+$row['winner_score'];
			$row['win_ratio']=round($row['winner_count']/$row['count'],2)*100;
			$sort[$row['winner_count']][$row['win_ratio']][]=$row;
		}
		$num=1;
		foreach ($sort as $items){
			krsort($items);
			foreach ($items as $item){
				foreach ($item as $arr){
					$arr['num']=$num;//排名
					$data[]=$arr;
					$num++;
				}
			}
		}
		unset($sort);
		break;
}

//历史排行
if($date!=date('Y-m-d') && isset($type)&&$action!='arena'){
	$mysqli=new DbMysqli();
	$sql="select rank from stat_rank where date='$date' and action='$action' and type='$type'";
	$list=$mysqli->findOne($sql);
	if(isset($list['rank'])){
		$rank=json_decode($list['rank'],true);
		$count=count($rank);
		$p=new Page($count>$limit ? $limit : $count);
		$data=array_splice($rank, $p->firstRow,$p->listRows);
		$page=$p->show();
	}
}

$smarty->assign('conditions',$conditions);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('camp_conf',$camp_conf);
$smarty->assign('occ_conf',$occ_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();