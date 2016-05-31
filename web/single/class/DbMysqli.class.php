<?php
!defined('MYSQL_HOST') && exit('forbid');
class DbMysqli extends mysqli{
	private $mysqli;
	public $result=null;

	/**
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $dbname
	 * @param string $charest
	 */
	public function __construct($host=MYSQL_HOST,$port=MYSQL_PORT,$user=MYSQL_USER, $pwd=MYSQL_PWD, $dbname=MYSQL_DB, $charest='utf8' ){
		$this->mysqli = new mysqli($host,$user, $pwd, $dbname,$port);
		if($this->mysqli->connect_errno){
			$this->mysqli = NULL;
			die('Connect error:'.$this->mysqli->connect_error);
		}else{
			$this->mysqli->set_charset($charest);
		}
	}
	
	public function __destruct(){
		$this->close();
	}
	
	/**
	 * 执行sql
	 * @param string $sql
	 * @param string $limit
	 * @return object
	 */
	public function query($sql){
		$this->result = $this->mysqli->query($sql);
		return $this->result;
	}
	
	//查出所有记录返回二维数组
	public function find($sql) {
		$arr=array();
		$this->result = $this->query($sql);
		if ($this->result) {
			while ($assoc = $this->result->fetch_assoc()) {
				$arr[] = $assoc;
			}
			$this->freeResut();
		}
		return $arr;
	}
	
	//查询一条记录返回一维数组
	public function findOne($sql) {
		$arr=array();
		$this->result = $this->query($sql);
		if ($this->result) {
			$arr= $this->result->num_rows > 0 ? $this->result->fetch_assoc() : array();
			$this->freeResut();
		}
		return $arr;
	}
	
	//记录条数
	public function count($sql) {
		//分析sql语句是否有count函数
		preg_match('#count\(.*\)#Ui', $sql, $matche1);
		preg_match('#count\(.*\) as ([^\s,]+)#i', $sql, $matche2);
		if (!empty($matche1) || !empty($matche2)) {
			$arr = $this->findOne($sql);
			if (!empty($arr)) {
				return !empty($matche2) ? $arr[trim($matche2[1], '`')] : $arr[$matche1[0]];
			} else {
				return 0;
			}
		} else {
			$this->query($sql);
			return $this->mysqli->affected_rows;
		}
	}
	
	//释放查询结果集
	public function freeResut(){
		$this->result->free();
	}
	
	//关闭数据库连接
	public function close(){
		if(isset($this->mysqli) && is_object($this->mysqli)){
			$this->mysqli->close();
			unset($this->mysqli);
		}
	}
}

?>