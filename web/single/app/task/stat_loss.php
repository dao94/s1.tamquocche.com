<?php
/*
 * 按日期统计基本信息
 * level：等级流水
 * online：在线数据
 * minute_loss：分钟流失
 * online_length：在线时长
 * login：登陆信息
 * keep_day：留存天数统计
 * keep_ratio：留存率统计
 * 在线数据
 * php stat_loss.php --task=level --start_date=2013-06-01 --end_date=2013-07-01
 */
include str_replace(array('//', '\\', 'app/task'), array('/', '/', ''), __DIR__).'/config/config.php';
include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';

if($argc<2) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$start_date=empty($params['end_date']) ? 0 : trim($params['start_date']);
$end_date=empty($params['end_date']) ? date('Y-m-d',strtotime('yesterday')) : trim($params['end_date']);
$task->name=$params['task'];
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'level':
		//等级流失 连续3天未登录判定为流失(为了保证数据准确性，凌晨12点执行)
		$mdb=new Mdb();
		$data=array();
		$condition=array('loginTime'=>array('$lt'=>strtotime('today')-86400*3));
		$group=array('level');
		for ($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$list=$mdb->count('characters', array(), $group);
			foreach ($list as $row){
				if(empty($data[$row['_id']]['count']))
					$data[$row['_id']]['count']=$row['value']['count'];
				else
					$data[$row['_id']]['count']+=$row['value']['count'];
			}
			
			$list=$mdb->count('characters', $condition, $group);
			foreach ($list as $row){
				if(empty($data[$row['_id']]['loss_count']))
					$data[$row['_id']]['loss_count']=$row['value']['count'];
				else
					$data[$row['_id']]['loss_count']+=$row['value']['count'];
			}
		}
		
		$mysqli=new DbMysqli();
		foreach ($data as $level=>$item){
			$item['date']=date('Ymd',strtotime('yesterday'));
			$item['time']=time();
			$item['level']=$level;
			$fields=implode(',', array_keys($item));
			$values=implode(',', array_values($item));
			$sql="insert into stat_level_loss ($fields) values ($values)";
			$mysqli->query($sql);
		}
		break;
}