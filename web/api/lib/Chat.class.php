<?php
//聊天监控数据推送到平台
include __API__ . 'lib/ApiBase.class.php';
class Chat extends ApiBase {
	protected $params = array();

	public function __construct(){
		exit('error');
	}

	//工厂方法
	final static function factory() {
		static $obj = null;
		if (!is_null($obj)){
			return $obj;
		}
		$className = __CLASS__ . SERVER_AGENT;
		$classFile = __API__ . '/lib/' . SERVER_AGENT . '/' . $className . '.class.php';
		if (file_exists($classFile)) {
			include $classFile;
			$obj = new $className();
		} else {
			$obj = new self();
		}
		return $obj;
	}
	
	//检测是否已经被封禁
	protected function checkForbid(){
		$sql="select count(*) as count from gm_forbid where char_name='{$this->params['name']}' and end_time>{$this->params['time']} and status=1 and type=1";
		$mysqli=new DbMysqli();
		$count=$mysqli->count($sql);
		$count&&exit('already forbid');
	}
	
	public function post(){
		$this->checkForbid();
		$url=$this->params['url'];
		unset($this->params['url']);
		$params=http_build_query($this->params);
		$result=http_post($url,$params);
		exit($result);
	}

	public function run() {
		$this->post();
	}
}

?>
