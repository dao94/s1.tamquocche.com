<?php
//场景、副本类
class Scene{
	var $cache_filename='';//数据缓存路径
	var $map_filename='';//地图配置文件名
	var $entry_filename='';//副本配置文件
	var $small_path='';//缩略图路径
	var $lost_path='';//生成图片路径
	var $cell_width=32;//单元格宽度px
	var $cell_height=16;//单元格高度px
	public $fill_width=5;//填充的宽度
	public $fill_height=5;//填充的高度

	public function __construct(){
		$local_map_file=dirname(__ROOT__).'/game_res/resource/assets/data/res/mapInfo.xml';
		$bin_map_file=dirname(dirname(dirname(__ROOT__))).'/client_bin/game_res/resource/assets/data/res/mapInfo.xml';//旧路径
		$bin_map_file_new=dirname(dirname(dirname(dirname(__ROOT__)))).'/client/game_res/resource/assets/data/res/mapInfo.xml';//新路径
		if(is_file($local_map_file)){
			$this->map_filename=$local_map_file;
		}elseif(is_file($bin_map_file)){
			$this->map_filename=$bin_map_file;
		}else if(is_file($bin_map_file_new)){
			$this->map_filename=$bin_map_file_new;
		}else{
			include __CONFIG__.'source_config.php';
			if(!empty($cdn_config[0])){
				$this->map_filename=$cdn_config[0].'/game_res/resource/assets/data/res/mapInfo.xml';
			}
		}

		$local_small_path=dirname(__ROOT__).'/game_res/resource/assets/map/';
		$bin_small_path=dirname(dirname(dirname(__ROOT__))).'/client_bin/game_res/resource/assets/map/';//旧路径
		$bin_small_path_new=dirname(dirname(dirname(dirname(__ROOT__)))).'/client/game_res/resource/assets/map/';//新路径
		if(is_file($local_map_file)){
			$this->small_path=$local_small_path;
		}elseif(is_file($bin_small_path)){
			$this->small_path=$bin_small_path;
		}else if(is_file($bin_small_path_new)){
			$this->small_path=$bin_small_path_new;
		}else{
			include __CONFIG__.'source_config.php';
			if(!empty($cdn_config[0])){
				$this->small_path=$cdn_config[0].'/game_res/resource/assets/map/';
			}
		}

		$version=get_server_version();
		$bin_entry_filename=dirname(dirname(dirname(__ROOT__)))."/server_bin/{$version}/bin/config/xml/scene/scene_layer.xml";//旧路径
		$bin_entry_filename_new=dirname(dirname(dirname(dirname(__ROOT__))))."/server_bin/{$version}/bin/config/xml/scene/scene_layer.xml";//新路径
		if(is_file($bin_entry_filename_new)){
			$this->entry_filename=$bin_entry_filename_new;
		}else{
			$this->entry_filename=$bin_entry_filename;
		}

		$this->cache_filename=__RUNTIME__.'cache/'.md5($this->map_filename).'.php';
		$this->lost_path=__RUNTIME__.'images/';
	}

	/**
	 +----------------------------------------------------------
	 * 获得所有地图列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getList(){
		$list=array();
		$file_time=$cache_time=$entry_time=0;
		if(file_exists($this->map_filename))
		$file_time=filemtime($this->map_filename);//配置文件Sửa时间
		if(file_exists($this->cache_filename))
		$cache_time=filemtime($this->cache_filename);//缓存文件Sửa时间
		if(file_exists($this->entry_filename))
		$entry_time=filemtime($this->entry_filename);
		if($file_time>$cache_time || $entry_time>$cache_time || !$cache_time || time()-$cache_time>86400*3){
			$map_list=$this->getMapListByXml();
			$entry_list=$this->getEntryListByXml();
			$list=$entry_list+$map_list;
			$this->createCache($list);//缓存数据
		}elseif($cache_time>0){
			$list=$this->getListByCache();
		}
		return $list;
	}

	/**
	 +----------------------------------------------------------
	 * 根据地图id获得地图名称
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $id 地图id
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	public function getName($id){
		$list=$this->getList();
		return array_key_exists($id, $list) ? $list[$id]['name'] : $id;
	}

	/**
	 +----------------------------------------------------------
	 * 根据地图id获得地图信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $id 地图id
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getInfo($id){
		$list=$this->getList();
		return array_key_exists($id, $list) ? $list[$id] : null;
	}

	/**
	 +----------------------------------------------------------
	 * 在地图缩略图上描点
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param int $id 地图id
	 * @param array $data 描点数组 格式array(array('x'=>110,'y'=>220),array('x'=>33,'y'=>44))
	 * @param array $condition 由此生成缓存id
	 * @param int $cache_time 缓存时间（秒）
	 +----------------------------------------------------------
	 * @return array 描点后图片路径
	 +----------------------------------------------------------
	 */
	public function plot($id,$data,$condition=array(),$cache_time=3600){
		$small_name=$this->small_path.$id.'/main/small.jpg';
		$image_name=$this->lost_path.md5(serialize($condition)).'.jpg';
		if(!file_exists($small_name)){
			return array('status'=>0,'info'=>__('缩略图不存在'),'data'=>'');
		}
		if(file_exists($image_name) &&  filectime($image_name)+$cache_time>time()){
			$image=imagecreatefromjpeg($image_name);//文件存在并且为失效直接输出
		}else{
			//计算出图片文件
			$image=imagecreatefromjpeg($small_name);
			$color=imagecolorallocate($image,255, 0, 0);//点的颜色
			$small_info=getimagesize($small_name);
			$info=$this->getInfo($id);//地图真实信息
			if(!$info)	return array('status'=>0,'info'=>__('mapInfo.xml未配置'),'data'=>'');
			$width_ratio=$small_info[0]/$info['width'];//宽度缩放比例
			$height_ratio=$small_info[1]/$info['height'];//高度缩放比例
			foreach ($data as $row){
				$x=$row['x']*$width_ratio*$this->cell_width;
				$y=$row['y']*$height_ratio*$this->cell_height;
				imagefilledellipse($image,$x,$y,$this->fill_width,$this->fill_height,$color);
			}
		}
		imagejpeg($image,$image_name);//保存图片信息
		$relative_image_name=str_replace($_SERVER['DOCUMENT_ROOT'], '', $image_name);//图片相对路径
		return array('status'=>1,'info'=>__('图片路径'),'data'=>$relative_image_name);
	}

	/**
	 +----------------------------------------------------------
	 * 从xml配置读取地图列表，并生成缓存文件
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 地图列表
	 +----------------------------------------------------------
	 */
	private function getMapListByXml(){
		$data=array();
		$reader = new XMLReader();
		$reader->open($this->map_filename);
		$scene_name=$req_lvl=$sceneId=$mapW=$mapH=array();
		while ($reader->read()){
			if($reader->localName=='scene' && $reader->nodeType==XMLReader::ELEMENT ){
				$scene_id=$reader->getAttribute('id');
				$scene_name=$reader->getAttribute('scene_name');
				$data[$scene_id]['name']=$scene_name;
				while ($reader->read()){
					if($reader->localName=='req_lvl'&&$reader->nodeType==XMLReader::ELEMENT){
						$reader->read();
						$level=$reader->value;
						$data[$scene_id]['level']=$level;
						break;
					}
				}
				while ($reader->read()){
					if($reader->localName=='mapW'&&$reader->nodeType==XMLReader::ELEMENT){
						$reader->read();
						$width=$reader->value;
						$data[$scene_id]['width']=$width;
						break;
					}
				}
				while ($reader->read()){
					if($reader->localName=='mapH'&&$reader->nodeType==XMLReader::ELEMENT){
						$reader->read();
						$height=$reader->value;
						$data[$scene_id]['height']=$height;
						break;
					}
				}
			}
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 从xml配置读取副本列表，并生成缓存文件
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 副本列表
	 +----------------------------------------------------------
	 */
	private function getEntryListByXml(){
		$data=array();
		if(is_file($this->entry_filename)){
			$reader = new XMLReader();
			$reader->open($this->entry_filename);
			while ($reader->read()){
				if($reader->localName=='item' && $reader->nodeType==XMLReader::ELEMENT){
					$id=$reader->getAttribute('entry_id');
					$name=$reader->getAttribute('name');
					$item=array('name'=>$name,);
					$id && !isset($data[$id]) ? $data[$id]=$item : '';
				}
			}
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 生成缓存文件
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 地图列表
	 +----------------------------------------------------------
	 */
	private function createCache($data){
		file_put_contents($this->cache_filename, "<?php\n//".serialize($data)."\n?>");
	}

	/**
	 +----------------------------------------------------------
	 * 从缓存文件读取地图列表
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 地图列表
	 +----------------------------------------------------------
	 */
	private function getListByCache(){
		$content=substr(file_get_contents($this->cache_filename),8,-3);
		return unserialize($content);
	}
}