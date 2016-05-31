<?php
//玩家创号、升级推送信息到游戏平台
include __API__ . 'lib/ApiBase.class.php';
class Push extends ApiBase {
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
	
	public function post(){
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
