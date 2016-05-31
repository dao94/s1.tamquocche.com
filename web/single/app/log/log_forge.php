<?php
//锻造流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';
include __CONFIG__.'game_config.php';

$action_conf=array(
'strong'=>__('强化流水'),
'wash'=>__('洗练流水'),
'gem'=>__('宝石流水'),
'up'=>__('升阶流水'),
'deify'=>__('神化流水'),
'sculpture'=>__('雕刻流水'),
);

$action=empty($_GET['action']) ? 'strong' : trim($_GET['action']);
$from=empty($_GET['from']) ? 'info' : trim($_GET['from']);
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];  //type类型
$part=empty($_GET['part']) ? array() : (array)$_GET['part'];  //part部位
$fire=empty($_GET['fire']) ? array() : (array)$_GET['fire'];  //fire炉火
$gem=empty($_GET['gem']) ? array() : (array)$_GET['gem'];  //gem宝石
$is_grade=empty($_GET['is_grade']) ? 0 : intval($_GET['is_grade']);
$item_id=empty($_GET['item_id']) ? '' : floatval($_GET['item_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));


$conditions=array(
'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
'char_id'=>$char_id,
'action'=>$action,
'from'=>$from,
'type'=>$type,
'part'=>$part,
'fire'=>$fire,
'gem'=>$gem,
'is_grade'=>$is_grade,
'item_id'=>$item_id,
'io'=>$io,
'start_date'=>$start_date,
'end_date'=>$end_date,
);

$io_conf=array(
1=>__('激活'),
2=>__('充能'),
);

$fire_conf=array(
1=>__('凡火'),
2=>__('灵火'),
3=>__('仙火'),
4=>__('神火'),
5=>__('圣火'),
);

$type_conf=array(
1=>__('替换'),
2=>__('更新'),
);

$gem_conf=array(
'30103000012'=>__('红宝石精华'),
'30103010012'=>__('蓝水晶精华'),
'30103020012'=>__('田黄石精华'),
);

$data=array();
$page='';
switch($action){
	case 'strong':
		//强化流水
		$where="where char_id='$char_id' ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$item_id ? " and item_id=$item_id" : '';
		$where.=$fire ? ' and fire in ('.implode(',', $fire).')' : '';
		$where.=$part ? ' and equip_part in ('.implode(',', $part).')' : '';
		$where.=$is_grade ? ' and is_grade=1 ' : '';  //升级
		$mysqli=new DbMysqli();
		$mdb=new Mdb();
		$sql="select count(*) as count from log_equip_strong $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_equip_strong $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$char_list=array();
		while($result && $row=$result->fetch_assoc()){
			if(isset($char_list[$row['char_id']])){
				$row['char_name']=$char_list[$row['char_id']];
			}else{
				$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
				$row['char_name']=$char ? $char['name'] : '';
				$char_list[$row['char_id']]=$row['char_name'];
			}
			$row['remark']=(array)json_decode($row['remark'],true);
			foreach($row['remark'] as $key=>$list){
				$level=$list[0];
				$row['strong_level'][$key]=$level;//宝石等级
			}
			unset($row['remark']);
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 'wash':
		//洗练流水
		$where="where char_id='$char_id' ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$item_id ? " and item_id=$item_id" : '';
		$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
		$where.=$part ? ' and equip_part in ('.implode(',', $part).')' : '';
		$mysqli=new DbMysqli();
		$mdb=new Mdb();
		$sql="select count(*) as count from log_equip_wash $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_equip_wash $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$char_list=array();
		while($result && $row=$result->fetch_assoc()){
			if(isset($char_list[$row['char_id']])){
				$row['char_name']=$char_list[$row['char_id']];
			}else{
				$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
				$row['char_name']=$char ? $char['name'] : '';
				$char_list[$row['char_id']]=$row['char_name'];
			}
			$row['remark']=(array)json_decode($row['remark'],true);
			if(isset($row['remark'])){
				foreach($row['remark'] as $key=>$list){
					$skill_name=$list[0];//技能名称
					$skill_value=$list[1];//技能值
					$skill_star=$list[2];//技能星级
					if(count($row['remark'])==($key+1)){
						$row['attr'][$key]=$bag_attr_conf[$skill_name].' '.$skill_value.'('.$skill_star. ')';
					}else{
						$row['attr'][$key]=$bag_attr_conf[$skill_name].' '.$skill_value.'('.$skill_star. '),';
					}
				}

			}

			unset($row['remark']);
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 'gem':
		//宝石流水
		$where="where char_id='$char_id' ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$item_id ? " and item_id=$item_id" : '';
		$where.=$gem ? ' and gem_id in ('.implode(',', $gem).')' : '';
		$where.=$part ? ' and equip_part in ('.implode(',', $part).')' : '';
		$where.=$is_grade ? ' and is_grade=1 ' : '';  //升级
		$where.=$io ? " and io=$io " : '';  //操作
		$mysqli=new DbMysqli();
		$mdb=new Mdb();
		$sql="select count(*) as count from log_equip_gem $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_equip_gem $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$char_list=array();
		while($result && $row=$result->fetch_assoc()){
			if(isset($char_list[$row['char_id']])){
				$row['char_name']=$char_list[$row['char_id']];
			}else{
				$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
				$row['char_name']=$char ? $char['name'] : '';
				$char_list[$row['char_id']]=$row['char_name'];
			}
			$row['remark']=(array)json_decode($row['remark'],true);
			foreach($row['remark'] as $key=>$list){
				$level=$list[0];
				$row['gem_level'][$key]=$level;//宝石等级
			}
			unset($row['remark']);
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 'up':
		//升阶流水
		$where="where char_id='$char_id' ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$item_id ? " and (equip_id_before=$item_id or equip_id_after=$item_id) " : '';
		$where.=$part ? ' and equip_part in ('.implode(',', $part).')' : '';
		$mysqli=new DbMysqli();
		$mdb=new Mdb();
		$sql="select count(*) as count from log_equip_up $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_equip_up $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$char_list=array();
		while($result && $row=$result->fetch_assoc()){
			if(isset($char_list[$row['char_id']])){
				$row['char_name']=$char_list[$row['char_id']];
			}else{
				$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$char=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
				$row['char_name']=$char ? $char['name'] : '';
				$char_list[$row['char_id']]=$row['char_name'];
			}
			$row['remark_before']=(array)json_decode($row['remark_before'],true); //升阶前
			if(isset($row['remark_before'])){
				foreach($row['remark_before'] as $key=>$list){
					$skill_name=$list[0];//技能名称
					$skill_value=$list[1];//技能值
					$skill_star=$list[2];//技能星级
					$row['attr_before'][$key]=$bag_attr_conf[$skill_name].'： '.$skill_value.'('.$skill_star. ')';
				}
			}

			unset($row['remark_before']);
			$row['remark_after']=(array)json_decode($row['remark_after'],true);//升阶后
			if(isset($row['remark_after'])){
				foreach($row['remark_after'] as $key=>$list){
					$skill_name=$list[0];//技能名称
					$skill_value=$list[1];//技能值
					$skill_star=$list[2];//技能星级
					$row['attr_after'][$key]=$bag_attr_conf[$skill_name].'： '.$skill_value.'('.$skill_star. ')';
				}
			}
			unset($row['remark_after']);
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		break;
		
	case 'deify':
	case 'sculpture':
		$where="where char_id=$char_id ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
		$where.=$item_id ? " and item_id=$item_id" : '';
		$where.=$part ? ' and part in ('.implode(',', $part).')' : '';
		$sql="select count(*) as count from log_equip_{$action} $where";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_equip_{$action} $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$data=array();
		while($result && $row=$result->fetch_assoc()){
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		break;
}
$smarty->assign('colour_conf',$colour_conf);
$smarty->assign('io_conf',$io_conf);
$smarty->assign('gem_conf',$gem_conf);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('fire_conf',$fire_conf);
$smarty->assign('part_conf',$part_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();