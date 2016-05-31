<?php
//多线程
class LogDelThread extends Thread{
	public $conf;
	public $table;
	public $del_time;
	public $result=array();//返回结果
	//public $mysqli=new DbMysqli();
	
	public function __construct($table,$conf,$del_time){
		$this->table=$table;
		$this->conf=$conf;
		$this->del_time=$del_time;
	}
	
	public function run(){
		if(!empty($this->table)){
			$mysqli=new DbMysqli();
			$field=empty($this->conf["field"])? "time" : $this->conf["field"];
			//$sql="select `id` from ".$this->table." where `".$field."` <".$this->del_time.(!empty($this->conf["where_add"])? " and ".$this->conf["where_add"]:"") ." order by `id` asc limit 1000000,1 ";
			$sql="select max(`id`) as id from ( select `id` from ".$this->table." where `".$field."` <".$this->del_time.(!empty($this->conf["where_add"])? " and ".$this->conf["where_add"]:"") ." order by `id` asc limit 0,1000000 ) as a";
			write_log(array(0=>$sql.';'), 'logdel_'.date('Ymd'));
			$list=$mysqli->findOne($sql);
			if(!empty($list['id'])){
				$maxId=$list['id'];
				$sql="delete from ".$this->table." where `id` <=".$maxId." and `".$field."` <".$this->del_time;
				if(!empty($this->conf["where_add"])){
					$sql.=" and ".$this->conf['where_add'];
				}
				//echo $sql;
				write_log(array(0=>$sql.';'), 'logdel_'.date('Ymd'));
				$mysqli->query($sql);
			}




			//$sql="select count(*) as count from " .$this->table." ".$this->where;
			//$count=$mysqli->count($sql);
			//$sql="delete from " .$this->table." ".$this->where." limit 1000000";
			//write_log(array(0=>$this->sql.';'), 'logdel_'.date('Ymd'));
			//$mysqli->query($this->sql);
			//echo $this->sql;
			//echo ';';
			//echo "<br>";
		}
	}
}