<?php

namespace brrpc{

/*************************************** B2rBgOper head*************************************/


/*remote invoke sour class*/
class Sour_B2rBgOper extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "brrpc_B2rBgOper";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_GM);
	}
	public function hotUpdate_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function flushPrint_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
}



/*************************************** B2rBgOper end *************************************/

}
?>
