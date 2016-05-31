<?php
//羽翼流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Skill.class.php';
include __CONFIG__.'attr_config.php';

$action_conf=array(
	'up'=>__('升阶流水'),
	'skill'=>__('技能流水'),
	'strong'=>__('强化流水'),
);
//品阶
$class_conf=array(
1=>__("凡"),
2=>__("灵"),
3=>__("仙"),
4=>__("神"),
5=>__("圣"),
);
$action=empty($_GET['action']) ? 'up' : trim($_GET['action']);
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? '' : trim($_GET['from']);

$data=array();
if($char_id>0){
	$where=" where char_id=$char_id";
	$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
	$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
	$mysqli=new DbMysqli();
	$sql="select count(*) as count from log_wing_{$action} $where";
	$total_rows=$mysqli->count($sql);
	$p=new Page($total_rows);
	$sql="select * from log_wing_{$action} $where order by time desc limit {$p->firstRow},{$p->listRows}";
	$result=$mysqli->query($sql);
	$Skill=new Skill();
	while($result && $row=$result->fetch_assoc()){
		$row['time']=date('Y-m-d H:i:s',$row['time']);
		
		//羽翼属性
		if(!empty($row['attr'])){
			$row['attr']=json_decode($row['attr'],true);
			if(isset($row['attr']['basePercent'])){
				unset($row['attr']['basePercent']);	
			}
			if(isset($row['attr']['insideBase'])){
				unset($row['attr']['insideBase']);
			}
			if(isset($row['attr']['insidePercent'])){
				unset($row['attr']['insidePercent']);
			}
		}
		
		//羽翼技能
		if(!empty($row['skill_id'])){
			$row['skill_name']=$Skill->getName($row['skill_id']);
		}

		//羽翼强化
		if(!empty($row['material_list'])){
			$row['material_list']=json_decode($row['material_list'],true);
		}
		$data[]=$row;
	}
	$page=$p->show();
}

$conditions=array(
	'action'=>$action,
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
);

$smarty->assign('action_conf',$action_conf);
$smarty->assign('class_conf',$class_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->assign('bag_attr_conf',$bag_attr_conf);
$smarty->assign('conditions',$conditions);
$smarty->display();
