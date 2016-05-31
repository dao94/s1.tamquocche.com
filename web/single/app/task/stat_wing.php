<?php
/*
 * 羽翼分析
 * php stat_wing.php --task=wing
 */
 define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__.'lang.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __CLASSES__.'Wing.class.php';

$open_time=SERVER_OPEN_TIME;
$open_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期  64800=12*3600 开服当天12点之后开始传送数据
if(!isset($argc) || $argc<2 || time()<(strtotime($open_date)+43200) || (defined('SERVER_DEBUG')&&SERVER_DEBUG==true)) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组transit
$task->name=$params['task'];
$mysqli=$task->mysqli();
$start_date=empty($params['start_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? 0 : trim($params['end_date']);
$start_date=$start_date&&strtotime($open_date)>strtotime($start_date) ? $open_date : $start_date;
$start_time=empty($start_date) ? 0 : strtotime($start_date);
$end_time=empty($end_date) ? 0 : strtotime($end_date)+86400;
switch ($params['task']){
	case 'wing':
		//羽翼分析
		$start_time=$start_time>0 ? $start_time : strtotime('yesterday');
		$end_time=$end_time>0 ? $end_time :  strtotime('yesterday')+86400;
		$data=array();
		$mdb=new Mdb();
		$Wing=new Wing();
		$data=array();
		for($i=$start_time;$i<$end_time;$i+=86400){
			$sql="select distinct char_id from log_login where login_time>=$i and login_time<($i+86400) ";
			$result=$mysqli->query($sql);
			while($result&&$row=$result->fetch_assoc()){
				$char_id=floatval($row['char_id']);//角色id
				$mdb->selectDb(MONGO_PERFIX.$char_id%4);
				$fields=array('advLvl','bless','skillList');
				$list=$mdb->findOne('wing', array('_id'=>$char_id),$fields);
				if($list){
					//阶数统计
					$adv_level=$list['advLvl'];
					$data['level'][$adv_level]=empty($data['level'][$adv_level]) ? $data['level'][$adv_level]=1 : ++$data['level'][$adv_level];
					//技能
					foreach($list['skillList'] as $item){
						//七个   15开头的技能，看顺数第四位，会有1~7，分别对应第一个~第七个羽翼技能
						$skill_level=substr($item[0],3,1);
						if($skill_level==1){
							$data['skill'][1][$item[1]]=empty($data['skill'][1][$item[1]]) ?  $data['skill'][1][$item[1]]=1 : ++$data['skill'][1][$item[1]];//技能id
						}else if($skill_level==2){
							$data['skill'][2][$item[1]]=empty($data['skill'][2][$item[1]]) ?  $data['skill'][2][$item[1]]=1 : ++$data['skill'][2][$item[1]];//技能id
						}else if($skill_level==3){
							$data['skill'][3][$item[1]]=empty($data['skill'][3][$item[1]]) ?  $data['skill'][3][$item[1]]=1 : ++$data['skill'][3][$item[1]];//技能id
						}else if($skill_level==4){
							$data['skill'][4][$item[1]]=empty($data['skill'][4][$item[1]]) ?  $data['skill'][4][$item[1]]=1 : ++$data['skill'][4][$item[1]];//技能id
						}else if($skill_level==5){
							$data['skill'][5][$item[1]]=empty($data['skill'][5][$item[1]]) ?  $data['skill'][5][$item[1]]=1 : ++$data['skill'][5][$item[1]];//技能id
						}else if($skill_level==6){
							$data['skill'][6][$item[1]]=empty($data['skill'][6][$item[1]]) ?  $data['skill'][6][$item[1]]=1 : ++$data['skill'][6][$item[1]];//技能id
						}else if($skill_level==7){
							$data['skill'][7][$item[1]]=empty($data['skill'][7][$item[1]]) ?  $data['skill'][7][$item[1]]=1 : ++$data['skill'][7][$item[1]];//技能id
						}
					}
				}
			}
		}
		if($data){
			$array_skill=array();
			$date=date('Y-m-d',strtotime('yesterday'));
			ksort($data['level']);
			$hp_remark=json_encode($data['level']);
			ksort($data['skill']);
			foreach($data['skill'] as $key=>$items){
				$array_skill[$key]=$items;
			}
			$up_remark=json_encode($data['level']);
			$skill_remark=json_encode($array_skill);
			$time=time();
			//插入数据
			$sql="replace into stat_wing set date='$date',up_remark='$up_remark',skill_remark='$skill_remark',time=$time";
			$mysqli->query($sql);
		}
		break;
}
?>