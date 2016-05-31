<?php
/*
 * 衮服玩家统计
 * php stat_gunfu.php --task=gunfu_account --start_date=2013-06-01 --end_date=2013-07-01
 */
define('__ROOT__', str_replace(array('//', '\\', '/app/task'), array('/', '/', ''), __DIR__));
include __ROOT__.'/config/config.php';
include __FUNCTIONS__.'function.php';
include __CLASSES__.'Task.class.php';
include __CLASSES__.'Mdb.class.php';
include __LIB__.'phprpc_php/phprpc_client.php';

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
	case 'gunfu_account':
		$start_time=$start_time>0 ? $start_time : strtotime('yesterday');
		$end_time=$end_time>0 ? $end_time :  strtotime('yesterday')+86400;
		$data=$accounts=array();
		for($i=$start_time;$i<=$end_time;$i+=86400){
			$mongo = new Mdb();
			$mongo->selectDb(MONGO_PERFIX.'4');
			$limit=2000;
			$offset=0;
			$condition=array('char_id' => array('$exists' => true),'create_time'=>array('$gte'=>$i,'$lt'=>$i+86400));
			$fields = array('account');
			$user=0;
			while($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mongo->find('account_data', $condition, $fields, $result_condition);
				if(!empty($list)){
						foreach($list as $items){
							$accounts[]=$items['account'];
						}
				}
				if(count($list) < $limit) break;
				$offset +=$limit;
			}
		}

		$server_list=array();
		$agent_name=SERVER_AGENT;		//当前代理名
		$sid=intval(substr(SERVER_ID,1));//当前代理服务器
		include __CONFIG__.'server_list_config.php';
		$agent_array=$server_list[$agent_name];
		$sid_array=$agent_ip=array();
		foreach($agent_array as $key=>$row){
			$sid_array[]=intval(substr($key,1));//代理服务器数组
			$agent_ip[]=$row;//代理ip地址数组
		}

		foreach($sid_array as $id=>$value){
			if($value<$sid){
				$domain=$agent_ip[$id][0];
				$type='single';
				$phprpc_client = new PHPRPC_Client();
				$phprpc_client->useService("http://{$domain}/{$type}/app/interface/gunfu_account.php");
				$phprpc_client->setEncryptMode(1);
				$phprpc_client->setEnableGZIP(true);
				$phprpc_client->setTimeout(10);
				//传数据到其他服
				$data=$phprpc_client->gunfu_account($accounts);//滚服玩家
				if(!empty($data)){
					foreach($data as $key=>$account){
						$time=time();
						//得到滚服玩家
						$sql="replace into log_old_account set account='$account',server_id=$value,time=$time";
						$mysqli->query($sql);
					}
				}
				$accounts=array_diff($accounts, $data);//得到剩下的玩家

			}
		}
		break;
}