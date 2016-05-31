<?php
//坐骑类
class Ride{
	var $conf_filename='';//配置文件名
	var $cache_time=86400;//缓存时间

	public function __construct(){
		$version=get_server_version();
		$bin_conf_filename=dirname(dirname(dirname(__ROOT__)))."/server_bin/{$version}/bin/config/xml/ride/ride_model.xml";//旧路径
		$bin_conf_filename_new=dirname(dirname(dirname(dirname(__ROOT__))))."/server_bin/{$version}/bin/config/xml/ride/ride_model.xml";//新路径
		if(file_exists($bin_conf_filename)){
			$this->conf_filename=$bin_conf_filename;
		}else if(file_exists($bin_conf_filename_new)){
			$this->conf_filename=$bin_conf_filename_new;
		}else{
			$this->conf_filename=__XML__.'ride_model.xml';
		}
	}

	/**
	 +----------------------------------------------------------
	 * 获得坐骑的系
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getSeriesList(){
		$cache_name=__CLASS__.'_'.__FUNCTION__;
		$data=array();
		if(S($cache_name)){
			$data=S($cache_name);
		}elseif(file_exists($this->conf_filename)){
			$reader = new XMLReader();
			$reader->open($this->conf_filename);
			while ($reader->read()){
				if($reader->localName=='ride' && $reader->nodeType==XMLReader::ELEMENT){
					$series=$reader->getAttribute('series');
					$name=$reader->getAttribute('name');
					$data[$series-1]=$name;
				}
			}
			S($cache_name,$data,$this->cache_time);
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 获得坐骑形象模型列表
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
				if($reader->localName=='model' && $reader->nodeType==XMLReader::ELEMENT){
					$model=$reader->getAttribute('id');
					$name=$reader->getAttribute('name');
					$data[$model]=$name;
				}
			}
			S($cache_name,$data);
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 获得坐骑形象模型名称
	 +----------------------------------------------------------
	 * @param string $model 坐骑形象模型id
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	public function getModelName($model){
		$list=$this->getModelList();
		return isset($list[$model]) ? $list[$model] : $model;
	}
}