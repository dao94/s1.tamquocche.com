<?php
//帮派资源
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';
include __CONFIG__.'log_config.php';
include __CLASSES__.'Skill.class.php';


$action_conf=array(
'resource'=>__('帮派资源'),
'copy'=>__('开副本'),
'skill'=>__('技能'),
);

$action=empty($_GET['action']) ? 'resource' : trim($_GET['action']);
$faction_id=empty($_GET['faction_id']) ? '' : my_escape_string(trim($_GET['faction_id']));
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';
$char_name=empty($_GET['char_name']) ? '' : trim($_GET['char_name']);
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'faction_id'=>$faction_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'io'=>$io,
	'type'=>$type,
	'char_name'=>$char_name,
	'from'=>$from,
	'action'=>$action,
);

$data=array();
$page='';

switch($action){
	case 'resource':
		//帮派资源
		$where=" where faction_id='$faction_id' and type !=9 ";//排除帮贡记录书
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
		$where.=$io!=='' ? " and io=$io ": '';
		//从角色名获取角色id
		$mdb=new Mdb();
		if($char_name!=''){
			$char_id='';
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$char=$mdb->findOne('characters', array('name'=>$char_name), array('_id'));
				if($char) {$char_id=$char['_id'];break;}
			}
			$where.=$char_id ? " and char_id='$char_id'" : " and (char_id='' and type!=7) ";  //如果输入的是错误的角色名 排除 开服排行榜情况
		}

		$mysqli=new DbMysqli();
		$sql='select count(*) as count from log_faction_resource '.$where;
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_faction_resource $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$mdb=new Mdb();
		while ($row=$result->fetch_assoc()){
			if($row['char_id']){
				$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$list=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
				$row['char_name']=$list ? $list['name'] : '';
			}
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 'copy':
		//帮派开副本
		$where=" where faction_id='$faction_id'";
		$where.=$start_date ? ' and end_time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and end_time<='.(strtotime($end_date)+86400) : '';
		//从角色名获取角色id
		$mdb=new Mdb();
		if($char_name!=''){
			$char_id='';
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$char=$mdb->findOne('characters', array('name'=>$char_name), array('_id'));
				if($char) {$char_id=$char['_id'];break;}
			}
			$where.=$char_id ? " and char_id='$char_id'" : " and char_id=''  ";  //如果输入的是错误的角色名 排除 开服排行榜情况
		}
		$mysqli=new DbMysqli();
		$sql='select count(*) as count from log_faction_copy '.$where;
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_faction_copy $where order by end_time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$mdb=new Mdb();
		while ($row=$result->fetch_assoc()){
			if($row['char_id']){
				$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$list=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
				$row['char_name']=$list ? $list['name'] : '';
			}
			$data[]=$row;
		}
		$page=$p->show();
		break;
	case 'skill':
		//帮派技能
		$where=" where faction_id='$faction_id'";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		//从角色名获取角色id
		$mdb=new Mdb();
		if($char_name!=''){
			$char_id='';
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$char=$mdb->findOne('characters', array('name'=>$char_name), array('_id'));
				if($char) {$char_id=$char['_id'];break;}
			}
			$where.=$char_id ? " and char_id='$char_id'" : " and char_id=''  ";  //如果输入的是错误的角色名 排除 开服排行榜情况
		}
		$mysqli=new DbMysqli();
		$sql='select count(*) as count from log_faction_skill '.$where;
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_faction_skill $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$mdb=new Mdb();
		$skill=new Skill();
		while ($row=$result->fetch_assoc()){
			if($row['char_id']){
				$mdb->selectDb(MONGO_PERFIX.(floatval($row['char_id'])%4));
				$list=$mdb->findOne('characters', array('_id'=>floatval($row['char_id'])), array('name'));
				$row['char_name']=$list ? $list['name'] : '';
			}
			$row['skill_name']=($row['skill_id'] ? $skill->getName($row['skill_id']) : '');
			$data[]=$row;
		}
		$page=$p->show();
		break;
}

$faction_io_conf=array(0=>__('消耗'),1=>__('获得'));
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('faction_io_conf',$faction_io_conf);
$smarty->assign('faction_type_conf',$faction_type_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();