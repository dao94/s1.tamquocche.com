<?php
Class ActivityXml{
	public $type=0;
	public $param=array();
	public $xml_config=array();
	public $start_time=0;
	public $end_time=0;
	public $activity_xml;
	private $xml_file;//文件名
	private $doc;

	function __construct(){
		$this->doc=new DOMDocument('1.0','utf-8');
		$this->doc->formatOutput=true;
		$this->doc->preserveWhiteSpace=false;
	}
	
	private function xml1($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		return $activity;
	}

	public function list1(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}
	
	private function xml7($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);

		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach ($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('total_limit', $param['total']);
			$reward->setAttribute('alone_limit', $param['limit']);
			$reward->setAttribute('count', $param['count']);
			$reward->setAttribute('occ', $param['occ']);
			$reward=$activity->appendChild($reward);

			//目标道具
			if(!empty($param['aim']['item'])){
				foreach ($param['aim']['item'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('itemId', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['aim']['money'])){
				foreach ($param['aim']['money'] as $money_item){
					$item=$doc->createElement('item');
					$item->setAttribute('currency', $money_item['type']);
					$item->setAttribute('number', $money_item['money']);
					$reward->appendChild($item);
				}
			}
			//兑换所需道具金钱
			if(!empty($param['need'])){
				$terms=$doc->createElement('terms');
				if(!empty($param['need']['item'])){
					foreach ($param['need']['item'] as $need_item){
						$bind=isset($need_item['bind']) ? $need_item['bind'] : 0;
						$item=$doc->createElement('item');
						$item->setAttribute('itemId', $need_item['itemId']);
						$item->setAttribute('number', $need_item['number']);
						$item->setAttribute('bind', $bind);
						$terms->appendChild($item);
					}
				}
				if(!empty($param['need']['money'])){
					foreach ($param['need']['money'] as $money_item){
						$item=$doc->createElement('item');
						$item->setAttribute('currency', $money_item['type']);
						$item->setAttribute('number', $money_item['money']);
						$terms->appendChild($item);
					}
				}
				$reward->appendChild($terms);
			}
		}
		return $activity;
	}

	public function list7(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml8($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);

		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		if(!empty($this->xml_config['item_list'])){
			foreach ($this->xml_config['item_list'] as $item_list){
				$item=$doc->createElement('item');
				$info=$doc->createElement('info');
				$item->appendChild($info);
				$buff_info=$doc->createCDATASection($item_list['buff_info']);
				$info->appendChild($buff_info);
				foreach ($item_list['time_list'] as $row){
					$time=$doc->createElement('time');
					$buff_show_date=$doc->createElement('show_time',$row['buff_show_date']);
					$time->appendChild($buff_show_date);
					$buff_hide_date=$doc->createElement('hide_time',$row['buff_hide_date']);
					$time->appendChild($buff_hide_date);
					$buff_start_date=$doc->createElement('start_time',$row['buff_start_date']);
					$time->appendChild($buff_start_date);
					$buff_end_date=$doc->createElement('end_time',$row['buff_end_date']);
					$time->appendChild($buff_end_date);
					$item->appendChild($time);
				}
				$activity->appendChild($item);
			}
		}
		return $activity;
	}

	public function list8(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
			$xml_config['item_list']=array();
			$items=$activity->getElementsByTagName('item');
			foreach ($items as $item){
				$item_list=array();
				$times=$item->getElementsByTagName('time');
				$item_list['buff_info']=$item->getElementsByTagName('info')->item(0)->nodeValue;
				foreach ($times as $time){
					$arr=array();
					$arr['buff_show_date']=$time->getElementsByTagName('show_time')->item(0)->nodeValue;
					$arr['buff_hide_date']=$time->getElementsByTagName('hide_time')->item(0)->nodeValue;
					$arr['buff_start_date']=$time->getElementsByTagName('start_time')->item(0)->nodeValue;
					$arr['buff_end_date']=$time->getElementsByTagName('end_time')->item(0)->nodeValue;
					$item_list['time_list'][]=$arr;
				}
				$xml_config['item_list'][]=$item_list;
			}
		}
		return $xml_config;
	}

	private function xml9($doc){
		include __CONFIG__.'activity_config.php';
		$world_cup_item=isset($activity_config['world_cup_item']) ? $activity_config['world_cup_item'] : array();

		$root=$doc->createElement('root');
		$root=$doc->appendChild($root);

		$config=$doc->createElement('config');
		$config->setAttribute('startTime',strtotime($this->start_time));
		$config->setAttribute('endTime',strtotime($this->end_time));
		$root->appendChild($config);
		foreach ($this->param['schedule'] as $param){
			$schedule=$doc->createElement('schedule');
			$schedule->setAttribute('id',$param['index']);
			$schedule->setAttribute('time',$param['time']);
			$schedule->setAttribute('win',$param['win']);
			$item=$doc->createElement('item',$param['group'][0]);
			$schedule->appendChild($item);
			$item=$doc->createElement('item',$param['group'][1]);
			$schedule->appendChild($item);

			$flag_num=intval(array_search($param['group'][0], $world_cup_item));
			$flag=$doc->createElement('flag',$flag_num);
			$schedule->appendChild($flag);

			$flag_num=intval(array_search($param['group'][1], $world_cup_item));
			$flag=$doc->createElement('flag',$flag_num);
			$schedule->appendChild($flag);
			$root->appendChild($schedule);
		}
		foreach ($this->param['quiz'] as $param){
			$quiz=$doc->createElement('quiz');
			$quiz->setAttribute('id',$param['id']);
			$quiz->setAttribute('betMoney',$param['money']);
			$quiz->setAttribute('type',$param['moneyType']);
			$quiz->setAttribute('quizNum',$param['recharge']);
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $item_list){
					$item=$doc->createElement('item');
					$item->setAttribute('itemId',$item_list['itemId']);
					$item->setAttribute('number',$item_list['number']);
					$item->setAttribute('bind',$item_list['bind']);
					$quiz->appendChild($item);
				}
			}
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_list){
					$money=$doc->createElement('money');
					$money->setAttribute('money',$money_list['money']);
					$money->setAttribute('type',$money_list['type']);
					$quiz->appendChild($money);
				}
			}
			$root->appendChild($quiz);
		}
		foreach ($this->param['other'] as $param){
			$money=$doc->createElement('money');
			$money->setAttribute('type',$param['moneyType']);
			$money->setAttribute('level',$param['level']);
			$root->appendChild($money);
		}
		return $root;
	}

	private function xml10($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', $param['type']);
			$reward->setAttribute('param', $param['aim']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('itemId', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('item');
					$item->setAttribute('currency', $money_item['type']);
					$item->setAttribute('number', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list10(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml11($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', $param['type']);
			if($param['type']==1){
				$reward->setAttribute('param', $param['count'].','.$param['aim']);
			}else{
				$reward->setAttribute('param', $param['aim']);
			}
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('itemId', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('item');
					$item->setAttribute('currency', $money_item['type']);
					$item->setAttribute('number', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list11(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml12($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', $param['type']);
			if($param['type']==1){
				$reward->setAttribute('param', $param['count'].','.$param['aim']);
			}else{
				$reward->setAttribute('param', $param['aim']);
			}
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('itemId', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('item');
					$item->setAttribute('currency', $money_item['type']);
					$item->setAttribute('number', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list12(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml13($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('state', $param['fireLv']);
			$reward->setAttribute('param', $param['strongLv']);
			$reward->setAttribute('equipNum', $param['number']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list13(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}
	
	private function xml14($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('param', $param['level']);
			$reward->setAttribute('series', $param['series']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list14(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml15($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('jingjie', $param['type']);
			$reward->setAttribute('chong', $param['chong']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list15(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml16($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('total_limit', $param['total']);
			$reward->setAttribute('need', $param['cost']);
			//1代表是个人限制  2代表个人日限制
			if($param['limit']==1){
				$reward->setAttribute('alone_limit', $param['count']);
			}else{
				$reward->setAttribute('count', $param['count']);
			}
			$reward->setAttribute('occ', $param['occ']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('itemId', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('number', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	private function list16(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private  function xml17($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('iconLv', $this->xml_config['icon_level']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);
		
		$id=0;
		$entry_id_list=array();
		foreach($this->param as $key=>$param){
			foreach ($param['entryId'] as $entry_id){
				if(!in_array($entry_id, $entry_id_list)){
					++$id;
					$genre=$doc->createElement('genre');
					$genre->setAttribute('id',$id);
					$genre->setAttribute('scenceId',$entry_id+1);
					$genre->setAttribute('enterId',$entry_id);
					
					$reward=$doc->createElement('reward');
					foreach ($param['pack'] as $pack){
						switch ($pack['type']){
							case 1:
								$item=$doc->createElement('item');
								$item->setAttribute('id',$pack['item']['itemId']);
								$item->setAttribute('number',$pack['item']['number']);
								$item->setAttribute('bind',$pack['item']['bind']);
								$reward->appendChild($item);
								break;
							case 2:
								$money=$doc->createElement('money');
								$money->setAttribute('type',$pack['item']['type']);
								$money->setAttribute('num',$pack['item']['money']);
								$reward->appendChild($money);
								break;
							case 3:
								foreach ($pack['item'] as $items){
									$item=$doc->createElement('item');
									$item->setAttribute('id',$items['item']['itemId']);
									$item->setAttribute('number',$items['item']['number']);
									$item->setAttribute('bind',$items['item']['bind']);
									$reward->appendChild($item);
								}
								break;
						}
					}
					$genre->appendChild($reward);
					$activity->appendChild($genre);
					
					$entry_id_list[]=$entry_id;
				}
			}
		}
		return $activity;
	}
	
	public function list17(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['icon_level']=$activity->getAttribute('iconLv');
		}
		return $xml_config;
	}

	private function xml18($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$limit=$doc->createElement('limit');
		$limit->nodeValue=$this->param['totalRebate'];
		$activity->appendChild($limit);

		$return_ratio=$doc->createElement('return_ratio');
		$return_ratio->nodeValue=$this->param['rebatePer'];
		$activity->appendChild($return_ratio);

		return $activity;
	}

	public function list18(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			//$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml19($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('level', $param['step']);
			$reward->setAttribute('time', $param['number']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list19(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml20($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('level', $param['step']);
			$reward->setAttribute('count', $param['number']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list20(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml21($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('level', $param['step']);
			$reward->setAttribute('time', $param['number']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list21(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml22($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('level', $param['level']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list22(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml23($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$reward=$doc->createElement('reward');
			$reward->setAttribute('id', $param['id']);
			$reward->setAttribute('type', 1);
			$reward->setAttribute('step', $param['level']);
			$reward=$activity->appendChild($reward);

			//道具
			if(!empty($param['itemList'])){
				foreach ($param['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$reward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['moneyList'])){
				foreach ($param['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$reward->appendChild($item);
				}
			}
		}
		return $activity;
	}

	public function list23(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}
	
	private function xml24($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$time=$doc->createElement('time');
		$time->setAttribute('startDay',1);
		$time->setAttribute('overDay',$this->param['day']);
		$activity->appendChild($time);
		
		$lottery=$doc->createElement('lottery');
		foreach($this->param['config'] as $conf){
			$item=$doc->createElement('item');
			$item->setAttribute('gold', $conf['needJade']);
			$item->setAttribute('number', $conf['count']);
			$lottery->appendChild($item);
		}
		$lottery=$activity->appendChild($lottery);
		
		$turntable=$doc->createElement('turntable');
		foreach($this->param['reward'] as $one_reward){
			$gird=$doc->createElement('gird');
			$gird->setAttribute('id',$one_reward['id']);
			$gird->setAttribute('type',$one_reward['type']);
			if($one_reward['type']==1){
				foreach($one_reward['itemList'] as $key=>$one_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $key+1);
					$item->setAttribute('itemId', $one_item['itemId']);
					$item->setAttribute('number', $one_item['number']);
					$item->setAttribute('bind', $one_item['bind']);
					$gird->appendChild($item);
				}
			}
			else {
				$item=$doc->createElement('item');
				$item->setAttribute('id', 1);
				$item->setAttribute('percent', $one_reward['percent']);
				$gird->appendChild($item);
			}
			$turntable->appendChild($gird);			
		}
		$activity->appendChild($turntable);
		
		return $activity;
	}

	public function list24(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			
		}
		return $xml_config;
	}

	private function xml25($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$level=$doc->createElement('level');
			$level->setAttribute('minLv', $param['minRank']);
			$level->setAttribute('maxLv', $param['maxRank']);
			$level=$activity->appendChild($level);

			$baseReward=$doc->createElement('baseReward');
			$basereward=$level->appendChild($baseReward);

			//道具
			if(!empty($param['reward']['itemList'])){
				foreach ($param['reward']['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$item->setAttribute('effect', $aim_item['effect']);
					$baseReward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['reward']['moneyList'])){
				foreach ($param['reward']['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$item->setAttribute('effect', $money_item['effect']);
					$baseReward->appendChild($item);
				}
			}
			//特殊物品
			if(!empty($param['reward']['otherList'])){
				foreach ($param['reward']['otherList'] as $key => $num){
					if(!$num) continue;
					$item=$doc->createElement('money');
					if($key=='exploit'){
						$item->setAttribute('type', 8);
					}
					else if($key=='arenaPoint'){
						$item->setAttribute('type', 10);
					}
					else if($key=='honor'){
						$item->setAttribute('type', 14);
					}
					$item->setAttribute('num', $num);
						$item->setAttribute('effect',$param['reward']['effect'][$key]);
					$baseReward->appendChild($item);
				}
			}

			if($param['reward']['extraReward']['minJade']){
				$extraReward=$doc->createElement('extraReward');
				$extraReward=$level->appendChild($extraReward);

				//额外
				//道具
				$extraReward->setAttribute('minJade',$param['reward']['extraReward']['minJade']);
				if(!empty($param['reward']['extraReward']['itemList'])){
					foreach ($param['reward']['extraReward']['itemList'] as $aim_item){
						$item=$doc->createElement('item');
						$item->setAttribute('id', $aim_item['itemId']);
						$item->setAttribute('number', $aim_item['number']);
						$item->setAttribute('bind', $aim_item['bind']);
						$item->setAttribute('effect', $aim_item['effect']);
						$extraReward->appendChild($item);
					}
				}
				//目标金钱
				if(!empty($param['reward']['extraReward']['moneyList'])){
					foreach ($param['reward']['extraReward']['moneyList'] as $money_item){
						$item=$doc->createElement('money');
						$item->setAttribute('type', $money_item['type']);
						$item->setAttribute('num', $money_item['money']);
						$item->setAttribute('effect', $money_item['effect']);
						$extraReward->appendChild($item);
					}
				}
				//特殊物品
				if(!empty($param['reward']['extraReward']['otherList'])){
					foreach ($param['reward']['extraReward']['otherList'] as $key => $num){
						if(!$num) continue;
						$item=$doc->createElement('money');
						if($key=='exploit'){
							$item->setAttribute('type', 8);
						}
						else if($key=='arenaPoint'){
							$item->setAttribute('type', 10);
						}
						else if($key=='honor'){
							$item->setAttribute('type', 14);
						}
						$item->setAttribute('num', $num);
						$item->setAttribute('effect',$param['reward']['extraReward']['effect'][$key]);
						$extraReward->appendChild($item);
					}
				}
			}
		}
		return $activity;
	}

	public function list25(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}

	private function xml26($doc){
		$config=$doc->createElement('config');
		$config=$doc->appendChild($config);
		$activity=$doc->createElement('activity');
		$activity->setAttribute('type', $this->type);
		$activity->setAttribute('name', $this->xml_config['name']);
		$config->appendChild($activity);

		$show_time=$doc->createElement('show_time');
		$show_time->nodeValue=$this->xml_config['show_start_date'];
		$activity->appendChild($show_time);

		$hide_time=$doc->createElement('hide_time');
		$hide_time->nodeValue=$this->xml_config['show_end_date'];
		$activity->appendChild($hide_time);

		$start_time=$doc->createElement('start_time');
		$start_time->nodeValue=$this->start_time;
		$activity->appendChild($start_time);

		$end_time=$doc->createElement('end_time');
		$end_time->nodeValue=$this->end_time;
		$activity->appendChild($end_time);

		$info=$doc->createElement('info');
		$activity->appendChild($info);
		$cdata_info=$doc->createCDATASection($this->xml_config['info']);
		$info->appendChild($cdata_info);

		foreach($this->param as $param){
			$level=$doc->createElement('level');
			$level->setAttribute('minLv', $param['minRank']);
			$level->setAttribute('maxLv', $param['maxRank']);
			$level=$activity->appendChild($level);

			$baseReward=$doc->createElement('baseReward');
			$basereward=$level->appendChild($baseReward);

			//道具
			if(!empty($param['reward']['itemList'])){
				foreach ($param['reward']['itemList'] as $aim_item){
					$item=$doc->createElement('item');
					$item->setAttribute('id', $aim_item['itemId']);
					$item->setAttribute('number', $aim_item['number']);
					$item->setAttribute('bind', $aim_item['bind']);
					$item->setAttribute('effect', $aim_item['effect']);
					$baseReward->appendChild($item);
				}
			}
			//目标金钱
			if(!empty($param['reward']['moneyList'])){
				foreach ($param['reward']['moneyList'] as $money_item){
					$item=$doc->createElement('money');
					$item->setAttribute('type', $money_item['type']);
					$item->setAttribute('num', $money_item['money']);
					$item->setAttribute('effect', $money_item['effect']);
					$baseReward->appendChild($item);
				}
			}
			//特殊物品
			if(!empty($param['reward']['otherList'])){
				foreach ($param['reward']['otherList'] as $key => $num){
					if(!$num) continue;
					$item=$doc->createElement('money');
					if($key=='exploit'){
						$item->setAttribute('type', 8);
					}
					else if($key=='arenaPoint'){
						$item->setAttribute('type', 10);
					}
					else if($key=='honor'){
						$item->setAttribute('type', 14);
					}
					$item->setAttribute('num', $num);
						$item->setAttribute('effect',$param['reward']['effect'][$key]);
					$baseReward->appendChild($item);
				}
			}

			if($param['reward']['extraReward']['minJade']){
				$extraReward=$doc->createElement('extraReward');
				$extraReward=$level->appendChild($extraReward);

				//额外
				//道具
				$extraReward->setAttribute('minJade',$param['reward']['extraReward']['minJade']);
				if(!empty($param['reward']['extraReward']['itemList'])){
					foreach ($param['reward']['extraReward']['itemList'] as $aim_item){
						$item=$doc->createElement('item');
						$item->setAttribute('id', $aim_item['itemId']);
						$item->setAttribute('number', $aim_item['number']);
						$item->setAttribute('bind', $aim_item['bind']);
						$item->setAttribute('effect', $aim_item['effect']);
						$extraReward->appendChild($item);
					}
				}
				//目标金钱
				if(!empty($param['reward']['extraReward']['moneyList'])){
					foreach ($param['reward']['extraReward']['moneyList'] as $money_item){
						$item=$doc->createElement('money');
						$item->setAttribute('type', $money_item['type']);
						$item->setAttribute('num', $money_item['money']);
						$item->setAttribute('effect', $money_item['effect']);
						$extraReward->appendChild($item);
					}
				}
				//特殊物品
				if(!empty($param['reward']['extraReward']['otherList'])){
					foreach ($param['reward']['extraReward']['otherList'] as $key => $num){
						if(!$num) continue;
						$item=$doc->createElement('money');
						if($key=='exploit'){
							$item->setAttribute('type', 8);
						}
						else if($key=='arenaPoint'){
							$item->setAttribute('type', 10);
						}
						else if($key=='honor'){
							$item->setAttribute('type', 14);
						}
						$item->setAttribute('num', $num);
						$item->setAttribute('effect',$param['reward']['extraReward']['effect'][$key]);
						$extraReward->appendChild($item);
					}
				}
			}
		}
		return $activity;
	}

	public function list26(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$doc=$this->doc;
		$xml_config=array();
		if($this->activity_xml){
			$doc->loadXML($this->activity_xml);
		}elseif(is_file($xml_file)){
			$doc->load($xml_file);
		}else{
			return $xml_config;
		}
		$activitys=$doc->getElementsByTagName('activity');
		foreach($activitys as $activity){
			$xml_config['name']=$activity->getAttribute('name');
			$xml_config['show_time']=$activity->getElementsByTagName('show_time')->item(0)->nodeValue;
			$xml_config['hide_time']=$activity->getElementsByTagName('hide_time')->item(0)->nodeValue;
			$xml_config['info']=$activity->getElementsByTagName('info')->item(0)->nodeValue;
		}
		return $xml_config;
	}
	
	public function getActivityXml(){
		$function_name='xml'.$this->type;
		$activity_xml=NULL;
		if(method_exists($this, $function_name)){
			$doc=$this->doc;
			$new_activity=$this->$function_name($doc);
			$activity_xml=$doc->saveXML();
		}
		return $activity_xml;
	}
	

	public function getList(){
		$function_name='list'.$this->type;
		$list=array();
		if(method_exists($this, $function_name)){
			$list=$this->$function_name();
		}
		return $list;
	}
	
	private function getNewActivity(){
		$doc=$this->doc;
		$doc->loadXml($this->activity_xml);
		return $doc->documentElement;
	}
	
	function write(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		return file_put_contents($xml_file, $this->activity_xml);
	}

	function remove(){
		$xml_file=__XML__."activity_{$this->type}.xml";
		$result=true;
		if(is_file($xml_file)){
			$result=@unlink($xml_file);
		}
		return $result;
	}
}