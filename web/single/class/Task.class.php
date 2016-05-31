<?php
class Task{
	public $name;//任务名称
	var $mysqli;//mysqli类
	var $start_time;//任务开始时间（毫秒）
	var $error;

	public function __construct(){
		$microtime=explode(' ', microtime());
		$this->start_time=$microtime[0]+$microtime[1];
		$this->mysqli=new DbMysqli();
	}

	/**
	 +----------------------------------------------------------
	 * 解析args参数 转化为常用的GET/post的参数模式
	 +----------------------------------------------------------
	 * @param array $args 需要转换的字符串 格式array('--a=AA','--b=BB')
	 +----------------------------------------------------------
	 * @return array 格式 array('a'=>'AA','b'=>'BB')
	 +----------------------------------------------------------
	 */
	public function parseArgs($argv){
		$out=array();
		foreach ($argv as $arg){
			if (substr($arg,0,2) == '--'){
				$eqPos = strpos($arg,'=');
				if ($eqPos === false){
					$key = substr($arg,2);
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				} else {
					$key = substr($arg,2,$eqPos-2);
					$out[$key] = substr($arg,$eqPos+1);
				}
			} else if (substr($arg,0,1) == '-'){
				if (substr($arg,2,1) == '='){
					$key = substr($arg,1,1);
					$out[$key] = substr($arg,3);
				} else {
					$chars = str_split(substr($arg,1));
					foreach ($chars as $char){
						$key = $char;
						$out[$key] = isset($out[$key]) ? $out[$key] : true;
					}
				}
			} else {
				$out[] = $arg;
			}
		}
		return $out;
	}

	/**
	 +----------------------------------------------------------
	 * 获取最佳开始日期，结合流水表、统计表、开服时间等因素处理
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param date $start_date 开始时间（2013-06-01）
	 * @param array $stat_table 统计表信息，格式：array('name'=>'表名','field'=>'时间字段，非时间戳')
	 * @param array $log_table 流水表信息  格式：array('name'=>'表名','field'=>'时间字段,时间戳')
	 +----------------------------------------------------------
	 * @return date 如（2013-07-01）
	 +----------------------------------------------------------
	 */
	public function getStartDate($start_date,$stat_table,$log_table=array()){
		if(empty($start_date)){
			$sql="select max({$stat_table['field']}) as date from {$stat_table['name']}";
			$sql.=isset($stat_table['where']) ? " where {$stat_table['where']}" : '';
			$list=$this->mysqli->findOne($sql);
			if(!empty($list['date'])){
				$start_date=date('Y-m-d',strtotime($list['date'])+86400);
			}elseif($log_table){
				$sql="select min({$log_table['field']}) as time from {$log_table['name']}";
				$sql.=isset($log_table['where']) ? " where {$log_table['where']}" : '';
				$list=$this->mysqli->findOne($sql);
				if(!$list)	exit('No data');
				$start_date=date('Y-m-d',$list['time']);
			}
		}elseif($log_table){
			//记录最早时间
			$sql="select min({$log_table['field']}) as time from {$log_table['name']}";
			$sql.=isset($log_table['where']) ? " where {$log_table['where']}" : '';
			$list=$this->mysqli->findOne($sql);
			if($list){
				$min_date=date('Y-m-d',$list['time']);
				$start_date=$start_date<$min_date ? $min_date : $start_date;
			}
		}
		$server_open_date=date('Y-m-d',SERVER_OPEN_TIME);
		return $start_date>$server_open_date ? $start_date : $server_open_date;
	}

	public function mysqli(){
		return $this->mysqli;
	}

	/**
	 +----------------------------------------------------------
	 * 析构函数,写耗时日志
	 +----------------------------------------------------------
	 */
	function __destruct(){
		$microtime=explode(' ', microtime());
		$end_time=$microtime[0]+$microtime[1];
		$data['name']=isset($_SERVER['argv']) ? implode(' ',$_SERVER['argv']) : $_SERVER['SCRIPT_FILENAME'].' '.$this->name;
		$data['error']=$this->error;
		$data['start_date']=date('Ymd H:i:s',$this->start_time);
		$data['time']=round($end_time-$this->start_time,5);
		write_log($data,'task_'.date('Ym'));
	}
}