<?php
class Item{
	public $item=array();
	var $attr=array();
	var $bag_attr_conf=array();

	public function __construct(){
		include_once __CONFIG__.'attr_config.php';
		$this->bag_attr_conf=$bag_attr_conf;
	}

	public function getAttr($item=array()){
		$this->attr=array();
		$this->item=$item;
		if(!empty($item['saveList']))	$this->saveList();
		if(!empty($item['otherList']))	$this->otherList();
		return $this->attr;
	}

	public function getList($item=array()){
		$list=array();
		$attr=$this->getAttr($item);
		foreach ($attr as $key=>$item){
			$list[]="<li>{$item['attr']}：{$item['value']}</li>";
		}
		return $list;
	}
	
	//获取装备道具颜色
	public function getColour($item_id){
		return substr($item_id,-1,1);
	}
	
	//获取装备道具等级
	public function getLevel($item_id){
		return substr($item_id,-4,3);
	}
	
	//获取装备道具部位
	public function getPart($item_id){
		return substr($item_id,1,2);
	}

	//附加属性
	private function saveList(){
		foreach ($this->item['saveList'] as $key=>$item){
			$this->attr[]=array(
				'attr'=>$this->bag_attr_conf[$item[0]],//属性
				'value'=>$item[1],//值
				//'star'=>$item[3],//星
			);
		}

	}

	private function otherList(){

	}
}