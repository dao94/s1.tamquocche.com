<?php
//宠物类
class Pet{
	var $conf_filename='pet.xml';//配置文件名
	var $hunt_conf_filename='hunt_item.xml';//兵符（猎灵系统物品）
	var $cache_time=86400;//缓存时间

	public function __construct(){
		$version=get_server_version();
		$bin_path=dirname(dirname(dirname(__ROOT__)))."/server_bin/{$version}/bin/config/xml/pet/";//旧路径
		$bin_path_new=dirname(dirname(dirname(dirname(__ROOT__))))."/server_bin/{$version}/bin/config/xml/pet/";//新路径
		if(is_file($bin_path.$this->conf_filename)){
			$this->conf_filename=$bin_path.$this->conf_filename;
		}else if(is_file($bin_path_new.$this->conf_filename)){
			$this->conf_filename=$bin_path_new.$this->conf_filename;
		}else{
			$this->conf_filename=__XML__.$this->conf_filename;
		}

		if(is_file($bin_path.$this->hunt_conf_filename)){
			$this->hunt_conf_filename=$bin_path.$this->hunt_conf_filename;
		}else if(is_file($bin_path_new.$this->hunt_conf_filename)){
			$this->hunt_conf_filename=$bin_path_new.$this->hunt_conf_filename;
		}else{
			$this->hunt_conf_filename=__XML__.$this->hunt_conf_filename;
		}
	}

	/**
	 +----------------------------------------------------------
	 * 获得宠物模型列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getModelList(){
		$cache_name=__CLASS__.'_'.__FUNCTION__;
		$data=array();
		if(S($cache_name)){
			$data=S($cache_name);
		}elseif(file_exists($this->conf_filename)){
			$reader = new XMLReader();
			$reader->open($this->conf_filename);
			while ($reader->read()){
				if($reader->localName=='pet' && $reader->nodeType==XMLReader::ELEMENT){
					$model=$reader->getAttribute('model');
					while ($reader->read()){
						if($reader->localName=='name'&&$reader->nodeType==XMLReader::ELEMENT){
							$reader->read();
							$name=$reader->value;
							$data[$model]=$name;
							break;
						}
					}
				}
			}
			S($cache_name,$data,$this->cache_time);
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 获得模型名称
	 +----------------------------------------------------------
	 * @param string $model 形象模型id
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	public function getModelName($model){
		$list=$this->getModelList();
		return isset($list[$model]) ? $list[$model] : $model;
	}

	/**
	 +----------------------------------------------------------
	 * 获得宠物兵符列表(猎灵系统物品)
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getItemList(){
		$cache_name=__CLASS__.'_'.__FUNCTION__;
		$data=array();
		if(S($cache_name)){
			$data=S($cache_name);
		}elseif(file_exists($this->hunt_conf_filename)){
			$reader = new XMLReader();
			$reader->open($this->hunt_conf_filename);
			while ($reader->read()){
				if($reader->localName=='item' && $reader->nodeType==XMLReader::ELEMENT){
					$id=$reader->getAttribute('id');
					$name=$reader->getAttribute('name');
					$data[$id]=$name;
				}
			}
			S($cache_name,$data,$this->cache_time);
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 获得兵符名称(猎灵系统物品名称)
	 +----------------------------------------------------------
	 * @param string $id 猎灵系统物品id
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	public function getItemName($id){
		$list=$this->getItemList();
		return isset($list[$id]) ? $list[$id] : $id;
	}

	/**
	 +----------------------------------------------------------
	 * 获得兵符等级(猎灵系统物品),id后2位
	 +----------------------------------------------------------
	 * @param string $id 猎灵系统物品id
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	public function getItemLevel($id){
		return intval(substr($id,-2,2));
	}
}