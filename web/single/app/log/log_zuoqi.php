<?php
//坐骑流水
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CONFIG__.'game_config.php';
include __CLASSES__.'Skill.class.php';
include __CLASSES__.'Ride.class.php';

//坐骑操作
$ride_io_conf = array (
1 => __('获得'),
);
//坐骑项目类型
$ride_type_conf = array (
1 => __('进阶'),
2 => __('增加祝福值'),
);
//坐骑形象项目类型
$ride_model_type_conf = array (
1 => __('激活形象'),
);

$action_conf=array(
'up'=>__('进阶流水'),
'model'=>__('形象流水'),
'refine'=>__('精炼流水'),
);
$action=empty($_GET['action']) ? 'up' : trim($_GET['action']);
$char_id=empty($_GET['char_id']) ? '' : floatval($_GET['char_id']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$from=empty($_GET['from']) ? 'info' : trim($_GET['from']);
$io=isset($_GET['io']) && $_GET['io']!=='' ? intval($_GET['io']) : '';
$type=empty($_GET['type']) ? array() : (array)$_GET['type'];
$model_name=empty($_GET['model_name']) ? '' : trim($_GET['model_name']);
$conditions=array(
	'action'=>$action,
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
	'char_id'=>$char_id,
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'from'=>$from,
	'io'=>$io,
	'type'=>$type,
	'model_name'=>$model_name,
);

$data=array();
$page='';

switch ($action){
	case 'up':
		//进阶流水
		$where=" where char_id=$char_id ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from log_ride_up $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_ride_up $where order by time desc,level desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$skill=new Skill();
		while($result && $row=$result->fetch_assoc()){
			$row['remark']=(array)json_decode($row['remark'],true);
			foreach ($row['remark'] as $key=>$items){
				switch ($key){
					case 'skill':
						if($items){
							$name = '';
							$skill_name=array();
							foreach ($items as $index=>$value){
								$skill_name[]= $skill->getName($value);
							}
							for($i=0;$i<count($skill_name);$i++){
								$name .= $skill_name[$i];
								if(($i+1) != count($skill_name)){
									$name .= ' + ';
								}
							}
							//技能填充
							$row['remark']['skill_name']['list']=$name;
						}
						break;
				}
			}
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		$smarty->assign('ride_type_conf',$ride_type_conf);
		break;
	case 'model':
		//形象流水
		$where=" where char_id=$char_id ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$where.=$io ? " and io=$io " : '';
		$where.=$type ? ' and type in ('.implode(',', $type).')' : '';
		$ride=new Ride();
		$list=$ride->getModelList();
		$model_id=0;
		foreach ($list as $key=>$item){
			if($item==$model_name){
				$model_id=$key;
				break;
			}
		}
		$where.=$model_name ? " and model_id='$model_id'" : '';

		$mysqli=new DbMysqli();
		$sql="select count(*) as count from log_ride_model $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_ride_model $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$result=$mysqli->query($sql);
		$ride=new Ride();
		while($result && $row=$result->fetch_assoc()){
			isset($row['model_id']) ? $row['model_name']=$ride->getModelName($row['model_id']) : '';
			$row['remark']=(array)json_decode($row['remark'],true);
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		$page=$p->show();
		$smarty->assign('ride_type_conf',$ride_model_type_conf);
		break;
		
	case 'refine':
		//精炼流水
		$where=" where char_id=$char_id ";
		$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
		$where.=$end_date ? ' and time<='.(strtotime($end_date)+86400) : '';
		$sql="select count(*) as count from log_ride_refine $where";
		$mysqli=new DbMysqli();
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_ride_refine $where order by time desc limit {$p->firstRow},{$p->listRows}";
		$query=$mysqli->query($sql);
		while ($row=$query->fetch_assoc()){
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}
		//部位配置
		$part_conf=array(
			1=>__('鞭具'),
			2=>__('鞍具'),
			3=>__('蹬具'),
			4=>__('蹄铁'),
		);
		$smarty->assign('part_conf',$part_conf);
		$page=$p->show();
		break;
}

$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('ride_io_conf',$ride_io_conf);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();