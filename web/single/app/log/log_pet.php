<?php
//宠物流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Skill.class.php';
include __CLASSES__.'Pet.class.php';
include __CONFIG__.'game_config.php';
include __CONFIG__.'log_config.php';
include __CLASSES__.'PetRealm.class.php';

$petRealm=new PetRealm();
$petRealmList=$petRealm->getList();

$action=empty($_GET['action']) ? 'egg' : trim($_GET['action']);
$action_conf=array(
	'egg'=>__('宠物流水'),
	'exp'=>__('经验流水'),
	'pullulate'=>__('灵识流水'),
	'realm'=>__('境界流水'),
	'skill'=>__('技能流水'),
	'equip'=>__('装备流水'),
	'up'=>__('装备进阶流水'),
);
$action=array_key_exists($action, $action_conf) ? $action : 'exp';
$pet_id=empty($_GET['pet_id']) ? '' : floatval($_GET['pet_id']);
$pet_original=empty($_GET['pet_original']) ? '' : trim($_GET['pet_original']);//原型
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$rank=isset($_GET['rank']) && $_GET['rank']!=='' ? intval($_GET['rank']) : '';//境界类型
$type=isset($_GET['type']) && $_GET['type']!=='' ? intval($_GET['type']) : '';
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';//操作
$xm_type=empty($_GET['xm_type']) ? array() : (array)$_GET['xm_type'];//项目类型
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);
$is_upgrade=empty($_GET['is_upgrade']) ? 0 : intval($_GET['is_upgrade']); //升级checkbox
$is_rare=empty($_GET['is_rare']) ? 0 : intval($_GET['is_rare']); //升级checkbox
$source=isset($_GET['source']) && $_GET['source']!=='' ? intval($_GET['source']) : '';//技能来源
//恢复批量宠物
$back_pet=empty($_GET['back_pet']) ? array() : (array)$_GET['back_pet'];//批量恢复失去宠物
$petids=empty($_GET['petids']) ? '' : floatval($_GET['petids']);
$conditions=array(
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'action'=>$action,
	'char_id'=>$char_id,
	'pet_id'=>$pet_id,
	'type'=>$type,
	'rank'=>$rank,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
	'pet_original'=>$pet_original,
	'io'=>$io,
	'xm_type'=>$xm_type,
	'is_upgrade'=>$is_upgrade,
	'source'=>$source,
	'back_pet'=>$back_pet,
	'is_rare'=>$is_rare,
);

$data=array();
$page='';
if($char_id && $action=='egg'){
	//宠物流水
	$where=" where char_id=$char_id and type !=1 ";
	$where.=$pet_id ? " and pet_id=$pet_id" : '';
	$where.=$pet_original ? " and original_name='$pet_original' " : '';
    $where.=$io!=='' ? " and io=$io" : '';
    $where.=$is_rare ? ' and is_rare=1 ' : '';  //稀有蛋
	$where.=$xm_type ? ' and type in ('.implode(',', $xm_type).')' : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';

	$mysqli=new DbMysqli();
	$sql="select count(*) as count from log_pet $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_pet $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$skill=new Skill();
	$pet=new Pet();
	while($result && $row=$result->fetch_assoc()){
		$row['remark']=(array)json_decode($row['remark'],true);
		foreach ($row['remark'] as $key=>$items){
			switch ($key){
				case 'modelList':
					$model_name=array();
					foreach($items['list'] as $k=>$item){
						$model_name[]=$pet->getModelName($item);
					}
					$name='';
					for($i=0;$i<count($model_name);$i++){
						$name .= $model_name[$i];
						if(($i+1) != count($model_name)){
							$name .= ' 、 ';
						}
					}
					$row['remark'][$key]='<span class="label label-important"></span> '.$name.'<br>';
					break;
				case 'skillList':
					//技能
					foreach ($items as $k=>$item){
						$level=0;//技能等级
						$skill_name=array();
						foreach ($item['info'][0] as $index=>$value){
							if($value==1){
								$level+=$index+1;
								$skill_name[]= $skill->getName($item['id']+($index+1));
							}
						}
						$name = '';
						for($i=0;$i<count($skill_name);$i++){
							$name .= $skill_name[$i];
							if(($i+1) != count($skill_name)){
								$name .= ' + ';
							}
						}
						//技能填充
						if($k > 0){
							$row['remark'][$key][$k]='<span class="label label-important" style="margin-left:43px;"> '.$skill->getName($item['id']+$level).'</span> = '.$name.'<br>';
						}else{
							$row['remark'][$key][$k]='<span class="label label-important"> '.$skill->getName($item['id']+$level).'</span> = '.$name.'<br>';
						}

					}
					break;
			}
		}
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();

}else if($char_id && $action=='exp'){
	//经验流水
	$where=" where char_id=$char_id";
	$where.=$pet_id ? " and pet_id=$pet_id" : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$rank!=='' ? " and rank=$rank" : '';
	$where.=$is_upgrade ? ' and is_level=1 ' : '';  //升级
	$where.=$pet_original ? " and original_name='$pet_original' " : '';
	$where.=$xm_type ? ' and type in ('.implode(',', $xm_type).')' : '';   //项目类型（经济来源）经济流水
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from log_pet_exp $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_pet_exp $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$row['type']=isset($exp_type_conf[$row['type']]) ? $exp_type_conf[$row['type']] : '';
		$data[]=$row;
	}
	$page=$p->show();

}else if($char_id && $action == 'pullulate'){
	//灵识流水
	$table='log_pet_pullulate';
	$where=" where char_id=$char_id ";
	$where.=$pet_id ? " and pet_id=$pet_id " : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$rank!=='' ? " and rank=$rank" : '';
	$where.=$is_upgrade ? ' and is_level=1 ' : '';  //升级
	$where.=$pet_original ? " and original_name='$pet_original' " : '';
    $where.=$xm_type ? ' and type in ('.implode(',', $xm_type).')' : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();

}else if($char_id&&$action=='realm'){
	//境界流水
	$table='log_pet_realm';
	$where=" where char_id=$char_id and type !=0 ";
	$where.=$pet_id ? " and pet_id=$pet_id " : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$rank!=='' ? " and rank=$rank" : '';
	$where.=$is_upgrade ? ' and is_level=1 ' : '';  //升级
	$where.=$pet_original ? " and original_name='$pet_original' " : '';
    $where.=$xm_type ? ' and type in ('.implode(',', $xm_type).')' : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$data[]=$row;
	}
	$page=$p->show();

}


else if($char_id&&$action=='skill'){
	//技能流水
	$table='log_pet_skill';
	$where=" where char_id=$char_id ";
	$where.=$pet_id ? " and pet_id=$pet_id " : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$rank!=='' ? " and rank=$rank" : '';
	$where.=$is_upgrade ? ' and is_level=1 ' : '';  //升级
	$where.=$pet_original ? " and original_name='$pet_original' " : '';
	$where.=$io ? " and io=$io" : '';
    $where.=$xm_type ? ' and type in ('.implode(',', $xm_type).')' : '';   //项目类型

	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$skill=new Skill();
	$skill_name = array();
	while($result && $row=$result->fetch_assoc()){
		$row['remark']=(array)json_decode($row['remark'],true);
		foreach ($row['remark'] as $key=>$items){
			switch ($key){
				case 'skillList':
					//技能
					foreach ($items as $k=>$item){
						$level=0;//技能等级
						foreach ($item['common'] as $index=>$value){
							$value==1 ? $level+=$index+1 : '';
						}
						$skill_name=$skill->getName($item['skillId']+$level);
						$row['remark'][$key][$k]=$skill_name;
					}
					break;
			}
		}


		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$row['skillName']=$skill->getName($row['skill_id']+$row['level']);
		$data[]=$row;
	}
	$page=$p->show();
}else if($char_id && $action=='equip'){
	//装备流水
	$table='log_pet_equip';
	$where=" where char_id=$char_id and type=1 ";
	$where.=$pet_id ? " and pet_id=$pet_id " : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$is_upgrade ? ' and is_up=1 ' : '';  //升级
	$where.=$pet_original ? " and original_name='$pet_original' " : '';
    $where.=$io!=='' ? " and io=$io" : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$remark=(array)json_decode($row['remark'],true);
		unset($row['remark']);
		$row['remark']['addExp']=$remark['addExp'];
		$row['remark']['exp']=$remark['exp'];
		$row['remark']['level']=$remark['level'];
		$row['remark']['rank']=$remark['rank'];
		$data[]=$row;
	}
	$page=$p->show();
}else if($char_id && $action=='up'){
	//装备升阶流水
	$table='log_pet_equip';
	$where=" where char_id=$char_id and type=2 ";
	$where.=$pet_id ? " and pet_id=$pet_id " : '';
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';
	$where.=$is_upgrade ? ' and is_up=4 ' : '';  //升级  3表示没进阶 4进阶 或者 降价
	$where.=$pet_original ? " and original_name='$pet_original' " : '';
    $where.=$io!=='' ? " and io=$io" : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from $table $where ";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from $table $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	while($result && $row=$result->fetch_assoc()){
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		$remark=(array)json_decode($row['remark'],true);
		unset($row['remark']);
		$row['remark']['rank']=$remark['rank'];
		$data[]=$row;
	}
	$page=$p->show();
}else if($char_id&&$petids){
	//处理批量恢复宠物
//	echo '处理批量恢复宠物';
//	print_r($petids);
}
$type_conf=array();
switch ($action){
	case 'egg':
		$type_conf=array(
//		1=>__('开蛋'),
		2=>__('获得宠物'),
		3=>__('放生'),
		4=>__('融合'),
		);
		$io_conf=array(
		0=>__('失去'),
		1=>__('获得'),
		2=>__('Sửa'),
		);
		//宠物项目类型
		$pet_xm_conf = array (
//		  1 => __('稀有蛋'),
		  2=>__('获得宠物'),
		  3=>__('放生'),
		  4=>__('融合'),
		);
		$smarty->assign('io_conf',$io_conf);
		$smarty->assign('pet_xm_conf',$pet_xm_conf);
		$smarty->assign('pet_realm_conf',$pet_realm_conf);
		break;
	case 'pullulate':
		$type_conf=array(
		0=>__('生命'),
		1=>__('攻击'),
		2=>__('防御'),
		);
		$smarty->assign('pet_xm_conf',$type_conf);
		break;
	case 'realm':
		$type_conf=array(
//		0=>__('升境'),
		1=>__('提升'),
		2=>__('增加祝福值'),
		);
		$smarty->assign('pet_xm_conf',$type_conf);
		$smarty->assign('pet_realm_conf',$pet_realm_conf);
		break;
	case 'exp':
		//宠物项目类型（经验来源）
		$smarty->assign('pet_xm_conf',$exp_type_conf);
		break;

	case 'skill':
		$io_conf=array(
			0=>__('失去'),
			1=>__('获得'),
			2=>__('更新'),
		);
		$type_conf=array(
			0=>__('技能书升级'),
			2=>__('提炼技能'),
			3=>__('融合获得'),
		);
		//宠物技能类型
		$smarty->assign('pet_xm_conf',$type_conf);
		$smarty->assign('io_conf',$io_conf);
		break;
	case 'equip':
		$io_conf=array(
			1=>__('道具升级'),
			2=>__('元宝升级'),
		);
		$type_conf=array(
			1=>__('装备升级'),
		);
		$smarty->assign('pet_xm_conf',$type_conf);
		$smarty->assign('io_conf',$io_conf);
		break;
	case 'up':
		$io_conf=array(
			3=>__('升阶'),
		);
		$type_conf=array(
			2=>__('装备升阶'),
		);
		$smarty->assign('pet_xm_conf',$type_conf);
		$smarty->assign('io_conf',$io_conf);
		break;
}
$smarty->assign('is_restore',isset($_SESSION['__'.SERVER_TYPE.'_ROLE_MENU']['log']['childs']['log_restore.php']) ? true :false);
$smarty->assign('part_conf',$part_conf);
$smarty->assign('petRealmList',$petRealmList);
$smarty->assign('type_conf',$type_conf);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();