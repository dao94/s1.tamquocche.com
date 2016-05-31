<?php

namespace burpc{

	/*************************************** B2uOper head*************************************/


	/*remote invoke sour class*/
	class Sour_B2uOper extends \ProxyObject
	{
		function __construct()
		{
			$this->_identity = "burpc_B2uOper";
			$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_USER);
		}
		public function hotUpdate_async($msg)
		{
			\Operator::invokeAsync($this, 0, NULL, $msg);
		}
	}



	/*************************************** B2uOper end *************************************/

	class Des_B2uOper extends \RpcObject{

	public $__identity	= "burpc_B2uOper";

	public function testA($cont, $msg){
		echo  "function must override!";
	}
	public function testB($cont){
		echo "function must override!";
	}

	public function __dispatch($cont, $call, $buf){
		switch($call->_oper){
			case 0:
				return $this->__testA($cont, $buf);
				break;
			case 1:
				return $this->__testB($cont);
				break;
		}
		return DISPATCH_OPERATION_NOTEXIST;
	}

	private function __testA($cont, $buf){
		$msg = new HotUpate();
		Protocol::parseFromArray($msg, $buf);
		$this->testA($cont, $msg);

		return DISPATCH_OK;
	}
	private function __testB($cont){
		$this->testB($cont);
		return DISPATCH_OK;
	}
}

}
?>
