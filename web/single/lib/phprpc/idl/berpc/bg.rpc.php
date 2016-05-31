<?php

namespace berpc{

/*************************************** B2eBgOper head*************************************/


/*remote invoke sour class*/
class Sour_B2eBgOper extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "berpc_B2eBgOper";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_CENTER);
	}
	public function hotUpdate_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function flushPrint_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
	public function b2eUpdateActivity_async($msg)
	{
		\Operator::invokeAsync($this, 2, NULL, $msg);
	}
}



/*************************************** B2eBgOper end *************************************/

}
?>
