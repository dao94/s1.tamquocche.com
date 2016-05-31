<?php
//任务类
class Ceshimulu{
	var $conf_filename='';//任务配置文件名
	var $cache_filename='';//数据缓存路径

	public function __construct(){
		$version=get_server_version();
		echo dirname(__ROOT__)."\n";
		echo dirname(dirname(dirname(dirname(__ROOT__))))."/server_bin/{$version}/bin/config/xml/scene/scene.xml"."\n";
		$bin_conf_filename=dirname(dirname(dirname(__ROOT__)))."/server_bin/{$version}/bin/config/xml/scene/scene.xml";
		if(file_exists($bin_conf_filename)){		
			$this->conf_filename=$bin_conf_filename;
			echo 'cunzai:'.$this->conf_filename."\n";
		}else{
			$this->conf_filename=__XML__.'copy.xml';
			echo 'bucunzai:'.$this->conf_filename."\n";
		}
	}
	
	public function helloWold(){
		echo 'hello world'."\n";
	}
}

?>
