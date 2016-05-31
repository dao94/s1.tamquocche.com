<?php
include __CLASSES__ . 'Mdb.class.php';
//单服数据推送到中央后台和代理中央后台
class Transit{
	var $mysqli;//mysqli类
	var $mongo;
	public function __construct(){
		$this->mysqli=new DbMysqli();
		$this->mongo = new Mdb();
	}

	//获取代理id
	public function getAgentId(){
		$agent_list=array();
		$agent_config_file=__CONFIG__.'agent_list_config.php';
		if(file_exists($agent_config_file)){
			include $agent_config_file;
		}
		if(empty($agent_list) || !in_array(SERVER_AGENT,$agent_list)){
			exit('Not found');
		}else{
			return array_search(SERVER_AGENT,$agent_list);
		}
	}

	//获取域名
	private function getDomain($type){
		$domain='';
		switch ($type){
			case 'center':
				$domain=CENTER_DOMAIN;
				break;
			case 'agent':
				$domain=AGENT_DOMAIN;
				break;
			default:
				exit('Type error');
				break;
		}
		return $domain;
	}

	//推送订单
	public function pay_order($type,$start_time=0,$end_time=0){
		include __CONFIG__.'key_config.php';
		$agent_id=$this->getAgentId();//获取代理id
		$money_type=defined('MONEY_TYPE') ? MONEY_TYPE : 1;
		$domain=$this->getDomain($type);

		$transit_type=$type.'_'.__FUNCTION__;
		$sql="select flag from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['flag'])){
			$sql="select min(ts) as time from pay_order";
			$list=$this->mysqli->findOne($sql);
			$min_time=empty($list['time']) ? 0 : intval($list['time']);
		}else{
			$min_time=intval($list['flag']);
		}

		$start_time=$start_time>0 ? $start_time : $min_time;
		$end_time=$end_time>0 ? $end_time : time();

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=100;
		$offset=0;
		while ($limit>0){
			$sql="select order_id,sid,account,char_name,money,gold,ts as time,is_test,is_first,level from pay_order where ts>=$start_time and ts<=$end_time and is_test=0 order by ts asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			$data=array();
			$flag=$start_time;
			while ($row=$result->fetch_assoc()){
				$row['money_type']=$money_type;
				$row['agent_id']=$agent_id;
				ksort($row);//重新排序key
				$data[]=$row;
				$flag=$row['time']>$flag ? $row['time'] : $flag;
			}
			$result=$phprpc_client->{__FUNCTION__}($data);
			if($result===true){
				//插入成功
				$date=date('Y-m-d',$flag);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data)<$limit){
				$limit=$offset=0;
				break;
			}
			$offset+=$limit;
		}
	}

	//推送在线数据
	public function online($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);

		$transit_type=$type.'_'.__FUNCTION__;
		$sql="select flag from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['flag'])){
			$sql="select min(time) as time from log_online";
			$list=$this->mysqli->findOne($sql);
			$min_time=empty($list['time']) ? 0 : intval($list['time']);
		}else{
			$min_time=intval($list['flag']);
		}

		$start_time=$start_time>0 ? $start_time : $min_time;
		$end_time=$end_time>0 ? $end_time : time();

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=100;
		$offset=0;
		while ($limit>0){
			$sql="select ip,count,time from log_online where time>=$start_time and time<=$end_time order by time asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			$data=array();
			$flag=$start_time;
			while ($row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
				$data[]=$row;
				$flag=$row['time']>$flag ? $row['time'] : $flag;
			}
			$result=$phprpc_client->{__FUNCTION__}($data);
			if($result===true){
				//插入成功
				$date=date('Y-m-d',$flag);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data)<$limit || $offset/$limit>10){
				$limit=$offset=0;
				break;
			}
			$offset+=$limit;
		}
	}

	//推送战斗力分析
	public function stat_fight($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);

		$transit_type=$type.'_stat_fight';
		$sql="select date from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['date'])){
			$sql="select min(date) as date from stat_keep_day";
			$list=$this->mysqli->findOne($sql);
			$min_date=empty($list['date']) ? date('Y-m-d',SERVER_OPEN_TIME) : $list['date'];
		}else{
			$min_date=$list['date'];
		}
		$start_time=$start_time>0 ? $start_time : strtotime($min_date);
		$end_time=$end_time>0 ? $end_time : time();
		$start_date=date('Y-m-d',$start_time);
		$end_date=date('Y-m-d',$end_time);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=200;
		$offset=0;
		while ($limit>0){
			$sql="select * from stat_fight where date>='$start_date' and date<='$end_date' order by date asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			$data=array();
			$date=$start_date;
			while ($row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
				$data[]=$row;
				$date=$row['date']>$date ? $row['date'] : $date;
			}
			$result=$phprpc_client->{__FUNCTION__}($data);
			if($result===true){
				//插入成功
				$flag=strtotime($date);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data)<$limit || $offset/$limit>10){
				$limit=$offset=0;
				break;
			}
			$offset+=$limit;
		}
	}

	//推送注册数量（账号，角色等）
	public function stat_reg($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);

		$transit_type=$type.'_'.__FUNCTION__;
		$sql="select date from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['date'])){
			$sql="select min(date) as date from stat_reg";
			$list=$this->mysqli->findOne($sql);
			$min_date=empty($list['date']) ? date('Y-m-d',SERVER_OPEN_TIME) : $list['date'];
		}else{
			$min_date=$list['date'];
		}

		$start_time=$start_time>0 ? $start_time : strtotime($min_date);
		$end_time=$end_time>0 ? $end_time : time();
		$start_date=date('Y-m-d',$start_time);
		$end_date=date('Y-m-d',$end_time);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=200;
		$offset=0;
		while ($limit>0){
			$sql="select * from stat_reg where date>='$start_date' and date<='$end_date' order by date asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			$data=array();
			$date=$start_date;
			while ($row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
				$data[]=$row;
				$date=$row['date']>$date ? $row['date'] : $date;
			}
			$result=$phprpc_client->{__FUNCTION__}($data);
			if($result===true){
				//插入成功
				$flag=strtotime($date);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data)<$limit || $offset/$limit>10){
				$limit=$offset=0;
				break;
			}
			$offset+=$limit;
		}
	}

	//推送留存率
	public function user_keep($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);

		$transit_type=$type.'_stat_user_keep';
		$sql="select date from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);

		if(empty($list['date'])){
			$sql="select date from stat_keep_day order by date asc limit 1";
			$list=$this->mysqli->findOne($sql);
			$min_date=empty($list['date']) ? date('Y-m-d',SERVER_OPEN_TIME) : $list['date'];
		}else{
			$min_date=$list['date'];
		}

		$start_time=$start_time>0 ? $start_time : strtotime($min_date);
		$end_time=$end_time>0 ? $end_time : time();
		$start_date=date('Y-m-d',$start_time);
		$end_date=date('Y-m-d',$end_time);
		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);

		$limit=500;
		$offset=0;
		$keep_day=array(0,1,2,3,4,5,6,7,14,21,30);//留存天数  0表示当天
		while($limit>0){
			$where=" where k.date>='$start_date' and k.date<='$end_date' and k.date=r.date order by k.date asc limit $offset,$limit ";
			$sql="select k.date,k.day1,k.day2,k.day3,k.day4,k.day5,k.day6,k.day7,k.day14,k.day21,k.day30,r.character_count as userCount,k.time " .
					" from stat_keep_day k,stat_reg r $where ";
			$result=$this->mysqli->query($sql);
			$data_count=$this->mysqli->count($sql);
			$data=array();
			$flag=$start_date;
			while ($row=$result->fetch_assoc()){
				foreach($keep_day as $day){
					$creat_date=date('Y-m-d',strtotime($row['date'])-86400*$day);
					$sql="select k.*,r.character_count as userCount from stat_keep_day k,stat_reg r where k.date='$creat_date' and k.date=r.date limit 1";
					$list=$data=array();
					$list=$this->mysqli->findOne($sql);
					if(empty($list)){
						continue;
					}else{
						//Sửa留存率之前的数据
						$list['agent_id']=$agent_id;
						$list['sid']=intval(substr(SERVER_ID,1));
						ksort($list);//重新排序key
						$data[]=$list;
			            $flag=$row['date']>$flag ? $row['date'] : $flag;
					}
					$result_insert=$phprpc_client->user_keep($data);
					if($result_insert===true){
						//插入成功
						$date=$flag;
						$flag=strtotime($date);
						$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
						$this->mysqli->query($sql);
					}else{
						$limit=$offset=0;
						break;
					}
				}

			}
			if($data_count < $limit) break;
			$offset +=$limit;
		}
	}

	//推送货币滞留率
	public function money_keep($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);

		$transit_type=$type.'_stat_money_keep';
		$sql="select date from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);

		if(empty($list['date'])){
			$sql="select date from stat_money order by date asc limit 1";
			$list=$this->mysqli->findOne($sql);
			$min_date=empty($list['date']) ? date('Y-m-d',SERVER_OPEN_TIME) : $list['date'];
		}else{
			$min_date=$list['date'];
		}

		$start_time=$start_time>0 ? $start_time : strtotime($min_date);
		$end_time=$end_time>0 ? $end_time : time();
		$start_date=date('Y-m-d',$start_time);
		$end_date=date('Y-m-d',$end_time);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);

		$money_class_conf = array(
			1 => __('铜币'),
			2 => __('铜券'),
			3 => __('元宝'),
			4 => __('礼券'),
			//5 => __('交易背包上的铜币'),
			//6 => __('交易背包上的元宝'),
			8=>__('功勋'),
			10=>__('竞技点'),
			11=>__('洗练积分'),
			12=>__('交情'),
		);
		//货币种类
		foreach($money_class_conf as $type=>$value){
			//非系统产出或消耗渠道
			$no_system='5,6';
			$limit=500;
			$offset=0;
			while ($limit>0){
					$data=array();
					$flag=$start_date;
					$sql="select date,io,sum(money_num) as money_num,time,money_type,sid from stat_money where date>='$start_date' and date<='$end_date' and money_type=$type and type not in ($no_system) group by date,io order by date desc limit $offset,$limit";
					$result=$this->mysqli->query($sql);
					while ($result && $row=$result->fetch_assoc()){
						$data[$row['date']]['agent_id']=$agent_id;
						$data[$row['date']]['sid']=intval(substr(SERVER_ID,1));
						$data[$row['date']]['date']=$row['date'];
						$data[$row['date']]['time']=$row['time'];
						$data[$row['date']]['sid_hefu']=$row['sid'];
						$data[$row['date']]['money_type']=$row['money_type'];
						$data[$row['date']]['list'][$row['io']]['money_num']=$row['money_num'];
						//截止今天总产出和总消耗
						$sql="select count(distinct date) as count,sum(money_num) as total_money_num from stat_money where date<='{$row['date']}' and money_type=$type and type not in ($no_system) and io={$row['io']}";
						$list=$this->mysqli->findOne($sql);
						$data[$row['date']]['list'][$row['io']]['total_money_num']=isset($list['total_money_num']) ? $list['total_money_num'] : 0;
						$data[$row['date']]['list'][$row['io']]['avg_money_num']=empty($list['count']) ? 0 : round($list['total_money_num']/$list['count'],0);
						//昨日实际存量
						if(!isset($data[$row['date']]['yesterday_real_money_num'])){
							$yesterday=date('Y-m-d',strtotime($row['date'])-86400);
							$sql="select money_num,not_loss_money_num from stat_money_real where date='$yesterday' and money_type=$type";
							$list=$this->mysqli->findOne($sql);
							$data[$row['date']]['yesterday_real_money_num']=isset($list['money_num']) ? $list['money_num'] : 0;
						}
						//今日实际存量
						if(!isset($data[$row['date']]['real_money_num'])){
							$sql="select money_num,not_loss_money_num from stat_money_real where date='{$row['date']}' and money_type=$type";
							$list=$this->mysqli->findOne($sql);
							$data[$row['date']]['real_money_num']=isset($list['money_num']) ? $list['money_num'] : 0;
							//非流失玩家货币存量
							$data[$row['date']]['not_loss_money_num']=isset($list['not_loss_money_num']) ? $list['not_loss_money_num'] : 0;
						}

						if(isset($data[$row['date']]['list'][0]['money_num']) && isset($data[$row['date']]['list'][1]['money_num'])){
							//今日理论存量=昨日实际存量 +今日产出 -今日消耗
							$data[$row['date']]['ideal_money']=$data[$row['date']]['yesterday_real_money_num']+$data[$row['date']]['list'][1]['money_num']-$data[$row['date']]['list'][0]['money_num'];
						}

						//报警机制(大于0.001) =(今日实际存量-今日理论存量)/今日理论存量
						$data[$row['date']]['alarm']=empty($data[$row['date']]['ideal_money']) ? 0 :
						($data[$row['date']]['real_money_num']-$data[$row['date']]['ideal_money'])/$data[$row['date']]['ideal_money'];

						ksort($row);//重新排序key
			            $flag=$row['date']>$flag ? $row['date'] : $flag;
					}
					$array=$money_data=array();
					foreach($data as $date=>$items){
						$array['agent_id']=$items['agent_id'];
						$array['sid']=$items['sid'];
						$array['date']=$items['date'];
						$array['time']=$items['time'];
						$array['sid_hefu']=$items['sid_hefu'];
						$array['money_type']=$items['money_type'];
						$array['alarm']=$items['alarm'];
						//消耗
						$array['money_num_0']=isset($items['list'][0]['money_num']) ? $items['list'][0]['money_num'] : 0;
						$array['total_money_num_0']=isset($items['list'][0]['total_money_num']) ? $items['list'][0]['total_money_num'] : 0;
						$array['avg_money_num_0']=isset($items['list'][0]['avg_money_num']) ? $items['list'][0]['avg_money_num'] : 0;
						//产出
						$array['money_num_1']=isset($items['list'][1]['money_num']) ? $items['list'][1]['money_num'] : 0;
						$array['total_money_num_1']=isset($items['list'][1]['total_money_num']) ? $items['list'][1]['total_money_num'] : 0;
						$array['avg_money_num_1']=isset($items['list'][1]['avg_money_num']) ? $items['list'][1]['avg_money_num'] : 0;
						$array['yesterday_real_money_num']=$items['yesterday_real_money_num'];//昨日实际存量
						$array['real_money_num']=$items['real_money_num'];//今日实际存量
						$array['not_loss_money_num']=$items['not_loss_money_num'];//非流失玩家货币存量
						ksort($array);
						$money_data[]=$array;
					}
					$result=$phprpc_client->money_keep($money_data);
					if($result===true){
						//插入成功
						$date=$flag;
						$flag=strtotime($date);
						$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
						$this->mysqli->query($sql);
					}else{
						$limit=$offset=0;
						break;
					}
					if(count($money_data) < $limit) break;
					$offset +=$limit;
				}

			}
	}

	//推送货币滞留率--详细
	public function money_keep_detail($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);
		$transit_type=$type.'_stat_money_keep_detail';
		$sql="select date from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['date'])){
			$sql="select date from stat_money order by date asc limit 1";
			$list=$this->mysqli->findOne($sql);
			$min_date=empty($list['date']) ? date('Y-m-d',SERVER_OPEN_TIME) : $list['date'];
		}else{
			$min_date=$list['date'];
		}
		$start_time=$start_time>0 ? $start_time : strtotime($min_date);
		$end_time=$end_time>0 ? $end_time : time();
		$start_date=date('Y-m-d',$start_time);
		$end_date=date('Y-m-d',$end_time);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);

		$money_class_conf = array(
			1 => __('铜币'),
			2 => __('铜券'),
			3 => __('元宝'),
			4 => __('礼券'),
			5 => __('交易背包上的铜币'),
			6 => __('交易背包上的元宝'),
			8=>__('功勋'),
			10=>__('竞技点'),
			11=>__('洗练积分'),
			12=>__('交情'),
		);
		//货币种类
		foreach($money_class_conf as $type=>$value){
			//非系统产出或消耗渠道
			$limit=500;
			$offset=0;
			$flag=$start_date;
			while ($limit>0){
				$data=array();
				$sql="select * from stat_money where date>='$start_date' and date<='$end_date' and money_type=$type order by date,money_type asc limit $offset,$limit ";
				$result=$this->mysqli->query($sql);
				while ($result && $row=$result->fetch_assoc()){
					$row['agent_id']=$agent_id;
					$row['sid_hefu']=$row['sid'];
					$row['sid']=intval(substr(SERVER_ID,1));
					ksort($row);//重新排序key
			        $flag=$row['date']>$flag ? $row['date'] : $flag;
					$data[]=$row;
				}

				$result=$phprpc_client->money_keep_detail($data);
				if($result===true){
					//插入成功
					$date=$flag;
					$flag=strtotime($date);
					$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
					$this->mysqli->query($sql);
				}else{
					$limit=$offset=0;
					break;
				}
				if(count($data) < $limit) break;
				$offset +=$limit;

			}
		 }
	}

	//登录人数和每日在线统计
	public function stat_online_player($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);
		$transit_type=$type.'_stat_online_player';
		$sql="select date from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['date'])){
			$sql="select min(date) as date from stat_online";
			$list=$this->mysqli->findOne($sql);
			$min_date=empty($list['date']) ? date('Y-m-d',SERVER_OPEN_TIME) : $list['date'];
		}else{
			$min_date=$list['date'];
		}
		$start_time=$start_time>0 ? $start_time : strtotime($min_date);
		$end_time=$end_time>0 ? $end_time : time();
		$start_date=date('Y-m-d',$start_time);
		$end_date=date('Y-m-d',$end_time);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=200;
		$offset=0;
		$flag=$start_date;
		while ($limit>0){
			$data=array();
			$sql="select so.*, r.character_count,r.total_character_count,l.* from  stat_reg r,stat_login l, stat_online so " .
				 " where so.date>='$start_date' and so.date<='$end_date' and so.date=l.date and so.date=r.date and l.date>='$start_date' and l.date<='$end_date' and r.date=l.date " .
				 " order by so.date,r.date,l.date asc limit $offset,$limit ";
			$result=$this->mysqli->query($sql);
			while ($result && $row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
		        $flag=$row['date']>$flag ? $row['date'] : $flag;
				$data[]=$row;
			}
			$result_insert=$phprpc_client->stat_online_player($data);
			if($result_insert===true){
				//插入成功
				$date=$flag;
				$flag=strtotime($date);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data) < $limit) break;
				$offset +=$limit;

		}
	}

	//奖励审核
	public function reward_verify($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();
		$domain=$this->getDomain($type);
		$transit_type=$type.'_stat_reward_verify';
		$sql="select flag from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['flag'])){//重新推送之前的数据
			$sql="select min(apply_ts) as time from reward_apply";
			$list=$this->mysqli->findOne($sql);
			$min_time=empty($list['time']) ? SERVER_OPEN_TIME : intval($list['time']);
		}else{
			$min_time=intval($list['flag']);
		}

		$start_time=$start_time>0 ? $start_time : $min_time;
		$end_time=$end_time>0 ? $end_time : time();

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=100;
		$offset=0;
		$flag=$start_time;
		while($limit>0){
			$data=array();
			$sql="select a.*,b.`status` as `internal` from `reward_apply`as `a` left join `internal_account` as `b` on a.`char_id`=b.`char_id` and b.type=1 where a.apply_ts>=$start_time and a.apply_ts<=$end_time order by b.`apply_ts` asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
				$flag=$row['apply_ts']>$flag ? $row['apply_ts'] : $flag;
				$data[]=$row;
			}
			$result_insert=$phprpc_client->reward_apply($data);
			if($result_insert===true){
				//插入成功
				$date=date('Y-m-d',$flag);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data) < $limit) break;
				$offset +=$limit;
		}

	}

	//玩家问题
	public function player_question($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();
		$domain=$this->getDomain($type);
		$transit_type=$type.'_player_question';
		$sql="select flag from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['time'])){//重新推送之前的数据
			$sql="select min(time) as time from gm_question ";
			$list=$this->mysqli->findOne($sql);
			$min_time=empty($list['time']) ? SERVER_OPEN_TIME : $list['time'];
		}else{
			$min_time=$list['flag'];
		}
		$start_time=$start_time>0 ? $start_time : $min_time;
		$end_time=$end_time>0 ? $end_time : time();

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=200;
		$offset=0;
		$flag=$start_time;
		while($limit>0){
			$data=array();
			$sql="select * from gm_question where time>='$start_time' and time<='$end_time' order by time asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
		        $flag=$row['time']>$flag ? $row['time'] : $flag;
				$data[]=$row;
			}
			$result_insert=$phprpc_client->player_question($data);
			if($result_insert===true){
				//插入成功
				$date=date('Y-m-d',$flag);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data) < $limit) break;
				$offset +=$limit;
		}
	}

	//充值审核
	public function pay_verify($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();
		$domain=$this->getDomain($type);
		$transit_type=$type.'_internal_account_gold';
		$sql="select flag from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['time'])){//重新推送之前的数据
			$sql="select min(apply_ts) as time from internal_account_gold ";
			$list=$this->mysqli->findOne($sql);
			$min_time=empty($list['time']) ? SERVER_OPEN_TIME : $list['time'];
		}else{
			$min_time=$list['flag'];
		}
		$start_time=$start_time>0 ? $start_time : $min_time;
		$end_time=$end_time>0 ? $end_time : time();

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=200;
		$offset=0;
		$flag=$start_time;
		while($limit>0){
			$data=array();
			$sql="select * from internal_account_gold where apply_ts>='$start_time' and apply_ts<='$end_time' order by apply_ts asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
		        $flag=$row['apply_ts']>$flag ? $row['apply_ts'] : $flag;
				$data[]=$row;
			}
			$result_insert=$phprpc_client->pay_verify($data);
			if($result_insert===true){
				//插入成功
				$date=date('Y-m-d',$flag);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data) < $limit) break;
				$offset +=$limit;
		}
	}

	//服务器开服当天的充值和总注册数  注册数 充值金额
	public function server_open_count($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();
		$domain=$this->getDomain($type);
		$start_time=SERVER_OPEN_TIME-10*60;//开服开始时间
		$end_time=strtotime(date('Ymd',SERVER_OPEN_TIME))+86400;//开服结束时间(当天)
		$today=strtotime("today");
		$data=$row=array();
		if($today==strtotime(date('Ymd',SERVER_OPEN_TIME))){
			//当开服时间 为今天 才进行推送数据
			//当天角色数
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'4');
			$condition=array('creat_time'=>array('$gte'=>$start_time,'$lt'=>$end_time));
			$row['char_count']=$mdb->allCount('characters', $condition);
			$row['open_time']=$today;
			$row['agent_id']=$agent_id;
			$row['time']=time();
			$row['sid']=intval(substr(SERVER_ID,1));
			ksort($row);
			$data[]=$row;

			$phprpc_client = new PHPRPC_Client();
			$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
			$phprpc_client->setEncryptMode(1);
			$phprpc_client->setEnableGZIP(true);
			$phprpc_client->setTimeout(30);
			$result_insert=$phprpc_client->server_open_count($data);
		}
	}

	//消费与产出
	public function stat_cost($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();
		$domain=$this->getDomain($type);
		$transit_type=$type.'_stat_cost';
		$sql="select date from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['date'])){
			$sql="select min(date) as date from stat_cost";
			$list=$this->mysqli->findOne($sql);
			$min_date=empty($list['date']) ? date('Y-m-d',SERVER_OPEN_TIME) : $list['date'];
		}else{
			$min_date=$list['date'];
		}
		$start_time=$start_time>0 ? $start_time : strtotime($min_date);
		$end_time=$end_time>0 ? $end_time : time();
		$start_date=date('Y-m-d',$start_time);
		$end_date=date('Y-m-d',$end_time);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=200;
		$offset=0;
		$flag=$start_date;
		while($limit>0){
			$data=array();
			$sql="select * from stat_cost where date>='$start_date' and date<='$end_date' order by date asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			while($result && $row=$result->fetch_assoc()){
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
		        $flag=$row['date']>$flag ? $row['date'] : $flag;
				$data[]=$row;
			}
			$result_insert=$phprpc_client->stat_cost($data);
			if($result_insert===true){
				//插入成功
				$date=$flag;
				$flag=strtotime($date);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data) < $limit) break;
				$offset +=$limit;
		}
	}

	//活跃玩家分等级统计
	public function active_player($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();
		$domain=$this->getDomain($type);

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);

		//活跃玩家 40 50 60 70
		$start_time=strtotime("yesterday");
		$end_time=$start_time+86400-1;
		$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=40 and is_first!=1 ";
		$count_40=$this->mysqli->count($sql);
		$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=50 and level>=41 and is_first!=1 ";
		$count_50=$this->mysqli->count($sql);
		$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level<=60 and level>=51 and is_first!=1 ";
		$count_60=$this->mysqli->count($sql);
		$sql="select count(distinct account) as allow_player  from log_login where login_time>=$start_time and login_time<=$end_time and level>=61 and is_first!=1 ";
		$count_70=$this->mysqli->count($sql);
		$total_count=$count_40+$count_50+$count_60+$count_70;

		$data[0]=array(
			'date'=>date('Y-m-d',$start_time),
			'40_level'=>$count_40,
			'50_level'=>$count_50,
			'60_level'=>$count_60,
			'70_level'=>$count_70,
			'agent_id'=>$agent_id,
			'count'=>$total_count,
			'sid'=>intval(substr(SERVER_ID,1)),
			'time'=>time(),
		);
		ksort($data[0]);
		$phprpc_client->active_player($data);
	}

	//推送玩家数据Sửa日志
	public function update_data($type,$start_time=0,$end_time=0){
		$agent_id=$this->getAgentId();//获取代理id
		$domain=$this->getDomain($type);

		$transit_type=$type.'_'.__FUNCTION__;
		$sql="select flag from stat_transit_flag where type='$transit_type'";
		$list=$this->mysqli->findOne($sql);
		if(empty($list['flag'])){
			$sql="select min(time) as time from log_update_data";
			$list=$this->mysqli->findOne($sql);
			$min_time=empty($list['time']) ? 0 : intval($list['time']);
		}else{
			$min_time=intval($list['flag']);
		}

		$start_time=$start_time>0 ? $start_time : $min_time;
		$end_time=$end_time>0 ? $end_time : time();

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$domain}/{$type}/app/interface/transit_data.php");
		$phprpc_client->setEncryptMode(1);
		$phprpc_client->setEnableGZIP(true);
		$phprpc_client->setTimeout(30);
		$limit=100;
		$offset=0;
		while ($limit>0){
			$sql="select * from log_update_data where time>=$start_time and time<=$end_time order by time asc limit $offset,$limit";
			$result=$this->mysqli->query($sql);
			$data=array();
			$flag=$start_time;
			while ($row=$result->fetch_assoc()){
				unset($row['id']);
				$row['agent_id']=$agent_id;
				$row['sid']=intval(substr(SERVER_ID,1));
				ksort($row);//重新排序key
				$data[]=$row;
				$flag=$row['time']>$flag ? $row['time'] : $flag;
			}
			$result=$phprpc_client->{__FUNCTION__}($data);
			if($result===true){
				//插入成功
				$date=date('Y-m-d',$flag);
				$sql="replace into stat_transit_flag (type,date,flag) value ('$transit_type','$date',$flag)";
				$this->mysqli->query($sql);
			}else{
				$limit=$offset=0;
				break;
			}
			if(count($data)<$limit || $offset/$limit>10){
				$limit=$offset=0;
				break;
			}
			$offset+=$limit;
		}
	}

}