<?php
//技能类
class Skill{
	var $conf_filename='';//配置文件名
	var $cache_filename='';//数据缓存路径

	public function __construct(){
		$local_conf_filename=dirname(__ROOT__).'/game_res/resource/assets/data/res/skill.xml';
		$bin_conf_filename=dirname(dirname(dirname(__ROOT__))).'/client_bin/game_res/resource/assets/data/res/skill.xml';//旧路径
		$bin_conf_filename_new=dirname(dirname(dirname(dirname(__ROOT__)))).'/client/game_res/resource/assets/data/res/skill.xml';//新路径
		if(is_file($local_conf_filename)){
			$this->conf_filename=$local_conf_filename;
		}elseif(is_dir($bin_conf_filename)){
			$this->conf_filename=$bin_conf_filename;
		}else if(is_file($bin_conf_filename_new)){
			$this->conf_filename=$bin_conf_filename_new;
		}else{
			include __CONFIG__.'source_config.php';
			if(!empty($cdn_config[0])){
				$this->conf_filename=$cdn_config[0].'/game_res/resource/assets/data/res/skill.xml';
			}
		}
		$this->cache_filename=__RUNTIME__.'cache/'.md5($this->conf_filename).'.php';
	}

	/**
	 +----------------------------------------------------------
	 * 获得所有技能列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getList(){
		$list=array();
		$file_time=$cache_time=$entry_time=0;
		if(file_exists($this->conf_filename))
		$file_time=filemtime($this->conf_filename);//配置文件Sửa时间
		if(file_exists($this->cache_filename))
		$cache_time=filemtime($this->cache_filename);//缓存文件Sửa时间
		if($file_time>$cache_time || !$cache_time || time()-$cache_time>5*86400){
			$list=$this->getListByXml();
			$this->createCache($list);//缓存数据
		}elseif($cache_time>0){
			$list=$this->getListByCache();
		}
		return $list;
	}

	/**
	 +----------------------------------------------------------
	 * 根据技能id获得技能名称
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $id 技能id
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	public function getName($id){
		$list=$this->getList();
		$id=(string)$id;
		return array_key_exists($id, $list) ? $list[$id]['name'] : $id;
	}

	/**
	 +----------------------------------------------------------
	 * 根据技能id获得技能信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $id 技能id
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
	 * 从xml配置读取技能列表，并生成缓存文件
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 技能列表
	 +----------------------------------------------------------
	 */
	private function getListByXml(){
		$data=array();
		$reader = new XMLReader();
		$reader->open($this->conf_filename);
		while ($reader->read()){
			if($reader->localName=='skill' && $reader->nodeType==XMLReader::ELEMENT){
				$id=$reader->getAttribute('id');
				$name=$reader->getAttribute('name');
				$level=intval(substr($id, -3,3));//id后三位是技能等级
				$item=array('name'=>$name,'level'=>$level);
				$id && !isset($data[$id]) ? $data[$id]=$item : '';
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
	 * @return array 技能列表
	 +----------------------------------------------------------
	 */
	private function createCache($data){
		file_put_contents($this->cache_filename, "<?php\n//".serialize($data)."\n?>");
	}

	/**
	 +----------------------------------------------------------
	 * 从缓存文件读取技能列表
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 技能列表
	 +----------------------------------------------------------
	 */
	private function getListByCache(){
		$content=substr(file_get_contents($this->cache_filename),8,-3);
		return unserialize($content);
	}
}