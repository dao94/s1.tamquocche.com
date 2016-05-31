<?php
include __API__ . 'lib/ApiBase.class.php';
include __CLASSES__.'Task.class.php';
class Order extends ApiBase {
	protected $params = array();

	protected $paramsExchange = array(
		'sid' => array('sid', 'intval', 2, ''),
		'order_id' => array('order_id', 'my_escape_string', 2, ''),
		'start_time' => array('start_time', 'intval', 2, ''),
		'end_time' => array('end_time', 'intval', 2, ''),
		'time' => array('time', 'intval', 2, ''),
		'sign' => array('sign', 'my_escape_string', 1, ''),
	);

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


	//验证码校验
	protected function checkCode() {
		if ($this->params['sign'] !== $this->params['flag']) {
			$this->apiReturn(8002);
		}
		return $this;
	}

	//超时校验
	protected function timeout() {
		if (abs(time() - $this->params['time']) > 180) {
			$this->apiReturn(8001);
		}
		return $this;
	}

	//获取数据
	protected function getData(){
		$task=new Task();
		$mysqli=$task->mysqli();
		$start_time=empty($this->params['start_time']) ? '' : intval($this->params['start_time']);
		$end_time=empty($this->params['end_time']) ? '' : intval($this->params['end_time']);
		$order_id=empty($this->params['order_id']) ? '' : my_escape_string(trim($this->params['order_id']));
		$pre=empty($this->params['pre']) ? '' : $this->params['pre'];
		if($start_time=='' && $end_time=='' && $order_id=='')  $this->apiReturn(8007);
		$orderIds=explode(',', $this->params['order_id']);
		$array=array();
		foreach ($orderIds as $order_id){
			$array[]="'$pre$order_id'";
		}
		$order_str='';//
		if(!empty($array)){
			$order_str=implode(',',$array);
			$order_str=$order_str=='' ? ' ' : " and  order_id in ($order_str) " ;
		}
		$count=empty($this->params['count']) ? '' : intval($this->params['count']);
		$page=empty($this->params['page']) ? '' : intval($this->params['page']);
		$sql="select count(*) from pay_order ";
		$total_count=$mysqli->count($sql);
		$where=" where true ";
		$where .=$start_time=='' ? ' ' : " and ts>=$start_time ";
		$where .=$end_time=='' ?  ' ' : " and ts<=$end_time ";
		$limit='';
		if($count!='' && $page!='') {
			$offest=($page-1)*10;
			$limit=" limit $offest,$count";//分页
		}
 		$sql="select * from pay_order $where $order_str $limit";
		$result=$mysqli->query($sql);
		$data=$list=$list_data=array();
		while ($result && $row=$result->fetch_assoc()){
			$list['ServerID']=$row['sid'];
			$list['UserID']=$row['account'];
			$list['RoleID']=$row['char_id'];
			$row['order_id']=str_replace($pre,"",$row['order_id']);
			$list['SinaOrderID']=$row['order_id'];
			$list['GameOrderID']=$row['order_id'];
			$list['GamePoint']=$row['gold'];
			$list['PayPoint']=round($row['money']/100,2);
		    $list_data[]=$list;

		}
		if(empty($list_data)) {
			$this->apiReturn(8009,$data);
		}else{
			$this->apiReturn(0,$list_data,$total_count);
		}

	}

	public function run() {
		$this->timeout()->checkCode()->getData();
	}

}

?>
