<?php
//活动解析类
class Activity {
	/*
	 * 活动模板说明
	 */

	static public function getInfo() {
		return array(
		1 => array(
                'name' => __('在线活动'),
                'info' => '[activity type="1" start="2013-06-24 10:54:05" end="2013-06-26 10:54:09" span="0"]<br>[param]<br>[level min="1" max="100"][/level]<br>[rule]<br>[online type="1" time="10"][/online]<br>[race id="11,21,31,41"]<br>[money type="1" num="1" name="铜币"][/money]<br>[money type="2" num="2" name="铜券"][/money]<br>[money type="3" num="3" name="元宝"][/money]<br>[money type="4" num="4" name="礼券"][/money]<br>[item id="50111000004" num="22" bind="0" name="传说·破军之剑"][/item]<br>[item id="51231000102" num="1" bind="0" name="优秀·清风饰品"][/item]<br>[/race]<br>[/rule]<br>[/param]<br>[/activity]'
                )
                // [activity]<br>[param]<br>[level][/level]<br>[rule]<br>[online][/online]<br>[race]<br>[money][/money]<br><i>more money……</i><br>[item][/item]<br><i>more item……</i><br>[/race]<br>[/rule]<br><i>more rule……</i><br>[/param]<br>[/activity]
                );
	}

	public function __construct() {

	}

	/*
	 * 解析活动配置
	 */

	public function getActivity($activity_text) {
		$activity_text = strip_tags($activity_text, '<p><br/>');
		$activity_pattern = '#\[activity\s+type\s*=\s*"(\d+)"\s+start\s*=\s*"(.*)"\s+end\s*=\s*"(.*)"\s+span\s*=\s*"(\d+)"\s*\](.*)\[/activity\]#Us';
		$matchs = preg_match_all($activity_pattern, $activity_text, $match_activitys, PREG_SET_ORDER);
		if ($matchs < 1 || $matchs === false)
		ajax_return('error', 'activity ' . __('格式有误'));
		$activitys = array();
		foreach ($match_activitys as $match_activity) {
			$activity['type'] = intval($match_activity[1]);
			$activity['param'] = $this->{'getActivity' . $match_activity[1]}($match_activity[5]);
			//保存编辑内容
			$activity['txt'] = '';
			$start = strtotime($match_activity[2]);
			$end = strtotime($match_activity[3]);
			//分天处理
			if ($match_activity[4] == 1) {
				$t = $start;
				do {
					$s = strtotime(date('Y-m-d 00:00:00', $t));
					$e = strtotime(date('Y-m-d 00:00:00', $t + 86400)) - 1;
					$ts = $s < $start ? $start : $s;
					$te = $e > $end ? $end : $e;
					$activity['start'] = $ts;
					$activity['over'] = $te;
					$activity['txt'] = '[activity type="' . $match_activity[1] . '" start="' . date('Y-m-d H:i:s', $ts) . '" end="' . date('Y-m-d H:i:s', $te) . '" span="0"]' . $match_activity[5] . '[/activity]';
					$activitys[] = $activity;
					$t+=86400;
				} while ($t <= $end);
			} else {
				$activity['start'] = $start;
				$activity['over'] = $end;
				$activity['txt'] = '[activity type="' . $match_activity[1] . '" start="' . $match_activity[2] . '" end="' . $match_activity[3] . '" span="0"]' . $match_activity[5] . '[/activity]';
				$activitys[] = $activity;
			}
		}
		return $activitys;
	}

	/*
	 * 显示活动参数
	 */

	public function showActivity($activity_txt) {
		$activity_txt = strip_tags($activity_txt, '<p><br/>');
		$activity_pattern = '#\[activity\s+type\s*=\s*"(\d+)"\s+start\s*=\s*"(.*)"\s+end\s*=\s*"(.*)"\s+span\s*=\s*"(\d+)"\s*\](.*)\[/activity\]#Us';
		$matchs = preg_match($activity_pattern, $activity_txt, $match_activitys);
		if ($matchs < 1 || $matchs === false)
		ajax_return('error', 'activity ' . __('格式有误'));
		return $this->{'showActivity' . $match_activitys[1]}($match_activitys[5]);
	}

	/*
	 * 活动1解析
	 */

	private function getActivity1($activity) {
		//参数解析
		$param_pattern = '#\[param\](.*)\[/param\]#Us';
		$matchs = preg_match($param_pattern, $activity, $match_param);
		if ($matchs < 1 || $matchs === false)
		ajax_return('error', 'param ' . __('格式有误'));
		//等级规则
		$level_pattern = '#\[level\s+min\s*=\s*"(\d+)"\s+max\s*=\s*"(\d+)"\](.*)\[/level\]#Us';
		$matchs = preg_match($level_pattern, $match_param[1], $match_level);
		if ($matchs < 1 || $matchs === false)
		ajax_return('error', 'level ' . __('格式有误'));
		$param['minlv'] = intval($match_level[1]);
		$param['maxlv'] = intval($match_level[2]);
		//规则解析
		$rule_pattern = '#\[rule\](.*)\[/rule\]#Us';
		preg_match_all($rule_pattern, $match_param[1], $match_rules, PREG_SET_ORDER);
		//活动类型解析
		$online_pattern = '#\[online\s+type\s*=\s*"(\d+)"\s+time\s*=\s*"(\d+)"\](.*)\[/online\]#Us';
		//职业解析
		$race_pattern = '#\[race\s+id\s*=\s*"([\d,]+)"\](.*)\[/race\]#Us';
		//online验证
		$online_verify = array();
		foreach ($match_rules as $match_rule) {
			$matchs = preg_match($online_pattern, $match_rule[1], $match_online);
			if ($matchs < 1 || $matchs === false)
			ajax_return('error', 'online ' . __('格式有误'));
			$typeid = intval($match_online[1]);
			$time_flag_sec = intval($match_online[2]) * 60;
			isset($online_verify[$typeid]) && in_array($time_flag_sec, $online_verify[$typeid]) && ajax_return('error', __('不能设置两个同样的online'));
			$rule = array('typeid' => $typeid, 'time_flag_sec' => $time_flag_sec);
			$online_verify[$typeid][] = $time_flag_sec;
			$matchs = preg_match_all($race_pattern, $match_rule[1], $match_races, PREG_SET_ORDER);
			if ($matchs < 1 || $matchs === false)
			ajax_return('error', 'race ' . __('格式有误'));
			$race_exists = array();
			$count11item = $count21item = $count31item = $count41item = 0;
			$count11money = $count21money = $count31money = $count41money = 0;
			foreach ($match_races as $match_race) {
				$reward = $this->getReward($match_race[2]);
				if (strpos($match_race[1], ',') === false) {
					if (isset($rule[$match_race[1]])) {
						foreach ($reward as $key => $val) {
							switch ($key) {
								case 'item':
									foreach ($val as $v) {
										if (isset($race_exists[$match_race[1]][$key][$v[0]][$v[2]])) {
											$locat = $race_exists[$match_race[1]][$key][$v[0]][$v[2]];
											$rule[$match_race[1]][$key][$locat][1] += $v[1];
										} else {
											$rule[$match_race[1]][$key][] = $v;
											$race_exists[$match_race[1]][$key][$v[0]][$v[2]] = ${'count' . $match_race[1] . $key};
											++${'count' . $match_race[1] . $key};
										}
									}
									break;
								case 'money':
									foreach ($val as $v) {
										if (isset($race_exists[$match_race[1]][$key][$v[0]])) {
											$locat = $race_exists[$match_race[1]][$key][$v[0]];
											$rule[$match_race[1]][$key][$locat][1] += $v[1];
										} else {
											$rule[$match_race[1]][$key][] = $v;
											$race_exists[$match_race[1]][$key][$v[0]] = ${'count' . $match_race[1] . $key};
											++${'count' . $match_race[1] . $key};
										}
									}
									break;
							}
						}
					} else {
						$rule[$match_race[1]] = $reward;
						foreach ($reward as $key => $val) {
							switch ($key) {
								case 'item':
									foreach ($val as $v) {
										$race_exists[$match_race[1]][$key][$v[0]][$v[2]] = ${'count' . $match_race[1] . $key};
										++${'count' . $match_race[1] . $key};
									}
									break;
								case 'money':
									foreach ($val as $v) {
										$race_exists[$match_race[1]][$key][$v[0]] = ${'count' . $match_race[1] . $key};
										++${'count' . $match_race[1] . $key};
									}
									break;
							}
						}
					}
				} else {
					$races = explode(',', $match_race[1]);
					foreach ($races as $race) {
						if (isset($rule[$race])) {
							foreach ($reward as $key => $val) {
								switch ($key) {
									case 'item':
										foreach ($val as $v) {
											if (isset($race_exists[$race][$key][$v[0]][$v[2]])) {
												$locat = $race_exists[$race][$key][$v[0]][$v[2]];
												$rule[$race][$key][$locat][1] += $v[1];
											} else {
												$rule[$race][$key][] = $v;
												$race_exists[$race][$key][$v[0]][$v[2]] = ${'count' . $race . $key};
												++${'count' . $race . $key};
											}
										}
										break;
									case 'money':
										foreach ($val as $v) {
											if (isset($race_exists[$race][$key][$v[0]])) {
												$locat = $race_exists[$race][$key][$v[0]];
												$rule[$race][$key][$locat][1] += $v[1];
											} else {
												$rule[$race][$key][] = $v;
												$race_exists[$race][$key][$v[0]] = ${'count' . $race . $key};
												++${'count' . $race . $key};
											}
										}
										break;
								}
							}
						} else {
							$rule[$race] = $reward;
							foreach ($reward as $key => $val) {
								switch ($key) {
									case 'item':
										foreach ($val as $v) {
											$race_exists[$race][$key][$v[0]][$v[2]] = ${'count' . $race . $key};
											++${'count' . $race . $key};
										}
										break;
									case 'money':
										foreach ($val as $v) {
											$race_exists[$race][$key][$v[0]] = ${'count' . $race . $key};
											++${'count' . $race . $key};
										}
										break;
								}
							}
						}
					}
				}
			}
			$param['rule_list'][] = $rule;
		}
		return $param;
	}

	/*
	 * 解析活动1参数
	 */

	private function showActivity1($activity) {
		$show = '';
		//参数解析
		$param_pattern = '#\[param\](.*)\[/param\]#Us';
		preg_match($param_pattern, $activity, $match_param);
		//等级规则
		$level_pattern = '#\[level\s+min\s*=\s*"(\d+)"\s+max\s*=\s*"(\d+)"\](.*)\[/level\]#Us';
		$matchs = preg_match($level_pattern, $match_param[1], $match_level);
		$show .= __('等级') . '：' . intval($match_level[1]) . ' - ' . intval($match_level[2]) . '<br/>';
		//规则解析
		$rule_pattern = '#\[rule\](.*)\[/rule\]#Us';
		preg_match_all($rule_pattern, $match_param[1], $match_rules, PREG_SET_ORDER);
		//活动类型解析
		$online_pattern = '#\[online\s+type\s*=\s*"(\d+)"\s+time\s*=\s*"(\d+)"\](.*)\[/online\]#Us';
		//职业解析
		$race_pattern = '#\[race\s+id\s*=\s*"([\d,]+)"\](.*)\[/race\]#Us';
		//online验证
		$online_verify = array();
		$race_type = array(
		11 => __('破天'),
		21 => __('舞月'),
		31 => __('飞羽'),
		41 => __('幻雪')
		);
		foreach ($match_rules as $match_rule) {
			$matchs = preg_match($online_pattern, $match_rule[1], $match_online);
			$typeid = intval($match_online[1]);
			$time_flag_sec = intval($match_online[2]);
			$show .= $typeid == 1 ? __('累积') : __('连续');
			$show .= __('在线') . '：' . $time_flag_sec . ' min <br/>';
			$matchs = preg_match_all($race_pattern, $match_rule[1], $match_races, PREG_SET_ORDER);
			$show .= __('职业') . '：';
			foreach ($match_races as $match_race) {
				$reward = $this->showReward($match_race[2]);
				if (strpos($match_race[1], ',') === false) {
					$show .= $race_type[$match_race[1]] . '<br>' . $reward;
				} else {
					$races = explode(',', $match_race[1]);
					foreach ($races as $race) {
						$show .= $race_type[$race] . ' ';
					}
					$show .= '<br>' . $reward;
				}
			}
		}
		return $show;
	}

	//验证解析 param,并转成mongo储存格式
	public function getParam($type,$param){
		switch ($type){
			case 1:
				$news=array();
				foreach ($param['rule_list'] as $row){
					$occ=$row['occ'];
					$param_new=array();
					$param_new['typeid']=intval($row['typeid']);
					$param_new['time_flag_sec']=intval($row['time_flag_sec']);
					$occ_type=array(11,21,31,41);
					//职业
					foreach($occ_type as $key=>$value){
						if(isset($row[$value]['item']) && isset($row[$value]['money'])){
							foreach($row[$value]['item'] as $id=>$item){
								$param_new[$value]['item'][$id][0]=$item[0];
								$param_new[$value]['item'][$id][1]=intval($item[1]);
								$param_new[$value]['item'][$id][2]=intval($item[2]);
							}
							foreach($row[$value]['money'] as $id=>$item){
								$param_new[$value]['money'][$id][0]=intval($item[0]);
								$param_new[$value]['money'][$id][1]=intval($item[1]);
							}
						}else if(isset($row[$value]['item']) && !isset($row[$value]['money'])){
							foreach($row[$value]['item'] as $id=>$item){
								$param_new[$value]['item'][$id][0]=$item[0];
								$param_new[$value]['item'][$id][1]=intval($item[1]);
								$param_new[$value]['item'][$id][2]=intval($item[2]);
							}
						}else if(!isset($row[$value]['item']) && isset($row[$value]['money'])){
							foreach($row[$value]['money'] as $id=>$item){
								$param_new[$value]['money'][$id][0]=intval($item[0]);
								$param_new[$value]['money'][$id][1]=intval($item[1]);
							}
						}
					}

					$news['rule_list'][]=$param_new;
				}
				$news['minlv']=intval($param['minlv']);
				$news['maxlv']=intval($param['maxlv']);
				$param=array();
				$param=$news;
				break;
			case 2:
				//怪物掉落
				foreach ($param as &$list){
					$list['model']=intval($list['model']);
					foreach ($list['occ'] as &$val){
						$val=intval($val);
					}
					foreach ($list['pack'] as &$row){
						$row['type']=intval($row['type']);
						$row['per']=doubleval($row['per']);
						switch ($row['type']){
							case 1:
							case 2:
								foreach ($row['item'] as $key=>&$item){
									if($key!='itemId'){
										$item=intval($item);
									}
								}
								break;
							case 3:
								foreach ($row['item'] as &$items){
									foreach($items['item'] as $key=>&$item){
										if($key!='itemId'){
											$item=intval($item);
										}
									}
									$items['per']=intval($items['per']);
								}
								$row['count']=intval($row['count']);
								break;
						}
					}
				}
				break;

			case 3:
				//砸蛋活动,分成两部部分 egg=砸蛋  exchange=积分兑换
				foreach ($param as $type=>&$p){
					$p['start']=intval($p['start']);
					$p['over']=intval($p['over']);
					foreach ($p['pack'] as &$pack){
						if($type=='egg'){
							$pack['eggType']=intval($pack['eggType']);
							$pack['lost']=intval($pack['lost']);
							$pack['count']=intval($pack['count']);
							$pack['point']=intval($pack['point']);
							foreach ($pack['reward'] as &$reward){
								$reward['per']=floatval($reward['per']);
								if(isset($reward['item'])&&is_array($reward['item'])){
									$reward['bdc']=intval($reward['bdc']);
									$reward['item']['number']=intval($reward['item']['number']);
									$reward['item']['bind']=intval($reward['item']['bind']);
								}
								if(isset($reward['money'])&&is_array($reward['money'])){
									$reward['money']['money']=intval($reward['money']['money']);
									$reward['money']['type']=intval($reward['money']['type']);
								}
							}
						}elseif($type=='exchange'){
							$pack['id']=intval($pack['id']);
							$pack['point']=intval($pack['point']);
							$pack['per']=intval($pack['per']);
							$pack['total']=intval($pack['total']);
							if(isset($pack['outMoney'])&&is_array($pack['outMoney'])){
								$pack['outMoney']['money']=intval($pack['outMoney']['money']);
								$pack['outMoney']['type']=intval($pack['outMoney']['type']);
							}
							if(isset($pack['inItem'])&&is_array($pack['inItem'])){
								$pack['inItem']['number']=intval($pack['inItem']['number']);
								$pack['inItem']['bind']=intval($pack['inItem']['bind']);
							}
						}
					}
				}
				break;

			case 5:
				//累计充值
				foreach ($param as &$list){
					isset($list['index'])&&$list['index']=intval($list['index']);
					isset($list['cost'])&&$list['cost']=intval($list['cost']);
					isset($list['limit'])&&$list['limit']=intval($list['limit']);
					isset($list['limitType'])&&$list['limitType']=intval($list['limitType']);
					if(isset($list['itemList'])&&is_array($list['itemList'])){
						foreach ($list['itemList'] as &$item){
							isset($item['number'])&&$item['number']=intval($item['number']);
							isset($item['bind'])&&$item['bind']=intval($item['bind']);
						}
					}
					if(isset($list['moneyList'])&&is_array($list['moneyList'])){
						foreach ($list['moneyList'] as &$item){
							isset($item['money'])&&$item['money']=intval($item['money']);
							isset($item['type'])&&$item['type']=intval($item['type']);
						}
					}
				}
				break;

			case 6:
				//有福同享
				isset($param['minCount'])&&$param['minCount']=intval($param['minCount']);
				isset($param['minCharge'])&&$param['minCharge']=intval($param['minCharge']);
				if(isset($param['rewardList'])&&is_array($param['rewardList'])){
					foreach ($param['rewardList'] as &$rewards){
						isset($rewards['min'])&&$rewards['min']=intval($rewards['min']);
						isset($rewards['max'])&&$rewards['max']=intval($rewards['max']);
						isset($rewards['index'])&&$rewards['index']=intval($rewards['index']);

						if(isset($rewards['itemList'])&&is_array($rewards['itemList'])){
							foreach ($rewards['itemList'] as &$item){
								isset($item['number'])&&$item['number']=intval($item['number']);
								isset($item['bind'])&&$item['bind']=intval($item['bind']);
							}
						}

						if(isset($rewards['moneyList'])&&is_array($rewards['moneyList'])){
							foreach ($rewards['moneyList'] as &$item){
								isset($item['money'])&&$item['money']=intval($item['money']);
								isset($item['type'])&&$item['type']=intval($item['type']);
							}
						}
					}
				}
				break;

			case 7:
				//兑换活动
				foreach ($param as &$list){
					isset($list['id'])&&$list['id']=intval($list['id']);
					isset($list['limit'])&&$list['limit']=intval($list['limit']);
					isset($list['count'])&&$list['count']=intval($list['count']);
					isset($list['total'])&&$list['total']=intval($list['total']);
					isset($list['bdc'])&&$list['bdc']=intval($list['bdc']);
					isset($list['occ'])&&$list['occ']=intval($list['occ']);
					if(isset($list['aim']['item'])&&is_array($list['aim']['item'])){
						foreach ($list['aim']['item'] as &$item){
							isset($item['number'])&&$item['number']=intval($item['number']);
							isset($item['bind'])&&$item['bind']=intval($item['bind']);
						}
					}
					if(isset($list['aim']['money'])&&is_array($list['aim']['money'])){
						foreach ($list['aim']['money'] as &$item){
							isset($item['money'])&&$item['money']=intval($item['money']);
							isset($item['type'])&&$item['type']=intval($item['type']);
						}
					}
					if(isset($list['need']['item'])&&is_array($list['need']['item'])){
						foreach ($list['need']['item'] as &$item){
							isset($item['number'])&&$item['number']=intval($item['number']);
							isset($item['bind'])&&$item['bind']=intval($item['bind']);
						}
					}
					if(isset($list['need']['money'])&&is_array($list['need']['money'])){
						foreach ($list['need']['money'] as &$item){
							isset($item['money'])&&$item['money']=intval($item['money']);
							isset($item['type'])&&$item['type']=intval($item['type']);
						}
					}
				}
				break;

			case 8:
				//buff活动
				foreach ($param as &$list){
					isset($list['id'])&&$list['id']=intval($list['id']);
					isset($list['buffId'])&&$list['buffId']=intval($list['buffId']);
					isset($list['addition'])&&$list['addition']=intval($list['addition']);
					if(isset($list['time'])&&is_array($list['time'])){
						foreach ($list['time'] as &$item){
							isset($item[0])&&$item[0]=intval($item[0]);
							isset($item[1])&&$item[1]=intval($item[1]);
						}
					}
				}

				break;
			case 9:
				//世界杯活动
				foreach ($param['schedule'] as &$schedule){
					$schedule['index']=intval($schedule['index']);
					$schedule['win']=intval($schedule['win']);
					$schedule['time']=intval($schedule['time']);
				}
				foreach ($param['quiz'] as &$quiz){
					$quiz['id']=intval($quiz['id']);
					$quiz['moneyType']=intval($quiz['moneyType']);
					$quiz['money']=intval($quiz['money']);
					$quiz['recharge']=intval($quiz['recharge']);
					if(isset($quiz['itemList'])&&is_array($quiz['itemList'])){
						foreach ($quiz['itemList'] as &$item){
							isset($item['number'])&&$item['number']=intval($item['number']);
							isset($item['bind'])&&$item['bind']=intval($item['bind']);
						}
					}
					if(isset($quiz['moneyList'])&&is_array($quiz['moneyList'])){
						foreach ($quiz['moneyList'] as &$item){
							isset($item['money'])&&$item['money']=intval($item['money']);
							isset($item['type'])&&$item['type']=intval($item['type']);
						}
					}
					if(isset($quiz['failItemList'])&&is_array($quiz['failItemList'])){
						foreach ($quiz['failItemList'] as &$item){
							isset($item['number'])&&$item['number']=intval($item['number']);
							isset($item['bind'])&&$item['bind']=intval($item['bind']);
						}
					}
					if(isset($quiz['failMoneyList'])&&is_array($quiz['failMoneyList'])){
						foreach ($quiz['failMoneyList'] as &$item){
							isset($item['money'])&&$item['money']=intval($item['money']);
							isset($item['type'])&&$item['type']=intval($item['type']);
						}
					}
				}
				foreach ($param['other'] as &$other){
					$other['moneyType']=intval($other['moneyType']);
					$other['level']=intval($other['level']);
				}
				break;
			case 10:
				//翅膀进化
				$news=array();
				foreach ($param as $row){
					$param_new['occ']=$row['occ'];
					$param_new=array();
					$param_new['type']=intval($row['type']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					$param_new['occ']=intval($row['occ']);
					$param_new['aim']=intval($row['aim']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 11:
				//装备神化
				$news=array();
				foreach ($param as $row){
					$param_new['occ']=$row['occ'];
					$param_new=array();
					$param_new['type']=intval($row['type']);
					$param_new['level']=intval($row['level']);
					$param_new['number']=intval($row['number']);
					$param_new['count']=intval($row['count']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					$param_new['occ']=intval($row['occ']);
					$param_new['aim']=intval($row['aim']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 12:
				//宝石雕刻
				$news=array();
				foreach ($param as $row){
					$param_new['occ']=$row['occ'];
					$param_new=array();
					$param_new['type']=intval($row['type']);
					$param_new['level']=intval($row['level']);
					$param_new['number']=intval($row['number']);
					$param_new['count']=intval($row['count']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					$param_new['occ']=intval($row['occ']);
					$param_new['aim']=intval($row['aim']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 13:
				//装备强化
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['fireLv']=intval($row['fireLv']);
					$param_new['number']=intval($row['number']);
					$param_new['strongLv']=intval($row['strongLv']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 14:
				//坐骑升阶
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['series']=intval($row['series']);
					$param_new['level']=intval($row['level']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 15:
				//伙伴境界升阶
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['level']=intval($row['level']);
					$param_new['type']=intval($row['type']);
					$param_new['chong']=intval($row['chong']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 16:
				$news=array();
				foreach($param as $row){
					$param_new=array();
					$param_new['id']=intval($row['id']);
					$param_new['type']=intval($row['type']);
					$param_new['index']=intval($row['index']);
					$param_new['occ']=intval($row['occ']);
					$param_new['cost']=intval($row['cost']);
					$param_new['limit']=intval($row['limit']);
					$param_new['total']=intval($row['total']);
					$param_new['count']=intval($row['count']);
					$param_new['bdc']=intval($row['bdc']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 17:
				foreach ($param as &$list){
					foreach ($list['entryId'] as &$val){
						$val=intval($val);
					}
					foreach ($list['occ'] as &$val){
						$val=intval($val);
					}
					foreach ($list['pack'] as &$row){
						$row['type']=intval($row['type']);
						$row['per']=doubleval($row['per']);
						switch ($row['type']){
							case 1:
							case 2:
								foreach ($row['item'] as $key=>&$item){
									if($key!='itemId'){
										$item=intval($item);
									}
								}
								break;
							case 3:
								foreach ($row['item'] as &$items){
									foreach($items['item'] as $key=>&$item){
										if($key!='itemId'){
											$item=intval($item);
										}
									}
									$items['per']=intval($items['per']);
								}
								$row['count']=intval($row['count']);
								break;
						}
					}
				}
				break;
			case 18:
				//充值返利
				$news=array();

				$row = $param;
				$param_new=array();
				$param_new['totalRebate']=intval($row['totalRebate']);
				$param_new['rebatePer']=intval($row['rebatePer']);
				$news=$param_new;

				$param=array();
				$param=$news;
				break;
			case 19:
				//伙伴装备升阶
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['step']=intval($row['step']);
					$param_new['number']=intval($row['number']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 20:
				//伙伴装备升级
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['step']=intval($row['step']);
					$param_new['number']=intval($row['number']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 21:
				//坐骑装备精炼
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['step']=intval($row['step']);
					$param_new['number']=intval($row['number']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 22:
				//武魂升级
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['level']=intval($row['level']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 23:
				//武魂祭炼
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['level']=intval($row['level']);
					$param_new['id']=intval($row['id']);
					$param_new['index']=intval($row['index']);
					if(isset($row['itemList'])){
						foreach($row['itemList'] as $id=>$item){
							$param_new['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['itemList'][$id]['number']=intval($item['number']);
							$param_new['itemList'][$id]['bind']=intval($item['bind']);
						}
					}
					if(isset($row['moneyList'])){
						foreach($row['moneyList'] as $id=>$item){
							$param_new['moneyList'][$id]['money']=intval($item['money']);
							$param_new['moneyList'][$id]['type']=intval($item['type']);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
			case 24:
				//充值转盘
				$new=array();
				$param_new=array();
				$param_new['day']=intval($param['day']);
				$param_new['initJade']=intval($param['initJade']);
				
				$reward_new=array();
				foreach($param['reward'] as $one_reward){
						$reward_temp=array();
						$reward_temp['id']=intval($one_reward['id']);
						$reward_temp['type']=intval($one_reward['type']);
						if($reward_temp['type']==1){
							foreach($one_reward['itemList'] as $item){
								$item_new=array();
								$item_new['itemId']=$item['itemId'];	
								$item_new['number']=intval($item['number']);	
								$item_new['bind']=intval($item['bind']);	
								$reward_temp['itemList'][]=$item_new;
							}
						}
						else{
							$reward_temp['percent']=intval($one_reward['percent']);
						}
						$reward_new[]=$reward_temp;
				}
				$param_new['reward']=$reward_new;
				
				$config_new=array();
				foreach($param['config'] as $one_config){
						$config_temp=array();
						$config_temp['count']=intval($one_config['count']);
						$config_temp['needJade']=intval($one_config['needJade']);
						foreach($one_config['weightList'] as $one_weight){
							$weight_new=array();
							$weight_new['id']=intval($one_weight['id']);
							$weight_new['weight']=intval($one_weight['weight']);
							$config_temp['weightList'][]=$weight_new;
						}
						$config_new[]=$config_temp;
				}
				$param_new['config']=$config_new;
				$param=array();
				$param=$param_new;
				break;
			case 25:
			case 26:
				//充值排行
				$news=array();
				foreach ($param as $row){
					$param_new=array();
					$param_new['minRank']=intval($row['minRank']);
					$param_new['maxRank']=intval($row['maxRank']);
					if(isset($row['reward']['itemList'])){
						foreach($row['reward']['itemList'] as $id=>$item){
							$param_new['reward']['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['reward']['itemList'][$id]['number']=intval($item['number']);
							$param_new['reward']['itemList'][$id]['bind']=intval($item['bind']);
							$param_new['reward']['itemList'][$id]['effect']=intval($item['effect']);
						}
					}
					if(isset($row['reward']['moneyList'])){
						foreach($row['reward']['moneyList'] as $id=>$item){
							$param_new['reward']['moneyList'][$id]['money']=intval($item['money']);
							$param_new['reward']['moneyList'][$id]['type']=intval($item['type']);
							$param_new['reward']['moneyList'][$id]['effect']=intval($item['effect']);
						}
					}
					if(isset($row['reward']['otherList'])){
						foreach($row['reward']['otherList'] as $id=>$item){
							$param_new['reward']['otherList'][$id]=intval($item);
							$param_new['reward']['otherList'][$id]=intval($item);
							$param_new['reward']['effect'][$id]=intval($row['reward']['effect'][$id]);
						}
					}

					$param_new['reward']['extraReward']['minJade']=intval($row['reward']['extraReward']['minJade']);
					if(isset($row['reward']['extraReward']['itemList'])){
						foreach($row['reward']['extraReward']['itemList'] as $id=>$item){
							$param_new['reward']['extraReward']['itemList'][$id]['itemId']=$item['itemId'];
							$param_new['reward']['extraReward']['itemList'][$id]['number']=intval($item['number']);
							$param_new['reward']['extraReward']['itemList'][$id]['bind']=intval($item['bind']);
							$param_new['reward']['extraReward']['itemList'][$id]['effect']=intval($item['effect']);
						}
					}
					if(isset($row['reward']['extraReward']['moneyList'])){
						foreach($row['reward']['extraReward']['moneyList'] as $id=>$item){
							$param_new['reward']['extraReward']['moneyList'][$id]['money']=intval($item['money']);
							$param_new['reward']['extraReward']['moneyList'][$id]['type']=intval($item['type']);
							$param_new['reward']['extraReward']['moneyList'][$id]['effect']=intval($item['effect']);
						}
					}
					if(isset($row['reward']['extraReward']['otherList'])){
						foreach($row['reward']['extraReward']['otherList'] as $id=>$item){
							$param_new['reward']['extraReward']['otherList'][$id]=intval($item);
							$param_new['reward']['extraReward']['otherList'][$id]=intval($item);
							$param_new['reward']['extraReward']['effect'][$id]=intval($row['reward']['extraReward']['effect'][$id]);
						}
					}
					$news[]=$param_new;
				}
				$param=array();
				$param=$news;
				break;
		}
		return $param;
	}

	/*
	 * 获取奖励列表
	 */

	private function getReward($rewardcontent) {
		$items = $this->getItem($rewardcontent);
		$moneys = $this->getMoney($rewardcontent);
		$reward = array();
		!empty($items) && $reward['item'] = $items;
		!empty($moneys) && $reward['money'] = $moneys;
		if (empty($reward))
		ajax_return('error', __('奖励格式有误'));
		return $reward;
	}

	/*
	 * 获取道具列表
	 */

	private function getItem($item_text) {
		$item_pattern = '#\[item\s+id\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"\s+bind\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/item\]#Us';
		$matchs = preg_match_all($item_pattern, $item_text, $match_items, PREG_SET_ORDER);
		if (!$matchs)
		return null;
		$items = array();
		$item_exists = array();
		$i = 0;
		foreach ($match_items as $match_item) {
			$id = trim($match_item[1]);
			$num = intval($match_item[2]);
			$bind = intval($match_item[3]);
			if (isset($item_exists[$id][$bind])) {
				$items[$item_exists[$id][$bind]][1] += $num;
			} else {
				$items[] = array($id, $num, $bind);
				$item_exists[$id][$bind] = $i;
				++$i;
			}
		}
		return $items;
	}

	/*
	 * 获取货币列表
	 */

	private function getMoney($money_text) {
		$money_pattern = '#\[money\s+type\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/money\]#Us';
		$matchs = preg_match_all($money_pattern, $money_text, $match_moneys, PREG_SET_ORDER);
		if (!$matchs)
		return null;
		$moneys = array();
		$i = 0;
		$money_exists = array();
		foreach ($match_moneys as $match_money) {
			$match_type = intval($match_money[1]);
			$match_num = intval($match_money[2]);
			if (isset($money_exists[$match_type])) {
				$moneys[$money_exists[$match_type]][1] += $match_num;
			} else {
				$moneys[] = array($match_type, $match_num);
				$money_exists[$match_type] = $i;  //保存位置
				++$i;
			}
		}
		return $moneys;
	}

	/*
	 * 显示奖励
	 */

	public function showReward($rewardcontent) {
		$items = $this->showItem($rewardcontent);
		$moneys = $this->showMoney($rewardcontent);
		$reward = __('奖励列表') . '：<br/>';
		!empty($items) && $reward .= $items;
		!empty($moneys) && $reward .= $moneys;
		return $reward;
	}

	/*
	 * 显示道具列表
	 */

	private function showItem($item_text) {
		$item_pattern = '#\[item\s+id\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"\s+bind\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/item\]#Us';
		$matchs = preg_match_all($item_pattern, $item_text, $match_items, PREG_SET_ORDER);
		if (!$matchs)
		return '';
		$items = '';
		foreach ($match_items as $match_item) {
			$items .= __($match_item[1]) . '【';
			$items .=intval($match_item[3]) ? __('非绑定') : __('绑定');
			$items .= '】：' . intval($match_item[2]) . '<br/>';
		}
		return $items;
	}

	/*
	 * 显示货币
	 */

	private function showMoney($money_text) {
		$money_pattern = '#\[money\s+type\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/money\]#Us';
		$money_types = array(
		1 => __('铜币'),
		2 => __('铜券'),
		3 => __('元宝'),
		4 => __('礼券'),
		);
		$matchs = preg_match_all($money_pattern, $money_text, $match_moneys, PREG_SET_ORDER);
		if (!$matchs)
		return '';
		$moneys = '';
		foreach ($match_moneys as $match_money) {
			$moneys .= $money_types[$match_money[1]] . '：' . intval($match_money[2]) . '<br>';
		}
		return $moneys;
	}
}

?>
