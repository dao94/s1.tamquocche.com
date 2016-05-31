<?php
//主要提供一些pm模块相关的功能接口
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __LIB__ . 'phprpc_php/phprpc_server.php';
include __AUTH__ . 'lang.php';

class PmApi{
	/*
	 * 奖励审核接口
	 * 奖励申请列表id  Sửa奖励状态  gmer
	 * $data格式 array('id'=>array(),'status'=>1,'gmer'=>'abc')
	 * 单服提供给单服、中央服、代理中央三方使用
	 */
	function pm_reward_verify($data) {
		$gmer = my_escape_string($data['gmer']);
		if (empty($data['id']))
		return array('status' => 'error', 'info' => __('请选择申请列表'));
		$ids = implode(',', $data['id']);
		switch ($data['status']) {
			case 2:
				//拒绝
				$apply_list_update_sql = 'update `reward_apply` set `verify_ts`=' . time() . ',`verifyer`="' . $gmer . '",`status`=2 where `id` in (' . $ids . ') and `status` = 0';
				$mysqli = new DbMysqli();
				$query = $mysqli->query($apply_list_update_sql);
				if (!$query)
				return array('status' => 'error', 'info' => __('更新数据库状态失败'));
				return array('status' => 'ok', 'info' => __('操作成功'));
				break;

			case 1:
				//通过，发放申请奖励
				$apply_list_sql = 'select `id`,`applyer`,`account`,`char_id`,`char_name`,`reward_list`,`email_title`,`reason`,`email_content` from `reward_apply` where `id` in(' . $ids . ') and `status` = 0';
				$mysqli = new DbMysqli();
				$apply_list_query = $mysqli->query($apply_list_sql);
				$apply_list_update_sql = 'update `reward_apply` set `verify_ts`=' . time() . ',`verifyer`="' . $gmer . '",`status`=1 where `id` in (' . $ids . ') and `status` = 0';
				$reward_list_sql = 'insert into `reward_log`(`id`,`account`,`char_id`,`char_name`,`gm`,`reason`,`email_title`,`email_content`,`send_ts`,`reward_list`) values ';
				$email_msg = array();
				$send_ts = time();
				while ($apply_row = $apply_list_query->fetch_assoc()) {
					//生成邮件对象，生成sql语句
					$emailid = uniqid("apply{$apply_row['id']}_");
					$reward_list_sql .= sprintf("('%s','%s',%s,'%s','%s','%s','%s','%s',%d,'%s'),", $emailid, my_escape_string($apply_row['account']), $apply_row['char_id'], my_escape_string($apply_row['char_name']), $gmer, my_escape_string($apply_row['reason']), my_escape_string($apply_row['email_title']), my_escape_string($apply_row['email_content']), $send_ts, $apply_row['reward_list']);
					$sub_email_msg = json_decode($apply_row['reward_list'], true);
					$sub_email_msg['list'] = array(
						array('charId' => floatval($apply_row['char_id']), 'emailId' => $emailid)
					);
					$sub_email_msg['title'] = $apply_row['email_title'];
					$sub_email_msg['content'] = $apply_row['email_content'];
					$email_msg['email'][] = $sub_email_msg;
				}
				$apply_update = $mysqli->query($apply_list_update_sql);
				if (!$apply_update)
					return array('status' => 'error', 'info' => __('更改状态失败，审核失败。'));
				$reward_insert = $mysqli->query(trim($reward_list_sql, ','));
				if (!$reward_insert)
					return array('status' => 'error', 'info' => __('奖励列表入库失败，奖励发放失败。'));

				//通知GM 发放励
				include __CLASSES__ . 'Gm.class.php';
				$gm = new Gm();
				$rpc = 'borpc/boemail.rpc';
				$rpc_obj = 'borpc\\Sour_B2oEmail';
				$async = 'b2ocreateEmailList_async';
				$gm->async($rpc, $rpc_obj, $async, $email_msg);
				return array('status' => 'ok', 'info' => __('奖励发送成功。'));
				break;
		}
	}

	/*
	 * 活动的更新和Xóa
	 * 操作  活动id列表  活动pm_activity类型列表
	 * $data= array('action'=>,'ids'=>'','types'=>)
	 */
	function pm_activity($data) {
		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$rpc='brrpc/bractivity.rpc';
		$rpc_obj='brrpc\\Sour_B2rActivity';
		$async='b2rUpdateActivity_async';
		switch ($data['action']){
			case 'add':
				$data['type']=intval($data['type']);
				$data['start']=intval($data['start']);
				$data['over']=intval($data['over']);
				$data['time']=time();
				$condition=array(
					'type'=>$data['type'],
					'over'=>array('$gt'=>$data['start']),
					'start'=>array('$lt'=>$data['over']),
				);
				$count=$mdb->count('activity',$condition);
				if($count){
					if($data['type']==9){
						//世界杯活动频繁更新,先Xóa，再添加
						if(!$mdb->remove('activity',$condition)){
							return array('status'=>0,'info'=>__('世界杯活动更新失败'));
						}
					}else{
						return array('status'=>0,'info'=>__('活动已存在，请Xóa后再操作'));
					}
				}

				$activity=new Activity();
				$data['param']=$activity->getParam($data['type'],$data['param']);

				if($data['is_xml']==1&&$data['activity_xml']){
					include __CLASSES__.'ActivityXml.class.php';
					$ActivityXml=new ActivityXml();
					$ActivityXml->type=$data['type'];
					$ActivityXml->activity_xml=$data['activity_xml'];
					$result=$ActivityXml->write();
					if(!$result){
						return array('status'=>0,'info'=>__('xml配置写入失败'));
					}
				}
				unset($data['action'],$data['activity_xml']);
				$count=$mdb->count('activity', array('_id'=>$data['id']));
				if($count){
					$result=$mdb->update('activity',array('_id'=>$data['id']),array('$set'=>$data));//_id更新
				}else{
					$data['_id']=$data['id'];
					unset($data['id']);
					$result=$mdb->insert('activity',$data);//_id插入
				}
				if(!$result){
					return array('status'=>0,'info'=>__('数据插入失败'));
				}
				break;

			case 'update':
				$data['type']=intval($data['type']);
				$data['start']=intval($data['start']);
				$data['over']=intval($data['over']);
				$data['time']=time();
				$condition=array(
					'type'=>$data['type'],
					'over'=>array('$gt'=>$data['start']),
					'start'=>array('$lt'=>$data['over']),
					'_id'=>array('$ne'=>$data['id']),
				);
				$count=$mdb->count('activity',$condition);
				if($count){
					return array('status'=>0,'info'=>__('在同时间段已有活动'));
				}
				$activity=new Activity();
				$data['param']=$activity->getParam($data['type'],$data['param']);

				if($data['is_xml']==1&&$data['activity_xml']){
					include __CLASSES__.'ActivityXml.class.php';
					$ActivityXml=new ActivityXml();
					$ActivityXml->type=$data['type'];
					$ActivityXml->activity_xml=$data['activity_xml'];
					$result=$ActivityXml->write();
					if(!$result){
						return array('status'=>0,'info'=>__('xml配置写入失败'));
					}
				}

				$id=$data['id'];
				unset($data['id'],$data['action']);
				$result=$mdb->update('activity', array('_id'=>$id), $data);
				if(!$result){
					return array('status'=>0,'info'=>__('数据更新失败'));
				}
				break;

			case 'remove':
				//Xóa数据
				$result=$mdb->remove('activity',array('_id'=>$data['id'],'type' =>intval($data['type'])));
				if(!$result){
					return array('status'=>0,'info'=>__('数据Xóa失败'));
				}
				if($data['is_xml']==1){
					include __CLASSES__.'ActivityXml.class.php';
					$ActivityXml=new ActivityXml();
					$ActivityXml->type=$data['type'];
					$result=$ActivityXml->remove();
					if(!$result){
						return array('status'=>0,'info'=>__('xml配置Xóa失败'));
					}
				}
				break;

			case 'add_mall':
				//新增珍宝阁 type=4
				$not_int_fields=array('gmer','item_id');
				foreach ($data['list'] as $record){
					foreach ($record as $key=>$value){
						if(!in_array($key, $not_int_fields)){
							$record[$key]=intval($value);
						}
					}

					$condition=array(
						'type'=>$data['type'],
						'over'=>array('$gt'=>$record['start']),
						'start'=>array('$lt'=>$record['over']),
						'item_id'=>$record['item_id'],
					);
					$count=$mdb->count('activity',$condition);
					unset($record['id']);
					$record['type']=$data['type'];
					$record['time']=time();
					if($count){
						$result=$mdb->update('activity', $condition, $record);
					}else{
						$result=$mdb->insert('activity', $record);
					}
					if(!$result){
						return array('status'=>0,'info'=>__('数据插入失败'));
					}
				}
				break;

			case 'remove_mall':
				//Xóa珍宝阁数据
				foreach ($data['list'] as $record){
					$result=$mdb->remove('activity',array('item_id'=>$record['item_id'],'type' =>intval($data['type'])));
					if(!$result){
						return array('status'=>0,'info'=>__('数据Xóa失败'));
					}
				}
				break;

			/***1、2暂时无视之*/
			case 1:
				//更新
				$act_types = array_unique($data['types']);
				$act_ids = $data['ids'];
				if (empty($act_ids) || empty($act_types)) {
					return array('status' => 'error', 'info' => __('请选择活动'));
				}
				$mdb->update('activity', array('_id' => array('$in' => $act_ids)), array('$set' => array('status' => 1)), array('multiple' => 1));
				$gm=new Gm();
				foreach ($act_types as $type) {
					$msg = array('type' => $type);
					$gm->async($rpc, $rpc_obj, $async, $msg);
				}
				return array('status' => 'ok', 'info' => __('活动更新成功'));
				break;

			case 2:
				//Xóa
				$act_types = array_unique($data['types']);
				$act_ids = $data['ids'];
				$remove_resule = $mdb->remove('activity', array('_id' => array('$in' => $act_ids)));
				$gm=new Gm();
				foreach ($act_types as $type) {
					$msg = array('type' => $type);
					$gm->async($rpc, $rpc_obj, $async, $msg);
				}
				return array('status' => 'ok', 'info' => __('Xóa活动成功'));
				break;

			default:
				return array('status' => 'error', 'info' => 'forbid');
				break;
		}
		$gm=new Gm();
		$msg=array('type'=>intval($data['type']));
		$gm->async($rpc,$rpc_obj,$async,$msg);
		return array('status'=>1,'info'=>__('操作成功'));
	}


	/*
	 * 功能开关
	 * $data = array('type'=>,'status'=>,'param'=>)
	 */
	function pm_switch($data) {
		$switch_type_conf=array(
			1=>array('name'=>__('防沉迷'),'file'=>'fcm.txt'),//防沉迷
			2=>array('name'=>__('游戏入口'),'file'=>'game_flag'),//游戏入口
			3=>array('name'=>__('聊天监控'),'file'=>'chat_flag'),//聊天监控
			4=>array('name'=>__('自动创号'),'file'=>'auto_flag'),//自动创号
		);
		if(!array_key_exists($data['type'], $switch_type_conf)){
			return array('status' => 'error', 'info' => __('类型错误'));
		}
		$file_name=__SWITCH__.$switch_type_conf[$data['type']]['file'];
		$content=time();
		if($data['type']==1){
			//防沉迷需要参数
			$content=empty($data['param']) ? 1 : intval($data['param']);//首次提示,单位：分钟
			$content=$content*60;//转成秒
		}
		switch ($data['status']) {
			case 1:
				//开启，生成文件
				if(file_put_contents($file_name,$content)){
					return array('status'=>1,'info'=>__('开启成功'));
				}else{
					return array('status'=>0,'info'=>__('开启失败'));
				}
				break;

			case 0:
				//关闭，Xóa文件
				if(is_file($file_name)&&!unlink($file_name)){
					return array('status'=>0,'info'=>__('关闭失败，请运维检查权限文件').$file_name);
				}
				return array('status' =>1, 'info' => __('关闭成功'));
				break;

			default:
				return array('status'=>0,'info'=>__('非法操作'));
				break;
		}
	}

	/*
	 * 系统广播
	 * 播放停止
	 * $data=array('ids'=>,'status'=>);
	 * 设置
	 * $data=array(
	 *   array(id,typeid,start,end,span,content)
	 * )
	 */
	function pm_broadcasting($data) {
		if (!isset($data['gmer'])) {
			return array('status' => 'error', 'info' => 'forbid');
		}
		$rpc = 'brrpc/broadcast.rpc';
		$rpc_obj = 'brrpc\\Sour_B2rBroadcast';
		$async = 'b2rUpdateBroadcast_async';
		include __CLASSES__ . 'Gm.class.php';
		include __CLASSES__ . 'Mdb.class.php';
		$gm = new Gm();
		$mongo = new Mdb();
		$mongo->selectDb(MONGO_PERFIX . '5');
		switch ($data['action']) {
			//播放、停止、Xóa
			case 'start':
			case 'stop':
				$status = $data['action'] == 'start' ? 1 : 0;
				$state = $data['action'] == 'start' ? 1 : 4;
				$ids = isset($data['data']['ids']) ? $data['data']['ids'] : array();
				if (empty($ids))
				return array('status' => 'error', 'info' => __('请选择消息'));
				$msg_data = array(
	                'msgId' => $ids,
	                'state' => $state
				);
				$gm->async($rpc, $rpc_obj, $async, $msg_data);
				$result = $mongo->update('bg_broadcast', array('msgId' => array('$in' => $ids)), array('$set' => array('status' => $status)), array('multiple' => 1));
				if ($result) {
					return array('status' => 'ok', 'info' => __('操作成功'));
				} else {
					return array('status' => 'error', 'info' => __('操作成功,数据库更新失败'));
				}
				break;
				//添加
			case 'add':
				$ids = array();
				foreach ($data['data'] as $msg) {
					if (empty($msg['msgId'])) {
						$ids[] = $db_msg['msgId'] = uniqid();
						$status = 1;
					} else {
						$ids[] = $db_msg['msgId'] = $msg['msgId'];
						$status = 3;
					}
					$db_msg['typeid'] = intval($msg['type']);
					$db_msg['span'] = intval($msg['span']) * 60;
					if (date('Y-m-d H:i:s', $start = strtotime($msg['start'])) !== $msg['start']) {
						return array('status' => 'error', 'info' => __('开始时间格式错误'));
					}
					$db_msg['startTm'] = $start;
					if (date('Y-m-d H:i:s', $end = strtotime($msg['end'])) !== $msg['end']) {
						return array('status' => 'error', 'info' => __('结束时间格式错误'));
					}
					$db_msg['overTm'] = $end;
					if ($end <= $start + $db_msg['span']) {
						return array('status' => 'error', 'info' => __('开始结束时间段设置有误'));
					}
					$content = strip_tags($msg['content'], '<a><strong><b><font>');
					$content = preg_replace('#<strong>(.*)</strong>#U', '<b>\1</b>', $content);
					if (empty($content)) {
						return array('status' => 'error', 'info' => __('内容不能为空'));
					}

					$db_msg['content'] = $content;
					$db_msg['status'] = 1;
					$db_msg['gmer'] = $data['gmer'];
					$mongo->update('bg_broadcast', array('msgId' => $msg['msgId']), $db_msg, array('upsert' => 1));
				}
				$mongo->close();
				$msg_data = array(
	                'msgId' => $ids,
	                'state' => $status
				);
				$gm->async($rpc, $rpc_obj, $async, $msg_data);
				return array('status' => 'ok', 'info' => __('消息设置成功'));
				break;
			case 'del':
				$ids = $data['data']['ids'];
				if (empty($ids))
				return array('status' => 'error', 'info' => __('请选择消息'));
				$msg_data = array(
	                'msgId' => $ids,
	                'state' => 2
				);
				$gm->async($rpc, $rpc_obj, $async, $msg_data);
				$mongo->remove('bg_broadcast', array('msgId' => array('$in' => $ids)));
				$mongo->close();
				return array('status' => 'ok', 'info' => __('Xóa成功'));
				break;
		}
	}

	//卡类活动
	function pm_card($data) {
		$action = isset($data['action']) ? $data['action'] : '';
		switch ($action) {
			case 'add':
				//增加卡类活动
				$db_data=array();
				//不为空的字段
				$fields=array('agent_id','sid','type','limit','name','count','category_parent','category','verify','start_date','end_date','gmer','union_id');
				//整型字段
				$integer_fields=array('agent_id','sid','type','limit','count','category_parent','category','verify','union_id');
				foreach ($data as $key=>$value){
					if(in_array($key,$fields) && $value!==''){
						$db_data[$key]=in_array($key,$integer_fields) ? intval($value) : $value;
					}
				}
				//中央服只进行初步验证，单服接口再进行详细验证
				if(count($fields)!=count($db_data)){
					return array('status'=>0,'info'=>__('参数错误')."({$db_data['type']})");
				}
				empty($data['money']) ? '' :$db_data['money']=json_decode($data['money'],true);
				empty($data['item']) ? '' :$db_data['item']=json_decode($data['item'],true);
				$db_data['start']=strtotime($db_data['start_date']);
				$db_data['over']=strtotime($db_data['end_date']);
				$db_data['url']=empty($data['url']) ? '' : trim($data['url']);

				if($db_data['type']>999 || $db_data['type']<1){
					return array('status'=>0,'info'=>__('卡号为1-999的数字')."({$db_data['type']})");
				}else if($db_data['limit']>100000 || $db_data['limit']<1){
					return array('status'=>0,'info'=>__('卡号数量溢出，应为1-100000的数字')."({$db_data['type']})");
				}else if(empty($db_data['name'])){
					return array('status'=>0,'info'=>__('卡名不能为空')."({$db_data['type']})");
				}else if(date('Y-m-d H:i:s',$db_data['start'])!=$db_data['start_date'] ||
					date('Y-m-d H:i:s',$db_data['over'])!=$db_data['end_date'] || $db_data['start']>$db_data['over']){
					return array('status'=>0,'info'=>__('Thời gian设置有误')."({$db_data['type']})");
				}else if($db_data['url']){
					$urls=parse_url($db_data['url']);
					if (!isset($urls['scheme']) || !isset($urls['host'])){
						return array('status'=>0,'info'=>__('url设置错误')."({$db_data['type']})");
					}
				}else if(empty($db_data['money'])&&empty($db_data['item'])){
					return array('status'=>0,'info'=>__('奖励不能为空')."({$db_data['type']})");
				}

				//移除不必要数据
				unset($db_data['start_date'],$db_data['end_date']);
				//数据库验证
				include __CLASSES__.'Mdb.class.php';
				$mdb=new Mdb();
				$mdb->selectDb(MONGO_PERFIX.'5');
				$count=$mdb->count('card', array('type'=>$db_data['type']));
				if($count>0){
					//已存在此卡,则先Xóa
					$mdb->remove('card', array('type'=>$db_data['type']));
					//return array('status'=>0,'info'=>__('已存在此卡')."({$db_data['type']})");
				}
				$result=$mdb->insert('card',$db_data);
				if(!$result){
					return array('status'=>0,'info'=>__('数据入库失败，设置活动失败')."({$db_data['type']})");
				}

				//通知GM服务器
				include __CLASSES__.'Gm.class.php';
				$gm=new Gm();
				$msg=array('type'=>array($db_data['type']),'status'=>1);
				$rpc='brrpc/brcard.rpc';
				$rpc_obj='brrpc\\Sour_B2rCardReward';
				$async='b2rUpdateCardStatus_async';
				$gm->async($rpc, $rpc_obj, $async, $msg);
				return array('status'=>1,'info'=>__('活动设置成功')."【{$db_data['type']}-{$db_data['name']}】");
				break;

			//Xóa活动
			case 'remove':
				$type=empty($data['type']) ? 0 : intval($data['type']);
				if(!$type)	return array('status'=>0,'info'=>__('请选择要Xóa的卡')."($type)");

				//Xóa数据库
				include __CLASSES__.'Mdb.class.php';
				$mdb=new Mdb();
				$mdb->selectDb(MONGO_PERFIX.'5');
				$result=$mdb->remove('card',array('type' =>$type));
				if (!$result) {
					return array('status'=>0,'info'=>__('Xóa数据失败')."【{$type}】-{$data['name']}");
				}

				//发送更新协议
				include __CLASSES__.'Gm.class.php';
				$rpc='brrpc/brcard.rpc';
				$rpc_obj='brrpc\\Sour_B2rCardReward';
				$async='b2rUpdateCardStatus_async';
				$msg=array(
					'type'=>array($type),
					'status'=>2
				);
				$gm = new Gm();
				$gm->async($rpc, $rpc_obj, $async, $msg);
				return array('status'=>1,'info'=>__('Xóa活动成功')."【{$type}-{$data['name']}】");
				break;
			default:
				return array('status' => 'error', 'info' => __('非法操作'));
				break;
		}
	}

	//服务器配置Sửa
	function pm_server($data) {
		$action = isset($data['action']) ? $data['action'] : '';
		$val = isset($data['val']) ? trim($data['val']) : '';
		$val = strip_tags($val, '<br>');
		switch ($action) {
			//区名
			case 'server_title':
			case 'server_open_time':
			case 'server_dependence':
				if ($action == 'server_open_time') {
					$server_open_time = strtotime($val);
					if (date('Y-m-d H:i:s', $server_open_time) != $val) {
						return array('status' => 'error', 'info' => __('时间格式错误'));
					}
					$val = $server_open_time;
				}elseif($action=='server_dependence'){
					$val = trim(str_replace(array('<br/>', "\t", "\n", '，', '<br>'), array(',', ',', ',', ',', ','), $val), ',');
					$val = explode(',', $val);
					$val = array_map("trim", $val);
					$val = array_unique($val);
					$val = array_filter($val);
					$val = array_map("intval", $val);
					$val = implode(',',$val);
				}
				$val = my_escape_string($val);
				$server_config_f = __CONFIG__ . 'server_config.php'; //服务器配置文件
				$server_config = array();
				//保存原始内容
				$server_config['SERVER_AGENT'] = defined('SERVER_AGENT') ? SERVER_AGENT : '';
				$server_config['SERVER_ID'] = defined('SERVER_ID') ? SERVER_ID : '';
				$server_config['SERVER_DEBUG'] = defined('SERVER_DEBUG') ? SERVER_DEBUG : 0;
				$server_config['DEFAULT_LANG'] = defined('DEFAULT_LANG') ? DEFAULT_LANG : 'zh-cn';
				$server_config['SERVER_TITLE'] = defined('SERVER_TITLE') ? SERVER_TITLE : '';
				$server_config['SERVER_OPEN_TIME'] = defined('SERVER_OPEN_TIME') ? SERVER_OPEN_TIME : '';
				$server_config['SERVER_DEPENDENCE'] = defined('SERVER_DEPENDENCE') ? SERVER_DEPENDENCE : '';
				defined('GAME_HOST') ? $server_config['GAME_HOST']=GAME_HOST : '';
				$server_config['GM_HOST']=defined('GM_HOST') ? GM_HOST : 'localhost';
				$server_config['GAME_PORT']=defined('GAME_PORT') ? GAME_PORT : 8000;
				$server_config['BL_PORT']='10041+(GAME_PORT-8000)/10*50';
				$server_config['GM_PORT']='10042+(GAME_PORT-8000)/10*50';

				//Sửa内容
				$server_config[strtoupper($action)] = $val;
				//生成新配置内容
				$server_config_c = '';
				foreach ($server_config as $key => $v) {
					if($key=='BL_PORT' || $key=='GM_PORT'){
						//BL_PORT和GM_PORT端口是通过计算赋值
						$server_config_c.="define('$key',$v);\n";
					}else{
						$server_config_c.="define('$key','$v');\n";
					}
				}
				//写入新内容
				if (is_writeable($server_config_f)) {
					$val=$action=='server_open_time' ? date('Y-m-d H:i:s',$val) : $val;
					return file_put_contents($server_config_f, "<?php\n" . $server_config_c . "?>") > 0 ? array('status' => 'ok', 'info' => __('Sửa成功'),'data'=>$val) : array('status' => 'error', 'info' => __('Sửa失败'));
				} else {
					return array('status' => 'error', 'info' => $server_config_f . __('不可写,Sửa失败'));
				}
				break;

				//游戏链接
			case 'guan':
			case 'xuan':
			case 'pay':
			case 'bbs':
			case 'fcm':
			case 'kefu':
			case 'zhuanyekefu':
			case 'zhujiu':
			case 'userc':
			case 'yuyue':
			case 'huiyuan':
			case 'nianfei':
			case 'launcher':
				$url_config_f = __CONFIG__ . 'url_config.php'; //游戏Link文件
				file_exists($url_config_f) && include $url_config_f;
				!isset($url_config) && $url_config = array();
				$url_config[$action] = $val;
				if (is_writeable(__CONFIG__)) {
					return file_put_contents($url_config_f, "<?php\n\$url_config = " . var_export($url_config, true) . ";\n?>") > 0 ? array('status' => 'ok', 'info' => __('Sửa成功'),'data'=>$val) : array('status' => 'error', 'info' => __('Sửa失败'));
				} else {
					return array('status' => 'error', 'info' => __CONFIG__ . __('不可写,Sửa失败'));
				}
				break;
				//ip白名单
			case 'ips':
				$ip_config_f = __CONFIG__ . 'ip_config.php'; //ip白名单配置文件
				file_exists($ip_config_f) && include $ip_config_f;
				!isset($ips) && $ips = array();
				$val = trim(str_replace(array('<br/>', "\t", "\n", '，', '<br>'), array(',', ',', ',', ',', ','), $val), ',');
				$ips = explode(',', $val);
				$ips = array_map("trim", $ips);
				$ips = array_unique($ips);
				$ips = array_filter($ips);
				$ips = array_map("my_escape_string", $ips);
				if (is_writeable($ip_config_f)) {
					return file_put_contents($ip_config_f, "<?php\n\$ips = " . var_export($ips, true) . ";\n?>") > 0 ? array('status' => 'ok', 'info' => __('Sửa成功')) : array('status' => 'error', 'info' => __('Sửa失败'));
				} else {
					return array('status' => 'error', 'info' => $server_config_f . __('不可写,Sửa失败'));
				}
				break;
			default:
				return array('status' => 'error', 'info' => __('非法操作'));
				break;
		}
}

	//内部号GM号
	function pm_gmer($data) {
		!isset($data['status']) || !isset($data['type']) || !isset($data['id']) && ajax_return('error', __('请确认信息'));
		$mysqli = new DbMysqli();
		$sql = 'update `internal_account` set `status`=' . intval($data['status']) . ',`verifyer`="' . $data['gmer'] . '",`verify_ts`=' . time() . ' where `type`=' . intval($data['type']) . ' and `char_id` in (' . implode(',', $data['id']) . ')';
		$query = $mysqli->query($sql);
		if (!$query && $mysqli->close())
		return array('status' => 'error', 'info' => __('更新数据失败'));
		switch ($data['type']) {
			//内部号直接更新状态即可
			case 1:
				break;
				//GM和新手指导员需要更新状态再发送GM协议
			case 2:
				$msg_data = array();
				if ($data['status'] == 1) {
					$listSql = 'select `char_id`,`apply_type` from `internal_account` where `type` =2 and `char_id` in(' . implode(',', $data['id']) . ')';
					$list = $mysqli->query($listSql);
					while ($row = $list->fetch_assoc()) {
						$msg_data['items'][] = array(
	                        'value' => floatval($row['char_id']),
	                        'key' => intval($row['apply_type'])
						);
					}
					$mysqli->close();
				} else {
					foreach ($data['id'] as $char_id) {
						$msg_data['items'][] = array(
	                        'value' => floatval($char_id),
	                        'key' => 0
						);
					}
				}

				include __CLASSES__ . 'Gm.class.php';
				$gm = new Gm();
				$rpc = 'borpc/boidentity.rpc';
				$rpc_obj = 'borpc\\Sour_B2oIdentity';
				$async = 'changeIdentity_async';
				$gm->async($rpc, $rpc_obj, $async, $msg_data);
				break;
		}
		return array('status' => 'ok', 'info' => __('设置成功'));
	}

	//条件发送奖励
	function pm_email($data) {
		//写入数据库， 发送更新命令
		switch ($data['action']) {
			case 'add':
				//中央后台发放邮件
				if(empty($data['id'])||empty($data['gmer'])){
					return array('status'=>'error', 'info'=> __('参数缺失'));
				}
				$mdb=new Mdb();
				$mdb->selectDb(MONGO_PERFIX.'4');
				$count=$mdb->count('boemail',array('eventid'=>$data['id']));
				if($count){
					return array('status'=>0,'info'=>__('邮件已存在'));
				}elseif(time()-$data['start_time']>10){
					return array('status'=>0,'info'=>__('开始时间已过时，发送失败'));
				}

				$record=array(
					'eventid'=>$data['id'],
					'starttime'=>intval($data['start_time']),
					'endtime'=>intval($data['end_time']),
					'level'=>array(intval($data['min_level']),intval($data['max_level'])),
					'occ'=>json_decode($data['occ'],true),
					'camps'=>json_decode($data['camp'],true),
					'title'=>$data['title'],
					'content'=>$data['content'],
					'gmer'=>$data['gmer'],
					'type'=>intval($data['type']),
					'state'=>1,
					'time'=>time(),
				);
				if(!empty($data['item_list'])){
					$record['itemList']=json_decode($data['item_list'],true);
				}
				if(!empty($data['money_list'])){
					$record['moneyList']=json_decode($data['money_list'],true);
				}

				$result=$mdb->insert('boemail', $record);//入库
				if($result){
					include __CLASSES__ . 'Gm.class.php';
					$gm=new Gm();
					$rpc='borpc/boemail.rpc';
					$rpc_obj='borpc\\Sour_B2oEmail';
					$async='b2ocreateEmailListAll_async';
					$msg_data=array('id'=>$data['id'], 'state'=>1);
					$gm->async($rpc, $rpc_obj, $async, $msg_data);
					return array('status'=>1,'info'=>__('操作成功'));
				}else{
					return array('status'=>0,'info'=>__('邮件入库失败'));
				}
				break;

			case 'remove':
				if(empty($data['id'])){
					return array('status'=>0,'info'=>__('参数ID错误'));
				}
				$mdb=new Mdb();
				$mdb->selectDb(MONGO_PERFIX.'4');
				$result=$mdb->remove('boemail', array('eventid'=>$data['id']));
				if(!$result){
					return array('status'=>0,'info'=>__('参数ID错误'));
				}
				//告知GM停止发送邮件
				include __CLASSES__ . 'Gm.class.php';
				$gm=new Gm();
				$rpc='borpc/boemail.rpc';
				$rpc_obj='borpc\\Sour_B2oEmail';
				$async='b2ocreateEmailListAll_async';
				$msg_data= array('id'=>$data['id'], 'state'=>2);
				$gm->async($rpc, $rpc_obj, $async, $msg_data);
				return array('status'=>1, 'info' => __('操作成功'));
				break;

			case 'set':
				//设置boemail
				$mustkeys = array('type', 'occ', 'camps', 'level', 'title', 'content', 'start', 'end', 'gmer');
				foreach ($mustkeys as $key) {
					if (!isset($data[$key]))
					return array('status' => 'error', 'info' => __('参数缺失'));
				}
				$db_data = array('eventid' => uniqid(), 'state' => 0, 'gmer' => $data['gmer']);
				$db_data['type'] = intval($data['type']);
				//在线情况  1：在线  2： 全服
				if (!in_array($data['type'], array(1, 2)))
					return array('status' => 'error', 'info' => __('请设置在线情况'));
				if (count($data['level']) != 2 || intval($data['level'][0]) > $data['level'][1] || intval($data['level'][0]) <= 0)
					return array('status' => 'error', 'info' => __('请确认等级限制'));
				$db_data['level'] = array(intval($data['level'][0]), intval($data['level'][1]));
				//职业限制
				if (empty($data['occ']))
				return array('status' => 'error', 'info' => __('请确认职业'));
				foreach ($data['occ'] as $occ) {
					if (!in_array($occ, array(11, 21, 31, 41))) {
						return array('status' => 'error', 'info' => __('请确认职业'));
					}
					$db_data['occ'][] = intval($occ);
				}
				//阵营限制
				if (empty($data['camps'])){
					return array('status' => 'error', 'info' => __('请确认阵营'));
				}
				foreach ($data['camps'] as $camps) {
					if (!in_array($camps, array(0,1, 2, 3))) {
						return array('status' => 'error', 'info' => __('请确认阵营'));
					}
					$db_data['camps'][] = intval($camps);
				}

				$db_data['starttime'] = strtotime($data['start']);
				$db_data['endtime'] = strtotime($data['end']);
				//开始结束时间
				if ($db_data['starttime'] >= $db_data['endtime'] || $db_data['starttime'] <= time()+300)
				return array('status' => 'error', 'info' => __('请确认开始结束时间'));
				if ($data['start'] != date('Y-m-d H:i:s', $db_data['starttime']) || $data['end'] != date('Y-m-d H:i:s', $db_data['endtime']))
				return array('status' => 'error', 'info' => __('请确认开始结束时间'));
				//邮件标题
				$db_data['title'] = my_escape_string($data['title']);
				$db_data['content'] = strip_tags($data['content'], '<a><strong><br>');
				$db_data['content'] = str_replace(array('<strong>', '</strong>'), array('<b>', '</b>'), $db_data['content']);
				//生成奖励列表
				$moneyType = array('gold' => 1, 'giftGold' => 2, 'jade' => 3, 'giftJade' => 4);
				if (isset($data['reward'])) {
					foreach ($data['reward'] as $list) {
						if ($list['type'] === 'item' && !empty($list['item']) && !empty($list['num'])) {
							$item_info = explode('|', $list['item']);
							$db_data['itemList'][] = array('itemId' => $item_info[0], 'number' => intval($list['num']), 'bind' => intval($list['bind']));
						} elseif (isset($moneyType[$list['type']])&& !empty($list['num'])) {
							$db_data['moneyList'][] = array('moneyType' => $moneyType[$list['type']], 'money' => intval($list['num']));
						}
					}
				}
				//if(empty($db_data['itemList']) && empty($db_data['moneyList']))	return array('status' => 'error', 'info' => __('请配置奖励列表'));
				include __CLASSES__ . 'Mdb.class.php';
				$mongo = new Mdb();
				$mongo->selectDb(MONGO_PERFIX . '4');
				$result = $mongo->insert('boemail', $db_data);
				$mongo->close();
				return $result ? array('status' => 'ok', 'info' => __('设置数据成功')) : array('status' => 'error', 'info' => __('设置数据失败'));
				break;
				//更新
			case 'update':
				include __CLASSES__ . 'Gm.class.php';
				$gm = new Gm();
				$rpc = 'borpc/boemail.rpc';
				$rpc_obj = 'borpc\\Sour_B2oEmail';
				$async = 'b2ocreateEmailListAll_async';
				$msg_data = array('id' => $data['eventid'], 'state' => $data['status']);
				$gm->async($rpc, $rpc_obj, $async, $msg_data);
				return array('status' => 'ok', 'info' => __('更新成功'));
				break;
			case 'fool_send':
				//简单模式
				$gmer = my_escape_string($data['gmer']);
				$char_type = isset($data['char_type']) ? intval($data['char_type']) : 1;
				$reason = isset($data['reason']) ? my_escape_string(trim($data['reason'])) : '';
				$etitle = isset($data['etitle']) ? my_escape_string(trim($data['etitle'])) : '';
				$econtent = isset($data['econtent']) ? trim(strip_tags($data['econtent'], '<a><strong><br>')) : '';
				$reward = isset($data['reward']) ? $data['reward'] : '';
				$player = isset($data['player']) ?$data['player'] : array();
				//$player_list = array_unique(explode("\n", $player));
				$player_list=array();
				foreach($player as $player_conf){
					$arr=explode("|",$player_conf);
					if($arr[0]==SERVER_AGENT&&$arr[1]==SERVER_ID){
						$player_list[]=$arr[2];
					}
				}
				//校验字段
				if (empty($player_list) || empty($reward) || empty($reason) || empty($etitle) || empty($econtent))
					return array('status' => 'error', 'info' => __('请设置玩家列表、奖励列表、邮件文本内容'));
				$econtent = str_replace(array('<strong>', '</strong>'), array('<b>', '</b>'), $econtent);
				$svemail = array('title' => $etitle, 'content' => $econtent);
				$econtent = my_escape_string($econtent);
				//生成奖励列表
				foreach ($reward as $list) {
					if ($list['type'] === 'item' && !empty($list['num']) && !empty($list['item'])) {
						$item_info = explode('|', $list['item']);
						$svemail['itemList'][] = array('itemId' => $item_info[0], 'number' => $list['num'], 'bind' => intval($list['bind']));
					} elseif (in_array($list['type'], array('gold', 'giftGold', 'jade', 'giftJade')) && !empty($list['num'])) {
						$svemail['moneyList'][$list['type']] = intval($list['num']);
					}
				}
				if(empty($svemail['itemList']) && empty($svemail['moneyList']))	return array('status' => 'error', 'info' => __('请配置奖励列表'));
				$reward_list = array();
				isset($svemail['itemList']) && $reward_list['itemList'] = $svemail['itemList'];
				isset($svemail['moneyList']) && $reward_list['moneyList'] = $svemail['moneyList'];
				$reward_list = my_escape_string(json_encode($reward_list));
				//写流水
				$sql = 'insert into `reward_log`(`id`,`account`,`char_id`,`char_name`,`gm`,`reason`,`email_title`,`email_content`,`send_ts`,`reward_list`) values ';
				include __CLASSES__ . 'Player.class.php';
				$player = new Player();
				$player_exists = $player->players_exists($player_list, $char_type);
				$svemail['list'] = array();
				$send_ts = time();
				$log_data=array();
				foreach ($player_exists as $player => $player_info) {
					if ($player_info === null) {   //退出程序通知说明玩家不存在
						return array('status' => 'error', 'info' => __('玩家不存在'));
					}
					$emailid = uuid();
					$svemail['list'][] = array('charId' => $player_info['id'], 'emailId' => $emailid);
					$sql .= sprintf('("%s","%s",%s,"%s","%s","%s","%s","%s",%d,\'%s\'),', $emailid, $player_info['account'],
						$player_info['id'], $player_info['name'], $gmer, $reason, $svemail['title'], $econtent, $send_ts, $reward_list);
					$log_row=array();
					$log_row['emailid']=$emailid;
					$log_row['server_info']=SERVER_AGENT .'_'. SERVER_ID;
					$log_row['account']=$player_info['account'];
					$log_row['char_id']=$player_info['id'];
					$log_row['char_name']=$player_info['name'];
					$log_row['gm']=$gmer;
					$log_row['reason']=$reason;
					$log_row['email_title']=$svemail['title'];
					$log_row['email_content']=$econtent;
					$log_row['send_ts']=$send_ts;
					$log_row['reward_list']=$reward_list;
					$log_data[]=$log_row;
				}
				$mysqli = new DbMysqli();
				$my_res = $mysqli->query(rtrim($sql, ','));
				$mysqli->close();
				if ($my_res) {
					include __CLASSES__ . '/Gm.class.php';
					$gm = new Gm();
					$rpc = 'borpc/boemail.rpc';
					$rpc_obj = 'borpc\\Sour_B2oEmail';
					$async = 'b2ocreateEmail_async';
					$gm->async($rpc, $rpc_obj, $async, $svemail);
					return array('status' => 'ok', 'info' => __('数据入库成功，发送奖励成功。'),'log_data' => $log_data);
				} else {
					return array('status' => 'error', 'info' => __('数据入库失败，发送奖励失败。'));
				}
				break;
			case 'diff_send':
				//快速模式,转换再处理
				$gmer = my_escape_string($data['gmer']);
				$reward_list = isset($data['reward']) ? $data['reward'] : array();
				$reward_lines = $reward_list[SERVER_AGENT][SERVER_ID];
				$char_type = isset($data['char_type']) ? intval($data['char_type']) : 1;
				$reason = isset($data['reason']) ? my_escape_string(trim($data['reason'])) : '';
				$etitle = isset($data['etitle']) ? my_escape_string(trim($data['etitle'])) : '';
				$econtent = isset($data['econtent']) ? trim(strip_tags($data['econtent'], '<a><strong><br>')) : '';
				//校验字段
				if (empty($reward_lines) || empty($reason) || empty($etitle) || empty($econtent))
					return array('status' => 'error', 'info' => __('玩家列表、奖励列表、邮件文本内容设置有误'));
				$econtent = str_replace(array('<strong>', '</strong>'), array('<b>', '</b>'), $econtent);
				$svemails = array();
				$player_list = array();
				$money_id_type = array(
					1 => 'gold',
					2 => 'giftGold',
					3 => 'jade',
					4 => 'giftJade'
				);
				foreach ($reward_lines as $reward_line) {
					if (trim($reward_line) !== '') {
						$reward_fields = explode('|', $reward_line);
						$reward_fields_count = count($reward_fields);//如果不存在角色id则进行查询获取角色id
						$player = $reward_fields[2];
						if (!isset($svemails[$player])) {
							$svemails[$player]['title'] = $etitle;
							$svemails[$player]['content'] = $econtent;
							$player_list[] = $player;
						}
						if ($reward_fields_count >= 5) {
							if (isset($money_id_type[$reward_fields[3]])) {
								$svemails[$player]['moneyList'][$money_id_type[$reward_fields[3]]] = intval($reward_fields[4]);
							} else if (in_array($reward_fields[3], array('gold', 'giftGold', 'jade', 'giftJade'))) {
								$svemails[$player]['moneyList'][$reward_fields[3]] = intval($reward_fields[4]);
							} else {
								if($reward_fields[3] && $reward_fields[4]){
									$temp = array('itemId' => $reward_fields[3], 'number' => intval($reward_fields[4]), 'bind' => 0);
									isset($reward_fields[5]) && $temp['bind'] = intval($reward_fields[5]) > 0 ? 1 : 0;
									$svemails[$player]['itemList'][] = $temp;
								}
							}
						} else {
							return array('status' => 'error', 'info' => __('格式错误'));
						}
						if(isset($svemails[$player]['itemList']) && count($svemails[$player]['itemList']) + (isset($svemails[$player]['moneyList']) ? 1 : 0) > 10)
							return array('status' => 'error', 'info' => $reward_fields[2] . __('奖励数量超出10个限制'));

					}
				}

				//写流水
				$sql = 'insert into `reward_log`(`id`,`account`,`char_id`,`char_name`,`gm`,`reason`,`email_title`,`email_content`,`send_ts`,`reward_list`) values ';
				include __CLASSES__ . 'Player.class.php';
				$player_obj = new Player();
				$player_exists = $player_obj->players_exists($player_list, $char_type);
				$send_ts = time();
				$msg_obj = array();
				$econtent = my_escape_string($econtent);
				$log_data=array();
				foreach ($player_exists as $player => $player_info) {
					if ($player_info === null) {   //退出程序通知说明玩家不存在
						return array('status' => 'error', 'info' => "发放失败玩家'".$player."'不存在");
					}
					if(!isset($svemails[$player]['itemList']) && !isset($svemails[$player]['moneyList']))
						return array('status' => 'error', 'info' => $player . __('：没有设置奖励或者格式错误'));
					$emailid = uuid();
					$svemails[$player]['list'][] = array('charId' => $player_info['id'], 'emailId' => $emailid);
					$reward_list = array();
					isset($svemails[$player]['itemList']) && $reward_list['itemList'] = $svemails[$player]['itemList'];
					isset($svemails[$player]['moneyList']) && $reward_list['moneyList'] = $svemails[$player]['moneyList'];
					$reward_list = my_escape_string(json_encode($reward_list));
					$sql .= sprintf('("%s","%s",%s,"%s","%s","%s","%s","%s",%d,\'%s\'),', $emailid, $player_info['account'], $player_info['id'], $player_info['name'], $gmer, $reason, $etitle, $econtent, $send_ts, $reward_list);
					$msg_obj['email'][] = $svemails[$player];
					$log_row=array();
					$log_row['emailid']=$emailid;
					$log_row['server_info']=SERVER_AGENT .'_'. SERVER_ID;
					$log_row['account']=$player_info['account'];
					$log_row['char_id']=$player_info['id'];
					$log_row['char_name']=$player_info['name'];
					$log_row['gm']=$gmer;
					$log_row['reason']=$reason;
					$log_row['email_title']=$etitle;
					$log_row['email_content']=$econtent;
					$log_row['send_ts']=$send_ts;
					$log_row['reward_list']=$reward_list;
					$log_data[]=$log_row;
				}
				$mysqli = new DbMysqli();
				$my_res = $mysqli->query(trim($sql, ','));
				$mysqli->close();
				if ($my_res) {
					include __CLASSES__ . '/Gm.class.php';
					$gm = new Gm();
					$rpc = 'borpc/boemail.rpc';
					$rpc_obj = 'borpc\\Sour_B2oEmail';
					$async = 'b2ocreateEmailList_async';
					$gm->async($rpc, $rpc_obj, $async, $msg_obj);
					return array('status' => 'ok', 'info' => __('数据入库成功，发送奖励成功。'),'log_data' => $log_data);
				} else {
					return array('status' => 'error', 'info' => __('数据入库失败，发送奖励失败。'));
				}
				break;
		}
	}

	//更新公告
	function pm_notice($data){
		$xml_file=__XML__.'notice.xml';
		if(file_put_contents($xml_file,$data)){
			return array('status' =>1, 'info' => __('更新成功'));
		}else{
			return array('status' =>0, 'info' => __('更新失败'));
		}
	}
}
$server = new PHPRPC_Server();
$server->add(get_class_methods(PmApi),new PmApi);
$server->setEnableGZIP(true);
$server->setDebugMode(true);
$server->start();
?>
