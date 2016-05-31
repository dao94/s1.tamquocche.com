<?php
/*
 * //日志清除
 *
 * php log_del.php --task=log_del --del_days=90
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';

ini_set("max_execution_time",0);
ini_set("mysql.connect_timeout",-1);

/*按时间Xóa 默认字段 time 特殊情况设置 field  附加条件 设置 where_add
 *log_battlefield"=>array("field"=>"end_time","where_add"=>" `money_type` != 3")
 */
$log_arr=array(
"log_boss"=>array(),
"log_general_audience"=>array(),
"log_items"=>array(),
"log_lua_memory"=>array(),
"log_mission_daily_accept"=>array(),
"log_mission_daily_complete"=>array(),
"log_mission_main_accept"=>array(),
"log_mission_main_complete"=>array(),
"log_money"=>array("where_add"=>" `money_type` != 3"),
"log_offline_arena"=>array("field"=>"end_time"),
"log_pet_equip"=>array(),
"log_pet_exp"=>array(),
"log_pet_jinjian"=>array(),
);
write_log("task start:". date("Y-m-d H:i:s"), 'logdel_'.date('Ym'));
if(!isset($argc) || $argc<2 || time()<SERVER_OPEN_TIME) exit('Invalid request');
array_shift($argv);
$task=new Task();
$params=$task->parseArgs($argv);//参数数组
$del_days=empty($params['del_days']) ? 90 : intval(trim($params['del_days']));
($del_days<1)&& $del_days=90;
$del_time=time()-$del_days*24*3600;
$task->name=$params['task'];
$sql_array=array();
$mysqli=$task->mysqli();
switch ($params['task']){
	case 'log_del':
		//
		/*
		foreach($log_arr as $key => $log){
			$field=empty($log["field"])? "time" : $log["field"];
			$sql="delete from `".$key."` where `".$field."` <".$del_time;
			//$sql=" where `".$field."` <".$del_time;
			if(!empty($log["where_add"])){
				$sql.=" and ".$log['where_add'];
			}
			$sql.=" order by ".$field." asc limit 1000000";
			$sql_array[$key]=$sql;
		}
		*/
		//print_r($sql_array);
		//$result_array=more_thread_logdel($sql_array,100);
		//foreach($result_array as $key=>$result){
			//print_r($result);
			//if(!($result['status']==TRUE)){
			//}
		//}
		//$result_array=more_thread_logdel($log_arr,$del_time,10);

		foreach($log_arr as $table=> $conf){
			$field=empty($conf["field"])? "time" : $conf["field"];
			$sql="select max(`id`) as id from " .$table." where `".$field."` <".$del_time;
			write_log(array(0=>$sql.';'), 'logdel_'.date('Ym'));
			$list=$mysqli->findOne($sql);
			if(!empty($list['id'])){
				$maxId=$list['id'];
				$sql="delete from ".$table." where `id` <=".$maxId." and `".$field."` <".$del_time;
				if(!empty($conf["where_add"])){
					$sql.=" and ".$conf['where_add'];
				}
				$sql.=" limit 1000000 ";
				//echo $sql;
				write_log(array(0=>$sql.';'), 'logdel_'.date('Ym'));
				$mysqli->query($sql);
			}
		}
		break;
}
write_log("task end:". date("Y-m-d H:i:s"), 'logdel_'.date('Ym'));
