<?php
//任务类
class StatBoss{
	var $conf_filename='';//任务配置文件名
	var $cache_filename='';//数据缓存路径
	public function __construct(){
		$version=get_server_version();
		$bin_conf_filename=dirname(dirname(dirname(__ROOT__)))."/server_bin/{$version}/bin/config/xml/scene/copy.xml";//旧路径
		$bin_conf_filename_new=dirname(dirname(dirname(dirname(__ROOT__))))."/server_bin/{$version}/bin/config/xml/scene/copy.xml";//新路径
		if(is_file($bin_conf_filename)){
			$this->conf_filename=$bin_conf_filename;
		}else if(is_file($bin_conf_filename_new)){
			$this->conf_filename=$bin_conf_filename_new;
		}else{
			$this->conf_filename=__XML__.'copy.xml';
		}
		$this->cache_filename=__RUNTIME__.'cache/'.md5($this->conf_filename.'_stat').'.php';

	}

	/**
	 +----------------------------------------------------------
	 * 获得所有列表
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getList(){
		$list=array();
		$file_time=$cache_time=0;
		if(file_exists($this->conf_filename))
			$file_time=filemtime($this->conf_filename);//配置文件Sửa时间
		if(file_exists($this->cache_filename))
			$cache_time=filemtime($this->cache_filename);//缓存文件Sửa时间
		if($file_time>$cache_time || !$cache_time || time()-$cache_time>7*86400){
			$list=$this->getListByXml();
			$this->createCache($list);//缓存数据
		}elseif($cache_time>0){
			$list=$this->getListByCache();
		}
		return $list;
	}

	/**
	 +----------------------------------------------------------
	 * 根据id获得名称
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $id 任务id
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
	 * 根据id获得信息
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $id 任务id
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
	 * 根据id获得信息---只处理 乾坤八卦副本
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param string $id 任务id
	 +----------------------------------------------------------
	 * @return array
	 +----------------------------------------------------------
	 */
	public function getInfoId($id){
		$list=$this->getListByXmlId();
		return array_key_exists($id, $list) ? $list[$id] : null;
	}

	/**
	 +----------------------------------------------------------
	 * 从xml配置读取列表，并生成缓存文件---只处理 乾坤八卦副本
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 任务列表
	 +----------------------------------------------------------
	 */
	public function getListByXmlId(){
		$data=array();
		$doc=new DOMDocument();
		$doc->load($this->conf_filename);
		$copys=$doc->getElementsByTagName('copy');
		foreach($copys as $copy){
			$monster_list=array();
			$entryid=$copy->getAttribute('entryid');
			$copy_name=$copy->getAttribute('name');
			$layers=$copy->getElementsByTagName('layer');
			$i=1;
			foreach($layers as $layer){
				$bosses=$layer->getElementsByTagName('boss');
				foreach($bosses as $boss){
					$occ=$boss->getAttribute('occ');
					$name=$boss->nodeValue;
					$monster_list[$occ]=array('occ'=>$occ,'name'=>$name,'is_boss'=>1,'boss_level'=>$i);
				}
				++$i;
			}
			$data[$entryid]=array('name'=>$copy_name,'layer_list'=>$monster_list);
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 从xml配置读取列表，并生成缓存文件
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 任务列表
	 +----------------------------------------------------------
	 */
	private function getListByXml(){
		$data=array();
		$doc=new DOMDocument();
		$doc->load($this->conf_filename);
		$copys=$doc->getElementsByTagName('copy');

		foreach ($copys as $copy){
			$monster_list=array();
			$entryid=$copy->getAttribute('entryid');
			$copy_name=$copy->getAttribute('name');
			$layers=$copy->getElementsByTagName('layer');
			foreach ($layers as $layer){
				$layer_name=$layer->getAttribute('name');
				$bosses=$layer->getElementsByTagName('boss');
				$i=0;  //boss_level 等级 1,2......
				foreach ($bosses as $boss){
					$occ=$boss->getAttribute('occ');
					$name=$boss->nodeValue;
					$monster_list[$occ]=array('occ'=>$occ,'name'=>$name,'is_boss'=>1,'boss_level'=>++$i);
				}
			}
			$data[$entryid]=array('name'=>$copy_name,'layer_list'=>$monster_list);
		}
		return $data;
	}

	/**
	 +----------------------------------------------------------
	 * 生成缓存文件
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 列表
	 +----------------------------------------------------------
	 */
	private function createCache($data){
		file_put_contents($this->cache_filename, "<?php\n//".serialize($data)."\n?>");
	}

	/**
	 +----------------------------------------------------------
	 * 从缓存文件读取任务列表
	 +----------------------------------------------------------
	 * @access private
	 +----------------------------------------------------------
	 * @return array 列表
	 +----------------------------------------------------------
	 */
	private function getListByCache(){
		$content=substr(file_get_contents($this->cache_filename),8,-3);
		return unserialize($content);
	}
}